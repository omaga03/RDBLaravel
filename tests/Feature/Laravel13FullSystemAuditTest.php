<?php

namespace Tests\Feature;

use App\Models\RdbDip;
use App\Models\RdbProject;
use App\Models\RdbProjectUtilize;
use App\Models\RdbPublished;
use App\Models\RdbResearcher;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Laravel13FullSystemAuditTest extends TestCase
{
    protected ?User $admin = null;
    protected ?User $researcher = null;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'rdbpcru',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');

        // Identify an admin and a researcher user
        $this->admin = User::find(129) ?? User::first();
        $this->researcher = User::whereNotNull('researcher_id')->first() ?? User::where('id', '!=', 129)->first();
    }

    /**
     * 1. Frontend: All Core Public Modules & Views
     */
    public function test_all_frontend_pages_render_successfully_on_laravel_13(): void
    {
        $frontendRoutes = [
            '/',
            '/site',
            '/rdbproject',
            '/rdbpublished',
            '/rdbdip',
            '/rdbprojectutilize',
            '/rdbresearcher',
            '/researchnews',
            '/researchcoferenceinthai',
            '/manuals',
            '/sitemap.xml',
            '/sitemap-projects.xml',
            '/sitemap-publications.xml',
            '/sitemap-researchers.xml',
            '/sitemap-ip.xml',
            '/sitemap-utilizations.xml',
        ];

        foreach ($frontendRoutes as $url) {
            $response = $this->get($url);
            $this->assertContains($response->getStatusCode(), [200, 301, 302], "Failed checking frontend route: {$url}");
        }
    }

    /**
     * 2. Frontend Detail Pages for all 5 Research Pillars
     */
    public function test_all_frontend_detail_pages_with_metadata_on_laravel_13(): void
    {
        // 2.1 Project Detail
        $project = RdbProject::where('data_show', 1)->first();
        if ($project) {
            $res = $this->get("/rdbproject/{$project->pro_id}");
            $res->assertStatus(200);
            $res->assertSee('โครงการวิจัย');
        }

        // 2.2 Published Detail
        $published = RdbPublished::where('data_show', 1)->first();
        if ($published) {
            $res = $this->get("/rdbpublished/{$published->id}");
            $res->assertStatus(200);
        }

        // 2.3 DIP Detail
        $dip = RdbDip::where('data_show', 1)->first();
        if ($dip) {
            $res = $this->get("/rdbdip/{$dip->dip_id}");
            $res->assertStatus(200);
        }

        // 2.4 Utilize Detail
        $utilize = RdbProjectUtilize::where('data_show', 1)->first();
        if ($utilize) {
            $res = $this->get("/rdbprojectutilize/{$utilize->utilize_id}");
            $res->assertStatus(200);
        }

        // 2.5 Researcher Profile
        $researcher = RdbResearcher::first();
        if ($researcher) {
            $res = $this->get("/rdbresearcher/{$researcher->researcher_id}");
            $res->assertStatus(200);
        }
    }

    /**
     * 3. Backend: All Administrative Hubs & Control Centers
     */
    public function test_all_backend_admin_modules_render_successfully_on_laravel_13(): void
    {
        $this->actingAs($this->admin);

        $backendUrls = [
            '/backend',
            '/backend/reports/executive-summary',
            '/backend/rdb_project',
            '/backend/rdb_published',
            '/backend/rdb_dip',
            '/backend/rdbprojectutilize',
            '/backend/rdb_researcher',
            '/backend/research_news',
            '/backend/research_news/reports',
            '/backend/research_news/test-sync',
            '/backend/research_conference',
            '/backend/research_conference/test-sync',
            '/backend/system-backup',
            '/backend/system-scheduler',
            '/backend/api-management',
            '/backend/rbac',
            '/backend/user',
        ];

        foreach ($backendUrls as $url) {
            $response = $this->get($url);
            $this->assertEquals(200, $response->getStatusCode(), "Backend URL failed with status {$response->getStatusCode()}: {$url}");
        }
    }

    /**
     * 4. Multi-Format UTF-8 BOM CSV Exports
     */
    public function test_all_backend_csv_exports_stream_with_utf8_bom_on_laravel_13(): void
    {
        $this->actingAs($this->admin);

        $exportEndpoints = [
            '/backend/rdb_project/export',
            '/backend/rdb_published/export',
            '/backend/rdb_dip/export',
            '/backend/rdbprojectutilize/export',
            '/backend/rdb_researcher/export',
        ];

        foreach ($exportEndpoints as $endpoint) {
            $response = $this->get($endpoint);
            $this->assertEquals(200, $response->getStatusCode(), "Export failed: {$endpoint}");
            $content = $response->streamedContent();
            $this->assertStringStartsWith("\xEF\xBB\xBF", $content, "Missing UTF-8 BOM on {$endpoint}");
        }
    }

    /**
     * 5. Bulk Operations on Laravel 13
     */
    public function test_bulk_operations_hub_functionality_on_laravel_13(): void
    {
        $this->actingAs($this->admin);

        // Bulk Export Projects
        $projects = RdbProject::limit(3)->pluck('pro_id')->toArray();
        if (!empty($projects)) {
            $res = $this->post('/backend/rdb_project/bulk-export', [
                'selected_ids' => $projects,
            ]);
            $res->assertStatus(200);
            $this->assertStringStartsWith("\xEF\xBB\xBF", $res->streamedContent());
        }

        // Bulk Export Publications
        $published = RdbPublished::limit(3)->pluck('id')->toArray();
        if (!empty($published)) {
            $res = $this->post('/backend/rdb_published/bulk-export', [
                'selected_ids' => $published,
            ]);
            $res->assertStatus(200);
            $this->assertStringStartsWith("\xEF\xBB\xBF", $res->streamedContent());
        }
    }

    /**
     * 6. REST API Endpoints & Security Scoping
     */
    public function test_all_api_tiers_on_laravel_13(): void
    {
        // Tier 3: Open Public API
        $resNews = $this->getJson('/api/v1/news');
        $resNews->assertStatus(200);
        $resNews->assertJsonStructure(['data', 'links', 'meta' => ['current_page']]);

        $resConf = $this->getJson('/api/v1/conferences');
        $resConf->assertStatus(200);
        $resConf->assertJsonStructure(['data', 'links', 'meta' => ['current_page']]);

        // Tier 2: PMIS Legacy API
        $resPmisFac = $this->get('/api/pmis/research');
        $this->assertContains($resPmisFac->getStatusCode(), [200, 302]);

        // Tier 1: Private API requires Authentication
        $resPrivate = $this->getJson('/api/v1/auth/me');
        $resPrivate->assertStatus(401);
    }

    /**
     * 7. Artisan CLI Commands Execution on Laravel 13
     */
    public function test_all_custom_artisan_commands_on_laravel_13(): void
    {
        $commands = [
            'rdb:count-pdf-pages' => ['--limit' => 1],
            'rdb:generate-pdf-covers' => ['--limit' => 1],
            'rdb:convert-images-webp' => ['--limit' => 1],
            'rdb:backup' => ['--type' => 'db'],
        ];

        foreach ($commands as $cmd => $params) {
            $code = Artisan::call($cmd, $params);
            $this->assertEquals(0, $code, "Artisan command failed: {$cmd}");
        }
    }
}
