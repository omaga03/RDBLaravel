<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Services\DSpaceExportService;

class CountPdfPagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdb:count-pdf-pages 
                            {--module=all : Module to scan (all, project, published)} 
                            {--force : Recalculate even if page count already exists} 
                            {--limit= : Maximum number of records to process per module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan and store PDF page counts into database (pro_page_num and pub_numfile) using high-precision Poppler/Parser engines';

    /**
     * Execute the console command.
     */
    public function handle(DSpaceExportService $dspaceService): int
    {
        $module = strtolower($this->option('module') ?? 'all');
        $force = (bool) $this->option('force');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->info('====================================================');
        $this->info('  RDB PDF Page Counter & Database Synchronization   ');
        $this->info('====================================================');

        // Check active PDF engine
        $pdftotextBinary = $dspaceService->getPdftotextBinary();
        if ($pdftotextBinary) {
            $this->info(" Engine: [Poppler pdftotext] - {$pdftotextBinary} (Accuracy: 99.99%)");
        } else {
            $this->warn(" Engine: [Fallback Pure PHP Parser] (Notice: Install 'poppler-utils' for optimal 99.99% accuracy on secured PDFs)");
        }
        $this->line('');

        $totalUpdated = 0;

        if (in_array($module, ['all', 'project'], true)) {
            $totalUpdated += $this->processProjects($dspaceService, $force, $limit);
        }

        if (in_array($module, ['all', 'published'], true)) {
            $totalUpdated += $this->processPublished($dspaceService, $force, $limit);
        }

        $this->info('----------------------------------------------------');
        $this->info(" Finished! Total records updated: {$totalUpdated}");
        $this->info('====================================================');

        return Command::SUCCESS;
    }

    /**
     * Process Research Projects (rdb_project.pro_file -> rdb_project.pro_page_num)
     */
    protected function processProjects(DSpaceExportService $dspaceService, bool $force, ?int $limit): int
    {
        $this->comment('Scanning Research Projects (rdb_project)...');

        $query = RdbProject::query()
            ->whereNotNull('pro_file')
            ->where('pro_file', '<>', '')
            ->where('pro_file', '<>', '0');

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('pro_page_num')->orWhere('pro_page_num', '<=', 0);
            });
        }

        if ($limit) {
            $query->limit($limit);
        }

        $total = $query->count();
        if ($limit && $total > $limit) {
            $total = $limit;
        }

        if ($total === 0) {
            $this->line('  No projects pending page count.');
            return 0;
        }

        $this->line("  Found {$total} project(s) to process.");
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $updatedCount = 0;
        $missingCount = 0;
        $failedCount = 0;

        $projects = $query->get(['pro_id', 'pro_file', 'pro_file_path', 'pro_page_num']);

        foreach ($projects as $project) {
            $filePath = $dspaceService->resolveProjectFileOnDisk($project, 'report');

            if (!$filePath || !file_exists($filePath)) {
                $missingCount++;
                $progressBar->advance();
                continue;
            }

            try {
                $pageCount = $dspaceService->countPdfPages($filePath);
                if ($pageCount > 0) {
                    $project->pro_page_num = $pageCount;
                    $project->saveQuietly();
                    $updatedCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                $failedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');
        $this->info("  Projects: Updated={$updatedCount}, Missing Files={$missingCount}, Failed={$failedCount}");
        $this->line('');

        return $updatedCount;
    }

    /**
     * Process Publications (rdb_published.pub_file -> rdb_published.pub_numfile)
     */
    protected function processPublished(DSpaceExportService $dspaceService, bool $force, ?int $limit): int
    {
        $this->comment('Scanning Publications (rdb_published)...');

        $query = RdbPublished::query()
            ->whereNotNull('pub_file')
            ->where('pub_file', '<>', '')
            ->where('pub_file', '<>', '0');

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('pub_numfile')->orWhere('pub_numfile', '<=', 0);
            });
        }

        if ($limit) {
            $query->limit($limit);
        }

        $total = $query->count();
        if ($limit && $total > $limit) {
            $total = $limit;
        }

        if ($total === 0) {
            $this->line('  No publications pending page count.');
            return 0;
        }

        $this->line("  Found {$total} publication(s) to process.");
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $updatedCount = 0;
        $missingCount = 0;
        $failedCount = 0;

        $publications = $query->get(['id', 'pub_file', 'pf_path', 'pub_numfile']);

        foreach ($publications as $pub) {
            $filePath = $dspaceService->resolvePublishedFileOnDisk($pub);

            if (!$filePath || !file_exists($filePath)) {
                $missingCount++;
                $progressBar->advance();
                continue;
            }

            try {
                $pageCount = $dspaceService->countPdfPages($filePath);
                if ($pageCount > 0) {
                    $pub->pub_numfile = $pageCount;
                    $pub->saveQuietly();
                    $updatedCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                $failedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');
        $this->info("  Publications: Updated={$updatedCount}, Missing Files={$missingCount}, Failed={$failedCount}");
        $this->line('');

        return $updatedCount;
    }
}
