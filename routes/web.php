<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Backend Controllers
use App\Http\Controllers\Backend\SiteController;
use App\Http\Controllers\Backend\RdbProjectController;
use App\Http\Controllers\Backend\RdbResearcherController;
use App\Http\Controllers\Backend\RdbbranchController;
use App\Http\Controllers\Backend\RdbdepartmentController;
use App\Http\Controllers\Backend\RdbdepartmenttypeController;
use App\Http\Controllers\Backend\RdbdepmajorController;
use App\Http\Controllers\Backend\RdbgroupprojectController;
use App\Http\Controllers\Backend\RdbprefixController;
use App\Http\Controllers\Backend\RdbprojectfilesController;
use App\Http\Controllers\Backend\RdbprojectpositionController;
use App\Http\Controllers\Backend\RdbprojecttypeController;
use App\Http\Controllers\Backend\RdbProjectTypesGroupController;
use App\Http\Controllers\Backend\RdbProjectTypeSubController;
use App\Http\Controllers\Backend\RdbprojectworkController;
use App\Http\Controllers\Backend\RdbstrategicController;
use App\Http\Controllers\Backend\RdbyearController;
use App\Http\Controllers\Backend\RbacController;
use App\Http\Controllers\Backend\UserController;

// Frontend Controllers
use App\Http\Controllers\Frontend\JournalstatusController;
use App\Http\Controllers\Frontend\RdbchangwatController;
use App\Http\Controllers\Frontend\RdbdateeventController;
use App\Http\Controllers\Frontend\RdbdateeventtypeController;
use App\Http\Controllers\Frontend\RdbdepartmentController as FrontendRdbdepartmentController;
use App\Http\Controllers\Frontend\RdbdepartmentcategoryController;
use App\Http\Controllers\Frontend\RdbdepartmentcourseController;
use App\Http\Controllers\Frontend\RdbdepmajorController as FrontendRdbdepmajorController;
use App\Http\Controllers\Frontend\RdbdipController;
use App\Http\Controllers\Frontend\RdbdiptypeController;
use App\Http\Controllers\Frontend\RdbgroupprojectController as FrontendRdbgroupprojectController;
use App\Http\Controllers\Frontend\RdbnaccController;
use App\Http\Controllers\Frontend\RdbprefixController as FrontendRdbprefixController;
use App\Http\Controllers\Frontend\RdbprojectController as FrontendRdbprojectController;
use App\Http\Controllers\Frontend\RdbprojectbudgetController;
use App\Http\Controllers\Frontend\RdbprojectdownloadController;
use App\Http\Controllers\Frontend\RdbprojectfilesController as FrontendRdbprojectfilesController;
use App\Http\Controllers\Frontend\RdbprojectgroupController;
use App\Http\Controllers\Frontend\RdbprojectpersonnelController;
use App\Http\Controllers\Frontend\RdbprojectpersonneldepController;
use App\Http\Controllers\Frontend\RdbprojectpositionController as FrontendRdbprojectpositionController;
use App\Http\Controllers\Frontend\RdbprojectstatusController;
use App\Http\Controllers\Frontend\RdbprojecttypeController as FrontendRdbprojecttypeController;
use App\Http\Controllers\Frontend\RdbprojecttypesubController as FrontendRdbprojecttypesubController;
use App\Http\Controllers\Frontend\RdbprojectutilizationController;
use App\Http\Controllers\Frontend\RdbprojectutilizeController;
use App\Http\Controllers\Frontend\RdbprojectutilizetypeController;
use App\Http\Controllers\Frontend\RdbprojectworkController as FrontendRdbprojectworkController;
use App\Http\Controllers\Frontend\RdbpublishedController;
use App\Http\Controllers\Frontend\RdbpublishedbranchController;
use App\Http\Controllers\Frontend\RdbpublishedbranchperController;
use App\Http\Controllers\Frontend\RdbpublishedcheckyearController;
use App\Http\Controllers\Frontend\RdbpublishedpersonnelController;
use App\Http\Controllers\Frontend\RdbpublishedpersonneldepController;
use App\Http\Controllers\Frontend\RdbpublishedtypeController;
use App\Http\Controllers\Frontend\RdbpublishedtypeauthorController;
use App\Http\Controllers\Frontend\RdbpublishedworkController;
use App\Http\Controllers\Frontend\RdbresearcherController as FrontendRdbresearcherController;
use App\Http\Controllers\Frontend\RdbresearchereducationController;
use App\Http\Controllers\Frontend\RdbresearcherstatusController;
use App\Http\Controllers\Frontend\RdbstrategicController as FrontendRdbstrategicController;
use App\Http\Controllers\Frontend\RdbtrainingController;
use App\Http\Controllers\Frontend\RdbtrainingregisterController;
use App\Http\Controllers\Frontend\RdbyearController as FrontendRdbyearController;
use App\Http\Controllers\Frontend\ResearchcoferenceinthaiController;
use App\Http\Controllers\Frontend\ResearchnewsController;
use App\Http\Controllers\Frontend\SiteController as FrontendSiteController;

Route::get('/', [FrontendSiteController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Backend Routes (Protected)
Route::prefix('backend')->name('backend.')->middleware(['auth'])->group(function () {
    Route::get('/', [SiteController::class, 'index'])->name('site.index');

    // AJAX Researcher Search (must be BEFORE resource route)
    Route::get('/rdb_project/search-researchers', [RdbProjectController::class, 'searchResearchers'])->name('rdb_project.search_researchers');
    // AJAX Project Search (Central)
    Route::get('/rdb_project/search', [RdbProjectController::class, 'search'])->name('rdb_project.search');

    // Project Files Management Routes
    Route::post('rdb_project/{id}/upload-abstract', [RdbProjectController::class, 'uploadAbstract'])->name('rdb_project.upload_abstract');
    Route::delete('rdb_project/{id}/delete-abstract', [RdbProjectController::class, 'deleteAbstract'])->name('rdb_project.delete_abstract');
    Route::get('rdb_project/{id}/view-abstract', [RdbProjectController::class, 'viewAbstract'])->name('rdb_project.view_abstract');

    Route::post('rdb_project/{id}/upload-report', [RdbProjectController::class, 'uploadReport'])->name('rdb_project.upload_report');
    Route::delete('rdb_project/{id}/delete-report', [RdbProjectController::class, 'deleteReport'])->name('rdb_project.delete_report');
    Route::get('rdb_project/{id}/view-report', [RdbProjectController::class, 'viewReport'])->name('rdb_project.view_report');

    Route::post('rdb_project/{id}/file', [RdbProjectController::class, 'storeFile'])->name('rdb_project.store_file');
    Route::put('rdb_project/{id}/file/{fileId}', [RdbProjectController::class, 'updateFile'])->name('rdb_project.update_file');
    Route::delete('rdb_project/{id}/file/{fileId}', [RdbProjectController::class, 'destroyFile'])->name('rdb_project.destroy_file');
    // Export Routes (Must be BEFORE resource routes)
    Route::get('/rdb_project/export', [RdbProjectController::class, 'export'])->name('rdb_project.export');
    Route::get('/rdb_researcher/export', [RdbResearcherController::class, 'export'])->name('rdb_researcher.export');

    Route::resource('rdb_project', RdbProjectController::class);
    Route::resource('rdb_researcher', RdbResearcherController::class);
    Route::post('/rdb_researcher/{id}/update-codeid', [RdbResearcherController::class, 'updateCodeId'])->name('rdb_researcher.update_codeid');
    Route::post('/rdb_researcher/{id}/update-image', [RdbResearcherController::class, 'updateImage'])->name('rdb_researcher.update_image');
    Route::get('/rdb_researcher/{id}/view-image', [RdbResearcherController::class, 'viewProfileImage'])->name('rdb_researcher.view_image');
    Route::post('/rdb_researcher/{id}/sync-scopus', [RdbResearcherController::class, 'syncScopus'])->name('rdb_researcher.sync_scopus');
    Route::resource('rdbbranch', RdbbranchController::class);
    Route::resource('rdbdepartment', RdbdepartmentController::class);
    Route::resource('rdbdepartmenttype', RdbdepartmenttypeController::class);
    Route::resource('rdbdepmajor', RdbdepmajorController::class);
    Route::resource('rdbgroupproject', RdbgroupprojectController::class);
    Route::resource('rdbprefix', RdbprefixController::class);
    Route::resource('rdbprojectfiles', RdbprojectfilesController::class);
    Route::resource('rdbprojectposition', RdbprojectpositionController::class);
    Route::resource('rdbprojecttype', RdbprojecttypeController::class);
    Route::resource('rdbprojecttypesgroup', RdbProjectTypesGroupController::class);
    Route::resource('rdbprojecttypesub', RdbProjectTypeSubController::class);
    Route::resource('rdbprojectwork', RdbprojectworkController::class);
    Route::resource('rdbstrategic', RdbstrategicController::class);
    
    // New Master Data Routes
    Route::resource('rdbpublishedtype', \App\Http\Controllers\Backend\RdbPublishedTypeController::class);
    Route::resource('rdbdiptype', \App\Http\Controllers\Backend\RdbDipTypeController::class);
    Route::resource('rdbprojectutilizetype', \App\Http\Controllers\Backend\RdbProjectUtilizeTypeController::class);

    Route::resource('rbac', RbacController::class);
    Route::resource('user', UserController::class);

    Route::resource('rdbyear', RdbyearController::class);
    Route::get('/rdb_published/search/researcher', [\App\Http\Controllers\Backend\RdbPublishedController::class, 'searchResearcher'])->name('rdb_published.search_researcher');
    Route::get('/rdb_published/search/project', [\App\Http\Controllers\Backend\RdbPublishedController::class, 'searchProject'])->name('rdb_published.search_project');
    
    // Published Author Management
    Route::post('/rdb_published/{id}/author', [\App\Http\Controllers\Backend\RdbPublishedController::class, 'storeAuthor'])->name('rdb_published.store_author');
    Route::put('/rdb_published/{id}/author/{researcher_id}', [\App\Http\Controllers\Backend\RdbPublishedController::class, 'updateAuthor'])->name('rdb_published.update_author');
    Route::delete('/rdb_published/{id}/author/{researcher_id}', [\App\Http\Controllers\Backend\RdbPublishedController::class, 'destroyAuthor'])->name('rdb_published.destroy_author');

    // Published File Management
    Route::post('/rdb_published/{id}/upload-file', [\App\Http\Controllers\Backend\RdbPublishedController::class, 'uploadFile'])->name('rdb_published.upload_file');
    Route::post('/rdb_published/{id}/delete-file', [\App\Http\Controllers\Backend\RdbPublishedController::class, 'deleteFile'])->name('rdb_published.delete_file');
    Route::get('/rdb_published/{id}/view-file', [\App\Http\Controllers\Backend\RdbPublishedController::class, 'viewFile'])->name('rdb_published.view_file');

    Route::resource('rdb_published', \App\Http\Controllers\Backend\RdbPublishedController::class);
    
    // DIP File Management
    Route::post('/rdb_dip/{id}/upload-file', [\App\Http\Controllers\Backend\RdbDipController::class, 'uploadFile'])->name('rdb_dip.upload_file');
    Route::get('/rdb_dip/{id}/view-file', [\App\Http\Controllers\Backend\RdbDipController::class, 'viewFile'])->name('rdb_dip.view_file');
    Route::delete('/rdb_dip/{id}/delete-file', [\App\Http\Controllers\Backend\RdbDipController::class, 'deleteFile'])->name('rdb_dip.delete_file');
    Route::get('/rdb_dip/search/years', [\App\Http\Controllers\Backend\RdbDipController::class, 'searchYears'])->name('rdb_dip.search_years');
    
    Route::resource('rdb_dip', \App\Http\Controllers\Backend\RdbDipController::class);
    
    // AJAX Location Search (for cascading dropdowns)
    Route::get('/rdbprojectutilize/search/provinces', [\App\Http\Controllers\Backend\RdbProjectUtilizeController::class, 'searchProvinces'])->name('rdbprojectutilize.search_provinces');
    Route::get('/rdbprojectutilize/search/amphoes', [\App\Http\Controllers\Backend\RdbProjectUtilizeController::class, 'searchAmphoes'])->name('rdbprojectutilize.search_amphoes');
    Route::get('/rdbprojectutilize/search/tambons', [\App\Http\Controllers\Backend\RdbProjectUtilizeController::class, 'searchTambons'])->name('rdbprojectutilize.search_tambons');
    Route::get('/rdbprojectutilize/search/location', [\App\Http\Controllers\Backend\RdbProjectUtilizeController::class, 'searchLocation'])->name('rdbprojectutilize.search_location');
    Route::get('/rdbprojectutilize/search/years', [\App\Http\Controllers\Backend\RdbProjectUtilizeController::class, 'searchYears'])->name('rdbprojectutilize.search_years');
    Route::get('/rdbprojectutilize/search/utilize-types', [\App\Http\Controllers\Backend\RdbProjectUtilizeController::class, 'searchUtilizeTypes'])->name('rdbprojectutilize.search_utilize_types');
    Route::get('/rdbprojectutilize/search/projects', [\App\Http\Controllers\Backend\RdbProjectUtilizeController::class, 'searchProjects'])->name('rdbprojectutilize.search_projects');
    Route::get('/rdbprojectutilize/download/{id}/{filename}', [\App\Http\Controllers\Backend\RdbProjectUtilizeController::class, 'downloadFile'])->name('rdbprojectutilize.download');
    
    Route::resource('rdbprojectutilize', \App\Http\Controllers\Backend\RdbProjectUtilizeController::class);
    Route::resource('research_news', \App\Http\Controllers\Backend\ResearchNewsController::class);
    Route::get('/research_news/{id}/view-image', [\App\Http\Controllers\Backend\ResearchNewsController::class, 'viewNewsImage'])->name('research_news.view_image');
    Route::resource('research_conference', \App\Http\Controllers\Backend\ResearchConferenceController::class);
    Route::get('/research_conference/{id}/view-image', [\App\Http\Controllers\Backend\ResearchConferenceController::class, 'viewConferenceImage'])->name('research_conference.view_image');

    // Project Researcher Management
    Route::post('/rdb_project/{id}/researcher', [RdbProjectController::class, 'storeResearcher'])->name('rdb_project.researcher.store');
    Route::put('/rdb_project/{id}/researcher/{rid}', [RdbProjectController::class, 'updateResearcher'])->name('rdb_project.researcher.update');
    Route::delete('/rdb_project/{id}/researcher/{rid}', [RdbProjectController::class, 'destroyResearcher'])->name('rdb_project.researcher.destroy');

    // File Management
    Route::post('/rdb_project/{id}/file', [RdbProjectController::class, 'storeFile'])->name('rdb_project.file.store');
    Route::put('/rdb_project/{id}/file/{fid}', [RdbProjectController::class, 'updateFile'])->name('rdb_project.file.update');
    Route::delete('/rdb_project/{id}/file/{fid}', [RdbProjectController::class, 'destroyFile'])->name('rdb_project.file.destroy');
    Route::get('/rdb_project/{id}/file/{fid}/download', [RdbProjectController::class, 'downloadFile'])->name('rdb_project.file.download');
    Route::post('/rdb_project/{id}/toggle-report-status', [RdbProjectController::class, 'toggleReportStatus'])->name('rdb_project.toggle_report_status');
    Route::post('/rdb_project/{id}/file/{fid}/toggle-status', [RdbProjectController::class, 'toggleFileStatus'])->name('rdb_project.file.toggle_status');

    // Project Main Files (Abstract & Report)
    Route::post('/rdb_project/{id}/upload-abstract', [RdbProjectController::class, 'uploadAbstract'])->name('rdb_project.upload_abstract');
    Route::delete('/rdb_project/{id}/delete-abstract', [RdbProjectController::class, 'deleteAbstract'])->name('rdb_project.delete_abstract');
    Route::post('/rdb_project/{id}/upload-report', [RdbProjectController::class, 'uploadReport'])->name('rdb_project.upload_report');
    Route::delete('/rdb_project/{id}/delete-report', [RdbProjectController::class, 'deleteReport'])->name('rdb_project.delete_report');
    Route::get('/rdb_project/{id}/view-abstract', [RdbProjectController::class, 'viewAbstract'])->name('rdb_project.view_abstract');
    Route::get('/rdb_project/{id}/view-report', [RdbProjectController::class, 'viewReport'])->name('rdb_project.view_report');

    // AJAX Search endpoints for Project Form
    Route::get('/rdb_project/search/project-type', [RdbProjectController::class, 'searchProjectType'])->name('rdb_project.search_project_type');
    Route::get('/rdb_project/search/project-type-sub', [RdbProjectController::class, 'searchProjectTypeSub'])->name('rdb_project.search_project_type_sub');
    Route::get('/rdb_project/search/pro-group', [RdbProjectController::class, 'searchProGroup'])->name('rdb_project.search_pro_group');
    Route::get('/rdb_project/search/researcher', [RdbProjectController::class, 'searchResearcher'])->name('rdb_project.search_researcher');
    Route::get('/rdb_project/search/depcou', [RdbProjectController::class, 'searchDepcou'])->name('rdb_project.search_depcou');
    Route::get('/rdb_project/search/major', [RdbProjectController::class, 'searchMajor'])->name('rdb_project.search_major');

});

// Frontend Routes
Route::name('frontend.')->group(function () {
    Route::resource('journalstatus', JournalstatusController::class)->parameters(['journalstatus' => 'id'])->only(['index', 'show']);
    Route::resource('rdbchangwat', RdbchangwatController::class)->parameters(['rdbchangwat' => 'id'])->only(['index', 'show']);
    Route::resource('rdbdateevent', RdbdateeventController::class)->parameters(['rdbdateevent' => 'id'])->only(['index', 'show']);
    Route::resource('rdbdateeventtype', RdbdateeventtypeController::class)->parameters(['rdbdateeventtype' => 'id'])->only(['index', 'show']);
    Route::resource('rdbdepartment', FrontendRdbdepartmentController::class)->parameters(['rdbdepartment' => 'id'])->only(['index', 'show']);
    Route::resource('rdbdepartmentcategory', RdbdepartmentcategoryController::class)->parameters(['rdbdepartmentcategory' => 'id'])->only(['index', 'show']);
    Route::resource('rdbdepartmentcourse', RdbdepartmentcourseController::class)->parameters(['rdbdepartmentcourse' => 'id'])->only(['index', 'show']);
    Route::resource('rdbdepartmenttype', RdbdepartmenttypeController::class)->parameters(['rdbdepartmenttype' => 'id'])->only(['index', 'show']);
    Route::resource('rdbdepmajor', RdbdepmajorController::class)->parameters(['rdbdepmajor' => 'id'])->only(['index', 'show']);
    Route::get('rdbdip/export', [RdbdipController::class, 'export'])->name('rdbdip.export');
    Route::resource('rdbdip', RdbdipController::class)->parameters(['rdbdip' => 'id'])->only(['index', 'show']);
    Route::resource('rdbdiptype', RdbdiptypeController::class)->parameters(['rdbdiptype' => 'id'])->only(['index', 'show']);
    Route::resource('rdbgroupproject', FrontendRdbgroupprojectController::class)->parameters(['rdbgroupproject' => 'id'])->only(['index', 'show']);
    Route::resource('rdbnacc', RdbnaccController::class)->parameters(['rdbnacc' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprefix', FrontendRdbprefixController::class)->parameters(['rdbprefix' => 'id'])->only(['index', 'show']);
    Route::resource('rdbproject', FrontendRdbprojectController::class)->parameters(['rdbproject' => 'id'])->only(['index', 'show']);
    Route::get('rdbproject/api/types-by-year', [FrontendRdbprojectController::class, 'getTypesByYear'])->name('rdbproject.typesByYear');
    Route::get('rdbproject/api/subtypes-by-type', [FrontendRdbprojectController::class, 'getSubTypesByType'])->name('rdbproject.subTypesByType');
    Route::get('rdbproject/api/search-researchers', [FrontendRdbprojectController::class, 'searchResearchers'])->name('rdbproject.search_researchers');
    Route::get('rdbproject/{id}/file/{fid}/download', [FrontendRdbprojectController::class, 'downloadFile'])->name('rdbproject.file.download');
    Route::get('rdbproject/{id}/view-abstract', [FrontendRdbprojectController::class, 'viewAbstract'])->name('rdbproject.view_abstract');
    Route::get('rdbproject/{id}/view-report', [FrontendRdbprojectController::class, 'viewReport'])->name('rdbproject.view_report');
    Route::resource('rdbprojectbudget', RdbprojectbudgetController::class)->parameters(['rdbprojectbudget' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprojectdownload', RdbprojectdownloadController::class)->parameters(['rdbprojectdownload' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprojectfiles', RdbprojectfilesController::class)->parameters(['rdbprojectfiles' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprojectgroup', RdbprojectgroupController::class)->parameters(['rdbprojectgroup' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprojectpersonnel', RdbprojectpersonnelController::class)->parameters(['rdbprojectpersonnel' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprojectpersonneldep', RdbprojectpersonneldepController::class)->parameters(['rdbprojectpersonneldep' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprojectposition', FrontendRdbprojectpositionController::class)->parameters(['rdbprojectposition' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprojectstatus', RdbprojectstatusController::class)->parameters(['rdbprojectstatus' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprojecttype', FrontendRdbprojecttypeController::class)->parameters(['rdbprojecttype' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprojecttypesub', FrontendRdbprojecttypesubController::class)->parameters(['rdbprojecttypesub' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprojectutilization', RdbprojectutilizationController::class)->parameters(['rdbprojectutilization' => 'id']);
    Route::resource('rdbprojectutilize', RdbprojectutilizeController::class)->parameters(['rdbprojectutilize' => 'id'])->only(['index', 'show']);

    Route::resource('rdbprojectutilizetype', RdbprojectutilizetypeController::class)->parameters(['rdbprojectutilizetype' => 'id'])->only(['index', 'show']);
    Route::resource('rdbprojectwork', RdbprojectworkController::class)->parameters(['rdbprojectwork' => 'id'])->only(['index', 'show']);
    Route::get('rdbpublished/export', [RdbpublishedController::class, 'export'])->name('rdbpublished.export');
    Route::resource('rdbpublished', RdbpublishedController::class)->parameters(['rdbpublished' => 'id'])->only(['index', 'show']);
    Route::resource('rdbpublishedbranch', RdbpublishedbranchController::class)->parameters(['rdbpublishedbranch' => 'id'])->only(['index', 'show']);
    Route::resource('rdbpublishedbranchper', RdbpublishedbranchperController::class)->parameters(['rdbpublishedbranchper' => 'id'])->only(['index', 'show']);
    Route::resource('rdbpublishedcheckyear', RdbpublishedcheckyearController::class)->parameters(['rdbpublishedcheckyear' => 'id'])->only(['index', 'show']);
    Route::resource('rdbpublishedpersonnel', RdbpublishedpersonnelController::class)->parameters(['rdbpublishedpersonnel' => 'id'])->only(['index', 'show']);
    Route::resource('rdbpublishedpersonneldep', RdbpublishedpersonneldepController::class)->parameters(['rdbpublishedpersonneldep' => 'id'])->only(['index', 'show']);
    Route::resource('rdbpublishedtype', RdbpublishedtypeController::class)->parameters(['rdbpublishedtype' => 'id'])->only(['index', 'show']);
    Route::resource('rdbpublishedtypeauthor', RdbpublishedtypeauthorController::class)->parameters(['rdbpublishedtypeauthor' => 'id'])->only(['index', 'show']);
    Route::resource('rdbpublishedwork', RdbpublishedworkController::class)->parameters(['rdbpublishedwork' => 'id'])->only(['index', 'show']);
    Route::get('rdbresearcher/export', [FrontendRdbresearcherController::class, 'export'])->name('rdbresearcher.export');
    Route::resource('rdbresearcher', FrontendRdbresearcherController::class)->parameters(['rdbresearcher' => 'id'])->only(['index', 'show']);
    Route::resource('rdbresearchereducation', RdbresearchereducationController::class)->parameters(['rdbresearchereducation' => 'id'])->only(['index', 'show']);
    Route::resource('rdbresearcherstatus', RdbresearcherstatusController::class)->parameters(['rdbresearcherstatus' => 'id'])->only(['index', 'show']);
    Route::resource('rdbstrategic', FrontendRdbstrategicController::class)->parameters(['rdbstrategic' => 'id'])->only(['index', 'show']);
    Route::resource('rdbtraining', RdbtrainingController::class)->parameters(['rdbtraining' => 'id'])->only(['index', 'show']);
    Route::resource('rdbtrainingregister', RdbtrainingregisterController::class)->parameters(['rdbtrainingregister' => 'id'])->only(['index', 'show']);
    Route::resource('rdbyear', FrontendRdbyearController::class)->parameters(['rdbyear' => 'id'])->only(['index', 'show']);
    Route::resource('researchcoferenceinthai', ResearchcoferenceinthaiController::class)->parameters(['researchcoferenceinthai' => 'id'])->only(['index', 'show']);
    Route::resource('researchnews', ResearchnewsController::class)->parameters(['researchnews' => 'id'])->only(['index', 'show']);
    Route::get('/site', [FrontendSiteController::class, 'index'])->name('site.index');
});

// Public Routes (ไม่ต้อง authentication)
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ✅ Debug Routes - เฉพาะ Local Environment เท่านั้น
if (app()->isLocal()) {
    Route::prefix('debug')->name('debug.')->group(function () {
        /**
         * ⚠️  Debug Permissions Route - Development ONLY
         * ใช้สำหรับตรวจสอบ RBAC system ในช่วงพัฒนา
         */
        Route::get('/permissions', function () {
            $user = auth()->user();
            if (!$user) {
                return response('ต้อง Login ก่อน', 403);
            }

            return [
                'user_id' => $user->id,
                'username' => $user->username,
                'roles' => $user->getRoles(),
                'permissions' => $user->getPermissions(),
            ];
        })->name('permissions')->middleware('auth');

        /**
         * ⚠️  Debug Database Route - Development ONLY
         */
        Route::get('/database', function () {
            return [
                'database' => config('database.default'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
            ];
        })->name('database');

        /**
         * ⚠️  Debug Environment Route - Development ONLY
         */
        Route::get('/environment', function () {
            return [
                'app_env' => app()->environment(),
                'app_debug' => config('app.debug'),
                'locale' => config('app.locale'),
            ];
        })->name('environment');
    });
}

// ✅ ถ้าพยายามเข้า Debug routes ในสภาพแวดล้อมที่ไม่ใช่ local → 404
if (!app()->isLocal()) {
    Route::any('/debug{any}', function () {
        abort(404);
    })->where('any', '.*');
}

require __DIR__.'/auth.php';

// ✅ Frontend Routes - Public/Guest Access (Read Only)
Route::prefix('project')->name('frontend.rdb_project.')->group(function () {
    // View project details
    Route::get('/{id}', [FrontendRdbprojectController::class, 'show'])
        ->name('show')
        ->where('id', '[0-9]+');

    // ✅ View abstract (read only - no upload/edit/delete)
    Route::get('/{id}/abstract/view', [FrontendRdbprojectController::class, 'viewAbstract'])
        ->name('view_abstract')
        ->where('id', '[0-9]+');

    // ✅ View report (read only - no upload/edit/delete)
    Route::get('/{id}/report/view', [FrontendRdbprojectController::class, 'viewReport'])
        ->name('view_report')
        ->where('id', '[0-9]+');

    // ✅ Download file (read only - no upload/edit/delete)
    Route::get('/{id}/file/{fid}/download', [FrontendRdbprojectController::class, 'downloadFile'])
        ->name('download_file')
        ->where('id', '[0-9]+')
        ->where('fid', '[0-9]+');
});
