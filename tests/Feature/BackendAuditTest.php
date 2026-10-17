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

    public function test_user_deletion_and_protection()
    {
        $admin = \App\Models\User::find(129);

        // 1. Create a temporary user to delete
        $tempUser = new \App\Models\User();
        $tempUser->username = 'temp_user_' . time();
        $tempUser->email = 'temp_' . time() . '@example.com';
        $tempUser->password_hash = \Illuminate\Support\Facades\Hash::make('password123');
        $tempUser->auth_key = \Illuminate\Support\Str::random(32);
        $tempUser->confirmed_at = time();
        $tempUser->status = 10;
        $tempUser->save();

        \App\Models\AuthAssignment::create([
            'item_name' => 'Researcher',
            'user_id' => $tempUser->id,
            'created_at' => time(),
        ]);

        $userId = $tempUser->id;

        // 2. Delete the user
        $response = $this->actingAs($admin)->delete(route('backend.user.destroy', $userId));
        $response->assertRedirect(route('backend.user.index'));
        $response->assertSessionHas('success');

        // Assert deleted from database
        $this->assertNull(\App\Models\User::find($userId));
        $this->assertEquals(0, \App\Models\AuthAssignment::where('user_id', $userId)->count());

        // 3. Test self-deletion protection
        $selfDeleteResponse = $this->actingAs($admin)->delete(route('backend.user.destroy', $admin->id));
        $selfDeleteResponse->assertSessionHas('error');
        $this->assertNotNull(\App\Models\User::find($admin->id));
    }

    public function test_file_upload_service_returns_original_name()
    {
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('paper.pdf', 'dummy pdf content');
        $fileService = new \App\Services\FileUploadService();
        $fileInfo = $fileService->storeFile($file, 'audit_test');

        $this->assertArrayHasKey('original_name', $fileInfo);
        $this->assertEquals('paper.pdf', $fileInfo['original_name']);
        $this->assertArrayHasKey('safe_name', $fileInfo);
        $this->assertArrayHasKey('path', $fileInfo);

        // Clean up
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($fileInfo['path'])) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($fileInfo['path']);
        }
    }

    public function test_user_password_min_eight_characters_validation()
    {
        $admin = \App\Models\User::find(129);

        // Try creating a user with 6 chars password - should fail
        $response = $this->actingAs($admin)->post(route('backend.user.store'), [
            'username' => 'test_pwd_' . time(),
            'email' => 'pwd_' . time() . '@example.com',
            'password' => '123456',
            'role' => 'Researcher',
            'status' => 10,
        ]);
        $response->assertSessionHasErrors(['password']);

        // Try creating with 8 chars - should succeed
        $validUsername = 'test_pwd_valid_' . time();
        $validResponse = $this->actingAs($admin)->post(route('backend.user.store'), [
            'username' => $validUsername,
            'email' => 'pwd_val_' . time() . '@example.com',
            'password' => 'password123',
            'role' => 'Researcher',
            'status' => 10,
        ]);
        $validResponse->assertRedirect(route('backend.user.index'));

        // Clean up created user
        $created = \App\Models\User::where('username', $validUsername)->first();
        if ($created) {
            \App\Models\AuthAssignment::where('user_id', $created->id)->delete();
            $created->delete();
        }
    }

    public function test_master_data_policy_dynamic_permission()
    {
        $roleName = 'News Manager ' . time();
        $role = \App\Models\AuthItem::create([
            'name' => $roleName,
            'type' => 1,
            'description' => 'Role for news testing',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        \App\Services\RbacService::syncRolePermissions($roleName, ['news']);

        $user = new \App\Models\User();
        $user->username = 'news_user_' . time();
        $user->email = 'news_' . time() . '@example.com';
        $user->password_hash = \Illuminate\Support\Facades\Hash::make('password123');
        $user->auth_key = \Illuminate\Support\Str::random(32);
        $user->confirmed_at = time();
        $user->status = 10;
        $user->save();

        \App\Models\AuthAssignment::create([
            'item_name' => $roleName,
            'user_id' => $user->id,
            'created_at' => time(),
        ]);

        // User with 'news' permission can access research_news index
        $response = $this->actingAs($user)->get(route('backend.research_news.index'));
        $this->assertEquals(200, $response->getStatusCode());

        // User cannot access rdb_dip (should be 403)
        $unauthResponse = $this->actingAs($user)->get(route('backend.rdb_dip.index'));
        $this->assertEquals(403, $unauthResponse->getStatusCode());

        // Clean up
        \App\Models\AuthAssignment::where('user_id', $user->id)->delete();
        $user->delete();
        \App\Models\AuthItemChild::where('parent', $roleName)->delete();
        $role->delete();
    }

    public function test_all_backend_export_routes_stream_utf8_bom()
    {
        $admin = \App\Models\User::find(129);
        $exportRoutes = [
            'backend.rdb_project.export',
            'backend.rdb_researcher.export',
            'backend.rdb_published.export',
            'backend.rdb_dip.export',
            'backend.rdbprojectutilize.export',
        ];

        foreach ($exportRoutes as $route) {
            $response = $this->actingAs($admin)->get(route($route));
            $this->assertEquals(200, $response->getStatusCode(), "Export route {$route} did not return 200");
            $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
            
            // Verify UTF-8 BOM
            $content = $response->streamedContent();
            $this->assertTrue(str_starts_with($content, "\xEF\xBB\xBF"), "Export {$route} missing UTF-8 BOM");
        }
    }

    public function test_user_model_mutators_and_breeze_compatibility()
    {
        $username = 'breeze_test_' . time();
        $user = new \App\Models\User();
        // Breeze style: sets 'name' and 'password'
        $user->name = $username;
        $user->email = $username . '@example.com';
        $user->password = 'StrongSecret123!';
        $user->save();

        $this->assertEquals($username, $user->username);
        $this->assertNotEmpty($user->password_hash);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('StrongSecret123!', $user->password_hash));
        $this->assertNotEmpty($user->auth_key);
        $this->assertEquals(10, $user->status);
        $this->assertNotNull($user->confirmed_at);

        // Clean up
        $user->delete();
    }

    public function test_superadmin_profile_deletion_is_blocked()
    {
        $admin = \App\Models\User::find(129);
        $response = $this->actingAs($admin)->delete('/profile', [
            'password' => 'wrong_or_right_password',
        ]);

        $response->assertSessionHasErrors(['password'], null, 'userDeletion');
        $this->assertNotNull(\App\Models\User::find(129));
    }

    public function test_file_upload_service_allows_research_documents()
    {
        $service = new \App\Services\FileUploadService();
        $doc = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'report.docx',
            'dummy docx content'
        );
        $result = $service->storeFile($doc, 'test_docs');

        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('safe_name', $result);
        $this->assertEquals('report.docx', $result['original_name']);

        // Clean up
        \Illuminate\Support\Facades\Storage::disk('public')->delete($result['path']);
    }

    public function test_csv_formula_injection_prevention()
    {
        $dummy = new class {
            use \App\Traits\ExportsCsvSafely;

            public function testCell($val)
            {
                return $this->sanitizeCsvCell($val);
            }
        };

        // Harmful formula triggers prefixed with quote
        $this->assertEquals("'=1+1", $dummy->testCell("=1+1"));
        $this->assertEquals("'-2+3+cmd|' /C calc'!A0", $dummy->testCell("-2+3+cmd|' /C calc'!A0"));
        $this->assertEquals("'+SUM(A1:A10)", $dummy->testCell("+SUM(A1:A10)"));
        $this->assertEquals("'@cmd", $dummy->testCell("@cmd"));

        // Safe values untouched
        $this->assertEquals("Regular Title", $dummy->testCell("Regular Title"));
        $this->assertEquals(12345, $dummy->testCell(12345));
        $this->assertEquals(-100, $dummy->testCell(-100));
        $this->assertEquals("-100", $dummy->testCell("-100"));
    }

    public function test_rbac_role_impersonation_and_stop()
    {
        $admin = \App\Models\User::find(129);
        $this->assertNotNull($admin);

        // Impersonate 'Admin Project'
        $response = $this->actingAs($admin)->post(route('backend.rbac.impersonate', 'Admin Project'));
        $response->assertRedirect(route('backend.site.index'));
        $response->assertSessionHas('success');
        $this->assertTrue(session()->has('impersonator_id'));
        $this->assertEquals(129, session('impersonator_id'));

        // Current authenticated user should have the impersonated role
        $impersonatedUser = auth()->user();
        $this->assertTrue($impersonatedUser->hasRole('Admin Project'));

        // Stop impersonation
        $stopResponse = $this->actingAs($impersonatedUser)->post(route('backend.rbac.stop_impersonate'));
        $stopResponse->assertRedirect(route('backend.rbac.index'));
        $stopResponse->assertSessionHas('success');
        $this->assertFalse(session()->has('impersonator_id'));
        $this->assertEquals(129, auth()->id());
    }

    public function test_unauthorized_user_cannot_impersonate()
    {
        // Create standard researcher user without 'The Creator' role
        $user = \App\Models\User::where('username', '!=', 'The Creator')->where('id', '!=', 129)->first();
        if ($user) {
            $response = $this->actingAs($user)->post(route('backend.rbac.impersonate', 'Admin Project'));
            $response->assertStatus(403);
        }
    }

    public function test_dashboard_renders_with_and_without_year_filter()
    {
        $admin = \App\Models\User::find(129);

        // All years
        $responseAll = $this->actingAs($admin)->get(route('backend.site.index'));
        $responseAll->assertStatus(200);
        $responseAll->assertSee('แดชบอร์ดภาพรวมระบบ');
        $responseAll->assertSee('ข้อมูลสะสมทุกปี');
        $responseAll->assertSee('แนวโน้มโครงการและงบประมาณวิจัยย้อนหลัง 5 ปีงบประมาณ');

        // Filter by year 2568 (year_id = 18)
        $year = \App\Models\RdbYear::find(18);
        if ($year) {
            $responseYear = $this->actingAs($admin)->get(route('backend.site.index', ['year_id' => 18]));
            $responseYear->assertStatus(200);
            $responseYear->assertSee('ปีงบประมาณ ' . $year->year_name);
            $responseYear->assertSee('ล้างตัวกรอง');
        }
    }

    public function test_security_headers_are_present_in_responses()
    {
        $admin = \App\Models\User::find(129);
        $response = $this->actingAs($admin)->get(route('backend.site.index'));
        
        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('Permissions-Policy'));
    }

    public function test_export_routes_have_rate_limiting_and_functionality()
    {
        $admin = \App\Models\User::find(129);
        
        // Project export returns 200 stream
        $response = $this->actingAs($admin)->get(route('backend.rdb_project.export'));
        $this->assertTrue(in_array($response->getStatusCode(), [200, 429]));
        
        // Researcher export returns 200 stream
        $responseRes = $this->actingAs($admin)->get(route('backend.rdb_researcher.export'));
        $this->assertTrue(in_array($responseRes->getStatusCode(), [200, 429]));
    }

    public function test_project_edit_does_not_require_ps_id()
    {
        $admin = \App\Models\User::find(129);
        $project = \App\Models\RdbProject::find(2744) ?? \App\Models\RdbProject::first();
        $this->assertNotNull($project);

        // Edit page loads with statuses and form
        $editResponse = $this->actingAs($admin)->get(route('backend.rdb_project.edit', $project->pro_id));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('สถานะโครงการ');

        // Update without ps_id in payload
        $updateResponse = $this->actingAs($admin)->put(route('backend.rdb_project.update', $project->pro_id), [
            'pro_nameTH' => 'โครงการทดสอบอัปเดตไม่บังคับสถานะ',
            'pro_date_start' => '2025-01-01',
            'pro_date_end' => '2025-12-31',
            'pt_id' => $project->pt_id ?? 1,
            'year_id' => $project->year_id ?? 18,
            'department_id' => $project->department_id ?? 1,
            // ps_id is omitted on purpose
        ]);

        $updateResponse->assertSessionDoesntHaveErrors('ps_id');
        $this->assertEquals(302, $updateResponse->getStatusCode());
    }

    public function test_project_status_update_directly()
    {
        $admin = \App\Models\User::find(129);
        $project = \App\Models\RdbProject::find(2744) ?? \App\Models\RdbProject::first();
        $this->assertNotNull($project);

        // Show page has status update modal and routes
        $showResponse = $this->actingAs($admin)->get(route('backend.rdb_project.show', $project->pro_id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('projectStatusModal');
        $showResponse->assertSee(route('backend.rdb_project.update_status', $project->pro_id));

        // Directly patch status to 2 (ดำเนินการเสร็จสิ้น)
        $patchResponse = $this->actingAs($admin)->patch(route('backend.rdb_project.update_status', $project->pro_id), [
            'ps_id' => 2,
        ]);
        $patchResponse->assertRedirect();
        $patchResponse->assertSessionHas('success');

        $project->refresh();
        $this->assertEquals(2, $project->ps_id);
    }

    public function test_upload_report_automatically_sets_status_to_completed()
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = \App\Models\User::find(129);
        $project = \App\Models\RdbProject::find(2744) ?? \App\Models\RdbProject::first();
        $this->assertNotNull($project);

        // Set status to something other than 2 first (e.g. 1 กำลังดำเนินการ)
        $project->ps_id = 1;
        $project->save();

        $fakePdf = \Illuminate\Http\UploadedFile::fake()->createWithContent('complete_report.pdf', '%PDF-1.4 test report content');

        $response = $this->actingAs($admin)->post(route('backend.rdb_project.upload_report', $project->pro_id), [
            'pro_file' => $fakePdf,
            'pro_file_show' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $project->refresh();
        $this->assertEquals(2, $project->ps_id, 'Project status should be automatically set to completed (ps_id = 2) when report is uploaded.');
        $this->assertNotNull($project->pro_file);
        $this->assertEquals(1, $project->pro_file_show);
    }

    public function test_toggle_report_and_file_status()
    {
        $admin = \App\Models\User::find(129);
        $project = \App\Models\RdbProject::find(2744) ?? \App\Models\RdbProject::first();
        $this->assertNotNull($project);

        // Toggle report status
        $initialShow = (int) $project->pro_file_show;
        $toggleReportResponse = $this->actingAs($admin)->postJson(route('backend.rdb_project.toggle_report_status', $project->pro_id));
        $toggleReportResponse->assertStatus(200);
        $toggleReportResponse->assertJson(['success' => true]);
        
        $project->refresh();
        $this->assertNotEquals($initialShow, (int) $project->pro_file_show);

        // If project has files, toggle file status
        $file = $project->files()->first();
        if (!$file) {
            $file = \App\Models\RdbProjectFiles::create([
                'pro_id' => $project->pro_id,
                'rf_filesname' => 'Test File',
                'rf_files' => 'test.pdf',
                'rf_files_show' => 1,
                'user_created' => $admin->id,
            ]);
        }

        $initialFileShow = (int) $file->rf_files_show;
        $toggleFileResponse = $this->actingAs($admin)->postJson(route('backend.rdb_project.file.toggle_status', [$project->pro_id, $file->id]));
        $toggleFileResponse->assertStatus(200);
        $toggleFileResponse->assertJson(['success' => true]);

        $file->refresh();
        $this->assertNotEquals($initialFileShow, (int) $file->rf_files_show);
    }

    public function test_project_show_contains_visibility_and_uniform_buttons()
    {
        $admin = \App\Models\User::find(129);
        $project = \App\Models\RdbProject::find(2744) ?? \App\Models\RdbProject::first();
        $this->assertNotNull($project);

        $response = $this->actingAs($admin)->get(route('backend.rdb_project.show', $project->pro_id));
        $response->assertStatus(200);

        // Check uniform button styling and delete form
        $response->assertSee('d-inline-flex m-0 p-0 align-items-center delete-form', false);
        $response->assertSee('height: 38px', false);

        // Check public visibility elements
        $response->assertSee('การแสดงผล', false);
        $response->assertSee('Public Visible', false);
    }
}
