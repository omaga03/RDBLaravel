<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RdbProject;
use App\Models\RdbResearcher;
use App\Models\RdbPublished;
use App\Models\RdbDip;
use App\Models\RdbProjectUtilize;
use App\Models\ResearchNews;
use App\Models\ResearchConferenceInThai;
use App\Models\User;

class SiteController extends Controller
{
    public function index()
    {
        $this->authorize('viewDashboard', User::class);
        
        $totalProjects = RdbProject::count();
        $totalResearchers = RdbResearcher::count();
        $totalPublished = RdbPublished::count();
        $totalDip = RdbDip::count();
        $totalUtilize = RdbProjectUtilize::count();
        $totalNews = ResearchNews::count();
        $totalConference = ResearchConferenceInThai::count();
        $totalUsers = User::count();
        
        // Projects by Status
        $projectsByStatus = RdbProject::select('ps_id', DB::raw('count(*) as total'))
            ->groupBy('ps_id')
            ->with('status')
            ->get();
            
        // Projects by Year (Last 5 Years)
        $projectsByYear = RdbProject::select('year_id', DB::raw('count(*) as total'))
            ->groupBy('year_id')
            ->with('year')
            ->orderBy('year_id', 'desc')
            ->limit(5)
            ->get()
            ->reverse()
            ->values();

        // Recent 5 Projects
        $recentProjects = RdbProject::with(['year', 'status', 'department'])
            ->orderBy('pro_id', 'desc')
            ->take(5)
            ->get();

        return view('backend.site.index', compact(
            'totalProjects',
            'totalResearchers',
            'totalPublished',
            'totalDip',
            'totalUtilize',
            'totalNews',
            'totalConference',
            'totalUsers',
            'projectsByStatus',
            'projectsByYear',
            'recentProjects'
        ));
    }
}