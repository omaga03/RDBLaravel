<?php

namespace Tests\Feature\Backend;

use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\User;
use Tests\TestCase;

class PublishedDspaceBatchExportTest extends TestCase
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
     * Test access restriction for unauthorized guest
     */
    public function test_guest_cannot_access_published_dspace_index(): void
    {
        $response = $this->get(route('backend.rdb_published.dspace_index'));
        $response->assertRedirect('/login');

        $responseCheck = $this->get(route('backend.rdb_published.dspace_check'));
        $responseCheck->assertRedirect('/login');
    }

    /**
     * Test authorized admin can access published dspace batch index
     */
    public function test_admin_can_access_published_dspace_batch_index(): void
    {
        $admin = User::find(129) ?? User::first();
        if (!$admin) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->actingAs($admin)->get(route('backend.rdb_published.dspace_index'));
        $response->assertStatus(200);
        $response->assertSee('ระบบส่งออก DSpace ผลงานตีพิมพ์ (Batch Mode)');
        $response->assertSee('แบ่งกลุ่มละ:');
        $response->assertSee('คำแนะนำ:');
        $response->assertSee('ตรวจสอบจำนวนหน้า & สถานะไฟล์ PDF (Check Mode)');
    }

    /**
     * Test authorized admin can access published dspace check page
     */
    public function test_admin_can_access_published_dspace_check(): void
    {
        $admin = User::find(129) ?? User::first();
        if (!$admin) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->actingAs($admin)->get(route('backend.rdb_published.dspace_check'));
        $response->assertStatus(200);
        $response->assertSee('ตรวจสอบจำนวนหน้า & สถานะไฟล์ PDF (Check Mode)');
        $response->assertSee('เช็คอัตโนมัติทั้งหมดในหน้านี้');
        $response->assertSee('ทั้งหมดที่มีไฟล์');
        $response->assertSee('บทความสมบูรณ์');
        $response->assertSee('ตัวอย่างบทความ');
    }

    /**
     * Test batch export generates SAF ZIP package
     */
    public function test_admin_can_export_published_dspace_batch(): void
    {
        $admin = User::find(129) ?? User::first();
        if (!$admin) {
            $this->markTestSkipped('No user found');
        }

        $pub = RdbPublished::where('data_show', 1)->first();
        if (!$pub) {
            $this->markTestSkipped('No publication found');
        }

        $response = $this->actingAs($admin)->post(route('backend.rdb_published.dspace_batch'), [
            'ids_json' => json_encode([$pub->id]),
            'part_label' => '77',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('DSpace_Published_Export_Part_77', $response->headers->get('content-disposition'));
    }

    /**
     * Test update batch status via AJAX
     */
    public function test_admin_can_update_published_batch_status(): void
    {
        $admin = User::find(129) ?? User::first();
        if (!$admin) {
            $this->markTestSkipped('No user found');
        }

        $pub = RdbPublished::where('data_show', 1)->first();
        if (!$pub) {
            $this->markTestSkipped('No publication found');
        }

        $original = $pub->todspace;

        $response = $this->actingAs($admin)->postJson(route('backend.rdb_published.dspace_update_status'), [
            'ids' => [$pub->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Restore
        $pub->todspace = $original;
        $pub->save();
    }

    /**
     * Test update single item status (todspace = 1 or 2)
     */
    public function test_admin_can_update_single_item_status(): void
    {
        $admin = User::find(129) ?? User::first();
        if (!$admin) {
            $this->markTestSkipped('No user found');
        }

        $pub = RdbPublished::where('data_show', 1)->first();
        if (!$pub) {
            $this->markTestSkipped('No publication found');
        }

        $original = $pub->todspace;

        $response = $this->actingAs($admin)->postJson(route('backend.rdb_published.dspace_ajax_update_status'), [
            'id' => $pub->id,
            'status' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 2,
            'status_text' => 'ตัวอย่างบทความ',
        ]);

        // Restore
        $pub->todspace = $original;
        $pub->save();
    }

    /**
     * Test Project 2709 show page displays related utilization with agency and type
     */
    public function test_project_show_displays_related_utilization(): void
    {
        $admin = User::find(129) ?? User::first();
        if (!$admin) {
            $this->markTestSkipped('No user found');
        }

        $project = RdbProject::find(2709);
        if (!$project) {
            $this->markTestSkipped('Project 2709 not found');
        }

        $response = $this->actingAs($admin)->get(route('backend.rdb_project.show', 2709));
        $response->assertStatus(200);
        $response->assertSee('ผลงานที่เกี่ยวข้อง');
        $response->assertSee('การนำไปใช้ประโยชน์ที่เชื่อมโยง');
        // Agency / Department name should be visible
        $response->assertSee('กลุ่มสถาบันเศรษฐกิจพอเพียงเครือข่ายเกษตรอินทรีย์วิถีไทย');
        // Utilization type name should be visible
        $response->assertSee('การนำไปใช้ประโยชน์ในเชิงสาธารณะ');
        // Leader name should be visible
        $response->assertSee('กอแก้ว เกศแก้วเกษร');
    }

    /**
     * Test ultra-high precision PDF page count with Smalot\PdfParser
     */
    public function test_smalot_pdf_parser_precision_page_count(): void
    {
        $this->assertTrue(class_exists(\Smalot\PdfParser\Parser::class), 'Smalot\PdfParser\Parser must be autoloaded');

        $service = app(\App\Services\DSpaceExportService::class);
        $testPdf = public_path('uploads/utilize/utz_1036_1766942354.pdf');

        if (!file_exists($testPdf)) {
            $this->markTestSkipped('Sample PDF not found');
        }

        $pageCount = $service->countPdfPages($testPdf);
        $this->assertEquals(1, $pageCount, 'Smalot parser must accurately identify 1 page for sample PDF');
    }

    /**
     * Test counting pages on secured/AES-encrypted PDF (Publication 303)
     */
    public function test_count_pdf_pages_on_secured_pdf_303(): void
    {
        $admin = User::find(129) ?? User::first();
        if (!$admin) {
            $this->markTestSkipped('No user found');
        }

        $service = app(\App\Services\DSpaceExportService::class);
        $pub303 = RdbPublished::find(303);

        if (!$pub303) {
            $this->markTestSkipped('Publication 303 not found');
        }

        $filePath = $service->resolvePublishedFileOnDisk($pub303);
        $this->assertNotNull($filePath, 'File path must be resolvable');
        $this->assertFileExists($filePath);

        $pages = $service->countPdfPages($filePath);
        $this->assertEquals(8, $pages, 'Secured publication 303 must accurately count 8 pages');

        // Test AJAX endpoint as well
        $response = $this->actingAs($admin)->postJson(route('backend.rdb_published.dspace_ajax_count_page'), [
            'id' => 303,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pages' => 8,
            'message' => 'บันทึกสำเร็จ',
        ]);
    }

    /**
     * Test dspace check page renders engine status notification
     */
    public function test_dspace_check_displays_pdftotext_engine_status(): void
    {
        $admin = User::find(129) ?? User::first();
        if (!$admin) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->actingAs($admin)->get(route('backend.rdb_published.dspace_check'));
        $response->assertStatus(200);

        $dspaceService = app(\App\Services\DSpaceExportService::class);
        if ($dspaceService->getPdftotextBinary()) {
            $response->assertSee('เอนจิน Poppler (pdftotext) พร้อมใช้งาน');
            $response->assertSee('ความแม่นยำสูง 99.99%');
        } else {
            $response->assertSee('กำลังใช้เอนจินสำรอง (Pure PHP Parser)');
        }
    }

    /**
     * Test Artisan rdb:count-pdf-pages command
     */
    public function test_count_pdf_pages_console_command(): void
    {
        $exitCode = $this->artisan('rdb:count-pdf-pages', [
            '--module' => 'project',
            '--limit' => 2,
        ]);

        $exitCode->assertExitCode(0);
    }
}
