<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FacebookConferenceSyncService;

class SyncFacebookConferences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdb:sync-facebook-conferences {--limit=8 : Number of posts to inspect}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync conference announcements from Facebook page into ResearchConference';

    /**
     * Execute the console command.
     */
    public function handle(FacebookConferenceSyncService $service)
    {
        $limit = (int) $this->option('limit');

        $this->info("Starting Facebook conferences sync (Limit: {$limit})...");

        $stats = $service->sync($limit, function ($message) {
            $this->line("  > {$message}");
        });

        $this->newLine();
        $this->info("Facebook sync completed: " . ($stats['message'] ?: 'Done'));
        $this->table(
            ['Status', 'Count'],
            [
                ['Synced / Created', $stats['synced']],
                ['Skipped (Existing)', $stats['skipped']],
                ['Failed', $stats['failed']],
            ]
        );

        return Command::SUCCESS;
    }
}
