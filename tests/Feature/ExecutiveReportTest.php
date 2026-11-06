<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RdbYear;
use App\Models\RdbDepartment;

class ExecutiveReportTest extends TestCase
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

    public function test_guest_is_redirected_from_executive_report()
    {
        $response = $this->get(route('backend.reports.executive_summary'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_view_executive_summary_report()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $response = $this->actingAs($admin)->get(route('backend.reports.executive_summary'));
        $response->assertStatus(200);
        $response->assertSee('เล่มรายงานสรุปภาพรวมงานวิจัยสำหรับผู้บริหาร');
        $response->assertSee('ตัวชี้วัดผลผลิตสำคัญ');
        $response->assertSee('คุณภาพผลงานตีพิมพ์ระดับสากลและระดับชาติ');
        $response->assertSee('Scopus Q1');
        $response->assertSee('มิติการนำผลงานวิจัยไปใช้ประโยชน์ 4 ด้าน');
        $response->assertSee('โครงการวิจัยที่ได้รับงบประมาณสูงสุด');
        $response->assertSee('ประเภททุนหลัก');
        $response->assertSee('ววน. / สกสว.');
        $response->assertSee('พิมพ์เล่มรายงาน / บันทึก PDF');
    }

    public function test_filter_by_fiscal_year_updates_report()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $year = RdbYear::first();
        if (!$year) {
            $this->markTestSkipped('No year found');
        }

        $response = $this->actingAs($admin)->get(route('backend.reports.executive_summary', ['year_id' => $year->year_id]));
        $response->assertStatus(200);
        $response->assertSee('ประจำปีงบประมาณ พ.ศ. ' . $year->year_name);
    }

    public function test_filter_by_department_updates_report()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $dept = RdbDepartment::first();
        if (!$dept) {
            $this->markTestSkipped('No department found');
        }

        $response = $this->actingAs($admin)->get(route('backend.reports.executive_summary', ['department_id' => $dept->department_id]));
        $response->assertStatus(200);
        $response->assertSee($dept->department_nameTH ?? $dept->department_name);
    }
}
