<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\FileUploadService;

class MigrateProjectFiles extends Command
{
    protected $signature = 'migrate:project-files {--dry-run}';
    protected $description = 'Migrate project files from old format to Modern Mode (UUID)';

    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        parent::__construct();
        $this->fileUploadService = $fileUploadService;
    }

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting Project Files Migration...');
        $this->newLine();

        // Migrate rdb_project abstract files
        $this->migrateProjectAbstractFiles($dryRun);

        // Migrate rdb_project report files
        $this->migrateProjectReportFiles($dryRun);

        // Migrate rdb_project_files
        $this->migrateProjectFileRecords($dryRun);

        $this->newLine();
        $this->info('✅ Migration completed successfully!');
    }

    /**
     * Migrate abstract files from rdb_project
     */
    private function migrateProjectAbstractFiles(bool $dryRun): void
    {
        $this->info('📄 Migrating Abstract Files...');

        $projects = DB::table('rdb_project')
            ->whereNotNull('pro_abstract_file')
            ->where('pro_abstract_file', '!=', '')
            ->where(function($q) {
                $q->whereNull('pro_abstract_file_path')
                  ->orWhere('pro_abstract_file_path', '');
            })
            ->get();

        $count = 0;
        $failed = 0;

        foreach ($projects as $project) {
            try {
                $oldFileName = $project->pro_abstract_file;
                $oldFilePath = 'uploads/projects/' . $oldFileName;

                // ตรวจสอบไฟล์มีอยู่จริง
                if (!Storage::disk('public')->exists($oldFilePath)) {
                    $this->warn("  ⚠️  File not found: {$oldFilePath}");
                    $failed++;
                    continue;
                }

                // สร้าง UUID ใหม่
                $ext = pathinfo($oldFileName, PATHINFO_EXTENSION);
                $newFileName = Str::uuid() . '.' . $ext;
                $newPath = 'abstracts/' . $newFileName;

                if (!$dryRun) {
                    // Copy ไฟล์เก่าไปชื่อใหม่
                    Storage::disk('local')->copy($oldFilePath, $newPath);

                    // Update database
                    DB::table('rdb_project')
                        ->where('pro_id', $project->pro_id)
                        ->update([
                            'pro_abstract_file_path' => $newPath,
                            'pro_abstract_size' => Storage::disk('local')->size($newPath),
                            'pro_abstract_mime' => $this->getMimeType($newPath),
                        ]);

                    $this->line("  ✓ {$project->pro_id}: {$oldFileName} → {$newFileName}");
                } else {
                    $this->line("  [DRY] {$project->pro_id}: {$oldFileName} → {$newFileName}");
                }

                $count++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error migrating {$project->pro_id}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("  Completed: {$count} files migrated, {$failed} failed");
        $this->newLine();
    }

    /**
     * Migrate report files from rdb_project
     */
    private function migrateProjectReportFiles(bool $dryRun): void
    {
        $this->info('📑 Migrating Report Files...');

        $projects = DB::table('rdb_project')
            ->whereNotNull('pro_file')
            ->where('pro_file', '!=', '')
            ->where(function($q) {
                $q->whereNull('pro_file_path')
                  ->orWhere('pro_file_path', '');
            })
            ->get();

        $count = 0;
        $failed = 0;

        foreach ($projects as $project) {
            try {
                $oldFileName = $project->pro_file;
                $oldFilePath = 'uploads/projects/' . $oldFileName;

                // ตรวจสอบไฟล์มีอยู่จริง
                if (!Storage::disk('public')->exists($oldFilePath)) {
                    $this->warn("  ⚠️  File not found: {$oldFilePath}");
                    $failed++;
                    continue;
                }

                // สร้าง UUID ใหม่
                $ext = pathinfo($oldFileName, PATHINFO_EXTENSION);
                $newFileName = Str::uuid() . '.' . $ext;
                $newPath = 'reports/' . $newFileName;

                if (!$dryRun) {
                    // Copy ไฟล์เก่าไปชื่อใหม่
                    Storage::disk('local')->copy($oldFilePath, $newPath);

                    // Update database
                    DB::table('rdb_project')
                        ->where('pro_id', $project->pro_id)
                        ->update([
                            'pro_file_path' => $newPath,
                            'pro_file_size' => Storage::disk('local')->size($newPath),
                            'pro_file_mime' => $this->getMimeType($newPath),
                        ]);

                    $this->line("  ✓ {$project->pro_id}: {$oldFileName} → {$newFileName}");
                } else {
                    $this->line("  [DRY] {$project->pro_id}: {$oldFileName} → {$newFileName}");
                }

                $count++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error migrating {$project->pro_id}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("  Completed: {$count} files migrated, {$failed} failed");
        $this->newLine();
    }

    /**
     * Migrate files from rdb_project_files
     */
    private function migrateProjectFileRecords(bool $dryRun): void
    {
        $this->info('📎 Migrating Project File Records...');

        $files = DB::table('rdb_project_files')
            ->whereNotNull('rf_files')
            ->where('rf_files', '!=', '')
            ->where(function($q) {
                $q->whereNull('prf_path')
                  ->orWhere('prf_path', '');
            })
            ->get();

        $count = 0;
        $failed = 0;

        foreach ($files as $file) {
            try {
                $oldFileName = $file->rf_files;
                $oldFilePath = 'uploads/project_files/' . $oldFileName;

                // ตรวจสอบไฟล์มีอยู่จริง
                if (!Storage::disk('public')->exists($oldFilePath)) {
                    $this->warn("  ⚠️  File not found: {$oldFilePath}");
                    $failed++;
                    continue;
                }

                // สร้าง UUID ใหม่
                $ext = pathinfo($oldFileName, PATHINFO_EXTENSION);
                $newFileName = Str::uuid() . '.' . $ext;
                $newPath = 'project_files/' . $newFileName;

                if (!$dryRun) {
                    // Copy ไฟล์เก่าไปชื่อใหม่
                    Storage::disk('local')->copy($oldFilePath, $newPath);

                    // Update database
                    DB::table('rdb_project_files')
                        ->where('id', $file->id)
                        ->update([
                            'prf_safe_name' => $newFileName,
                            'prf_path' => $newPath,
                            'prf_size' => Storage::disk('local')->size($newPath),
                            'prf_mime_type' => $this->getMimeType($newPath),
                        ]);

                    $this->line("  ✓ ID {$file->id}: {$oldFileName} → {$newFileName}");
                } else {
                    $this->line("  [DRY] ID {$file->id}: {$oldFileName} → {$newFileName}");
                }

                $count++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error migrating ID {$file->id}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("  Completed: {$count} files migrated, {$failed} failed");
        $this->newLine();
    }

    /**
     * Get MIME type from file path
     */
    private function getMimeType(string $filePath): string
    {
        try {
            $realPath = Storage::disk('local')->path($filePath);
            return mime_content_type($realPath) ?: 'application/octet-stream';
        } catch (\Exception $e) {
            return 'application/octet-stream';
        }
    }
}
