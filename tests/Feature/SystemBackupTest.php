<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\SystemBackupService;
use Illuminate\Support\Facades\Cache;

class SystemBackupTest extends TestCase
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

    /**
     * Non-creator users cannot create backup
     */
    public function test_regular_user_cannot_create_backup(): void
    {
        $user = User::whereHas('roles', fn($q) => $q->where('item_name', 'Researcher'))->first();
        if (!$user) {
            $user = User::where('id', '<>', 129)->first();
        }

        // Regular user without 'The Creator' role
        $response = $this->actingAs($user)->post('/backend/system-commands/backup/create', [
            'type' => 'db',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Non-creator users cannot download backup
     */
    public function test_regular_user_cannot_download_backup(): void
    {
        $user = User::whereHas('roles', fn($q) => $q->where('item_name', 'Researcher'))->first();
        if (!$user) {
            $user = User::where('id', '<>', 129)->first();
        }

        $response = $this->actingAs($user)->get('/backend/system-commands/backup/download/test.sql.gz');

        $response->assertStatus(403);
    }

    /**
     * Non-creator users cannot request restore OTP
     */
    public function test_regular_user_cannot_request_restore_otp(): void
    {
        $user = User::whereHas('roles', fn($q) => $q->where('item_name', 'Researcher'))->first();
        if (!$user) {
            $user = User::where('id', '<>', 129)->first();
        }

        $response = $this->actingAs($user)->postJson('/backend/system-commands/backup/restore-otp', [
            'filename' => 'test.sql.gz',
        ]);

        $response->assertStatus(403);
    }

    /**
     * The Creator can request OTP and verify OTP
     */
    public function test_creator_can_request_and_verify_restore_otp(): void
    {
        $creator = User::where('id', 129)->first();
        if (!$creator) {
            $creator = User::factory()->create(['id' => 129]);
        }

        $service = app(SystemBackupService::class);
        $backups = $service->getBackupList();
        if (empty($backups)) {
            $service->createDatabaseBackup('test_verify');
            $backups = $service->getBackupList();
        }

        $filename = $backups[0]['filename'];

        // 1. Request OTP via AJAX
        $response = $this->actingAs($creator)->postJson('/backend/system-commands/backup/restore-otp', [
            'filename' => $filename,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 2. Check that OTP was cached
        $cacheData = Cache::get("restore_otp_{$filename}");
        $this->assertNotNull($cacheData);
        $this->assertEquals(6, strlen($cacheData['otp']));

        // 3. Invalid OTP should fail verification
        $this->assertFalse($service->verifyRestoreOtp($filename, '000000'));

        // 4. Valid OTP should pass verification
        $validOtp = $cacheData['otp'];
        $this->assertTrue($service->verifyRestoreOtp($filename, $validOtp));
    }

    /**
     * The Creator can list and download valid backup
     */
    public function test_creator_can_download_existing_backup(): void
    {
        $creator = User::where('id', 129)->first();
        if (!$creator) {
            $creator = User::factory()->create(['id' => 129]);
        }

        $service = app(SystemBackupService::class);
        $backups = $service->getBackupList();
        $this->assertNotEmpty($backups);

        $filename = $backups[0]['filename'];

        $response = $this->actingAs($creator)->get("/backend/system-commands/backup/download/{$filename}");
        $response->assertStatus(200);
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
    }
}
