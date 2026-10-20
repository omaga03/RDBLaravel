<?php

namespace Tests\Feature;

use Tests\TestCase;

class SearchAutocompleteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'rdbv2',
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');
    }

    public function test_project_search_autocomplete_and_title_keywords()
    {
        // 1. Search by project title
        $response = $this->getJson(route('frontend.rdbproject.search_projects', ['q' => 'วิจัย']));
        $response->assertStatus(200);

        // 2. Search by researcher name
        $responseRes = $this->getJson(route('frontend.rdbproject.search_projects', ['q' => 'การันต์']));
        $responseRes->assertStatus(200);
        $this->assertNotEmpty($responseRes->json());
        $firstItemText = $responseRes->json()[0]['text'];
        $this->assertStringContainsString('การันต์', $firstItemText);
        $this->assertMatchesRegularExpression('/^\[\d{4}\]/', $firstItemText);

        // 3. Search by grant/type name
        $responseType = $this->getJson(route('frontend.rdbproject.search_projects', ['q' => 'งบประมาณ']));
        $responseType->assertStatus(200);
        $this->assertNotEmpty($responseType->json());
        $this->assertStringContainsString('ทุน:', $responseType->json()[0]['text']);
        $this->assertMatchesRegularExpression('/^\[\d{4}\]/', $responseType->json()[0]['text']);

        // Test title keywords with AND mode
        $indexResponse = $this->get(route('frontend.rdbproject.index', [
            'title_keywords' => 'วิจัย การพัฒนา',
            'title_keywords_mode' => 'all'
        ]));
        $indexResponse->assertStatus(200);

        // Test title keywords with OR mode
        $indexResponseOr = $this->get(route('frontend.rdbproject.index', [
            'title_keywords' => 'วิจัย การพัฒนา',
            'title_keywords_mode' => 'any'
        ]));
        $indexResponseOr->assertStatus(200);
    }

    public function test_published_search_autocomplete_and_title_keywords()
    {
        $response = $this->getJson(route('frontend.rdbpublished.search_publications', ['q' => 'การ']));
        $response->assertStatus(200);

        $indexResponse = $this->get(route('frontend.rdbpublished.index', [
            'title_keywords' => 'ศึกษา ผลกระทบ',
            'title_keywords_mode' => 'all'
        ]));
        $indexResponse->assertStatus(200);
    }

    public function test_dip_search_autocomplete_and_title_keywords()
    {
        $response = $this->getJson(route('frontend.rdbdip.search_dips', ['q' => 'การ']));
        $response->assertStatus(200);

        $indexResponse = $this->get(route('frontend.rdbdip.index', [
            'title_keywords' => 'เครื่อง ระบบ',
            'title_keywords_mode' => 'any'
        ]));
        $indexResponse->assertStatus(200);
    }

    public function test_utilize_search_autocomplete_and_title_keywords()
    {
        $response = $this->getJson(route('frontend.rdbprojectutilize.search_utilizes', ['q' => 'การ']));
        $response->assertStatus(200);

        $indexResponse = $this->get(route('frontend.rdbprojectutilize.index', [
            'title_keywords' => 'ชุมชน พัฒนา',
            'title_keywords_mode' => 'all'
        ]));
        $indexResponse->assertStatus(200);
    }

    public function test_researcher_search_endpoints()
    {
        $routes = [
            'frontend.rdbproject.search_researchers',
            'frontend.rdbpublished.search_researchers',
            'frontend.rdbdip.search_researchers',
            'frontend.rdbprojectutilize.search_researchers',
        ];

        foreach ($routes as $r) {
            $response = $this->getJson(route($r, ['q' => 'สม']));
            $response->assertStatus(200);
        }
    }
}
