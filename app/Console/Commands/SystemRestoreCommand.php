<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SystemBackupService;

class SystemRestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdb:restore 
                            {file : Backup archive filename (e.g. backup_db_20261103_220000.sql.gz)}
                            {--force : Force restore without interactive confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database and storage files from a verified backup archive (The Creator / SysAdmin only)';

    /**
     * Execute the console command.
     */
    public function handle(SystemBackupService $backupService): int
    {
        $filename = $this->argument('file');
        $force = (bool) $this->option('force');

        $this->warn("=================================================");
        $this->warn("  CRITICAL SYSTEM RESTORE — PCRU Research Database");
        $this->warn("=================================================");
        $this->warn("TARGET FILE: {$filename}");

        $path = $backupService->getBackupFilePath($filename);
        if (!$path) {
            $this->error("Error: Backup archive '{$filename}' not found in " . $backupService->getBackupDir());
            return Command::FAILURE;
        }

        if (!$force) {
            $this->warn("WARNING: Restoring will overwrite existing database records and storage files!");
            $this->warn("A safety snapshot will be created automatically before restore starts.");
            if (!$this->confirm("Are you ABSOLUTELY SURE you want to continue?", false)) {
                $this->info("Restore operation aborted by user.");
                return Command::SUCCESS;
            }
        }

        try {
            $this->comment("Step 1: Creating automated pre-restore safety snapshot...");
            $res = $backupService->restoreBackup($filename);

            $this->info("✓ Restore operation completed successfully!");
            $this->line("  Restored Archive: " . $res['restored_file']);
            $this->line("  Safety Snapshot:  " . $res['safety_snapshot']);
            $this->line("  Completed at:     " . $res['restored_at']);
            $this->info("-------------------------------------------------");
            $this->info("Cache cleared automatically. System is ready.");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Restore failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
