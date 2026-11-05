<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ConvertNewsImagesToWebp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdb:convert-news-webp
                            {--module=all : Module to process (all, news, conference)}
                            {--force : Force reconvert even if already .webp}
                            {--limit= : Limit number of records to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backward compatibility command - delegates to rdb:convert-images-webp';

    public function handle(): int
    {
        return $this->call('rdb:convert-images-webp', [
            '--module' => $this->option('module') ?: 'all',
            '--force' => (bool) $this->option('force'),
            '--limit' => $this->option('limit'),
        ]);
    }
}
