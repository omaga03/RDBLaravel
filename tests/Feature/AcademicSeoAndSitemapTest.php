<?php

namespace Tests\Feature;

use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\RdbResearcher;
use Tests\TestCase;

class AcademicSeoAndSitemapTest extends TestCase
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

    public function test_sitemap_index_returns_xml_with_sub_sitemaps()
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<sitemapindex', false);
        $response->assertSee('sitemap-projects.xml', false);
        $response->assertSee('sitemap-publications.xml', false);
        $response->assertSee('sitemap-researchers.xml', false);
        $response->assertSee('sitemap-utilizations.xml', false);
        $response->assertSee('sitemap-ip.xml', false);
    }

    public function test_sub_sitemaps_return_valid_urlsets()
    {
        $submaps = [
            '/sitemap-projects.xml',
            '/sitemap-publications.xml',
            '/sitemap-researchers.xml',
            '/sitemap-utilizations.xml',
            '/sitemap-ip.xml',
        ];

        foreach ($submaps as $uri) {
            $response = $this->get($uri);
            $response->assertStatus(200);
            $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
            $response->assertSee('<urlset', false);
            $response->assertSee('<loc>', false);
        }
    }

    public function test_project_show_page_contains_academic_meta_tags()
    {
        $project = RdbProject::where('data_show', 1)->first();
        if (!$project) {
            $this->markTestSkipped('No public project found.');
        }

        $response = $this->get(route('frontend.rdbproject.show', $project->pro_id));
        $response->assertStatus(200);
        $response->assertSee('name="citation_title"', false);
        $response->assertSee('name="DC.title"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type": "ResearchProject"', false);
    }

    public function test_publication_show_page_contains_google_scholar_tags()
    {
        $pub = RdbPublished::first();
        if (!$pub) {
            $this->markTestSkipped('No publication found.');
        }

        $response = $this->get(route('frontend.rdbpublished.show', $pub->id));
        $response->assertStatus(200);
        $response->assertSee('name="citation_title"', false);
        $response->assertSee('name="citation_publisher"', false);
        $response->assertSee('name="DC.publisher"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type": "ScholarlyArticle"', false);
    }

    public function test_researcher_show_page_contains_person_schema()
    {
        $researcher = RdbResearcher::where('data_show', 1)->first();
        if (!$researcher) {
            $this->markTestSkipped('No researcher found.');
        }

        $response = $this->get(route('frontend.rdbresearcher.show', $researcher->researcher_id));
        $response->assertStatus(200);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type": "Person"', false);
    }

    public function test_sitemaps_strictly_filter_by_data_show_one()
    {
        // 1. Projects with data_show = 0 are excluded
        $hiddenProject = RdbProject::withoutGlobalScope('active')->where('data_show', 0)->first();
        if ($hiddenProject) {
            $res = $this->get('/sitemap-projects.xml');
            $res->assertDontSee(route('frontend.rdbproject.show', $hiddenProject->pro_id), false);
        }

        // 2. Publications with data_show = 0 are excluded
        $hiddenPub = RdbPublished::withoutGlobalScope('active')->where('data_show', 0)->first();
        if ($hiddenPub) {
            $res = $this->get('/sitemap-publications.xml');
            $res->assertDontSee(route('frontend.rdbpublished.show', $hiddenPub->id), false);
        }

        // 3. Researchers with data_show = 0 are excluded
        $hiddenResearcher = RdbResearcher::withoutGlobalScope('active')->where('data_show', 0)->first();
        if ($hiddenResearcher) {
            $res = $this->get('/sitemap-researchers.xml');
            $res->assertDontSee(route('frontend.rdbresearcher.show', $hiddenResearcher->researcher_id), false);
        }
    }
}
