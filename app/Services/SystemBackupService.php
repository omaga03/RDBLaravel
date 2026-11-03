<?php

namespace App\Services;

use App\Models\User;
use App\Models\AuditLog;
use App\Mail\RestoreOtpMail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use ZipArchive;

class SystemBackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Get backup storage directory path
     */
    public function getBackupDir(): string
    {
        return $this->backupDir;
    }

    /**
     * Create database backup (.sql.gz)
     */
    public function createDatabaseBackup(string $prefix = 'backup_db'): array
    {
        $mysqldump = $this->getMysqldumpBinary();
        if (!$mysqldump) {
            throw new \RuntimeException('ไม่พบคำสั่ง mysqldump บนเซิร์ฟเวอร์ กรุณาติดตั้งหรือระบุ MYSQLDUMP_PATH ใน .env');
        }

        $config = config('database.connections.mysql');
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $timestamp = date('Ymd_His');
        $sqlFileName = "{$prefix}_{$timestamp}.sql";
        $gzFileName = "{$sqlFileName}.gz";
        $sqlTempPath = $this->backupDir . DIRECTORY_SEPARATOR . $sqlFileName;
        $gzFinalPath = $this->backupDir . DIRECTORY_SEPARATOR . $gzFileName;

        // Build mysqldump command
        $cmd = [
            $mysqldump,
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--force',
            '--default-character-set=utf8mb4',
        ];

        // Skip column statistics to prevent errors when querying MariaDB / older MySQL schemas
        if ($this->supportsSkipColumnStatistics($mysqldump)) {
            $cmd[] = '--skip-column-statistics';
        }

        // Dynamically detect and ignore any broken legacy SQL views (prevents error 1356)
        try {
            $views = DB::select("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = ?", [$database]);
            foreach ($views as $v) {
                $vName = $v->TABLE_NAME;
                try {
                    DB::select("SELECT 1 FROM `{$vName}` LIMIT 1");
                } catch (\Throwable $e) {
                    $cmd[] = "--ignore-table={$database}.{$vName}";
                }
            }
        } catch (\Throwable $e) {
            // proceed
        }

        if (!empty($password)) {
            $cmd[] = "--password={$password}";
        }

        $cmd[] = $database;

        // Execute dump into temporary .sql file
        $process = new Process($cmd);
        $process->setTimeout(300); // 5 minutes timeout

        $outFile = fopen($sqlTempPath, 'w');
        if (!$outFile) {
            throw new \RuntimeException("ไม่สามารถสร้างไฟล์ชั่วคราว {$sqlTempPath} ได้");
        }

        $process->run(function ($type, $buffer) use ($outFile) {
            if ($type === Process::OUT) {
                fwrite($outFile, $buffer);
            }
        });
        fclose($outFile);

        if (!$process->isSuccessful()) {
            @unlink($sqlTempPath);
            $err = $process->getErrorOutput() ?: $process->getOutput();
            throw new \RuntimeException("การส่งออกฐานข้อมูลล้มเหลว: {$err}");
        }

        // Compress SQL file with gzip (level 9 for maximum compression)
        $rawSql = file_get_contents($sqlTempPath);
        $compressed = gzencode($rawSql, 9);
        file_put_contents($gzFinalPath, $compressed);
        @unlink($sqlTempPath); // remove uncompressed file

        $size = filesize($gzFinalPath);
        $checksum = md5_file($gzFinalPath);

        $this->logAction('backup_db_created', [
            'filename' => $gzFileName,
            'size' => $size,
            'checksum' => $checksum,
        ]);

        return [
            'filename' => $gzFileName,
            'path' => $gzFinalPath,
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'checksum' => $checksum,
            'type' => 'database',
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Create storage files backup (.zip)
     */
    public function createFilesBackup(string $prefix = 'backup_files'): array
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('PHP ZipArchive extension ไม่ได้เปิดใช้งาน');
        }

        $timestamp = date('Ymd_His');
        $zipFileName = "{$prefix}_{$timestamp}.zip";
        $zipPath = $this->backupDir . DIRECTORY_SEPARATOR . $zipFileName;

        $sourceDir = storage_path('app/public');
        if (!is_dir($sourceDir)) {
            throw new \RuntimeException('ไม่พบโฟลเดอร์ storage/app/public');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("ไม่สามารถสร้างไฟล์ ZIP ที่ {$zipPath} ได้");
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $fileCount = 0;
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = 'storage/' . substr($filePath, strlen($sourceDir) + 1);
                $zip->addFile($filePath, $relativePath);
                $fileCount++;
            }
        }

        $zip->close();

        $size = filesize($zipPath);
        $checksum = md5_file($zipPath);

        $this->logAction('backup_files_created', [
            'filename' => $zipFileName,
            'size' => $size,
            'file_count' => $fileCount,
            'checksum' => $checksum,
        ]);

        return [
            'filename' => $zipFileName,
            'path' => $zipPath,
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'file_count' => $fileCount,
            'checksum' => $checksum,
            'type' => 'files',
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Create full system archive (Database dump + Storage files + Environment in a single package)
     */
    public function createFullBackup(): array
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('PHP ZipArchive extension ไม่ได้เปิดใช้งาน');
        }

        $timestamp = date('Ymd_His');
        $fullFileName = "backup_full_{$timestamp}.zip";
        $fullZipPath = $this->backupDir . DIRECTORY_SEPARATOR . $fullFileName;

        // 1. First dump database
        $dbBackup = $this->createDatabaseBackup('temp_db');
        $dbPath = $dbBackup['path'];

        $zip = new ZipArchive();
        if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($dbPath);
            throw new \RuntimeException("ไม่สามารถสร้างไฟล์ ZIP เต็มรูปแบบได้");
        }

        // Add the SQL.gz into zip root
        $zip->addFile($dbPath, 'database/' . basename($dbPath));

        // Add storage/app/public files
        $sourceDir = storage_path('app/public');
        if (is_dir($sourceDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = 'storage/' . substr($filePath, strlen($sourceDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }

        // Add .env configuration snapshot
        if (file_exists(base_path('.env'))) {
            $zip->addFile(base_path('.env'), 'config/env_snapshot.txt');
        }

        // Add manifest metadata
        $manifest = [
            'system' => 'PCRU Research Database (laravel_rdb)',
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 'full_backup',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'db_backup_file' => basename($dbPath),
        ];
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $zip->close();
        @unlink($dbPath); // clean temp DB file

        $size = filesize($fullZipPath);
        $checksum = md5_file($fullZipPath);

        $this->logAction('backup_full_created', [
            'filename' => $fullFileName,
            'size' => $size,
            'checksum' => $checksum,
        ]);

        return [
            'filename' => $fullFileName,
            'path' => $fullZipPath,
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'checksum' => $checksum,
            'type' => 'full',
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * List all available backup archives sorted by newest first
     */
    public function getBackupList(): array
    {
        if (!is_dir($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $name = $file->getFilename();
            // Match our backup naming convention
            if (!preg_match('/^backup_(db|files|full|pre_restore_safety_snapshot)_.*\.(sql\.gz|zip)$/i', $name)) {
                continue;
            }

            $type = 'unknown';
            if (str_starts_with($name, 'backup_db_')) {
                $type = 'database';
            } elseif (str_starts_with($name, 'backup_files_')) {
                $type = 'files';
            } elseif (str_starts_with($name, 'backup_full_')) {
                $type = 'full';
            } elseif (str_starts_with($name, 'backup_pre_restore_safety_snapshot_')) {
                $type = 'safety_snapshot';
            }

            $backups[] = [
                'filename' => $name,
                'path' => $file->getRealPath(),
                'size' => $file->getSize(),
                'size_formatted' => $this->formatBytes($file->getSize()),
                'type' => $type,
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                'created_at_thai' => date('d/m/Y H:i:s', $file->getMTime()),
                'checksum' => md5_file($file->getRealPath()),
            ];
        }

        // Sort descending by creation timestamp
        usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    /**
     * Get verified path to a backup file (prevents directory traversal)
     */
    public function getBackupFilePath(string $filename): ?string
    {
        $clean = basename($filename);
        $fullPath = $this->backupDir . DIRECTORY_SEPARATOR . $clean;
        if (file_exists($fullPath) && is_file($fullPath)) {
            return $fullPath;
        }
        return null;
    }

    /**
     * Delete a backup file
     */
    public function deleteBackup(string $filename): bool
    {
        $path = $this->getBackupFilePath($filename);
        if ($path && @unlink($path)) {
            $this->logAction('backup_deleted', ['filename' => $filename]);
            return true;
        }
        return false;
    }

    /**
     * Clean backups older than specified days
     */
    public function cleanOldBackups(int $days = 30): int
    {
        $backups = $this->getBackupList();
        $cutoff = time() - ($days * 86400);
        $deleted = 0;

        foreach ($backups as $b) {
            $mtime = filemtime($b['path']);
            if ($mtime < $cutoff) {
                if (@unlink($b['path'])) {
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            $this->logAction('backups_cleaned_automatically', ['deleted_count' => $deleted, 'older_than_days' => $days]);
        }

        return $deleted;
    }

    /**
     * Send Restore OTP to The Creator via Email
     */
    public function sendRestoreOtp(User $creator, string $filename, string $ip, string $userAgent): array
    {
        $clean = basename($filename);
        $path = $this->getBackupFilePath($clean);
        if (!$path) {
            throw new \InvalidArgumentException('ไม่พบไฟล์สำรองที่ระบุ');
        }

        // Generate 6-digit cryptographic OTP
        $otp = (string) random_int(100000, 999999);
        $cacheKey = "restore_otp_{$clean}";

        // Store OTP in cache for 10 minutes (600 seconds)
        Cache::put($cacheKey, [
            'otp' => $otp,
            'creator_id' => $creator->id,
            'filename' => $clean,
            'ip' => $ip,
            'requested_at' => time(),
        ], 600);

        // Attempt sending email
        $mailSent = false;
        $errorMsg = null;
        try {
            Mail::to($creator->email)->send(
                new RestoreOtpMail($otp, $clean, $ip, $userAgent, $creator->name ?? 'The Creator')
            );
            $mailSent = true;
        } catch (\Throwable $e) {
            Log::warning("SystemBackupService: Failed to send Restore OTP email: {$e->getMessage()}");
            $errorMsg = $e->getMessage();
        }

        // Always log OTP in secure system log for audit trail & local development fallback
        Log::info("CRITICAL SECURITY: Restore OTP generated for {$clean} by Creator #{$creator->id}: [{$otp}] (Expires in 10 mins)");

        $this->logAction('restore_otp_requested', [
            'filename' => $clean,
            'creator_id' => $creator->id,
            'ip' => $ip,
            'mail_sent' => $mailSent,
        ]);

        return [
            'success' => true,
            'mail_sent' => $mailSent,
            'recipient' => $creator->email,
            'expires_in' => 600,
            'error' => $errorMsg,
            // Only expose OTP in debug/local if mailer is log
            'dev_otp' => (config('app.env') === 'local' && config('mail.default') === 'log') ? $otp : null,
        ];
    }

    /**
     * Verify Restore OTP
     */
    public function verifyRestoreOtp(string $filename, string $otp): bool
    {
        $clean = basename($filename);
        $cacheKey = "restore_otp_{$clean}";
        $data = Cache::get($cacheKey);

        if (!$data || !is_array($data)) {
            return false;
        }

        if (hash_equals((string) $data['otp'], trim($otp))) {
            Cache::forget($cacheKey);
            return true;
        }

        return false;
    }

    /**
     * Restore database from backup file
     * Always takes an automated safety snapshot before performing restore!
     */
    public function restoreBackup(string $filename): array
    {
        $clean = basename($filename);
        $path = $this->getBackupFilePath($clean);
        if (!$path) {
            throw new \InvalidArgumentException('ไม่พบไฟล์สำรองที่ต้องการกู้คืน');
        }

        // Step 1: ALWAYS take a pre-restore safety snapshot of the live database
        $safetySnapshot = $this->createDatabaseBackup('backup_pre_restore_safety_snapshot');

        $mysql = $this->getMysqlBinary();
        if (!$mysql) {
            throw new \RuntimeException('ไม่พบคำสั่ง mysql บนเซิร์ฟเวอร์ กรุณาระบุ MYSQL_PATH ใน .env');
        }

        $config = config('database.connections.mysql');
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $sqlContent = null;
        $tempSqlPath = null;

        if (str_ends_with($clean, '.sql.gz')) {
            $raw = file_get_contents($path);
            $sqlContent = @gzdecode($raw);
            if ($sqlContent === false) {
                throw new \RuntimeException('ไม่สามารถคลายการบีบอัดไฟล์ SQL.GZ ได้');
            }
            $tempSqlPath = $this->backupDir . DIRECTORY_SEPARATOR . 'temp_restore_' . time() . '.sql';
            file_put_contents($tempSqlPath, $sqlContent);
        } elseif (str_ends_with($clean, '.zip')) {
            // Full or files backup
            $zip = new ZipArchive();
            if ($zip->open($path) !== true) {
                throw new \RuntimeException('ไม่สามารถเปิดไฟล์ ZIP เพื่อกู้คืนได้');
            }

            // Check if there is a database dump inside the zip
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (str_starts_with($stat['name'], 'database/') && str_ends_with($stat['name'], '.sql.gz')) {
                    $gzData = $zip->getFromIndex($i);
                    $sqlContent = @gzdecode($gzData);
                    $tempSqlPath = $this->backupDir . DIRECTORY_SEPARATOR . 'temp_restore_' . time() . '.sql';
                    file_put_contents($tempSqlPath, $sqlContent);
                    break;
                }
            }

            // Also restore storage files
            $zip->extractTo(storage_path('app/public'), array_filter(
                array_map(fn ($idx) => $zip->getNameIndex($idx), range(0, $zip->numFiles - 1)),
                fn ($name) => str_starts_with($name, 'storage/')
            ));

            $zip->close();
        }

        // Execute MySQL restore if SQL content is present
        if ($tempSqlPath && file_exists($tempSqlPath)) {
            $cmd = [
                $mysql,
                "--host={$host}",
                "--port={$port}",
                "--user={$username}",
                '--default-character-set=utf8mb4',
            ];

            if (!empty($password)) {
                $cmd[] = "--password={$password}";
            }

            $cmd[] = $database;

            $process = new Process($cmd);
            $process->setTimeout(600); // 10 minutes timeout for restore

            $sqlStream = fopen($tempSqlPath, 'r');
            $process->setInput($sqlStream);
            $process->run();
            if (is_resource($sqlStream)) {
                fclose($sqlStream);
            }
            @unlink($tempSqlPath);

            if (!$process->isSuccessful()) {
                $err = $process->getErrorOutput() ?: $process->getOutput();
                throw new \RuntimeException("การกู้คืนฐานข้อมูลล้มเหลว: {$err}");
            }
        }

        $this->logAction('backup_restored', [
            'restored_file' => $clean,
            'safety_snapshot' => $safetySnapshot['filename'],
        ]);

        return [
            'success' => true,
            'restored_file' => $clean,
            'safety_snapshot' => $safetySnapshot['filename'],
            'restored_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get Database size in bytes
     */
    public function getDatabaseSize(): int
    {
        try {
            $database = config('database.connections.mysql.database');
            $res = DB::selectOne("
                SELECT SUM(data_length + index_length) AS size 
                FROM information_schema.TABLES 
                WHERE table_schema = ?
            ", [$database]);

            return (int) ($res->size ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Get Storage directory size in bytes
     */
    public function getStorageSize(): int
    {
        $dir = storage_path('app/public');
        if (!is_dir($dir)) return 0;

        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    /**
     * Find mysqldump binary
     */
    public function getMysqldumpBinary(): ?string
    {
        $envPath = env('MYSQLDUMP_PATH');
        if ($envPath && file_exists($envPath)) {
            return $envPath;
        }

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $testCmd = $isWindows ? 'where mysqldump 2>nul' : 'which mysqldump 2>/dev/null';
        $out = @shell_exec($testCmd);
        if (!empty(trim((string)$out))) {
            $lines = preg_split('/\r\n|\r|\n/', trim($out));
            if (!empty($lines[0]) && file_exists(trim($lines[0]))) {
                return trim($lines[0]);
            }
            return 'mysqldump';
        }

        if ($isWindows) {
            $globs = [
                'F:\laragon\bin\mysql\*\bin\mysqldump.exe',
                'C:\laragon\bin\mysql\*\bin\mysqldump.exe',
                'D:\laragon\bin\mysql\*\bin\mysqldump.exe',
                'C:\Program Files\MySQL\*\bin\mysqldump.exe',
                'C:\Program Files\MariaDB*\bin\mysqldump.exe',
            ];
            foreach ($globs as $pattern) {
                $matches = glob($pattern);
                if (!empty($matches)) {
                    return $matches[0];
                }
            }
        } else {
            $linux = ['/usr/bin/mysqldump', '/usr/local/bin/mysqldump', '/bin/mysqldump'];
            foreach ($linux as $c) {
                if (file_exists($c)) return $c;
            }
        }

        return null;
    }

    /**
     * Check if mysqldump supports --skip-column-statistics
     */
    protected function supportsSkipColumnStatistics(string $mysqldump): bool
    {
        try {
            $cmd = escapeshellarg($mysqldump) . ' --help 2>&1';
            $help = @shell_exec($cmd);
            return is_string($help) && str_contains($help, 'column-statistics');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Find mysql CLI binary
     */
    public function getMysqlBinary(): ?string
    {
        $envPath = env('MYSQL_PATH');
        if ($envPath && file_exists($envPath)) {
            return $envPath;
        }

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $testCmd = $isWindows ? 'where mysql 2>nul' : 'which mysql 2>/dev/null';
        $out = @shell_exec($testCmd);
        if (!empty(trim((string)$out))) {
            $lines = preg_split('/\r\n|\r|\n/', trim($out));
            if (!empty($lines[0]) && file_exists(trim($lines[0]))) {
                return trim($lines[0]);
            }
            return 'mysql';
        }

        if ($isWindows) {
            $globs = [
                'F:\laragon\bin\mysql\*\bin\mysql.exe',
                'C:\laragon\bin\mysql\*\bin\mysql.exe',
                'D:\laragon\bin\mysql\*\bin\mysql.exe',
                'C:\Program Files\MySQL\*\bin\mysql.exe',
                'C:\Program Files\MariaDB*\bin\mysql.exe',
            ];
            foreach ($globs as $pattern) {
                $matches = glob($pattern);
                if (!empty($matches)) {
                    return $matches[0];
                }
            }
        } else {
            $linux = ['/usr/bin/mysql', '/usr/local/bin/mysql', '/bin/mysql'];
            foreach ($linux as $c) {
                if (file_exists($c)) return $c;
            }
        }

        return null;
    }

    /**
     * Format bytes into human readable format
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Record action in AuditLog
     */
    protected function logAction(string $action, array $data = []): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id() ?? 0,
                'action' => $action,
                'model_type' => 'SystemBackup',
                'model_id' => 0,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'CLI/System',
                'details' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable $e) {
            Log::info("SystemBackupService audit log: {$action}", $data);
        }
    }
}
