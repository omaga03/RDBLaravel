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

    /**
     * The Creator can trigger background backup and track progress via AJAX
     */
    public function test_creator_can_trigger_background_backup_and_poll_progress(): void
    {
        $creator = User::where('id', 129)->first();
        if (!$creator) {
            $creator = User::factory()->create(['id' => 129]);
        }

        SystemBackupService::clearProgress();

        // 1. Dispatch backup via AJAX
        $response = $this->actingAs($creator)->postJson('/backend/system-backup/create', [
            'type' => 'db',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'type' => 'db',
        ]);

        // 2. Poll progress
        $progressRes = $this->actingAs($creator)->getJson('/backend/system-backup/progress');
        $progressRes->assertStatus(200);
        $progressRes->assertJsonStructure([
            'running',
            'progress' => [
                'status',
                'percent',
            ],
        ]);

        // 3. Clear progress
        $cancelRes = $this->actingAs($creator)->postJson('/backend/system-backup/cancel');
        $cancelRes->assertStatus(200);
        $cancelRes->assertJson(['success' => true]);

        $this->assertNull(SystemBackupService::getProgress());
    }

    /**
     * Subtask database backup inside Full Backup must remain in running state (15%) and not mark progress completed early,
     * and deleting backup via POST body is idempotent against duplicate requests
     */
    public function test_full_backup_db_subtask_keeps_running_progress_and_delete_is_idempotent(): void
    {
        $creator = User::where('id', 129)->first();
        if (!$creator) {
            $creator = User::factory()->create(['id' => 129]);
        }

        $service = app(SystemBackupService::class);
        SystemBackupService::clearProgress();

        // Simulate Step 1 of createFullBackup()
        $subtaskResult = $service->createDatabaseBackup('temp_db', false);
        $progress = SystemBackupService::getProgress();

        $this->assertNotNull($progress);
        $this->assertEquals('running', $progress['status']);
        $this->assertEquals('all', $progress['type']);
        $this->assertEquals(15, $progress['percent']);
        if (isset($subtaskResult['path']) && file_exists($subtaskResult['path'])) {
            @unlink($subtaskResult['path']);
        }

        // Create a dummy backup archive to test delete endpoint and idempotency
        $dummyName = 'backup_db_20991231_235959.sql.gz';
        $dummyPath = $service->getBackupDir() . DIRECTORY_SEPARATOR . $dummyName;
        file_put_contents($dummyPath, gzencode('-- test sql dump', 9));
        $this->assertFileExists($dummyPath);

        // 1st delete request via POST body
        $delRes1 = $this->actingAs($creator)->post('/backend/system-backup/delete', [
            'filename' => $dummyName,
        ]);
        $delRes1->assertRedirect(route('backend.system_backup.index'));
        $delRes1->assertSessionHas('success');
        $this->assertFileDoesNotExist($dummyPath);

        // 2nd duplicate/replayed delete request should also succeed without false error flash
        $delRes2 = $this->actingAs($creator)->post('/backend/system-backup/delete', [
            'filename' => $dummyName,
        ]);
        $delRes2->assertRedirect(route('backend.system_backup.index'));
        $delRes2->assertSessionHas('success');

        SystemBackupService::clearProgress();
    }
}
