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

        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Research'));
        $activeScope = $user ? $user->getActiveRoleScope() : null;
        $isFacultyCoord = $user && !$isSuperAdmin && ($user->hasRole('Faculty Coordinator') || ($activeScope && $activeScope->role_name === 'Faculty Coordinator'));
        $isResearcher = $user && !$isSuperAdmin && !$isFacultyCoord;

        $facultyDeptId = null;
        $facultyDeptName = null;
        if ($isFacultyCoord) {
            $facultyDeptId = $activeScope?->department_id ?? $user->researcher?->department_id;
            if ($facultyDeptId) {
                $facultyDeptName = \App\Models\RdbDepartment::find($facultyDeptId)?->department_name;
            }
        }

        $userRoleType = $isSuperAdmin ? 'admin' : ($isFacultyCoord ? 'faculty' : 'researcher');
        $researcherId = $user?->researcher_id;

        // Helper to apply user/faculty scope to a project query
        $scopeProject = function ($query) use ($isFacultyCoord, $facultyDeptId, $isResearcher, $researcherId, $user) {
            if ($isFacultyCoord && $facultyDeptId) {
                $query->where('department_id', $facultyDeptId);
            } elseif ($isResearcher) {
                $query->where(function ($q) use ($researcherId, $user) {
                    $q->where('user_created', $user->id);
                    if ($researcherId) {
                        $q->orWhereHas('rdbProjectWorks', function ($wq) use ($researcherId) {
                            $wq->where('rdb_project_work.researcher_id', $researcherId);
                        });
                    }
                });
            }
        };

        // 1. Projects Count & Total Budget
        $projectQuery = RdbProject::query();
        if ($selectedYearId) {
            $projectQuery->where('year_id', $selectedYearId);
        }
        $scopeProject($projectQuery);
        $totalProjects = $projectQuery->count();
        $totalProjectBudget = (float) $projectQuery->sum('pro_budget');

        // 2. Researchers
        if ($isSuperAdmin) {
            if ($selectedYearId) {
                $totalResearchers = RdbResearcher::whereHas('rdbProjects', function ($q) use ($selectedYearId) {
                    $q->where('year_id', $selectedYearId);
                })->count();
            } else {
                $totalResearchers = RdbResearcher::count();
            }
        } elseif ($isFacultyCoord && $facultyDeptId) {
            $totalResearchers = RdbResearcher::where('department_id', $facultyDeptId)->count();
        } else {
            $totalResearchers = $user?->researcher ? 1 : 0;
        }

        // 3. Publications
        $publishedQuery = RdbPublished::query();
        if ($selectedYearId) {
            $publishedQuery->where(function ($q) use ($selectedYearId) {
                $q->where('year_id', $selectedYearId)
                  ->orWhere('year_bud', $selectedYearId);
            });
        }
        if ($isFacultyCoord && $facultyDeptId) {
            $publishedQuery->where('department_id', $facultyDeptId);
        } elseif ($isResearcher) {
            $publishedQuery->where(function ($q) use ($researcherId, $user) {
                $q->where('user_created', $user->id);
                if ($researcherId) {
                    $q->orWhere('researcher_id', $researcherId)
                      ->orWhereHas('authors', function ($aq) use ($researcherId) {
                          $aq->where('rdb_published_work.researcher_id', $researcherId);
                      });
                }
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
        if ($isFacultyCoord && $facultyDeptId) {
            $dipQuery->whereHas('project', function ($q) use ($facultyDeptId) {
                $q->where('department_id', $facultyDeptId);
            });
        } elseif ($isResearcher) {
            $dipQuery->where(function ($q) use ($researcherId, $user) {
                $q->where('user_created', $user->id);
                if ($researcherId) {
                    $q->orWhere('researcher_id', $researcherId);
                }
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
        if ($isFacultyCoord && $facultyDeptId) {
            $utilizeQuery->whereHas('project', function ($q) use ($facultyDeptId) {
                $q->where('department_id', $facultyDeptId);
            });
        } elseif ($isResearcher) {
            $utilizeQuery->where(function ($q) use ($researcherId, $user) {
                $q->where('user_created', $user->id);
                if ($researcherId) {
                    $q->orWhereHas('project.rdbProjectWorks', function ($wq) use ($researcherId) {
                        $wq->where('rdb_project_work.researcher_id', $researcherId);
                    });
                }
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

        // 7. Projects by Status (Filtered by year and scope)
        $statusQuery = RdbProject::select('ps_id', DB::raw('count(*) as total'));
        if ($selectedYearId) {
            $statusQuery->where('year_id', $selectedYearId);
        }
        $scopeProject($statusQuery);
        $projectsByStatus = $statusQuery->groupBy('ps_id')
            ->with('status')
            ->get();

        // 8. Projects by Year (Last 5 Years) with Total Count & Total Budget
        $yearGroupQuery = RdbProject::select(
                'year_id',
                DB::raw('count(*) as total'),
                DB::raw('COALESCE(SUM(pro_budget), 0) as total_budget')
            )
            ->whereNotNull('year_id');
        $scopeProject($yearGroupQuery);
        $projectsByYear = $yearGroupQuery->groupBy('year_id')
            ->with('year')
            ->orderBy('year_id', 'desc')
            ->limit(5)
            ->get()
            ->reverse()
            ->values();

        // 9. Recent 5 Projects (Filtered by year and scope)
        $recentQuery = RdbProject::with(['year', 'status', 'department']);
        if ($selectedYearId) {
            $recentQuery->where('year_id', $selectedYearId);
        }
        $scopeProject($recentQuery);
        $recentProjects = $recentQuery->orderBy('pro_id', 'desc')
            ->take(5)
            ->get();

        // 10. YoY Delta calculation for Backend KPI
        $prevYear = null;
        $prevTotalProjects = 0;
        $prevTotalBudget = 0.0;
        $prevTotalPublished = 0;

        if ($selectedYear) {
            $prevYear = $availableYears->first(fn($y) => (int)$y->year_name < (int)$selectedYear->year_name);
            if ($prevYear) {
                $prevProjQ = RdbProject::where('year_id', $prevYear->year_id);
                $scopeProject($prevProjQ);
                $prevTotalProjects = $prevProjQ->count();
                $prevTotalBudget = (float) $prevProjQ->sum('pro_budget');

                $prevPubQ = RdbPublished::where(function ($q) use ($prevYear) {
                    $q->where('year_id', $prevYear->year_id)
                      ->orWhere('year_bud', $prevYear->year_id);
                });
                if ($isFacultyCoord && $facultyDeptId) {
                    $prevPubQ->where('department_id', $facultyDeptId);
                } elseif ($isResearcher) {
                    $prevPubQ->where(function ($q) use ($researcherId, $user) {
                        $q->where('user_created', $user->id);
                        if ($researcherId) {
                            $q->orWhere('researcher_id', $researcherId);
                        }
                    });
                }
                $prevTotalPublished = $prevPubQ->count();
            }
        }

        $yoyDelta = [
            'prev_year_name' => $prevYear?->year_name,
            'project_prev' => $prevTotalProjects,
            'project_diff' => $totalProjects - $prevTotalProjects,
            'project_pct' => $prevTotalProjects > 0 ? round((($totalProjects - $prevTotalProjects) / $prevTotalProjects) * 100, 1) : null,
            'budget_prev' => $prevTotalBudget,
            'budget_diff' => $totalProjectBudget - $prevTotalBudget,
            'budget_pct' => $prevTotalBudget > 0 ? round((($totalProjectBudget - $prevTotalBudget) / $prevTotalBudget) * 100, 1) : null,
            'published_prev' => $prevTotalPublished,
            'published_diff' => $totalPublished - $prevTotalPublished,
            'published_pct' => $prevTotalPublished > 0 ? round((($totalPublished - $prevTotalPublished) / $prevTotalPublished) * 100, 1) : null,
        ];

        // 11. Ranked Breakdown by Status with Share Bars
        $statusRankings = [];
        $maxStatusCount = 0;
        foreach ($projectsByStatus as $st) {
            if ($st->status) {
                $statusRankings[] = [
                    'name' => $st->status->ps_name,
                    'color' => $st->status->ps_color ?? '#2563EB',
                    'count' => (int) $st->total,
                ];
                if ((int)$st->total > $maxStatusCount) {
                    $maxStatusCount = (int)$st->total;
                }
            }
        }
        usort($statusRankings, fn($a, $b) => $b['count'] <=> $a['count']);

        return view('backend.site.index', compact(
            'availableYears',
            'selectedYear',
            'selectedYearId',
            'userRoleType',
            'facultyDeptName',
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
            'recentProjects',
            'yoyDelta',
            'statusRankings',
            'maxStatusCount'
        ));
    }
}