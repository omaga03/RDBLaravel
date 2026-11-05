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

    public function test_frontend_manual_route_is_accessible_to_guests()
    {
        $response = $this->get(route('manuals.frontend'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('เล่มที่ 1: คู่มือการใช้งานระบบหน้าบ้าน');
        // Ensure no backend manual links are present in Volume 1 topbar
        $response->assertDontSee('VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL.html');
        $response->assertDontSee('index.html');
        $response->assertSee('href="/login"', false);
    }

    public function test_guest_is_redirected_to_login_when_accessing_backend_manuals()
    {
        // 1. All Manuals Index
        $resIndex = $this->get(route('manuals.index'));
        $resIndex->assertRedirect(route('login'));

        // 2. Volume 2 Backend Manual
        $resBackend = $this->get(route('manuals.backend'));
        $resBackend->assertRedirect(route('login'));

        // 3. Direct HTML file request
        $resDirect = $this->get('/manuals/VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL.html');
        $resDirect->assertRedirect(route('login'));

        // 4. UAT Plan
        $resUat = $this->get('/manuals/SYSTEM_ACCEPTANCE_TEST_PLAN.html');
        $resUat->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_all_manuals()
    {
        $admin = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        // 1. Index
        $resIndex = $this->actingAs($admin)->get(route('manuals.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('ศูนย์รวมคู่มือการใช้งานระบบฐานข้อมูลงานวิจัย');

        // 2. Volume 2 Backend
        $resBackend = $this->actingAs($admin)->get(route('manuals.backend'));
        $resBackend->assertStatus(200);
        $resBackend->assertSee('เล่มที่ 2: คู่มือการใช้งานระบบหลังบ้าน');

        // 3. Volume 1 Frontend
        $resFrontend = $this->actingAs($admin)->get(route('manuals.frontend'));
        $resFrontend->assertStatus(200);
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
