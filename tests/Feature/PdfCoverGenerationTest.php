<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Services\PdfCoverService;
use App\Services\DSpaceExportService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class PdfCoverGenerationTest extends TestCase
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
     * Helper สำหรับสร้างไฟล์ PDF จำลองอย่างง่าย
     */
    protected function createDummyPdfContent(): string
    {
        return "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\nxref\n0 4\n0000000000 65535 f\n0000000010 00000 n\n0000000060 00000 n\n0000000117 00000 n\ntrailer\n<< /Size 4 /Root 1 0 R >>\nstartxref\n190\n%%EOF";
    }

    public function test_pdf_cover_service_extracts_or_generates_webp_cover()
    {
        $service = app(PdfCoverService::class);
        $tempPdf = tempnam(sys_get_temp_dir(), 'test_pdf_') . '.pdf';
        file_put_contents($tempPdf, $this->createDummyPdfContent());

        $targetWebp = tempnam(sys_get_temp_dir(), 'test_cover_') . '.webp';

        $success = $service->extractFirstPageToWebp($tempPdf, $targetWebp, [
            'type' => 'รายงานวิจัยฉบับสมบูรณ์',
            'title' => 'Test Project Cover Generation',
            'author' => 'Test Researcher',
        ]);

        $this->assertTrue($success);
        $this->assertFileExists($targetWebp);
        $this->assertGreaterThan(0, filesize($targetWebp));

        // ตรวจสอบว่าเป็น WebP จริงผ่าน getimagesize หรือ GD
        $imgInfo = @getimagesize($targetWebp);
        $this->assertNotEmpty($imgInfo);
        $this->assertEquals('image/webp', $imgInfo['mime']);

        @unlink($tempPdf);
        @unlink($targetWebp);
    }

    public function test_project_cover_generation_and_cover_url()
    {
        $project = RdbProject::first();
        if (!$project) {
            $this->markTestSkipped('No project found in database.');
        }

        // วางไฟล์ PDF จำลอง
        $pdfPath = 'project/test_report.pdf';
        Storage::disk('public')->put($pdfPath, $this->createDummyPdfContent());
        $project->pro_file = 'test_report.pdf';
        $project->pro_file_path = $pdfPath;
        $project->pro_file_show = 0; // เปิดเผยแพร่
        $project->save();

        $service = app(PdfCoverService::class);
        $coverPath = $service->generateProjectCover($project, true);

        $this->assertNotNull($coverPath);
        $this->assertStringEndsWith('.webp', $coverPath);
        Storage::disk('public')->assertExists($coverPath);

        // ตรวจสอบ accessor getCoverUrlAttribute
        $project->refresh();
        $this->assertNotNull($project->cover_url);
        $this->assertStringContainsString($coverPath, $project->cover_url);
    }

    public function test_published_cover_generation_and_cover_url()
    {
        $published = RdbPublished::first();
        if (!$published) {
            $this->markTestSkipped('No published paper found in database.');
        }

        $pdfPath = 'published/test_paper.pdf';
        Storage::disk('public')->put($pdfPath, $this->createDummyPdfContent());
        $published->pub_file = 'test_paper.pdf';
        $published->data_show = 1;
        $published->save();

        $service = app(PdfCoverService::class);
        $coverPath = $service->generatePublishedCover($published, true);

        $this->assertNotNull($coverPath);
        $this->assertStringEndsWith('.webp', $coverPath);
        Storage::disk('public')->assertExists($coverPath);

        $published->refresh();
        $this->assertNotNull($published->cover_url);
        $this->assertStringContainsString($coverPath, $published->cover_url);
    }

    public function test_generate_pdf_covers_artisan_command_runs_successfully()
    {
        $this->artisan('rdb:generate-pdf-covers', [
            '--module' => 'all',
            '--limit' => 2,
        ])->assertExitCode(0);
    }

    public function test_backend_regenerate_cover_routes()
    {
        $admin = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin user found');
        }

        $project = RdbProject::first();
        if ($project) {
            $response = $this->actingAs($admin)->post(route('backend.rdb_project.regenerate_cover', $project->pro_id));
            $response->assertSessionHas('success');
            $response->assertRedirect();
        }

        $published = RdbPublished::first();
        if ($published) {
            $response = $this->actingAs($admin)->post(route('backend.rdb_published.regenerate_cover', $published->id));
            $response->assertSessionHas('success');
            $response->assertRedirect();
        }
    }
}
