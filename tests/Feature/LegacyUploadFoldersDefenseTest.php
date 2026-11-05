<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\RdbProject;
use App\Models\RdbProjectFiles;
use App\Models\RdbDip;
use App\Models\RdbPublished;
use App\Models\RdbProjectUtilize;
use App\Models\RdbResearcher;
use App\Models\RdbProjectTypeSub;
use App\Models\ResearchNews;
use App\Models\ResearchConference;
use App\Models\JournalStatus;
use App\Models\RdbNacc;
use Illuminate\Support\Facades\Storage;

class LegacyUploadFoldersDefenseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mysql']);
    }

    public function test_all_nine_legacy_folders_exist_in_storage()
    {
        $folders = [
            'datacoferenceinthai',
            'datadip',
            'datafiles',
            'datajournalstatus',
            'datanewsimg',
            'datapts',
            'datapublished',
            'dataresearcher',
            'datautilize',
        ];

        foreach ($folders as $folder) {
            $this->assertTrue(
                Storage::disk('public')->exists('uploads/' . $folder) || is_dir(storage_path('app/public/uploads/' . $folder)),
                "Folder 'uploads/{$folder}' must exist in storage/app/public/uploads"
            );
        }
    }

    public function test_dual_path_trait_normalizes_legacy_prefixes()
    {
        $dummy = new class {
            use \App\Models\Traits\HasDualFilePath;
        };

        $this->assertEquals('uploads/datafiles/1/test.pdf', $dummy->normalizeLegacyUploadPath('uploads/project_files/1/test.pdf'));
        $this->assertEquals('uploads/datadip/2/test.pdf', $dummy->normalizeLegacyUploadPath('uploads/dips/2/test.pdf'));
        $this->assertEquals('uploads/datacoferenceinthai/img.jpg', $dummy->normalizeLegacyUploadPath('uploads/conference/img.jpg'));
        $this->assertEquals('uploads/datanewsimg/img.jpg', $dummy->normalizeLegacyUploadPath('uploads/news/img.jpg'));
    }

    public function test_project_and_files_resolve_under_uploads_datafiles()
    {
        // 1. RdbProject report and abstract
        $project = new RdbProject();
        $project->pro_id = 999999;
        $project->pro_file = 'test-report.pdf';
        $project->pro_abstract_file = 'test-abs.pdf';

        // Fake disk file
        Storage::disk('public')->put('uploads/datafiles/999999/test-report.pdf', 'dummy');
        Storage::disk('public')->put('uploads/datafiles/999999/test-abs.pdf', 'dummy');

        $this->assertEquals('uploads/datafiles/999999/test-report.pdf', $project->getReportFilePath());
        $this->assertEquals('uploads/datafiles/999999/test-abs.pdf', $project->getAbstractFilePath());

        // 2. RdbProjectFiles
        $prf = new RdbProjectFiles();
        $prf->pro_id = 999999;
        $prf->rf_files = 'test-attachment.pdf';
        Storage::disk('public')->put('uploads/datafiles/999999/test-attachment.pdf', 'dummy');
        $this->assertEquals('uploads/datafiles/999999/test-attachment.pdf', $prf->getFilePath());

        // 3. RdbNacc
        $nacc = new RdbNacc();
        $nacc->pro_id = 999999;
        $nacc->nacc_files = 'test-nacc.pdf';
        Storage::disk('public')->put('uploads/datafiles/999999/test-nacc.pdf', 'dummy');
        $this->assertEquals('uploads/datafiles/999999/test-nacc.pdf', $nacc->getNaccFilePath());

        // Cleanup
        Storage::disk('public')->deleteDirectory('uploads/datafiles/999999');
    }

    public function test_dip_resolves_under_uploads_datadip()
    {
        $dip = new RdbDip();
        $dip->dip_id = 999999;
        $dip->dip_files = 'test-dip.pdf';
        $dip->dip_data1_files = 'test-dip-sub.pdf';

        Storage::disk('public')->put('uploads/datadip/999999/test-dip.pdf', 'dummy');
        Storage::disk('public')->put('uploads/datadip/999999/test-dip-sub.pdf', 'dummy');

        $this->assertEquals('uploads/datadip/999999/test-dip.pdf', $dip->getDipFilePath());
        $this->assertEquals('uploads/datadip/999999/test-dip-sub.pdf', $dip->getDipFilePath('dip_data1_files'));

        Storage::disk('public')->deleteDirectory('uploads/datadip/999999');
    }

    public function test_published_resolves_under_uploads_datapublished()
    {
        $pub = new RdbPublished();
        $pub->id = 999999;
        $pub->pub_file = 'test-pub.pdf';

        Storage::disk('public')->put('uploads/datapublished/999999/test-pub.pdf', 'dummy');
        $this->assertEquals('uploads/datapublished/999999/test-pub.pdf', $pub->getPublishedFilePath());

        Storage::disk('public')->deleteDirectory('uploads/datapublished/999999');
    }

    public function test_utilize_resolves_under_uploads_datautilize()
    {
        $utz = new RdbProjectUtilize();
        $utz->utz_id = 999999;
        $utz->utz_files = 'test-utz.pdf';

        Storage::disk('public')->put('uploads/datautilize/999999/test-utz.pdf', 'dummy');
        $this->assertEquals('uploads/datautilize/999999/test-utz.pdf', $utz->getUtilizeFilePath());

        Storage::disk('public')->deleteDirectory('uploads/datautilize/999999');
    }

    public function test_researcher_resolves_under_uploads_dataresearcher()
    {
        $res = new RdbResearcher();
        $res->researcher_id = 999999;
        $res->researcher_picture = 'test-profile.jpg';

        Storage::disk('public')->put('uploads/dataresearcher/test-profile.jpg', 'dummy');
        $this->assertEquals('uploads/dataresearcher/test-profile.jpg', $res->getProfileFilePath());

        Storage::disk('public')->delete('uploads/dataresearcher/test-profile.jpg');
    }

    public function test_news_and_conference_resolve_under_uploads()
    {
        // News
        $news = new ResearchNews();
        $news->news_img = 'test-news.jpg';
        Storage::disk('public')->put('uploads/datanewsimg/test-news.jpg', 'dummy');
        $this->assertEquals('uploads/datanewsimg/test-news.jpg', $news->getNewsFilePath());
        $this->assertStringContainsString('storage/uploads/datanewsimg/test-news.jpg', $news->image_url);
        Storage::disk('public')->delete('uploads/datanewsimg/test-news.jpg');

        // Conference
        $conf = new ResearchConference();
        $conf->con_img = 'test-conf.jpg';
        Storage::disk('public')->put('uploads/datacoferenceinthai/test-conf.jpg', 'dummy');
        $this->assertEquals('uploads/datacoferenceinthai/test-conf.jpg', $conf->getConferenceFilePath());
        $this->assertStringContainsString('storage/uploads/datacoferenceinthai/test-conf.jpg', $conf->image_url);
        Storage::disk('public')->delete('uploads/datacoferenceinthai/test-conf.jpg');
    }

    public function test_journal_status_and_project_types_sub_resolve_under_uploads()
    {
        // JournalStatus
        $js = new JournalStatus();
        $js->id = 999999;
        $js->jou_files = 'test-journal.pdf';
        Storage::disk('public')->put('uploads/datajournalstatus/999999/test-journal.pdf', 'dummy');
        $this->assertEquals('uploads/datajournalstatus/999999/test-journal.pdf', $js->getJournalStatusFilePath());
        Storage::disk('public')->deleteDirectory('uploads/datajournalstatus/999999');

        // RdbProjectTypeSub
        $pts = new RdbProjectTypeSub();
        $pts->pts_file = 'test-pts.pdf';
        Storage::disk('public')->put('uploads/datappts/test-pts.pdf', 'dummy');
        Storage::disk('public')->put('uploads/datapts/test-pts.pdf', 'dummy');
        $this->assertEquals('uploads/datapts/test-pts.pdf', $pts->getTypeSubFilePath());
        Storage::disk('public')->delete('uploads/datapts/test-pts.pdf');
    }
}
