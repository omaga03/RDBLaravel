<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SystemCommandBackendTest extends TestCase
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

    public function test_superadmin_can_access_system_commands_dashboard()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin user found');
        }

        $response = $this->actingAs($admin)->get(route('backend.system_commands.index'));
        $response->assertStatus(200);
        $response->assertSee('ศูนย์จัดการคำสั่งระบบและการบำรุงรักษา');
        $response->assertSee('คำสั่งสร้างภาพหน้าปก');
        $response->assertSee('คำสั่งตรวจนับจำนวนหน้า');
        $response->assertSee('ล้างแคชระบบทันที');
    }

    public function test_regular_researcher_cannot_access_system_commands()
    {
        $researcher = User::whereHas('roles', fn($q) => $q->where('item_name', 'Researcher'))->first();
        if (!$researcher) {
            $this->markTestSkipped('No researcher user found');
        }

        $response = $this->actingAs($researcher)->get(route('backend.system_commands.index'));
        $response->assertStatus(403);
    }

    public function test_superadmin_can_run_clear_cache_command()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin user found');
        }

        $response = $this->actingAs($admin)->post(route('backend.system_commands.run'), [
            'command' => 'clear_cache',
        ]);

        $response->assertRedirect(route('backend.system_commands.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('command_name');
        $response->assertSessionHas('command_output');
    }
}
