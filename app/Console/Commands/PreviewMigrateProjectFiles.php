<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\FileUploadService;

class PreviewMigrateProjectFiles extends Command
{
    protected $signature = 'migrate:preview-files {--table=rdb_project} {--limit=5}';
    protected $description = 'Preview database changes before actual migration (ไม่มีการเปลี่ยนแปลงข้อมูล)';

    public function handle()
    {
        $table = $this->option('table');
        $limit = $this->option('limit');

        $this->info("🔍 Preview Migration: {$table} (แสดง {$limit} แถว)\n");

        match ($table) {
            'rdb_project' => $this->previewRdbProject($limit),
            'rdb_project_files' => $this->previewRdbProjectFiles($limit),
            'rdb_published' => $this->previewRdbPublished($limit),
            'rdb_researcher' => $this->previewRdbResearcher($limit),
            default => $this->error("ตาราที่ไม่รู้จัก: {$table}"),
        };

        $this->newLine();
        $this->info('✅ นี่คือ Preview เท่านั้น - ไม่มีการเปลี่ยนแปลงข้อมูลจริง');
    }

    /**
     * Preview rdb_project table changes
     */
    private function previewRdbProject(int $limit): void
    {
        $this->info('📊 ตาราก่อน Migration (rdb_project):\n');

        $records = DB::table('rdb_project')
            ->select('pro_id', 'pro_abstract_file', 'pro_file')
            ->limit($limit)
            ->get();

        // แสดงตารา BEFORE
        $this->table(
            ['pro_id', 'pro_abstract_file', 'pro_file', 'pro_abstract_file_path', 'pro_file_path'],
            $records->map(fn($r) => [
                $r->pro_id,
                $r->pro_abstract_file,
                $r->pro_file,
                'NULL',
                'NULL',
            ])->toArray()
        );

        $this->newLine(2);
        $this->info('📊 ตาราหลัง Migration (rdb_project):\n');

        // แสดงตาราง AFTER
        $afterData = $records->map(function($r) {
            $abstractPath = null;
            $abstractSize = null;
            $abstractMime = null;
            $filePath = null;
            $fileSize = null;
            $fileMime = null;

            // Generate UUID paths
            if ($r->pro_abstract_file) {
                $mockData = FileUploadService::generateMockFileData($r->pro_abstract_file);
                $abstractPath = 'abstracts/' . $mockData['new_filename'];
                $abstractSize = $mockData['size'];
                $abstractMime = $mockData['mime_type'];
            }

            if ($r->pro_file) {
                $mockData = FileUploadService::generateMockFileData($r->pro_file);
                $filePath = 'reports/' . $mockData['new_filename'];
                $fileSize = $mockData['size'];
                $fileMime = $mockData['mime_type'];
            }

            return [
                $r->pro_id,
                $r->pro_abstract_file,
                $r->pro_file,
                $abstractPath ?? '-',
                $abstractSize ?? '-',
                $abstractMime ?? '-',
                $filePath ?? '-',
                $fileSize ?? '-',
                $fileMime ?? '-',
            ];
        })->toArray();

        $this->table(
            [
                'pro_id',
                'pro_abstract_file',
                'pro_file',
                'pro_abstract_file_path',
                'pro_abstract_size',
                'pro_abstract_mime',
                'pro_file_path',
                'pro_file_size',
                'pro_file_mime',
            ],
            $afterData
        );
    }

    /**
     * Preview rdb_project_files table changes
     */
    private function previewRdbProjectFiles(int $limit): void
    {
        $this->info('📊 ตาราก่อน Migration (rdb_project_files):\n');

        $records = DB::table('rdb_project_files')
            ->select('id', 'pro_id', 'rf_filesname', 'rf_files')
            ->limit($limit)
            ->get();

        // แสดงตาราก่อน
        $this->table(
            ['id', 'pro_id', 'rf_filesname', 'rf_files', 'prf_safe_name', 'prf_path', 'prf_size', 'prf_mime_type'],
            $records->map(fn($r) => [
                $r->id,
                $r->pro_id,
                $r->rf_filesname,
                $r->rf_files,
                'NULL',
                'NULL',
                'NULL',
                'NULL',
            ])->toArray()
        );

        $this->newLine(2);
        $this->info('📊 ตาราหลัง Migration (rdb_project_files):\n');

        // แสดงตารางหลัง
        $afterData = $records->map(function($r) {
            $mockData = FileUploadService::generateMockFileData($r->rf_files);
            $newPath = 'project_files/' . $mockData['new_filename'];

            return [
                $r->id,
                $r->pro_id,
                $r->rf_filesname,
                $r->rf_files,
                $mockData['new_filename'],
                $newPath,
                $mockData['size'],
                $mockData['mime_type'],
            ];
        })->toArray();

        $this->table(
            ['id', 'pro_id', 'rf_filesname', 'rf_files', 'prf_safe_name', 'prf_path', 'prf_size', 'prf_mime_type'],
            $afterData
        );
    }

    /**
     * Preview rdb_published table changes
     */
    private function previewRdbPublished(int $limit): void
    {
        $this->info('📊 ตาราก่อน Migration (rdb_published):\n');

        $records = DB::table('rdb_published')
            ->select('id', 'pub_file')
            ->limit($limit)
            ->get();

        $this->table(
            ['id', 'pub_file', 'pf_path', 'pf_size', 'pf_mime_type'],
            $records->map(fn($r) => [
                $r->id,
                $r->pub_file,
                'NULL',
                'NULL',
                'NULL',
            ])->toArray()
        );

        $this->newLine(2);
        $this->info('📊 ตาราหลัง Migration (rdb_published):\n');

        $afterData = $records->map(function($r) {
            $mockData = FileUploadService::generateMockFileData($r->pub_file);
            $newPath = 'published/' . $mockData['new_filename'];

            return [
                $r->id,
                $r->pub_file,
                $newPath,
                $mockData['size'],
                $mockData['mime_type'],
            ];
        })->toArray();

        $this->table(
            ['id', 'pub_file', 'pf_path', 'pf_size', 'pf_mime_type'],
            $afterData
        );
    }

    /**
     * Preview rdb_researcher table changes
     */
    private function previewRdbResearcher(int $limit): void
    {
        $this->info('📊 ตาราก่อน Migration (rdb_researcher):\n');

        $records = DB::table('rdb_researcher')
            ->select('researcher_id', 'researcher_fname', 'researcher_lname', 'researcher_picture')
            ->limit($limit)
            ->get();

        $this->table(
            ['researcher_id', 'researcher_fname', 'researcher_picture', 'rf_path', 'rf_size', 'rf_mime_type'],
            $records->map(fn($r) => [
                $r->researcher_id,
                $r->researcher_fname,
                $r->researcher_picture ?? '-',
                'NULL',
                'NULL',
                'NULL',
            ])->toArray()
        );

        $this->newLine(2);
        $this->info('📊 ตาราหลัง Migration (rdb_researcher):\n');

        $afterData = $records->map(function($r) {
            if (!$r->researcher_picture) {
                return [
                    $r->researcher_id,
                    $r->researcher_fname,
                    '-',
                    '-',
                    '-',
                    '-',
                ];
            }

            $mockData = FileUploadService::generateMockFileData($r->researcher_picture);
            $newPath = 'researchers/' . $mockData['new_filename'];

            return [
                $r->researcher_id,
                $r->researcher_fname,
                $r->researcher_picture,
                $newPath,
                $mockData['size'],
                $mockData['mime_type'],
            ];
        })->toArray();

        $this->table(
            ['researcher_id', 'researcher_fname', 'researcher_picture', 'rf_path', 'rf_size', 'rf_mime_type'],
            $afterData
        );
    }
}
