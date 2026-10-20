<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RdbProject;
use App\Models\RdbYear;
use App\Models\RdbProjectType;
use App\Models\RdbDepartment;
use App\Models\RdbProjectStatus;
use Illuminate\Support\Facades\DB;

use App\Models\ResearchCoferenceinthai;
use App\Models\ResearchNews;

class SiteController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        // 0. Find the latest year that actually has project data
        $latest_active_year_id = RdbProject::where('data_show', 1)
                                           ->where('ps_id', '<>', RdbProjectStatus::REFUNDED)
                                           ->max('year_id');

        // If no projects at all, fallback to latest year in DB
        if (!$latest_active_year_id) {
            $latest_active_year_id = RdbYear::max('year_id');
        }

        // 1. Get Years for Dropdown (Only up to the latest active year)
        $years_list = RdbYear::where('year_id', '<=', $latest_active_year_id)
                             ->orderBy('year_name', 'desc')
                             ->get();

        // 2. Determine Year to Show
        if ($request->filled('year_id')) {
            $max_year = RdbYear::find($request->year_id);
        } else {
            // Default to the latest active year
            $max_year = RdbYear::find($latest_active_year_id);
        }

        if (!$max_year) {
            return view('frontend.site.index', ['no_data' => true]);
        }

        // 3. Departments for Chart (Type 1 = Academic faculties + central divisions)
        $depIds = RdbDepartment::getProDepTea(1);
        $departments = RdbDepartment::whereIn('department_id', $depIds)
                                    ->orderBy('department_nameTH', 'asc')
                                    ->pluck('department_nameTH', 'department_id')
                                    ->toArray();

        // 4. Determine 4 comparison years up to $max_year (sorted ascending)
        $chartYears = RdbYear::where('year_id', '<=', $latest_active_year_id)
                             ->where('year_name', '<=', $max_year->year_name)
                             ->orderBy('year_name', 'desc')
                             ->take(4)
                             ->get()
                             ->sortBy('year_name')
                             ->values();
        $chartYearIds = $chartYears->pluck('year_id')->toArray();

        // 5. Query Project counts and budgets grouped by year and department
        $chartDataRows = DB::table('rdb_project')
            ->select(
                'year_id',
                'department_id',
                DB::raw('COUNT(pro_id) as cpro'),
                DB::raw('SUM(pro_budget) as sum_budget')
            )
            ->whereIn('year_id', $chartYearIds)
            ->whereIn('department_id', array_keys($departments))
            ->where('data_show', 1)
            ->where(function ($q) {
                $q->whereNull('ps_id')
                  ->orWhere('ps_id', '<>', RdbProjectStatus::REFUNDED);
            })
            ->groupBy('year_id', 'department_id')
            ->get();

        $matrix = [];
        $budgetPoints = [];

        foreach ($chartDataRows as $row) {
            $matrix[$row->year_id][$row->department_id] = (int)$row->cpro;
            if ($row->year_id == $max_year->year_id) {
                $budgetPoints[$row->department_id] = (float)$row->sum_budget;
            }
        }

        // Prepare Chart.js Data
        $chartColors = ['#1E3A5F', '#2563EB', '#0D9488', '#D97706', '#059669'];
        $datasets = [];
        $i = 0;

        foreach ($chartYears as $yr) {
            $barData = [];
            foreach ($departments as $dId => $dName) {
                $barData[] = $matrix[$yr->year_id][$dId] ?? 0;
            }
            $datasets[] = [
                'label' => (string)$yr->year_name,
                'data' => $barData,
                'backgroundColor' => $chartColors[$i % count($chartColors)],
                'type' => 'bar'
            ];
            $i++;
        }

        // Add Budget Line for the selected year ($max_year)
        $lineData = [];
        foreach ($departments as $dId => $dName) {
            $lineData[] = $budgetPoints[$dId] ?? 0;
        }
        $datasets[] = [
            'label' => 'งบประมาณสนับสนุน (บาท)',
            'data' => $lineData,
            'borderColor' => '#D97706',
            'type' => 'line',
            'yAxisID' => 'y1'
        ];

        $chartData = [
            'labels' => array_values($departments),
            'datasets' => $datasets
        ];

        // 6. Latest Data
        $latestProjects = RdbProject::where('data_show', 1)
                                    ->where(function ($q) {
                                        $q->whereNull('ps_id')
                                          ->orWhere('ps_id', '<>', RdbProjectStatus::REFUNDED);
                                    })
                                    // Eager load researchers sorted by position (1, 2 first)
                                    ->with(['rdbProjectWorks' => function($q) {
                                        $q->orderBy('position_id', 'asc')
                                          ->orderBy('ratio', 'desc');
                                    }, 'rdbProjectWorks.researcher', 'type', 'typeSub'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();

        // 7. Stats for Top Cards
        $sumProject = RdbProject::where('year_id', $max_year->year_id)
                                ->where('data_show', 1)
                                ->where(function ($q) {
                                    $q->whereNull('ps_id')
                                      ->orWhere('ps_id', '<>', RdbProjectStatus::REFUNDED);
                                })
                                ->count();

        $sumBudget = RdbProject::where('year_id', $max_year->year_id)
                               ->where('data_show', 1)
                               ->where(function ($q) {
                                   $q->whereNull('ps_id')
                                     ->orWhere('ps_id', '<>', RdbProjectStatus::REFUNDED);
                               })
                               ->sum('pro_budget');

        // Count distinct researchers who have their names in projects of this fiscal year in ANY status
        $countResearcher = DB::table('rdb_project_work')
            ->join('rdb_project', 'rdb_project_work.pro_id', '=', 'rdb_project.pro_id')
            ->where('rdb_project.year_id', $max_year->year_id)
            ->where('rdb_project.data_show', 1)
            ->where(function ($q) {
                $q->whereNull('rdb_project.ps_id')
                  ->orWhere('rdb_project.ps_id', '<>', RdbProjectStatus::REFUNDED);
            })
            ->whereNotNull('rdb_project_work.researcher_id')
            ->where('rdb_project_work.researcher_id', '>', 0)
            ->distinct()
            ->count('rdb_project_work.researcher_id'); 

        // 6. Latest Publications
        $latestPublications = \App\Models\RdbPublished::with(['project', 'pubtype', 'authors' => function($q) {
                                        $q->withPivot('pubta_id', 'pubw_main'); 
                                    }, 'authors.prefix'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();

        // 7. Latest IP
        $latestIP = \App\Models\RdbDip::with(['dipType', 'researcher', 'researcher.prefix']) 
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();

        // 8. Latest Utilization
        $latestUtilization = \App\Models\RdbProjectUtilize::with(['project', 'changwat'])
                                    ->orderBy('utz_date', 'desc')
                                    ->limit(5)
                                    ->get();

        // 9. Latest Conference
        $latestConference = ResearchCoferenceinthai::orderBy('id', 'desc')->limit(5)->get();

        // 10. Latest News
        $latestNews = ResearchNews::orderBy('id', 'desc')->limit(5)->get();

        return view('frontend.site.index', compact(
            'max_year', 
            'chartData', 
            'latestProjects',
            'latestPublications',
            'latestIP',
            'latestUtilization',
            'latestConference',
            'latestNews',
            'sumProject',
            'sumBudget',
            'countResearcher',
            'years_list'
        ));
    }
}
