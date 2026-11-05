<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManualsAccessTest extends TestCase
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

    public function test_frontend_manual_route_is_accessible()
    {
        $response = $this->get(route('manuals.frontend'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('เล่มที่ 1: คู่มือการใช้งานระบบหน้าบ้าน');
    }

    public function test_all_manuals_index_route_is_accessible()
    {
        $response = $this->get(route('manuals.index'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('ศูนย์รวมคู่มือการใช้งานระบบฐานข้อมูลงานวิจัย');
    }

    public function test_backend_manual_route_is_accessible()
    {
        $response = $this->get(route('manuals.backend'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('เล่มที่ 2: คู่มือการใช้งานระบบหลังบ้าน');
    }

    public function test_manual_screenshot_route_serves_image()
    {
        $response = $this->get('/manuals/screenshots/frontend_01_home.png');
        $response->assertStatus(200);
    }

    public function test_frontend_navbar_contains_frontend_manual_icon_link()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee(route('manuals.frontend'));
        $response->assertSee('rdb-manual-link-icon');
        $response->assertSee('คู่มือการใช้งานระบบหน้าบ้าน');
        $response->assertDontSee('href="' . route('manuals.index') . '"', false);
    }

    public function test_backend_navbar_contains_all_manuals_icon_link()
    {
        $admin = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $response = $this->actingAs($admin)->get(route('backend.site.index'));
        $response->assertStatus(200);
        $response->assertSee(route('manuals.index'));
        $response->assertSee('rdb-manual-link-icon');
        $response->assertSee('คู่มือการใช้งานระบบทั้งหมด');
        $response->assertDontSee('href="' . route('manuals.frontend') . '"', false);
    }
}
