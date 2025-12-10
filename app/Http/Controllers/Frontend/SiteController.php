<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProject;
use App\Models\RdbYear;
use App\Models\RdbProjectType;
use App\Models\RdbDepartment;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        // 0. Find the latest year that actually has project data
        $latest_active_year_id = RdbProject::where('data_show', 1)
                                           ->where('ps_id', '<>', 6) // Exclude cancelled if needed, or keep all
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

        // 2. Get Project Types (Type 1 = Academic)
        $ptid = RdbProjectType::getProTyTeaChart($max_year->year_id, 1);

        // 3. Get Departments (Type 1 = Academic)
        $dep = RdbDepartment::getProDepTea(1);

        // 4. Fetch Chart Data
        $sql = RdbProject::getReportProjectChartSplit($dep, $ptid);
        
        // Execute Raw SQL
        $chartDataRaw = DB::select($sql);

        // DEBUG: Check data for 2568
        // dd($chartDataRaw);

        // Process Data for Chart.js
        // We need:
        // - Labels: Departments
        // - Datasets: One for each Year (Project Count) + One for Budget (Spline? Maybe just stick to Project Count for now as per legacy chart structure)
        
        // Legacy Chart Structure:
        // X-Axis: Departments
        // Series: Years (Columns) -> Project Count
        // Series: Budget (Spline) -> Total Budget per Department (Sum of all years?)
        
        // Let's organize data
        $departments = [];
        $years = [];
        $dataPoints = []; // [year][department] = count
        $budgetPoints = []; // [department] = total_budget

        foreach ($chartDataRaw as $row) {
            $depName = trim($row->department_nameTH);
            $yearName = trim($row->year_name);
            
            if (!in_array($depName, $departments)) {
                $departments[] = $depName;
            }
            if (!in_array($yearName, $years)) {
                $years[] = $yearName;
            }

            $dataPoints[$yearName][$depName] = (int)$row->cpro;
            
            if (!isset($budgetPoints[$depName])) {
                $budgetPoints[$depName] = 0;
            }
            $budgetPoints[$depName] += (float)$row->sum_budget;
        }

        // Limit to last 4 years based on LATEST ACTIVE YEAR
        rsort($years); // Sort Descending to get latest years first
        
        // Filter out years greater than max_year (latest active year)
        // Ensure we are comparing numeric values or string values consistently
        if ($max_year) {
             $years = array_filter($years, function($y) use ($max_year) {
                 return $y <= $max_year->year_name;
             });
        }
        
        $years = array_slice($years, 0, 4); 
        sort($years); // Sort Ascending for Chart Display

        // Prepare Chart.js Data
        $chartData = [
            'labels' => $departments,
            'datasets' => []
        ];

        // Add Year Columns
        $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']; 
        $i = 0;
        foreach ($years as $year) {
            $dataset = [
                'label' => $year,
                'data' => [],
                'backgroundColor' => $colors[$i % count($colors)],
                'type' => 'bar'
            ];
            foreach ($departments as $dep) {
                $dataset['data'][] = $dataPoints[$year][$dep] ?? 0;
            }
            $chartData['datasets'][] = $dataset;
            $i++;
        }

        // Add Budget Line (Optional, legacy had it)
        $budgetDataset = [
            'label' => 'งบประมาณสนับสนุน (บาท)',
            'data' => [],
            'borderColor' => '#e74a3b',
            'type' => 'line',
            'yAxisID' => 'y1' // Secondary Axis
        ];
        foreach ($departments as $dep) {
            $budgetDataset['data'][] = $budgetPoints[$dep] ?? 0;
        }
        $chartData['datasets'][] = $budgetDataset;

        // 5. Latest Data
        $latestProjects = RdbProject::where('data_show', 1)
                                    ->where('ps_id', '<>', 6)
                                    // Eager load researchers sorted by position (1,2 pivot)
                                    ->with(['rdbProjectWorks' => function($q) {
                                        $q->orderBy('position_id', 'asc') // 1, 2 first
                                          ->orderBy('ratio', 'desc');
                                    }, 'rdbProjectWorks.researcher', 'type', 'typeSub'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();

        // Stats for Top Cards
        $sumProject = RdbProject::where('year_id', $max_year->year_id)->where('data_show', 1)->count();
        $sumBudget = RdbProject::where('year_id', $max_year->year_id)->where('data_show', 1)->sum('pro_budget');
        // $countResearcher = ... (Need RdbResearcher logic, skip for now or use simple count)
        $countResearcher = \App\Models\RdbResearcher::count(); // Simple count for now

        // 6. Latest Publications
        $latestPublications = \App\Models\RdbPublished::with(['project', 'pubtype', 'authors' => function($q) {
                                        // Need to identify first author. Usually pivot pubw_main=1 or similar?
                                        // Or distinct status. User says "status name first".
                                        // If we don't know exact pivot field, we load all and filter in View.
                                        $q->withPivot('pubta_id', 'pubw_main'); 
                                    }, 'authors.prefix'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();

        // 7. Latest IP
        $latestIP = \App\Models\RdbDip::with(['dipType', 'researcher', 'researcher.prefix']) // Load researcher as inventor
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();

        // 8. Latest Utilization
        $latestUtilization = \App\Models\RdbProjectUtilize::with(['project', 'changwat'])
                                    ->orderBy('utz_date', 'desc')
                                    ->limit(5)
                                    ->get();

        return view('frontend.site.index', compact(
            'max_year', 
            'chartData', 
            'latestProjects',
            'latestPublications',
            'latestIP',
            'latestUtilization',
            'sumProject',
            'sumBudget',
            'countResearcher',
            'years_list'
        ));
    }
}
