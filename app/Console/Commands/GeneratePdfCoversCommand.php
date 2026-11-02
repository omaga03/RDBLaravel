<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Services\PdfCoverService;

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
    protected $description = 'Generate high-performance .webp cover images from first page of published research and project PDFs';

    public function handle(PdfCoverService $coverService): int
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        $module = strtolower($this->option('module') ?: 'all');
        $force = (bool)$this->option('force');
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;

        $this->info("==================================================================");
        $this->info(" PCRU RDB - Automated PDF Cover Image (.webp) Generator");
        $this->info(" Module: {$module} | Force: " . ($force ? 'YES' : 'NO') . " | Limit: " . ($limit ?: 'NONE'));
        $this->info("==================================================================");

        $pdftoppm = $coverService->getPdftoppmBinary();
        if ($pdftoppm) {
            $this->info("Engine: Poppler pdftoppm CLI detected at [{$pdftoppm}]");
        } else {
            $this->comment("Engine: System fallback mode (PHP GD & Embedded Extraction)");
        }
        $this->newLine();

        if (in_array($module, ['all', 'project'])) {
            $this->processProjects($coverService, $force, $limit);
        }

        if (in_array($module, ['all', 'published'])) {
            $this->processPublished($coverService, $force, $limit);
        }

        $this->info("All processing completed successfully!");
        return Command::SUCCESS;
    }

    protected function processProjects(PdfCoverService $coverService, bool $force, ?int $limit): void
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

        $query->chunkById(50, function ($projects) use ($coverService, $force, $limit, $bar, &$success, &$failed, &$processed) {
            foreach ($projects as $project) {
                if ($limit && $processed >= $limit) {
                    return false;
                }
                $path = $coverService->generateProjectCover($project, $force);
                if ($path) {
                    $success++;
                } else {
                    $failed++;
                }
                $processed++;
                $bar->advance();
            }
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

    protected function processPublished(PdfCoverService $coverService, bool $force, ?int $limit): void
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

        $query->chunkById(50, function ($items) use ($coverService, $force, $limit, $bar, &$success, &$failed, &$processed) {
            foreach ($items as $item) {
                if ($limit && $processed >= $limit) {
                    return false;
                }
                $path = $coverService->generatePublishedCover($item, $force);
                if ($path) {
                    $success++;
                } else {
                    $failed++;
                }
                $processed++;
                $bar->advance();
            }
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
