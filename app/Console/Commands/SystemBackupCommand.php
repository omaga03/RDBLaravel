<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SystemBackupService;

class SystemBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdb:backup 
                            {--type=all : Backup type: all (Full Archive), db (Database SQL.GZ), or files (Storage ZIP)}
                            {--clean-older-than-days=0 : Automatically purge backup files older than specified days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform automated system backup (Database, Storage files, or Full archive)';

    /**
     * Execute the console command.
     */
    public function handle(SystemBackupService $backupService): int
    {
        $type = strtolower($this->option('type') ?: 'all');
        $cleanDays = (int) $this->option('clean-older-than-days');

        $this->info("=================================================");
        $this->info("   PCRU Research Database — System Backup Hub   ");
        $this->info("=================================================");
        $this->info("Mode: " . strtoupper($type) . " | Time: " . date('Y-m-d H:i:s'));

        try {
            if ($type === 'db') {
                $this->comment("Starting Database dump (.sql.gz)...");
                $res = $backupService->createDatabaseBackup();
                $this->info("✓ Database backup completed successfully!");
                $this->line("  File: " . $res['filename']);
                $this->line("  Size: " . $res['size_formatted']);
                $this->line("  Checksum (MD5): " . $res['checksum']);
            } elseif ($type === 'files') {
                $this->comment("Starting Storage files archive (.zip)...");
                $res = $backupService->createFilesBackup();
                $this->info("✓ Storage files backup completed successfully!");
                $this->line("  File: " . $res['filename']);
                $this->line("  Size: " . $res['size_formatted']);
                $this->line("  Files archived: " . number_format($res['file_count']));
                $this->line("  Checksum (MD5): " . $res['checksum']);
            } elseif ($type === 'all') {
                $this->comment("Starting Full System archive (Database + Storage + Config)...");
                $res = $backupService->createFullBackup();
                $this->info("✓ Full system backup completed successfully!");
                $this->line("  File: " . $res['filename']);
                $this->line("  Size: " . $res['size_formatted']);
                $this->line("  Checksum (MD5): " . $res['checksum']);
            } else {
                $this->error("Invalid backup type: {$type}. Use --type=all, --type=db, or --type=files");
                return Command::FAILURE;
            }

            if ($cleanDays > 0) {
                $this->comment("Cleaning backup archives older than {$cleanDays} days...");
                $deleted = $backupService->cleanOldBackups($cleanDays);
                $this->info("✓ Cleaned {$deleted} old backup archive(s).");
            }

            $this->info("-------------------------------------------------");
            $this->info("Backup destination: " . $backupService->getBackupDir());
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            SystemBackupService::updateProgress([
                'status' => 'failed',
                'stage' => 'failed',
                'stage_label' => 'การสำรองข้อมูลล้มเหลว: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ]);
            $this->error("Backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
