<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\FileUploadService;

class MigrateProjectFilesDatabase extends Command
{
    protected $signature = 'migrate:files-db {--dry-run} {--table=all}';
    protected $description = 'Update database columns based on old filenames (ไม่ต้องไฟล์จริง - อ้างอิงชื่อเก่า)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $table = $this->option('table');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - ไม่มีการเปลี่ยนแปลงฐานข้อมูล\n');
        }

        $this->info('เริ่มต้น Update ฐานข้อมูลจากชื่อไฟล์เก่า...\n');

        if ($table === 'all' || $table === 'rdb_project') {
            $this->migrateRdbProject($dryRun);
        }

        if ($table === 'all' || $table === 'rdb_project_files') {
            $this->migrateRdbProjectFiles($dryRun);
        }

        if ($table === 'all' || $table === 'rdb_published') {
            $this->migrateRdbPublished($dryRun);
        }

        if ($table === 'all' || $table === 'rdb_researcher') {
            $this->migrateRdbResearcher($dryRun);
        }

        $this->newLine();
        $this->info('✅ เสร็จสิ้น!');
    }

    /**
     * Migrate rdb_project table
     */
    private function migrateRdbProject(bool $dryRun): void
    {
        $this->info('📊 Migrating rdb_project...');

        $count = 0;

        // Migrate abstract files
        $abstractRecords = DB::table('rdb_project')
            ->whereNotNull('pro_abstract_file')
            ->where('pro_abstract_file', '!=', '')
            ->whereNull('pro_abstract_file_path')
            ->get();

        foreach ($abstractRecords as $record) {
            try {
                $mockData = FileUploadService::generateMockFileData($record->pro_abstract_file);
                $newPath = 'abstracts/' . $mockData['new_filename'];

                if (!$dryRun) {
                    DB::table('rdb_project')
                        ->where('pro_id', $record->pro_id)
                        ->update([
                            'pro_abstract_file_path' => $newPath,
                            'pro_abstract_size' => $mockData['size'],
                            'pro_abstract_mime' => $mockData['mime_type'],
                        ]);
                    $this->line("  ✓ Abstract: pro_id {$record->pro_id} → {$newPath}");
                } else {
                    $this->line("  [DRY] Abstract: pro_id {$record->pro_id} → {$newPath}");
                }
                $count++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error: {$e->getMessage()}");
            }
        }

        // Migrate full report files
        $reportRecords = DB::table('rdb_project')
            ->whereNotNull('pro_file')
            ->where('pro_file', '!=', '')
            ->whereNull('pro_file_path')
            ->get();

        foreach ($reportRecords as $record) {
            try {
                $mockData = FileUploadService::generateMockFileData($record->pro_file);
                $newPath = 'reports/' . $mockData['new_filename'];

                if (!$dryRun) {
                    DB::table('rdb_project')
                        ->where('pro_id', $record->pro_id)
                        ->update([
                            'pro_file_path' => $newPath,
                            'pro_file_size' => $mockData['size'],
                            'pro_file_mime' => $mockData['mime_type'],
                        ]);
                    $this->line("  ✓ Report: pro_id {$record->pro_id} → {$newPath}");
                } else {
                    $this->line("  [DRY] Report: pro_id {$record->pro_id} → {$newPath}");
                }
                $count++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error: {$e->getMessage()}");
            }
        }

        $this->info("  เสร็จ: {$count} บันทึก update\n");
    }

    /**
     * Migrate rdb_project_files table
     */
    private function migrateRdbProjectFiles(bool $dryRun): void
    {
        $this->info('📎 Migrating rdb_project_files...');

        $records = DB::table('rdb_project_files')
            ->whereNotNull('rf_files')
            ->where('rf_files', '!=', '')
            ->whereNull('prf_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $mockData = FileUploadService::generateMockFileData($record->rf_files);
                $newPath = 'project_files/' . $mockData['new_filename'];

                if (!$dryRun) {
                    DB::table('rdb_project_files')
                        ->where('id', $record->id)
                        ->update([
                            'prf_safe_name' => $mockData['new_filename'],
                            'prf_path' => $newPath,
                            'prf_size' => $mockData['size'],
                            'prf_mime_type' => $mockData['mime_type'],
                        ]);
                    $this->line("  ✓ ID {$record->id}: {$record->rf_files} → {$newPath}");
                } else {
                    $this->line("  [DRY] ID {$record->id}: {$record->rf_files} → {$newPath}");
                }
                $count++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error: {$e->getMessage()}");
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ update\n");
    }

    /**
     * Migrate rdb_published table
     */
    private function migrateRdbPublished(bool $dryRun): void
    {
        $this->info('📄 Migrating rdb_published...');

        $records = DB::table('rdb_published')
            ->whereNotNull('pub_file')
            ->where('pub_file', '!=', '')
            ->whereNull('pf_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $mockData = FileUploadService::generateMockFileData($record->pub_file);
                $newPath = 'published/' . $mockData['new_filename'];

                if (!$dryRun) {
                    DB::table('rdb_published')
                        ->where('id', $record->id)
                        ->update([
                            'pf_path' => $newPath,
                            'pf_size' => $mockData['size'],
                            'pf_mime_type' => $mockData['mime_type'],
                        ]);
                    $this->line("  ✓ ID {$record->id}: {$newPath}");
                } else {
                    $this->line("  [DRY] ID {$record->id}: {$newPath}");
                }
                $count++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error: {$e->getMessage()}");
            }
        }

        $this->info("  เสร็จ: {$count} บันทึก update\n");
    }

    /**
     * Migrate rdb_researcher table
     */
    private function migrateRdbResearcher(bool $dryRun): void
    {
        $this->info('👤 Migrating rdb_researcher...');

        $records = DB::table('rdb_researcher')
            ->whereNotNull('researcher_picture')
            ->where('researcher_picture', '!=', '')
            ->whereNull('rf_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $mockData = FileUploadService::generateMockFileData($record->researcher_picture);
                $newPath = 'researchers/' . $mockData['new_filename'];

                if (!$dryRun) {
                    DB::table('rdb_researcher')
                        ->where('researcher_id', $record->researcher_id)
                        ->update([
                            'rf_path' => $newPath,
                            'rf_size' => $mockData['size'],
                            'rf_mime_type' => $mockData['mime_type'],
                        ]);
                    $this->line("  ✓ ID {$record->researcher_id}: {$newPath}");
                } else {
                    $this->line("  [DRY] ID {$record->researcher_id}: {$newPath}");
                }
                $count++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error: {$e->getMessage()}");
            }
        }

        $this->info("  เสร็จ: {$count} บันทึก update\n");
    }
}
