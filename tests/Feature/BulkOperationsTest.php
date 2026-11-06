<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\RdbProjectStatus;

class BulkOperationsTest extends TestCase
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

    public function test_guest_cannot_access_bulk_endpoints()
    {
        $this->post(route('backend.rdb_project.bulk_status'))->assertRedirect(route('login'));
        $this->post(route('backend.rdb_project.bulk_visibility'))->assertRedirect(route('login'));
        $this->post(route('backend.rdb_project.bulk_export'))->assertRedirect(route('login'));
        $this->post(route('backend.rdb_published.bulk_status'))->assertRedirect(route('login'));
        $this->post(route('backend.rdb_published.bulk_export'))->assertRedirect(route('login'));
    }

    public function test_admin_can_bulk_update_project_status()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $projects = RdbProject::where('data_show', 1)->take(2)->get();
        if ($projects->count() < 2) {
            $this->markTestSkipped('Not enough projects found');
        }

        $ids = $projects->pluck('pro_id')->toArray();
        $targetStatus = RdbProjectStatus::where('ps_id', 2)->first() ?? RdbProjectStatus::first();

        $response = $this->actingAs($admin)->post(route('backend.rdb_project.bulk_status'), [
            'selected_ids' => implode(',', $ids),
            'ps_id' => $targetStatus->ps_id,
        ]);

        $response->assertSessionHas('success');

        foreach ($ids as $id) {
            $this->assertDatabaseHas('rdb_project', [
                'pro_id' => $id,
                'ps_id' => $targetStatus->ps_id,
            ]);
        }
    }

    public function test_admin_can_bulk_update_project_visibility()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $projects = RdbProject::where('data_show', 1)->take(2)->get();
        if ($projects->count() < 2) {
            $this->markTestSkipped('Not enough projects found');
        }

        $ids = $projects->pluck('pro_id')->toArray();

        $response = $this->actingAs($admin)->post(route('backend.rdb_project.bulk_visibility'), [
            'selected_ids' => implode(',', $ids),
            'pro_file_show' => 0, // Public
        ]);

        $response->assertSessionHas('success');

        foreach ($ids as $id) {
            $this->assertDatabaseHas('rdb_project', [
                'pro_id' => $id,
                'pro_file_show' => 0,
            ]);
        }
    }

    public function test_admin_can_bulk_export_projects_csv()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $projects = RdbProject::where('data_show', 1)->take(2)->get();
        if ($projects->count() < 2) {
            $this->markTestSkipped('Not enough projects found');
        }

        $ids = $projects->pluck('pro_id')->toArray();

        $response = $this->actingAs($admin)->post(route('backend.rdb_project.bulk_export'), [
            'selected_ids' => implode(',', $ids),
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('รหัสโครงการ', $content);
        $this->assertStringContainsString('ชื่อโครงการ (ไทย)', $content);
    }

    public function test_admin_can_bulk_update_published_status()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $pubs = RdbPublished::take(2)->get();
        if ($pubs->count() < 2) {
            $this->markTestSkipped('Not enough published items found');
        }

        $ids = $pubs->pluck('id')->toArray();

        $response = $this->actingAs($admin)->post(route('backend.rdb_published.bulk_status'), [
            'selected_ids' => implode(',', $ids),
            'action_type' => 'data_show',
            'status_val' => 1,
        ]);

        $response->assertSessionHas('success');

        foreach ($ids as $id) {
            $this->assertDatabaseHas('rdb_published', [
                'id' => $id,
                'data_show' => 1,
            ]);
        }
    }

    public function test_admin_can_bulk_export_published_csv()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $pubs = RdbPublished::take(2)->get();
        if ($pubs->count() < 2) {
            $this->markTestSkipped('Not enough published items found');
        }

        $ids = $pubs->pluck('id')->toArray();

        $response = $this->actingAs($admin)->post(route('backend.rdb_published.bulk_export'), [
            'selected_ids' => implode(',', $ids),
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('ชื่อผลงานตีพิมพ์', $content);
    }

    public function test_empty_selection_returns_error()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $response = $this->actingAs($admin)->post(route('backend.rdb_project.bulk_export'), [
            'selected_ids' => '',
        ]);

        $response->assertSessionHas('error');
    }
}
