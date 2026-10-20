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
            'backend.settings.access_control',
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
            'pro_file_show' => 0, // 0 = เปิด (Public)
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $project->refresh();
        $this->assertEquals(2, $project->ps_id, 'Project status should be automatically set to completed (ps_id = 2) when report is uploaded.');
        $this->assertNotNull($project->pro_file);
        $this->assertEquals(0, $project->pro_file_show);
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

    public function test_rdbprojectutilize_edit_page_contains_blank_url_and_download_works()
    {
        $admin = \App\Models\User::find(129);
        $utilize = \App\Models\RdbProjectUtilize::find(1036);
        $this->assertNotNull($utilize);

        // Test edit page contains target="_blank" and download route
        $editResponse = $this->actingAs($admin)->get(route('backend.rdbprojectutilize.edit', 1036));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('target="_blank"', false);
        $editResponse->assertSee('เปิดดูไฟล์', false);
        $editResponse->assertSee(route('backend.rdbprojectutilize.download', ['id' => 1036, 'filename' => $utilize->utz_files]), false);

        // Test edit page when old('utz_group') is an array (flashed input) does not throw TypeError htmlspecialchars
        $editResponseWithOldArray = $this->actingAs($admin)
            ->withSession(['_old_input' => ['utz_group' => ['1', '2']]])
            ->get(route('backend.rdbprojectutilize.edit', 1036));
        $editResponseWithOldArray->assertStatus(200);
        $editResponseWithOldArray->assertSee('const selectedIds = ["1","2"];', false);

        // Test download / view endpoint works and returns 200 with inline disposition
        $fileResponse = $this->actingAs($admin)->get(route('backend.rdbprojectutilize.download', ['id' => 1036, 'filename' => $utilize->utz_files]));
        $this->assertStringContainsString('inline', $fileResponse->headers->get('Content-Disposition'));
        $this->assertStringContainsString(basename($utilize->getUtilizeFilePath()), $fileResponse->headers->get('Content-Disposition'));
    }

    public function test_file_upload_security_and_dual_path_accessors()
    {
        // 1. Verify FileUploadService renames to UUID v4
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('research_paper_with_unsafe_name.pdf', 'fake pdf binary content');
        $fileService = new \App\Services\FileUploadService();
        $fileInfo = $fileService->storeFile($file, 'audit_security');

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.pdf$/', $fileInfo['safe_name']);
        $this->assertStringStartsWith('audit_security/', $fileInfo['path']);
        $this->assertEquals('research_paper_with_unsafe_name.pdf', $fileInfo['original_name']);

        // Clean up stored file
        \Illuminate\Support\Facades\Storage::disk('public')->delete($fileInfo['path']);

        // 2. Test Conference model image_url accessor with both new and legacy
        $conf = new \App\Models\ResearchConference();
        $conf->con_path = 'conference/test-uuid.jpg';
        $this->assertStringContainsString('storage/conference/test-uuid.jpg', $conf->image_url);

        $confLegacy = new \App\Models\ResearchConference();
        $confLegacy->con_img = 'conth_12345.jpg';
        $this->assertStringContainsString('storage/uploads/conference/conth_12345.jpg', $confLegacy->image_url);

        // 3. Test News model image_url accessor with both new and legacy
        $news = new \App\Models\ResearchNews();
        $news->news_path = 'news/test-news-uuid.jpg';
        $this->assertStringContainsString('storage/news/test-news-uuid.jpg', $news->image_url);

        $newsLegacy = new \App\Models\ResearchNews();
        $newsLegacy->news_img = 'news_12345.jpg';
        $this->assertStringContainsString('storage/uploads/news/news_12345.jpg', $newsLegacy->image_url);

        // 4. Test Researcher profile_image_url accessor
        $res = new \App\Models\RdbResearcher();
        $res->rf_path = 'researchers/test-res-uuid.jpg';
        $this->assertStringContainsString('storage/researchers/test-res-uuid.jpg', $res->profile_image_url);

        $resLegacy = new \App\Models\RdbResearcher();
        $resLegacy->researcher_picture = 'res_12345.jpg';
        $this->assertStringContainsString('storage/uploads/researchers/res_12345.jpg', $resLegacy->profile_image_url);

        // 5. Test RdbDip default getDipFilePath uses dip_files column
        \Illuminate\Support\Facades\Storage::disk('public')->put('dips/test-dip.pdf', 'dummy');
        $dip = new \App\Models\RdbDip();
        $dip->dip_path = 'dips/test-dip.pdf';
        $this->assertEquals('dips/test-dip.pdf', $dip->getDipFilePath());
        \Illuminate\Support\Facades\Storage::disk('public')->delete('dips/test-dip.pdf');
    }

    public function test_home_and_site_index_renders_research_database_and_not_welcome_page()
    {
        // 1. Root route '/'
        $responseHome = $this->get('/');
        $responseHome->assertStatus(200);
        $responseHome->assertSee('ระบบฐานข้อมูลงานวิจัย');
        $responseHome->assertSee('สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์');
        $responseHome->assertDontSee("Let's get started");

        // 2. '/site' route
        $responseSite = $this->get('/site');
        $responseSite->assertStatus(200);
        $responseSite->assertSee('ระบบฐานข้อมูลงานวิจัย');
        $responseSite->assertSee('สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์');
        $responseSite->assertDontSee("Let's get started");
    }

    public function test_access_control_settings_page_and_update()
    {
        $admin = User::find(129);
        $this->actingAs($admin);

        // 1. GET access control settings page
        $response = $this->get(route('backend.settings.access_control'));
        $response->assertStatus(200);
        $response->assertSee('Google Workspace SSO');
        $response->assertSee('download_policy_full_report');

        // 2. PUT update settings
        $updateResponse = $this->put(route('backend.settings.access_control.update'), [
            'google_login_enabled' => '1',
            'google_client_id' => 'test-client-id.apps.googleusercontent.com',
            'google_allowed_domains' => 'pcru.ac.th',
            'google_auto_register' => '1',
            'standard_login_enabled' => '1',
            'download_policy_full_report' => 'members_only',
            'download_policy_abstract' => 'public',
            'download_policy_attachments' => 'members_only',
            'download_require_login_message' => 'กรุณาเข้าสู่ระบบก่อนเพื่อดาวน์โหลดเอกสารเล่มรายงานวิจัยฉบับสมบูรณ์',
            'download_logging_enabled' => '1',
        ]);

        $updateResponse->assertRedirect(route('backend.settings.access_control'));
        $this->assertEquals('members_only', \App\Models\SystemSetting::get('download_policy_full_report'));
        $this->assertEquals('test-client-id.apps.googleusercontent.com', \App\Models\SystemSetting::get('google_client_id'));
    }

    public function test_login_page_renders_google_sso_and_collapsible_form()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('เข้าสู่ระบบด้วย Google Workspace');
        $response->assertSee('@pcru.ac.th');
        $response->assertSee('เข้าสู่ระบบด้วยชื่อผู้ใช้และรหัสผ่าน');
        $response->assertSee('standardLoginForm');
        $response->assertSee('images/logo.png');
        $response->assertSee('max-height: 84px');
    }

    public function test_register_page_is_google_sso_only_and_blocks_manual_post()
    {
        // 1. Register page loads properly
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('ลงทะเบียนเข้าใช้งานระบบ');
        $response->assertSee('ลงทะเบียนด้วย Google Workspace');
        $response->assertSee('@pcru.ac.th');
        $response->assertSee('เข้าสู่ระบบที่นี่');
        $response->assertDontSee('name="password_confirmation"');

        // 2. Direct POST to register is blocked and redirected with warning
        $postResponse = $this->post(route('register'), [
            'name' => 'Spam Bot',
            'email' => 'bot@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $postResponse->assertRedirect(route('register'));
        $postResponse->assertSessionHas('warning');
    }

    public function test_document_download_policy_enforcement()
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();
        \Illuminate\Support\Facades\Auth::logout();
        \App\Models\SystemSetting::set('download_policy_full_report', 'members_only');

        $project = RdbProject::where('data_show', 1)
            ->where('pro_file_show', 0)
            ->whereNotNull('pro_file')
            ->where('pro_file', '!=', '')
            ->where('pro_file', '!=', '0')
            ->first();

        if ($project) {
            // Unauthenticated guest user attempting to view full report should be redirected to login
            $guestResponse = $this->get(route('frontend.rdbproject.view_report', $project->pro_id));
            $guestResponse->assertRedirect(route('login'));

            // Public show page should display login requirement message or badge
            $showResponse = $this->get(route('frontend.rdbproject.show', $project->pro_id));
            $showResponse->assertStatus(200);
            $showResponse->assertSee('ต้องเข้าสู่ระบบ');
        }
    }

    public function test_pro_file_show_three_states_logic()
    {
        $project = RdbProject::where('data_show', 1)
            ->whereNotNull('pro_file')
            ->where('pro_file', '!=', '')
            ->where('pro_file', '!=', '0')
            ->first();

        $this->assertNotNull($project);
        $originalShow = $project->pro_file_show;

        // State 0 = เปิด (Public)
        $project->pro_file_show = 0;
        $project->save();
        $this->assertTrue($project->isReportOpen());
        $res0 = $this->get(route('frontend.rdbproject.show', $project->pro_id));
        $res0->assertStatus(200);
        $res0->assertSee('รายงานฉบับสมบูรณ์');

        // State 1 = ปิด (Concealed)
        $project->pro_file_show = 1;
        $project->save();
        $this->assertTrue($project->isReportClosed());
        $res1 = $this->get(route('frontend.rdbproject.show', $project->pro_id));
        $res1->assertStatus(200);
        $res1->assertSee('เล่มรายงานวิจัยถูกปกปิด');
        $viewRes1 = $this->get(route('frontend.rdbproject.view_report', $project->pro_id));
        $viewRes1->assertStatus(403);

        // State 2 = ร่าง (Draft)
        $project->pro_file_show = 2;
        $project->save();
        $this->assertTrue($project->isReportDraft());
        $res2 = $this->get(route('frontend.rdbproject.show', $project->pro_id));
        $res2->assertStatus(200);
        $res2->assertSee('เล่มรายงานวิจัยอยู่ในสถานะฉบับร่าง');
        $viewRes2 = $this->get(route('frontend.rdbproject.view_report', $project->pro_id));
        $viewRes2->assertStatus(403);

        // Restore original
        $project->pro_file_show = $originalShow;
        $project->save();
    }

    public function test_research_intelligence_dashboards_render_with_contextual_filters()
    {
        // 1. Overview Dashboard with 5 filters
        $resOverview = $this->get(route('frontend.dashboard.index', [
            'year_id' => 18,
            'department_id' => 2,
            'ps_id' => 2,
            'pt_id' => 1,
            'has_report' => '1',
        ]));
        $resOverview->assertStatus(200);
        $resOverview->assertSee('ตัวกรองข้อมูลสารสนเทศ');
        $resOverview->assertSee('ภาพรวมยุทธศาสตร์งานวิจัย');

        // 2. Projects Dashboard with budget range and faculty
        $resProjects = $this->get(route('frontend.dashboard.projects', [
            'department_id' => 2,
            'budget_range' => '100k-500k',
        ]));
        $resProjects->assertStatus(200);
        $resProjects->assertSee('โครงการวิจัยและงบประมาณ');
        $resProjects->assertSee('budget_range');

        // 3. Publications Dashboard with quality tier & project linkage
        $resPubs = $this->get(route('frontend.dashboard.publications', [
            'quality_group' => 'scopus',
            'has_project' => '1',
        ]));
        $resPubs->assertStatus(200);
        $resPubs->assertSee('ผลงานตีพิมพ์');
        $resPubs->assertSee('quality_group');

        // 4. Utilizations Dashboard with 5 pillars, multi-province, cascading amphoe, QA
        $resUtils = $this->get(route('frontend.dashboard.utilizations', [
            'utz_pillar' => '5',
            'qa_status' => '1',
            'changwat' => ['จ.เพชรบูรณ์'],
            'amphoe' => ['อ.เมืองเพชรบูรณ์', 'อ.หล่มสัก'],
        ]));
        $resUtils->assertStatus(200);
        $resUtils->assertSee('การใช้ประโยชน์');
        $resUtils->assertSee('utz_pillar');
        $resUtils->assertSee('สัดส่วน 5 มิติการนำไปใช้ประโยชน์');
        $resUtils->assertSee('อื่น ๆ');

        // Test multi-province filter without amphoe
        $resMultiProv = $this->get(route('frontend.dashboard.utilizations', [
            'changwat' => ['จ.เพชรบูรณ์', 'จ.พิษณุโลก'],
        ]));
        $resMultiProv->assertStatus(200);

        // Test AJAX endpoint for amphoes by province
        $resAmphoes = $this->get(route('frontend.dashboard.amphoes', [
            'changwat' => 'จ.เพชรบูรณ์',
        ]));
        $resAmphoes->assertStatus(200);

        // 5. Intellectual Property Dashboard with IP category & registration status
        $resIP = $this->get(route('frontend.dashboard.ip', [
            'dipt_id' => 1,
            'reg_status' => 'registered',
        ]));
        $resIP->assertStatus(200);
        $resIP->assertSee('ทรัพย์สินทางปัญญา');
        $resIP->assertSee('reg_status');

        // 6. Researchers Matrix Dashboard with title and profile tier
        $resResearchers = $this->get(route('frontend.dashboard.researchers', [
            'prefix_id' => 1,
            'profile_tier' => 'both',
        ]));
        $resResearchers->assertStatus(200);
        $resResearchers->assertSee('ศักยภาพนักวิจัย');
        $resResearchers->assertSee('profile_tier');
    }

    public function test_user_impersonation_and_stop()
    {
        $admin = User::find(129);
        $targetUser = User::where('id', '!=', 129)->first();
        if (!$admin || !$targetUser) {
            $this->markTestSkipped('Admin or target user not found.');
        }

        // Impersonate
        $res = $this->actingAs($admin)->post(route('backend.user.impersonate', $targetUser->id));
        $res->assertRedirect();
        $this->assertEquals($targetUser->id, session('impersonated_user_id'));
        $this->assertEquals($admin->id, session('impersonator_id'));

        // Check impersonate audit log
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'impersonate',
            'auditable_id' => $targetUser->id,
        ]);

        // Stop impersonating
        $stopRes = $this->actingAs($targetUser)->post(route('backend.user.stop_impersonate'));
        $stopRes->assertRedirect();
        $this->assertNull(session('impersonated_user_id'));
        $this->assertNull(session('impersonator_id'));

        // Check stop impersonate audit log
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'impersonate',
            'auditable_id' => $admin->id,
        ]);
    }

    public function test_scoped_rbac_and_role_switch()
    {
        $admin = User::find(129);
        if (!$admin) {
            $this->markTestSkipped('Admin not found.');
        }

        $scope = \App\Models\UserRoleScope::firstOrCreate([
            'user_id' => $admin->id,
            'role_name' => 'The Creator',
        ], [
            'is_active' => true,
            'notes' => 'Test Scoped Role',
        ]);

        $res = $this->actingAs($admin)->post(route('backend.rbac.switch_scope'), [
            'scope_id' => $scope->id,
        ]);
        $res->assertRedirect();
        $this->assertEquals($scope->id, session('active_role_scope_id'));
    }

    public function test_audit_log_records_diff_and_ui()
    {
        $admin = User::find(129);
        $project = RdbProject::first();
        if (!$admin || !$project) {
            $this->markTestSkipped('Admin or project not found.');
        }

        $originalTitle = $project->pro_nameTH;
        $testTitle = $originalTitle . ' (Audit Log Test)';

        // Update project to trigger Auditable trait
        $this->actingAs($admin);
        $project->pro_nameTH = $testTitle;
        $project->save();

        // Restore original title
        $project->pro_nameTH = $originalTitle;
        $project->save();

        // Check audit_logs table has update record
        $log = \App\Models\AuditLog::where('auditable_type', 'RdbProject')
            ->where('auditable_id', $project->pro_id)
            ->where('event', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);

        // Test Audit Log Index
        $indexRes = $this->actingAs($admin)->get(route('backend.audit_log.index'));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('Audit Trail');

        // Test Audit Log Show (Visual Diff)
        $showRes = $this->actingAs($admin)->get(route('backend.audit_log.show', $log->id));
        $showRes->assertStatus(200);
        $showRes->assertSee('รายละเอียดการเปลี่ยนแปลง');
        $showRes->assertSee('ค่าเดิม');
        $showRes->assertSee('ค่าใหม่');
    }

    public function test_workflow_review_queue_and_status_transitions()
    {
        $admin = User::find(129);
        $project = RdbProject::first();
        if (!$admin || !$project) {
            $this->markTestSkipped('Admin or project not found.');
        }

        // Test Workflow Queue page access
        $queueRes = $this->actingAs($admin)->get(route('backend.workflow.queue'));
        $queueRes->assertStatus(200);
        $queueRes->assertSee('Review Queue');

        // Test Workflow Submit
        $submitRes = $this->actingAs($admin)->post(route('backend.workflow.submit', ['type' => 'project', 'id' => $project->pro_id]));
        $submitRes->assertRedirect();
        $project->refresh();
        $this->assertEquals('pending_review', $project->workflow_status);
        $this->assertNotNull($project->submitted_at);

        // Test Workflow Reject
        $rejectRes = $this->actingAs($admin)->post(route('backend.workflow.reject', ['type' => 'project', 'id' => $project->pro_id]), [
            'remarks' => 'เอกสารแนบไม่ครบถ้วน กรุณาแนบรายงานฉบับสมบูรณ์ใหม่',
        ]);
        $rejectRes->assertRedirect();
        $project->refresh();
        $this->assertEquals('revision_required', $project->workflow_status);
        $this->assertEquals('เอกสารแนบไม่ครบถ้วน กรุณาแนบรายงานฉบับสมบูรณ์ใหม่', $project->rejection_remarks);

        // Test Workflow Approve
        $approveRes = $this->actingAs($admin)->post(route('backend.workflow.approve', ['type' => 'project', 'id' => $project->pro_id]));
        $approveRes->assertRedirect();
        $project->refresh();
        $this->assertEquals('published', $project->workflow_status);
        $this->assertEquals($admin->id, $project->reviewed_by);
        $this->assertNotNull($project->reviewed_at);
        $this->assertNull($project->rejection_remarks);
    }

    public function test_notification_inbox_and_mark_as_read()
    {
        $admin = User::find(129);
        if (!$admin) {
            $this->markTestSkipped('Admin not found.');
        }

        // Create a test notification
        $notificationId = (string) \Illuminate\Support\Str::uuid();
        \Illuminate\Support\Facades\DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\Notifications\ResearchItemSubmittedNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $admin->id,
            'data' => json_encode([
                'title' => 'มีผลงานวิจัยใหม่ส่งเข้ามาตรวจรับ',
                'message' => 'โครงการทดสอบได้รับการส่งเข้าสู่ระบบ',
                'url' => route('backend.workflow.queue'),
                'action_text' => 'ตรวจสอบผลงาน',
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Access notifications index
        $indexRes = $this->actingAs($admin)->get(route('notifications.index'));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('ศูนย์การแจ้งเตือน');
        $indexRes->assertSee('มีผลงานวิจัยใหม่ส่งเข้ามาตรวจรับ');

        // Mark as read
        $readRes = $this->actingAs($admin)->post(route('notifications.read', $notificationId));
        $readRes->assertRedirect(route('backend.workflow.queue'));

        $this->assertNotNull(\Illuminate\Support\Facades\DB::table('notifications')->where('id', $notificationId)->value('read_at'));

        // Mark all as read
        $readAllRes = $this->actingAs($admin)->post(route('notifications.read_all'));
        $readAllRes->assertRedirect();

        // Cleanup
        \Illuminate\Support\Facades\DB::table('notifications')->where('id', $notificationId)->delete();
    }

    public function test_researcher_impersonation()
    {
        $admin = User::find(129);
        $researcher = \App\Models\RdbResearcher::whereHas('user', function ($q) {
            $q->where('id', '!=', 129);
        })->first();

        if (!$admin || !$researcher) {
            $this->markTestSkipped('Admin or linked researcher not found.');
        }

        // Impersonate researcher via endpoint
        $res = $this->actingAs($admin)->post(route('backend.rdb_researcher.impersonate', $researcher->researcher_id));
        $res->assertRedirect(route('backend.site.index'));
        $this->assertEquals($researcher->user->id, session('impersonated_user_id'));
        $this->assertEquals($admin->id, session('impersonator_id'));

        // Check index page renders impersonate button for admin
        $indexRes = $this->actingAs($admin)->get(route('backend.rdb_researcher.index'));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('bi-person-fill-gear');

        // Stop impersonating
        $stopRes = $this->actingAs($researcher->user)->post(route('backend.user.stop_impersonate'));
        $stopRes->assertRedirect();
        $this->assertNull(session('impersonated_user_id'));
        $this->assertNull(session('impersonator_id'));
    }

    public function test_impersonated_researcher_can_view_backend_dashboard()
    {
        $admin = User::find(129);
        $user = User::where('username', '655169')->first();
        if (!$admin || !$user) {
            $this->markTestSkipped('Admin or user 655169 not found.');
        }

        // Impersonate user 655169
        $impersonateRes = $this->actingAs($admin)->post(route('backend.user.impersonate', $user->id));
        $impersonateRes->assertRedirect(route('backend.site.index'));

        // Access backend dashboard as user 655169
        $dashRes = $this->actingAs($user)->get(route('backend.site.index'));
        $dashRes->assertStatus(200);
        $dashRes->assertDontSee('403');
        $dashRes->assertSee('แดชบอร์ดผลงานวิจัยของฉัน');
        $dashRes->assertSee('ชลธิชา ระลึก');
    }

    public function test_researcher_can_access_research_modules_and_create_records()
    {
        $user = User::where('username', '655169')->first();
        if (!$user) {
            $this->markTestSkipped('User 655169 not found.');
        }

        // 1. Projects Index and Create
        $projIndex = $this->actingAs($user)->get(route('backend.rdb_project.index'));
        $projIndex->assertStatus(200);
        $projIndex->assertSee('จัดการโครงการวิจัย');
        $projIndex->assertSee('เพิ่มโครงการใหม่');

        $projCreate = $this->actingAs($user)->get(route('backend.rdb_project.create'));
        $projCreate->assertStatus(200);
        $projCreate->assertSee('เพิ่มโครงการวิจัย');

        // 2. Publications Index and Create
        $pubIndex = $this->actingAs($user)->get(route('backend.rdb_published.index'));
        $pubIndex->assertStatus(200);
        $pubIndex->assertSee('เพิ่มข้อมูลตีพิมพ์');

        $pubCreate = $this->actingAs($user)->get(route('backend.rdb_published.create'));
        $pubCreate->assertStatus(200);
        $pubCreate->assertSee('เพิ่มข้อมูลการตีพิมพ์ใหม่');

        // 3. IP (DIP) Index and Create
        $dipIndex = $this->actingAs($user)->get(route('backend.rdb_dip.index'));
        $dipIndex->assertStatus(200);
        $dipIndex->assertSee('เพิ่มข้อมูลใหม่');

        $dipCreate = $this->actingAs($user)->get(route('backend.rdb_dip.create'));
        $dipCreate->assertStatus(200);

        // 4. Utilization Index and Create
        $utilizeIndex = $this->actingAs($user)->get(route('backend.rdbprojectutilize.index'));
        $utilizeIndex->assertStatus(200);
        $utilizeIndex->assertSee('เพิ่มข้อมูลใหม่');

        $utilizeCreate = $this->actingAs($user)->get(route('backend.rdbprojectutilize.create'));
        $utilizeCreate->assertStatus(200);
    }

    public function test_user_edit_page_restricts_role_and_status_for_researchers()
    {
        $user = User::where('username', '655169')->first();
        if (!$user) {
            $this->markTestSkipped('User 655169 not found.');
        }

        // 1. Visit edit page as researcher
        $editRes = $this->actingAs($user)->get(route('backend.user.edit', $user->id));
        $editRes->assertStatus(200);
        $editRes->assertSee('เฉพาะผู้ดูแลระบบ');
        $editRes->assertDontSee('name="role"', false);
        $editRes->assertDontSee('name="status"', false);

        // 2. Submit update attempting privilege escalation
        $updateRes = $this->actingAs($user)->put(route('backend.user.update', $user->id), [
            'username' => $user->username,
            'email' => $user->email,
            'role' => 'The Creator',
            'status' => 0,
        ]);
        $updateRes->assertSessionDoesntHaveErrors();
        
        // Assert role was NOT changed
        $this->assertFalse($user->fresh()->hasRole('The Creator'));
    }

    public function test_navigation_drafts_and_pending_submenus()
    {
        $user = User::where('username', '655169')->first();
        if (!$user) {
            $this->markTestSkipped('User 655169 not found.');
        }

        $res = $this->actingAs($user)->get(route('backend.site.index'));
        $res->assertStatus(200);
        $res->assertSee('ฉบับร่าง / รออนุมัติ');

        // Test filtering unapproved projects
        $unapprovedRes = $this->actingAs($user)->get(route('backend.rdb_project.index', ['workflow_status' => 'unapproved']));
        $unapprovedRes->assertStatus(200);
    }

    public function test_researcher_cannot_edit_others_projects()
    {
        $user = User::where('username', '655169')->first();
        if (!$user) {
            $this->markTestSkipped('User 655169 not found.');
        }

        // Find a project where user 655169 is NOT author or creator
        $otherProject = RdbProject::whereDoesntHave('researchers', function($q) use ($user) {
            $q->where('rdb_researcher.researcher_id', $user->researcher_id);
        })->where('user_created', '!=', $user->id)->first();

        if ($otherProject) {
            $editRes = $this->actingAs($user)->get(route('backend.rdb_project.edit', $otherProject->pro_id));
            $this->assertEquals(403, $editRes->getStatusCode());
        }
    }

    public function test_published_work_cannot_be_edited_without_admin_approval_and_moves_to_draft()
    {
        $user = User::where('username', '655169')->first();
        $admin = User::find(129) ?? User::whereHas('roles', function($q) { $q->where('item_name', 'The Creator'); })->first();

        if (!$user || !$admin) {
            $this->markTestSkipped('User 655169 or Admin not found.');
        }

        // Find or create a project authored/created by user 655169
        $project = RdbProject::where('user_created', $user->id)->first();
        if (!$project && $user->researcher_id) {
            $project = RdbProject::whereHas('researchers', function($q) use ($user) {
                $q->where('rdb_researcher.researcher_id', $user->researcher_id);
            })->first();
        }

        if (!$project) {
            $this->markTestSkipped('No project found for user 655169.');
        }

        // 1. Set project as 'published'
        $project->workflow_status = 'published';
        $project->save();

        // 2. Researcher CANNOT edit published project directly (Must return 403 Forbidden)
        $editAttempt = $this->actingAs($user)->get(route('backend.rdb_project.edit', $project->pro_id));
        $this->assertEquals(403, $editAttempt->getStatusCode(), 'Published project must be strictly locked from researcher direct edit');

        // 2.1 Admin of the domain can edit directly without requesting unlock
        $adminEditAttempt = $this->actingAs($admin)->get(route('backend.rdb_project.edit', $project->pro_id));
        $this->assertEquals(200, $adminEditAttempt->getStatusCode(), 'Admin of the domain can edit published project directly without requesting');
        $this->assertFalse($admin->can('requestUnlock', $project), 'Admin does not need to request unlock');

        // 3. Researcher submits unlock request
        $unlockReq = $this->actingAs($user)->post(route('backend.workflow.request_unlock', ['type' => 'project', 'id' => $project->pro_id]), [
            'reason' => 'ต้องการปรับปรุงรายละเอียดและงบประมาณเพิ่มเติมตามข้อตกลงใหม่',
        ]);
        $unlockReq->assertSessionHas('success');

        $project->refresh();
        $this->assertEquals('unlock_requested', $project->workflow_status);
        $this->assertEquals('ต้องการปรับปรุงรายละเอียดและงบประมาณเพิ่มเติมตามข้อตกลงใหม่', $project->unlock_reason);

        // 4. Researcher STILL cannot edit while unlock is pending
        $pendingAttempt = $this->actingAs($user)->get(route('backend.rdb_project.edit', $project->pro_id));
        $this->assertEquals(403, $pendingAttempt->getStatusCode());

        // 5. Admin approves unlock -> Must move item back to 'draft'
        $approveUnlock = $this->actingAs($admin)->post(route('backend.workflow.approve_unlock', ['type' => 'project', 'id' => $project->pro_id]));
        $approveUnlock->assertSessionHas('success');

        $project->refresh();
        $this->assertEquals('draft', $project->workflow_status, 'Approved unlock must move work to draft for editing');

        // 6. Now researcher CAN access edit page
        $editUrl = route('backend.rdb_project.edit', $project->pro_id);
        $draftEdit = $this->actingAs($user)->get($editUrl);
        $draftEdit->assertStatus(200);

        // 7. Researcher submits edited item for review
        $submitRes = $this->actingAs($user)->post(route('backend.workflow.submit', ['type' => 'project', 'id' => $project->pro_id]));
        $submitRes->assertSessionHas('success');
        $project->refresh();
        $this->assertEquals('pending_review', $project->workflow_status);

        // 8. Admin approves item back to 'published'
        $finalApprove = $this->actingAs($admin)->post(route('backend.workflow.approve', ['type' => 'project', 'id' => $project->pro_id]));
        $finalApprove->assertSessionHas('success');
        $project->refresh();
        $this->assertEquals('published', $project->workflow_status);
        $this->assertEquals(1, $project->data_show);
    }

    public function test_domain_admins_can_edit_all_published_modules_directly()
    {
        $admin = User::find(129);
        $this->assertNotNull($admin);

        // 1. Published article
        $pub = \App\Models\RdbPublished::first();
        if ($pub) {
            $pub->workflow_status = 'published';
            $pub->save();
            $this->assertTrue($admin->can('update', $pub), 'Admin can update published publication');
            $this->assertFalse($admin->can('requestUnlock', $pub), 'Admin does not need to request unlock for publication');
            $this->actingAs($admin)->get(route('backend.rdb_published.edit', $pub->id))->assertStatus(200);
        }

        // 2. Intellectual Property (DIP)
        $dip = \App\Models\RdbDip::first();
        if ($dip) {
            $dip->workflow_status = 'published';
            $dip->save();
            $this->assertTrue($admin->can('update', $dip), 'Admin can update published IP');
            $this->assertFalse($admin->can('requestUnlock', $dip), 'Admin does not need to request unlock for IP');
            $this->actingAs($admin)->get(route('backend.rdb_dip.edit', $dip->dip_id))->assertStatus(200);
        }

        // 3. Utilization (Project Utilize)
        $utilize = \App\Models\RdbProjectUtilize::first();
        if ($utilize) {
            $utilize->workflow_status = 'published';
            $utilize->save();
            $this->assertTrue($admin->can('update', $utilize), 'Admin can update published utilization');
            $this->assertFalse($admin->can('requestUnlock', $utilize), 'Admin does not need to request unlock for utilization');
            $this->actingAs($admin)->get(route('backend.rdbprojectutilize.edit', $utilize->utz_id))->assertStatus(200);
        }
    }

    public function test_locked_or_unauthorized_records_hide_all_edit_delete_buttons_across_modules()
    {
        $researcherUser = \App\Models\User::where('username', '655169')->first() 
            ?? \App\Models\User::whereNotNull('researcher_id')->first();
        $this->assertNotNull($researcherUser, 'A researcher user must exist');

        // 1. RdbPublished show page when published
        $pub = \App\Models\RdbPublished::first();
        if ($pub) {
            $pub->workflow_status = 'published';
            $pub->user_created = $researcherUser->id;
            $pub->save();

            $response = $this->actingAs($researcherUser)->get(route('backend.rdb_published.show', $pub->getKey()));
            $response->assertStatus(200);
            $response->assertSee('ขอแก้ไข');
            $response->assertDontSee(route('backend.rdb_published.edit', $pub->getKey()));
            $response->assertDontSee('delete-form-header');
            $response->assertDontSee('delete-form-bottom');
            $response->assertDontSee('> ลบ</button>', false);
            $response->assertDontSee('ลบไฟล์');
            $response->assertDontSee('#uploadFileModal');
            $response->assertDontSee('data-mode="add"');
            $response->assertDontSee('data-mode="edit"');
        }

        // 2. RdbProject show page when published
        $project = \App\Models\RdbProject::first();
        if ($project) {
            $project->workflow_status = 'published';
            $project->user_created = $researcherUser->id;
            $project->save();

            $response = $this->actingAs($researcherUser)->get(route('backend.rdb_project.show', $project->pro_id));
            $response->assertStatus(200);
            $response->assertSee('ขอแก้ไข');
            $response->assertDontSee(route('backend.rdb_project.edit', $project->pro_id));
            $response->assertDontSee('delete-form-header');
            $response->assertDontSee('delete-form-bottom');
            $response->assertDontSee('> ลบ</button>', false);
            $response->assertDontSee('#projectStatusModal');
            $response->assertDontSee('#researcherModal');
            $response->assertDontSee('#projectFilesModal');
        }

        // 3. RdbDip show page when published
        $dip = \App\Models\RdbDip::first();
        if ($dip) {
            $dip->workflow_status = 'published';
            $dip->user_created = $researcherUser->id;
            $dip->save();

            $response = $this->actingAs($researcherUser)->get(route('backend.rdb_dip.show', $dip->dip_id));
            $response->assertStatus(200);
            $response->assertSee('ขอแก้ไข');
            $response->assertDontSee(route('backend.rdb_dip.edit', $dip->dip_id));
            $response->assertDontSee('delete-form-header');
            $response->assertDontSee('delete-form-bottom');
            $response->assertDontSee('> ลบ</button>', false);
            $response->assertDontSee('#fileModal');
        }

        // 4. RdbProjectUtilize show page when published
        $utilize = \App\Models\RdbProjectUtilize::first();
        if ($utilize) {
            $utilize->workflow_status = 'published';
            $utilize->user_created = $researcherUser->id;
            $utilize->save();

            $response = $this->actingAs($researcherUser)->get(route('backend.rdbprojectutilize.show', $utilize->utz_id));
            $response->assertStatus(200);
            $response->assertSee('ขอแก้ไข');
            $response->assertDontSee(route('backend.rdbprojectutilize.edit', $utilize->utz_id));
            $response->assertDontSee('delete-form-header');
            $response->assertDontSee('delete-form-bottom');
            $response->assertDontSee('> ลบ</button>', false);
        }
    }

    public function test_context_aware_links_and_strict_backend_view_scoping_for_researchers()
    {
        $researcherUser = \App\Models\User::where('username', '655169')->first()
            ?? \App\Models\User::whereNotNull('researcher_id')->first();
        $this->assertNotNull($researcherUser);

        // 1. Find or pick another researcher
        $otherResearcher = \App\Models\RdbResearcher::where('researcher_id', '!=', $researcherUser->researcher_id)->first();
        $this->assertNotNull($otherResearcher);

        // Regular researcher cannot view other researcher in backend
        $this->actingAs($researcherUser)
            ->get(route('backend.rdb_researcher.show', $otherResearcher->researcher_id))
            ->assertStatus(403);

        // But regular researcher CAN view their own profile in backend
        $this->actingAs($researcherUser)
            ->get(route('backend.rdb_researcher.show', $researcherUser->researcher_id))
            ->assertStatus(200);

        // 2. A project where researcher is not creator and not an author -> 403
        $unrelatedProject = \App\Models\RdbProject::whereDoesntHave('rdbProjectWorks', function ($q) use ($researcherUser) {
            $q->where('rdb_project_work.researcher_id', $researcherUser->researcher_id);
        })->where('user_created', '!=', $researcherUser->id)->first();

        if ($unrelatedProject) {
            $this->actingAs($researcherUser)
                ->get(route('backend.rdb_project.show', $unrelatedProject->pro_id))
                ->assertStatus(403);
        }

        // 3. A project where researcher IS an author -> 200, and other researcher is not linked
        $coProject = \App\Models\RdbProject::whereHas('rdbProjectWorks', function ($q) use ($researcherUser) {
            $q->where('rdb_project_work.researcher_id', $researcherUser->researcher_id);
        })->first();

        if ($coProject) {
            $response = $this->actingAs($researcherUser)->get(route('backend.rdb_project.show', $coProject->pro_id));
            $response->assertStatus(200);
            // Self profile link is present
            $response->assertSee(route('backend.rdb_researcher.show', $researcherUser->researcher_id));
            if ($otherResearcher && $coProject->rdbProjectWorks()->where('rdb_project_work.researcher_id', $otherResearcher->researcher_id)->exists()) {
                // Other researcher is plain text, not a link to backend
                $response->assertDontSee(route('backend.rdb_researcher.show', $otherResearcher->researcher_id));
            }
        }
    }

    public function test_abstract_and_long_text_formatting_and_splitting_across_modules()
    {
        $admin = \App\Models\User::find(129) ?? \App\Models\User::role('Admin')->first();
        $this->assertNotNull($admin);

        // 1. Test project abstract splitting and frontend-like styling
        $thaiPart = "นี่คือเนื้อหาบทคัดย่อภาษาไทยสำหรับทดสอบความถูกต้องของระบบ";
        $engPart = "This is the English abstract content for testing system accuracy.";
        $project = \App\Models\RdbProject::first();
        if ($project) {
            $origAbstract = $project->pro_abstract;
            $project->pro_abstract = $thaiPart . '<br><br><br><br>' . $engPart;
            $project->save();

            $res = $this->actingAs($admin)->get(route('backend.rdb_project.show', $project->pro_id));
            $res->assertStatus(200);
            $res->assertSee('บทคัดย่อ (ภาษาไทย)');
            $res->assertSee('Abstract (English)');
            $res->assertSee('abstract-box');
            $res->assertSee('abstract-content');
            $res->assertSee('abstract-indent');
            $res->assertSee('copy-abstract-btn');
            $res->assertSee('copyAbstractText');
            $res->assertSee($thaiPart);
            $res->assertSee($engPart);

            // 2. Also verify frontend project show splits the same abstract
            $feRes = $this->get(route('frontend.rdbproject.show', $project->pro_id));
            $feRes->assertStatus(200);
            $feRes->assertSee('บทคัดย่อ (ภาษาไทย)');
            $feRes->assertSee('Abstract (English)');

            // Restore original abstract
            $project->pro_abstract = $origAbstract;
            $project->save();
        }

        // 3. Test published item abstract splitting with 5 newlines
        $pub = \App\Models\RdbPublished::first();
        if ($pub) {
            $origPubAbstract = $pub->pub_abstract;
            $pub->pub_abstract = $thaiPart . "\n\n\n\n\n" . $engPart;
            $pub->save();

            $pubRes = $this->actingAs($admin)->get(route('backend.rdb_published.show', $pub->id));
            $pubRes->assertStatus(200);
            $pubRes->assertSee('บทคัดย่อ (ภาษาไทย)');
            $pubRes->assertSee('Abstract (English)');
            $pubRes->assertSee('abstract-box');
            $pubRes->assertSee('copy-abstract-btn');

            // Restore original abstract
            $pub->pub_abstract = $origPubAbstract;
            $pub->save();
        }

        // 4. Test Project 2141 explicitly: Items 1, 2, 3 must be in Thai box, and English must be in English box
        $p2141 = \App\Models\RdbProject::find(2141);
        if ($p2141) {
            $split2141 = \App\Helpers\AbstractHelper::split($p2141->pro_abstract);
            $this->assertCount(2, $split2141);
            // Item 2 and 3 must belong to Thai abstract
            $this->assertStringContainsString('ผู้สูงวัยที่ได้รับการจัดกิจกรรมการเล่นบอร์ดเกมฝึกสมอง', $split2141[0]);
            $this->assertStringContainsString('ผู้สูงวัยที่ได้รับการจัดกิจกรรมการออกกำลังกายโดยใช้สมองเป็นฐาน', $split2141[0]);
            // English part must be in English abstract, not Thai
            $this->assertStringContainsString('Research and development of innovative dementia prevention', $split2141[1]);
            $this->assertStringNotContainsString('Research and development of innovative dementia prevention', $split2141[0]);

            // Test backend show response for project 2141
            $res2141 = $this->actingAs($admin)->get(route('backend.rdb_project.show', 2141));
            $res2141->assertStatus(200);
            $res2141->assertSee('บทคัดย่อ (ภาษาไทย)');
            $res2141->assertSee('Abstract (English)');
        }
    }
}


