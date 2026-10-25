<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NriisSyncService;

class SyncNriisNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdb:sync-nriis-news {--limit=50 : Number of news items to fetch} {--force : Re-sync existing news items}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync research funding news from NRIIS API into ResearchNews';

    /**
     * Execute the console command.
     */
    public function handle(NriisSyncService $service)
    {
        $limit = (int) $this->option('limit');
        $force = (bool) $this->option('force');

        $this->info("Starting NRIIS news sync (Limit: {$limit}, Force: " . ($force ? 'yes' : 'no') . ")...");

        $stats = $service->sync($limit, $force, function ($message) {
            $this->line("  > {$message}");
        });

        $this->newLine();
        $this->info("NRIIS sync completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Synced / Updated', $stats['synced']],
                ['Skipped (Existing)', $stats['skipped']],
                ['Failed', $stats['failed']],
            ]
        );

        return Command::SUCCESS;
    }
}
