<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\RdbProjectUtilize;
use App\Models\RdbDip;
use App\Models\RdbResearcher;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbProjectType;
use App\Models\RdbDipType;
use App\Models\RdbPublishedType;
use App\Models\RdbProjectStatus;
use App\Models\RdbPrefix;
use App\Models\RdbChangwat;

class DashboardController extends Controller
{
    /**
     * Shared context: List of years and selected year
     */
    private function getYearContext(Request $request)
    {
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $selectedYearId = $request->filled('year_id') && $request->year_id !== 'all' ? (int)$request->year_id : null;
        $selectedYear = $selectedYearId ? $years->firstWhere('year_id', $selectedYearId) : null;

        return [$years, $selectedYearId, $selectedYear];
    }

    /**
     * Shared lookups across dashboards
     */
    private function getCommonLookups()
    {
        $departments = RdbDepartment::whereIn('department_id', [2, 3, 4, 5, 6, 7, 13])
            ->orWhere('department_nameTH', 'like', '%คณะ%')
            ->orWhere('department_nameTH', 'like', '%สถาบัน%')
            ->orWhere('department_nameTH', 'like', '%สำนัก%')
            ->orderBy('department_nameTH')
            ->get();

        $statuses = RdbProjectStatus::all();
        $projectTypes = RdbProjectType::orderBy('pt_name')->get();
        $dipTypes = RdbDipType::all();
        $prefixes = RdbPrefix::orderBy('prefix_id')->get();
        $amphoes = [
            'อ.เมืองเพชรบูรณ์', 'อ.หล่มสัก', 'อ.หล่มเก่า', 'อ.วิเชียรบุรี',
            'อ.เขาค้อ', 'อ.บึงสามพัน', 'อ.หนองไผ่', 'อ.ชนแดน',
            'อ.ศรีเทพ', 'อ.วังโป่ง', 'อ.น้ำหนาว'
        ];

        return [
            'departments' => $departments,
            'statuses' => $statuses,
            'projectTypes' => $projectTypes,
            'dipTypes' => $dipTypes,
            'prefixes' => $prefixes,
            'amphoes' => $amphoes,
        ];
    }

    /**
     * 1. Executive Overview Dashboard (ภาพรวมยุทธศาสตร์)
     * Filters: year_id, department_id, ps_id, pt_id, has_report
     */
    public function index(Request $request)
    {
        [$years, $selectedYearId, $selectedYear] = $this->getYearContext($request);
        $lookups = $this->getCommonLookups();

        $deptId = $request->filled('department_id') && $request->department_id !== 'all' ? (int)$request->department_id : null;
        $psId = $request->filled('ps_id') && $request->ps_id !== 'all' ? (int)$request->ps_id : null;
        $ptId = $request->filled('pt_id') && $request->pt_id !== 'all' ? (int)$request->pt_id : null;
        $hasReport = $request->filled('has_report') && $request->has_report !== 'all' ? $request->has_report : null;

        // Project types are scoped to selected year
        if ($selectedYearId) {
            $lookups['projectTypes'] = RdbProjectType::where('year_id', $selectedYearId)->orderBy('pt_name')->get();
        } else {
            $lookups['projectTypes'] = collect();
            $ptId = null;
        }

        // Active filter counter
        $activeFilterCount = count(array_filter([$selectedYearId, $deptId, $psId, $ptId, $hasReport]));

        // Core Queries
        $proQuery = RdbProject::query();
        $pubQuery = RdbPublished::query();
        $utilQuery = RdbProjectUtilize::query();
        $dipQuery = RdbDip::query();

        // 1. Year Filter
        if ($selectedYearId) {
            $proQuery->where('year_id', $selectedYearId);
            $pubQuery->where(function ($q) use ($selectedYearId) {
                $q->where('year_id', $selectedYearId)->orWhere('year_bud', $selectedYearId);
            });
            $utilQuery->where(function ($q) use ($selectedYearId) {
                $q->where('utz_year_id', $selectedYearId)->orWhere('utz_year_bud', $selectedYearId);
            });
            if ($selectedYear) {
                $ceYear = (int)$selectedYear->year_name - 543;
                $dipQuery->where(function ($q) use ($selectedYearId, $ceYear) {
                    $q->whereHas('project', function ($pq) use ($selectedYearId) {
                        $pq->where('year_id', $selectedYearId);
                    })->orWhereYear('dip_request_date', $ceYear);
                });
            }
        }

        // 2. Department / Faculty Filter
        if ($deptId) {
            $proQuery->where('department_id', $deptId);
            $pubQuery->where('department_id', $deptId);
            $utilQuery->whereHas('project', fn($q) => $q->where('department_id', $deptId));
            $dipQuery->where(function ($q) use ($deptId) {
                $q->whereHas('project', fn($pq) => $pq->where('department_id', $deptId))
                  ->orWhereHas('researcher', fn($rq) => $rq->where('department_id', $deptId));
            });
        }

        // 3. Project Status Filter
        if ($psId) {
            $proQuery->where('ps_id', $psId);
            $utilQuery->whereHas('project', fn($q) => $q->where('ps_id', $psId));
        }

        // 4. Project Type Filter
        if ($ptId) {
            $proQuery->where('pt_id', $ptId);
            $utilQuery->whereHas('project', fn($q) => $q->where('pt_id', $ptId));
        }

        // 5. Full Report Availability Filter
        if ($hasReport !== null) {
            if ($hasReport === '1') {
                $proQuery->where(function ($q) {
                    $q->whereNotNull('pro_file_path')->where('pro_file_path', '!=', '')
                      ->orWhere(function ($sq) {
                          $sq->whereNotNull('pro_file')->where('pro_file', '!=', '');
                      });
                });
            } elseif ($hasReport === '0') {
                $proQuery->where(function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereNull('pro_file_path')->orWhere('pro_file_path', '');
                    })->where(function ($sq) {
                        $sq->whereNull('pro_file')->orWhere('pro_file', '');
                    });
                });
            }
        }

        $totalProjects = $proQuery->count();
        $totalBudget = (float) $proQuery->sum('pro_budget');
        $totalPublished = $pubQuery->count();
        $totalUtilizations = $utilQuery->count();
        $totalDips = $dipQuery->count();

        // International publications (Scopus / WoS)
        $internationalPubsCount = (clone $pubQuery)->whereHas('pubtype', function ($q) {
            $q->where('pubtype_subgroup', 'like', '%Scopus%')
              ->orWhere('pubtype_subgroup', 'like', '%Web of Science%')
              ->orWhere('pubtype_subgroup', 'like', '%PubMed%');
        })->count();

        // Active Researchers (Distinct researchers participating in projects, respecting filters)
        $resWorkQuery = DB::table('rdb_project_work')
            ->join('rdb_project', 'rdb_project_work.pro_id', '=', 'rdb_project.pro_id')
            ->where('rdb_project.data_show', 1)
            ->where(function ($q) {
                $q->whereNull('rdb_project.ps_id')
                  ->orWhere('rdb_project.ps_id', '<>', RdbProjectStatus::REFUNDED);
            })
            ->whereNotNull('rdb_project_work.researcher_id')
            ->where('rdb_project_work.researcher_id', '>', 0);

        if ($selectedYearId) {
            $resWorkQuery->where('rdb_project.year_id', $selectedYearId);
        }

        if ($deptId) {
            $resWorkQuery->where('rdb_project.department_id', $deptId);
        }

        if ($psId) {
            $resWorkQuery->where('rdb_project.ps_id', $psId);
        }

        if ($ptId) {
            $resWorkQuery->where('rdb_project.pt_id', $ptId);
        }

        if ($hasReport !== null) {
            if ($hasReport === '1') {
                $resWorkQuery->where(function ($q) {
                    $q->whereNotNull('rdb_project.pro_file_path')->where('rdb_project.pro_file_path', '!=', '')
                      ->orWhere(function ($sq) {
                          $sq->whereNotNull('rdb_project.pro_file')->where('rdb_project.pro_file', '!=', '');
                      });
                });
            } elseif ($hasReport === '0') {
                $resWorkQuery->where(function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereNull('rdb_project.pro_file_path')->orWhere('rdb_project.pro_file_path', '');
                    })->where(function ($sq) {
                        $sq->whereNull('rdb_project.pro_file')->orWhere('rdb_project.pro_file', '');
                    });
                });
            }
        }

        $totalResearchers = $resWorkQuery->distinct()->count('rdb_project_work.researcher_id');

        // Projects by Status
        $statusQuery = (clone $proQuery)->select('ps_id', DB::raw('count(*) as total'));
        $projectsByStatus = $statusQuery->groupBy('ps_id')->with('status')->get();

        // Multi-year Trend (Last 6 Years)
        $yearlyTrend = RdbYear::whereHas('projects')
            ->orderBy('year_id', 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($y) use ($deptId, $ptId) {
                $pq = RdbProject::where('year_id', $y->year_id);
                $pubq = RdbPublished::where(fn($q) => $q->where('year_id', $y->year_id)->orWhere('year_bud', $y->year_id));

                if ($deptId) {
                    $pq->where('department_id', $deptId);
                    $pubq->where('department_id', $deptId);
                }
                if ($ptId) {
                    $pq->where('pt_id', $ptId);
                }

                $count = $pq->count();
                $budget = $pq->sum('pro_budget');
                $pubs = $pubq->count();

                return [
                    'year_name' => $y->year_name,
                    'projects' => $count,
                    'budget' => (float)$budget,
                    'publications' => $pubs,
                ];
            });

        // Faculty Performance Comparison (If deptId is selected, show only that faculty; otherwise show all)
        $targetDepartments = $deptId
            ? $lookups['departments']->where('department_id', $deptId)
            : $lookups['departments'];

        $facultyStats = $targetDepartments->map(function ($dept) use ($selectedYearId, $psId, $ptId) {
            $pq = RdbProject::where('department_id', $dept->department_id);
            $pubq = RdbPublished::where('department_id', $dept->department_id);
            $uq = RdbProjectUtilize::whereHas('project', fn($q) => $q->where('department_id', $dept->department_id));

            if ($selectedYearId) {
                $pq->where('year_id', $selectedYearId);
                $pubq->where(fn($q) => $q->where('year_id', $selectedYearId)->orWhere('year_bud', $selectedYearId));
                $uq->where(fn($q) => $q->where('utz_year_id', $selectedYearId)->orWhere('utz_year_bud', $selectedYearId));
            }
            if ($psId) {
                $pq->where('ps_id', $psId);
            }
            if ($ptId) {
                $pq->where('pt_id', $ptId);
            }

            return [
                'name' => $dept->department_nameTH,
                'short_name' => str_replace(['คณะ', 'สถาบัน', 'สำนัก'], '', $dept->department_nameTH),
                'projects' => $pq->count(),
                'budget' => (float)$pq->sum('pro_budget'),
                'publications' => $pubq->count(),
                'utilizations' => $uq->count(),
            ];
        })->values();

        // Recent / Top Projects
        $recentProjects = (clone $proQuery)->with(['year', 'department', 'status'])
            ->orderBy('pro_budget', 'desc')
            ->take(6)
            ->get();

        return view('frontend.dashboard.index', array_merge($lookups, [
            'years' => $years,
            'selectedYearId' => $selectedYearId,
            'selectedYear' => $selectedYear,
            'deptId' => $deptId,
            'psId' => $psId,
            'ptId' => $ptId,
            'hasReport' => $hasReport,
            'activeFilterCount' => $activeFilterCount,
            'totalProjects' => $totalProjects,
            'totalBudget' => $totalBudget,
            'totalPublished' => $totalPublished,
            'internationalPubsCount' => $internationalPubsCount,
            'totalUtilizations' => $totalUtilizations,
            'totalDips' => $totalDips,
            'totalResearchers' => $totalResearchers,
            'projectsByStatus' => $projectsByStatus,
            'yearlyTrend' => $yearlyTrend,
            'facultyStats' => $facultyStats,
            'recentProjects' => $recentProjects,
        ]));
    }

    /**
     * 2. Research Projects & Budget Dashboard
     * Filters: year_id, department_id, ps_id, pt_id, budget_range, has_report
     */
    public function projects(Request $request)
    {
        [$years, $selectedYearId, $selectedYear] = $this->getYearContext($request);
        $lookups = $this->getCommonLookups();

        $deptId = $request->filled('department_id') && $request->department_id !== 'all' ? (int)$request->department_id : null;
        $psId = $request->filled('ps_id') && $request->ps_id !== 'all' ? (int)$request->ps_id : null;
        $ptId = $request->filled('pt_id') && $request->pt_id !== 'all' ? (int)$request->pt_id : null;
        $budgetRange = $request->filled('budget_range') && $request->budget_range !== 'all' ? $request->budget_range : null;
        $hasReport = $request->filled('has_report') && $request->has_report !== 'all' ? $request->has_report : null;

        // Project types are scoped to selected year
        if ($selectedYearId) {
            $lookups['projectTypes'] = RdbProjectType::where('year_id', $selectedYearId)->orderBy('pt_name')->get();
        } else {
            $lookups['projectTypes'] = collect();
            $ptId = null;
        }

        $activeFilterCount = count(array_filter([$selectedYearId, $deptId, $psId, $ptId, $budgetRange, $hasReport]));

        $query = RdbProject::query();

        if ($selectedYearId) {
            $query->where('year_id', $selectedYearId);
        }
        if ($deptId) {
            $query->where('department_id', $deptId);
        }
        if ($psId) {
            $query->where('ps_id', $psId);
        }
        if ($ptId) {
            $query->where('pt_id', $ptId);
        }
        if ($budgetRange) {
            if ($budgetRange === '0-50k') {
                $query->where('pro_budget', '<', 50000);
            } elseif ($budgetRange === '50k-100k') {
                $query->whereBetween('pro_budget', [50000, 100000]);
            } elseif ($budgetRange === '100k-500k') {
                $query->whereBetween('pro_budget', [100000, 500000]);
            } elseif ($budgetRange === '500k+') {
                $query->where('pro_budget', '>=', 500000);
            }
        }
        if ($hasReport !== null) {
            if ($hasReport === '1') {
                $query->where(function ($q) {
                    $q->whereNotNull('pro_file_path')->where('pro_file_path', '!=', '')
                      ->orWhere(function ($sq) {
                          $sq->whereNotNull('pro_file')->where('pro_file', '!=', '');
                      });
                });
            } elseif ($hasReport === '0') {
                $query->where(function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereNull('pro_file_path')->orWhere('pro_file_path', '');
                    })->where(function ($sq) {
                        $sq->whereNull('pro_file')->orWhere('pro_file', '');
                    });
                });
            }
        }

        $totalProjects = (clone $query)->count();
        $totalBudget = (float) (clone $query)->sum('pro_budget');
        $avgBudget = $totalProjects > 0 ? $totalBudget / $totalProjects : 0;

        // Completion & status breakdown
        $completedCount = (clone $query)->where('ps_id', 2)->count();
        $ongoingCount = (clone $query)->where('ps_id', 1)->count();
        $extendedCount = (clone $query)->whereIn('ps_id', [3, 4])->count();
        $overdueCount = (clone $query)->where('ps_id', 5)->count();

        // Full report files availability
        $fullReportCount = (clone $query)->where(function ($q) {
            $q->whereNotNull('pro_file_path')->where('pro_file_path', '!=', '')
              ->orWhere(function ($sq) {
                  $sq->whereNotNull('pro_file')->where('pro_file', '!=', '');
              });
        })->count();
        $abstractCount = (clone $query)->where(function ($q) {
            $q->whereNotNull('pro_abstract_file_path')->where('pro_abstract_file_path', '!=', '')
              ->orWhere(function ($sq) {
                  $sq->whereNotNull('pro_abstract_file')->where('pro_abstract_file', '!=', '');
              });
        })->count();
        $totalDownloads = (clone $query)->sum('pro_count_full');

        // Funding types
        $fundingTypes = DB::table('rdb_project')
            ->leftJoin('rdb_project_type', 'rdb_project.pt_id', '=', 'rdb_project_type.pt_id')
            ->when($selectedYearId, fn($q) => $q->where('rdb_project.year_id', $selectedYearId))
            ->when($deptId, fn($q) => $q->where('rdb_project.department_id', $deptId))
            ->when($psId, fn($q) => $q->where('rdb_project.ps_id', $psId))
            ->when($ptId, fn($q) => $q->where('rdb_project.pt_id', $ptId))
            ->select(
                DB::raw('COALESCE(rdb_project_type.pt_name, "ไม่ระบุ") as type_name'),
                DB::raw('count(*) as count'),
                DB::raw('SUM(pro_budget) as budget')
            )
            ->groupBy('rdb_project_type.pt_name')
            ->orderByDesc('budget')
            ->get();

        // Budget by Faculty
        $deptBudgets = DB::table('rdb_project')
            ->join('rdb_department', 'rdb_project.department_id', '=', 'rdb_department.department_id')
            ->when($selectedYearId, fn($q) => $q->where('rdb_project.year_id', $selectedYearId))
            ->when($deptId, fn($q) => $q->where('rdb_project.department_id', $deptId))
            ->when($psId, fn($q) => $q->where('rdb_project.ps_id', $psId))
            ->when($ptId, fn($q) => $q->where('rdb_project.pt_id', $ptId))
            ->select(
                'rdb_department.department_nameTH',
                DB::raw('count(*) as project_count'),
                DB::raw('SUM(pro_budget) as total_budget')
            )
            ->groupBy('rdb_department.department_nameTH')
            ->orderByDesc('total_budget')
            ->get();

        // Multi-Year Budget Trend
        $yearlyBudgetTrend = RdbYear::whereHas('projects')
            ->orderBy('year_id', 'desc')
            ->limit(8)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($y) use ($deptId, $psId, $ptId) {
                $q = RdbProject::where('year_id', $y->year_id);
                if ($deptId) $q->where('department_id', $deptId);
                if ($psId) $q->where('ps_id', $psId);
                if ($ptId) $q->where('pt_id', $ptId);

                $count = $q->count();
                $budget = (float) $q->sum('pro_budget');
                return [
                    'year_name' => $y->year_name,
                    'projects' => $count,
                    'budget' => $budget,
                    'avg' => $count > 0 ? round($budget / $count, 0) : 0,
                ];
            });

        // Top funded projects list
        $topProjects = (clone $query)->with(['department', 'year', 'status'])
            ->orderBy('pro_budget', 'desc')
            ->take(20)
            ->get();

        return view('frontend.dashboard.projects', array_merge($lookups, [
            'years' => $years,
            'selectedYearId' => $selectedYearId,
            'selectedYear' => $selectedYear,
            'deptId' => $deptId,
            'psId' => $psId,
            'ptId' => $ptId,
            'budgetRange' => $budgetRange,
            'hasReport' => $hasReport,
            'activeFilterCount' => $activeFilterCount,
            'totalProjects' => $totalProjects,
            'totalBudget' => $totalBudget,
            'avgBudget' => $avgBudget,
            'completedCount' => $completedCount,
            'ongoingCount' => $ongoingCount,
            'extendedCount' => $extendedCount,
            'overdueCount' => $overdueCount,
            'fullReportCount' => $fullReportCount,
            'abstractCount' => $abstractCount,
            'totalDownloads' => $totalDownloads,
            'fundingTypes' => $fundingTypes,
            'deptBudgets' => $deptBudgets,
            'yearlyBudgetTrend' => $yearlyBudgetTrend,
            'topProjects' => $topProjects,
        ]));
    }

    /**
     * 3. Academic Publications & Impact Dashboard
     * Filters: year_id, department_id, quality_group, has_project, has_file
     */
    public function publications(Request $request)
    {
        [$years, $selectedYearId, $selectedYear] = $this->getYearContext($request);
        $lookups = $this->getCommonLookups();

        $deptId = $request->filled('department_id') && $request->department_id !== 'all' ? (int)$request->department_id : null;
        $qualityGroup = $request->filled('quality_group') && $request->quality_group !== 'all' ? $request->quality_group : null;
        $hasProject = $request->filled('has_project') && $request->has_project !== 'all' ? $request->has_project : null;
        $hasFile = $request->filled('has_file') && $request->has_file !== 'all' ? $request->has_file : null;

        $activeFilterCount = count(array_filter([$selectedYearId, $deptId, $qualityGroup, $hasProject, $hasFile]));

        $query = RdbPublished::query();

        if ($selectedYearId) {
            $query->where(function ($q) use ($selectedYearId) {
                $q->where('year_id', $selectedYearId)->orWhere('year_bud', $selectedYearId);
            });
        }
        if ($deptId) {
            $query->where('rdb_published.department_id', $deptId);
        }
        if ($qualityGroup) {
            if ($qualityGroup === 'scopus') {
                $query->whereHas('pubtype', fn($q) => $q->where('pubtype_subgroup', 'like', '%Scopus%'));
            } elseif ($qualityGroup === 'wos') {
                $query->whereHas('pubtype', fn($q) => $q->where('pubtype_subgroup', 'like', '%Web of Science%'));
            } elseif ($qualityGroup === 'tci1') {
                $query->whereHas('pubtype', fn($q) => $q->where('pubtype_subgroup', 'like', '%TCI %1%'));
            } elseif ($qualityGroup === 'tci2') {
                $query->whereHas('pubtype', fn($q) => $q->where('pubtype_subgroup', 'like', '%TCI %2%'));
            } elseif ($qualityGroup === 'conf') {
                $query->whereHas('pubtype', fn($q) => $q->where('pubtype_group', 'like', '%ประชุม%'));
            }
        }
        if ($hasProject !== null) {
            if ($hasProject === '1') {
                $query->whereNotNull('pro_id')->where('pro_id', '>', 0);
            } elseif ($hasProject === '0') {
                $query->where(fn($q) => $q->whereNull('pro_id')->orWhere('pro_id', 0));
            }
        }
        if ($hasFile !== null) {
            if ($hasFile === '1') {
                $query->where(fn($q) => $q->whereNotNull('pub_file')->where('pub_file', '!=', ''));
            } elseif ($hasFile === '0') {
                $query->where(fn($q) => $q->whereNull('pub_file')->orWhere('pub_file', ''));
            }
        }

        $totalPublished = (clone $query)->count();

        // Quality Tiers Breakdown
        $qualityTiers = (clone $query)
            ->leftJoin('rdb_published_type', 'rdb_published.pubtype_id', '=', 'rdb_published_type.pubtype_id')
            ->select(
                DB::raw('COALESCE(rdb_published_type.pubtype_subgroup, "วารสารวิชาการทั่วไป / อื่นๆ") as tier_name'),
                DB::raw('count(*) as count')
            )
            ->groupBy('rdb_published_type.pubtype_subgroup')
            ->orderByDesc('count')
            ->get();

        // Count international (Scopus/WoS) vs TCI vs Conf
        $scopusCount = (clone $query)->whereHas('pubtype', fn($q) => $q->where('pubtype_subgroup', 'like', '%Scopus%'))->count();
        $wosCount = (clone $query)->whereHas('pubtype', fn($q) => $q->where('pubtype_subgroup', 'like', '%Web of Science%'))->count();
        $tci1Count = (clone $query)->whereHas('pubtype', fn($q) => $q->where('pubtype_subgroup', 'like', '%TCI %1%'))->count();
        $tci2Count = (clone $query)->whereHas('pubtype', fn($q) => $q->where('pubtype_subgroup', 'like', '%TCI %2%'))->count();
        $confCount = (clone $query)->whereHas('pubtype', fn($q) => $q->where('pubtype_group', 'like', '%ประชุม%'))->count();

        // Publications by Faculty
        $byFaculty = (clone $query)
            ->join('rdb_department', 'rdb_published.department_id', '=', 'rdb_department.department_id')
            ->select('rdb_department.department_nameTH', DB::raw('count(*) as count'))
            ->groupBy('rdb_department.department_nameTH')
            ->orderByDesc('count')
            ->get();

        // Top Journals
        $topJournals = (clone $query)
            ->whereNotNull('pub_name_journal')
            ->where('pub_name_journal', '!=', '')
            ->select('pub_name_journal', DB::raw('count(*) as count'))
            ->groupBy('pub_name_journal')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Linkage
        $linkedProjectCount = (clone $query)->whereNotNull('pro_id')->where('pro_id', '>', 0)->count();

        // Recent Publications
        $publicationsList = (clone $query)->with(['pubtype', 'department', 'project'])
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        return view('frontend.dashboard.publications', array_merge($lookups, [
            'years' => $years,
            'selectedYearId' => $selectedYearId,
            'selectedYear' => $selectedYear,
            'deptId' => $deptId,
            'qualityGroup' => $qualityGroup,
            'hasProject' => $hasProject,
            'hasFile' => $hasFile,
            'activeFilterCount' => $activeFilterCount,
            'totalPublished' => $totalPublished,
            'scopusCount' => $scopusCount,
            'wosCount' => $wosCount,
            'tci1Count' => $tci1Count,
            'tci2Count' => $tci2Count,
            'confCount' => $confCount,
            'qualityTiers' => $qualityTiers,
            'byFaculty' => $byFaculty,
            'topJournals' => $topJournals,
            'linkedProjectCount' => $linkedProjectCount,
            'publicationsList' => $publicationsList,
        ]));
    }

    /**
     * AJAX endpoint: Get distinct Amphoes for a given Changwat
     */
    public function getAmphoesByChangwat(Request $request)
    {
        $changwat = $request->input('changwat');
        if (!$changwat) {
            return response()->json([]);
        }

        $amphoes = RdbChangwat::where('changwat_t', $changwat)
            ->whereNotNull('amphoe_t')
            ->where('amphoe_t', '!=', '')
            ->distinct()
            ->orderBy('amphoe_t')
            ->pluck('amphoe_t');

        return response()->json($amphoes);
    }

    /**
     * 4. Geographic & Community Utilization Dashboard (GIS Map)
     * Filters: year_id, department_id, utz_pillar, changwat[], amphoe[], qa_status
     */
    public function utilizations(Request $request)
    {
        [$years, $selectedYearId, $selectedYear] = $this->getYearContext($request);
        $lookups = $this->getCommonLookups();

        $deptId = $request->filled('department_id') && $request->department_id !== 'all' ? (int)$request->department_id : null;
        $utzPillar = $request->filled('utz_pillar') && $request->utz_pillar !== 'all' ? $request->utz_pillar : null;
        $qaStatus = $request->filled('qa_status') && $request->qa_status !== 'all' ? $request->qa_status : null;

        // Multi-select Changwat (Province)
        $selectedChangwats = [];
        if ($request->filled('changwat')) {
            $raw = $request->input('changwat');
            if (is_array($raw)) {
                $selectedChangwats = array_values(array_filter($raw, fn($v) => !empty($v) && $v !== 'all'));
            } elseif (is_string($raw) && $raw !== 'all') {
                $selectedChangwats = array_values(array_filter(explode(',', $raw), fn($v) => !empty(trim($v)) && trim($v) !== 'all'));
            }
        }

        // Multi-select Amphoe (District)
        $selectedAmphoes = [];
        if ($request->filled('amphoe')) {
            $rawAmp = $request->input('amphoe');
            if (is_array($rawAmp)) {
                $selectedAmphoes = array_values(array_filter($rawAmp, fn($v) => !empty($v) && $v !== 'all'));
            } elseif (is_string($rawAmp) && $rawAmp !== 'all') {
                $selectedAmphoes = array_values(array_filter(explode(',', $rawAmp), fn($v) => !empty(trim($v)) && trim($v) !== 'all'));
            }
        }

        // Backward compatibility: If amphoe was provided directly without changwat, resolve its changwat
        if (empty($selectedChangwats) && !empty($selectedAmphoes)) {
            $foundChangwat = RdbChangwat::where('amphoe_t', $selectedAmphoes[0])->value('changwat_t');
            if ($foundChangwat) {
                $selectedChangwats = [$foundChangwat];
            }
        }

        // Amphoe selection is strictly valid only when exactly 1 province is selected
        if (count($selectedChangwats) !== 1) {
            $selectedAmphoes = [];
        }

        // Available provinces (prioritize จ.เพชรบูรณ์ first, then alphabetical)
        $allProvinces = RdbChangwat::whereNotNull('changwat_t')
            ->where('changwat_t', '!=', '')
            ->distinct()
            ->orderByRaw("CASE WHEN changwat_t = 'จ.เพชรบูรณ์' THEN 0 ELSE 1 END")
            ->orderBy('changwat_t')
            ->pluck('changwat_t');

        // Available amphoes if exactly 1 province is selected
        $availableAmphoes = collect();
        if (count($selectedChangwats) === 1) {
            $availableAmphoes = RdbChangwat::where('changwat_t', $selectedChangwats[0])
                ->whereNotNull('amphoe_t')
                ->where('amphoe_t', '!=', '')
                ->distinct()
                ->orderBy('amphoe_t')
                ->pluck('amphoe_t');
        }

        $activeFilterCount = count(array_filter([
            $selectedYearId, 
            $deptId, 
            $utzPillar, 
            !empty($selectedChangwats) ? count($selectedChangwats) : null,
            !empty($selectedAmphoes) ? count($selectedAmphoes) : null,
            $qaStatus
        ]));

        $query = RdbProjectUtilize::query();

        if ($selectedYearId) {
            $query->where(function ($q) use ($selectedYearId) {
                $q->where('utz_year_id', $selectedYearId)->orWhere('utz_year_bud', $selectedYearId);
            });
        }
        if ($deptId) {
            $query->whereHas('project', fn($q) => $q->where('department_id', $deptId));
        }

        // 5 Pillars Breakdown Filter (1: สาธารณะ, 2: นโยบาย, 3: พาณิชย์, 4: ชุมชน/พื้นที่, 5: อื่น ๆ)
        if ($utzPillar) {
            if ($utzPillar === '5') {
                $query->where(function ($q) {
                    if (DB::connection()->getDriverName() === 'sqlite') {
                        $q->where('utz_group', 'like', '%5%')
                          ->orWhere('utz_group', 'like', '%6%')
                          ->orWhere('utz_group', 'like', '%7%')
                          ->orWhere('utz_group', 'like', '%8%')
                          ->orWhere('utz_group', 'like', '%9%')
                          ->orWhere('utz_group', 'like', '%10%')
                          ->orWhere('utz_group', 'like', '%11%');
                    } else {
                        $q->whereRaw("utz_group REGEXP '(^|,)([5-9]|1[0-9])(,|$)'");
                    }
                });
            } else {
                if (DB::connection()->getDriverName() === 'sqlite') {
                    $query->whereRaw("',' || utz_group || ',' LIKE ?", ["%,{$utzPillar},%"]);
                } else {
                    $query->whereRaw("FIND_IN_SET(?, utz_group)", [$utzPillar]);
                }
            }
        }

        // Filter by Changwat & Amphoe
        if (!empty($selectedChangwats)) {
            $query->whereHas('changwat', function ($q) use ($selectedChangwats, $selectedAmphoes) {
                $q->whereIn('changwat_t', $selectedChangwats);
                if (!empty($selectedAmphoes)) {
                    $q->whereIn('amphoe_t', $selectedAmphoes);
                }
            });
        }

        if ($qaStatus !== null) {
            if ($qaStatus === '1') {
                $query->where('utz_group_qa', '1');
            } elseif ($qaStatus === '0') {
                $query->where(fn($q) => $q->whereNull('utz_group_qa')->orWhere('utz_group_qa', '!=', '1'));
            }
        }

        $totalUtilizations = (clone $query)->count();
        $totalBudget = (float) (clone $query)->sum('utz_budget');
        $qaCount = (clone $query)->where('utz_group_qa', '1')->count();

        // Distinct partner agencies
        $partnerCount = (clone $query)->whereNotNull('utz_department_name')->distinct('utz_department_name')->count('utz_department_name');

        // Districts Ranking
        $districtsRanking = (clone $query)
            ->join('rdb_changwat', 'rdb_project_utilize.chw_id', '=', 'rdb_changwat.id')
            ->select(
                'rdb_changwat.amphoe_t',
                'rdb_changwat.changwat_t',
                DB::raw('count(*) as count'),
                DB::raw('AVG(rdb_changwat.lat) as lat'),
                DB::raw('AVG(rdb_changwat.long) as lng')
            )
            ->groupBy('rdb_changwat.amphoe_t', 'rdb_changwat.changwat_t')
            ->orderByDesc('count')
            ->get();

        // 5 Pillars Breakdown
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $fivePillars = [
            'public' => (clone $query)->whereRaw($isSqlite ? "',' || utz_group || ',' LIKE '%,1,%'" : "FIND_IN_SET('1', utz_group)")->count(),
            'policy' => (clone $query)->whereRaw($isSqlite ? "',' || utz_group || ',' LIKE '%,2,%'" : "FIND_IN_SET('2', utz_group)")->count(),
            'commercial' => (clone $query)->whereRaw($isSqlite ? "',' || utz_group || ',' LIKE '%,3,%'" : "FIND_IN_SET('3', utz_group)")->count(),
            'community' => (clone $query)->whereRaw($isSqlite ? "',' || utz_group || ',' LIKE '%,4,%'" : "FIND_IN_SET('4', utz_group)")->count(),
            'other' => (clone $query)->where(function ($q) use ($isSqlite) {
                if ($isSqlite) {
                    $q->where('utz_group', 'like', '%5%')
                      ->orWhere('utz_group', 'like', '%6%')
                      ->orWhere('utz_group', 'like', '%7%')
                      ->orWhere('utz_group', 'like', '%8%')
                      ->orWhere('utz_group', 'like', '%9%')
                      ->orWhere('utz_group', 'like', '%10%')
                      ->orWhere('utz_group', 'like', '%11%');
                } else {
                    $q->whereRaw("utz_group REGEXP '(^|,)([5-9]|1[0-9])(,|$)'");
                }
            })->count(),
        ];

        // Top Partner Agencies
        $topAgencies = (clone $query)
            ->whereNotNull('utz_department_name')
            ->where('utz_department_name', '!=', '')
            ->select('utz_department_name', DB::raw('count(*) as count'))
            ->groupBy('utz_department_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Leaflet Markers Data
        $mapMarkers = (clone $query)
            ->join('rdb_changwat', 'rdb_project_utilize.chw_id', '=', 'rdb_changwat.id')
            ->whereNotNull('rdb_changwat.lat')
            ->whereNotNull('rdb_changwat.long')
            ->where('rdb_changwat.lat', '!=', '')
            ->select(
                'rdb_changwat.amphoe_t',
                'rdb_changwat.tambon_t',
                'rdb_changwat.changwat_t',
                'rdb_changwat.lat',
                'rdb_changwat.long',
                DB::raw('count(*) as count')
            )
            ->groupBy('rdb_changwat.amphoe_t', 'rdb_changwat.tambon_t', 'rdb_changwat.changwat_t', 'rdb_changwat.lat', 'rdb_changwat.long')
            ->get();

        // Recent Utilizations List
        $utilizationsList = (clone $query)->with(['project.department', 'changwat'])
            ->orderBy('utz_id', 'desc')
            ->take(20)
            ->get();

        return view('frontend.dashboard.utilizations', array_merge($lookups, [
            'years' => $years,
            'selectedYearId' => $selectedYearId,
            'selectedYear' => $selectedYear,
            'deptId' => $deptId,
            'utzPillar' => $utzPillar,
            'selectedChangwats' => $selectedChangwats,
            'selectedAmphoes' => $selectedAmphoes,
            'allProvinces' => $allProvinces,
            'availableAmphoes' => $availableAmphoes,
            'amphoe' => count($selectedAmphoes) === 1 ? $selectedAmphoes[0] : null,
            'qaStatus' => $qaStatus,
            'activeFilterCount' => $activeFilterCount,
            'totalUtilizations' => $totalUtilizations,
            'totalBudget' => $totalBudget,
            'qaCount' => $qaCount,
            'partnerCount' => $partnerCount,
            'districtsRanking' => $districtsRanking,
            'fivePillars' => $fivePillars,
            'fourPillars' => $fivePillars,
            'topAgencies' => $topAgencies,
            'mapMarkers' => $mapMarkers,
            'utilizationsList' => $utilizationsList,
        ]));
    }

    /**
     * 5. Intellectual Property & Innovation Dashboard
     * Filters: year_id, department_id, dipt_id, reg_status, has_file
     */
    public function ip(Request $request)
    {
        [$years, $selectedYearId, $selectedYear] = $this->getYearContext($request);
        $lookups = $this->getCommonLookups();

        $deptId = $request->filled('department_id') && $request->department_id !== 'all' ? (int)$request->department_id : null;
        $diptId = $request->filled('dipt_id') && $request->dipt_id !== 'all' ? (int)$request->dipt_id : null;
        $regStatus = $request->filled('reg_status') && $request->reg_status !== 'all' ? $request->reg_status : null;
        $hasFile = $request->filled('has_file') && $request->has_file !== 'all' ? $request->has_file : null;

        $activeFilterCount = count(array_filter([$selectedYearId, $deptId, $diptId, $regStatus, $hasFile]));

        $query = RdbDip::query();

        if ($selectedYearId && $selectedYear) {
            $ceYear = (int)$selectedYear->year_name - 543;
            $query->where(function ($q) use ($selectedYearId, $ceYear) {
                $q->whereHas('project', function ($pq) use ($selectedYearId) {
                    $pq->where('year_id', $selectedYearId);
                })->orWhereYear('dip_request_date', $ceYear);
            });
        }
        if ($deptId) {
            $query->where(function ($q) use ($deptId) {
                $q->whereHas('project', fn($pq) => $pq->where('department_id', $deptId))
                  ->orWhereHas('researcher', fn($rq) => $rq->where('department_id', $deptId));
            });
        }
        if ($diptId) {
            $query->where('rdb_dip.dipt_id', $diptId);
        }
        if ($regStatus) {
            if ($regStatus === 'registered') {
                $query->whereNotNull('dip_patent_number')->where('dip_patent_number', '!=', '');
            } elseif ($regStatus === 'pending') {
                $query->where(fn($q) => $q->whereNull('dip_patent_number')->orWhere('dip_patent_number', ''));
            }
        }
        if ($hasFile !== null) {
            if ($hasFile === '1') {
                $query->where(function ($q) {
                    $q->whereNotNull('dip_path')->where('dip_path', '!=', '')
                      ->orWhere(fn($sq) => $sq->whereNotNull('dip_files')->where('dip_files', '!=', ''));
                });
            } elseif ($hasFile === '0') {
                $query->where(function ($q) {
                    $q->where(fn($sq) => $sq->whereNull('dip_path')->orWhere('dip_path', ''))
                      ->where(fn($sq) => $sq->whereNull('dip_files')->orWhere('dip_files', ''));
                });
            }
        }

        $totalDips = (clone $query)->count();

        // Categories count
        $patentsCount = (clone $query)->where('rdb_dip.dipt_id', 1)->count();
        $pettyPatentsCount = (clone $query)->where('rdb_dip.dipt_id', 2)->count();
        $copyrightsCount = (clone $query)->where('rdb_dip.dipt_id', 3)->count();

        // Registered vs Pending
        $registeredCount = (clone $query)->whereNotNull('dip_patent_number')->where('dip_patent_number', '!=', '')->count();
        $pendingCount = $totalDips - $registeredCount;

        // By Type
        $byType = (clone $query)
            ->leftJoin('rdb_dip_type', 'rdb_dip.dipt_id', '=', 'rdb_dip_type.dipt_id')
            ->select('rdb_dip_type.dipt_name', DB::raw('count(*) as count'))
            ->groupBy('rdb_dip_type.dipt_name')
            ->orderByDesc('count')
            ->get();

        // IP by Faculty
        $byFaculty = (clone $query)
            ->join('rdb_researcher', 'rdb_dip.researcher_id', '=', 'rdb_researcher.researcher_id')
            ->join('rdb_department', 'rdb_researcher.department_id', '=', 'rdb_department.department_id')
            ->select('rdb_department.department_nameTH', DB::raw('count(*) as count'))
            ->groupBy('rdb_department.department_nameTH')
            ->orderByDesc('count')
            ->get();

        // Recent IP List
        $ipList = (clone $query)->with(['dipType', 'project', 'researcher.department'])
            ->orderBy('dip_id', 'desc')
            ->take(30)
            ->get();

        return view('frontend.dashboard.ip', array_merge($lookups, [
            'years' => $years,
            'selectedYearId' => $selectedYearId,
            'selectedYear' => $selectedYear,
            'deptId' => $deptId,
            'diptId' => $diptId,
            'regStatus' => $regStatus,
            'hasFile' => $hasFile,
            'activeFilterCount' => $activeFilterCount,
            'totalDips' => $totalDips,
            'patentsCount' => $patentsCount,
            'pettyPatentsCount' => $pettyPatentsCount,
            'copyrightsCount' => $copyrightsCount,
            'registeredCount' => $registeredCount,
            'pendingCount' => $pendingCount,
            'byType' => $byType,
            'byFaculty' => $byFaculty,
            'ipList' => $ipList,
        ]));
    }

    /**
     * 6. Researcher Talent & Faculty Matrix Dashboard
     * Filters: department_id, prefix_id, profile_tier, has_projects, has_publications
     */
    public function researchers(Request $request)
    {
        [$years, $selectedYearId, $selectedYear] = $this->getYearContext($request);
        $lookups = $this->getCommonLookups();

        $deptId = $request->filled('department_id') && $request->department_id !== 'all' ? (int)$request->department_id : null;
        $prefixId = $request->filled('prefix_id') && $request->prefix_id !== 'all' ? (int)$request->prefix_id : null;
        $profileTier = $request->filled('profile_tier') && $request->profile_tier !== 'all' ? $request->profile_tier : null;
        $hasProjects = $request->filled('has_projects') && $request->has_projects !== 'all' ? $request->has_projects : null;
        $hasPublications = $request->filled('has_publications') && $request->has_publications !== 'all' ? $request->has_publications : null;

        $activeFilterCount = count(array_filter([$deptId, $prefixId, $profileTier, $hasProjects, $hasPublications]));

        $query = RdbResearcher::query();

        if ($deptId) {
            $query->where('rdb_researcher.department_id', $deptId);
        }
        if ($prefixId) {
            $query->where('rdb_researcher.prefix_id', $prefixId);
        }
        if ($profileTier) {
            if ($profileTier === 'both') {
                $query->whereNotNull('scopus_authorId')->where('scopus_authorId', '!=', '')
                      ->whereNotNull('orcid')->where('orcid', '!=', '');
            } elseif ($profileTier === 'scopus') {
                $query->whereNotNull('scopus_authorId')->where('scopus_authorId', '!=', '');
            } elseif ($profileTier === 'orcid') {
                $query->whereNotNull('orcid')->where('orcid', '!=', '');
            }
        }
        if ($hasProjects !== null) {
            if ($hasProjects === '1') {
                $query->whereHas('rdbProjects');
            } elseif ($hasProjects === '0') {
                $query->whereDoesntHave('rdbProjects');
            }
        }
        if ($hasPublications !== null) {
            if ($hasPublications === '1') {
                $query->whereHas('rdbPublisheds');
            } elseif ($hasPublications === '0') {
                $query->whereDoesntHave('rdbPublisheds');
            }
        }

        $totalResearchers = (clone $query)->count();
        $scopusProfileCount = (clone $query)->whereNotNull('scopus_authorId')->where('scopus_authorId', '!=', '')->count();
        $orcidProfileCount = (clone $query)->whereNotNull('orcid')->where('orcid', '!=', '')->count();

        // Researchers by Faculty
        $researchersByFaculty = (clone $query)
            ->join('rdb_department', 'rdb_researcher.department_id', '=', 'rdb_department.department_id')
            ->select('rdb_department.department_nameTH', DB::raw('count(*) as count'))
            ->groupBy('rdb_department.department_nameTH')
            ->orderByDesc('count')
            ->get();

        // Leaderboard: Top by Projects
        $topByProjectsQuery = DB::table('rdb_project_work')
            ->join('rdb_researcher', 'rdb_project_work.researcher_id', '=', 'rdb_researcher.researcher_id')
            ->leftJoin('rdb_prefix', 'rdb_researcher.prefix_id', '=', 'rdb_prefix.prefix_id')
            ->leftJoin('rdb_department', 'rdb_researcher.department_id', '=', 'rdb_department.department_id');
        if ($deptId) $topByProjectsQuery->where('rdb_researcher.department_id', $deptId);
        if ($prefixId) $topByProjectsQuery->where('rdb_researcher.prefix_id', $prefixId);

        $topByProjects = $topByProjectsQuery->select(
                'rdb_researcher.researcher_id',
                'rdb_prefix.prefix_nameTH',
                'rdb_researcher.researcher_fname',
                'rdb_researcher.researcher_lname',
                'rdb_department.department_nameTH',
                DB::raw('count(rdb_project_work.pro_id) as project_count')
            )
            ->groupBy(
                'rdb_researcher.researcher_id',
                'rdb_prefix.prefix_nameTH',
                'rdb_researcher.researcher_fname',
                'rdb_researcher.researcher_lname',
                'rdb_department.department_nameTH'
            )
            ->orderByDesc('project_count')
            ->limit(10)
            ->get();

        // Leaderboard: Top by Publications
        $topByPubsQuery = DB::table('rdb_published_work')
            ->join('rdb_researcher', 'rdb_published_work.researcher_id', '=', 'rdb_researcher.researcher_id')
            ->leftJoin('rdb_prefix', 'rdb_researcher.prefix_id', '=', 'rdb_prefix.prefix_id')
            ->leftJoin('rdb_department', 'rdb_researcher.department_id', '=', 'rdb_department.department_id');
        if ($deptId) $topByPubsQuery->where('rdb_researcher.department_id', $deptId);
        if ($prefixId) $topByPubsQuery->where('rdb_researcher.prefix_id', $prefixId);

        $topByPubs = $topByPubsQuery->select(
                'rdb_researcher.researcher_id',
                'rdb_prefix.prefix_nameTH',
                'rdb_researcher.researcher_fname',
                'rdb_researcher.researcher_lname',
                'rdb_department.department_nameTH',
                DB::raw('count(rdb_published_work.published_id) as pub_count')
            )
            ->groupBy(
                'rdb_researcher.researcher_id',
                'rdb_prefix.prefix_nameTH',
                'rdb_researcher.researcher_fname',
                'rdb_researcher.researcher_lname',
                'rdb_department.department_nameTH'
            )
            ->orderByDesc('pub_count')
            ->limit(10)
            ->get();

        // Academic Titles breakdown
        $academicTitles = (clone $query)
            ->leftJoin('rdb_prefix', 'rdb_researcher.prefix_id', '=', 'rdb_prefix.prefix_id')
            ->select(
                DB::raw('COALESCE(rdb_prefix.prefix_nameTH, "ไม่ระบุ") as title_name'),
                DB::raw('count(*) as count')
            )
            ->groupBy('rdb_prefix.prefix_nameTH')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        // Active Researchers List
        $researchersList = (clone $query)->with(['prefix', 'department', 'status'])
            ->withCount(['rdbProjects', 'rdbPublisheds'])
            ->orderByDesc('rdb_projects_count')
            ->take(20)
            ->get();

        return view('frontend.dashboard.researchers', array_merge($lookups, [
            'years' => $years,
            'selectedYearId' => $selectedYearId,
            'selectedYear' => $selectedYear,
            'deptId' => $deptId,
            'prefixId' => $prefixId,
            'profileTier' => $profileTier,
            'hasProjects' => $hasProjects,
            'hasPublications' => $hasPublications,
            'activeFilterCount' => $activeFilterCount,
            'totalResearchers' => $totalResearchers,
            'scopusProfileCount' => $scopusProfileCount,
            'orcidProfileCount' => $orcidProfileCount,
            'researchersByFaculty' => $researchersByFaculty,
            'topByProjects' => $topByProjects,
            'topByPubs' => $topByPubs,
            'academicTitles' => $academicTitles,
            'researchersList' => $researchersList,
        ]));
    }
}
