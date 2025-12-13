
$ErrorActionPreference = "Stop"

# Configuration
$dbName = "rdbv2"
$backupDir = "backups"
$dateStr = "2025_12_11"
$fullBackupFile = "$backupDir/backup_ordered_$dateStr.sql"
$structBackupFile = "$backupDir/structure_ordered_$dateStr.sql"
$dataBackupFile = "$backupDir/data_ordered_$dateStr.sql"
$listFile = "$backupDir/table_order_list_$dateStr.txt"

# Ensure clean state
Remove-Item "all_objects.txt", "table_deps.txt", "view_deps.txt", "sorted_objects.txt", "base_tables.txt" -ErrorAction SilentlyContinue

echo "1. Analyzing Database..."
# Get Objects and Dependencies (Ensure UTF8 output from mysql using --default-character-set=utf8mb4)
cmd /c "mysql -u root --default-character-set=utf8mb4 -N -e ""SHOW FULL TABLES FROM $dbName WHERE Table_type = 'BASE TABLE' OR Table_type = 'VIEW'"" | powershell -command ""`$Input | ForEach-Object { (`$_ -split '\t')[0] }"" > all_objects.txt"
cmd /c "mysql -u root --default-character-set=utf8mb4 -N -e ""SHOW FULL TABLES FROM $dbName WHERE Table_type = 'BASE TABLE'"" | powershell -command ""`$Input | ForEach-Object { (`$_ -split '\t')[0] }"" > base_tables.txt"
cmd /c "mysql -u root --default-character-set=utf8mb4 -N -e ""SELECT TABLE_NAME, REFERENCED_TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = '$dbName' AND REFERENCED_TABLE_NAME IS NOT NULL;"" > table_deps.txt"
cmd /c "mysql -u root --default-character-set=utf8mb4 -N -e ""SELECT VIEW_NAME, TABLE_NAME FROM INFORMATION_SCHEMA.VIEW_TABLE_USAGE WHERE VIEW_SCHEMA = '$dbName';"" > view_deps.txt"

# Fix encoding of text files to UTF-8 BOM for Python/PowerShell consistency
foreach ($f in @("all_objects.txt", "base_tables.txt", "table_deps.txt", "view_deps.txt")) {
    if (Test-Path $f) {
        $content = Get-Content $f
        $content | Out-File -Encoding UTF8 $f
    }
}

echo "2. Sorting Tables..."
python sort_tables.py > sorted_objects.txt

if (-not (Test-Path "sorted_objects.txt") -or (Get-Item "sorted_objects.txt").Length -eq 0) {
    Write-Error "Sorting failed."
}

$sortedObjects = Get-Content "sorted_objects.txt"

echo "3. Generating Structure Backup..."
$list = $sortedObjects -join " "
cmd /c "mysqldump -u root --default-character-set=utf8mb4 $dbName --tables $list --no-data --result-file=""$structBackupFile"""

# --- SIMPLE STRICT UTF-8 STRATEGY (User Approved) ---

# --- CHUNKED STRICT UTF-8 STRATEGY ---
# Force force force strict UTF-8 as requested.
# Split into approx 5MB chunks.

$chunkSizeLimit = 5 * 1024 * 1024 # 5 MB
$chunkIndex = 1
$currentChunkSize = 0
$baseFilename = "$backupDir/data_ordered_chunk"

# Helper to init a new chunk
function Start-NewChunk {
    param($idx)
    $filename = "${baseFilename}_${idx}_$dateStr.sql"
    "SET FOREIGN_KEY_CHECKS=0;" | Out-File -Encoding UTF8 $filename
    return $filename
}

echo "4. Generating Data Backups (Chunked 5MB, Strict UTF-8)..."

# 4.1 Initialize first chunk
$currentChunkFile = Start-NewChunk -idx $chunkIndex
$currentChunkSize = (Get-Item $currentChunkFile).Length

# 4.2 Iterate tables and append
foreach ($table in $sortedObjects) {
    $tempTableFile = "temp_table_dump.sql"
    
    # Dump single table (Strict UTF-8, Direct File Write)
    # Using --compact to reduce noise, but keeping --hex-blob for safety if blobs exist
    cmd /c "mysqldump -u root --default-character-set=utf8 --hex-blob $dbName --tables $table --no-create-info --skip-add-locks --skip-disable-keys --skip-set-charset --result-file=""$tempTableFile"""
    
    $tableSize = (Get-Item $tempTableFile).Length
    
    # Truncate statement (Strict UTF-8)
    $truncateStr = "TRUNCATE TABLE $table;`r`n"
    $truncateBytes = [System.Text.Encoding]::UTF8.GetBytes($truncateStr)
    $truncateSize = $truncateBytes.Length

    # Check if we need to rotate (New file if adding this table exceeds limit)
    # Exception: If table itself is huge (> limit) and file is empty, we must put it in.
    # Logic: If current > 0 AND (current + new) > limit -> Rotate
    if ($currentChunkSize -gt 0 -and ($currentChunkSize + $tableSize + $truncateSize) -gt $chunkSizeLimit) {
        # Close current chunk with footer
        "SET FOREIGN_KEY_CHECKS=1;" | Out-File -Append -Encoding UTF8 $currentChunkFile
        
        # Start new
        $chunkIndex++
        $currentChunkFile = Start-NewChunk -idx $chunkIndex
        $currentChunkSize = (Get-Item $currentChunkFile).Length
    }

    # Append Truncate
    # We use FileStream to append valid UTF-8 bytes to match the dump
    $fs = [System.IO.File]::Open($currentChunkFile, [System.IO.FileMode]::Append)
    $fs.Write($truncateBytes, 0, $truncateSize)
    $fs.Close()

    # Append Body (Stream Write to avoid cmd path issues)
    if ((Get-Item $tempTableFile).Length -gt 0) {
        $tableBytes = [System.IO.File]::ReadAllBytes("$pwd\$tempTableFile")
        $fs = [System.IO.File]::Open($currentChunkFile, [System.IO.FileMode]::Append)
        $fs.Write($tableBytes, 0, $tableBytes.Length)
        $fs.Close()
    }

    $currentChunkSize += $tableSize + $truncateSize
    Remove-Item $tempTableFile
}

# 4.3 Finalize last chunk
"SET FOREIGN_KEY_CHECKS=1;" | Out-File -Append -Encoding UTF8 $currentChunkFile

# Cleanup Old Single Files if exist
if (Test-Path "$backupDir/data_ordered_standard_$dateStr.sql") { Remove-Item "$backupDir/data_ordered_standard_$dateStr.sql" }


echo "5. Generating Full Backup..."
cmd /c "mysqldump -u root --default-character-set=utf8 --hex-blob $dbName --tables $list --result-file=""$fullBackupFile"""

echo "6. Generating List..."
$i = 1
$sortedObjects | ForEach-Object { "{0}. {1}" -f $i++, $_ } | Out-File -Encoding UTF8 $listFile

echo "Done."
