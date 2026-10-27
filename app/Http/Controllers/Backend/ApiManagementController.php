<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class ApiManagementController extends Controller
{
    /**
     * Display the API and Bearer Token Management Dashboard.
     */
    public function index(Request $request): View
    {
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser && $currentUser->isSuperAdmin();
        $hasToken = $currentUser && $currentUser->tokens()->exists();

        // Only Super Admin or users/researchers who have an issued Bearer Token can access this portal
        if (!$isSuperAdmin && !$hasToken) {
            abort(403, 'ท่านยังไม่ได้รับสิทธิ์เข้าถึงบริการ API (ยังไม่มี Bearer Token ที่สร้างขึ้น)');
        }

        $query = PersonalAccessToken::query();

        // If not super admin, restricted to viewing own tokens only
        if (!$isSuperAdmin) {
            $query->where('tokenable_id', $currentUser->id)
                  ->where('tokenable_type', User::class);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHasMorph('tokenable', [User::class], function ($uq) use ($search) {
                      $uq->where('username', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhereHas('researcher', function ($rq) use ($search) {
                             $rq->where('tea_code', 'like', "%{$search}%")
                                ->orWhere('researcher_fname', 'like', "%{$search}%")
                                ->orWhere('researcher_lname', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Filter by user ID if requested (Super Admin only)
        if ($request->filled('user_id') && $isSuperAdmin) {
            $query->where('tokenable_id', $request->query('user_id'))
                  ->where('tokenable_type', User::class);
        }

        $tokens = $query->orderByDesc('id')->paginate(15)->withQueryString();

        // Summary metrics
        $metricsQuery = $isSuperAdmin ? PersonalAccessToken::query() : PersonalAccessToken::where('tokenable_id', $currentUser->id)->where('tokenable_type', User::class);
        $totalTokens = (clone $metricsQuery)->count();
        $activeTokens = (clone $metricsQuery)->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->count();
        $usersWithTokens = $isSuperAdmin 
            ? PersonalAccessToken::where('tokenable_type', User::class)->distinct('tokenable_id')->count('tokenable_id')
            : ($hasToken ? 1 : 0);

        // User list for token creation modal (Super Admin only)
        $usersList = [];
        if ($isSuperAdmin) {
            $usersList = User::with(['researcher.department', 'researcher.prefix'])
                ->where('status', '!=', 0)
                ->orderBy('username')
                ->get();
        }

        // User's own latest token (for auto-filling in live tester)
        $myToken = $currentUser ? $currentUser->tokens()->latest()->first() : null;

        // List of all documented API endpoints
        $endpoints = $this->getEndpointsCatalog();

        return view('backend.api_management.index', compact(
            'tokens',
            'totalTokens',
            'activeTokens',
            'usersWithTokens',
            'usersList',
            'endpoints',
            'isSuperAdmin',
            'myToken'
        ));
    }

    /**
     * Create / Issue a new Bearer Token for a specific user / researcher (Super Admin only).
     */
    public function createToken(Request $request): RedirectResponse
    {
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            abort(403, 'เฉพาะผู้ดูแลระบบระดับสูงสุด (The Creator) เท่านั้นที่มีสิทธิ์ออก Bearer Token');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'user_id' => ['required', 'exists:user,id'],
        ];

        $validated = $request->validate($rules);

        $targetUserId = (int) $validated['user_id'];
        $targetUser = User::findOrFail($targetUserId);

        // Determine abilities
        $abilities = $validated['abilities'] ?? [];
        if (empty($abilities)) {
            $abilities = ['project:read', 'publication:read'];
        } elseif (in_array('*', $abilities)) {
            $abilities = ['*'];
        }

        // Expiration
        $expiresAt = null;
        if (!empty($validated['expires_in_days'])) {
            $expiresAt = now()->addDays((int) $validated['expires_in_days']);
        }

        // Create Sanctum Token
        $token = $targetUser->createToken($validated['name'], $abilities, $expiresAt);

        // Flash plainTextToken so it can be viewed and copied once
        session()->flash('newly_generated_token', [
            'token' => $token->plainTextToken,
            'name' => $validated['name'],
            'user_name' => $targetUser->name,
            'username' => $targetUser->username,
            'expires_at' => $expiresAt ? $expiresAt->format('d/m/Y H:i') : 'ไม่มีวันหมดอายุ',
            'abilities' => $abilities,
        ]);

        return redirect()->route('backend.api_management.index')
            ->with('success', "ออก Bearer Token สำหรับ \"{$targetUser->name}\" เรียบร้อยแล้ว กรุณาคัดลอก Token ด้านบนเพื่อนำไปใช้งาน");
    }

    /**
     * Revoke a single personal access token (Super Admin only).
     */
    public function revokeToken(Request $request, int $id): RedirectResponse
    {
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            abort(403, 'เฉพาะผู้ดูแลระบบระดับสูงสุด (The Creator) เท่านั้นที่มีสิทธิ์เพิกถอน Bearer Token');
        }

        $token = PersonalAccessToken::findOrFail($id);
        $tokenName = $token->name;
        $token->delete();

        return redirect()->back()
            ->with('success', "เพิกถอน Token \"{$tokenName}\" (ID: {$id}) สำเร็จ");
    }

    /**
     * Revoke all personal access tokens for a specific user (Super Admin only).
     */
    public function revokeAllUserTokens(Request $request, int $userId): RedirectResponse
    {
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            abort(403, 'เฉพาะผู้ดูแลระบบระดับสูงสุด (The Creator) เท่านั้นที่มีสิทธิ์เพิกถอน Bearer Token ทั้งหมด');
        }

        $targetUser = User::findOrFail($userId);
        $count = PersonalAccessToken::where('tokenable_type', User::class)
            ->where('tokenable_id', $userId)
            ->delete();

        return redirect()->back()
            ->with('success', "เพิกถอน Bearer Token ทั้งหมด ({$count} รายการ) ของ \"{$targetUser->name}\" สำเร็จ");
    }

    /**
     * Live Endpoint Tester Proxy (AJAX).
     */
    public function testEndpoint(Request $request): JsonResponse
    {
        $request->validate([
            'path' => ['required', 'string'],
            'method' => ['required', 'in:GET,POST,PUT,DELETE'],
            'bearer_token' => ['nullable', 'string'],
        ]);

        $path = '/' . ltrim($request->input('path'), '/');
        $method = strtoupper($request->input('method'));
        $bearerToken = trim((string) $request->input('bearer_token'));

        $startTime = microtime(true);

        // Keep reference to the current main request and session to prevent session corruption
        $originalRequest = request();
        $originalSession = $request->hasSession() ? $request->session() : null;

        try {
            // Setup internal request environment
            $serverVars = [
                'HTTP_ACCEPT' => 'application/json',
            ];
            if (!empty($bearerToken)) {
                $serverVars['HTTP_AUTHORIZATION'] = "Bearer {$bearerToken}";
            }

            $subRequest = Request::create($path, $method, [], [], [], $serverVars);

            // Inherit session and cookies so StartSession middleware re-uses active session instead of destroying it
            if ($originalSession) {
                $subRequest->setLaravelSession($originalSession);
                $subRequest->cookies->add($request->cookies->all());
            }

            // Dispatch directly through the router
            app()->instance('request', $subRequest);
            $subResponse = app('router')->dispatch($subRequest);
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            $statusCode = $subResponse->getStatusCode();
            $content = $subResponse->getContent();
            $decodedJson = json_decode($content, true);

            return response()->json([
                'success' => $statusCode >= 200 && $statusCode < 400,
                'status_code' => $statusCode,
                'duration_ms' => $durationMs,
                'url' => url($path),
                'data' => $decodedJson ?? $content,
            ]);
        } catch (\Throwable $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            return response()->json([
                'success' => false,
                'status_code' => 500,
                'duration_ms' => $durationMs,
                'error' => $e->getMessage(),
                'url' => url($path),
            ], 500);
        } finally {
            // Guarantee original request and session are restored in the container
            app()->instance('request', $originalRequest);
            \Illuminate\Support\Facades\Facade::clearResolvedInstance('request');
            if ($originalSession) {
                $originalRequest->setLaravelSession($originalSession);
            }
        }
    }

    /**
     * Built-in Endpoints Catalog & Documentation data.
     */
    protected function getEndpointsCatalog(): array
    {
        return [
            'tier1' => [
                'name' => 'Tier 1: Legacy Compatibility & Integration Feeds (Document 2)',
                'badge' => 'Legacy Contract 100%',
                'color' => 'warning',
                'description' => 'Endpoint ที่เข้ากันได้กับระบบเดิมของสถาบันวิจัยฯ และระบบสารสนเทศคณะ เพื่อให้ระบบภายนอกหรือ Script เดิมใช้งานได้ต่อเนื่องโดยไม่ต้องแก้ไขโค้ด',
                'items' => [
                    [
                        'id' => 'legacy-proj-all',
                        'method' => 'GET',
                        'path' => '/rdbproject/jsonprojectall',
                        'alt_paths' => ['/rdb/project/jsonprojectall'],
                        'auth' => 'Public (Rate Limited 60/min)',
                        'auth_type' => 'public',
                        'title' => 'Project Master Data & Filter Feeds',
                        'desc' => 'ดึงรายการโครงการวิจัยทั้งหมด พร้อมข้อมูลทีมนักวิจัย (Research Team Array) และงบประมาณ',
                        'params' => [
                            ['name' => 'dep', 'type' => 'integer', 'desc' => 'รหัสคณะ/หน่วยงาน (1-13) เช่น dep=2'],
                            ['name' => 'year', 'type' => 'integer', 'desc' => 'ปีงบประมาณ พ.ศ. เช่น year=2567'],
                            ['name' => 'pmis', 'type' => 'string', 'desc' => 'รหัสอาจารย์ PMIS เช่น pmis=656219'],
                        ],
                        'sample_url' => '/rdbproject/jsonprojectall?dep=2&year=2567',
                    ],
                    [
                        'id' => 'legacy-pub-all',
                        'method' => 'GET',
                        'path' => '/rdbpublished/jsonpublisheddataall',
                        'alt_paths' => ['/rdb/published/jsonpublisheddataall'],
                        'auth' => 'Public (Rate Limited 60/min)',
                        'auth_type' => 'public',
                        'title' => 'Publication Master Data & Filter Feeds',
                        'desc' => 'ดึงรายการผลงานตีพิมพ์ทางวิชาการ พร้อมข้อมูลผู้ร่วมวิจัย (Authors) และโครงการที่เกี่ยวข้อง',
                        'params' => [
                            ['name' => 'dep', 'type' => 'integer', 'desc' => 'รหัสคณะ/หน่วยงาน (1-13) เช่น dep=6'],
                            ['name' => 'year', 'type' => 'integer', 'desc' => 'ปีที่ตีพิมพ์ พ.ศ. เช่น year=2568'],
                            ['name' => 'pmis', 'type' => 'string', 'desc' => 'รหัสอาจารย์ PMIS เช่น pmis=656219'],
                        ],
                        'sample_url' => '/rdbpublished/jsonpublisheddataall?dep=6&year=2568',
                    ],
                    [
                        'id' => 'legacy-pmis-research',
                        'method' => 'GET',
                        'path' => '/api/pmis/research',
                        'alt_paths' => ['/rdb/researcher/datagetresearchbypmis', '/rdbresearcher/datagetresearchbypmis'],
                        'auth' => 'Public (Rate Limited 60/min)',
                        'auth_type' => 'public',
                        'title' => 'PMIS Researcher Project Portfolio',
                        'desc' => 'ดึงประวัติและรายการโครงการวิจัยรายบุคคลตามรหัสอาจารย์ PMIS',
                        'params' => [
                            ['name' => 'pmis', 'type' => 'string', 'desc' => 'รหัสอาจารย์ เช่น pmis=655154'],
                            ['name' => 'year', 'type' => 'integer', 'desc' => 'ปีงบประมาณ เช่น year=2564'],
                        ],
                        'sample_url' => '/api/pmis/research?pmis=655154',
                    ],
                    [
                        'id' => 'legacy-pmis-pub',
                        'method' => 'GET',
                        'path' => '/api/pmis/publication',
                        'alt_paths' => ['/rdb/researcher/datagetpublicationbypmis', '/rdbresearcher/datagetpublicationbypmis'],
                        'auth' => 'Public (Rate Limited 60/min)',
                        'auth_type' => 'public',
                        'title' => 'PMIS Researcher Publication List',
                        'desc' => 'ดึงรายการผลงานตีพิมพ์ทางวิชาการ วารสาร สัดส่วนผู้แต่ง และคะแนนคุณภาพตามรหัส PMIS',
                        'params' => [
                            ['name' => 'pmis', 'type' => 'string', 'desc' => 'รหัสอาจารย์ เช่น pmis=656219'],
                            ['name' => 'year', 'type' => 'integer', 'desc' => 'ปีที่ตีพิมพ์ เช่น year=2568'],
                        ],
                        'sample_url' => '/api/pmis/publication?pmis=656219',
                    ],
                ]
            ],
            'tier2' => [
                'name' => 'Tier 2: Modern RESTful API v1 (Laravel Sanctum & OpenAPI Design)',
                'badge' => 'Standard RESTful v1',
                'color' => 'primary',
                'description' => 'บริการ API ยุคใหม่ ปลอดภัยด้วย Bearer Token (Sanctum), มีระบบ Resource Transformer, รองรับ Pagination, Search, Sorting และ Atomic Transaction',
                'items' => [
                    [
                        'id' => 'v1-auth-token',
                        'method' => 'POST',
                        'path' => '/api/v1/auth/token',
                        'alt_paths' => ['/api/v1/login'],
                        'auth' => 'Public (Credentials Required)',
                        'auth_type' => 'public',
                        'title' => 'Issue Personal Access Token',
                        'desc' => 'ขอรับ Bearer Token สำหรับเรียกใช้งาน API โดยใช้ username และ password ของระบบ',
                        'params' => [
                            ['name' => 'username', 'type' => 'string', 'desc' => 'ชื่อผู้ใช้งาน หรือ อีเมล'],
                            ['name' => 'password', 'type' => 'string', 'desc' => 'รหัสผ่าน'],
                            ['name' => 'device_name', 'type' => 'string', 'desc' => 'ชื่ออุปกรณ์/ระบบที่เชื่อมต่อ (Optional)'],
                        ],
                        'sample_url' => '/api/v1/auth/token',
                    ],
                    [
                        'id' => 'v1-auth-me',
                        'method' => 'GET',
                        'path' => '/api/v1/auth/me',
                        'alt_paths' => [],
                        'auth' => 'Bearer Token Required',
                        'auth_type' => 'bearer',
                        'title' => 'Current Token & User Profile',
                        'desc' => 'ตรวจสอบโปรไฟล์ผู้ใช้งาน และสิทธิ์ Abilities ของ Bearer Token ปัจจุบัน',
                        'params' => [],
                        'sample_url' => '/api/v1/auth/me',
                    ],
                    [
                        'id' => 'v1-master-deps',
                        'method' => 'GET',
                        'path' => '/api/v1/master/departments',
                        'alt_paths' => [],
                        'auth' => 'Bearer Token Required',
                        'auth_type' => 'bearer',
                        'title' => 'Faculty & Department Master Data',
                        'desc' => 'ดึงรายชื่อคณะ/หน่วยงานทั้งหมด (1-13) พร้อมรหัสอ้างอิงและชื่อภาษาอังกฤษ',
                        'params' => [],
                        'sample_url' => '/api/v1/master/departments',
                    ],
                    [
                        'id' => 'v1-master-years',
                        'method' => 'GET',
                        'path' => '/api/v1/master/years',
                        'alt_paths' => [],
                        'auth' => 'Bearer Token Required',
                        'auth_type' => 'bearer',
                        'title' => 'Fiscal Years Master Data',
                        'desc' => 'ดึงรายการปีงบประมาณ พ.ศ. ทั้งหมดในระบบฐานข้อมูล',
                        'params' => [],
                        'sample_url' => '/api/v1/master/years',
                    ],
                    [
                        'id' => 'v1-projects-index',
                        'method' => 'GET',
                        'path' => '/api/v1/projects',
                        'alt_paths' => [],
                        'auth' => 'Bearer Token Required',
                        'auth_type' => 'bearer',
                        'title' => 'List & Filter Projects (Pagination)',
                        'desc' => 'ค้นหาและกรองโครงการวิจัย รองรับ Pagination, Sorting, กรองคณะ ปีงบประมาณ และค้นหาคำสำคัญ',
                        'params' => [
                            ['name' => 'dep', 'type' => 'integer', 'desc' => 'รหัสคณะ เช่น dep=2'],
                            ['name' => 'year', 'type' => 'integer', 'desc' => 'ปีงบประมาณ เช่น year=2567'],
                            ['name' => 'pmis', 'type' => 'string', 'desc' => 'รหัสอาจารย์ เช่น pmis=656219'],
                            ['name' => 'search', 'type' => 'string', 'desc' => 'คำค้นชื่อโครงการวิจัย รหัส หรือคำสำคัญ'],
                            ['name' => 'per_page', 'type' => 'integer', 'desc' => 'จำนวนต่อหน้า (Default: 20, Max: 100)'],
                            ['name' => 'sort_by', 'type' => 'string', 'desc' => 'pro_id, pro_budget, pro_date_start'],
                        ],
                        'sample_url' => '/api/v1/projects?dep=2&year=2567',
                    ],
                    [
                        'id' => 'v1-projects-show',
                        'method' => 'GET',
                        'path' => '/api/v1/projects/{id}',
                        'alt_paths' => [],
                        'auth' => 'Bearer Token Required',
                        'auth_type' => 'bearer',
                        'title' => 'Get Single Project Details',
                        'desc' => 'ดูรายละเอียดโครงการวิจัยรายโครงการ พร้อมข้อมูลทีมนักวิจัยและสัดส่วนการมีส่วนร่วม',
                        'params' => [
                            ['name' => 'id', 'type' => 'integer', 'desc' => 'รหัสโครงการวิจัย เช่น 2597'],
                        ],
                        'sample_url' => '/api/v1/projects/2597',
                    ],
                    [
                        'id' => 'v1-projects-store',
                        'method' => 'POST',
                        'path' => '/api/v1/projects',
                        'alt_paths' => [],
                        'auth' => 'Bearer Token Required (Ability: project:write)',
                        'auth_type' => 'bearer',
                        'title' => 'Create Project with Research Team',
                        'desc' => 'สร้างโครงการวิจัยใหม่ บันทึกทีมนักวิจัยใน transaction เดียว พร้อมตรวจสอบผลรวมสัดส่วนต้องไม่เกิน 100%',
                        'params' => [
                            ['name' => 'title_th', 'type' => 'string', 'desc' => 'ชื่อโครงการ (ไทย) [จำเป็น]'],
                            ['name' => 'fiscal_year', 'type' => 'integer', 'desc' => 'ปีงบประมาณ พ.ศ. [จำเป็น]'],
                            ['name' => 'budget_amount', 'type' => 'numeric', 'desc' => 'งบประมาณ [จำเป็น]'],
                            ['name' => 'department_id', 'type' => 'integer', 'desc' => 'รหัสคณะ [จำเป็น]'],
                            ['name' => 'research_team', 'type' => 'array', 'desc' => 'อาร์เรย์ทีมนักวิจัย [{researcher_id, contribution_percentage, position_id}]'],
                        ],
                        'sample_url' => '/api/v1/projects',
                    ],
                    [
                        'id' => 'v1-publications-index',
                        'method' => 'GET',
                        'path' => '/api/v1/publications',
                        'alt_paths' => [],
                        'auth' => 'Bearer Token Required',
                        'auth_type' => 'bearer',
                        'title' => 'List & Filter Publications (Pagination)',
                        'desc' => 'ค้นหาและกรองผลงานตีพิมพ์ทางวิชาการ รองรับ Pagination และการกรองตามคณะ/ปี/ผู้แต่ง',
                        'params' => [
                            ['name' => 'dep', 'type' => 'integer', 'desc' => 'รหัสคณะ เช่น dep=6'],
                            ['name' => 'year', 'type' => 'integer', 'desc' => 'ปีที่ตีพิมพ์ เช่น year=2568'],
                            ['name' => 'pmis', 'type' => 'string', 'desc' => 'รหัสอาจารย์ เช่น pmis=656219'],
                            ['name' => 'search', 'type' => 'string', 'desc' => 'คำค้นชื่อผลงาน ชื่อวารสาร หรือคำสำคัญ'],
                        ],
                        'sample_url' => '/api/v1/publications?dep=6&year=2568',
                    ],
                    [
                        'id' => 'v1-researchers-portfolio',
                        'method' => 'GET',
                        'path' => '/api/v1/researchers/{pmis}/portfolio',
                        'alt_paths' => [],
                        'auth' => 'Bearer Token Required',
                        'auth_type' => 'bearer',
                        'title' => 'Complete Researcher Portfolio',
                        'desc' => 'ดึงพอร์ตผลงานวิชาการครบวงจรของนักวิจัย ทั้งข้อมูลส่วนตัว, ดัชนี H-index, Scopus, โครงการวิจัย และผลงานตีพิมพ์',
                        'params' => [
                            ['name' => 'pmis', 'type' => 'string', 'desc' => 'รหัสอาจารย์ PMIS เช่น 656219'],
                            ['name' => 'year', 'type' => 'integer', 'desc' => 'กรองเฉพาะปีที่ระบุ (Optional)'],
                        ],
                        'sample_url' => '/api/v1/researchers/656219/portfolio',
                    ],
                ]
            ]
        ];
    }
}
