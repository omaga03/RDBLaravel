<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\TelegramNotificationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SystemSchedulerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'rdbpcru',
            'database.connections.mysql.password' => '',
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');
    }

    public function test_superadmin_can_access_scheduler_hub(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $response = $this->actingAs($admin)->get(route('backend.system_scheduler.index'));
        $response->assertStatus(200);
        $response->assertSee('Task Scheduler Hub');
        $response->assertSee('Daily Database Backup');
        $response->assertSee('Weekly Full Backup');
        $response->assertSee('Funding Sync');
        $response->assertSee('Event Sync');
        
        // Assert PDF counter & cover tasks are removed
        $response->assertDontSee('นับจำนวนหน้าเอกสาร PDF อัตโนมัติ');
        $response->assertDontSee('เรนเดอร์ภาพหน้าปก WebP อัตโนมัติ');

        $response->assertSee('งานอัตโนมัติในระบบ');
    }

    public function test_scheduler_hub_displays_active_task_count_when_a_task_is_disabled(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $orig = SystemSetting::get('scheduler_backup_db_enabled', '1');
        SystemSetting::set('scheduler_backup_db_enabled', '0');

        try {
            $response = $this->actingAs($admin)->get(route('backend.system_scheduler.index'));
            $response->assertStatus(200);
            $response->assertSee('3');
            $response->assertSee('ปิดพัก 1 ตัว');
        } finally {
            SystemSetting::set('scheduler_backup_db_enabled', $orig);
        }
    }

    public function test_regular_researcher_cannot_access_scheduler_hub(): void
    {
        $researcher = User::whereHas('roles', fn($q) => $q->where('item_name', 'Researcher'))->first();
        if (!$researcher) {
            $this->markTestSkipped('No researcher found');
        }

        $response = $this->actingAs($researcher)->get(route('backend.system_scheduler.index'));
        $response->assertStatus(403);
    }

    public function test_superadmin_can_update_task_schedule_timing(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $response = $this->actingAs($admin)->post(route('backend.system_scheduler.update_schedule'), [
            'task_id' => 'backup-db',
            'enabled' => '1',
            'frequency_type' => 'daily',
            'time' => '04:15',
        ]);

        $response->assertRedirect(route('backend.system_scheduler.index'));
        $response->assertSessionHas('success');

        $savedExpr = SystemSetting::get('scheduler_backup_db_expression');
        $this->assertEquals('15 4 * * *', $savedExpr);
    }

    public function test_superadmin_can_run_scheduled_command(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        // Test running sync-nriis-news
        $response = $this->actingAs($admin)->post(route('backend.system_scheduler.run'), [
            'task_id' => 'sync-nriis-news',
        ]);

        $response->assertRedirect(route('backend.system_scheduler.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('command_output');
    }

    public function test_user_can_update_personal_notification_preferences(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->actingAs($user)->put(route('backend.user.update', $user->id), [
            'username' => $user->username,
            'email' => $user->email,
            'telegram_chat_id' => '987654321',
            'pref_mail_enabled' => '1',
            'pref_telegram_enabled' => '1',
            'pref_topic_research_approved' => '1',
            'pref_topic_research_rejected' => '1',
            'pref_topic_review_assigned' => '1',
            'pref_topic_review_final_approved' => '1',
        ]);

        $response->assertRedirect();
        $user->refresh();

        $this->assertEquals('987654321', $user->telegram_chat_id);
        $this->assertTrue($user->wantsMailFor('research_approved'));
        $this->assertTrue($user->wantsTelegramFor('research_approved'));
    }

    public function test_user_can_test_my_telegram(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 123]], 200),
        ]);

        Config::set('services.telegram.bot_token', 'test-token-456');

        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->actingAs($user)->post(route('backend.user.test_my_telegram'), [
            'chat_id' => '987654321',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_user_can_test_my_mail(): void
    {
        Mail::fake();

        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->actingAs($user)->post(route('backend.user.test_my_mail'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_telegram_notification_service_handles_missing_token_gracefully(): void
    {
        Config::set('services.telegram.bot_token', null);
        Config::set('services.telegram.admin_chat_id', null);
        SystemSetting::set('telegram_bot_token', '');
        SystemSetting::set('telegram_admin_chat_id', '');

        $this->assertFalse(TelegramNotificationService::isConfigured());
        $this->assertFalse(TelegramNotificationService::isAdminConfigured());

        $result = TelegramNotificationService::sendMessage('12345', 'Hello World');
        $this->assertFalse($result['success']);

        $sent = TelegramNotificationService::notifyAdmin('Test Title', 'Test Body');
        $this->assertFalse($sent);
    }

    public function test_the_creator_can_access_dedicated_system_backup_page(): void
    {
        $creator = User::find(129);
        if (!$creator) {
            $creator = User::whereHas('roles', fn($q) => $q->where('item_name', 'The Creator'))->first();
        }

        if (!$creator) {
            $this->markTestSkipped('No Creator user found');
        }

        $response = $this->actingAs($creator)->get(route('backend.system_backup.index'));
        $response->assertStatus(200);
        $response->assertSee('ศูนย์สำรองและกู้คืนข้อมูลระบบ');
        $response->assertSee('The Creator Only');
    }

    public function test_non_creator_cannot_access_dedicated_system_backup_page(): void
    {
        $nonCreator = User::where('id', '<>', 129)
            ->whereHas('roles', fn($q) => $q->where('item_name', '<>', 'The Creator'))
            ->first();

        if (!$nonCreator) {
            $this->markTestSkipped('No non-creator user found');
        }

        $response = $this->actingAs($nonCreator)->get(route('backend.system_backup.index'));
        $response->assertStatus(403);
    }
}
