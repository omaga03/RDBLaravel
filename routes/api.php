<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DipController;
use App\Http\Controllers\Api\V1\MasterDataController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectUtilizeController;
use App\Http\Controllers\Api\V1\PublicationController;
use App\Http\Controllers\Api\V1\ResearcherController;
use App\Http\Controllers\Api\V1\ResearchNewsApiController;
use App\Http\Controllers\Api\V1\ResearchConferenceApiController;
use App\Http\Controllers\Frontend\RdbresearcherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Dual-Tier API:
| 1. PMIS Legacy Aliases:
|    - /api/pmis/research
|    - /api/pmis/publication
|
| 2. Open Public APIs (GET only):
|    - /api/v1/news, /api/v1/news/{id}
|    - /api/v1/conferences, /api/v1/conferences/{id}
|    - /api/news, /api/conferences
|
| 3. Modern RESTful API (v1 with Sanctum for core research databases):
|    - /api/v1/auth/token (POST)
|    - /api/v1/master/...
|    - /api/v1/projects (GET)
|    - /api/v1/publications (GET)
*/

// Open Public News & Conference Endpoints under /api
Route::middleware('throttle:60,1')->group(function () {
    Route::get('news', [ResearchNewsApiController::class, 'index'])->name('api.news.index');
    Route::get('news/{id}', [ResearchNewsApiController::class, 'show'])->name('api.news.show');
    Route::get('conferences', [ResearchConferenceApiController::class, 'index'])->name('api.conferences.index');
    Route::get('conferences/{id}', [ResearchConferenceApiController::class, 'show'])->name('api.conferences.show');

    // Aliases
    Route::get('research-news', [ResearchNewsApiController::class, 'index'])->name('api.research_news.index');
    Route::get('research-news/{id}', [ResearchNewsApiController::class, 'show'])->name('api.research_news.show');
    Route::get('research-conferences', [ResearchConferenceApiController::class, 'index'])->name('api.research_conferences.index');
    Route::get('research-conferences/{id}', [ResearchConferenceApiController::class, 'show'])->name('api.research_conferences.show');
});

// Legacy PMIS aliases under /api
Route::middleware('throttle:60,1')->group(function () {
    Route::get('pmis/research', [RdbresearcherController::class, 'datagetresearchbypmis'])->name('api.pmis.research');
    Route::get('pmis/publication', [RdbresearcherController::class, 'datagetpublicationbypmis'])->name('api.pmis.publication');
});

// Modern RESTful API v1
Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    // Open / Public Endpoints (GET only) - ข่าวทุนวิจัย & งานประชุมวิชาการ
    Route::get('news', [ResearchNewsApiController::class, 'index'])->name('api.v1.news.index');
    Route::get('news/{id}', [ResearchNewsApiController::class, 'show'])->name('api.v1.news.show');
    Route::get('conferences', [ResearchConferenceApiController::class, 'index'])->name('api.v1.conferences.index');
    Route::get('conferences/{id}', [ResearchConferenceApiController::class, 'show'])->name('api.v1.conferences.show');

    // Authentication: Token issuance via API is disabled (restricted to Super Admin via Backend only)
    Route::post('auth/token', [AuthController::class, 'token'])->name('api.v1.auth.token');
    Route::post('login', [AuthController::class, 'token'])->name('api.v1.login');

    // Tier 2: All Modern RESTful Endpoints require Bearer Token (Sanctum)
    // Non-SuperAdmin users are restricted to safe GET requests only
    Route::middleware([
        'auth:sanctum',
        \App\Http\Middleware\EnsureSuperAdminForNonSafeApiMethods::class,
    ])->group(function () {
        // Master Data
        Route::get('master/departments', [MasterDataController::class, 'departments'])->name('api.v1.master.departments');
        Route::get('master/years', [MasterDataController::class, 'years'])->name('api.v1.master.years');
        Route::get('master/project-types', [MasterDataController::class, 'projectTypes'])->name('api.v1.master.projectTypes');
        Route::get('master/utilize-types', [MasterDataController::class, 'utilizeTypes'])->name('api.v1.master.utilizeTypes');
        Route::get('master/dip-types', [MasterDataController::class, 'dipTypes'])->name('api.v1.master.dipTypes');

        // Resources (Read)
        Route::get('projects', [ProjectController::class, 'index'])->name('api.v1.projects.index');
        Route::get('projects/{id}', [ProjectController::class, 'show'])->name('api.v1.projects.show');

        Route::get('publications', [PublicationController::class, 'index'])->name('api.v1.publications.index');
        Route::get('publications/{id}', [PublicationController::class, 'show'])->name('api.v1.publications.show');

        // Research Utilization (งานนำไปใช้ประโยชน์)
        Route::get('utilizations', [ProjectUtilizeController::class, 'index'])->name('api.v1.utilizations.index');
        Route::get('utilizations/{id}', [ProjectUtilizeController::class, 'show'])->name('api.v1.utilizations.show');
        Route::get('project-utilizations', [ProjectUtilizeController::class, 'index'])->name('api.v1.project_utilizations.index');
        Route::get('project-utilizations/{id}', [ProjectUtilizeController::class, 'show'])->name('api.v1.project_utilizations.show');

        // Intellectual Property (งานทรัพย์สินทางปัญญา - DIP)
        Route::get('intellectual-properties', [DipController::class, 'index'])->name('api.v1.intellectual_properties.index');
        Route::get('intellectual-properties/{id}', [DipController::class, 'show'])->name('api.v1.intellectual_properties.show');
        Route::get('dips', [DipController::class, 'index'])->name('api.v1.dips.index');
        Route::get('dips/{id}', [DipController::class, 'show'])->name('api.v1.dips.show');

        Route::get('researchers/{id}', [ResearcherController::class, 'show'])->name('api.v1.researchers.show');
        Route::get('researchers/{pmis}/portfolio', [ResearcherController::class, 'portfolio'])->name('api.v1.researchers.portfolio');

        // Authenticated Actions & User Profile
        Route::get('auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
    });
});
