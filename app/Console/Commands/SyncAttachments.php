<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SyncAttachments extends Command
{
    protected $signature = 'rdb:sync-attachments {--dry-run : Only inspect and report without updating database}';
    protected $description = 'Synchronize and verify all uploaded attachments into database paths and metadata';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info('====================================================');
        $this->info('  RDB ATTACHMENTS VERIFICATION & SYNC');
        $this->info('====================================================');
        if ($dryRun) {
            $this->warn('🔍 RUNNING IN DRY-RUN MODE (No database updates)');
        }
        $this->newLine();

        $stats = [
            'RdbPublished' => $this->syncPublished($dryRun),
            'RdbProject (Report)' => $this->syncProjectReports($dryRun),
            'RdbProject (Abstract)' => $this->syncProjectAbstracts($dryRun),
            'RdbProjectFiles' => $this->syncProjectFiles($dryRun),
            'RdbProjectUtilize' => $this->syncUtilize($dryRun),
            'RdbDip' => $this->syncDip($dryRun),
            'RdbResearcher' => $this->syncResearchers($dryRun),
            'ResearchNews' => $this->syncNews($dryRun),
            'ResearchConference' => $this->syncConference($dryRun),
            'RdbProjectTypeSub' => $this->syncProjectTypeSub($dryRun),
        ];

        $this->newLine();
        $this->info('====================================================');
        $this->info('  SUMMARY OF ATTACHMENT SYNC');
        $this->info('====================================================');
        $rows = [];
        $totalFound = 0;
        $totalRecords = 0;
        $totalSynced = 0;

        foreach ($stats as $module => $data) {
            $percent = $data['total'] > 0 ? round(($data['found'] / $data['total']) * 100, 1) : 0;
            $rows[] = [
                $module,
                $data['total'],
                $data['found'],
                $percent . '%',
                $data['updated'],
            ];
            $totalRecords += $data['total'];
            $totalFound += $data['found'];
            $totalSynced += $data['updated'];
        }

        $overallPercent = $totalRecords > 0 ? round(($totalFound / $totalRecords) * 100, 1) : 0;
        $rows[] = ['----------------------', '-------', '-------', '-------', '-------'];
        $rows[] = ['TOTAL OVERALL', $totalRecords, $totalFound, $overallPercent . '%', $totalSynced];

        $this->table(['Module', 'Total in DB', 'Files Found', 'Coverage', 'Synced/Updated'], $rows);

        $this->info('✅ Process completed successfully!');
        return Command::SUCCESS;
    }

    private function fileExistsOnDisk(?string $path): bool
    {
        if (empty($path)) return false;
        return Storage::disk('public')->exists($path) || file_exists(public_path('storage/' . $path)) || file_exists(public_path($path));
    }

    private function getFileInfo(string $relativePath): array
    {
        $fullPath = Storage::disk('public')->path($relativePath);
        if (!file_exists($fullPath)) {
            $alt = public_path('storage/' . $relativePath);
            if (file_exists($alt)) {
                $fullPath = $alt;
            } else {
                $alt2 = public_path($relativePath);
                if (file_exists($alt2)) {
                    $fullPath = $alt2;
                }
            }
        }

        $size = file_exists($fullPath) ? @filesize($fullPath) : null;
        
        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        $extMap = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'rar' => 'application/vnd.rar',
        ];
        $mime = $extMap[$ext] ?? 'application/octet-stream';

        return [
            'size' => $size ?: null,
            'mime' => $mime,
        ];
    }

    private function syncPublished(bool $dryRun): array
    {
        $this->comment('Checking RdbPublished (pub_file)...');
        $records = DB::table('rdb_published')->whereNotNull('pub_file')->where('pub_file', '!=', '')->get(['id', 'pub_file', 'pf_path']);
        $total = count($records);
        $found = 0;
        $updated = 0;

        foreach ($records as $r) {
            $candidates = [
                $r->pf_path,
                "uploads/datapublished/{$r->id}/{$r->pub_file}",
                "uploads/datapublished/{$r->pub_file}",
                "uploads/rdb_published/{$r->pub_file}",
                "uploads/published/{$r->pub_file}",
                "published/{$r->pub_file}",
            ];

            $matched = null;
            foreach ($candidates as $c) {
                if ($this->fileExistsOnDisk($c)) {
                    $matched = $c;
                    break;
                }
            }

            if ($matched) {
                $found++;
                if (empty($r->pf_path) && !$dryRun) {
                    $info = $this->getFileInfo($matched);
                    DB::table('rdb_published')->where('id', $r->id)->update([
                        'pf_path' => $matched,
                        'pf_size' => $info['size'],
                        'pf_mime_type' => $info['mime'],
                    ]);
                    $updated++;
                }
            }
        }

        return ['total' => $total, 'found' => $found, 'updated' => $updated];
    }

    private function syncProjectReports(bool $dryRun): array
    {
        $this->comment('Checking RdbProject reports (pro_file)...');
        $records = DB::table('rdb_project')->whereNotNull('pro_file')->where('pro_file', '!=', '')->get(['pro_id', 'pro_file', 'pro_file_path']);
        $total = count($records);
        $found = 0;
        $updated = 0;

        foreach ($records as $r) {
            $candidates = [
                $r->pro_file_path,
                "uploads/datafiles/{$r->pro_id}/{$r->pro_file}",
                "uploads/project_files/{$r->pro_id}/{$r->pro_file}",
                "uploads/projects/{$r->pro_file}",
                "projects/{$r->pro_file}",
                "datafiles/{$r->pro_id}/{$r->pro_file}",
            ];

            $matched = null;
            foreach ($candidates as $c) {
                if ($this->fileExistsOnDisk($c)) {
                    $matched = $c;
                    break;
                }
            }

            if ($matched) {
                $found++;
                if (empty($r->pro_file_path) && !$dryRun) {
                    $info = $this->getFileInfo($matched);
                    DB::table('rdb_project')->where('pro_id', $r->pro_id)->update([
                        'pro_file_path' => $matched,
                        'pro_file_size' => $info['size'],
                        'pro_file_mime' => $info['mime'],
                    ]);
                    $updated++;
                }
            }
        }

        return ['total' => $total, 'found' => $found, 'updated' => $updated];
    }

    private function syncProjectAbstracts(bool $dryRun): array
    {
        $this->comment('Checking RdbProject abstracts (pro_abstract_file)...');
        $records = DB::table('rdb_project')->whereNotNull('pro_abstract_file')->where('pro_abstract_file', '!=', '')->get(['pro_id', 'pro_abstract_file', 'pro_abstract_file_path']);
        $total = count($records);
        $found = 0;
        $updated = 0;

        foreach ($records as $r) {
            $candidates = [
                $r->pro_abstract_file_path,
                "uploads/datafiles/{$r->pro_id}/{$r->pro_abstract_file}",
                "uploads/project_files/{$r->pro_id}/{$r->pro_abstract_file}",
                "uploads/projects/{$r->pro_abstract_file}",
                "abstracts/{$r->pro_abstract_file}",
                "projects/{$r->pro_abstract_file}",
            ];

            $matched = null;
            foreach ($candidates as $c) {
                if ($this->fileExistsOnDisk($c)) {
                    $matched = $c;
                    break;
                }
            }

            if ($matched) {
                $found++;
                if (empty($r->pro_abstract_file_path) && !$dryRun) {
                    $info = $this->getFileInfo($matched);
                    DB::table('rdb_project')->where('pro_id', $r->pro_id)->update([
                        'pro_abstract_file_path' => $matched,
                        'pro_abstract_size' => $info['size'],
                        'pro_abstract_mime' => $info['mime'],
                    ]);
                    $updated++;
                }
            }
        }

        return ['total' => $total, 'found' => $found, 'updated' => $updated];
    }

    private function syncProjectFiles(bool $dryRun): array
    {
        $this->comment('Checking RdbProjectFiles (rf_files)...');
        $records = DB::table('rdb_project_files')->whereNotNull('rf_files')->where('rf_files', '!=', '')->get(['id', 'pro_id', 'rf_files', 'prf_path']);
        $total = count($records);
        $found = 0;
        $updated = 0;

        foreach ($records as $r) {
            $candidates = [
                $r->prf_path,
                "uploads/datafiles/{$r->pro_id}/{$r->rf_files}",
                "uploads/datafiles/{$r->rf_files}",
                "uploads/project_files/{$r->pro_id}/{$r->rf_files}",
                "uploads/project_files/{$r->rf_files}",
                "project_files/{$r->rf_files}",
                "uploads/projects/{$r->rf_files}",
            ];

            $matched = null;
            foreach ($candidates as $c) {
                if ($this->fileExistsOnDisk($c)) {
                    $matched = $c;
                    break;
                }
            }

            if ($matched) {
                $found++;
                if (empty($r->prf_path) && !$dryRun) {
                    $info = $this->getFileInfo($matched);
                    DB::table('rdb_project_files')->where('id', $r->id)->update([
                        'prf_path' => $matched,
                        'prf_size' => $info['size'],
                        'prf_mime_type' => $info['mime'],
                    ]);
                    $updated++;
                }
            }
        }

        return ['total' => $total, 'found' => $found, 'updated' => $updated];
    }

    private function syncUtilize(bool $dryRun): array
    {
        $this->comment('Checking RdbProjectUtilize (utz_files)...');
        $records = DB::table('rdb_project_utilize')->whereNotNull('utz_files')->where('utz_files', '!=', '')->get(['utz_id', 'pro_id', 'utz_files', 'uf_path']);
        $total = count($records);
        $found = 0;
        $updated = 0;

        foreach ($records as $r) {
            $candidates = [
                $r->uf_path,
                "uploads/datautilize/{$r->utz_id}/{$r->utz_files}",
                "uploads/datautilize/{$r->utz_files}",
                "uploads/utilize/{$r->utz_files}",
                "utilize/{$r->utz_files}",
            ];

            $matched = null;
            foreach ($candidates as $c) {
                if ($this->fileExistsOnDisk($c)) {
                    $matched = $c;
                    break;
                }
            }

            if ($matched) {
                $found++;
                if (empty($r->uf_path) && !$dryRun) {
                    $info = $this->getFileInfo($matched);
                    DB::table('rdb_project_utilize')->where('utz_id', $r->utz_id)->update([
                        'uf_path' => $matched,
                        'uf_size' => $info['size'],
                        'uf_mime_type' => $info['mime'],
                    ]);
                    $updated++;
                }
            }
        }

        return ['total' => $total, 'found' => $found, 'updated' => $updated];
    }

    private function syncDip(bool $dryRun): array
    {
        $this->comment('Checking RdbDip (dip_files)...');
        $records = DB::table('rdb_dip')->whereNotNull('dip_files')->where('dip_files', '!=', '')->get(['dip_id', 'dip_files', 'dip_path']);
        $total = count($records);
        $found = 0;
        $updated = 0;

        foreach ($records as $r) {
            $candidates = [
                $r->dip_path,
                "uploads/datadip/{$r->dip_id}/{$r->dip_files}",
                "uploads/datadip/{$r->dip_files}",
                "uploads/dips/{$r->dip_id}/{$r->dip_files}",
                "uploads/dips/{$r->dip_files}",
                "dips/{$r->dip_files}",
            ];

            $matched = null;
            foreach ($candidates as $c) {
                if ($this->fileExistsOnDisk($c)) {
                    $matched = $c;
                    break;
                }
            }

            if ($matched) {
                $found++;
                if (empty($r->dip_path) && !$dryRun) {
                    $info = $this->getFileInfo($matched);
                    DB::table('rdb_dip')->where('dip_id', $r->dip_id)->update([
                        'dip_path' => $matched,
                        'dip_size' => $info['size'],
                        'dip_mime_type' => $info['mime'],
                    ]);
                    $updated++;
                }
            }
        }

        return ['total' => $total, 'found' => $found, 'updated' => $updated];
    }

    private function syncResearchers(bool $dryRun): array
    {
        $this->comment('Checking RdbResearcher (researcher_picture)...');
        $records = DB::table('rdb_researcher')->whereNotNull('researcher_picture')->where('researcher_picture', '!=', '')->get(['researcher_id', 'researcher_picture', 'rf_path']);
        $total = count($records);
        $found = 0;
        $updated = 0;

        foreach ($records as $r) {
            $candidates = [
                $r->rf_path,
                "uploads/dataresearcher/{$r->researcher_picture}",
                "uploads/researchers/{$r->researcher_picture}",
                "researchers/{$r->researcher_picture}",
            ];

            $matched = null;
            foreach ($candidates as $c) {
                if ($this->fileExistsOnDisk($c)) {
                    $matched = $c;
                    break;
                }
            }

            if ($matched) {
                $found++;
                if (empty($r->rf_path) && !$dryRun) {
                    $info = $this->getFileInfo($matched);
                    DB::table('rdb_researcher')->where('researcher_id', $r->researcher_id)->update([
                        'rf_path' => $matched,
                        'rf_size' => $info['size'],
                        'rf_mime_type' => $info['mime'],
                    ]);
                    $updated++;
                }
            }
        }

        return ['total' => $total, 'found' => $found, 'updated' => $updated];
    }

    private function syncNews(bool $dryRun): array
    {
        $this->comment('Checking ResearchNews (news_img)...');
        $records = DB::table('research_news')->whereNotNull('news_img')->where('news_img', '!=', '')->get(['id', 'news_img', 'news_path']);
        $total = count($records);
        $found = 0;
        $updated = 0;

        foreach ($records as $r) {
            $candidates = [
                $r->news_path,
                "uploads/datanewsimg/{$r->news_img}",
                "uploads/news/{$r->news_img}",
                "uploads/images/{$r->news_img}",
                "news/{$r->news_img}",
            ];

            $matched = null;
            foreach ($candidates as $c) {
                if ($this->fileExistsOnDisk($c)) {
                    $matched = $c;
                    break;
                }
            }

            if ($matched) {
                $found++;
                if (empty($r->news_path) && !$dryRun) {
                    $info = $this->getFileInfo($matched);
                    DB::table('research_news')->where('id', $r->id)->update([
                        'news_path' => $matched,
                        'news_size' => $info['size'],
                        'news_mime_type' => $info['mime'],
                    ]);
                    $updated++;
                }
            }
        }

        return ['total' => $total, 'found' => $found, 'updated' => $updated];
    }

    private function syncConference(bool $dryRun): array
    {
        $this->comment('Checking ResearchConference (con_img)...');
        $records = DB::table('research_coferenceinthai')->whereNotNull('con_img')->where('con_img', '!=', '')->get(['id', 'con_img', 'con_path']);
        $total = count($records);
        $found = 0;
        $updated = 0;

        foreach ($records as $r) {
            $candidates = [
                $r->con_path,
                "uploads/datacoferenceinthai/{$r->con_img}",
                "uploads/conference/{$r->con_img}",
                "uploads/images/{$r->con_img}",
                "conference/{$r->con_img}",
            ];

            $matched = null;
            foreach ($candidates as $c) {
                if ($this->fileExistsOnDisk($c)) {
                    $matched = $c;
                    break;
                }
            }

            if ($matched) {
                $found++;
                if (empty($r->con_path) && !$dryRun) {
                    $info = $this->getFileInfo($matched);
                    DB::table('research_coferenceinthai')->where('id', $r->id)->update([
                        'con_path' => $matched,
                        'file_path' => $matched,
                        'con_size' => $info['size'],
                        'file_size' => $info['size'],
                        'con_mime_type' => $info['mime'],
                        'file_mime_type' => $info['mime'],
                    ]);
                    $updated++;
                }
            }
        }

        return ['total' => $total, 'found' => $found, 'updated' => $updated];
    }

    private function syncProjectTypeSub(bool $dryRun): array
    {
        $this->comment('Checking RdbProjectTypeSub (pts_file)...');
        $records = DB::table('rdb_project_types_sub')->whereNotNull('pts_file')->where('pts_file', '!=', '')->get(['pts_id', 'pts_file', 'pts_path']);
        $total = count($records);
        $found = 0;
        $updated = 0;

        foreach ($records as $r) {
            $candidates = [
                $r->pts_path,
                "uploads/datapts/{$r->pts_file}",
                "uploads/project_types_sub/{$r->pts_file}",
                "project_types_sub/{$r->pts_file}",
            ];

            $matched = null;
            foreach ($candidates as $c) {
                if ($this->fileExistsOnDisk($c)) {
                    $matched = $c;
                    break;
                }
            }

            if ($matched) {
                $found++;
                if (empty($r->pts_path) && !$dryRun) {
                    $info = $this->getFileInfo($matched);
                    DB::table('rdb_project_types_sub')->where('pts_id', $r->pts_id)->update([
                        'pts_path' => $matched,
                        'pts_size' => $info['size'],
                        'pts_mime_type' => $info['mime'],
                    ]);
                    $updated++;
                }
            }
        }

        return ['total' => $total, 'found' => $found, 'updated' => $updated];
    }
}
