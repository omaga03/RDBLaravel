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
use App\Http\Controllers\Backend\RdbprojectworkController;
use App\Http\Controllers\Backend\RdbstrategicController;
use App\Http\Controllers\Backend\RdbyearController;

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
use App\Http\Controllers\Frontend\RdbprojecttypesubController;
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

    Route::resource('rdb_project', RdbProjectController::class);
    Route::resource('rdb_researcher', RdbResearcherController::class);
    Route::post('/rdb_researcher/{id}/update-codeid', [RdbResearcherController::class, 'updateCodeId'])->name('rdb_researcher.update_codeid');
    Route::post('/rdb_researcher/{id}/update-image', [RdbResearcherController::class, 'updateImage'])->name('rdb_researcher.update_image');
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
    Route::resource('rdbprojectwork', RdbprojectworkController::class);
    Route::resource('rdbstrategic', RdbstrategicController::class);
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
    Route::resource('journalstatus', JournalstatusController::class)->parameters(['journalstatus' => 'id']);
    Route::resource('rdbchangwat', RdbchangwatController::class)->parameters(['rdbchangwat' => 'id']);
    Route::resource('rdbdateevent', RdbdateeventController::class)->parameters(['rdbdateevent' => 'id']);
    Route::resource('rdbdateeventtype', RdbdateeventtypeController::class)->parameters(['rdbdateeventtype' => 'id']);
    Route::resource('rdbdepartment', FrontendRdbdepartmentController::class)->parameters(['rdbdepartment' => 'id']);
    Route::resource('rdbdepartmentcategory', RdbdepartmentcategoryController::class)->parameters(['rdbdepartmentcategory' => 'id']);
    Route::resource('rdbdepartmentcourse', RdbdepartmentcourseController::class)->parameters(['rdbdepartmentcourse' => 'id']);
    Route::resource('rdbdepartmenttype', RdbdepartmenttypeController::class)->parameters(['rdbdepartmenttype' => 'id']);
    Route::resource('rdbdepmajor', RdbdepmajorController::class)->parameters(['rdbdepmajor' => 'id']);
    Route::get('rdbdip/export', [RdbdipController::class, 'export'])->name('rdbdip.export');
    Route::resource('rdbdip', RdbdipController::class)->parameters(['rdbdip' => 'id']);
    Route::resource('rdbdiptype', RdbdiptypeController::class)->parameters(['rdbdiptype' => 'id']);
    Route::resource('rdbgroupproject', FrontendRdbgroupprojectController::class)->parameters(['rdbgroupproject' => 'id']);
    Route::resource('rdbnacc', RdbnaccController::class)->parameters(['rdbnacc' => 'id']);
    Route::resource('rdbprefix', FrontendRdbprefixController::class)->parameters(['rdbprefix' => 'id']);
    Route::get('rdbproject/export', [FrontendRdbprojectController::class, 'export'])->name('rdbproject.export');
    Route::get('rdbproject/api/types-by-year', [FrontendRdbprojectController::class, 'getTypesByYear'])->name('rdbproject.typesByYear');
    Route::get('rdbproject/api/subtypes-by-type', [FrontendRdbprojectController::class, 'getSubTypesByType'])->name('rdbproject.subTypesByType');
    Route::resource('rdbproject', FrontendRdbprojectController::class)->parameters(['rdbproject' => 'id']);
    Route::resource('rdbprojectbudget', RdbprojectbudgetController::class)->parameters(['rdbprojectbudget' => 'id']);
    Route::resource('rdbprojectdownload', RdbprojectdownloadController::class)->parameters(['rdbprojectdownload' => 'id']);
    Route::resource('rdbprojectfiles', RdbprojectfilesController::class)->parameters(['rdbprojectfiles' => 'id']);
    Route::resource('rdbprojectgroup', RdbprojectgroupController::class)->parameters(['rdbprojectgroup' => 'id']);
    Route::resource('rdbprojectpersonnel', RdbprojectpersonnelController::class)->parameters(['rdbprojectpersonnel' => 'id']);
    Route::resource('rdbprojectpersonneldep', RdbprojectpersonneldepController::class)->parameters(['rdbprojectpersonneldep' => 'id']);
    Route::resource('rdbprojectposition', FrontendRdbprojectpositionController::class)->parameters(['rdbprojectposition' => 'id']);
    Route::resource('rdbprojectstatus', RdbprojectstatusController::class)->parameters(['rdbprojectstatus' => 'id']);
    Route::resource('rdbprojecttype', FrontendRdbprojecttypeController::class)->parameters(['rdbprojecttype' => 'id']);
    Route::resource('rdbprojecttypesub', RdbprojecttypesubController::class)->parameters(['rdbprojecttypesub' => 'id']);
    Route::resource('rdbprojectutilization', RdbprojectutilizationController::class)->parameters(['rdbprojectutilization' => 'id']);
    Route::resource('rdbprojectutilize', RdbprojectutilizeController::class)->parameters(['rdbprojectutilize' => 'id']);

    Route::resource('rdbprojectutilizetype', RdbprojectutilizetypeController::class)->parameters(['rdbprojectutilizetype' => 'id']);
    Route::resource('rdbprojectwork', RdbprojectworkController::class)->parameters(['rdbprojectwork' => 'id']);
    Route::get('rdbpublished/export', [RdbpublishedController::class, 'export'])->name('rdbpublished.export');
    Route::resource('rdbpublished', RdbpublishedController::class)->parameters(['rdbpublished' => 'id']);
    Route::resource('rdbpublishedbranch', RdbpublishedbranchController::class)->parameters(['rdbpublishedbranch' => 'id']);
    Route::resource('rdbpublishedbranchper', RdbpublishedbranchperController::class)->parameters(['rdbpublishedbranchper' => 'id']);
    Route::resource('rdbpublishedcheckyear', RdbpublishedcheckyearController::class)->parameters(['rdbpublishedcheckyear' => 'id']);
    Route::resource('rdbpublishedpersonnel', RdbpublishedpersonnelController::class)->parameters(['rdbpublishedpersonnel' => 'id']);
    Route::resource('rdbpublishedpersonneldep', RdbpublishedpersonneldepController::class)->parameters(['rdbpublishedpersonneldep' => 'id']);
    Route::resource('rdbpublishedtype', RdbpublishedtypeController::class)->parameters(['rdbpublishedtype' => 'id']);
    Route::resource('rdbpublishedtypeauthor', RdbpublishedtypeauthorController::class)->parameters(['rdbpublishedtypeauthor' => 'id']);
    Route::resource('rdbpublishedwork', RdbpublishedworkController::class)->parameters(['rdbpublishedwork' => 'id']);
    Route::get('rdbresearcher/export', [FrontendRdbresearcherController::class, 'export'])->name('rdbresearcher.export');
    Route::resource('rdbresearcher', FrontendRdbresearcherController::class)->parameters(['rdbresearcher' => 'id']);
    Route::resource('rdbresearchereducation', RdbresearchereducationController::class)->parameters(['rdbresearchereducation' => 'id']);
    Route::resource('rdbresearcherstatus', RdbresearcherstatusController::class)->parameters(['rdbresearcherstatus' => 'id']);
    Route::resource('rdbstrategic', FrontendRdbstrategicController::class)->parameters(['rdbstrategic' => 'id']);
    Route::resource('rdbtraining', RdbtrainingController::class)->parameters(['rdbtraining' => 'id']);
    Route::resource('rdbtrainingregister', RdbtrainingregisterController::class)->parameters(['rdbtrainingregister' => 'id']);
    Route::resource('rdbyear', FrontendRdbyearController::class)->parameters(['rdbyear' => 'id']);
    Route::resource('researchcoferenceinthai', ResearchcoferenceinthaiController::class)->parameters(['researchcoferenceinthai' => 'id']);
    Route::resource('researchnews', ResearchnewsController::class)->parameters(['researchnews' => 'id']);
    Route::get('/site', [FrontendSiteController::class, 'index'])->name('site.index');
});

require __DIR__.'/auth.php';

Route::get('/debug/permissions', function () {
    return \App\Models\AuthItem::select('name', 'type', 'description')->get();
});
