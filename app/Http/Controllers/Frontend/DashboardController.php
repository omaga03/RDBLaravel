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
use App\Models\RdbProjectTypeMaster;
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
        $masterProjectTypes = RdbProjectTypeMaster::orderBy('order_index')->get();
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
            'masterProjectTypes' => $masterProjectTypes,
            'dipTypes' => $dipTypes,
            'prefixes' => $prefixes,
            'amphoes' => $amphoes,
        ];
    }

    /**
     * Compute YoY Delta metrics (prev, diff, pct) with small base support
     */
    protected function computeMetricYoY($current, $prev)
    {
        $diff = $current - $prev;
        $pct = $prev > 0 ? round(($diff / $prev) * 100, 1) : null;
        return [
            'prev' => $prev,
            'diff' => $diff,
            'pct'  => $pct,
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
        $masterPtId = $request->filled('master_pt_id') && $request->master_pt_id !== 'all' ? (int)$request->master_pt_id : null;
        $ptId = $request->filled('pt_id') && $request->pt_id !== 'all' ? (int)$request->pt_id : null;
        $hasReport = $request->filled('has_report') && $request->has_report !== 'all' ? $request->has_report : null;

        // Project types are scoped to selected year
        if ($selectedYearId) {
            $lookups['projectTypes'] = RdbProjectType::where('year_id', $selectedYearId)
                ->when($masterPtId, fn($q) => $q->where('master_pt_id', $masterPtId))
                ->orderBy('pt_name')
                ->get();
        } else {
            $lookups['projectTypes'] = collect();
            $ptId = null;
        }

        // Active filter counter
        $activeFilterCount = count(array_filter([$selectedYearId, $deptId, $psId, $masterPtId, $ptId, $hasReport]));

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
        if ($masterPtId) {
            $proQuery->whereHas('type', fn($q) => $q->where('master_pt_id', $masterPtId));
            $utilQuery->whereHas('project.type', fn($q) => $q->where('master_pt_id', $masterPtId));
        }
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
        $internationalPubsCount = (clone $pubQuery)->where(function ($q) {
            $q->whereHas('pubtype', function ($sq) {
                $sq->where('pubtype_subgroup', 'like', '%Scopus%')
                  ->orWhere('pubtype_subgroup', 'like', '%Web of Science%')
                  ->orWhere('pubtype_subgroup', 'like', '%PubMed%');
            })->orWhere(function ($sq) {
                $sq->where(fn($ssq) => $ssq->whereNull('pubtype_id')->orWhere('pubtype_id', ''))
                   ->where(fn($ssq) => $ssq->where('pub_score', '>=', 1.0)
                                           ->orWhere('pub_name_journal', 'like', '%Scopus%')
                                           ->orWhere('pub_name_journal', 'like', '%Web of Science%')
                                           ->orWhere('pub_name_journal', 'like', '%WoS%'));
            });
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

        if ($masterPtId) {
            $resWorkQuery->whereHas('project.type', fn($q) => $q->where('master_pt_id', $masterPtId));
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
            ->map(function ($y) use ($deptId, $masterPtId, $ptId) {
                $pq = RdbProject::where('year_id', $y->year_id);
                $pubq = RdbPublished::where(fn($q) => $q->where('year_id', $y->year_id)->orWhere('year_bud', $y->year_id));

                if ($deptId) {
                    $pq->where('department_id', $deptId);
                    $pubq->where('department_id', $deptId);
                }
                if ($masterPtId) {
                    $pq->whereHas('type', fn($q) => $q->where('master_pt_id', $masterPtId));
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

        $facultyStats = $targetDepartments->map(function ($dept) use ($selectedYearId, $psId, $masterPtId, $ptId) {
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
            if ($masterPtId) {
                $pq->whereHas('type', fn($q) => $q->where('master_pt_id', $masterPtId));
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
        $recentProjects = (clone $proQuery)->with([
            'year',
            'department',
            'status',
            'type.masterType',
            'rdbProjectWorks.researcher.prefix',
        ])
            ->orderBy('pro_budget', 'desc')
            ->take(6)
            ->get();

        // YoY Delta calculation
        $prevYear = null;
        $prevTotalProjects = 0;
        $prevTotalBudget = 0.0;
        $prevTotalPublished = 0;
        $prevTotalResearchers = 0;
        $prevTotalDips = 0;
        $prevTotalUtilizations = 0;

        if ($selectedYear) {
            $prevYear = $years->first(fn($y) => (int)$y->year_name < (int)$selectedYear->year_name);
            if ($prevYear) {
                $prevProQ = RdbProject::where('year_id', $prevYear->year_id);
                $prevPubQ = RdbPublished::where(fn($q) => $q->where('year_id', $prevYear->year_id)->orWhere('year_bud', $prevYear->year_id));
                $prevUtilQ = RdbProjectUtilize::where(fn($q) => $q->where('utz_year_id', $prevYear->year_id)->orWhere('utz_year_bud', $prevYear->year_id));

                $prevCeYear = (int)$prevYear->year_name - 543;
                $prevDipQ = RdbDip::where(function ($q) use ($prevYear, $prevCeYear) {
                    $q->whereHas('project', function ($pq) use ($prevYear) {
                        $pq->where('year_id', $prevYear->year_id);
                    })->orWhereYear('dip_request_date', $prevCeYear);
                });

                if ($deptId) {
                    $prevProQ->where('department_id', $deptId);
                    $prevPubQ->where('department_id', $deptId);
                    $prevUtilQ->whereHas('project', fn($q) => $q->where('department_id', $deptId));
                    $prevDipQ->where(function ($q) use ($deptId) {
                        $q->whereHas('project', fn($pq) => $pq->where('department_id', $deptId))
                          ->orWhereHas('researcher', fn($rq) => $rq->where('department_id', $deptId));
                    });
                }
                if ($psId) {
                    $prevProQ->where('ps_id', $psId);
                    $prevUtilQ->whereHas('project', fn($q) => $q->where('ps_id', $psId));
                }
                if ($masterPtId) {
                    $prevProQ->whereHas('type', fn($q) => $q->where('master_pt_id', $masterPtId));
                    $prevUtilQ->whereHas('project.type', fn($q) => $q->where('master_pt_id', $masterPtId));
                }
                if ($ptId) {
                    $prevProQ->where('pt_id', $ptId);
                    $prevUtilQ->whereHas('project', fn($q) => $q->where('pt_id', $ptId));
                }

                $prevTotalProjects = $prevProQ->count();
                $prevTotalBudget = (float) $prevProQ->sum('pro_budget');
                $prevTotalPublished = $prevPubQ->count();
                $prevTotalUtilizations = $prevUtilQ->count();
                $prevTotalDips = $prevDipQ->count();

                // Prev Researchers
                $prevResWorkQ = DB::table('rdb_project_work')
                    ->join('rdb_project', 'rdb_project_work.pro_id', '=', 'rdb_project.pro_id')
                    ->where('rdb_project.data_show', 1)
                    ->where(function ($q) {
                        $q->whereNull('rdb_project.ps_id')
                          ->orWhere('rdb_project.ps_id', '<>', RdbProjectStatus::REFUNDED);
                    })
                    ->where('rdb_project.year_id', $prevYear->year_id)
                    ->whereNotNull('rdb_project_work.researcher_id')
                    ->where('rdb_project_work.researcher_id', '>', 0);
                if ($deptId) {
                    $prevResWorkQ->where('rdb_project.department_id', $deptId);
                }
                if ($psId) {
                    $prevResWorkQ->where('rdb_project.ps_id', $psId);
                }
                $prevTotalResearchers = $prevResWorkQ->distinct()->count('rdb_project_work.researcher_id');
            }
        }

        $yoyDelta = [
            'prev_year_name' => $prevYear?->year_name,
            'project_prev' => $prevTotalProjects,
            'project_diff' => $totalProjects - $prevTotalProjects,
            'project_pct' => $prevTotalProjects > 0 ? round((($totalProjects - $prevTotalProjects) / $prevTotalProjects) * 100, 1) : null,
            'budget_prev' => $prevTotalBudget,
            'budget_diff' => $totalBudget - $prevTotalBudget,
            'budget_pct' => $prevTotalBudget > 0 ? round((($totalBudget - $prevTotalBudget) / $prevTotalBudget) * 100, 1) : null,
            'published_prev' => $prevTotalPublished,
            'published_diff' => $totalPublished - $prevTotalPublished,
            'published_pct' => $prevTotalPublished > 0 ? round((($totalPublished - $prevTotalPublished) / $prevTotalPublished) * 100, 1) : null,
            'researcher_prev' => $prevTotalResearchers,
            'researcher_diff' => $totalResearchers - $prevTotalResearchers,
            'researcher_pct' => $prevTotalResearchers > 0 ? round((($totalResearchers - $prevTotalResearchers) / $prevTotalResearchers) * 100, 1) : null,
            'dip_prev' => $prevTotalDips,
            'dip_diff' => $totalDips - $prevTotalDips,
            'dip_pct' => $prevTotalDips > 0 ? round((($totalDips - $prevTotalDips) / $prevTotalDips) * 100, 1) : null,
            'utilize_prev' => $prevTotalUtilizations,
            'utilize_diff' => $totalUtilizations - $prevTotalUtilizations,
            'utilize_pct' => $prevTotalUtilizations > 0 ? round((($totalUtilizations - $prevTotalUtilizations) / $prevTotalUtilizations) * 100, 1) : null,
        ];

        // Ranked Status Breakdown for Share Bars
        $statusRankings = [];
        foreach ($projectsByStatus as $st) {
            if ($st->status) {
                $statusRankings[] = [
                    'name' => $st->status->ps_name,
                    'color' => $st->status->ps_color ?? '#2563EB',
                    'count' => (int) $st->total,
                ];
            }
        }
        usort($statusRankings, fn($a, $b) => $b['count'] <=> $a['count']);

        return view('frontend.dashboard.index', array_merge($lookups, [
            'years' => $years,
            'selectedYearId' => $selectedYearId,
            'selectedYear' => $selectedYear,
            'deptId' => $deptId,
            'psId' => $psId,
            'masterPtId' => $masterPtId,
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
            'statusRankings' => $statusRankings,
            'yoyDelta' => $yoyDelta,
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
        $masterPtId = $request->filled('master_pt_id') && $request->master_pt_id !== 'all' ? (int)$request->master_pt_id : null;
        $ptId = $request->filled('pt_id') && $request->pt_id !== 'all' ? (int)$request->pt_id : null;
        $budgetRange = $request->filled('budget_range') && $request->budget_range !== 'all' ? $request->budget_range : null;
        $hasReport = $request->filled('has_report') && $request->has_report !== 'all' ? $request->has_report : null;

        // Project types are scoped to selected year
        if ($selectedYearId) {
            $lookups['projectTypes'] = RdbProjectType::where('year_id', $selectedYearId)
                ->when($masterPtId, fn($q) => $q->where('master_pt_id', $masterPtId))
                ->orderBy('pt_name')
                ->get();
        } else {
            $lookups['projectTypes'] = collect();
            $ptId = null;
        }

        $activeFilterCount = count(array_filter([$selectedYearId, $deptId, $psId, $masterPtId, $ptId, $budgetRange, $hasReport]));

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
        if ($masterPtId) {
            $query->whereHas('type', fn($q) => $q->where('master_pt_id', $masterPtId));
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

        // Funding types - Aggregated by Master Type
        $fundingTypes = DB::table('rdb_project')
            ->leftJoin('rdb_project_types', 'rdb_project.pt_id', '=', 'rdb_project_types.pt_id')
            ->leftJoin('rdb_project_types_master', 'rdb_project_types.master_pt_id', '=', 'rdb_project_types_master.master_pt_id')
            ->when($selectedYearId, fn($q) => $q->where('rdb_project.year_id', $selectedYearId))
            ->when($deptId, fn($q) => $q->where('rdb_project.department_id', $deptId))
            ->when($psId, fn($q) => $q->where('rdb_project.ps_id', $psId))
            ->when($masterPtId, fn($q) => $q->where('rdb_project_types.master_pt_id', $masterPtId))
            ->when($ptId, fn($q) => $q->where('rdb_project.pt_id', $ptId))
            ->where('rdb_project.data_show', '1')
            ->select(
                DB::raw('COALESCE(rdb_project_types_master.master_pt_name, "ทุนที่ไม่ระบุ") as type_name'),
                DB::raw('count(*) as count'),
                DB::raw('SUM(COALESCE(rdb_project.pro_budget, 0)) as budget'),
                DB::raw('COALESCE(rdb_project_types_master.color_code, "#64748B") as color')
            )
            ->groupBy('rdb_project_types_master.master_pt_id', 'rdb_project_types_master.master_pt_name', 'rdb_project_types_master.order_index', 'rdb_project_types_master.color_code')
            ->orderBy('rdb_project_types_master.order_index')
            ->orderByDesc('budget')
            ->get();

        // Budget by Faculty
        $deptBudgets = DB::table('rdb_project')
            ->join('rdb_department', 'rdb_project.department_id', '=', 'rdb_department.department_id')
            ->when($masterPtId, fn($q) => $q->join('rdb_project_types as dpt', 'rdb_project.pt_id', '=', 'dpt.pt_id')->where('dpt.master_pt_id', $masterPtId))
            ->when($selectedYearId, fn($q) => $q->where('rdb_project.year_id', $selectedYearId))
            ->when($deptId, fn($q) => $q->where('rdb_project.department_id', $deptId))
            ->when($psId, fn($q) => $q->where('rdb_project.ps_id', $psId))
            ->when($ptId, fn($q) => $q->where('rdb_project.pt_id', $ptId))
            ->where('rdb_project.data_show', '1')
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
            ->map(function ($y) use ($deptId, $psId, $masterPtId, $ptId) {
                $q = RdbProject::where('year_id', $y->year_id)->where('data_show', '1');
                if ($deptId) $q->where('department_id', $deptId);
                if ($psId) $q->where('ps_id', $psId);
                if ($masterPtId) {
                    $q->whereHas('type', fn($sq) => $sq->where('master_pt_id', $masterPtId));
                }
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
        $topProjects = (clone $query)->with([
            'department',
            'year',
            'status',
            'type.masterType',
            'rdbProjectWorks.researcher.prefix',
        ])
            ->orderBy('pro_budget', 'desc')
            ->take(20)
            ->get();

        $qaProjectTable = collect();

        // YoY Delta Calculation
        $prevYear = null;
        $prevTotalProjects = 0;
        $prevTotalBudget = 0.0;
        if ($selectedYear) {
            $prevYear = $years->first(fn($y) => (int)$y->year_name < (int)$selectedYear->year_name);
            if ($prevYear) {
                $prevQ = RdbProject::where('year_id', $prevYear->year_id);
                if ($deptId) $prevQ->where('department_id', $deptId);
                if ($psId) $prevQ->where('ps_id', $psId);
                if ($masterPtId) $prevQ->whereHas('type', fn($q) => $q->where('master_pt_id', $masterPtId));
                if ($ptId) $prevQ->where('pt_id', $ptId);
                if ($budgetRange) {
                    if ($budgetRange === '0-50k') $prevQ->where('pro_budget', '<', 50000);
                    elseif ($budgetRange === '50k-100k') $prevQ->whereBetween('pro_budget', [50000, 100000]);
                    elseif ($budgetRange === '100k-500k') $prevQ->whereBetween('pro_budget', [100000, 500000]);
                    elseif ($budgetRange === '500k+') $prevQ->where('pro_budget', '>=', 500000);
                }
                if ($hasReport !== null) {
                    if ($hasReport === '1') {
                        $prevQ->where(fn($q) => $q->whereNotNull('pro_file_path')->where('pro_file_path', '!=', '')->orWhere(fn($sq) => $sq->whereNotNull('pro_file')->where('pro_file', '!=', '')));
                    } elseif ($hasReport === '0') {
                        $prevQ->where(fn($q) => $q->where(fn($sq) => $sq->whereNull('pro_file_path')->orWhere('pro_file_path', ''))->where(fn($sq) => $sq->whereNull('pro_file')->orWhere('pro_file', '')));
                    }
                }
                $prevTotalProjects = (clone $prevQ)->count();
                $prevTotalBudget = (float)(clone $prevQ)->sum('pro_budget');
            }
        }

        $yoyDelta = [
            'prev_year_name' => $prevYear?->year_name,
            'budget' => $this->computeMetricYoY($totalBudget, $prevTotalBudget),
            'projects' => $this->computeMetricYoY($totalProjects, $prevTotalProjects),
        ];

        return view('frontend.dashboard.projects', array_merge($lookups, [
            'years' => $years,
            'selectedYearId' => $selectedYearId,
            'selectedYear' => $selectedYear,
            'deptId' => $deptId,
            'psId' => $psId,
            'masterPtId' => $masterPtId,
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
            'qaProjectTable' => $qaProjectTable,
            'yoyDelta' => $yoyDelta,
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
                $query->where(function($q) {
                    $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%Scopus%'))
                      ->orWhere(function($sq) {
                          $sq->where(fn($ssq) => $ssq->whereNull('rdb_published.pubtype_id')->orWhere('rdb_published.pubtype_id', ''))
                             ->where(fn($ssq) => $ssq->where('rdb_published.pub_name_journal', 'like', '%Scopus%')->orWhere('rdb_published.pub_note', 'like', '%Scopus%')->orWhere(fn($x) => $x->where('rdb_published.pub_score', '>=', 1.0)->where('rdb_published.pub_name_journal', 'not like', '%Web of Science%')));
                      });
                });
            } elseif ($qualityGroup === 'wos') {
                $query->where(function($q) {
                    $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%Web of Science%'))
                      ->orWhere(function($sq) {
                          $sq->where(fn($ssq) => $ssq->whereNull('rdb_published.pubtype_id')->orWhere('rdb_published.pubtype_id', ''))
                             ->where(fn($ssq) => $ssq->where('rdb_published.pub_name_journal', 'like', '%Web of Science%')->orWhere('rdb_published.pub_name_journal', 'like', '%WoS%')->orWhere('rdb_published.pub_note', 'like', '%Web of Science%'));
                      });
                });
            } elseif ($qualityGroup === 'tci1') {
                $query->where(function($q) {
                    $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%TCI %1%'))
                      ->orWhere(function($sq) {
                          $sq->where(fn($ssq) => $ssq->whereNull('rdb_published.pubtype_id')->orWhere('rdb_published.pubtype_id', ''))
                             ->where(fn($ssq) => $ssq->where('rdb_published.pub_name_journal', 'like', '%TCI%1%')->orWhere('rdb_published.pub_note', 'like', '%TCI%1%')->orWhere('rdb_published.pub_score', '0.80'));
                      });
                });
            } elseif ($qualityGroup === 'tci2') {
                $query->where(function($q) {
                    $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%TCI %2%'))
                      ->orWhere(function($sq) {
                          $sq->where(fn($ssq) => $ssq->whereNull('rdb_published.pubtype_id')->orWhere('rdb_published.pubtype_id', ''))
                             ->where(fn($ssq) => $ssq->where('rdb_published.pub_name_journal', 'like', '%TCI%2%')->orWhere('rdb_published.pub_note', 'like', '%TCI%2%')->orWhere('rdb_published.pub_score', '0.60'));
                      });
                });
            } elseif ($qualityGroup === 'conf') {
                $query->where(function($q) {
                    $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_group', 'like', '%ประชุม%'))
                      ->orWhere(function($sq) {
                          $sq->where(fn($ssq) => $ssq->whereNull('rdb_published.pubtype_id')->orWhere('rdb_published.pubtype_id', ''))
                             ->where(fn($ssq) => $ssq->whereIn('rdb_published.pub_score', ['0.20', '0.25', '0.40'])->orWhere('rdb_published.pub_name_journal', 'like', '%ประชุม%'));
                      });
                });
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

        // Quality Tiers Breakdown (with smart fallback to score & journal keywords for pre-2025 data)
        $tierSql = "
            CASE 
                WHEN rdb_published_type.pubtype_subgroup IS NOT NULL AND rdb_published_type.pubtype_subgroup != '' THEN rdb_published_type.pubtype_subgroup
                WHEN rdb_published.pub_score >= 1.0 OR rdb_published.pub_name_journal LIKE '%Scopus%' OR rdb_published.pub_name_journal LIKE '%Web of Science%' THEN 'วารสารวิชาการระดับนานาชาติ (Scopus / WoS)'
                WHEN rdb_published.pub_score = 0.80 OR rdb_published.pub_name_journal LIKE '%TCI%1%' THEN 'วารสารวิชาการระดับชาติ TCI กลุ่มที่ 1'
                WHEN rdb_published.pub_score = 0.60 OR rdb_published.pub_name_journal LIKE '%TCI%2%' THEN 'วารสารวิชาการระดับชาติ TCI กลุ่มที่ 2'
                WHEN rdb_published.pub_score = 0.40 THEN 'การประชุมวิชาการระดับนานาชาติ'
                WHEN rdb_published.pub_score = 0.20 OR rdb_published.pub_score = 0.25 OR rdb_published.pub_name_journal LIKE '%ประชุม%' THEN 'การประชุมวิชาการระดับชาติ'
                ELSE 'วารสารวิชาการทั่วไป / อื่นๆ'
            END
        ";

        $qualityTiers = (clone $query)
            ->leftJoin('rdb_published_type', 'rdb_published.pubtype_id', '=', 'rdb_published_type.pubtype_id')
            ->select(
                DB::raw("{$tierSql} as tier_name"),
                DB::raw('count(*) as count')
            )
            ->groupBy('tier_name')
            ->orderByDesc('count')
            ->get();

        // Count international (Scopus/WoS) vs TCI vs Conf (with smart fallback)
        $scopusCount = (clone $query)->where(function($q) {
            $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%Scopus%'))
              ->orWhere(function($sq) {
                  $sq->where(fn($ssq) => $ssq->whereNull('pubtype_id')->orWhere('pubtype_id', ''))
                     ->where(fn($ssq) => $ssq->where('pub_name_journal', 'like', '%Scopus%')->orWhere('pub_note', 'like', '%Scopus%')->orWhere(fn($x) => $x->where('pub_score', '>=', 1.0)->where('pub_name_journal', 'not like', '%Web of Science%')));
              });
        })->count();

        $wosCount = (clone $query)->where(function($q) {
            $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%Web of Science%'))
              ->orWhere(function($sq) {
                  $sq->where(fn($ssq) => $ssq->whereNull('pubtype_id')->orWhere('pubtype_id', ''))
                     ->where(fn($ssq) => $ssq->where('pub_name_journal', 'like', '%Web of Science%')->orWhere('pub_name_journal', 'like', '%WoS%')->orWhere('pub_note', 'like', '%Web of Science%'));
              });
        })->count();

        $tci1Count = (clone $query)->where(function($q) {
            $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%TCI %1%'))
              ->orWhere(function($sq) {
                  $sq->where(fn($ssq) => $ssq->whereNull('pubtype_id')->orWhere('pubtype_id', ''))
                     ->where(fn($ssq) => $ssq->where('pub_name_journal', 'like', '%TCI%1%')->orWhere('pub_note', 'like', '%TCI%1%')->orWhere('pub_score', '0.80'));
              });
        })->count();

        $tci2Count = (clone $query)->where(function($q) {
            $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%TCI %2%'))
              ->orWhere(function($sq) {
                  $sq->where(fn($ssq) => $ssq->whereNull('pubtype_id')->orWhere('pubtype_id', ''))
                     ->where(fn($ssq) => $ssq->where('pub_name_journal', 'like', '%TCI%2%')->orWhere('pub_note', 'like', '%TCI%2%')->orWhere('pub_score', '0.60'));
              });
        })->count();

        $confCount = (clone $query)->where(function($q) {
            $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_group', 'like', '%ประชุม%'))
              ->orWhere(function($sq) {
                  $sq->where(fn($ssq) => $ssq->whereNull('pubtype_id')->orWhere('pubtype_id', ''))
                     ->where(fn($ssq) => $ssq->whereIn('pub_score', ['0.20', '0.25', '0.40'])->orWhere('pub_name_journal', 'like', '%ประชุม%'));
              });
        })->count();

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

        $qaPubTable = collect();

        // YoY Delta Calculation
        $prevYear = null;
        $prevTotalPublished = 0;
        $prevIntlCount = 0;
        $prevTci1Count = 0;
        $prevTci2Count = 0;
        $prevConfCount = 0;
        $prevLinkedProjectCount = 0;

        if ($selectedYear) {
            $prevYear = $years->first(fn($y) => (int)$y->year_name < (int)$selectedYear->year_name);
            if ($prevYear) {
                $prevPubQ = RdbPublished::where(fn($q) => $q->where('year_id', $prevYear->year_id)->orWhere('year_bud', $prevYear->year_id));
                if ($deptId) $prevPubQ->where('rdb_published.department_id', $deptId);
                if ($hasProject !== null) {
                    if ($hasProject === '1') $prevPubQ->whereNotNull('pro_id')->where('pro_id', '>', 0);
                    elseif ($hasProject === '0') $prevPubQ->where(fn($q) => $q->whereNull('pro_id')->orWhere('pro_id', 0));
                }
                if ($hasFile !== null) {
                    if ($hasFile === '1') $prevPubQ->where(fn($q) => $q->whereNotNull('pub_file')->where('pub_file', '!=', ''));
                    elseif ($hasFile === '0') $prevPubQ->where(fn($q) => $q->whereNull('pub_file')->orWhere('pub_file', ''));
                }

                $prevTotalPublished = (clone $prevPubQ)->count();
                $prevScopus = (clone $prevPubQ)->where(function($q) {
                    $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%Scopus%'))
                      ->orWhere(function($sq) {
                          $sq->where(fn($ssq) => $ssq->whereNull('pubtype_id')->orWhere('pubtype_id', ''))
                             ->where(fn($ssq) => $ssq->where('pub_name_journal', 'like', '%Scopus%')->orWhere('pub_note', 'like', '%Scopus%')->orWhere(fn($x) => $x->where('pub_score', '>=', 1.0)->where('pub_name_journal', 'not like', '%Web of Science%')));
                      });
                })->count();
                $prevWos = (clone $prevPubQ)->where(function($q) {
                    $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%Web of Science%'))
                      ->orWhere(function($sq) {
                          $sq->where(fn($ssq) => $ssq->whereNull('pubtype_id')->orWhere('pubtype_id', ''))
                             ->where(fn($ssq) => $ssq->where('pub_name_journal', 'like', '%Web of Science%')->orWhere('pub_name_journal', 'like', '%WoS%')->orWhere('pub_note', 'like', '%Web of Science%'));
                      });
                })->count();
                $prevIntlCount = $prevScopus + $prevWos;

                $prevTci1Count = (clone $prevPubQ)->where(function($q) {
                    $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%TCI %1%'))
                      ->orWhere(function($sq) {
                          $sq->where(fn($ssq) => $ssq->whereNull('pubtype_id')->orWhere('pubtype_id', ''))
                             ->where(fn($ssq) => $ssq->where('pub_name_journal', 'like', '%TCI%1%')->orWhere('pub_note', 'like', '%TCI%1%')->orWhere('pub_score', '0.80'));
                      });
                })->count();

                $prevTci2Count = (clone $prevPubQ)->where(function($q) {
                    $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_subgroup', 'like', '%TCI %2%'))
                      ->orWhere(function($sq) {
                          $sq->where(fn($ssq) => $ssq->whereNull('pubtype_id')->orWhere('pubtype_id', ''))
                             ->where(fn($ssq) => $ssq->where('pub_name_journal', 'like', '%TCI%2%')->orWhere('pub_note', 'like', '%TCI%2%')->orWhere('pub_score', '0.60'));
                      });
                })->count();

                $prevConfCount = (clone $prevPubQ)->where(function($q) {
                    $q->whereHas('pubtype', fn($sq) => $sq->where('pubtype_group', 'like', '%ประชุม%'))
                      ->orWhere(function($sq) {
                          $sq->where(fn($ssq) => $ssq->whereNull('pubtype_id')->orWhere('pubtype_id', ''))
                             ->where(fn($ssq) => $ssq->whereIn('pub_score', ['0.20', '0.25', '0.40'])->orWhere('pub_name_journal', 'like', '%ประชุม%'));
                      });
                })->count();

                $prevLinkedProjectCount = (clone $prevPubQ)->whereNotNull('pro_id')->where('pro_id', '>', 0)->count();
            }
        }

        $currentIntl = $scopusCount + $wosCount;
        $yoyDelta = [
            'prev_year_name' => $prevYear?->year_name,
            'published' => $this->computeMetricYoY($totalPublished, $prevTotalPublished),
            'intl' => $this->computeMetricYoY($currentIntl, $prevIntlCount),
            'tci1' => $this->computeMetricYoY($tci1Count, $prevTci1Count),
            'tci2' => $this->computeMetricYoY($tci2Count, $prevTci2Count),
            'conf' => $this->computeMetricYoY($confCount, $prevConfCount),
            'linked' => $this->computeMetricYoY($linkedProjectCount, $prevLinkedProjectCount),
        ];

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
            'qaPubTable' => $qaPubTable,
            'yoyDelta' => $yoyDelta,
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

        // Available provinces (prioritize จ.เพชรบูรณ์ and จ.พิจิตร first, then alphabetical)
        $allProvinces = RdbChangwat::whereNotNull('changwat_t')
            ->where('changwat_t', '!=', '')
            ->distinct()
            ->orderByRaw("CASE WHEN changwat_t = 'จ.เพชรบูรณ์' THEN 0 WHEN changwat_t = 'จ.พิจิตร' THEN 1 ELSE 2 END")
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

        // YoY Delta Calculation
        $prevYear = null;
        $prevTotalUtilizations = 0;
        $prevDistrictsCount = 0;
        $prevPartnerCount = 0;
        $prevQaCount = 0;
        $prevTotalBudget = 0.0;

        if ($selectedYear) {
            $prevYear = $years->first(fn($y) => (int)$y->year_name < (int)$selectedYear->year_name);
            if ($prevYear) {
                $prevUtilQ = RdbProjectUtilize::where(fn($q) => $q->where('utz_year_id', $prevYear->year_id)->orWhere('utz_year_bud', $prevYear->year_id));
                if ($deptId) $prevUtilQ->whereHas('project', fn($q) => $q->where('department_id', $deptId));
                if (!empty($selectedChangwats)) {
                    $prevUtilQ->whereHas('changwat', function ($q) use ($selectedChangwats, $selectedAmphoes) {
                        $q->whereIn('changwat_t', $selectedChangwats);
                        if (!empty($selectedAmphoes)) {
                            $q->whereIn('amphoe_t', $selectedAmphoes);
                        }
                    });
                }
                if ($qaStatus !== null) {
                    if ($qaStatus === '1') $prevUtilQ->where('utz_group_qa', '1');
                    elseif ($qaStatus === '0') $prevUtilQ->where(fn($q) => $q->whereNull('utz_group_qa')->orWhere('utz_group_qa', '!=', '1'));
                }

                $prevTotalUtilizations = (clone $prevUtilQ)->count();
                $prevTotalBudget = (float)(clone $prevUtilQ)->sum('utz_budget');
                $prevQaCount = (clone $prevUtilQ)->where('utz_group_qa', '1')->count();
                $prevPartnerCount = (clone $prevUtilQ)->whereNotNull('utz_department_name')->distinct('utz_department_name')->count('utz_department_name');
                $prevDistrictsCount = (clone $prevUtilQ)->join('rdb_changwat', 'rdb_project_utilize.chw_id', '=', 'rdb_changwat.id')->distinct()->count('rdb_changwat.amphoe_t');
            }
        }

        $yoyDelta = [
            'prev_year_name' => $prevYear?->year_name,
            'utilizations' => $this->computeMetricYoY($totalUtilizations, $prevTotalUtilizations),
            'districts' => $this->computeMetricYoY($districtsRanking->count(), $prevDistrictsCount),
            'partner' => $this->computeMetricYoY($partnerCount, $prevPartnerCount),
            'qa' => $this->computeMetricYoY($qaCount, $prevQaCount),
            'budget' => $this->computeMetricYoY($totalBudget, $prevTotalBudget),
        ];

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
            'yoyDelta' => $yoyDelta,
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

        // YoY Delta Calculation
        $prevYear = null;
        $prevTotalDips = 0;
        $prevPatentsCount = 0;
        $prevPettyPatentsCount = 0;
        $prevCopyrightsCount = 0;
        $prevRegisteredCount = 0;
        $prevPendingCount = 0;

        if ($selectedYear) {
            $prevYear = $years->first(fn($y) => (int)$y->year_name < (int)$selectedYear->year_name);
            if ($prevYear) {
                $prevCeYear = (int)$prevYear->year_name - 543;
                $prevDipQ = RdbDip::where(function ($q) use ($prevYear, $prevCeYear) {
                    $q->whereHas('project', fn($pq) => $pq->where('year_id', $prevYear->year_id))
                      ->orWhereYear('dip_request_date', $prevCeYear);
                });
                if ($deptId) {
                    $prevDipQ->where(function ($q) use ($deptId) {
                        $q->whereHas('project', fn($pq) => $pq->where('department_id', $deptId))
                          ->orWhereHas('researcher', fn($rq) => $rq->where('department_id', $deptId));
                    });
                }
                if ($diptId) $prevDipQ->where('rdb_dip.dipt_id', $diptId);
                if ($regStatus) {
                    if ($regStatus === 'registered') $prevDipQ->whereNotNull('dip_patent_number')->where('dip_patent_number', '!=', '');
                    elseif ($regStatus === 'pending') $prevDipQ->where(fn($q) => $q->whereNull('dip_patent_number')->orWhere('dip_patent_number', ''));
                }
                if ($hasFile !== null) {
                    if ($hasFile === '1') {
                        $prevDipQ->where(fn($q) => $q->whereNotNull('dip_path')->where('dip_path', '!=', '')->orWhere(fn($sq) => $sq->whereNotNull('dip_files')->where('dip_files', '!=', '')));
                    } elseif ($hasFile === '0') {
                        $prevDipQ->where(fn($q) => $q->where(fn($sq) => $sq->whereNull('dip_path')->orWhere('dip_path', ''))->where(fn($sq) => $sq->whereNull('dip_files')->orWhere('dip_files', '')));
                    }
                }

                $prevTotalDips = (clone $prevDipQ)->count();
                $prevPatentsCount = (clone $prevDipQ)->where('rdb_dip.dipt_id', 1)->count();
                $prevPettyPatentsCount = (clone $prevDipQ)->where('rdb_dip.dipt_id', 2)->count();
                $prevCopyrightsCount = (clone $prevDipQ)->where('rdb_dip.dipt_id', 3)->count();
                $prevRegisteredCount = (clone $prevDipQ)->whereNotNull('dip_patent_number')->where('dip_patent_number', '!=', '')->count();
                $prevPendingCount = $prevTotalDips - $prevRegisteredCount;
            }
        }

        $yoyDelta = [
            'prev_year_name' => $prevYear?->year_name,
            'total' => $this->computeMetricYoY($totalDips, $prevTotalDips),
            'patents' => $this->computeMetricYoY($patentsCount, $prevPatentsCount),
            'petty' => $this->computeMetricYoY($pettyPatentsCount, $prevPettyPatentsCount),
            'copyrights' => $this->computeMetricYoY($copyrightsCount, $prevCopyrightsCount),
            'registered' => $this->computeMetricYoY($registeredCount, $prevRegisteredCount),
            'pending' => $this->computeMetricYoY($pendingCount, $prevPendingCount),
        ];

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
            'yoyDelta' => $yoyDelta,
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

        $activeFilterCount = count(array_filter([$selectedYearId, $deptId, $prefixId, $profileTier, $hasProjects, $hasPublications]));

        $query = RdbResearcher::query();

        if ($selectedYearId) {
            $query->whereHas('rdbProjects', function ($q) use ($selectedYearId) {
                $q->where('rdb_project.year_id', $selectedYearId)
                  ->where('rdb_project.data_show', 1)
                  ->where(function ($sq) {
                      $sq->whereNull('rdb_project.ps_id')
                        ->orWhere('rdb_project.ps_id', '<>', RdbProjectStatus::REFUNDED);
                  });
            });
        }
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
        if ($selectedYearId) {
            $topByProjectsQuery->join('rdb_project', 'rdb_project_work.pro_id', '=', 'rdb_project.pro_id')
                ->where('rdb_project.year_id', $selectedYearId)
                ->where('rdb_project.data_show', 1)
                ->where(fn($q) => $q->whereNull('rdb_project.ps_id')->orWhere('rdb_project.ps_id', '<>', RdbProjectStatus::REFUNDED));
        }
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
        if ($selectedYearId) {
            $topByPubsQuery->join('rdb_published', 'rdb_published_work.published_id', '=', 'rdb_published.id')
                ->where(fn($q) => $q->where('rdb_published.year_id', $selectedYearId)->orWhere('rdb_published.year_bud', $selectedYearId));
        }
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

        // New Researcher Report (reportfirstproject: นักวิจัยรุ่นใหม่ที่เริ่มทำโครงการวิจัยเป็นครั้งแรกในปีงบประมาณ)
        $firstProjectSub = DB::table('rdb_project_work')
            ->join('rdb_project', 'rdb_project_work.pro_id', '=', 'rdb_project.pro_id')
            ->where('rdb_project.data_show', 1)
            ->where(fn($q) => $q->whereNull('rdb_project.ps_id')->orWhere('rdb_project.ps_id', '<>', RdbProjectStatus::REFUNDED))
            ->whereIn('rdb_project_work.position_id', [1, 2])
            ->select(
                'rdb_project_work.researcher_id',
                DB::raw('MIN(rdb_project.year_id) as first_year_id'),
                DB::raw('MIN(rdb_project.pro_id) as first_pro_id')
            )
            ->groupBy('rdb_project_work.researcher_id');

        $newResearchersQuery = DB::query()->fromSub($firstProjectSub, 'fp')
            ->join('rdb_researcher', 'fp.researcher_id', '=', 'rdb_researcher.researcher_id')
            ->leftJoin('rdb_prefix', 'rdb_researcher.prefix_id', '=', 'rdb_prefix.prefix_id')
            ->leftJoin('rdb_department', 'rdb_researcher.department_id', '=', 'rdb_department.department_id')
            ->join('rdb_year', 'fp.first_year_id', '=', 'rdb_year.year_id')
            ->join('rdb_project', 'fp.first_pro_id', '=', 'rdb_project.pro_id');

        if ($selectedYearId) {
            $newResearchersQuery->where('fp.first_year_id', $selectedYearId);
        }
        if ($deptId) {
            $newResearchersQuery->where('rdb_researcher.department_id', $deptId);
        }

        $firstProjectByFaculty = (clone $newResearchersQuery)
            ->select(
                DB::raw('COALESCE(rdb_department.department_nameTH, "ไม่ระบุหน่วยงาน") as department_nameTH'),
                DB::raw('COUNT(*) as new_researcher_count')
            )
            ->groupBy('rdb_department.department_nameTH')
            ->orderByDesc('new_researcher_count')
            ->get();

        $firstProjectList = (clone $newResearchersQuery)
            ->select(
                'rdb_researcher.researcher_id',
                'rdb_prefix.prefix_nameTH',
                'rdb_researcher.researcher_fname',
                'rdb_researcher.researcher_lname',
                'rdb_department.department_nameTH',
                'rdb_year.year_name',
                'rdb_project.pro_id',
                'rdb_project.pro_nameTH',
                'rdb_project.pro_budget'
            )
            ->orderByDesc('rdb_year.year_name')
            ->limit(15)
            ->get();

        // YoY Delta Calculation
        $prevYear = null;
        $prevTotalResearchers = 0;
        $prevScopusProfileCount = 0;
        $prevOrcidProfileCount = 0;
        $prevFacultyCount = 0;

        if ($selectedYear) {
            $prevYear = $years->first(fn($y) => (int)$y->year_name < (int)$selectedYear->year_name);
            if ($prevYear) {
                $prevResQ = RdbResearcher::query();
                $prevResQ->whereHas('rdbProjects', function ($q) use ($prevYear) {
                    $q->where('rdb_project.year_id', $prevYear->year_id)
                      ->where('rdb_project.data_show', 1)
                      ->where(function ($sq) {
                          $sq->whereNull('rdb_project.ps_id')
                            ->orWhere('rdb_project.ps_id', '<>', RdbProjectStatus::REFUNDED);
                      });
                });
                if ($deptId) $prevResQ->where('rdb_researcher.department_id', $deptId);
                if ($prefixId) $prevResQ->where('rdb_researcher.prefix_id', $prefixId);

                $prevTotalResearchers = (clone $prevResQ)->count();
                $prevScopusProfileCount = (clone $prevResQ)->whereNotNull('scopus_authorId')->where('scopus_authorId', '!=', '')->count();
                $prevOrcidProfileCount = (clone $prevResQ)->whereNotNull('orcid')->where('orcid', '!=', '')->count();
                $prevFacultyCount = (clone $prevResQ)->join('rdb_department', 'rdb_researcher.department_id', '=', 'rdb_department.department_id')->distinct('rdb_department.department_nameTH')->count('rdb_department.department_nameTH');
            }
        }

        $yoyDelta = [
            'prev_year_name' => $prevYear?->year_name,
            'researchers' => $this->computeMetricYoY($totalResearchers, $prevTotalResearchers),
            'scopus' => $this->computeMetricYoY($scopusProfileCount, $prevScopusProfileCount),
            'orcid' => $this->computeMetricYoY($orcidProfileCount, $prevOrcidProfileCount),
            'faculties' => $this->computeMetricYoY($researchersByFaculty->count(), $prevFacultyCount),
        ];

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
            'firstProjectByFaculty' => $firstProjectByFaculty,
            'firstProjectList' => $firstProjectList,
            'yoyDelta' => $yoyDelta,
        ]));
    }

    /**
     * 7. Quality Assurance (QA) & Accreditations Dashboard
     * Consolidated QA metrics across Projects, Publications (0.20-1.00), Utilizations, and New Researchers
     */
    public function qa(Request $request)
    {
        [$years, $selectedYearId, $selectedYear] = $this->getYearContext($request);
        $lookups = $this->getCommonLookups();

        // Academic Faculties of PCRU (คณะวิชาการ 6 คณะ)
        $facultyIds = [2, 3, 4, 5, 6, 13];

        // Calculation Scope: 'faculty' (เฉพาะคณะวิชาการ - Default) vs 'all' (ทั้งหมด รวมสำนัก/สถาบัน/ส่วนกลาง)
        $scope = $request->input('scope', 'faculty');
        if (!in_array($scope, ['faculty', 'all'])) {
            $scope = 'faculty';
        }

        $deptId = $request->filled('department_id') && $request->department_id !== 'all' ? (int)$request->department_id : null;
        $activeTab = $request->input('tab', 'pub');

        $activeFilterCount = count(array_filter([$selectedYearId, $deptId]));
        if ($scope === 'all') {
            $activeFilterCount++;
        }

        // Departments filtered by calculation scope
        $qaDepartments = $lookups['departments'];
        if ($scope === 'faculty') {
            $qaDepartments = $qaDepartments->whereIn('department_id', $facultyIds);
        }
        if ($deptId) {
            $qaDepartments = $qaDepartments->where('department_id', $deptId);
        }

        $targetYearForQa = $selectedYearId ?: RdbYear::whereHas('projects')->max('year_id');
        $targetPubYearId = $selectedYearId ?: RdbPublished::max('year_id');
        $branches = DB::table('rdb_published_branch')->whereIn('branch_id', [1, 2, 3])->get();

        // 1. QA Project Table by Department (สัดส่วนโครงการวิจัยและงบประมาณต่อบุคลากร)
        $qaProjectTable = $qaDepartments->map(function ($dept) use ($selectedYearId, $targetYearForQa) {
            $pq = RdbProject::where('department_id', $dept->department_id)
                ->where('data_show', '1')
                ->where(fn($q) => $q->whereNull('ps_id')->orWhere('ps_id', '<>', RdbProjectStatus::REFUNDED));
            if ($selectedYearId) {
                $pq->where('year_id', $selectedYearId);
            }

            $projCount = $pq->count();
            $projBudget = (float) $pq->sum('pro_budget');
            $perNum = (int) DB::table('rdb_project_personnel')
                ->where('department_id', $dept->department_id)
                ->when($targetYearForQa, fn($q) => $q->where('year_id', $targetYearForQa))
                ->sum('pp_num');

            return [
                'department_id' => $dept->department_id,
                'department_nameTH' => $dept->department_nameTH,
                'per_num' => $perNum,
                'project_count' => $projCount,
                'total_budget' => $projBudget,
                'avg_budget_per_person' => $perNum > 0 ? round($projBudget / $perNum, 2) : 0,
                'project_ratio_pct' => $perNum > 0 ? round(($projCount / $perNum) * 100, 2) : 0,
            ];
        })->values();

        // 1.1 QA Project Faculty & Branch Detailed Table (แยก คณะ - กลุ่มสาขา: วิทย์ฯ / มนุษย์ฯ / วิทย์สุขภาพ)
        $projPersonnelQuery = DB::table('rdb_project_personnel as p')
            ->join('rdb_department as d', 'p.department_id', '=', 'd.department_id')
            ->join('rdb_published_branch as b', 'p.depcat_id', '=', 'b.branch_id')
            ->when($targetYearForQa, fn($q) => $q->where('p.year_id', $targetYearForQa));

        if ($scope === 'faculty') {
            $projPersonnelQuery->whereIn('p.department_id', $facultyIds);
        }
        if ($deptId) {
            $projPersonnelQuery->where('p.department_id', $deptId);
        }

        $qaProjectFacultyBranchTable = $projPersonnelQuery
            ->select(
                'd.department_id',
                'd.department_nameTH',
                'b.branch_id',
                'b.branch_name',
                'p.pp_num',
                'p.pp_standard'
            )
            ->orderBy('d.department_id')
            ->orderBy('b.branch_id')
            ->get()
            ->map(function ($r) use ($selectedYearId) {
                $pq = DB::table('rdb_project')
                    ->where('department_id', $r->department_id)
                    ->where('depcat_id', $r->branch_id)
                    ->where('data_show', '1')
                    ->where(fn($q) => $q->whereNull('ps_id')->orWhere('ps_id', '<>', RdbProjectStatus::REFUNDED));

                if ($selectedYearId) {
                    $pq->where('year_id', $selectedYearId);
                }

                $projCount = $pq->count();
                $projBudget = (float) $pq->sum('pro_budget');
                $perNum = (float) $r->pp_num;
                $standardBudget = (float) ($r->pp_standard ?: ($r->branch_id == 1 ? 60000 : ($r->branch_id == 3 ? 50000 : 25000)));
                $avgBudgetPerPerson = $perNum > 0 ? round($projBudget / $perNum, 2) : 0;

                $qaScore5 = $standardBudget > 0 ? round(($avgBudgetPerPerson / $standardBudget) * 5, 2) : 0;
                if ($qaScore5 > 5.00) {
                    $qaScore5 = 5.00;
                }

                return [
                    'department_id' => $r->department_id,
                    'department_nameTH' => $r->department_nameTH,
                    'branch_id' => $r->branch_id,
                    'branch_name' => $r->branch_name,
                    'per_num' => $perNum,
                    'proj_count' => $projCount,
                    'proj_budget' => $projBudget,
                    'avg_budget_per_person' => $avgBudgetPerPerson,
                    'standard_budget' => $standardBudget,
                    'qa_score_5' => $qaScore5,
                ];
            });

        // 1.2 QA Project Faculty Grouped with Faculty Overall Score (จัดเรียงเป็นกรุ๊ปคณะ และคำนวณคะแนนภาพรวมระดับคณะ เต็ม 5.00)
        $qaProjectFacultyGroups = $qaProjectFacultyBranchTable->groupBy('department_id')->map(function ($items) {
            $dept = $items->first();
            $totalPer = (float) $items->sum('per_num');
            $totalProjects = (int) $items->sum('proj_count');
            $totalBudget = (float) $items->sum('proj_budget');
            $avgBudgetPerPerson = $totalPer > 0 ? round($totalBudget / $totalPer, 2) : 0;

            // Faculty QA Score / 5 is the arithmetic average of all branch scores
            // (e.g. if a faculty has 3 branches with scores 5.00, 0.00, 4.08, the overall score is (5 + 0 + 4.08) / 3 = 3.03)
            $facultyQaScore5 = $items->count() > 0 ? round($items->avg('qa_score_5'), 2) : 0;
            if ($facultyQaScore5 > 5.00) {
                $facultyQaScore5 = 5.00;
            }

            return [
                'department_id' => $dept['department_id'],
                'department_nameTH' => $dept['department_nameTH'],
                'branches' => $items,
                'total_personnel' => $totalPer,
                'total_projects' => $totalProjects,
                'total_budget' => $totalBudget,
                'avg_budget_per_person' => $avgBudgetPerPerson,
                'faculty_qa_score_5' => $facultyQaScore5,
            ];
        })->values();

        // 1.3 QA Project Budget by Academic Field / Branch (วิทย์ฯ 60,000, มนุษย์ฯ 25,000, วิทย์สุขภาพ 50,000)
        $qaProjectBranchTable = $branches->map(function ($b) use ($selectedYearId, $targetYearForQa, $scope, $facultyIds, $deptId) {
            $pp = DB::table('rdb_project_personnel')
                ->where('depcat_id', $b->branch_id)
                ->when($targetYearForQa, fn($q) => $q->where('year_id', $targetYearForQa));

            if ($scope === 'faculty') {
                $pp->whereIn('department_id', $facultyIds);
            }
            if ($deptId) {
                $pp->where('department_id', $deptId);
            }

            $perNum = (float) (clone $pp)->sum('pp_num');
            $standardBudget = (float) (clone $pp)->avg('pp_standard') ?: ($b->branch_id == 1 ? 60000 : ($b->branch_id == 3 ? 50000 : 25000));

            $deptIds = (clone $pp)->pluck('department_id')->unique();

            $pq = DB::table('rdb_project')
                ->whereIn('department_id', $deptIds)
                ->where('depcat_id', $b->branch_id)
                ->where('data_show', '1')
                ->where(fn($q) => $q->whereNull('ps_id')->orWhere('ps_id', '<>', RdbProjectStatus::REFUNDED));

            if ($selectedYearId) {
                $pq->where('year_id', $selectedYearId);
            }

            $projCount = $pq->count();
            $projBudget = (float) $pq->sum('pro_budget');
            $avgBudgetPerPerson = $perNum > 0 ? round($projBudget / $perNum, 2) : 0;

            // OHEC/CHE Formula: (avgBudgetPerPerson / standardBudget) * 5
            $qaScore5 = $standardBudget > 0 ? round(($avgBudgetPerPerson / $standardBudget) * 5, 2) : 0;
            if ($qaScore5 > 5.00) {
                $qaScore5 = 5.00;
            }

            return [
                'branch_id' => $b->branch_id,
                'branch_name' => $b->branch_name,
                'per_num' => $perNum,
                'proj_count' => $projCount,
                'proj_budget' => $projBudget,
                'avg_budget_per_person' => $avgBudgetPerPerson,
                'standard_budget' => $standardBudget,
                'qa_score_5' => $qaScore5,
            ];
        });
        
        // Overall institutional QA project score: average across faculties
        $avgProjectQaScore5 = $qaProjectFacultyGroups->where('total_personnel', '>', 0)->count() > 0 
            ? round($qaProjectFacultyGroups->where('total_personnel', '>', 0)->avg('faculty_qa_score_5'), 2) 
            : 0;

        // 2. QA Publications Table by Department (คะแนนถ่วงน้ำหนัก 0.20 - 1.00)
        $qaPubTable = $qaDepartments->map(function ($dept) use ($selectedYearId, $targetPubYearId) {
            $basePub = RdbPublished::where('department_id', $dept->department_id)->where('data_show', 1);
            if ($selectedYearId) {
                $basePub->where(fn($q) => $q->where('year_id', $selectedYearId)->orWhere('year_bud', $selectedYearId));
            }

            $c020 = (clone $basePub)->whereIn('pub_score', ['0.20', '0.25'])->count();
            $c040 = (clone $basePub)->where('pub_score', '0.40')->count();
            $c060 = (clone $basePub)->where('pub_score', '0.60')->count();
            $c080 = (clone $basePub)->where('pub_score', '0.80')->count();
            $c100 = (clone $basePub)->where('pub_score', '1.00')->count();

            $totalItems = $c020 + $c040 + $c060 + $c080 + $c100;
            $weightedScore = round(($c020 * 0.20) + ($c040 * 0.40) + ($c060 * 0.60) + ($c080 * 0.80) + ($c100 * 1.00), 2);

            $perNum = (int) DB::table('rdb_published_personnel')
                ->where('department_id', $dept->department_id)
                ->when($targetPubYearId, fn($q) => $q->where('year_id', $targetPubYearId))
                ->sum('personnel_num');

            return [
                'department_id' => $dept->department_id,
                'department_nameTH' => $dept->department_nameTH,
                'per_num' => $perNum,
                'count_020' => $c020,
                'count_040' => $c040,
                'count_060' => $c060,
                'count_080' => $c080,
                'count_100' => $c100,
                'total_items' => $totalItems,
                'weighted_score' => $weightedScore,
                'score_pct' => $perNum > 0 ? round(($weightedScore / $perNum) * 100, 2) : 0,
            ];
        })->values();

        // 2.1 QA Publications Faculty & Branch Detailed Table (แยก คณะ - กลุ่มสาขา: วิทย์ฯ / มนุษย์ฯ / วิทย์สุขภาพ)
        $pubPersonnelQuery = DB::table('rdb_published_personnel as p')
            ->join('rdb_department as d', 'p.department_id', '=', 'd.department_id')
            ->join('rdb_published_branch as b', 'p.depcat_id', '=', 'b.branch_id')
            ->leftJoin('rdb_published_branchper as bp', function($join) use ($targetPubYearId) {
                $join->on('p.depcat_id', '=', 'bp.branch_id')
                     ->when($targetPubYearId, fn($q) => $q->where('bp.year_id', $targetPubYearId));
            })
            ->when($targetPubYearId, fn($q) => $q->where('p.year_id', $targetPubYearId));

        if ($scope === 'faculty') {
            $pubPersonnelQuery->whereIn('p.department_id', $facultyIds);
        }
        if ($deptId) {
            $pubPersonnelQuery->where('p.department_id', $deptId);
        }

        $qaPubFacultyBranchTable = $pubPersonnelQuery
            ->select(
                'd.department_id',
                'd.department_nameTH',
                'b.branch_id',
                'b.branch_name',
                'p.personnel_num',
                'bp.branchper_percent'
            )
            ->orderBy('d.department_id')
            ->orderBy('b.branch_id')
            ->get()
            ->map(function ($r) use ($selectedYearId) {
                $pubs = DB::table('rdb_published')
                    ->where('department_id', $r->department_id)
                    ->where('branch_id', $r->branch_id)
                    ->where('data_show', 1);

                if ($selectedYearId) {
                    $pubs->where(fn($q) => $q->where('year_id', $selectedYearId)->orWhere('year_bud', $selectedYearId));
                }

                $c020 = (clone $pubs)->whereIn('pub_score', ['0.20', '0.25'])->count();
                $c040 = (clone $pubs)->where('pub_score', '0.40')->count();
                $c060 = (clone $pubs)->where('pub_score', '0.60')->count();
                $c080 = (clone $pubs)->where('pub_score', '0.80')->count();
                $c100 = (clone $pubs)->where('pub_score', '1.00')->count();

                $totalPubs = $c020 + $c040 + $c060 + $c080 + $c100;
                $weightedScore = round(($c020 * 0.20) + ($c040 * 0.40) + ($c060 * 0.60) + ($c080 * 0.80) + ($c100 * 1.00), 2);
                $perNum = (float) $r->personnel_num;
                $scorePct = $perNum > 0 ? round(($weightedScore / $perNum) * 100, 2) : 0;
                $benchPercent = (float) ($r->branchper_percent ?: ($r->branch_id == 1 ? 30 : ($r->branch_id == 3 ? 30 : 20)));

                $qaScore5 = $benchPercent > 0 ? round(($scorePct * 5) / $benchPercent, 2) : 0;
                if ($qaScore5 > 5.00) {
                    $qaScore5 = 5.00;
                }

                return [
                    'department_id' => $r->department_id,
                    'department_nameTH' => $r->department_nameTH,
                    'branch_id' => $r->branch_id,
                    'branch_name' => $r->branch_name,
                    'per_num' => $perNum,
                    'total_pubs' => $totalPubs,
                    'count_020' => $c020,
                    'count_040' => $c040,
                    'count_060' => $c060,
                    'count_080' => $c080,
                    'count_100' => $c100,
                    'weighted_score' => $weightedScore,
                    'score_pct' => $scorePct,
                    'bench_percent' => $benchPercent,
                    'qa_score_5' => $qaScore5,
                ];
            });

        // 2.2 QA Publications Faculty Grouped with Faculty Overall Score (จัดเรียงเป็นกรุ๊ปคณะ และคำนวณคะแนนภาพรวมระดับคณะ เต็ม 5.00)
        $qaPubFacultyGroups = $qaPubFacultyBranchTable->groupBy('department_id')->map(function ($items) {
            $dept = $items->first();
            $totalPer = (float) $items->sum('per_num');
            $totalPubs = (int) $items->sum('total_pubs');
            $totalWeighted = (float) $items->sum('weighted_score');
            $scorePct = $totalPer > 0 ? round(($totalWeighted / $totalPer) * 100, 2) : 0;

            // Faculty QA Score / 5 is the arithmetic average of all branch scores
            // (e.g. if a faculty has 3 branches with scores 5.00, 0.00, 4.08, the overall score is (5 + 0 + 4.08) / 3 = 3.03)
            $facultyQaScore5 = $items->count() > 0 ? round($items->avg('qa_score_5'), 2) : 0;
            if ($facultyQaScore5 > 5.00) {
                $facultyQaScore5 = 5.00;
            }

            return [
                'department_id' => $dept['department_id'],
                'department_nameTH' => $dept['department_nameTH'],
                'branches' => $items,
                'total_personnel' => $totalPer,
                'total_pubs' => $totalPubs,
                'total_weighted_score' => $totalWeighted,
                'score_pct' => $scorePct,
                'faculty_qa_score_5' => $facultyQaScore5,
            ];
        })->values();

        // 2.3 QA Publications by Academic Field / Branch (วิทยาศาสตร์ฯ 30%, มนุษยศาสตร์ฯ 20%, วิทย์สุขภาพ 30%)
        $qaPubBranchTable = $branches->map(function ($b) use ($selectedYearId, $targetPubYearId, $scope, $facultyIds, $deptId) {
            $pp = DB::table('rdb_published_personnel')
                ->where('depcat_id', $b->branch_id)
                ->when($targetPubYearId, fn($q) => $q->where('year_id', $targetPubYearId));

            if ($scope === 'faculty') {
                $pp->whereIn('department_id', $facultyIds);
            }
            if ($deptId) {
                $pp->where('department_id', $deptId);
            }

            $perNum = (float) (clone $pp)->sum('personnel_num');

            $benchPercent = (float) DB::table('rdb_published_branchper')
                ->where('branch_id', $b->branch_id)
                ->when($targetPubYearId, fn($q) => $q->where('year_id', $targetPubYearId))
                ->value('branchper_percent') ?: ($b->branch_id == 1 ? 30 : ($b->branch_id == 3 ? 30 : 20));

            $deptIds = (clone $pp)->pluck('department_id')->unique();

            $pubs = DB::table('rdb_published')
                ->whereIn('department_id', $deptIds)
                ->where('branch_id', $b->branch_id)
                ->where('data_show', 1);

            if ($selectedYearId) {
                $pubs->where(fn($q) => $q->where('year_id', $selectedYearId)->orWhere('year_bud', $selectedYearId));
            }

            $c020 = (clone $pubs)->whereIn('pub_score', ['0.20', '0.25'])->count();
            $c040 = (clone $pubs)->where('pub_score', '0.40')->count();
            $c060 = (clone $pubs)->where('pub_score', '0.60')->count();
            $c080 = (clone $pubs)->where('pub_score', '0.80')->count();
            $c100 = (clone $pubs)->where('pub_score', '1.00')->count();

            $totalPubs = $c020 + $c040 + $c060 + $c080 + $c100;
            $weightedScore = round(($c020 * 0.20) + ($c040 * 0.40) + ($c060 * 0.60) + ($c080 * 0.80) + ($c100 * 1.00), 2);
            $scorePct = $perNum > 0 ? round(($weightedScore / $perNum) * 100, 2) : 0;

            // OHEC/CHE Formula: ( (weightedScore / perNum * 100) * 5 ) / benchPercent
            $qaScore5 = $benchPercent > 0 ? round(($scorePct * 5) / $benchPercent, 2) : 0;
            if ($qaScore5 > 5.00) {
                $qaScore5 = 5.00;
            }

            return [
                'branch_id' => $b->branch_id,
                'branch_name' => $b->branch_name,
                'per_num' => $perNum,
                'count_020' => $c020,
                'count_040' => $c040,
                'count_060' => $c060,
                'count_080' => $c080,
                'count_100' => $c100,
                'total_pubs' => $totalPubs,
                'weighted_score' => $weightedScore,
                'score_pct' => $scorePct,
                'bench_percent' => $benchPercent,
                'qa_score_5' => $qaScore5,
            ];
        });
        
        // Overall institutional QA publication score: average across faculties
        $avgPubQaScore5 = $qaPubFacultyGroups->where('total_personnel', '>', 0)->count() > 0 
            ? round($qaPubFacultyGroups->where('total_personnel', '>', 0)->avg('faculty_qa_score_5'), 2) 
            : 0;

        // 3. QA Utilizations Table (ผลงานที่ผ่านการรับรอง QA)
        $qaUtilTable = $qaDepartments->map(function ($dept) use ($selectedYearId) {
            $baseUtil = DB::table('rdb_project_utilize')
                ->join('rdb_project', 'rdb_project_utilize.pro_id', '=', 'rdb_project.pro_id')
                ->where('rdb_project_utilize.data_show', '1')
                ->where('rdb_project.data_show', '1')
                ->where('rdb_project.department_id', $dept->department_id);

            if ($selectedYearId) {
                $baseUtil->where(function ($q) use ($selectedYearId) {
                    $q->where('rdb_project_utilize.utz_year_id', $selectedYearId)
                      ->orWhere('rdb_project_utilize.utz_year_bud', $selectedYearId)
                      ->orWhere('rdb_project_utilize.utz_year_edu', $selectedYearId)
                      ->orWhere('rdb_project.year_id', $selectedYearId);
                });
            }

            $totalUtil = (clone $baseUtil)->count();
            $qaCertified = (clone $baseUtil)->where('rdb_project_utilize.utz_group_qa', '1')->count();

            return [
                'department_id' => $dept->department_id,
                'department_nameTH' => $dept->department_nameTH,
                'total_util' => $totalUtil,
                'qa_certified' => $qaCertified,
                'qa_pct' => $totalUtil > 0 ? round(($qaCertified / $totalUtil) * 100, 1) : 0,
            ];
        })->values();

        // 4. First-time Grantee Researchers (reportfirstproject)
        // STRICT QA RULE: Only count researchers with an existing assigned faculty/department
        $firstProjectSub = DB::table('rdb_project_work')
            ->join('rdb_project', 'rdb_project_work.pro_id', '=', 'rdb_project.pro_id')
            ->where('rdb_project.data_show', '1')
            ->where(fn($q) => $q->whereNull('rdb_project.ps_id')->orWhere('rdb_project.ps_id', '<>', RdbProjectStatus::REFUNDED))
            ->select(
                'rdb_project_work.researcher_id',
                DB::raw('MIN(rdb_project.year_id) as first_year_id'),
                DB::raw('MIN(rdb_project.pro_id) as first_pro_id')
            )
            ->groupBy('rdb_project_work.researcher_id');

        $newResearchersQuery = DB::query()->fromSub($firstProjectSub, 'fp')
            ->join('rdb_researcher', 'fp.researcher_id', '=', 'rdb_researcher.researcher_id')
            ->leftJoin('rdb_prefix', 'rdb_researcher.prefix_id', '=', 'rdb_prefix.prefix_id')
            ->join('rdb_department', 'rdb_researcher.department_id', '=', 'rdb_department.department_id')
            ->join('rdb_year', 'fp.first_year_id', '=', 'rdb_year.year_id')
            ->join('rdb_project', 'fp.first_pro_id', '=', 'rdb_project.pro_id')
            ->whereNotNull('rdb_researcher.department_id')
            ->where('rdb_researcher.department_id', '>', 0);

        if ($scope === 'faculty') {
            $newResearchersQuery->whereIn('rdb_researcher.department_id', $facultyIds);
        }
        if ($selectedYearId) {
            $newResearchersQuery->where('fp.first_year_id', $selectedYearId);
        }
        if ($deptId) {
            $newResearchersQuery->where('rdb_researcher.department_id', $deptId);
        }

        $firstProjectByFaculty = (clone $newResearchersQuery)
            ->select(
                'rdb_department.department_id',
                'rdb_department.department_nameTH',
                DB::raw('COUNT(*) as new_researcher_count')
            )
            ->groupBy('rdb_department.department_id', 'rdb_department.department_nameTH')
            ->orderByDesc('new_researcher_count')
            ->get();

        $firstProjectList = (clone $newResearchersQuery)
            ->select(
                'rdb_researcher.researcher_id',
                'rdb_prefix.prefix_nameTH',
                'rdb_researcher.researcher_fname',
                'rdb_researcher.researcher_lname',
                'rdb_department.department_nameTH',
                'rdb_year.year_name',
                'rdb_project.pro_id',
                'rdb_project.pro_nameTH',
                'rdb_project.pro_budget'
            )
            ->orderByDesc('rdb_year.year_name')
            ->limit(30)
            ->get();

        $firstProjectByYear = (clone $newResearchersQuery)
            ->select(
                'rdb_year.year_name',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('rdb_year.year_name')
            ->orderBy('rdb_year.year_name')
            ->get();

        // 5. Consolidated KPI Summary Metrics
        $totalWeightedPubScore = $qaPubTable->sum('weighted_score');
        $totalPubItems = $qaPubTable->sum('total_items');
        $totalProjects = $qaProjectTable->sum('project_count');
        $totalBudget = $qaProjectTable->sum('total_budget');
        $totalPersonnel = $qaProjectTable->sum('per_num');
        $avgBudgetPerPersonnel = $totalPersonnel > 0 ? round($totalBudget / $totalPersonnel, 2) : 0;
        $totalUtilCertified = $qaUtilTable->sum('qa_certified');
        $totalUtil = $qaUtilTable->sum('total_util');
        $totalFirstResearchers = $firstProjectByFaculty->sum('new_researcher_count');

        // YoY Delta Calculation
        $prevYear = null;
        $prevTotalWeightedPubScore = 0.0;
        $prevAvgBudgetPerPersonnel = 0.0;
        $prevTotalUtilCertified = 0;
        $prevTotalFirstResearchers = 0;

        if ($selectedYear) {
            $prevYear = $years->first(fn($y) => (int)$y->year_name < (int)$selectedYear->year_name);
            if ($prevYear) {
                // Prev weighted pubs
                $prevPubs = DB::table('rdb_published')
                    ->where('data_show', 1)
                    ->where(fn($q) => $q->where('year_id', $prevYear->year_id)->orWhere('year_bud', $prevYear->year_id));
                if ($scope === 'faculty') $prevPubs->whereIn('department_id', $facultyIds);
                if ($deptId) $prevPubs->where('department_id', $deptId);

                $p020 = (clone $prevPubs)->whereIn('pub_score', ['0.20', '0.25'])->count();
                $p040 = (clone $prevPubs)->where('pub_score', '0.40')->count();
                $p060 = (clone $prevPubs)->where('pub_score', '0.60')->count();
                $p080 = (clone $prevPubs)->where('pub_score', '0.80')->count();
                $p100 = (clone $prevPubs)->where('pub_score', '1.00')->count();
                $prevTotalWeightedPubScore = round(($p020 * 0.20) + ($p040 * 0.40) + ($p060 * 0.60) + ($p080 * 0.80) + ($p100 * 1.00), 2);

                // Prev budget per personnel
                $prevProjQ = RdbProject::where('data_show', 1)
                    ->where(fn($q) => $q->whereNull('ps_id')->orWhere('ps_id', '<>', RdbProjectStatus::REFUNDED))
                    ->where('year_id', $prevYear->year_id);
                if ($scope === 'faculty') $prevProjQ->whereIn('department_id', $facultyIds);
                if ($deptId) $prevProjQ->where('department_id', $deptId);
                $prevBudget = (float) $prevProjQ->sum('pro_budget');

                $prevPerQuery = DB::table('rdb_project_personnel')
                    ->where('year_id', $prevYear->year_id);
                if ($scope === 'faculty') $prevPerQuery->whereIn('department_id', $facultyIds);
                if ($deptId) $prevPerQuery->where('department_id', $deptId);
                $prevPerNum = (int) $prevPerQuery->sum('pp_num');
                $prevAvgBudgetPerPersonnel = $prevPerNum > 0 ? round($prevBudget / $prevPerNum, 2) : 0.0;

                // Prev QA util certified
                $prevUtilQ = DB::table('rdb_project_utilize')
                    ->join('rdb_project', 'rdb_project_utilize.pro_id', '=', 'rdb_project.pro_id')
                    ->where('rdb_project_utilize.data_show', '1')
                    ->where('rdb_project.data_show', '1')
                    ->where('rdb_project_utilize.utz_group_qa', '1')
                    ->where(fn($q) => $q->where('rdb_project_utilize.utz_year_id', $prevYear->year_id)->orWhere('rdb_project_utilize.utz_year_bud', $prevYear->year_id));
                if ($scope === 'faculty') $prevUtilQ->whereIn('rdb_project.department_id', $facultyIds);
                if ($deptId) $prevUtilQ->where('rdb_project.department_id', $deptId);
                $prevTotalUtilCertified = $prevUtilQ->count();

                // Prev First Researchers
                $prevFirstQ = DB::table('rdb_researcher')
                    ->joinSub(
                        DB::table('rdb_project_work as pw')
                            ->join('rdb_project as p', 'pw.pro_id', '=', 'p.pro_id')
                            ->where('p.data_show', 1)
                            ->where(fn($q) => $q->whereNull('p.ps_id')->orWhere('p.ps_id', '<>', RdbProjectStatus::REFUNDED))
                            ->select('pw.researcher_id', DB::raw('MIN(p.year_id) as first_year_id'))
                            ->groupBy('pw.researcher_id'),
                        'fp_prev',
                        'rdb_researcher.researcher_id',
                        '=',
                        'fp_prev.researcher_id'
                    )
                    ->where('fp_prev.first_year_id', $prevYear->year_id);
                if ($scope === 'faculty') $prevFirstQ->whereIn('rdb_researcher.department_id', $facultyIds);
                if ($deptId) $prevFirstQ->where('rdb_researcher.department_id', $deptId);
                $prevTotalFirstResearchers = $prevFirstQ->count();
            }
        }

        $yoyDelta = [
            'prev_year_name' => $prevYear?->year_name,
            'weighted_score' => $this->computeMetricYoY($totalWeightedPubScore, $prevTotalWeightedPubScore),
            'budget_per_person' => $this->computeMetricYoY($avgBudgetPerPersonnel, $prevAvgBudgetPerPersonnel),
            'util_certified' => $this->computeMetricYoY($totalUtilCertified, $prevTotalUtilCertified),
            'first_researchers' => $this->computeMetricYoY($totalFirstResearchers, $prevTotalFirstResearchers),
        ];

        return view('frontend.dashboard.qa', array_merge($lookups, [
            'years' => $years,
            'selectedYearId' => $selectedYearId,
            'selectedYear' => $selectedYear,
            'deptId' => $deptId,
            'scope' => $scope,
            'qaDepartments' => $qaDepartments,
            'activeTab' => $activeTab,
            'activeFilterCount' => $activeFilterCount,
            'qaProjectTable' => $qaProjectTable,
            'qaProjectBranchTable' => $qaProjectBranchTable,
            'qaProjectFacultyBranchTable' => $qaProjectFacultyBranchTable,
            'qaProjectFacultyGroups' => $qaProjectFacultyGroups,
            'avgProjectQaScore5' => $avgProjectQaScore5,
            'qaPubTable' => $qaPubTable,
            'qaPubBranchTable' => $qaPubBranchTable,
            'qaPubFacultyBranchTable' => $qaPubFacultyBranchTable,
            'qaPubFacultyGroups' => $qaPubFacultyGroups,
            'avgPubQaScore5' => $avgPubQaScore5,
            'qaUtilTable' => $qaUtilTable,
            'firstProjectByFaculty' => $firstProjectByFaculty,
            'firstProjectByYear' => $firstProjectByYear,
            'firstProjectList' => $firstProjectList,
            'totalWeightedPubScore' => $totalWeightedPubScore,
            'totalPubItems' => $totalPubItems,
            'totalProjects' => $totalProjects,
            'totalBudget' => $totalBudget,
            'totalPersonnel' => $totalPersonnel,
            'avgBudgetPerPersonnel' => $avgBudgetPerPersonnel,
            'totalUtilCertified' => $totalUtilCertified,
            'totalUtil' => $totalUtil,
            'totalFirstResearchers' => $totalFirstResearchers,
            'yoyDelta' => $yoyDelta,
        ]));
    }
}
