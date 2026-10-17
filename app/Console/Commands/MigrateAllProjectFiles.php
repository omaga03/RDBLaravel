<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\FileUploadService;

class MigrateAllProjectFiles extends Command
{
    protected $signature = 'migrate:all-files {--dry-run}';
    protected $description = 'Migrate all old file names to new UUID format (Dual Path Support)';

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
            $this->info('🔍 DRY RUN MODE - ไม่มีการเปลี่ยนแปลง');
        }

        $this->info('เริ่มต้น Migrate ไฟล์ทั้งหมด...');
        $this->newLine();

        // Migrate ทั้งหมด 12 ตาราที่มีไฟล์
        $this->migrateRdbPublished($dryRun);
        $this->migrateRdbResearcher($dryRun);
        $this->migrateResearchConferenceInThai($dryRun);
        $this->migrateResearchNews($dryRun);
        $this->migrateRdbProjectTypesSub($dryRun);
        $this->migrateRdbDip($dryRun);
        $this->migrateJournalStatus($dryRun);
        $this->migrateJournalOtherPage($dryRun);
        $this->migrateRdbNacc($dryRun);
        $this->migrateRdbProject($dryRun);
        $this->migrateRdbProjectFiles($dryRun);
        $this->migrateRdbProjectUtilize($dryRun);

        $this->newLine();
        $this->info('✅ Migration เสร็จสิ้น!');
    }

    // ตาราโครงการตีพิมพ์ (rdb_published)
    private function migrateRdbPublished(bool $dryRun): void
    {
        $this->info('📄 Migrating rdb_published (pub_file → pf_path)...');

        $records = DB::table('rdb_published')
            ->whereNotNull('pub_file')
            ->where('pub_file', '!=', '')
            ->whereNull('pf_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->pub_file;
                $oldPath = 'uploads/published/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    $this->warn("  ⚠️  ไม่พบไฟล์: {$oldPath}");
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'published/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('rdb_published')
                        ->where('id', $record->id)
                        ->update([
                            'pf_path' => $newPath,
                            'pf_size' => Storage::size($newPath),
                            'pf_mime_type' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                    $this->line("  ✓ {$oldFile} → {$newFile}");
                } else {
                    $this->line("  [DRY] {$oldFile} → {$newFile}");
                }

                $count++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }

    // ตารานักวิจัย (rdb_researcher)
    private function migrateRdbResearcher(bool $dryRun): void
    {
        $this->info('👤 Migrating rdb_researcher (researcher_picture → rf_path)...');

        $records = DB::table('rdb_researcher')
            ->whereNotNull('researcher_picture')
            ->where('researcher_picture', '!=', '')
            ->whereNull('rf_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->researcher_picture;
                $oldPath = 'uploads/researchers/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'researchers/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('rdb_researcher')
                        ->where('researcher_id', $record->researcher_id)
                        ->update([
                            'rf_path' => $newPath,
                            'rf_size' => Storage::size($newPath),
                            'rf_mime_type' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip error
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }

    // ตาราการประชุมทั้งประเทศ (research_coferenceinthai)
    private function migrateResearchConferenceInThai(bool $dryRun): void
    {
        $this->info('🎤 Migrating research_coferenceinthai (con_img → file_path)...');

        $records = DB::table('research_coferenceinthai')
            ->whereNotNull('con_img')
            ->where('con_img', '!=', '')
            ->whereNull('file_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->con_img;
                $oldPath = 'uploads/conferences/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'conferences/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('research_coferenceinthai')
                        ->where('id', $record->id)
                        ->update([
                            'file_path' => $newPath,
                            'file_size' => Storage::size($newPath),
                            'file_mime_type' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }

    // ตาราข่าววิจัย (research_news)
    private function migrateResearchNews(bool $dryRun): void
    {
        $this->info('📰 Migrating research_news (news_img → news_path)...');

        $records = DB::table('research_news')
            ->whereNotNull('news_img')
            ->where('news_img', '!=', '')
            ->whereNull('news_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->news_img;
                $oldPath = 'uploads/news/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'news/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('research_news')
                        ->where('id', $record->id)
                        ->update([
                            'news_path' => $newPath,
                            'news_size' => Storage::size($newPath),
                            'news_mime_type' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }

    // ตาราประเภทโครงการย่อย (rdb_project_types_sub)
    private function migrateRdbProjectTypesSub(bool $dryRun): void
    {
        $this->info('📋 Migrating rdb_project_types_sub (pts_file → pts_path)...');

        $records = DB::table('rdb_project_types_sub')
            ->whereNotNull('pts_file')
            ->where('pts_file', '!=', '')
            ->whereNull('pts_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->pts_file;
                $oldPath = 'uploads/project_types/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'project_types/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('rdb_project_types_sub')
                        ->where('pts_id', $record->pts_id)
                        ->update([
                            'pts_path' => $newPath,
                            'pts_size' => Storage::size($newPath),
                            'pts_mime_type' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }

    // ตาราดิปโลม (rdb_dip)
    private function migrateRdbDip(bool $dryRun): void
    {
        $this->info('🎓 Migrating rdb_dip (dip_files → dip_path)...');

        $records = DB::table('rdb_dip')
            ->whereNotNull('dip_files')
            ->where('dip_files', '!=', '')
            ->whereNull('dip_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->dip_files;
                $oldPath = 'uploads/dips/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'dips/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('rdb_dip')
                        ->where('dip_id', $record->dip_id)
                        ->update([
                            'dip_path' => $newPath,
                            'dip_size' => Storage::size($newPath),
                            'dip_mime_type' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }

    // ตาระสถานะวารสาร (journal_status)
    private function migrateJournalStatus(bool $dryRun): void
    {
        $this->info('📑 Migrating journal_status (jou_files → js_path)...');

        $records = DB::table('journal_status')
            ->whereNotNull('jou_files')
            ->where('jou_files', '!=', '')
            ->whereNull('js_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->jou_files;
                $oldPath = 'uploads/journals/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'journals/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('journal_status')
                        ->where('id', $record->id)
                        ->update([
                            'js_path' => $newPath,
                            'js_size' => Storage::size($newPath),
                            'js_mime_type' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }

    // ตาราหน้าอื่นๆของวารสาร (journalotherpage)
    private function migrateJournalOtherPage(bool $dryRun): void
    {
        $this->info('📄 Migrating journalotherpage (file_name → jop_path)...');

        $records = DB::table('journalotherpage')
            ->whereNotNull('file_name')
            ->where('file_name', '!=', '')
            ->whereNull('jop_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->file_name;
                $oldPath = 'uploads/journal_pages/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'journal_pages/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('journalotherpage')
                        ->where('id', $record->id)
                        ->update([
                            'jop_path' => $newPath,
                            'jop_size' => Storage::size($newPath),
                            'jop_mime_type' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }

    // ตารา NACC (rdb_nacc)
    private function migrateRdbNacc(bool $dryRun): void
    {
        $this->info('🔬 Migrating rdb_nacc (nacc_files → nacc_path)...');

        $records = DB::table('rdb_nacc')
            ->whereNotNull('nacc_files')
            ->where('nacc_files', '!=', '')
            ->whereNull('nacc_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->nacc_files;
                $oldPath = 'uploads/nacc/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'nacc/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('rdb_nacc')
                        ->where('id', $record->id)
                        ->update([
                            'nacc_path' => $newPath,
                            'nacc_size' => Storage::size($newPath),
                            'nacc_mime_type' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }

    // ตาราโครงการ (rdb_project)
    private function migrateRdbProject(bool $dryRun): void
    {
        $this->info('🔬 Migrating rdb_project (abstract & report files)...');

        // Abstract files
        $records = DB::table('rdb_project')
            ->whereNotNull('pro_abstract_file')
            ->where('pro_abstract_file', '!=', '')
            ->whereNull('pro_abstract_file_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->pro_abstract_file;
                $oldPath = 'uploads/projects/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'abstracts/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('rdb_project')
                        ->where('pro_id', $record->pro_id)
                        ->update([
                            'pro_abstract_file_path' => $newPath,
                            'pro_abstract_size' => Storage::size($newPath),
                            'pro_abstract_mime' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        // Report files
        $reportRecords = DB::table('rdb_project')
            ->whereNotNull('pro_file')
            ->where('pro_file', '!=', '')
            ->whereNull('pro_file_path')
            ->get();

        foreach ($reportRecords as $record) {
            try {
                $oldFile = $record->pro_file;
                $oldPath = 'uploads/projects/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'reports/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('rdb_project')
                        ->where('pro_id', $record->pro_id)
                        ->update([
                            'pro_file_path' => $newPath,
                            'pro_file_size' => Storage::size($newPath),
                            'pro_file_mime' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }

    // ตาราไฟล์โครงการ (rdb_project_files)
    private function migrateRdbProjectFiles(bool $dryRun): void
    {
        $this->info('📎 Migrating rdb_project_files (rf_files → prf_path)...');

        $records = DB::table('rdb_project_files')
            ->whereNotNull('rf_files')
            ->where('rf_files', '!=', '')
            ->whereNull('prf_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->rf_files;
                $oldPath = 'uploads/project_files/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'project_files/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('rdb_project_files')
                        ->where('id', $record->id)
                        ->update([
                            'prf_safe_name' => $newFile,
                            'prf_path' => $newPath,
                            'prf_size' => Storage::size($newPath),
                            'prf_mime_type' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }

    // ตาราการใช้ประโยชน์โครงการ (rdb_project_utilize)
    private function migrateRdbProjectUtilize(bool $dryRun): void
    {
        $this->info('🎯 Migrating rdb_project_utilize (utz_files → uf_path)...');

        $records = DB::table('rdb_project_utilize')
            ->whereNotNull('utz_files')
            ->where('utz_files', '!=', '')
            ->whereNull('uf_path')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $oldFile = $record->utz_files;
                $oldPath = 'uploads/utilize/' . $oldFile;

                if (!Storage::exists($oldPath)) {
                    continue;
                }

                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = Str::uuid() . '.' . $ext;
                $newPath = 'utilize/' . $newFile;

                if (!$dryRun) {
                    Storage::copy($oldPath, $newPath);
                    DB::table('rdb_project_utilize')
                        ->where('id', $record->id)
                        ->update([
                            'uf_path' => $newPath,
                            'uf_size' => Storage::size($newPath),
                            'uf_mime_type' => mime_content_type(Storage::path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        $this->info("  เสร็จ: {$count} ไฟล์ migrate\n");
    }
}
