<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RdbProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendAuditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'rdbv2',
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');
    }

    public function test_backend_routes_are_accessible_by_admin()
    {
        $admin = User::find(129);
        if (!$admin) {
            $this->markTestSkipped('No admin user found.');
        }

        $routes = [
            'backend.site.index',
            'backend.rbac.index',
            'backend.rbac.create',
            'backend.user.index',
            'backend.user.create',
            'backend.rdb_project.index',
            'backend.rdb_project.export',
            'backend.rdb_researcher.index',
            'backend.rdb_researcher.export',
            'backend.rdb_published.index',
            'backend.rdb_dip.index',
            'backend.rdbprojectutilize.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get(route($route));
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 302]),
                "Route {$route} failed with status {$response->getStatusCode()}"
            );
        }

        // Test Project Show Tabbed View
        $project = RdbProject::first();
        if ($project) {
            $showResponse = $this->actingAs($admin)->get(route('backend.rdb_project.show', $project->pro_id));
            $this->assertEquals(200, $showResponse->getStatusCode());
            $showResponse->assertSee('tab-details');
            $showResponse->assertSee('tab-researchers');
            $showResponse->assertSee('tab-files');
            $showResponse->assertSee('tab-related');
        }

        // Test RBAC Edit & Update
        $rbacResponse = $this->actingAs($admin)->get(route('backend.rbac.edit', 'The Creator'));
        $this->assertEquals(200, $rbacResponse->getStatusCode());
        $rbacResponse->assertSee('The Creator');

        $updateResponse = $this->actingAs($admin)->put(route('backend.rbac.update', 'The Creator'), [
            'description' => 'Super Administrator (Full System Access)',
        ]);
        $this->assertEquals(302, $updateResponse->getStatusCode());

        // Test User Edit & Show
        $userEditResponse = $this->actingAs($admin)->get(route('backend.user.edit', $admin->id));
        $this->assertEquals(200, $userEditResponse->getStatusCode());
        $userEditResponse->assertSee($admin->username);

        $userShowResponse = $this->actingAs($admin)->get(route('backend.user.show', $admin->id));
        $this->assertEquals(200, $userShowResponse->getStatusCode());
        $userShowResponse->assertSee($admin->username);

        // Test User Update via PUT
        $userUpdateResponse = $this->actingAs($admin)->put(route('backend.user.update', $admin->id), [
            'username' => $admin->username,
            'email' => $admin->email,
            'role' => 'The Creator',
            'status' => $admin->status,
            'researcher_id' => $admin->researcher_id,
        ]);
        $this->assertEquals(302, $userUpdateResponse->getStatusCode());

        // Test RBAC Permission Matrix (Approach 2)
        $permViewResponse = $this->actingAs($admin)->get(route('backend.rbac.permissions', 'Admin'));
        $this->assertEquals(200, $permViewResponse->getStatusCode());
        $permViewResponse->assertSee('Permission Matrix');
        $permViewResponse->assertSee('RdbProject.View');

        $permUpdateResponse = $this->actingAs($admin)->put(route('backend.rbac.update_permissions', 'Admin'), [
            'permissions' => [
                'RdbProject.View',
                'RdbProject.Create',
                'RdbResearcher.View',
                'RdbPublished.View',
                'RdbDip.View',
                'RdbProjectUtilize.View',
                'User Manager',
            ]
        ]);
        $this->assertEquals(302, $permUpdateResponse->getStatusCode());

        // Test dynamic permission check on user model
        $this->assertTrue($admin->hasPermission('RdbProject.View'));
        $this->assertTrue($admin->hasPermission('User Manager'));
        $this->assertTrue($admin->hasPermission(['NonExistent', 'RdbProject.View']));
    }

    public function test_dynamic_role_permission_assignment()
    {
        $roleName = 'Test Role ' . time();
        $role = \App\Models\AuthItem::create([
            'name' => $roleName,
            'type' => 1,
            'description' => 'Role for dynamic testing',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        \App\Services\RbacService::syncRolePermissions($roleName, ['RdbProject.View', 'RdbProject.Create']);
        $assigned = \App\Services\RbacService::getAssignedPermissions($roleName);

        $this->assertContains('RdbProject.View', $assigned);
        $this->assertContains('RdbProject.Create', $assigned);
        $this->assertNotContains('RdbProject.Delete', $assigned);

        // Clean up
        \App\Models\AuthItemChild::where('parent', $roleName)->delete();
        $role->delete();
    }
}
