<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RdbProject;
use App\Models\RdbResearcher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class TestSmoke extends Command
{
    protected $signature = 'test:smoke';
    protected $description = 'Run a smoke test to verify critical routes';

    public function handle()
    {
        $this->info('Starting Smoke Test...');

        // 1. Test User Login (Simulation)
        $user = User::first();
        if (!$user) {
            $this->error('No users found!');
            return 1;
        }
        $this->info("Using user: {$user->username} (ID: {$user->id})");
        Auth::login($user);

        // 2. Test Backend Routes
        $backendRoutes = [
            'backend.site.index',
            'backend.rdb_project.index',
            'backend.rdb_project.create',
            'backend.rdb_researcher.index',
            'backend.rdb_researcher.create',
        ];

        foreach ($backendRoutes as $route) {
            $this->testRoute($route);
        }

        // 3. Test Frontend Routes
        $frontendRoutes = [
            'home',
            'frontend.rdbproject.index',
        ];

        foreach ($frontendRoutes as $route) {
            $this->testRoute($route);
        }

        // 4. Test Detail Views (if data exists)
        $project = RdbProject::first();
        if ($project) {
            $this->testRoute('backend.rdb_project.show', ['rdb_project' => $project->pro_id]);
            $this->testRoute('backend.rdb_project.edit', ['rdb_project' => $project->pro_id]);
            $this->testRoute('frontend.rdbproject.show', ['id' => $project->pro_id]);
        } else {
            $this->warn('No projects found to test detail views.');
        }

        $researcher = RdbResearcher::first();
        if ($researcher) {
            $this->testRoute('backend.rdb_researcher.show', ['rdb_researcher' => $researcher->researcher_id]);
            $this->testRoute('backend.rdb_researcher.edit', ['rdb_researcher' => $researcher->researcher_id]);
        } else {
            $this->warn('No researchers found to test detail views.');
        }

        $this->info('Smoke Test Completed.');
        return 0;
    }

    private function testRoute($routeName, $params = [])
    {
        try {
            $url = route($routeName, $params);
            $this->line("Testing: {$routeName} ({$url})");
            
            // Simulate a GET request
            $request = Request::create($url, 'GET');
            $response = app()->handle($request);

            if ($response->getStatusCode() == 200) {
                $this->info("  [PASS] Status 200");
            } else {
                $this->error("  [FAIL] Status " . $response->getStatusCode());
                $this->error("  Response Snippet: " . substr(strip_tags($response->getContent()), 0, 500)); 
            }
        } catch (\Throwable $e) {
            $this->error("  [ERROR] " . $e->getMessage());
            $this->error("  in " . $e->getFile() . ":" . $e->getLine());
            // $this->error($e->getTraceAsString());
        }
    }
}
