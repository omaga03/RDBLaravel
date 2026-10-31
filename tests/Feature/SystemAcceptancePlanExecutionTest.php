<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\RdbDip;
use App\Models\RdbProjectUtilize;
use App\Models\RdbResearcher;
use App\Services\PdfCoverService;
use App\Services\DSpaceExportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SystemAcceptancePlanExecutionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'rdbpcru',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');
    }

    // ==========================================
    // หมวดที่ 1: Environment & Pre-flight Checks
    // ==========================================

    public function test_env_01_php_required_extensions_are_loaded()
    {
        $requiredExtensions = ['pdo_mysql', 'curl', 'fileinfo', 'gd', 'intl', 'mbstring', 'openssl', 'zip'];
        foreach ($requiredExtensions as $ext) {
            $this->assertTrue(extension_loaded($ext), "Required PHP extension '{$ext}' is not loaded.");
        }
    }

    public function test_env_02_and_03_database_connection_and_views_exist()
    {
        $this->assertTrue(DB::connection()->getPdo() !== null);

        // ตรวจสอบ View หลัก 5 รายการจากทั้งหมด 46 รายการ
        $sampleViews = ['view_year', 'rdb_project_type', 'rdb_strategic', 'rdb_department', 'rdb_dip_type'];
        foreach ($sampleViews as $view) {
            $exists = DB::select("SHOW TABLES LIKE '{$view}'");
            $this->assertNotEmpty($exists, "View or table '{$view}' does not exist in rdbpcru.");
        }
    }

    public function test_env_04_storage_directories_and_symlink()
    {
        $this->assertTrue(is_dir(storage_path('app/public')));
        $this->assertTrue(file_exists(public_path('storage')));
    }

    // ==========================================
    // หมวดที่ 2: ระบบหน้าบ้าน (Public Portal)
    // ==========================================

    public function test_pub_01_and_02_home_page_and_year_filter()
    {
        $response = $this->get(route('frontend.site.index'));
        $response->assertStatus(200);
        $response->assertSee('ฐานข้อมูลงานวิจัย');

        // ทดสอบกรองปี พ.ศ. 2567
        $filterResponse = $this->get(route('frontend.site.index', ['year_id' => 2567]));
        $filterResponse->assertStatus(200);
    }

    public function test_pub_03_global_search()
    {
        $response = $this->get(route('frontend.site.index', ['search' => 'วิจัย']));
        $response->assertStatus(200);
    }

    public function test_pro_01_to_07_projects_public_list_detail_and_privacy()
    {
        // PRO-01: List
        $res = $this->get(route('frontend.rdbproject.index'));
        $res->assertStatus(200);

        $project = RdbProject::where('data_show', 1)->whereNotNull('pro_file')->first() ?? RdbProject::where('data_show', 1)->first();
        if (!$project) {
            $this->markTestSkipped('No public project found.');
        }

        if (empty($project->pro_file)) {
            $project->pro_file = 'test_report.pdf';
        }

        // PRO-03: Show
        $showRes = $this->get(route('frontend.rdbproject.show', $project->pro_id));
        $showRes->assertStatus(200);

        // PRO-04 & PRO-05: Privacy test
        // 1. เปิดเผย (0)
        $project->pro_file_show = 0;
        $project->save();
        $openRes = $this->get(route('frontend.rdbproject.show', $project->pro_id));
        $openRes->assertStatus(200);

        // 2. ปกปิด (1)
        $project->pro_file_show = 1;
        $project->save();
        $concealedRes = $this->get(route('frontend.rdbproject.show', $project->pro_id));
        $concealedRes->assertStatus(200);
        $concealedRes->assertSee('เล่มรายงานวิจัยถูกปกปิด');
    }

    public function test_pub_01_to_03_publications_public_list_and_detail()
    {
        $res = $this->get(route('frontend.rdbpublished.index'));
        $res->assertStatus(200);

        $item = RdbPublished::where('data_show', 1)->first();
        if ($item) {
            $showRes = $this->get(route('frontend.rdbpublished.show', $item->id));
            $showRes->assertStatus(200);
            $showRes->assertSee('Citations');
        }
    }

    public function test_ip_and_utilize_and_researcher_public_views()
    {
        $this->get(route('frontend.rdbdip.index'))->assertStatus(200);
        $this->get(route('frontend.rdbprojectutilize.index'))->assertStatus(200);
        $this->get(route('frontend.rdbresearcher.index'))->assertStatus(200);
    }

    // ==========================================
    // หมวดที่ 3: ระบบหลังบ้าน & วงจรชีวิตผลงาน (WF-01 ถึง WF-07)
    // ==========================================

    public function test_auth_01_to_04_backend_login_and_admin_access()
    {
        $admin = User::find(129);
        $this->assertNotNull($admin);

        $res = $this->actingAs($admin)->get(route('backend.site.index'));
        $res->assertStatus(200);
    }

    public function test_wf_01_to_07_full_researcher_submission_and_approval_lifecycle()
    {
        $admin = User::find(129);
        $this->assertNotNull($admin);

        // ค้นหาหรือจำลองผลงานตีพิมพ์สำหรับทดสอบวงจร Workflow
        $pub = RdbPublished::first();
        $this->assertNotNull($pub);

        // WF-01: สถานะ Draft
        $pub->workflow_status = 'draft';
        $pub->data_show = 0;
        $pub->save();
        $this->assertEquals('draft', $pub->workflow_status);

        // WF-02: นักวิจัยกดส่งตรวจ (Submit for Review)
        $submitRes = $this->actingAs($admin)->post(route('backend.workflow.submit', ['type' => 'published', 'id' => $pub->id]));
        $submitRes->assertRedirect();
        $pub->refresh();
        $this->assertEquals('pending_review', $pub->workflow_status);
        $this->assertNotNull($pub->submitted_at);

        // WF-03: คิวงานของเจ้าหน้าที่ (Admin Queue)
        $queueRes = $this->actingAs($admin)->get(route('backend.workflow.queue'));
        $queueRes->assertStatus(200);
        $queueRes->assertSee('Review Queue');

        // WF-04: เจ้าหน้าที่ส่งกลับแก้ไข (Revision Required / Reject)
        $rejectRes = $this->actingAs($admin)->post(route('backend.workflow.reject', ['type' => 'published', 'id' => $pub->id]), [
            'remarks' => 'กรุณาแนบไฟล์ PDF ที่มีรายละเอียดครบถ้วน',
        ]);
        $rejectRes->assertRedirect();
        $pub->refresh();
        $this->assertEquals('revision_required', $pub->workflow_status);
        $this->assertEquals('กรุณาแนบไฟล์ PDF ที่มีรายละเอียดครบถ้วน', $pub->rejection_remarks);

        // ส่งตรวจใหม่อีกครั้ง
        $this->actingAs($admin)->post(route('backend.workflow.submit', ['type' => 'published', 'id' => $pub->id]));
        $pub->refresh();
        $this->assertEquals('pending_review', $pub->workflow_status);

        // WF-05: เจ้าหน้าที่อนุมัติเผยแพร่ (Approve & Publish)
        $approveRes = $this->actingAs($admin)->post(route('backend.workflow.approve', ['type' => 'published', 'id' => $pub->id]));
        $approveRes->assertRedirect();
        $pub->refresh();
        $this->assertEquals('published', $pub->workflow_status);
        $this->assertEquals(1, $pub->data_show);

        // WF-06: ขอปลดล็อกเพื่อแก้ไข (Request Unlock) โดยนักวิจัยเจ้าของผลงาน
        $validResearcher = RdbResearcher::first();
        $researcherUser = User::where('researcher_id', $validResearcher->researcher_id)->first();
        if (!$researcherUser) {
            $researcherUser = User::factory()->create([
                'researcher_id' => $validResearcher->researcher_id,
            ]);
        }

        if ($researcherUser) {
            $pub->researcher_id = $validResearcher->researcher_id;
            $pub->workflow_status = 'published';
            $pub->save();

            $unlockReqRes = $this->actingAs($researcherUser)->post(route('backend.workflow.request_unlock', ['type' => 'published', 'id' => $pub->id]), [
                'reason' => 'ต้องการแก้ไขชื่อวารสารให้ถูกต้อง',
            ]);
            $unlockReqRes->assertRedirect();
            $pub->refresh();
            $this->assertEquals('unlock_requested', $pub->workflow_status);

            // WF-07: อนุมัติการปลดล็อก (Approve Unlock -> Move to Draft) โดย Admin
            $approveUnlockRes = $this->actingAs($admin)->post(route('backend.workflow.approve_unlock', ['type' => 'published', 'id' => $pub->id]));
            $approveUnlockRes->assertRedirect();
            $pub->refresh();
            $this->assertEquals('draft', $pub->workflow_status);
        }
    }

    public function test_adm_pro_02_and_03_pdf_page_count_and_cover_webp_generation()
    {
        $project = RdbProject::first();
        $this->assertNotNull($project);

        $tempPdf = tempnam(sys_get_temp_dir(), 'test_p_') . '.pdf';
        file_put_contents($tempPdf, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\nxref\n0 4\n0000000000 65535 f\n0000000010 00000 n\n0000000060 00000 n\n0000000117 00000 n\ntrailer\n<< /Size 4 /Root 1 0 R >>\nstartxref\n190\n%%EOF");

        $targetWebp = tempnam(sys_get_temp_dir(), 'test_c_') . '.webp';
        $coverService = app(PdfCoverService::class);
        $ok = $coverService->extractFirstPageToWebp($tempPdf, $targetWebp, [
            'type' => 'รายงานวิจัยฉบับสมบูรณ์',
            'title' => $project->pro_nameTH ?: 'โครงการทดสอบ',
            'author' => 'นักวิจัย มรภ.เพชรบูรณ์',
        ]);

        $this->assertTrue($ok);
        $this->assertFileExists($targetWebp);
        $this->assertGreaterThan(0, filesize($targetWebp));

        @unlink($tempPdf);
        @unlink($targetWebp);
    }

    // ==========================================
    // หมวดที่ 4: ความสมบูรณ์ของข้อมูลจริง (Legacy Data Verification)
    // ==========================================

    public function test_mig_01_to_05_legacy_data_counts_verification()
    {
        // 1. โครงการวิจัย
        $projectCount = DB::table('rdb_project')->count();
        $this->assertGreaterThanOrEqual(2800, $projectCount, "Expected at least 2,800 projects, got {$projectCount}");

        // 2. ผลงานตีพิมพ์
        $pubCount = DB::table('rdb_published')->count();
        $this->assertGreaterThanOrEqual(1800, $pubCount, "Expected at least 1,800 publications, got {$pubCount}");

        // 3. นักวิจัย
        $resCount = DB::table('rdb_researcher')->count();
        $this->assertGreaterThanOrEqual(1000, $resCount, "Expected at least 1,000 researchers, got {$resCount}");

        // 4. การนำไปใช้ประโยชน์
        $utilizeCount = DB::table('rdb_project_utilize')->count();
        $this->assertGreaterThanOrEqual(1100, $utilizeCount, "Expected at least 1,100 utilizations, got {$utilizeCount}");

        // 5. ทรัพย์สินทางปัญญา
        $dipCount = DB::table('rdb_dip')->count();
        $this->assertGreaterThanOrEqual(50, $dipCount, "Expected at least 50 DIP records, got {$dipCount}");
    }

    // ==========================================
    // หมวดที่ 5: ความปลอดภัย & การส่งออก (Security & Export)
    // ==========================================

    public function test_sec_01_to_05_security_and_export_utf8_bom()
    {
        $admin = User::find(129);
        $this->assertNotNull($admin);

        // ทดสอบ Export โครงการวิจัยต้องมี UTF-8 BOM
        $exportRes = $this->actingAs($admin)->get(route('backend.rdb_project.export'));
        $this->assertEquals(200, $exportRes->getStatusCode());

        ob_start();
        $exportRes->sendContent();
        $content = ob_get_clean();

        $bom = "\xEF\xBB\xBF";
        $this->assertStringStartsWith($bom, $content, "Export file must start with UTF-8 BOM for Microsoft Excel compatibility.");
    }
}
