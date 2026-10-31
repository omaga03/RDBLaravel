<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\ResearchNews;
use App\Models\ResearchConferenceInThai;
use App\Services\DSpaceExportService;
use App\Services\NriisSyncService;
use Illuminate\Support\Facades\Http;

class IntegrationsAndAutomationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.database' => 'rdbpcru',
            'database.connections.mysql.password' => '',
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');
    }

    public function test_pmis_endpoints_return_successful_json()
    {
        $response1 = $this->get('/rdbresearcher/datagetresearchbypmis');
        $response1->assertStatus(200);
        $response1->assertHeader('Content-Type', 'application/json');

        $response2 = $this->get('/rdbresearcher/datagetpublicationbypmis');
        $response2->assertStatus(200);
        $response2->assertHeader('Content-Type', 'application/json');

        $response3 = $this->get('/api/pmis/research');
        $response3->assertStatus(200);

        $response4 = $this->get('/api/pmis/publication');
        $response4->assertStatus(200);
    }

    public function test_rss_feeds_return_valid_rss_xml()
    {
        $responseNews = $this->get('/rss/news');
        $responseNews->assertStatus(200);
        $this->assertStringContainsString('application/rss+xml', $responseNews->headers->get('Content-Type'));
        $this->assertStringContainsString('<rss version="2.0"', $responseNews->getContent());

        $responseConf = $this->get('/rss/conferences');
        $responseConf->assertStatus(200);
        $this->assertStringContainsString('application/rss+xml', $responseConf->headers->get('Content-Type'));
        $this->assertStringContainsString('<rss version="2.0"', $responseConf->getContent());
    }

    public function test_dspace_export_service_creates_valid_zip()
    {
        $service = app(DSpaceExportService::class);

        $project = RdbProject::first();
        if ($project) {
            $zipProject = $service->exportProject($project);
            $this->assertNotNull($zipProject);
            $this->assertFileExists($zipProject);

            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($zipProject));
            $this->assertNotFalse($zip->locateName('dublin_core.xml'));
            $this->assertNotFalse($zip->locateName('metadata_dspace.xml'));
            $zip->close();
            @unlink($zipProject);
        }

        $published = RdbPublished::first();
        if ($published) {
            $zipPub = $service->exportPublished($published);
            $this->assertNotNull($zipPub);
            $this->assertFileExists($zipPub);

            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($zipPub));
            $this->assertNotFalse($zip->locateName('dublin_core.xml'));
            $this->assertNotFalse($zip->locateName('metadata_dspace.xml'));
            $zip->close();
            @unlink($zipPub);
        }
    }

    public function test_nriis_sync_service_handles_api_gracefully()
    {
        Http::fake([
            'https://nriis.go.th/home/api/v1/general/news/999999' => Http::response([
                'NID' => '999999',
                'NNAME' => 'ข่าวกองทุนทดสอบ NRIIS Test รายละเอียด',
                'NTNAME' => 'ทุนวิจัย',
                'createdateTH' => '2026-10-25',
                'NDETAIL' => '<p>รายละเอียดการรับสมัครทุนวิจัย</p>',
            ], 200),
            'https://nriis.go.th/home/api/v1/general/news*' => Http::response([
                'data' => [
                    [
                        'NID' => '999999',
                        'NNAME' => 'ข่าวกองทุนทดสอบ NRIIS Test',
                        'NTNAME' => 'ทุนวิจัย',
                        'createdateTH' => '2026-10-25',
                    ]
                ]
            ], 200),
        ]);

        $service = app(NriisSyncService::class);
        $stats = $service->sync(1, true);

        $this->assertEquals(1, $stats['synced']);
        $this->assertDatabaseHas('research_news', [
            'news_id' => '999999',
            'news_name' => 'ข่าวกองทุนทดสอบ NRIIS Test รายละเอียด',
        ]);

        ResearchNews::where('news_id', '999999')->delete();
    }

    public function test_facebook_conference_sync_service_handles_sync()
    {
        $mockHtml = '<a href="https://www.facebook.com/photo.php?fbid=123456789012345">Photo</a>';
        $mockPhoto = '{"text":"การประชุมวิชาการระดับชาติ ณ มหาวิทยาลัยขอนแก่น วันที่ 15-16 พฤศจิกายน 2569","image":{"uri":"https:\/\/example.com\/fake_banner.jpg"}}';

        Http::fake([
            'https://www.facebook.com/prachume/photos*' => Http::response($mockHtml, 200),
            'https://www.facebook.com/photo.php?fbid=123456789012345' => Http::response($mockPhoto, 200),
            'https://example.com/fake_banner.jpg' => Http::response('fake-image-bytes', 200),
        ]);

        $service = app(\App\Services\FacebookConferenceSyncService::class);
        $result = $service->sync(1);

        $this->assertArrayHasKey('synced', $result);
        $this->assertArrayHasKey('skipped', $result);
        $this->assertArrayHasKey('failed', $result);

        \App\Models\ResearchConference::where('con_id', '123456789012345')->delete();
    }

    public function test_download_security_default_policy_is_public()
    {
        $defaults = \App\Models\SystemSetting::getDefaults();
        $this->assertEquals('public', $defaults['download_policy_full_report']);
        $this->assertEquals('public', $defaults['download_policy_abstract']);
        $this->assertEquals('public', $defaults['download_policy_attachments']);

        $this->assertEquals('public', \App\Models\SystemSetting::get('download_policy_full_report'));
        $this->assertEquals('public', \App\Models\SystemSetting::get('download_policy_abstract'));
        $this->assertEquals('public', \App\Models\SystemSetting::get('download_policy_attachments'));
    }

    public function test_backend_subnav_and_dspace_menu_structure()
    {
        $admin = User::find(129) ?? User::first();
        if (!$admin) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->actingAs($admin)->get('/backend');
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Redundant dashboard removed from subnav
        $this->assertStringNotContainsString('class="nav-link active fw-bold" href="http://localhost/backend"><i class="bi bi-speedometer2 text-primary me-1.5"></i> แดชบอร์ด', $content);

        // 2. Trash separated from drafts
        $this->assertStringContainsString('ถังขยะและกู้คืนข้อมูล', $content);

        // 3. DSpace submenus in project and published
        $this->assertStringContainsString('ส่งออก DSpace (คลังปัญญา)', $content);

        // 4. Strategic removed from menu
        $this->assertStringNotContainsString('ยุทธศาสตร์</a>', $content);

        // 5. Check DSpace index pages HTTP 200
        $projectDspace = $this->actingAs($admin)->get(route('backend.rdb_project.dspace_index'));
        $projectDspace->assertStatus(200);
        $projectDspace->assertSee('DSpace (Batch Mode)');

        $publishedDspace = $this->actingAs($admin)->get(route('backend.rdb_published.dspace_index'));
        $publishedDspace->assertStatus(200);
        $publishedDspace->assertSee('DSpace (Batch Mode)');

        // 6. Test specific export zip endpoint
        $published = RdbPublished::find(1997) ?? RdbPublished::first();
        if ($published) {
            $exportResp = $this->actingAs($admin)->get(route('backend.rdb_published.export_dspace', ['id' => $published->id]));
            $this->assertTrue(in_array($exportResp->getStatusCode(), [200, 302]));
            if ($exportResp->getStatusCode() === 200) {
                $this->assertStringContainsString('zip', $exportResp->headers->get('Content-Type'));
            }
        }
    }

    public function test_researcher_menu_and_dspace_hidden_for_researchers_and_faculty_users()
    {
        $researcher = User::where('username', '656236')->first()
            ?? User::whereNotNull('researcher_id')->where('id', '!=', 129)->first();
        $this->assertNotNull($researcher);

        // 1. In backend subnav, researcher cannot see DSpace or Researcher menu
        $response = $this->actingAs($researcher)->get('/backend');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringNotContainsString('ส่งออก DSpace (คลังปัญญา)', $content);
        $this->assertStringNotContainsString('รายการนักวิจัยทั้งหมด', $content);
        $this->assertStringContainsString('ส่งออกข้อมูลโครงการ (CSV)', $content);
        $this->assertStringContainsString('ส่งออกข้อมูลตีพิมพ์ (CSV)', $content);

        // 2. Direct access to DSpace endpoints blocked for researcher -> 403
        $this->actingAs($researcher)->get(route('backend.rdb_project.dspace_index'))->assertStatus(403);
        $this->actingAs($researcher)->get(route('backend.rdb_published.dspace_index'))->assertStatus(403);

        // 3. Direct access to researcher index blocked -> 403
        $this->actingAs($researcher)->get(route('backend.rdb_researcher.index'))->assertStatus(403);
    }

    public function test_csv_export_scoping_for_researcher_and_users()
    {
        $researcher = User::where('username', '656236')->first()
            ?? User::whereNotNull('researcher_id')->where('id', '!=', 129)->first();
        $this->assertNotNull($researcher);

        $admin = User::find(129) ?? User::where('username', 'admin')->first();
        $this->assertNotNull($admin);

        // 1. Researcher exports project CSV: must only contain their own project
        $resProjResponse = $this->actingAs($researcher)->get(route('backend.rdb_project.export'));
        $resProjResponse->assertStatus(200);
        $resContent = $resProjResponse->streamedContent();
        $resLines = array_filter(explode("\n", trim($resContent)));
        $resProjectRows = max(0, count($resLines) - 1);

        // Admin exports project CSV: contains all university projects
        $adminProjResponse = $this->actingAs($admin)->get(route('backend.rdb_project.export'));
        $adminProjResponse->assertStatus(200);
        $adminContent = $adminProjResponse->streamedContent();
        $adminLines = array_filter(explode("\n", trim($adminContent)));
        $adminProjectRows = max(0, count($adminLines) - 1);

        // Assert scoping: researcher gets only their 1 record, while admin gets > 2000
        $this->assertEquals(1, $resProjectRows);
        $this->assertGreaterThan(2000, $adminProjectRows);

        // 2. Researcher cannot export all researchers -> 403
        $this->actingAs($researcher)->get(route('backend.rdb_researcher.export'))->assertStatus(403);
    }

    public function test_utilize_types_are_ordered_by_display_order()
    {
        $admin = User::find(129) ?? User::first();
        $response = $this->actingAs($admin)->get(route('backend.rdbprojectutilizetype.index'));
        $response->assertStatus(200);

        $items = $response->viewData('items');
        $indexes = $items->pluck('utz_type_index')->filter()->values()->toArray();
        $sorted = $indexes;
        sort($sorted);
        $this->assertEquals($sorted, $indexes);
    }

    public function test_research_conference_backend_displays_images_and_filters_date_range()
    {
        $admin = User::find(129) ?? User::first();
        $response = $this->actingAs($admin)->get(route('backend.research_conference.index'));
        $response->assertStatus(200);

        $items = $response->viewData('items');
        $this->assertNotEmpty($items);

        // Verify that items with con_img have valid non-empty image_url
        $hasImage = false;
        foreach ($items as $item) {
            if (!empty($item->con_img)) {
                $hasImage = true;
                $this->assertNotNull($item->image_url);
                $this->assertNotEmpty($item->image_url);
            }
        }
        $this->assertTrue($hasImage, 'At least one conference item should have an image');

        // Test filtering by date range (created_at)
        $firstItem = $items->first();
        $createdDate = date('Y-m-d', strtotime($firstItem->created_at));

        $filterResponse = $this->actingAs($admin)->get(route('backend.research_conference.index', [
            'date_type' => 'created_at',
            'date_start' => $createdDate,
            'date_end' => $createdDate,
        ]));
        $filterResponse->assertStatus(200);

        $filteredItems = $filterResponse->viewData('items');
        foreach ($filteredItems as $fItem) {
            $this->assertEquals($createdDate, date('Y-m-d', strtotime($fItem->created_at)));
        }
    }

    public function test_qa_dashboard_renders_successfully_with_all_metrics()
    {
        $response = $this->get(route('frontend.dashboard.qa'));
        $response->assertStatus(200);

        // Verify view data
        $this->assertNotNull($response->viewData('qaPubTable'));
        $this->assertNotNull($response->viewData('qaProjectTable'));
        $this->assertNotNull($response->viewData('qaPubFacultyBranchTable'));
        $this->assertNotNull($response->viewData('qaProjectFacultyBranchTable'));
        $this->assertNotNull($response->viewData('qaPubFacultyGroups'));
        $this->assertNotNull($response->viewData('qaProjectFacultyGroups'));
        $this->assertNotNull($response->viewData('qaUtilTable'));
        $this->assertNotNull($response->viewData('firstProjectList'));
        $this->assertEquals('faculty', $response->viewData('scope'));

        // Verify key text elements in rendered HTML
        $response->assertSee('สารสนเทศเพื่อการประกันคุณภาพ');
        $response->assertSee('คะแนนถ่วงน้ำหนักผลงานตีพิมพ์');
        $response->assertSee('สัดส่วนโครงการและงบวิจัยต่อบุคลากร');
        $response->assertSee('ผลงานวิจัยนำไปใช้ประโยชน์ที่ผ่านเกณฑ์ QA');
        $response->assertSee('นักวิจัยรุ่นใหม่');
        $response->assertSee('เฉพาะคณะวิชาการ (6 คณะ)');
        $response->assertSee('ทุกหน่วยงาน (รวมส่วนกลาง/สำนัก)');
        $response->assertSee('คณะและกลุ่มสาขาวิชา');
        $response->assertSee('คะแนนสาขา (เต็ม 5)');
        $response->assertSee('คะแนนภาพรวมคณะ (เต็ม 5)');

        // Verify table collapse containers and all visual chart canvas elements
        $response->assertSee('qaPubTablesCollapse');
        $response->assertSee('chartPubFacultyScore');
        $response->assertSee('chartPubBranchScore');
        $response->assertSee('chartPubWeightStack');
        $response->assertSee('chartPubBranchCompare');

        $response->assertSee('qaProjectTablesCollapse');
        $response->assertSee('chartProjectFacultyScore');
        $response->assertSee('chartProjectBranchCompare');
        $response->assertSee('chartProjectBudgetPerPerson');
        $response->assertSee('chartProjectRatioPct');

        $response->assertSee('qaUtilTablesCollapse');
        $response->assertSee('chartUtilFaculty');
        $response->assertSee('chartUtilOverallDonut');
        $response->assertSee('chartUtilPctFaculty');

        $response->assertSee('qaFirstTablesCollapse');
        $response->assertSee('chartFirstResearchersFaculty');
        $response->assertSee('chartFirstResearchersYear');

        $response->assertSee('แสดงตารางข้อมูลรายละเอียด');
        $response->assertSee('cdn.jsdelivr.net/npm/chart.js');

        // Test with filters matching user scenario: ?department_id=all&year_id=18
        $filterResponse = $this->get(route('frontend.dashboard.qa', ['department_id' => 'all', 'year_id' => 18]));
        $filterResponse->assertStatus(200);
        $this->assertNotEmpty($filterResponse->viewData('qaPubFacultyBranchTable'));
        $this->assertNotEmpty($filterResponse->viewData('qaProjectFacultyBranchTable'));

        // Test with scope=all
        $allResponse = $this->get(route('frontend.dashboard.qa', ['scope' => 'all', 'year_id' => 18]));
        $allResponse->assertStatus(200);
        $this->assertEquals('all', $allResponse->viewData('scope'));

        // Test with specific faculty filter: ?department_id=2&year_id=18
        $deptResponse = $this->get(route('frontend.dashboard.qa', ['department_id' => 2, 'year_id' => 18]));
        $deptResponse->assertStatus(200);

        // Verify that in Section 4, firstProjectList does not contain researchers with null or 0 department_id
        $firstProjectList = $filterResponse->viewData('firstProjectList');
        foreach ($firstProjectList as $fp) {
            $this->assertNotNull($fp->department_nameTH);
            $this->assertNotEquals('ไม่ระบุหน่วยงาน', $fp->department_nameTH);
        }
    }
}
