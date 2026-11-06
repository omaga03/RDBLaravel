<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\RdbDip;
use App\Models\RdbProjectUtilize;
use App\Models\RdbResearcher;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbProjectType;

class ExecutiveReportService
{
    /**
     * Get list of fiscal years for filtering
     */
    public function getAvailableYears()
    {
        return RdbYear::orderBy('year_name', 'desc')->get();
    }

    /**
     * Get main faculties / departments for filtering
     */
    public function getFaculties()
    {
        return RdbDepartment::whereIn('department_id', [2, 3, 4, 5, 6, 7, 13])
            ->orWhere('department_nameTH', 'like', '%คณะ%')
            ->orWhere('department_nameTH', 'like', '%สถาบัน%')
            ->orWhere('department_nameTH', 'like', '%สำนัก%')
            ->orderBy('department_nameTH')
            ->get();
    }

    /**
     * Build comprehensive 5-dimension executive report data
     */
    public function getReportData(?int $yearId = null, ?int $deptId = null): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        // 1. Base Query Builders
        $projectQ = RdbProject::where('data_show', '1');
        $publishedQ = RdbPublished::where('data_show', '1');
        $dipQ = RdbDip::where('data_show', '1');
        $utilQ = RdbProjectUtilize::where('data_show', '1');

        if ($yearId) {
            $projectQ->where('year_id', $yearId);
            $publishedQ->where(function ($q) use ($yearId) {
                $q->where('year_id', $yearId)
                  ->orWhere('year_bud', $yearId)
                  ->orWhere('year_edu', $yearId);
            });
            $dipQ->whereHas('project', function ($q) use ($yearId) {
                $q->where('year_id', $yearId);
            });
            $utilQ->where(function ($q) use ($yearId) {
                $q->where('utz_year_id', $yearId)
                  ->orWhere('utz_year_bud', $yearId);
            });
        }

        if ($deptId) {
            $projectQ->where('department_id', $deptId);
            $publishedQ->where('department_id', $deptId);
            $dipQ->whereHas('project', function ($q) use ($deptId) {
                $q->where('department_id', $deptId);
            });
            $utilQ->whereHas('project', function ($q) use ($deptId) {
                $q->where('department_id', $deptId);
            });
        }

        // 2. High-Level Executive KPI Strip
        $totalProjects = (clone $projectQ)->count();
        $totalBudget = (float) (clone $projectQ)->sum('pro_budget');
        $totalPublished = (clone $publishedQ)->count();
        $totalDips = (clone $dipQ)->count();
        $totalUtilizations = (clone $utilQ)->count();

        // Active Researchers
        $projectIds = (clone $projectQ)->pluck('pro_id');
        $activeProjectResearchers = DB::table('rdb_project_work')
            ->whereIn('pro_id', $projectIds)
            ->pluck('researcher_id')
            ->filter()
            ->unique();
        $activePubResearchers = (clone $publishedQ)->pluck('researcher_id')->filter()->unique();
        $totalActiveResearchers = $activeProjectResearchers->merge($activePubResearchers)->unique()->count();

        // 3. Dimension: Budget by Project Type / Fund Source
        $budgetByTypeRaw = (clone $projectQ)
            ->select('pt_id', DB::raw('count(*) as count'), DB::raw('sum(pro_budget) as total_budget'))
            ->groupBy('pt_id')
            ->orderByDesc('total_budget')
            ->get();

        $projectTypes = RdbProjectType::all()->keyBy('pt_id');
        $budgetByType = [];
        foreach ($budgetByTypeRaw as $row) {
            $pt = $projectTypes->get($row->pt_id);
            $name = $pt ? $pt->pt_name : 'ไม่ระบุประเภททุน';
            $budget = (float) $row->total_budget;
            $percent = $totalBudget > 0 ? round(($budget / $totalBudget) * 100, 1) : 0;
            $budgetByType[] = [
                'pt_id' => $row->pt_id,
                'name' => $name,
                'count' => $row->count,
                'budget' => $budget,
                'percentage' => $percent,
            ];
        }

        // 4. Dimension: Budget & Output by Faculty
        $departments = RdbDepartment::all()->keyBy('department_id');
        $byFacultyRaw = (clone $projectQ)
            ->select('department_id', DB::raw('count(*) as count'), DB::raw('sum(pro_budget) as total_budget'))
            ->groupBy('department_id')
            ->orderByDesc('total_budget')
            ->get();

        $byFaculty = [];
        foreach ($byFacultyRaw as $row) {
            $dept = $departments->get($row->department_id);
            $name = $dept ? ($dept->department_nameTH ?? $dept->department_name) : 'ไม่ระบุคณะ/หน่วยงาน';
            $budget = (float) $row->total_budget;
            $percent = $totalBudget > 0 ? round(($budget / $totalBudget) * 100, 1) : 0;
            $byFaculty[] = [
                'department_id' => $row->department_id,
                'name' => $name,
                'count' => $row->count,
                'budget' => $budget,
                'percentage' => $percent,
            ];
        }

        // 5. Dimension: Publication Quality & Quartiles
        $pubTypesRaw = (clone $publishedQ)
            ->join('rdb_published_type', 'rdb_published.pubtype_id', '=', 'rdb_published_type.pubtype_id')
            ->select('rdb_published_type.pubtype_subgroup', 'rdb_published_type.pubtype_grouptype', DB::raw('count(*) as count'))
            ->groupBy('rdb_published_type.pubtype_subgroup', 'rdb_published_type.pubtype_grouptype')
            ->get();

        $scopusQ1 = 0;
        $scopusQ2 = 0;
        $scopusQ3 = 0;
        $scopusQ4 = 0;
        $tciTier1 = 0;
        $tciTier2 = 0;
        $internationalConf = 0;
        $nationalConf = 0;
        $otherPubs = 0;

        foreach ($pubTypesRaw as $row) {
            $sub = mb_strtolower($row->pubtype_subgroup ?? '');
            $cnt = $row->count;

            if (str_contains($sub, 'q1')) {
                $scopusQ1 += $cnt;
            } elseif (str_contains($sub, 'q2')) {
                $scopusQ2 += $cnt;
            } elseif (str_contains($sub, 'q3')) {
                $scopusQ3 += $cnt;
            } elseif (str_contains($sub, 'q4')) {
                $scopusQ4 += $cnt;
            } elseif (str_contains($sub, 'กลุ่มที่ 1') || str_contains($sub, 'tier 1')) {
                $tciTier1 += $cnt;
            } elseif (str_contains($sub, 'กลุ่มที่ 2') || str_contains($sub, 'tier 2')) {
                $tciTier2 += $cnt;
            } elseif (str_contains($sub, 'นานาชาติ') || str_contains($sub, 'international')) {
                $internationalConf += $cnt;
            } elseif (str_contains($sub, 'ระดับชาติ') || str_contains($sub, 'national')) {
                $nationalConf += $cnt;
            } else {
                $otherPubs += $cnt;
            }
        }

        $totalScopus = $scopusQ1 + $scopusQ2 + $scopusQ3 + $scopusQ4;
        $totalTci = $tciTier1 + $tciTier2;

        // 6. Dimension: Utilizations (4 Pillars)
        $utilPillars = [
            'commercial' => (clone $utilQ)->where(function ($q) use ($isSqlite) {
                if ($isSqlite) {
                    $q->whereRaw("',' || utz_group || ',' LIKE '%,1,%'");
                } else {
                    $q->whereRaw("FIND_IN_SET('1', utz_group)");
                }
            })->count(),
            'policy' => (clone $utilQ)->where(function ($q) use ($isSqlite) {
                if ($isSqlite) {
                    $q->whereRaw("',' || utz_group || ',' LIKE '%,2,%'");
                } else {
                    $q->whereRaw("FIND_IN_SET('2', utz_group)");
                }
            })->count(),
            'public' => (clone $utilQ)->where(function ($q) use ($isSqlite) {
                if ($isSqlite) {
                    $q->whereRaw("',' || utz_group || ',' LIKE '%,3,%'");
                } else {
                    $q->whereRaw("FIND_IN_SET('3', utz_group)");
                }
            })->count(),
            'community' => (clone $utilQ)->where(function ($q) use ($isSqlite) {
                if ($isSqlite) {
                    $q->whereRaw("',' || utz_group || ',' LIKE '%,4,%'");
                } else {
                    $q->whereRaw("FIND_IN_SET('4', utz_group)");
                }
            })->count(),
        ];

        // 7. Top Highlights
        $topProjects = (clone $projectQ)
            ->with(['rdbProjectWorks.researcher', 'department', 'year'])
            ->orderByDesc('pro_budget')
            ->take(5)
            ->get();

        $topPublications = (clone $publishedQ)
            ->with(['researcher', 'department', 'pubtype', 'year'])
            ->orderByDesc('id')
            ->take(5)
            ->get();

        // Selected Context
        $selectedYear = $yearId ? RdbYear::find($yearId) : null;
        $selectedDept = $deptId ? RdbDepartment::find($deptId) : null;

        return [
            'kpis' => [
                'total_projects' => $totalProjects,
                'total_budget' => $totalBudget,
                'total_published' => $totalPublished,
                'total_dips' => $totalDips,
                'total_utilizations' => $totalUtilizations,
                'active_researchers' => $totalActiveResearchers,
            ],
            'budget_by_type' => $budgetByType,
            'budget_by_faculty' => $byFaculty,
            'publication_quality' => [
                'scopus_q1' => $scopusQ1,
                'scopus_q2' => $scopusQ2,
                'scopus_q3' => $scopusQ3,
                'scopus_q4' => $scopusQ4,
                'total_scopus' => $totalScopus,
                'tci_tier1' => $tciTier1,
                'tci_tier2' => $tciTier2,
                'total_tci' => $totalTci,
                'international_conf' => $internationalConf,
                'national_conf' => $nationalConf,
                'other_pubs' => $otherPubs,
            ],
            'utilization_pillars' => $utilPillars,
            'top_projects' => $topProjects,
            'top_publications' => $topPublications,
            'selected_year' => $selectedYear,
            'selected_department' => $selectedDept,
            'filter_year_id' => $yearId,
            'filter_dept_id' => $deptId,
        ];
    }
}
