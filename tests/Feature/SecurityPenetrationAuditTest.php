<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RdbProject;
use App\Models\RdbPublished;

class SecurityPenetrationAuditTest extends TestCase
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
     * 1. SQL Injection Fuzzing on Frontend & Backend Search Queries
     */
    public function test_sql_injection_payloads_are_safely_parameterized()
    {
        $payloads = [
            "' OR '1'='1",
            "'; DROP TABLE dummy_test; --",
            "1' UNION SELECT 1,2,3,4,5,6,7,8,9,10-- -",
            "admin'--",
            "\" OR \"\"=\"",
            "1; WAITFOR DELAY '0:0:5'",
            "1 AND (SELECT 1 FROM (SELECT COUNT(*), CONCAT((SELECT DATABASE()), FLOOR(RAND(0)*2)) x FROM INFORMATION_SCHEMA.TABLES GROUP BY x) a)",
        ];

        foreach ($payloads as $payload) {
            // Test Frontend Project Search
            $response = $this->get(route('frontend.rdbproject.index', [
                'search' => $payload,
                'pro_nameTH' => $payload,
                'year_id' => $payload,
                'department_id' => $payload,
            ]));
            $this->assertNotEquals(500, $response->getStatusCode(), "Frontend project search crashed on payload: {$payload}");
            $response->assertDontSee('SQLSTATE');
            $response->assertDontSee('Syntax error');

            // Test Frontend Published Search
            $pubResponse = $this->get(route('frontend.rdbpublished.index', [
                'search' => $payload,
                'pub_name' => $payload,
                'year_id' => $payload,
            ]));
            $this->assertNotEquals(500, $pubResponse->getStatusCode(), "Frontend published search crashed on payload: {$payload}");
            $pubResponse->assertDontSee('SQLSTATE');
            $pubResponse->assertDontSee('Syntax error');
        }
    }

    /**
     * 2. Cross-Site Scripting (XSS) Prevention & Neutralization
     */
    public function test_xss_injection_payloads_are_escaped_on_render()
    {
        $xssPayloads = [
            "<script>alert('XSS_ATTACK')</script>",
            "<img src=x onerror=alert('XSS_IMG')>",
            "\" onmouseover=\"alert('XSS_EVENT')",
            "<svg/onload=alert('XSS_SVG')>",
            "javascript:alert('XSS_JS')",
        ];

        foreach ($xssPayloads as $xss) {
            $response = $this->get(route('frontend.rdbproject.index', ['search' => $xss]));
            $this->assertNotEquals(500, $response->getStatusCode());
            // Must not contain raw unescaped script tag
            $response->assertDontSee("<script>alert('XSS_ATTACK')</script>", false);
            $response->assertDontSee("<img src=x onerror=alert('XSS_IMG')>", false);
            $response->assertDontSee("<svg/onload=alert('XSS_SVG')>", false);
        }
    }

    /**
     * 3. CSRF Protection on State-Changing Endpoints
     */
    public function test_csrf_token_is_enforced_on_state_changing_post_routes()
    {
        // Verify CSRF token mismatch throws TokenMismatchException (HTTP 419)
        $request = \Illuminate\Http\Request::create('/login', 'POST', [
            'username' => 'admin',
            'password' => 'password',
            '_token' => 'malicious-forged-token',
        ]);
        
        $session = app('session')->driver();
        $session->setId('security-test-session');
        $session->start();
        $session->put('_token', 'legitimate-server-token');
        $request->setLaravelSession($session);

        // Subclass to enforce runningUnitTests = false so CSRF validation actively runs
        $csrfMiddleware = new class(app(), app('encrypter')) extends \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };

        $this->expectException(\Illuminate\Session\TokenMismatchException::class);
        $csrfMiddleware->handle($request, function () {
            return response('OK');
        });
    }

    /**
     * 4. API Rate Limiting Verification
     */
    public function test_api_rate_limiting_protects_against_brute_force()
    {
        // Make repeated calls to throttled API
        $hitLimit = false;
        for ($i = 0; $i < 70; $i++) {
            $res = $this->getJson('/api/v1/news');
            if ($res->getStatusCode() === 429) {
                $hitLimit = true;
                break;
            }
        }

        $this->assertTrue($hitLimit, 'Rate limiting should trigger HTTP 429 Too Many Requests after 60 hits/min');
    }

    /**
     * 5. Insecure Direct Object References (IDOR) & Scoped Access Prevention
     */
    public function test_researcher_cannot_edit_unauthorized_records()
    {
        $researcher = User::whereHas('roles', fn($q) => $q->where('item_name', 'Researcher'))->first();
        if (!$researcher) {
            $this->markTestSkipped('No researcher found');
        }

        // Find a project owned by someone else
        $otherProject = RdbProject::where('user_created', '!=', $researcher->id)->first();
        if (!$otherProject) {
            $this->markTestSkipped('No other project found');
        }

        // Attempt to access edit page as researcher
        $response = $this->actingAs($researcher)->get(route('backend.rdb_project.edit', $otherProject->pro_id));
        $this->assertTrue(in_array($response->getStatusCode(), [403, 302]), 'Unauthorized researcher must be blocked with 403 or redirect');
    }

    /**
     * 6. Executive Report Multi-Dimension Parameter Binding Security
     */
    public function test_executive_report_query_parameter_binding()
    {
        $admin = User::whereHas('roles', fn($q) => $q->whereIn('item_name', ['The Creator', 'Admin']))->first();
        if (!$admin) {
            $this->markTestSkipped('No admin found');
        }

        $maliciousYear = "1' OR '1'='1";
        $response = $this->actingAs($admin)->get(route('backend.reports.executive_summary', ['year_id' => $maliciousYear]));
        $this->assertNotEquals(500, $response->getStatusCode());
        $response->assertDontSee('SQLSTATE');
    }
}
