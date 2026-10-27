<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MasterDataController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\PublicationController;
use App\Http\Controllers\Api\V1\ResearcherController;
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
| 2. Modern RESTful API (v1):
|    - /api/v1/auth/token (POST)
|    - /api/v1/auth/me (GET, auth:sanctum)
|    - /api/v1/auth/logout (POST, auth:sanctum)
|    - /api/v1/master/departments (GET)
|    - /api/v1/master/years (GET)
|    - /api/v1/master/project-types (GET)
|    - /api/v1/projects (GET, POST auth:sanctum)
|    - /api/v1/projects/{id} (GET, PUT auth:sanctum, DELETE auth:sanctum)
|    - /api/v1/publications (GET)
|    - /api/v1/publications/{id} (GET)
|    - /api/v1/researchers/{id} (GET)
|    - /api/v1/researchers/{pmis}/portfolio (GET)
*/

// Legacy PMIS aliases under /api
Route::middleware('throttle:60,1')->group(function () {
    Route::get('pmis/research', [RdbresearcherController::class, 'datagetresearchbypmis'])->name('api.pmis.research');
    Route::get('pmis/publication', [RdbresearcherController::class, 'datagetpublicationbypmis'])->name('api.pmis.publication');
});

// Modern RESTful API v1
Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    // Authentication & Token Issuance
    Route::post('auth/token', [AuthController::class, 'token'])->name('api.v1.auth.token');
    Route::post('login', [AuthController::class, 'token'])->name('api.v1.login');

    // Master Data
    Route::get('master/departments', [MasterDataController::class, 'departments'])->name('api.v1.master.departments');
    Route::get('master/years', [MasterDataController::class, 'years'])->name('api.v1.master.years');
    Route::get('master/project-types', [MasterDataController::class, 'projectTypes'])->name('api.v1.master.projectTypes');

    // Public Read Resources
    Route::get('projects', [ProjectController::class, 'index'])->name('api.v1.projects.index');
    Route::get('projects/{id}', [ProjectController::class, 'show'])->name('api.v1.projects.show');

    Route::get('publications', [PublicationController::class, 'index'])->name('api.v1.publications.index');
    Route::get('publications/{id}', [PublicationController::class, 'show'])->name('api.v1.publications.show');

    Route::get('researchers/{id}', [ResearcherController::class, 'show'])->name('api.v1.researchers.show');
    Route::get('researchers/{pmis}/portfolio', [ResearcherController::class, 'portfolio'])->name('api.v1.researchers.portfolio');

    // Authenticated Actions (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');

        // Project CRUD mutations
        Route::post('projects', [ProjectController::class, 'store'])->name('api.v1.projects.store');
        Route::put('projects/{id}', [ProjectController::class, 'update'])->name('api.v1.projects.update');
        Route::delete('projects/{id}', [ProjectController::class, 'destroy'])->name('api.v1.projects.destroy');
    });
});
