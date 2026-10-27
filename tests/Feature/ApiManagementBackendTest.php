<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApiManagementBackendTest extends TestCase
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

    public function test_admin_can_access_api_management_dashboard(): void
    {
        $admin = User::find(129);
        $this->assertNotNull($admin, 'Admin User 129 must exist');

        $response = $this->actingAs($admin)->get(route('backend.api_management.index'));
        $response->assertStatus(200);
        $response->assertSee('ศูนย์บริหารจัดการบริการ API และ Bearer Token');
        $response->assertSee('จัดการ Bearer Tokens');
        $response->assertSee('ตรวจดู Endpoint &amp; ทดสอบเรียลไทม์', false);
        $response->assertSee('คู่มือการเชื่อมต่อสำหรับนักพัฒนา');
        $response->assertSee('ออก Bearer Token ใหม่');
    }

    public function test_admin_can_create_bearer_token_for_user(): void
    {
        $admin = User::find(129);
        $this->assertNotNull($admin);

        $payload = [
            'user_id' => $admin->id,
            'name' => 'Automated Integration Token',
            'expires_in_days' => 30,
            'abilities' => ['project:read', 'publication:read'],
        ];

        $response = $this->actingAs($admin)
            ->post(route('backend.api_management.tokens.create'), $payload);

        $response->assertRedirect(route('backend.api_management.index'));
        $response->assertSessionHas('newly_generated_token');
        $response->assertSessionHas('success');

        $newTokenInfo = session('newly_generated_token');
        $this->assertNotEmpty($newTokenInfo['token']);
        $this->assertEquals('Automated Integration Token', $newTokenInfo['name']);

        // Verify token exists in database
        $tokenInDb = PersonalAccessToken::where('name', 'Automated Integration Token')
            ->where('tokenable_id', $admin->id)
            ->first();

        $this->assertNotNull($tokenInDb);
        $this->assertTrue($tokenInDb->can('project:read'));
        $this->assertFalse($tokenInDb->can('project:write'));
    }

    public function test_admin_can_revoke_bearer_token(): void
    {
        $admin = User::find(129);
        $this->assertNotNull($admin);

        // Create temporary token
        $token = $admin->createToken('Token To Revoke', ['*']);
        $tokenId = $token->accessToken->id;

        $response = $this->actingAs($admin)
            ->delete(route('backend.api_management.tokens.revoke', $tokenId));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_live_tester_endpoint_proxy(): void
    {
        $admin = User::find(129);
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)
            ->postJson(route('backend.api_management.test_endpoint'), [
                'path' => '/api/v1/master/departments',
                'method' => 'GET',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('status_code', 200);

        $this->assertArrayHasKey('duration_ms', $response->json());
        $this->assertArrayHasKey('data', $response->json());
        $this->assertAuthenticatedAs($admin);

        // Verify that testing a legacy web-middleware endpoint also preserves admin authentication
        $legacyResponse = $this->actingAs($admin)
            ->postJson(route('backend.api_management.test_endpoint'), [
                'path' => '/rdbproject/jsonprojectall?dep=2',
                'method' => 'GET',
            ]);

        $legacyResponse->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_superadmin_cannot_create_or_revoke_token(): void
    {
        $regularUser = User::where('id', '!=', 129)->first();
        $this->assertNotNull($regularUser);

        // Attempt to create token
        $response = $this->actingAs($regularUser)
            ->post(route('backend.api_management.tokens.create'), [
                'user_id' => $regularUser->id,
                'name' => 'Unauthorized Token',
            ]);
        $response->assertStatus(403);

        // Attempt to revoke token
        $response2 = $this->actingAs($regularUser)
            ->delete(route('backend.api_management.tokens.revoke', 1));
        $response2->assertStatus(403);
    }

    public function test_user_without_token_is_blocked_from_api_dashboard(): void
    {
        $user = User::where('id', '!=', 129)
            ->whereDoesntHave('tokens')
            ->first();
        
        if (!$user) {
            $user = User::factory()->create();
        }

        $this->actingAs($user)
            ->get(route('backend.api_management.index'))
            ->assertStatus(403);
    }

    public function test_user_with_token_can_access_endpoints_and_guide_tabs_only(): void
    {
        $user = User::where('id', '!=', 129)->first();
        $this->assertNotNull($user);

        // Issue a token to this user
        $user->createToken('Research Sync Token');

        $response = $this->actingAs($user)->get(route('backend.api_management.index'));
        $response->assertStatus(200);

        // Can see Endpoints and Developer Guide
        $response->assertSee('ตรวจดู Endpoint &amp; ทดสอบเรียลไทม์', false);
        $response->assertSee('คู่มือการเชื่อมต่อสำหรับนักพัฒนา');

        // Cannot see Token Management tab or issue token button
        $response->assertDontSee('ออก Bearer Token ใหม่');
        $response->assertDontSee('id="createTokenModal"', false);
    }
}
