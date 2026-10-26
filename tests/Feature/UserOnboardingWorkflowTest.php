<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RdbDepartment;
use App\Models\RdbPrefix;
use App\Models\RdbResearcher;
use App\Models\AuthAssignment;
use Illuminate\Support\Str;

class UserOnboardingWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.database' => 'rdbpcru',
            'database.connections.mysql.password' => '',
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');
    }

    /**
     * Test new user without researcher/department is redirected to frontend upon login,
     * and restricted to onboarding in backend until approved by admin.
     */
    public function test_new_user_onboarding_and_admin_unlock_lifecycle()
    {
        // 1. Create a brand new unverified user (e.g. registered via Google or manual without existing researcher)
        $newUser = User::create([
            'username' => 'new_user_' . time(),
            'email' => 'new_teacher_' . time() . '@pcru.ac.th',
            'password' => 'secret1234',
            'status' => 10,
            'onboarding_status' => 'none',
            'researcher_id' => null,
            'confirmed_at' => time(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->assertFalse($newUser->canAccessFullBackend());
        $this->assertTrue($newUser->isPendingOnboarding());

        // 2. Standard login test: must redirect to frontend (site.index)
        $loginResponse = $this->post(route('login'), [
            'username' => $newUser->username,
            'password' => 'secret1234',
        ]);
        $loginResponse->assertRedirect(route('site.index'));

        // 3. Visiting backend dashboard or project list directly: must intercept & redirect to onboarding
        $this->actingAs($newUser)->get('/backend')
            ->assertRedirect(route('backend.onboarding.index'));

        $this->actingAs($newUser)->get(route('backend.rdb_project.index'))
            ->assertRedirect(route('backend.onboarding.index'));

        // 4. Visiting onboarding page displays form and department selection
        $onboardingResp = $this->actingAs($newUser)->get(route('backend.onboarding.index'));
        $onboardingResp->assertStatus(200);
        $onboardingResp->assertSee('ขั้นตอนการยืนยันตัวตนและสังกัดนักวิจัย');
        $onboardingResp->assertSee('เลือกสังกัด / คณะ');

        // Subnav contains the onboarding notice rather than management menus
        $onboardingContent = $onboardingResp->getContent();
        $this->assertStringContainsString('ยืนยันตัวตนนักวิจัย', $onboardingContent);
        $this->assertStringNotContainsString('รายการโครงการทั้งหมด', $onboardingContent);

        // 5. Submit onboarding form
        $department = RdbDepartment::first();
        $prefix = RdbPrefix::first();
        $this->assertNotNull($department);
        $this->assertNotNull($prefix);

        $submitResp = $this->actingAs($newUser)->post(route('backend.onboarding.store'), [
            'onboarding_department_id' => $department->department_id,
            'onboarding_prefix_id' => $prefix->prefix_id,
            'onboarding_fname' => 'สมศักดิ์',
            'onboarding_lname' => 'ใจดีวิจัย',
            'onboarding_fname_en' => 'Somsak',
            'onboarding_lname_en' => 'Jaidee',
            'onboarding_tel' => '089-999-8888',
            'onboarding_position' => 'อาจารย์ผู้ช่วยสอน',
        ]);
        $submitResp->assertRedirect(route('backend.onboarding.index'));

        // Reload user and check status is now 'pending'
        $newUser->refresh();
        $this->assertEquals('pending', $newUser->onboarding_status);
        $this->assertEquals($department->department_id, $newUser->onboarding_department_id);
        $this->assertEquals('สมศักดิ์', $newUser->onboarding_fname);

        // Onboarding view now displays the pending status card
        $pendingView = $this->actingAs($newUser)->get(route('backend.onboarding.index'));
        $pendingView->assertStatus(200);
        $pendingView->assertSee('สถานะ: ส่งคำขอแล้ว อยู่ระหว่างรอผู้ดูแลระบบตรวจสอบและปลดล็อก');
        $pendingView->assertSee('สมศักดิ์ ใจดีวิจัย');

        // 6. Admin logs in and views user management
        $admin = User::find(129) ?? User::whereHas('roles', fn($q) => $q->where('item_name', 'The Creator'))->first();
        $this->assertNotNull($admin);

        $adminUserIndex = $this->actingAs($admin)->get(route('backend.user.index', ['onboarding_status' => 'pending']));
        $adminUserIndex->assertStatus(200);
        $adminUserIndex->assertSee('รอยืนยัน/ปลดล็อก');
        $adminUserIndex->assertSee($newUser->username);
        $adminUserIndex->assertSee('toggleResearcherMode');
        $adminUserIndex->assertSee('existing_researcher_id');
        $adminUserIndex->assertSee('พิมพ์ค้นหาชื่อนักวิจัย หรือคณะ/สังกัด');

        // 7. Admin Approves and Unlocks user
        $approveResp = $this->actingAs($admin)->post(route('backend.user.approve_onboarding', $newUser->id), [
            'mode' => 'create_new',
        ]);
        $approveResp->assertRedirect();

        // 8. Verify user is now fully unlocked and has researcher profile
        $newUser->refresh();
        $this->assertEquals('approved', $newUser->onboarding_status);
        $this->assertNotNull($newUser->researcher_id);

        $createdResearcher = RdbResearcher::find($newUser->researcher_id);
        $this->assertNotNull($createdResearcher);
        $this->assertEquals('สมศักดิ์', $createdResearcher->researcher_fname);
        $this->assertEquals('ใจดีวิจัย', $createdResearcher->researcher_lname);
        $this->assertEquals($department->department_id, $createdResearcher->department_id);
        $this->assertEquals($newUser->email, $createdResearcher->researcher_email);

        $this->assertTrue($newUser->canAccessFullBackend());
        $this->assertTrue($newUser->hasRole('Researcher'));

        // 9. Now the approved researcher visits backend: access is fully permitted!
        $backendResp = $this->actingAs($newUser)->get('/backend');
        $backendResp->assertStatus(200);
        $backendContent = $backendResp->getContent();
        $this->assertStringContainsString('รายการโครงการทั้งหมด', $backendContent);

        // 10. Clean up test user & created researcher
        AuthAssignment::where('user_id', (string)$newUser->id)->delete();
        $newUser->delete();
        $createdResearcher->delete();
    }

    /**
     * Test admin rejection workflow with reason.
     */
    public function test_admin_rejection_workflow()
    {
        $dept = RdbDepartment::first();
        $prefix = RdbPrefix::first();

        $user = User::create([
            'username' => 'reject_test_' . time(),
            'email' => 'reject_' . time() . '@pcru.ac.th',
            'password' => 'secret1234',
            'status' => 10,
            'onboarding_status' => 'pending',
            'onboarding_department_id' => $dept->department_id,
            'onboarding_prefix_id' => $prefix->prefix_id,
            'onboarding_fname' => 'วิชัย',
            'onboarding_lname' => 'ทดสอบ',
            'confirmed_at' => time(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $admin = User::find(129) ?? User::whereHas('roles', fn($q) => $q->where('item_name', 'The Creator'))->first();

        // Admin rejects request with a reason
        $rejectResp = $this->actingAs($admin)->post(route('backend.user.reject_onboarding', $user->id), [
            'reason' => 'ไม่พบข้อมูลในบัญชีรายชื่อสังกัดคณะ กรุณาระบุชื่อ-นามสกุลให้ตรงกับบัตรประชาชน',
        ]);
        $rejectResp->assertRedirect();

        $user->refresh();
        $this->assertEquals('rejected', $user->onboarding_status);
        $this->assertEquals('ไม่พบข้อมูลในบัญชีรายชื่อสังกัดคณะ กรุณาระบุชื่อ-นามสกุลให้ตรงกับบัตรประชาชน', $user->onboarding_reject_reason);

        // User visits onboarding and sees the rejection card with reason
        $viewResp = $this->actingAs($user)->get(route('backend.onboarding.index'));
        $viewResp->assertStatus(200);
        $viewResp->assertSee('สถานะ: คำขอไม่ผ่านการอนุมัติ');
        $viewResp->assertSee('ไม่พบข้อมูลในบัญชีรายชื่อสังกัดคณะ กรุณาระบุชื่อ-นามสกุลให้ตรงกับบัตรประชาชน');

        // Clean up
        $user->delete();
    }

    /**
     * Test admin impersonating an unverified user can successfully stop impersonating
     * and return to admin account without being blocked by EnsureResearcherVerified middleware.
     */
    public function test_impersonated_admin_can_stop_impersonating_unverified_user()
    {
        $admin = User::find(129) ?? User::whereHas('roles', fn($q) => $q->where('item_name', 'The Creator'))->first();

        $unverifiedUser = User::create([
            'username' => 'test_impersonate_' . time(),
            'email' => 'test_impersonate_' . time() . '@pcru.ac.th',
            'password' => 'secret1234',
            'status' => 10,
            'onboarding_status' => 'none',
            'researcher_id' => null,
            'confirmed_at' => time(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // Admin impersonates unverified user
        $impersonateResp = $this->actingAs($admin)->post(route('backend.user.impersonate', $unverifiedUser->id));
        $impersonateResp->assertRedirect();

        // While impersonating, user is unverified and session has impersonator_id
        $this->actingAs($unverifiedUser)
            ->withSession([
                'impersonator_id' => $admin->id,
                'impersonated_user_id' => $unverifiedUser->id,
            ]);

        // Admin stops impersonating via POST
        $stopResp = $this->post(route('backend.user.stop_impersonate'));
        $stopResp->assertRedirect(route('backend.user.index'));
        $this->assertEquals($admin->id, auth()->id());

        // Also test GET method on stop_impersonate
        $this->actingAs($unverifiedUser)
            ->withSession([
                'impersonator_id' => $admin->id,
                'impersonated_user_id' => $unverifiedUser->id,
            ]);
        $stopGetResp = $this->get(route('backend.user.stop_impersonate'));
        $stopGetResp->assertRedirect(route('backend.user.index'));
        $this->assertEquals($admin->id, auth()->id());

        // Clean up
        $unverifiedUser->delete();
    }

    /**
     * Test filter button for all admin level users.
     */
    public function test_admin_filter_button_shows_all_admin_level_users()
    {
        $admin = User::find(129) ?? User::whereHas('roles', fn($q) => $q->where('item_name', 'The Creator'))->first();

        // 1. Visit index: button exists and displays admin count
        $resp = $this->actingAs($admin)->get(route('backend.user.index'));
        $resp->assertStatus(200);
        $resp->assertSee('ระดับ Admin ทั้งหมด');
        $resp->assertSee('role=admin_all');

        // 2. Filter by admin_all: returns only admin level users
        $adminResp = $this->actingAs($admin)->get(route('backend.user.index', ['role' => 'admin_all']));
        $adminResp->assertStatus(200);
        $adminResp->assertSee('ระดับ Admin ทั้งหมด');
        $adminResp->assertSee('btn-danger text-white shadow-sm');

        // Check that an admin user is present in the list
        $adminResp->assertSee($admin->username);
    }
}
