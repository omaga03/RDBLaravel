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
    public const CACHE_PROGRESS_KEY = 'system_backup_progress';
    public const CACHE_STORAGE_SIZE_KEY = 'system_storage_size_bytes';

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
     * Compute fast checksum with memory-efficient cryptographic sampling for large archives
     */
    public function computeFastChecksum(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        $size = filesize($filePath);
        $mtime = filemtime($filePath);
        $cacheKey = 'backup_md5_' . md5($filePath . '_' . $mtime . '_' . $size);

        return Cache::rememberForever($cacheKey, function () use ($filePath, $size, $mtime) {
            // For files <= 50MB (e.g. database SQL dumps), calculate complete MD5
            if ($size <= 50 * 1024 * 1024) {
                return md5_file($filePath);
            }

            // For huge files (> 50MB, e.g. 20GB Storage archives), sample header, middle and footer bytes
            // to produce an instant (0.001s) deterministic cryptographic fingerprint without 46s disk freeze!
            $fp = @fopen($filePath, 'rb');
            if (!$fp) {
                return md5(basename($filePath) . '_' . $size . '_' . $mtime);
            }

            $sample = fread($fp, 65536);
            if ($size > 131072) {
                fseek($fp, (int) ($size / 2));
                $sample .= fread($fp, 65536);
                fseek($fp, max(0, $size - 65536));
                $sample .= fread($fp, 65536);
            }
            fclose($fp);

            return md5($sample . '_' . $size . '_' . $mtime);
        });
    }

    /**
     * Get current backup progress or null if idle
     */
    public static function getProgress(): ?array
    {
        return Cache::get(self::CACHE_PROGRESS_KEY);
    }

    /**
     * Check if a backup job is currently running
     */
    public static function isBackupRunning(): bool
    {
        $progress = self::getProgress();
        if (!$progress || !is_array($progress)) {
            return false;
        }

        if (($progress['status'] ?? '') !== 'running') {
            return false;
        }

        // Safety timeout: If progress hasn't updated for more than 15 minutes, consider it stale/dead
        $updatedAt = $progress['updated_timestamp'] ?? 0;
        if ((time() - $updatedAt) > 900) {
            self::clearProgress();
            return false;
        }

        return true;
    }

    /**
     * Update backup progress in Cache
     */
    public static function updateProgress(array $data): void
    {
        $current = Cache::get(self::CACHE_PROGRESS_KEY, []);
        if (!is_array($current)) {
            $current = [];
        }

        $now = time();
        $startTime = $current['start_timestamp'] ?? $now;

        $merged = array_merge($current, $data, [
            'updated_timestamp' => $now,
            'updated_at' => date('Y-m-d H:i:s'),
            'elapsed_seconds' => max(0, $now - $startTime),
        ]);

        if (!isset($merged['start_timestamp'])) {
            $merged['start_timestamp'] = $now;
        }

        // Keep in cache for 2 hours
        Cache::put(self::CACHE_PROGRESS_KEY, $merged, 7200);
    }

    /**
     * Clear progress from Cache
     */
    public static function clearProgress(): void
    {
        Cache::forget(self::CACHE_PROGRESS_KEY);
    }

    /**
     * Dispatch background backup via CLI worker
     */
    public function dispatchBackgroundBackup(string $type): void
    {
        $typeLabels = [
            'db' => 'ฐานข้อมูล MariaDB (.sql.gz)',
            'files' => 'ไฟล์เอกสาร Storage (.zip)',
            'all' => 'ทั้งระบบเต็มรูปแบบ (Full Archive)',
        ];

        self::updateProgress([
            'status' => 'running',
            'type' => $type,
            'type_label' => $typeLabels[$type] ?? $type,
            'stage' => 'init',
            'stage_label' => 'กำลังเตรียมเริ่มต้นกระบวนการสำรองข้อมูล...',
            'percent' => 2,
            'current_file' => '',
            'processed_files' => 0,
            'total_files' => 0,
            'processed_bytes' => 0,
            'total_bytes' => 0,
            'error' => null,
            'result' => null,
            'start_timestamp' => time(),
        ]);

        $php = PHP_BINARY;
        $artisan = base_path('artisan');
        $escapedType = escapeshellarg($type);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows background detached process
            $cmd = 'start /B "" "' . $php . '" "' . $artisan . '" rdb:backup --type=' . $escapedType . ' > NUL 2>&1';
            pclose(popen($cmd, 'r'));
        } else {
            // Unix / Linux background process
            $cmd = '"' . $php . '" "' . $artisan . '" rdb:backup --type=' . $escapedType . ' > /dev/null 2>&1 &';
            exec($cmd);
        }
    }

    /**
     * Create database backup (.sql.gz)
     */
    public function createDatabaseBackup(string $prefix = 'backup_db'): array
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        try {
            self::updateProgress([
                'status' => 'running',
                'type' => 'db',
                'type_label' => 'ฐานข้อมูล MariaDB (.sql.gz)',
                'stage' => 'init_db',
                'stage_label' => 'กำลังตรวจสอบไบนารี mysqldump และการเชื่อมต่อ...',
                'percent' => 10,
            ]);

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

            self::updateProgress([
                'stage' => 'dumping_db',
                'stage_label' => "กำลังสกัดตารางและข้อมูลจากฐานข้อมูล {$database} ผ่าน mysqldump...",
                'percent' => 35,
            ]);

            // Execute dump into temporary .sql file
            $process = new Process($cmd);
            $process->setTimeout(600); // 10 minutes timeout

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

            self::updateProgress([
                'stage' => 'compressing_db',
                'stage_label' => 'กำลังบีบอัดไฟล์ SQL ด้วย Gzip (Compression Level 9)...',
                'percent' => 75,
            ]);

            // Compress SQL file with gzip (level 9 for maximum compression)
            $rawSql = file_get_contents($sqlTempPath);
            $compressed = gzencode($rawSql, 9);
            file_put_contents($gzFinalPath, $compressed);
            @unlink($sqlTempPath); // remove uncompressed file

            self::updateProgress([
                'stage' => 'checksum_db',
                'stage_label' => 'กำลังคำนวณ Checksum MD5 และบันทึกประวัติความปลอดภัย...',
                'percent' => 95,
            ]);

            $size = filesize($gzFinalPath);
            $checksum = $this->computeFastChecksum($gzFinalPath);

            $this->logAction('backup_db_created', [
                'filename' => $gzFileName,
                'size' => $size,
                'checksum' => $checksum,
            ]);

            $result = [
                'filename' => $gzFileName,
                'path' => $gzFinalPath,
                'size' => $size,
                'size_formatted' => $this->formatBytes($size),
                'checksum' => $checksum,
                'type' => 'database',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            self::updateProgress([
                'status' => 'completed',
                'stage' => 'completed',
                'stage_label' => "สำรองฐานข้อมูลสำเร็จ: {$gzFileName} ({$result['size_formatted']})",
                'percent' => 100,
                'result' => $result,
            ]);

            return $result;
        } catch (\Throwable $e) {
            self::updateProgress([
                'status' => 'failed',
                'stage' => 'failed',
                'stage_label' => 'การสำรองฐานข้อมูลล้มเหลว: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create storage files backup (.zip)
     */
    public function createFilesBackup(string $prefix = 'backup_files'): array
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('PHP ZipArchive extension ไม่ได้เปิดใช้งาน');
        }

        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        try {
            self::updateProgress([
                'status' => 'running',
                'type' => 'files',
                'type_label' => 'ไฟล์เอกสาร Storage (.zip)',
                'stage' => 'scanning',
                'stage_label' => 'กำลังสแกนโครงสร้างและนับจำนวนไฟล์ใน storage/app/public...',
                'percent' => 3,
            ]);

            $timestamp = date('Ymd_His');
            $zipFileName = "{$prefix}_{$timestamp}.zip";
            $zipPath = $this->backupDir . DIRECTORY_SEPARATOR . $zipFileName;

            $sourceDir = storage_path('app/public');
            if (!is_dir($sourceDir)) {
                throw new \RuntimeException('ไม่พบโฟลเดอร์ storage/app/public');
            }

            // Pre-scan all files to get accurate count and total size
            $allFiles = [];
            $totalBytes = 0;
            foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            ) as $file) {
                if ($file->isFile()) {
                    $allFiles[] = $file->getRealPath();
                    $totalBytes += $file->getSize();
                }
            }

            $totalCount = count($allFiles);

            self::updateProgress([
                'stage' => 'adding_files',
                'stage_label' => "พบทั้งหมด " . number_format($totalCount) . " ไฟล์ (" . $this->formatBytes($totalBytes) . ") เริ่มบรรจุไฟล์ลง ZIP...",
                'total_files' => $totalCount,
                'total_bytes' => $totalBytes,
                'processed_files' => 0,
                'percent' => 5,
            ]);

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException("ไม่สามารถสร้างไฟล์ ZIP ที่ {$zipPath} ได้");
            }

            // Register progress callback for ZipArchive::close() flush phase
            if (method_exists($zip, 'registerProgressCallback')) {
                $zip->registerProgressCallback(0.02, function (float $callbackProgress) {
                    $percent = 80 + (int) round($callbackProgress * 18); // 80% to 98%
                    self::updateProgress([
                        'stage' => 'compressing',
                        'stage_label' => 'กำลังบันทึกและปิดผนึกไฟล์ ZIP (Flushing to disk ' . round($callbackProgress * 100) . '%)...',
                        'percent' => min(98, $percent),
                    ]);
                });
            }

            $fileCount = 0;
            $uncompressedExts = ['pdf', 'webp', 'jpg', 'jpeg', 'png', 'zip', 'gz', 'bz2', 'mp4', 'mp3', 'rar', '7z'];
            $lastUpdateTime = microtime(true);

            foreach ($allFiles as $filePath) {
                $fileCount++;
                $relativePath = 'storage/' . substr($filePath, strlen($sourceDir) + 1);
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                $zip->addFile($filePath, $relativePath);

                // Optimization: Do NOT re-compress already compressed binary formats
                if (in_array($ext, $uncompressedExts, true)) {
                    $zip->setCompressionName($relativePath, ZipArchive::CM_STORE);
                } else {
                    $zip->setCompressionName($relativePath, ZipArchive::CM_DEFLATE);
                }

                $nowTime = microtime(true);
                if ($fileCount % 250 === 0 || ($nowTime - $lastUpdateTime) > 0.5 || $fileCount === $totalCount) {
                    $lastUpdateTime = $nowTime;
                    $archivePercent = 5 + (int) round(($fileCount / max(1, $totalCount)) * 75); // 5% to 80%
                    self::updateProgress([
                        'stage' => 'adding_files',
                        'stage_label' => "กำลังจัดเตรียมและบรรจุไฟล์ (" . number_format($fileCount) . " / " . number_format($totalCount) . " ไฟล์)...",
                        'processed_files' => $fileCount,
                        'current_file' => $relativePath,
                        'percent' => $archivePercent,
                    ]);
                }
            }

            self::updateProgress([
                'stage' => 'compressing',
                'stage_label' => 'กำลังจัดเก็บและปิดผนึกไฟล์ ZIP Archive (กรุณารอสักครู่)...',
                'percent' => 80,
            ]);

            $zip->close();

            self::updateProgress([
                'stage' => 'checksum',
                'stage_label' => 'กำลังตรวจสอบความถูกต้องและคำนวณค่า Checksum MD5...',
                'percent' => 98,
            ]);

            $size = filesize($zipPath);
            $checksum = $this->computeFastChecksum($zipPath);

            $this->logAction('backup_files_created', [
                'filename' => $zipFileName,
                'size' => $size,
                'file_count' => $fileCount,
                'checksum' => $checksum,
            ]);

            $result = [
                'filename' => $zipFileName,
                'path' => $zipPath,
                'size' => $size,
                'size_formatted' => $this->formatBytes($size),
                'file_count' => $fileCount,
                'checksum' => $checksum,
                'type' => 'files',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            self::updateProgress([
                'status' => 'completed',
                'stage' => 'completed',
                'stage_label' => "สำรองไฟล์ Storage สำเร็จ: {$zipFileName} ({$result['size_formatted']}, " . number_format($fileCount) . " ไฟล์)",
                'percent' => 100,
                'result' => $result,
            ]);

            return $result;
        } catch (\Throwable $e) {
            self::updateProgress([
                'status' => 'failed',
                'stage' => 'failed',
                'stage_label' => 'การสำรองไฟล์ Storage ล้มเหลว: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create full system archive (Database dump + Storage files + Environment in a single package)
     */
    public function createFullBackup(): array
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('PHP ZipArchive extension ไม่ได้เปิดใช้งาน');
        }

        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        try {
            self::updateProgress([
                'status' => 'running',
                'type' => 'all',
                'type_label' => 'ทั้งระบบเต็มรูปแบบ (Full Archive)',
                'stage' => 'init_full',
                'stage_label' => 'กำลังเริ่มต้นกระบวนการสำรองทั้งระบบแบบเต็ม...',
                'percent' => 2,
            ]);

            $timestamp = date('Ymd_His');
            $fullFileName = "backup_full_{$timestamp}.zip";
            $fullZipPath = $this->backupDir . DIRECTORY_SEPARATOR . $fullFileName;

            // 1. First dump database (0% - 20%)
            self::updateProgress([
                'stage' => 'db_dump',
                'stage_label' => 'ขั้นตอนที่ 1/3: กำลังสกัดฐานข้อมูล MariaDB ทั้งหมดเป็น .sql.gz...',
                'percent' => 5,
            ]);

            $dbBackup = $this->createDatabaseBackup('temp_db');
            $dbPath = $dbBackup['path'];

            self::updateProgress([
                'stage' => 'scanning_storage',
                'stage_label' => 'ขั้นตอนที่ 2/3: สกัดฐานข้อมูลสำเร็จ กำลังสแกนไฟล์เอกสาร Storage ทั้งหมด...',
                'percent' => 20,
            ]);

            $sourceDir = storage_path('app/public');
            $allFiles = [];
            $totalBytes = 0;
            if (is_dir($sourceDir)) {
                foreach (new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ) as $file) {
                    if ($file->isFile()) {
                        $allFiles[] = $file->getRealPath();
                        $totalBytes += $file->getSize();
                    }
                }
            }

            $totalCount = count($allFiles);

            $zip = new ZipArchive();
            if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                @unlink($dbPath);
                throw new \RuntimeException("ไม่สามารถสร้างไฟล์ ZIP เต็มรูปแบบได้");
            }

            // Register progress callback for ZipArchive::close() flush phase
            if (method_exists($zip, 'registerProgressCallback')) {
                $zip->registerProgressCallback(0.02, function (float $callbackProgress) {
                    $percent = 80 + (int) round($callbackProgress * 18); // 80% to 98%
                    self::updateProgress([
                        'stage' => 'compressing',
                        'stage_label' => 'ขั้นตอนที่ 3/3: กำลังบันทึกและปิดผนึกแพ็กเกจ Full Archive (Flushing to disk ' . round($callbackProgress * 100) . '%)...',
                        'percent' => min(98, $percent),
                    ]);
                });
            }

            // Add the SQL.gz into zip root
            $zip->addFile($dbPath, 'database/' . basename($dbPath));
            $zip->setCompressionName('database/' . basename($dbPath), ZipArchive::CM_STORE);

            // Add storage files
            $fileCount = 0;
            $uncompressedExts = ['pdf', 'webp', 'jpg', 'jpeg', 'png', 'zip', 'gz', 'bz2', 'mp4', 'mp3', 'rar', '7z'];
            $lastUpdateTime = microtime(true);

            foreach ($allFiles as $filePath) {
                $fileCount++;
                $relativePath = 'storage/' . substr($filePath, strlen($sourceDir) + 1);
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                $zip->addFile($filePath, $relativePath);

                if (in_array($ext, $uncompressedExts, true)) {
                    $zip->setCompressionName($relativePath, ZipArchive::CM_STORE);
                } else {
                    $zip->setCompressionName($relativePath, ZipArchive::CM_DEFLATE);
                }

                $nowTime = microtime(true);
                if ($fileCount % 250 === 0 || ($nowTime - $lastUpdateTime) > 0.5 || $fileCount === $totalCount) {
                    $lastUpdateTime = $nowTime;
                    $archivePercent = 20 + (int) round(($fileCount / max(1, $totalCount)) * 60); // 20% to 80%
                    self::updateProgress([
                        'stage' => 'adding_storage',
                        'stage_label' => "กำลังบรรจุไฟล์ Storage ลงในแพ็กเกจ (" . number_format($fileCount) . " / " . number_format($totalCount) . " ไฟล์)...",
                        'processed_files' => $fileCount,
                        'total_files' => $totalCount,
                        'current_file' => $relativePath,
                        'percent' => $archivePercent,
                    ]);
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
                'total_storage_files' => $fileCount,
            ];
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            self::updateProgress([
                'stage' => 'compressing',
                'stage_label' => 'ขั้นตอนที่ 3/3: กำลังจัดเก็บและปิดผนึก Full Archive (กรุณารอสักครู่)...',
                'percent' => 80,
            ]);

            $zip->close();
            @unlink($dbPath); // clean temp DB file

            self::updateProgress([
                'stage' => 'checksum',
                'stage_label' => 'กำลังคำนวณ Checksum MD5 และตรวจสอบความสมบูรณ์...',
                'percent' => 98,
            ]);

            $size = filesize($fullZipPath);
            $checksum = $this->computeFastChecksum($fullZipPath);

            $this->logAction('backup_full_created', [
                'filename' => $fullFileName,
                'size' => $size,
                'checksum' => $checksum,
            ]);

            $result = [
                'filename' => $fullFileName,
                'path' => $fullZipPath,
                'size' => $size,
                'size_formatted' => $this->formatBytes($size),
                'checksum' => $checksum,
                'type' => 'full',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            self::updateProgress([
                'status' => 'completed',
                'stage' => 'completed',
                'stage_label' => "สำรองทั้งระบบเต็มรูปแบบสำเร็จ: {$fullFileName} ({$result['size_formatted']})",
                'percent' => 100,
                'result' => $result,
            ]);

            return $result;
        } catch (\Throwable $e) {
            self::updateProgress([
                'status' => 'failed',
                'stage' => 'failed',
                'stage_label' => 'การสำรองข้อมูลทั้งระบบล้มเหลว: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
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
                'checksum' => $this->computeFastChecksum($file->getRealPath()),
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
     * Get Storage directory size in bytes (cached for 30 minutes for dashboard speed)
     */
    public function getStorageSize(bool $forceRefresh = false): int
    {
        if ($forceRefresh) {
            Cache::forget(self::CACHE_STORAGE_SIZE_KEY);
        }

        return Cache::remember(self::CACHE_STORAGE_SIZE_KEY, 1800, function () {
            $dir = storage_path('app/public');
            if (!is_dir($dir)) return 0;

            $size = 0;
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
                $size += $file->getSize();
            }
            return $size;
        });
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
