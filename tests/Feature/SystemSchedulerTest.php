<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\TelegramNotificationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
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
        $response->assertSee('ศูนย์จัดการระบบตั้งเวลาทำงานอัตโนมัติ');
        $response->assertSee('สำรองฐานข้อมูลระบบประจำวัน');
        $response->assertSee('สำรองระบบเต็มรูปแบบประจำสัปดาห์');
        $response->assertSee('นับจำนวนหน้าเอกสาร PDF อัตโนมัติ');
        $response->assertSee('เรนเดอร์ภาพหน้าปก WebP อัตโนมัติ');
        $response->assertSee('ซิงค์ข่าวประกาศทุนวิจัยจากระบบ NRIIS');
        $response->assertSee('ซิงค์งานประชุมวิชาการจากเพจสถาบันวิจัยฯ');
        $response->assertSee('รันทันที');
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

    public function test_superadmin_can_run_scheduled_command(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        // Test running count-pdf-pages with limit 1
        $response = $this->actingAs($admin)->post(route('backend.system_scheduler.run'), [
            'task_id' => 'count-pdf-pages',
        ]);

        $response->assertRedirect(route('backend.system_scheduler.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('command_output');
    }

    public function test_telegram_notification_service_handles_missing_token_gracefully(): void
    {
        Config::set('services.telegram.bot_token', null);
        Config::set('services.telegram.admin_chat_id', null);

        $this->assertFalse(TelegramNotificationService::isConfigured());
        $this->assertFalse(TelegramNotificationService::isAdminConfigured());

        $result = TelegramNotificationService::sendMessage('12345', 'Hello World');
        $this->assertFalse($result['success']);

        $sent = TelegramNotificationService::notifyAdmin('Test Title', 'Test Body');
        $this->assertFalse($sent);
    }

    public function test_telegram_notification_service_sends_formatted_alert(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 999]], 200),
        ]);

        Config::set('services.telegram.bot_token', 'fake-token-123');
        Config::set('services.telegram.admin_chat_id', '123456789');
        Config::set('services.telegram.enabled', true);

        $this->assertTrue(TelegramNotificationService::isConfigured());
        $this->assertTrue(TelegramNotificationService::isAdminConfigured());

        $sent = TelegramNotificationService::notifyAdmin('หัวข้อทดสอบ', 'ข้อความทดสอบแจ้งเตือน');
        $this->assertTrue($sent);

        // Test with AuditLog
        $mockLog = new AuditLog([
            'event' => 'create',
            'auditable_type' => 'RdbProject',
            'auditable_id' => 101,
            'user_name' => 'ผู้ช่วยศาสตราจารย์ ดร. ทดสอบ',
            'user_role' => 'Researcher',
            'ip_address' => '127.0.0.1',
            'diff_summary' => 'เพิ่มโครงการวิจัยใหม่',
            'created_at' => now(),
        ]);

        $auditSent = TelegramNotificationService::notifyAuditEvent($mockLog);
        $this->assertTrue($auditSent);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'sendMessage') &&
                   $request['chat_id'] === '123456789' &&
                   $request['parse_mode'] === 'HTML';
        });
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
