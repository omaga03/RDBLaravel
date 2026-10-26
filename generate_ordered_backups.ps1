param(
    [string]$dateStr = (Get-Date -Format "yyyy_MM_dd"),
    [string]$dbName = "rdbpcru",
    [string]$backupDir = "backups"
)

$ErrorActionPreference = "Stop"

# Configuration & Absolute Path Resolution
$scriptDir = $PSScriptRoot
if (-not $scriptDir) { $scriptDir = (Get-Location).Path }
Set-Location $scriptDir

$backupDirAbs = [System.IO.Path]::GetFullPath((Join-Path $scriptDir $backupDir))
if (-not (Test-Path $backupDirAbs)) {
    New-Item -ItemType Directory -Path $backupDirAbs | Out-Null
}

$fullBackupFile = Join-Path $backupDirAbs "backup_ordered_$dateStr.sql"
$structBackupFile = Join-Path $backupDirAbs "structure_ordered_$dateStr.sql"
$dataBackupFile = Join-Path $backupDirAbs "data_ordered_$dateStr.sql"
$listFile = Join-Path $backupDirAbs "table_order_list_$dateStr.txt"
$wpBackupFile = Join-Path $backupDirAbs "backup_wp_$dateStr.sql"
$baseChunkFilename = Join-Path $backupDirAbs "data_ordered_chunk"
$cleanDefinersScript = Join-Path $scriptDir "clean_definers.py"
$sortTablesScript = Join-Path $scriptDir "sort_tables.py"

# Ensure clean state
Remove-Item "all_objects.txt", "table_deps.txt", "view_deps.txt", "sorted_objects.txt", "base_tables.txt", "temp_table_dump.sql" -ErrorAction SilentlyContinue

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " MySQL Migration & Backup Package Generator ($dbName)" -ForegroundColor Cyan
Write-Host " Target Date: $dateStr" -ForegroundColor Cyan
Write-Host " Output Directory: $backupDirAbs" -ForegroundColor Cyan
Write-Host " Character Set: utf8mb4 (Strict Unicode)" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

Write-Host "`n1. Analyzing Database Schema & Dependencies..." -ForegroundColor Yellow

# Query all objects (Tables + Views)
cmd /c "mysql -u root --default-character-set=utf8mb4 -N -e ""SHOW FULL TABLES FROM $dbName WHERE Table_type = 'BASE TABLE' OR Table_type = 'VIEW'"" | powershell -command ""`$Input | ForEach-Object { (`$_ -split '\t')[0] }"" > all_objects.txt"
# Query base tables only
cmd /c "mysql -u root --default-character-set=utf8mb4 -N -e ""SHOW FULL TABLES FROM $dbName WHERE Table_type = 'BASE TABLE'"" | powershell -command ""`$Input | ForEach-Object { (`$_ -split '\t')[0] }"" > base_tables.txt"
# Query FK dependencies
cmd /c "mysql -u root --default-character-set=utf8mb4 -N -e ""SELECT TABLE_NAME, REFERENCED_TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = '$dbName' AND REFERENCED_TABLE_NAME IS NOT NULL;"" > table_deps.txt"
# Query View dependencies
cmd /c "mysql -u root --default-character-set=utf8mb4 -N -e ""SELECT VIEW_NAME, TABLE_NAME FROM INFORMATION_SCHEMA.VIEW_TABLE_USAGE WHERE VIEW_SCHEMA = '$dbName';"" > view_deps.txt"

# Ensure UTF-8 BOM encoding for Python compatibility
foreach ($f in @("all_objects.txt", "base_tables.txt", "table_deps.txt", "view_deps.txt")) {
    if (Test-Path $f) {
        $content = Get-Content $f
        $content | Out-File -Encoding UTF8 $f
    }
}

Write-Host "`n2. Sorting Tables and Views Topologically..." -ForegroundColor Yellow
python "$sortTablesScript" > sorted_objects.txt

if (-not (Test-Path "sorted_objects.txt") -or (Get-Item "sorted_objects.txt").Length -eq 0) {
    Write-Error "Topological sorting failed."
}

$sortedObjects = Get-Content "sorted_objects.txt"
$baseTables = Get-Content "base_tables.txt"
$objectListStr = $sortedObjects -join " "
Write-Host "   Total sorted objects: $($sortedObjects.Count) (Base tables: $($baseTables.Count))" -ForegroundColor Green

Write-Host "`n3. Generating Clean Structure Backup ($structBackupFile)..." -ForegroundColor Yellow
# Dump structure in topological order with utf8mb4
cmd /c "mysqldump -u root --default-character-set=utf8mb4 --routines --triggers --set-gtid-purged=OFF --no-data $dbName $objectListStr > ""$structBackupFile"""
# Clean DEFINER clauses for universal portability
python "$cleanDefinersScript" "$structBackupFile"
Write-Host "   Structure backup generated and definers cleaned." -ForegroundColor Green

Write-Host "`n4. Generating Monolithic Full Backup ($fullBackupFile)..." -ForegroundColor Yellow
# Prepend SET FOREIGN_KEY_CHECKS=0
"SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;`r`nSET NAMES utf8mb4;`r`n" | Out-File -Encoding UTF8 "$fullBackupFile"
# Dump all objects with data
cmd /c "mysqldump -u root --default-character-set=utf8mb4 --single-transaction --quick --hex-blob --routines --triggers --set-gtid-purged=OFF $dbName $objectListStr >> ""$fullBackupFile"""
# Append SET FOREIGN_KEY_CHECKS=1
"`r`nSET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;`r`n" | Out-File -Append -Encoding UTF8 "$fullBackupFile"
# Clean DEFINER clauses
python "$cleanDefinersScript" "$fullBackupFile"
Write-Host "   Full monolithic backup generated (Size: $([math]::Round((Get-Item $fullBackupFile).Length / 1MB, 2)) MB)." -ForegroundColor Green

Write-Host "`n5. Generating Consolidated & Chunked Data Backups..." -ForegroundColor Yellow
# Single data backup file
"SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;`r`nSET NAMES utf8mb4;`r`n" | Out-File -Encoding UTF8 "$dataBackupFile"

$chunkSizeLimit = 5 * 1024 * 1024 # 5 MB per chunk
$chunkIndex = 1

function Start-NewChunk {
    param($idx)
    $filename = "${baseChunkFilename}_${idx}_$dateStr.sql"
    "SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;`r`nSET NAMES utf8mb4;`r`n" | Out-File -Encoding UTF8 $filename
    return $filename
}

# Remove any previous chunk files
Remove-Item "${baseChunkFilename}_*_${dateStr}.sql" -ErrorAction SilentlyContinue

$currentChunkFile = Start-NewChunk -idx $chunkIndex
$currentChunkSize = (Get-Item $currentChunkFile).Length

foreach ($table in $sortedObjects) {
    # Only dump data for BASE TABLES, skip VIEWs
    if ($baseTables -notcontains $table) {
        continue
    }

    $tempTableFile = Join-Path $scriptDir "temp_table_dump.sql"
    
    # Dump single table data with utf8mb4 and hex-blob
    cmd /c "mysqldump -u root --default-character-set=utf8mb4 --hex-blob $dbName --tables $table --no-create-info --skip-add-locks --skip-disable-keys --skip-set-charset --result-file=""$tempTableFile"""
    
    $tableSize = (Get-Item $tempTableFile).Length
    $truncateStr = "TRUNCATE TABLE `$table`;`r`n"
    $truncateBytes = [System.Text.Encoding]::UTF8.GetBytes($truncateStr)
    $truncateSize = $truncateBytes.Length

    # Check if we need to rotate chunks
    if ($currentChunkSize -gt 0 -and ($currentChunkSize + $tableSize + $truncateSize) -gt $chunkSizeLimit) {
        "`r`nSET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;`r`n" | Out-File -Append -Encoding UTF8 $currentChunkFile
        $chunkIndex++
        $currentChunkFile = Start-NewChunk -idx $chunkIndex
        $currentChunkSize = (Get-Item $currentChunkFile).Length
    }

    # Append Truncate statement to Chunk File and Consolidated Data File
    $fsChunk = [System.IO.File]::Open($currentChunkFile, [System.IO.FileMode]::Append)
    $fsChunk.Write($truncateBytes, 0, $truncateSize)
    $fsChunk.Close()

    $fsConsolidated = [System.IO.File]::Open($dataBackupFile, [System.IO.FileMode]::Append)
    $fsConsolidated.Write($truncateBytes, 0, $truncateSize)
    $fsConsolidated.Close()

    # Append Data Body
    if ($tableSize -gt 0) {
        $tableBytes = [System.IO.File]::ReadAllBytes($tempTableFile)
        
        $fsChunk = [System.IO.File]::Open($currentChunkFile, [System.IO.FileMode]::Append)
        $fsChunk.Write($tableBytes, 0, $tableBytes.Length)
        $fsChunk.Close()

        $fsConsolidated = [System.IO.File]::Open($dataBackupFile, [System.IO.FileMode]::Append)
        $fsConsolidated.Write($tableBytes, 0, $tableBytes.Length)
        $fsConsolidated.Close()
    }

    $currentChunkSize += $tableSize + $truncateSize
    Remove-Item $tempTableFile -ErrorAction SilentlyContinue
}

# Close final chunk and consolidated data file
"`r`nSET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;`r`n" | Out-File -Append -Encoding UTF8 $currentChunkFile
"`r`nSET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;`r`n" | Out-File -Append -Encoding UTF8 $dataBackupFile

Write-Host "   Consolidated data backup: $([math]::Round((Get-Item $dataBackupFile).Length / 1MB, 2)) MB" -ForegroundColor Green
Write-Host "   Chunked data backups: $chunkIndex chunks generated (~5MB each)." -ForegroundColor Green

Write-Host "`n6. Generating Table Inventory & Order List ($listFile)..." -ForegroundColor Yellow
$inventoryScript = @"
`$tables = DB::select("SELECT TABLE_NAME, TABLE_TYPE, ENGINE, TABLE_COLLATION, TABLE_ROWS, ROUND((DATA_LENGTH + INDEX_LENGTH)/1024, 1) as size_kb FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$dbName'");
`$map = [];
foreach(`$tables as `$t) { `$map[`$t->TABLE_NAME] = `$t; }
`$sorted = file('sorted_objects.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
echo sprintf("%-4s | %-35s | %-10s | %-8s | %-20s | %-10s | %-10s\n", '#', 'Object Name', 'Type', 'Engine', 'Collation', 'Rows', 'Size (KB)');
echo str_repeat('-', 112) . "\n";
`$i = 1;
foreach(`$sorted as `$name) {
    `$info = `$map[`$name] ?? null;
    `$type = `$info ? (`$info->TABLE_TYPE === 'BASE TABLE' ? 'TABLE' : 'VIEW') : 'UNKNOWN';
    `$engine = `$info ? (`$info->ENGINE ?? '-') : '-';
    `$collat = `$info ? (`$info->TABLE_COLLATION ?? '-') : '-';
    `$rows = `$info ? number_format(`$info->TABLE_ROWS ?? 0) : '-';
    `$size = `$info ? (`$info->size_kb ? number_format(`$info->size_kb, 1) : '-') : '-';
    echo sprintf("%-4d | %-35s | %-10s | %-8s | %-20s | %-10s | %-10s\n", `$i++, `$name, `$type, `$engine, `$collat, `$rows, `$size);
}
"@
php -r "require 'vendor/autoload.php'; `$app = require_once 'bootstrap/app.php'; `$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); $inventoryScript" | Out-File -Encoding UTF8 "$listFile"
Write-Host "   Table inventory generated ($($sortedObjects.Count) objects listed)." -ForegroundColor Green

# 7. WordPress Backup (if exists)
Write-Host "`n7. Checking WordPress DB (wp)..." -ForegroundColor Yellow
$wpExists = (cmd /c "mysql -u root -N -e ""SHOW DATABASES LIKE 'wp'""")
if ($wpExists) {
    cmd /c "mysqldump -u root --default-character-set=utf8mb4 --single-transaction --quick --hex-blob --routines --triggers wp > ""$wpBackupFile"""
    python "$cleanDefinersScript" "$wpBackupFile"
    Write-Host "   WordPress backup generated: $wpBackupFile ($([math]::Round((Get-Item $wpBackupFile).Length / 1MB, 2)) MB)" -ForegroundColor Green
} else {
    Write-Host "   No 'wp' database found, skipping." -ForegroundColor Gray
}

# Cleanup temporary files
Remove-Item "all_objects.txt", "table_deps.txt", "view_deps.txt", "sorted_objects.txt", "base_tables.txt" -ErrorAction SilentlyContinue

Write-Host "`n==========================================================" -ForegroundColor Cyan
Write-Host " Backup & Migration Package Generation Complete!" -ForegroundColor Green
Write-Host " Files generated in: $backupDirAbs" -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Cyan
