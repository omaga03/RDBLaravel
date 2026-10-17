<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RdbProject;
use App\Models\RdbProjectFiles;
use App\Models\RdbPublished;
use App\Models\RdbResearcher;
use App\Services\FileUploadService;

class CheckFilesIntegrity extends Command
{
    protected $signature = 'check:files-integrity';
    protected $description = 'ตรวจสอบไฟล์ที่หายไปในฐานข้อมูล';

    public function handle()
    {
        $this->info('🔍 ตรวจสอบความสมบูรณ์ของไฟล์...\n');

        $errors = 0;
        $warnings = 0;

        // ตรวจสอบ rdb_project
        $projects = RdbProject::all();
        foreach ($projects as $project) {
            $integrity = FileUploadService::checkFileIntegrity(
                $project->pro_abstract_file_path ?? '',
                $project->pro_abstract_file ?? '',
                'uploads/projects'
            );

            if ($integrity['status'] === 'error') {
                $this->error("❌ Project {$project->pro_id}: {$integrity['message']}");
                $errors++;
            } elseif ($integrity['status'] === 'warning') {
                $this->warn("⚠️  Project {$project->pro_id}: {$integrity['message']}");
                $warnings++;
            }
        }

        // ตรวจสอบ rdb_project_files
        $files = RdbProjectFiles::all();
        foreach ($files as $file) {
            $integrity = FileUploadService::checkFileIntegrity(
                $file->prf_path ?? '',
                $file->rf_files ?? '',
                'uploads/project_files'
            );

            if ($integrity['status'] === 'error') {
                $this->error("❌ File {$file->id}: {$integrity['message']}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info("สรุป: ❌ {$errors} ไฟล์หาย | ⚠️  {$warnings} ไฟล์ใช้ fallback");
    }
}
