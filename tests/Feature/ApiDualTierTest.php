<?php

namespace Tests\Feature;

use App\Models\RdbDepartment;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\RdbResearcher;
use App\Models\RdbYear;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiDualTierTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'rdbpcru',
            'database.connections.mysql.password' => '',
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');
        \Illuminate\Support\Facades\DB::reconnect('mysql');
    }

    /**
     * Tier 1: Legacy Compatibility Endpoints
     */
    public function test_legacy_project_json_endpoints(): void
    {
        $response = $this->getJson('/rdb/project/jsonprojectall?dep=2');
        $response->assertStatus(200);
        $this->assertIsArray($response->json());

        $response2 = $this->getJson('/rdbproject/jsonprojectall?dep=2&year=2567');
        $response2->assertStatus(200);
        $this->assertIsArray($response2->json());

        // Field contract check against Document 2
        if (!empty($response2->json())) {
            $item = $response2->json()[0];
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('project_code', $item);
            $this->assertArrayHasKey('fiscal_year', $item);
            $this->assertArrayHasKey('budget_amount', $item);
            $this->assertArrayHasKey('status_label', $item);
            $this->assertArrayHasKey('title_th', $item);
            $this->assertArrayHasKey('funding_source', $item);
            $this->assertArrayHasKey('department_name', $item);
            $this->assertArrayHasKey('research_team', $item);
        }
    }

    public function test_legacy_publication_json_endpoints(): void
    {
        $response = $this->getJson('/rdb/published/jsonpublisheddataall?dep=6');
        $response->assertStatus(200);
        $this->assertIsArray($response->json());

        $response2 = $this->getJson('/rdbpublished/jsonpublisheddataall?dep=6&year=2568');
        $response2->assertStatus(200);
        $this->assertIsArray($response2->json());

        // Field contract check against Document 2
        if (!empty($response2->json())) {
            $item = $response2->json()[0];
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('project_id', $item);
            $this->assertArrayHasKey('year_name', $item);
            $this->assertArrayHasKey('pubtypesubgroup', $item);
            $this->assertArrayHasKey('publication_title', $item);
            $this->assertArrayHasKey('authors', $item);
            $this->assertArrayHasKey('department_id', $item);
            $this->assertArrayHasKey('department_nameTH', $item);
            $this->assertArrayHasKey('journal_source', $item);
        }
    }

    public function test_pmis_portfolio_endpoints(): void
    {
        $response = $this->getJson('/rdb/researcher/datagetresearchbypmis?pmis=655154');
        $response->assertStatus(200);

        $response2 = $this->getJson('/rdbresearcher/datagetpublicationbypmis?pmis=656219');
        $response2->assertStatus(200);

        $response3 = $this->getJson('/api/pmis/research?pmis=655154');
        $response3->assertStatus(200);

        $response4 = $this->getJson('/api/pmis/publication?pmis=656219');
        $response4->assertStatus(200);
    }

    /**
     * Tier 2: Modern RESTful API v1 Endpoints (All Require Sanctum Bearer Token)
     */
    public function test_v1_master_data(): void
    {
        // 1. Unauthenticated request must return 401 Unauthorized
        $this->getJson('/api/v1/master/departments')->assertStatus(401);

        // 2. Authenticated with Sanctum Bearer Token returns 200 OK
        $admin = User::find(129);
        Sanctum::actingAs($admin, ['*']);

        $this->getJson('/api/v1/master/departments')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name_th', 'code']]]);

        $this->getJson('/api/v1/master/years')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'year_name']]]);

        $this->getJson('/api/v1/master/project-types')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'sub_types']]]);
    }

    public function test_v1_projects_index_and_show(): void
    {
        // 1. Unauthenticated request must return 401 Unauthorized
        $this->getJson('/api/v1/projects')->assertStatus(401);

        // 2. Authenticated with Sanctum Bearer Token
        $admin = User::find(129);
        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson('/api/v1/projects?dep=2&year=2567');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'project_code',
                        'fiscal_year',
                        'budget_amount',
                        'status_label',
                        'title_th',
                        'funding_source',
                        'funding_type',
                        'department_id',
                        'department_name',
                        'research_team',
                    ]
                ],
                'links',
                'meta'
            ]);

        $firstId = $response->json('data.0.id');
        if ($firstId) {
            $this->getJson("/api/v1/projects/{$firstId}")
                ->assertStatus(200)
                ->assertJsonPath('data.id', (string) $firstId);
        }
    }

    public function test_v1_publications_index_and_show(): void
    {
        // 1. Unauthenticated request must return 401 Unauthorized
        $this->getJson('/api/v1/publications')->assertStatus(401);

        // 2. Authenticated with Sanctum Bearer Token
        $admin = User::find(129);
        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson('/api/v1/publications?dep=6&year=2568');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'project_id',
                        'year_name',
                        'pubtypesubgroup',
                        'publication_title',
                        'authors',
                        'department_id',
                        'department_nameTH',
                    ]
                ],
                'links',
                'meta'
            ]);

        $firstId = $response->json('data.0.id');
        if ($firstId) {
            $this->getJson("/api/v1/publications/{$firstId}")
                ->assertStatus(200)
                ->assertJsonPath('data.id', (string) $firstId);
        }
    }

    public function test_v1_researcher_portfolio(): void
    {
        // 1. Unauthenticated request must return 401 Unauthorized
        $this->getJson('/api/v1/researchers/656219/portfolio')->assertStatus(401);

        // 2. Authenticated with Sanctum Bearer Token
        $admin = User::find(129);
        Sanctum::actingAs($admin, ['*']);

        $this->getJson('/api/v1/researchers/656219/portfolio')
            ->assertStatus(200)
            ->assertJsonStructure([
                'researcher' => ['id', 'teacher_code', 'first_name', 'last_name', 'full_name'],
                'projects_count',
                'publications_count',
                'projects',
                'publications'
            ]);
    }

    public function test_v1_auth_me_and_crud_workflow(): void
    {
        $admin = User::find(129) ?? User::factory()->create();
        Sanctum::actingAs($admin, ['*']);

        // 1. Auth Me
        $this->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('user.id', (string) $admin->id);

        // 2. FormRequest validation: Team contribution > 100% must return 422
        $invalidPayload = [
            'title_th' => 'โครงการทดสอบสัดส่วนเกิน 100%',
            'fiscal_year' => 2568,
            'budget_amount' => 50000,
            'department_id' => 2,
            'research_team' => [
                ['researcher_id' => 4, 'contribution_percentage' => 70],
                ['researcher_id' => 5, 'contribution_percentage' => 45], // Sum = 115%
            ]
        ];

        $this->postJson('/api/v1/projects', $invalidPayload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['research_team']);

        // 3. Valid creation: Team contribution <= 100%
        $validPayload = [
            'title_th' => 'โครงการทดสอบระบบ RESTful API v1 อัตโนมัติ (Feature Test)',
            'title_en' => 'Automated Test RESTful Project v1 Feature Test',
            'fiscal_year' => 2568,
            'budget_amount' => 120000.00,
            'department_id' => 2,
            'abstract' => 'บทคัดย่อทดสอบระบบ API Integration',
            'keywords' => 'API, Sanctum, Test',
            'start_date' => '2025-10-01',
            'end_date' => '2026-09-30',
            'research_team' => [
                ['researcher_id' => 4, 'contribution_percentage' => 60, 'position_id' => 1],
                ['researcher_id' => 5, 'contribution_percentage' => 40, 'position_id' => 2],
            ]
        ];

        $createResponse = $this->postJson('/api/v1/projects', $validPayload);
        $createResponse->assertStatus(201)
            ->assertJsonPath('data.title_th', $validPayload['title_th']);

        $createdId = $createResponse->json('data.id');
        $this->assertNotNull($createdId);

        // 4. Update project
        $this->putJson("/api/v1/projects/{$createdId}", [
            'title_th' => 'โครงการทดสอบระบบ RESTful API v1 (แก้ไขโดย Feature Test)',
            'budget_amount' => 180000.00,
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.budget_amount', '180000.00');

        // 5. Delete project (soft delete)
        $this->deleteJson("/api/v1/projects/{$createdId}")
            ->assertStatus(200)
            ->assertJsonPath('message', 'ลบโครงการวิจัยสำเร็จ');
    }
}
