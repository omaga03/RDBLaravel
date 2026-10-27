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

        $this->getJson('/api/v1/master/utilize-types')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'index']]]);

        $this->getJson('/api/v1/master/dip-types')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name']]]);
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

    public function test_v1_utilizations_endpoints(): void
    {
        // 1. Unauthenticated request must return 401 Unauthorized
        $this->getJson('/api/v1/utilizations')->assertStatus(401);

        // 2. Authenticated with Sanctum Bearer Token
        $admin = User::find(129);
        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson('/api/v1/utilizations?dep=2');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'project_id',
                        'project',
                        'fiscal_year',
                        'utilize_date',
                        'target_organization',
                        'target_address',
                        'location',
                        'utilize_types',
                        'detail',
                        'budget',
                        'workflow_status',
                    ]
                ],
                'links',
                'meta'
            ]);

        // 3. Test alias endpoint /api/v1/project-utilizations
        $this->getJson('/api/v1/project-utilizations')
            ->assertStatus(200);

        // 4. Test Single Item Show
        $firstId = $response->json('data.0.id');
        if ($firstId) {
            $this->getJson("/api/v1/utilizations/{$firstId}")
                ->assertStatus(200)
                ->assertJsonPath('data.id', (string) $firstId);

            $this->getJson("/api/v1/project-utilizations/{$firstId}")
                ->assertStatus(200)
                ->assertJsonPath('data.id', (string) $firstId);
        }
    }

    public function test_v1_intellectual_properties_endpoints(): void
    {
        // 1. Unauthenticated request must return 401 Unauthorized
        $this->getJson('/api/v1/intellectual-properties')->assertStatus(401);

        // 2. Authenticated with Sanctum Bearer Token
        $admin = User::find(129);
        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson('/api/v1/intellectual-properties');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'ip_title',
                        'ip_type' => ['id', 'name'],
                        'registration_number',
                        'status',
                        'conclusion',
                        'workflow_status',
                    ]
                ],
                'links',
                'meta'
            ]);

        // 3. Test alias endpoint /api/v1/dips
        $this->getJson('/api/v1/dips')
            ->assertStatus(200);

        // 4. Test Single Item Show
        $firstId = $response->json('data.0.id');
        if ($firstId) {
            $this->getJson("/api/v1/intellectual-properties/{$firstId}")
                ->assertStatus(200)
                ->assertJsonPath('data.id', (string) $firstId);

            $this->getJson("/api/v1/dips/{$firstId}")
                ->assertStatus(200)
                ->assertJsonPath('data.id', (string) $firstId);
        }
    }

    public function test_v1_auth_me_and_read_only_enforcement(): void
    {
        $admin = User::find(129) ?? User::factory()->create();
        Sanctum::actingAs($admin, ['*']);

        // 1. Auth Me (GET is permitted)
        $this->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('user.id', (string) $admin->id);

        // 2. Mutating requests (POST, PUT, DELETE) on data endpoints are disallowed (405 Method Not Allowed)
        $this->postJson('/api/v1/projects', [
            'title_th' => 'โครงการทดสอบบล็อก POST',
            'fiscal_year' => 2568,
        ])
        ->assertStatus(405);

        $this->putJson('/api/v1/projects/1', [
            'title_th' => 'โครงการทดสอบบล็อก PUT',
        ])
        ->assertStatus(405);

        $this->deleteJson('/api/v1/projects/1')
            ->assertStatus(405);
    }

    public function test_researchers_and_users_restricted_to_get_methods_only(): void
    {
        $regularUser = User::where('id', '!=', 129)->first();
        $this->assertNotNull($regularUser);
        $this->assertFalse($regularUser->isSuperAdmin());

        Sanctum::actingAs($regularUser, ['*']);

        // 1. GET requests are allowed for users with Bearer Token
        $this->getJson('/api/v1/projects')
            ->assertStatus(200);

        $this->getJson('/api/v1/master/departments')
            ->assertStatus(200);

        $this->getJson('/api/v1/auth/me')
            ->assertStatus(200);

        // 2. Mutating requests (POST, PUT, DELETE) are disallowed with 405 Method Not Allowed
        $this->postJson('/api/v1/projects', [
            'title_th' => 'โครงการทดสอบโดยผู้ใช้ทั่วไป',
            'fiscal_year' => 2568,
        ])
        ->assertStatus(405);

        $this->putJson('/api/v1/projects/1', [
            'title_th' => 'แก้ไขโครงการโดยผู้ใช้ทั่วไป',
        ])
        ->assertStatus(405);

        $this->deleteJson('/api/v1/projects/1')
        ->assertStatus(405);
    }

    public function test_token_issuance_via_api_is_blocked_and_must_be_issued_by_superadmin(): void
    {
        // 1. Calling /api/v1/auth/token returns 403 Forbidden explaining Super Admin requirement
        $this->postJson('/api/v1/auth/token', [
            'username' => 'testuser',
            'password' => 'password123',
        ])
        ->assertStatus(403)
        ->assertJsonPath('error', 'TokenIssuanceRestrictedToSuperAdmin');

        // 2. Calling alias /api/v1/login also returns 403 Forbidden
        $this->postJson('/api/v1/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ])
        ->assertStatus(403)
        ->assertJsonPath('error', 'TokenIssuanceRestrictedToSuperAdmin');
    }

    public function test_v1_auth_logout_revokes_current_token(): void
    {
        $user = User::first();
        $this->assertNotNull($user);

        // 1. Create a token for the user
        $tokenModel = $user->createToken('logout-verification-token', ['*']);
        $plainToken = $tokenModel->plainTextToken;

        // 2. Calling auth/me with this token succeeds
        $this->withToken($plainToken)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200);

        // 3. Calling auth/logout with this token successfully revokes/deletes it
        $this->withToken($plainToken)
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(200)
            ->assertJsonPath('message', 'ออกจากระบบและเพิกถอน Token สำเร็จ');

        // 4. Token is confirmed deleted from the database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenModel->accessToken->id,
        ]);

        // 5. Reset application auth state and verify subsequent request with the revoked token returns 401 Unauthorized
        app('auth')->forgetGuards();
        $this->withToken($plainToken)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }
}
