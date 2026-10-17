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
use App\Models\RdbYear;
use App\Models\User;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewDashboard', User::class);

        $availableYears = RdbYear::orderBy('year_name', 'desc')->get();
        $selectedYearId = $request->filled('year_id') ? (int)$request->year_id : null;
        $selectedYear = $selectedYearId ? $availableYears->firstWhere('year_id', $selectedYearId) : null;

        // 1. Projects Count & Total Budget
        $projectQuery = RdbProject::query();
        if ($selectedYearId) {
            $projectQuery->where('year_id', $selectedYearId);
        }
        $totalProjects = $projectQuery->count();
        $totalProjectBudget = (float) $projectQuery->sum('pro_budget');

        // 2. Researchers (Involved in projects of the selected year or total)
        if ($selectedYearId) {
            $totalResearchers = RdbResearcher::whereHas('rdbProjects', function ($q) use ($selectedYearId) {
                $q->where('year_id', $selectedYearId);
            })->count();
        } else {
            $totalResearchers = RdbResearcher::count();
        }

        // 3. Publications
        $publishedQuery = RdbPublished::query();
        if ($selectedYearId) {
            $publishedQuery->where(function ($q) use ($selectedYearId) {
                $q->where('year_id', $selectedYearId)
                  ->orWhere('year_bud', $selectedYearId);
            });
        }
        $totalPublished = $publishedQuery->count();

        // 4. Intellectual Property (DIP)
        $dipQuery = RdbDip::query();
        if ($selectedYearId && $selectedYear) {
            $ceYear = (int)$selectedYear->year_name - 543;
            $dipQuery->where(function ($q) use ($selectedYearId, $ceYear) {
                $q->whereHas('project', function ($pq) use ($selectedYearId) {
                    $pq->where('year_id', $selectedYearId);
                })->orWhereYear('dip_request_date', $ceYear);
            });
        }
        $totalDip = $dipQuery->count();

        // 5. Utilization
        $utilizeQuery = RdbProjectUtilize::query();
        if ($selectedYearId) {
            $utilizeQuery->where(function ($q) use ($selectedYearId) {
                $q->where('utz_year_id', $selectedYearId)
                  ->orWhere('utz_year_bud', $selectedYearId);
            });
        }
        $totalUtilize = $utilizeQuery->count();

        // 6. News & Conferences
        if ($selectedYearId && $selectedYear) {
            $ceYear = (int)$selectedYear->year_name - 543;
            $totalNews = ResearchNews::whereYear('news_date', $ceYear)->count();
            $totalConference = ResearchConferenceInThai::whereYear('con_even_date', $ceYear)->count();
        } else {
            $totalNews = ResearchNews::count();
            $totalConference = ResearchConferenceInThai::count();
        }

        $totalUsers = User::count();

        // 7. Projects by Status (Filtered by year if selected)
        $statusQuery = RdbProject::select('ps_id', DB::raw('count(*) as total'));
        if ($selectedYearId) {
            $statusQuery->where('year_id', $selectedYearId);
        }
        $projectsByStatus = $statusQuery->groupBy('ps_id')
            ->with('status')
            ->get();

        // 8. Projects by Year (Last 5 Years) with Total Count & Total Budget
        $projectsByYear = RdbProject::select(
                'year_id',
                DB::raw('count(*) as total'),
                DB::raw('COALESCE(SUM(pro_budget), 0) as total_budget')
            )
            ->whereNotNull('year_id')
            ->groupBy('year_id')
            ->with('year')
            ->orderBy('year_id', 'desc')
            ->limit(5)
            ->get()
            ->reverse()
            ->values();

        // 9. Recent 5 Projects (Filtered by year if selected)
        $recentQuery = RdbProject::with(['year', 'status', 'department']);
        if ($selectedYearId) {
            $recentQuery->where('year_id', $selectedYearId);
        }
        $recentProjects = $recentQuery->orderBy('pro_id', 'desc')
            ->take(5)
            ->get();

        return view('backend.site.index', compact(
            'availableYears',
            'selectedYear',
            'selectedYearId',
            'totalProjects',
            'totalProjectBudget',
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