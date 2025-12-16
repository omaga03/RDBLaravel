<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateProjectDepcat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:update-project-depcat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update rdb_project.depcat_id based on primary researcher linked via rdb_project_work';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projects = \App\Models\RdbProject::all();
        $this->info("Found start {$projects->count()} projects.");
        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        $updatedCount = 0;

        foreach ($projects as $project) {
            // Find valid work record: position 1 or 2, sorted by position ASC (1 first)
            $work = \App\Models\RdbProjectWork::where('pro_id', $project->pro_id)
                ->whereIn('position_id', [1, 2])
                ->orderBy('position_id', 'asc')
                ->with('researcher')
                ->first();

            if ($work && $work->researcher && $work->researcher->depcat_id) {
                // Determine if update is needed
                if ($project->depcat_id != $work->researcher->depcat_id) {
                    $project->depcat_id = $work->researcher->depcat_id;
                    $project->save();
                    $updatedCount++;
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Update completed. {$updatedCount} projects updated.");
    }
}
