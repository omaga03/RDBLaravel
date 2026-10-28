<?php

namespace Tests\Feature\Backend;

use App\Models\RdbProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDspaceBatchExportTest extends TestCase
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
     * Test access restriction for unauthorized user
     */
    public function test_guest_cannot_access_dspace_index(): void
    {
        $response = $this->get(route('backend.rdb_project.dspace_index'));
        $response->assertRedirect('/login');
    }

    /**
     * Test authorized admin can access dspace batch index
     */
    public function test_admin_can_access_dspace_batch_index(): void
    {
        $admin = User::find(129) ?? User::whereHas('roles', fn($q) => $q->where('item_name', 'The Creator'))->first();

        if (!$admin) {
            $this->markTestSkipped('No admin user found');
        }

        $response = $this->actingAs($admin)->get(route('backend.rdb_project.dspace_index'));
        $response->assertStatus(200);
        $response->assertSee('ระบบส่งออก DSpace (Batch Mode)');
        $response->assertSee('แบ่งกลุ่มละ:');
        $response->assertSee('คำแนะนำ:');
    }

    /**
     * Test batch export generates zip file
     */
    public function test_admin_can_export_dspace_batch(): void
    {
        $admin = User::find(129) ?? User::whereHas('roles', fn($q) => $q->where('item_name', 'The Creator'))->first();

        if (!$admin) {
            $this->markTestSkipped('No admin user found');
        }

        $project = RdbProject::where('data_show', 1)->first();
        if (!$project) {
            $this->markTestSkipped('No project found');
        }

        $response = $this->actingAs($admin)->post(route('backend.rdb_project.export_dspace_batch'), [
            'ids_json' => json_encode([$project->pro_id]),
            'part_label' => '99',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('DSpace_Export_Part_99.zip', $response->headers->get('content-disposition'));
    }

    /**
     * Test update batch status via AJAX
     */
    public function test_admin_can_update_batch_status(): void
    {
        $admin = User::find(129) ?? User::whereHas('roles', fn($q) => $q->where('item_name', 'The Creator'))->first();

        if (!$admin) {
            $this->markTestSkipped('No admin user found');
        }

        $project = RdbProject::where('data_show', 1)->first();
        if (!$project) {
            $this->markTestSkipped('No project found');
        }

        // Save original todspace
        $originalTodspace = $project->todspace;

        $response = $this->actingAs($admin)->postJson(route('backend.rdb_project.update_dspace_batch_status'), [
            'ids' => [$project->pro_id],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('rdb_project', [
            'pro_id' => $project->pro_id,
            'todspace' => 1,
        ]);

        // Restore original value
        RdbProject::where('pro_id', $project->pro_id)->update([
            'todspace' => $originalTodspace,
        ]);
    }
}
