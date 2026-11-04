<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\RdbProject;
use App\Models\RdbResearcher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecordLevelAuditLogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'rdbpcru',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');
    }

    public function test_user_creation_and_update_records_audit_log_without_passwords(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $this->actingAs($admin);

        // Create test user
        $testUser = User::create([
            'username' => 'audit_test_user_' . time(),
            'email' => 'audit_test_' . time() . '@example.com',
            'password_hash' => '$2y$10$abcdefghijklmnopqrstuvwxyz123456',
            'auth_key' => 'secret_auth_key_123',
            'status' => 10,
        ]);

        $createLog = AuditLog::where('auditable_type', 'User')
            ->where('auditable_id', $testUser->id)
            ->where('event', 'create')
            ->first();

        $this->assertNotNull($createLog);
        $this->assertArrayNotHasKey('password_hash', $createLog->new_values ?? []);
        $this->assertArrayNotHasKey('auth_key', $createLog->new_values ?? []);

        // Update user
        $testUser->update([
            'email' => 'audit_updated_' . time() . '@example.com',
            'status' => 9,
        ]);

        $updateLog = AuditLog::where('auditable_type', 'User')
            ->where('auditable_id', $testUser->id)
            ->where('event', 'update')
            ->first();

        $this->assertNotNull($updateLog);
        $this->assertEquals(9, $updateLog->new_values['status'] ?? null);
        $this->assertEquals(10, $updateLog->old_values['status'] ?? null);

        // Clean up
        $testUser->delete();
    }

    public function test_researcher_creation_sanitizes_citizen_id(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $this->actingAs($admin);

        $res = RdbResearcher::create([
            'researcher_fname' => 'ทดสอบ',
            'researcher_lname' => 'ออดิต',
            'researcher_codeid' => '1234567890123',
            'department_id' => 1,
            'restatus_id' => 1,
            'data_show' => 1,
        ]);

        $log = AuditLog::where('auditable_type', 'RdbResearcher')
            ->where('auditable_id', $res->researcher_id)
            ->where('event', 'create')
            ->first();

        $this->assertNotNull($log);
        $savedCodeId = $log->new_values['researcher_codeid'] ?? '';
        $this->assertStringContainsString('XXXX', $savedCodeId);
        $this->assertNotEquals('1234567890123', $savedCodeId);

        // Clean up
        $res->delete();
    }

    public function test_admin_can_view_project_audit_trail_in_backend(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        $project = RdbProject::first();

        if (!$admin || !$project) {
            $this->markTestSkipped('Admin or project missing');
        }

        // Ensure there is at least one log entry
        AuditLog::record(
            event: 'update',
            auditable: $project,
            oldValues: ['pro_name_th' => 'ชื่อเดิม'],
            newValues: ['pro_name_th' => 'ชื่อใหม่'],
            remarks: 'ทดสอบบันทึก Log'
        );

        $response = $this->actingAs($admin)->get(route('backend.rdb_project.show', $project->pro_id));
        $response->assertStatus(200);
        $response->assertSee('ประวัติกิจกรรมและการแก้ไขโครงการวิจัย');
        $response->assertSee('recordAuditTrailCard');
        $response->assertSee('ทดสอบบันทึก Log');
    }

    public function test_researcher_owner_can_view_own_project_audit_trail(): void
    {
        $project = RdbProject::whereNotNull('user_created')->first();
        if (!$project) {
            $this->markTestSkipped('No project with user_created found');
        }

        $owner = User::find($project->user_created);
        if (!$owner) {
            $owner = User::whereHas('roles', fn($q) => $q->where('item_name', 'Researcher'))->first();
            $project->user_created = $owner->id;
            $project->save();
        }

        // Add dummy log
        AuditLog::record(
            event: 'update',
            auditable: $project,
            oldValues: ['pro_name_th' => 'ก่อนแก้'],
            newValues: ['pro_name_th' => 'หลังแก้'],
            remarks: 'แก้ไขโดยเจ้าของ'
        );

        $response = $this->actingAs($owner)->get(route('backend.rdb_project.show', $project->pro_id));
        $response->assertStatus(200);
        $response->assertSee('ประวัติกิจกรรมและการแก้ไขโครงการวิจัย');
        $response->assertSee('แก้ไขโดยเจ้าของ');
    }

    public function test_non_owner_researcher_cannot_view_other_project_audit_trail(): void
    {
        $project = RdbProject::whereNotNull('user_created')->first();
        $otherResearcher = User::whereHas('roles', fn($q) => $q->where('item_name', 'Researcher'))
            ->where('id', '<>', $project->user_created)
            ->first();

        if (!$project || !$otherResearcher) {
            $this->markTestSkipped('Project or other researcher missing');
        }

        $response = $this->actingAs($otherResearcher)->get(route('backend.rdb_project.show', $project->pro_id));
        // Strict backend security: non-owner researcher receives 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_user_can_view_own_account_audit_trail(): void
    {
        $user = User::whereHas('roles', fn($q) => $q->where('item_name', 'Researcher'))->first();
        if (!$user) {
            $this->markTestSkipped('No researcher user found');
        }

        AuditLog::record(
            event: 'update',
            auditable: $user,
            oldValues: ['status' => 10],
            newValues: ['status' => 10],
            remarks: 'ทดสอบบันทึกโปรไฟล์ตนเอง'
        );

        $response = $this->actingAs($user)->get(route('backend.user.show', $user->id));
        $response->assertStatus(200);
        $response->assertSee('ประวัติกิจกรรมและการแก้ไขบัญชีผู้ใช้');
        $response->assertSee('ทดสอบบันทึกโปรไฟล์ตนเอง');
    }

    public function test_non_admin_cannot_view_other_user_account_audit_trail(): void
    {
        $userA = User::whereHas('roles', fn($q) => $q->where('item_name', 'Researcher'))->first();
        if (!$userA) {
            $userA = User::create([
                'username' => 'test_user_a_' . time(),
                'email' => 'user_a_' . time() . '@test.com',
                'password_hash' => bcrypt('password123'),
                'auth_key' => 'auth_key_a_123',
                'status' => 10,
            ]);
            \App\Models\AuthAssignment::create(['item_name' => 'Researcher', 'user_id' => $userA->id]);
        }

        $userB = User::create([
            'username' => 'test_user_b_' . time(),
            'email' => 'user_b_' . time() . '@test.com',
            'password_hash' => bcrypt('password123'),
            'auth_key' => 'auth_key_b_123',
            'status' => 10,
        ]);
        \App\Models\AuthAssignment::create(['item_name' => 'Researcher', 'user_id' => $userB->id]);

        try {
            $response = $this->actingAs($userA)->get(route('backend.user.show', $userB->id));
            $response->assertStatus(403);
        } finally {
            \App\Models\AuthAssignment::where('user_id', $userB->id)->delete();
            $userB->delete();
        }
    }

    public function test_public_frontend_project_view_does_not_contain_audit_trail(): void
    {
        $project = RdbProject::where('data_show', 1)->first();
        if (!$project) {
            $this->markTestSkipped('No public project found');
        }

        $response = $this->get(route('frontend.rdbproject.show', $project->pro_id));
        $response->assertStatus(200);
        $response->assertDontSee('recordAuditTrailCard');
        $response->assertDontSee('Activity & Audit Trail');
    }

    public function test_hit_and_download_counters_are_ignored_from_audit_logs(): void
    {
        $project = RdbProject::first();
        if (!$project) {
            $this->markTestSkipped('No project found');
        }

        $initialLogsCount = AuditLog::where('auditable_type', 'RdbProject')
            ->where('auditable_id', $project->pro_id)
            ->count();

        // Simulate page view counter increment
        $project->increment('pro_count_page');
        $project->increment('pro_count_abs');
        $project->increment('pro_count_full');

        $afterLogsCount = AuditLog::where('auditable_type', 'RdbProject')
            ->where('auditable_id', $project->pro_id)
            ->count();

        $this->assertEquals($initialLogsCount, $afterLogsCount, 'Counter increments must not create audit log entries');
    }

    public function test_audit_trail_paginates_ten_items_per_page_with_page_controls(): void
    {
        $admin = User::find(129) ?? User::whereHas('roles', fn($q) => $q->where('item_name', 'The Creator'))->first();
        $project = RdbProject::first();
        if (!$project) {
            $this->markTestSkipped('No project found');
        }

        // Create 15 audit logs to trigger 2 pages
        for ($i = 1; $i <= 15; $i++) {
            AuditLog::record(
                event: 'update',
                auditable: $project,
                oldValues: ['test_field' => "old_{$i}"],
                newValues: ['test_field' => "new_{$i}"],
                remarks: "ทดสอบหน้าเพจรายการที่ {$i}"
            );
        }

        $response = $this->actingAs($admin)->get(route('backend.rdb_project.show', $project->pro_id));
        $response->assertStatus(200);
        $response->assertSee("recordAuditTrailCard_{$project->pro_id}");
        $response->assertSee('switchAuditPage');
        $response->assertSee('ถัดไป');
        $response->assertSee('ก่อนหน้า');
        $response->assertSee('data-page="1"', false);
        $response->assertSee('data-page="2"', false);
    }

    public function test_managing_project_researchers_records_audit_trail_on_project(): void
    {
        $admin = User::find(129) ?? User::whereHas('roles', fn($q) => $q->where('item_name', 'The Creator'))->first();
        $project = RdbProject::create([
            'pro_nameTH' => 'โครงการทดสอบสิทธิ์นักวิจัย ' . time(),
            'pro_budget' => 50000,
            'year_id' => 19,
            'data_show' => 1,
        ]);
        $researcher = RdbResearcher::first();

        if (!$project || !$researcher) {
            $this->markTestSkipped('Project or Researcher not available for test');
        }

        try {
            // 1. Add researcher
            $response = $this->actingAs($admin)->post(route('backend.rdb_project.researcher.store', $project->pro_id), [
                'researcher_id' => $researcher->researcher_id,
                'position_id' => 3, // Researcher
                'ratio' => 20,
            ]);
            $response->assertSessionHasNoErrors();

            $addLog = AuditLog::where('auditable_type', 'RdbProject')
                ->where('auditable_id', $project->pro_id)
                ->where('remarks', 'like', "%เพิ่มนักวิจัยร่วมโครงการ%")
                ->first();
            $this->assertNotNull($addLog, 'Adding a researcher must record audit log on project');
            $this->assertNotEmpty($addLog->new_values['position'], 'Position must not be null in audit log');
            $this->assertStringNotContainsString('()', $addLog->remarks, 'Remarks must include position name, not empty parentheses');

            // 2. Update researcher
            $response = $this->actingAs($admin)->put(route('backend.rdb_project.researcher.update', [$project->pro_id, $researcher->researcher_id]), [
                'position_id' => 3,
                'ratio' => 25,
            ]);
            $response->assertSessionHasNoErrors();

            $updateLog = AuditLog::where('auditable_type', 'RdbProject')
                ->where('auditable_id', $project->pro_id)
                ->where('remarks', 'like', "%ปรับปรุงข้อมูลนักวิจัยร่วม%")
                ->first();
            $this->assertNotNull($updateLog, 'Updating a researcher must record audit log on project');

            // 3. Delete researcher
            $response = $this->actingAs($admin)->delete(route('backend.rdb_project.researcher.destroy', [$project->pro_id, $researcher->researcher_id]));
            $response->assertSessionHasNoErrors();

            $deleteLog = AuditLog::where('auditable_type', 'RdbProject')
                ->where('auditable_id', $project->pro_id)
                ->where('remarks', 'like', "%ลบนักวิจัยออกจากโครงการ%")
                ->first();
            $this->assertNotNull($deleteLog, 'Deleting a researcher must record audit log on project');
        } finally {
            \App\Models\RdbProjectWork::where('pro_id', $project->pro_id)->delete();
            AuditLog::where('auditable_type', 'RdbProject')->where('auditable_id', $project->pro_id)->delete();
            $project->delete();
        }
    }

    public function test_managing_published_authors_records_audit_trail_on_publication(): void
    {
        $admin = User::find(129) ?? User::whereHas('roles', fn($q) => $q->where('item_name', 'The Creator'))->first();
        $published = \App\Models\RdbPublished::create([
            'pub_name' => 'บทความวิจัยทดสอบผู้แต่ง ' . time(),
            'pub_name_journal' => 'วารสารวิจัย มรภ.เพชรบูรณ์',
            'pub_date' => '2026-01-15',
            'pubtype_id' => 1,
            'year_id' => 19,
            'data_show' => 1,
        ]);
        $researcher = RdbResearcher::first();

        if (!$published || !$researcher) {
            $this->markTestSkipped('Published or Researcher not available for test');
        }

        try {
            $this->withoutExceptionHandling();
            // 1. Add author
            $response = $this->actingAs($admin)->post(route('backend.rdb_published.store_author', $published->id), [
                'researcher_id' => $researcher->researcher_id,
                'pubta_id' => 2, // Co-author
                'pubw_main' => 0,
                'pubw_bud' => 0,
            ]);
            $response->assertSessionHasNoErrors();

            $addLog = AuditLog::where('auditable_type', 'RdbPublished')
                ->where('auditable_id', $published->id)
                ->where('remarks', 'like', "%เพิ่มผู้แต่งผลงาน%")
                ->first();
            $this->assertNotNull($addLog, 'Adding author must record audit log on publication');

            // 2. Update author
            $response = $this->actingAs($admin)->put(route('backend.rdb_published.update_author', [$published->id, $researcher->researcher_id]), [
                'pubta_id' => 3, // Corresponding
                'pubw_main' => 0,
                'pubw_bud' => 0,
            ]);
            $response->assertSessionHasNoErrors();

            $updateLog = AuditLog::where('auditable_type', 'RdbPublished')
                ->where('auditable_id', $published->id)
                ->where('remarks', 'like', "%ปรับปรุงข้อมูลผู้แต่ง%")
                ->first();
            $this->assertNotNull($updateLog, 'Updating author must record audit log on publication');

            // 3. Delete author
            $response = $this->actingAs($admin)->delete(route('backend.rdb_published.destroy_author', [$published->id, $researcher->researcher_id]));
            $response->assertSessionHasNoErrors();

            $deleteLog = AuditLog::where('auditable_type', 'RdbPublished')
                ->where('auditable_id', $published->id)
                ->where('remarks', 'like', "%ลบผู้แต่งออกจากผลงานตีพิมพ์%")
                ->first();
            $this->assertNotNull($deleteLog, 'Deleting author must record audit log on publication');
        } finally {
            \App\Models\RdbPublishedWork::where('published_id', $published->id)->delete();
            AuditLog::where('auditable_type', 'RdbPublished')->where('auditable_id', $published->id)->delete();
            $published->delete();
        }
    }
}
