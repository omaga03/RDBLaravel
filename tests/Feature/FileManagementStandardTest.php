<?php

namespace Tests\Feature;

use App\Models\RdbDip;
use App\Models\RdbProject;
use App\Models\RdbProjectUtilize;
use App\Models\RdbPublished;
use App\Models\RdbProjectTypeSub;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileManagementStandardTest extends TestCase
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
        Storage::fake('public');
    }

    public function test_dip_file_resolution_and_view_routes(): void
    {
        // 1. New file in dips/
        Storage::disk('public')->put('dips/test-patent.pdf', 'dummy content for patent');

        $dip = RdbDip::create([
            'dip_data2_name' => 'ผลงานทดสอบสิทธิบัตร',
            'dip_files' => 'test-patent.pdf',
            'dip_path' => 'dips/test-patent.pdf',
            'data_show' => 1,
            'user_created' => 1,
            'user_updated' => 1,
        ]);

        $this->assertEquals('dips/test-patent.pdf', $dip->getDipFilePath());

        // Frontend view_file
        $response = $this->get(route('frontend.rdbdip.view_file', $dip->dip_id));
        $response->assertOk();

        // Frontend show page should render button linking to route
        $showResponse = $this->get(route('frontend.rdbdip.show', $dip->dip_id));
        $showResponse->assertOk();
        $showResponse->assertSee(route('frontend.rdbdip.view_file', $dip->dip_id));

        // Backend view_file
        $admin = User::find(129) ?? User::first();
        $backendResponse = $this->actingAs($admin)->get(route('backend.rdb_dip.view_file', $dip->dip_id));
        $backendResponse->assertOk();

        // 2. Missing file returns 404
        $dipMissing = RdbDip::create([
            'dip_data2_name' => 'ผลงานที่ยังไม่มีไฟล์จริง',
            'dip_files' => 'missing-file.pdf',
            'data_show' => 1,
            'user_created' => 1,
            'user_updated' => 1,
        ]);
        $missingResponse = $this->get(route('frontend.rdbdip.view_file', $dipMissing->dip_id));
        $missingResponse->assertNotFound();

        // Clean up
        $dip->delete();
        $dipMissing->delete();
    }

    public function test_utilize_file_resolution_and_view_routes(): void
    {
        Storage::disk('public')->put('utilize/test-utilize.pdf', 'dummy content for utilize');

        $utz = RdbProjectUtilize::create([
            'utz_detail' => 'การนำไปใช้ประโยชน์ทดสอบ',
            'utz_files' => 'test-utilize.pdf',
            'uf_path' => 'utilize/test-utilize.pdf',
            'data_show' => 1,
            'user_created' => 1,
            'user_updated' => 1,
        ]);

        $this->assertEquals('utilize/test-utilize.pdf', $utz->getUtilizeFilePath());

        // Frontend view_file
        $response = $this->get(route('frontend.rdbprojectutilize.view_file', $utz->utz_id));
        $response->assertOk();

        // Frontend show page links to route
        $showResponse = $this->get(route('frontend.rdbprojectutilize.show', $utz->utz_id));
        $showResponse->assertOk();
        $showResponse->assertSee(route('frontend.rdbprojectutilize.view_file', $utz->utz_id));

        $utz->delete();
    }

    public function test_project_type_sub_file_resolution_and_view_routes(): void
    {
        Storage::disk('public')->put('project_types_sub/test-pts.pdf', 'dummy content for pts');

        $pts = RdbProjectTypeSub::create([
            'pts_name' => 'ประเภทย่อยทดสอบ',
            'pts_file' => 'test-pts.pdf',
            'pts_path' => 'project_types_sub/test-pts.pdf',
            'pt_id' => 1,
            'user_created' => 1,
            'user_updated' => 1,
        ]);

        $this->assertEquals('project_types_sub/test-pts.pdf', $pts->getTypeSubFilePath());

        $admin = User::find(129) ?? User::first();
        $backendResponse = $this->actingAs($admin)->get(route('backend.rdbprojecttypesub.view_file', $pts->pts_id));
        $backendResponse->assertOk();

        $pts->delete();
    }

    public function test_backend_project_and_published_view_routes(): void
    {
        $admin = User::find(129) ?? User::first();

        // 1. Project Report and Abstract
        Storage::disk('public')->put('reports/test-rep.pdf', 'dummy report content');
        Storage::disk('public')->put('abstracts/test-abs.pdf', 'dummy abstract content');

        $project = RdbProject::create([
            'pro_nameTH' => 'โครงการทดสอบไฟล์',
            'pro_budget' => 50000,
            'pro_file' => 'test-rep.pdf',
            'pro_file_path' => 'reports/test-rep.pdf',
            'pro_abstract_file' => 'test-abs.pdf',
            'pro_abstract_file_path' => 'abstracts/test-abs.pdf',
            'pro_file_show' => 0,
            'data_show' => 1,
            'user_created' => 1,
            'user_updated' => 1,
        ]);

        $this->assertEquals('reports/test-rep.pdf', $project->getReportFilePath());
        $this->assertEquals('abstracts/test-abs.pdf', $project->getAbstractFilePath());

        $repResponse = $this->actingAs($admin)->get(route('backend.rdb_project.view_report', $project->pro_id));
        $repResponse->assertOk();

        $absResponse = $this->actingAs($admin)->get(route('backend.rdb_project.view_abstract', $project->pro_id));
        $absResponse->assertOk();

        // Backend show page should see route links
        $backendShow = $this->actingAs($admin)->get(route('backend.rdb_project.show', $project->pro_id));
        $backendShow->assertOk();
        $backendShow->assertSee(route('backend.rdb_project.view_report', $project->pro_id));
        $backendShow->assertSee(route('backend.rdb_project.view_abstract', $project->pro_id));

        // 2. Published Paper
        Storage::disk('public')->put('published/test-pub.pdf', 'dummy pub content');

        $published = RdbPublished::create([
            'pub_name' => 'บทความทดสอบไฟล์',
            'pub_name_journal' => 'วารสารทดสอบ',
            'pub_date' => date('Y-m-d'),
            'pub_file' => 'test-pub.pdf',
            'pf_path' => 'published/test-pub.pdf',
            'data_show' => 1,
            'user_created' => 1,
            'user_updated' => 1,
        ]);

        $this->assertEquals('published/test-pub.pdf', $published->getPublishedFilePath());

        $pubBackend = $this->actingAs($admin)->get(route('backend.rdb_published.view_file', $published->id));
        $pubBackend->assertOk();

        $pubShow = $this->actingAs($admin)->get(route('backend.rdb_published.show', $published->id));
        $pubShow->assertOk();
        $pubShow->assertSee(route('backend.rdb_published.view_file', $published->id));

        $project->delete();
        $published->delete();
    }
}
