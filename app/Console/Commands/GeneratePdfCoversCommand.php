<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Services\PdfCoverService;
use App\Services\DSpaceExportService;

class GeneratePdfCoversCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdb:generate-pdf-covers
                            {--module=all : Module to process (all, project, published)}
                            {--force : Force regenerate even if cover already exists}
                            {--limit= : Limit the number of records to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate high-performance .webp cover images from the entire first page of published research and project PDFs';

    public function handle(PdfCoverService $coverService, DSpaceExportService $dspaceService): int
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        $module = strtolower($this->option('module') ?: 'all');
        $force = (bool)$this->option('force');
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;

        $this->info("==================================================================");
        $this->info(" PCRU RDB - Automated Full-Page PDF Cover (.webp) Generator");
        $this->info(" Module: {$module} | Force: " . ($force ? 'YES' : 'NO') . " | Limit: " . ($limit ?: 'NONE'));
        $this->info("==================================================================");
        $this->info("Engine: " . $coverService->getActiveEngineDescription());
        $this->newLine();

        if (in_array($module, ['all', 'project'])) {
            $this->processProjects($coverService, $dspaceService, $force, $limit);
        }

        if (in_array($module, ['all', 'published'])) {
            $this->processPublished($coverService, $dspaceService, $force, $limit);
        }

        $this->info("All processing completed successfully!");
        return Command::SUCCESS;
    }

    protected function processProjects(PdfCoverService $coverService, DSpaceExportService $dspaceService, bool $force, ?int $limit): void
    {
        $this->info(">> Processing Research Projects (rdb_project)...");

        $query = RdbProject::with(['year', 'department'])
            ->where('data_show', 1)
            ->where(function ($q) {
                $q->where('pro_file_show', 0)
                  ->orWhere('pro_file_show', '0');
            })
            ->where(function ($q) {
                $q->whereNotNull('pro_file_path')->where('pro_file_path', '<>', '')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('pro_file')->where('pro_file', '<>', '')->where('pro_file', '<>', '0');
                  });
            });

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('pro_cover_path')->orWhere('pro_cover_path', '');
            });
        }

        $total = $query->count();
        if ($limit && $total > $limit) {
            $total = $limit;
        }

        if ($total === 0) {
            $this->line("   No projects need cover generation.");
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;
        $processed = 0;

        $query->chunkById(100, function ($projects) use ($coverService, $dspaceService, $force, $limit, $bar, &$success, &$failed, &$processed) {
            $batchItems = [];
            $projectMap = [];
            $relPathMap = [];

            foreach ($projects as $project) {
                if ($limit && $processed >= $limit) {
                    break;
                }
                $processed++;

                if (!$force && !empty($project->pro_cover_path) && Storage::disk('public')->exists($project->pro_cover_path)) {
                    $success++;
                    $bar->advance();
                    continue;
                }

                $pdfPath = $dspaceService->resolveProjectFileOnDisk($project, 'report');
                if (!$pdfPath || !file_exists($pdfPath)) {
                    $failed++;
                    $bar->advance();
                    continue;
                }

                $folder = 'project/covers';
                $filename = 'project_' . $project->pro_id . '_' . substr(md5($project->pro_id . '_' . filemtime($pdfPath)), 0, 8) . '.webp';
                $relativeStoragePath = $folder . '/' . $filename;
                $absoluteTargetPath = Storage::disk('public')->path($relativeStoragePath);

                $batchItems[] = [
                    'key' => $project->pro_id,
                    'pdf' => $pdfPath,
                    'webp' => $absoluteTargetPath,
                    'meta' => $coverService->buildProjectMetadata($project),
                ];
                $projectMap[$project->pro_id] = $project;
                $relPathMap[$project->pro_id] = $relativeStoragePath;
            }

            if (!empty($batchItems)) {
                $batchResults = $coverService->renderBatchFirstPageToWebp($batchItems);
                foreach ($batchItems as $item) {
                    $id = $item['key'];
                    $ok = $batchResults[$id] ?? false;
                    if ($ok) {
                        $proj = $projectMap[$id];
                        $proj->pro_cover_path = $relPathMap[$id];
                        $proj->saveQuietly();
                        $success++;
                    } else {
                        $failed++;
                    }
                    $bar->advance();
                }
            }

            unset($batchItems, $projectMap, $relPathMap);
            gc_collect_cycles();

            if ($limit && $processed >= $limit) {
                return false;
            }
        }, 'pro_id');

        $bar->finish();
        $this->newLine();
        $this->info("   Projects done: {$success} generated, {$failed} skipped/failed.");
        $this->newLine();
    }

    protected function processPublished(PdfCoverService $coverService, DSpaceExportService $dspaceService, bool $force, ?int $limit): void
    {
        $this->info(">> Processing Published Articles (rdb_published)...");

        $query = RdbPublished::with(['year', 'department', 'pubtype'])
            ->where('data_show', 1)
            ->where(function ($q) {
                $q->whereNotNull('pf_path')->where('pf_path', '<>', '')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('pub_file')->where('pub_file', '<>', '')->where('pub_file', '<>', '0');
                  });
            });

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('pub_cover_path')->orWhere('pub_cover_path', '');
            });
        }

        $total = $query->count();
        if ($limit && $total > $limit) {
            $total = $limit;
        }

        if ($total === 0) {
            $this->line("   No published articles need cover generation.");
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;
        $processed = 0;

        $query->chunkById(100, function ($items) use ($coverService, $dspaceService, $force, $limit, $bar, &$success, &$failed, &$processed) {
            $batchItems = [];
            $itemMap = [];
            $relPathMap = [];

            foreach ($items as $item) {
                if ($limit && $processed >= $limit) {
                    break;
                }
                $processed++;

                if (!$force && !empty($item->pub_cover_path) && Storage::disk('public')->exists($item->pub_cover_path)) {
                    $success++;
                    $bar->advance();
                    continue;
                }

                $pdfPath = $dspaceService->resolvePublishedFileOnDisk($item);
                if (!$pdfPath || !file_exists($pdfPath)) {
                    $failed++;
                    $bar->advance();
                    continue;
                }

                $folder = 'published/covers';
                $filename = 'published_' . $item->id . '_' . substr(md5($item->id . '_' . filemtime($pdfPath)), 0, 8) . '.webp';
                $relativeStoragePath = $folder . '/' . $filename;
                $absoluteTargetPath = Storage::disk('public')->path($relativeStoragePath);

                $batchItems[] = [
                    'key' => $item->id,
                    'pdf' => $pdfPath,
                    'webp' => $absoluteTargetPath,
                    'meta' => $coverService->buildPublishedMetadata($item),
                ];
                $itemMap[$item->id] = $item;
                $relPathMap[$item->id] = $relativeStoragePath;
            }

            if (!empty($batchItems)) {
                $batchResults = $coverService->renderBatchFirstPageToWebp($batchItems);
                foreach ($batchItems as $bItem) {
                    $id = $bItem['key'];
                    $ok = $batchResults[$id] ?? false;
                    if ($ok) {
                        $pub = $itemMap[$id];
                        $pub->pub_cover_path = $relPathMap[$id];
                        $pub->saveQuietly();
                        $success++;
                    } else {
                        $failed++;
                    }
                    $bar->advance();
                }
            }

            unset($batchItems, $itemMap, $relPathMap);
            gc_collect_cycles();

            if ($limit && $processed >= $limit) {
                return false;
            }
        }, 'id');

        $bar->finish();
        $this->newLine();
        $this->info("   Published done: {$success} generated, {$failed} skipped/failed.");
        $this->newLine();
    }
}
