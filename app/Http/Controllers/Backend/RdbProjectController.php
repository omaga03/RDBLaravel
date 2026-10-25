<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProject;
use App\Models\RdbGroupproject;
use App\Models\RdbProjectType;
use App\Models\RdbDepartment;
use App\Models\RdbYear;
use App\Models\RdbStrategic;
use App\Models\RdbProjectStatus;
use App\Models\RdbResearcher;
use App\Models\RdbProjectPosition;
use App\Models\RdbProjectWork;
use App\Models\RdbProjectFiles;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploadService;
use App\Http\Requests\Backend\StoreProjectRequest;
use App\Http\Requests\Backend\UpdateProjectRequest;
use App\Http\Requests\Backend\StoreProjectResearcherRequest;
use App\Http\Requests\Backend\UpdateProjectResearcherRequest;
use App\Http\Requests\Backend\StoreProjectFileRequest;
use App\Http\Requests\Backend\UpdateProjectFileRequest;
use App\Http\Requests\Backend\UpdateProjectStatusRequest;
use App\Http\Requests\Backend\UploadProjectAbstractRequest;
use App\Http\Requests\Backend\UploadProjectReportRequest;


use App\Traits\ExportsCsvSafely;

class RdbProjectController extends Controller
{
    use ExportsCsvSafely;

    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbProject::class);
        $query = RdbProject::with([
            'year', 
            'type', 
            'typeSub',
            'status', 
            'department', 
            'rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            },
            'rdbProjectWorks.researcher.prefix',
            'rdbProjectWorks.position',
            'createdBy',
            'updatedBy'
        ]);

        // Apply RBAC Scoping:
        // 1. Super Admin ('The Creator') and 'Admin Project' can view all projects across the university.
        // 2. Legacy 'Admin' manages own group data.
        // 3. 'Faculty Coordinator' manages projects in their department.
        // 4. 'Researcher' views projects they authored/created or projects in their faculty.
        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->hasRole('The Creator') && !$currentUser->hasRole('Admin Project')) {
            if ($currentUser->hasRole('Admin')) {
                $adminIds = \App\Models\User::whereHas('roles', function ($q) {
                    $q->where('item_name', 'Admin');
                })->pluck('id');
                $query->whereIn('user_created', $adminIds);
            } elseif ($currentUser->hasRole('Faculty Coordinator')) {
                $deptId = $currentUser->getActiveRoleScope()?->department_id ?? $currentUser->researcher?->department_id;
                if ($deptId) {
                    $query->where('department_id', $deptId);
                }
            } elseif ($currentUser->hasRole('Researcher') || $currentUser->researcher_id) {
                $researcherId = $currentUser->researcher_id;
                $query->where(function($q) use ($researcherId, $currentUser) {
                    $q->where('user_created', $currentUser->id);
                    if ($researcherId) {
                        $q->orWhereHas('rdbProjectWorks', function($wq) use ($researcherId) {
                            $wq->where('rdb_project_work.researcher_id', $researcherId);
                        });
                    }
                });
            }
        }

        // Workflow Status filter (e.g. Drafts & Pending)
        if ($request->filled('workflow_status')) {
            if ($request->workflow_status === 'unapproved') {
                $query->whereIn('workflow_status', ['draft', 'pending_review', 'rejected']);
            } else {
                $query->where('workflow_status', $request->workflow_status);
            }
        }

        // General Search - searches across ALL fields
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $searchWords = preg_split('/\s+/', $searchTerm);
            
            $query->where(function($q) use ($searchTerm, $searchWords) {
                $q->where('pro_nameTH', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pro_nameEN', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pro_keyword', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pro_code', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pro_abstract', 'like', '%' . $searchTerm . '%');
                
                $q->orWhereHas('type', function($tq) use ($searchTerm) {
                    $tq->where('pt_name', 'like', '%' . $searchTerm . '%');
                });
                
                $q->orWhereHas('typeSub', function($tsq) use ($searchTerm) {
                    $tsq->where('pts_name', 'like', '%' . $searchTerm . '%');
                });
                
                $q->orWhereHas('year', function($yq) use ($searchTerm) {
                    $yq->where('year_name', 'like', '%' . $searchTerm . '%');
                });
                
                $q->orWhereHas('department', function($dq) use ($searchTerm) {
                    $dq->where('department_nameTH', 'like', '%' . $searchTerm . '%')
                       ->orWhere('department_nameEN', 'like', '%' . $searchTerm . '%');
                });
                
                $q->orWhereHas('researchers', function($rq) use ($searchWords) {
                    foreach ($searchWords as $word) {
                        $rq->where(function($wq) use ($word) {
                            $wq->where('researcher_fname', 'like', '%' . $word . '%')
                               ->orWhere('researcher_lname', 'like', '%' . $word . '%');
                        });
                    }
                });
            });
        }

        // Project ID / Title Autocomplete Search
        if ($request->filled('pro_id') && $request->pro_id !== 'pre') {
            $query->where('pro_id', $request->pro_id);
        } elseif ($request->filled('pro_nameTH')) {
            $proName = trim($request->pro_nameTH);
            $query->where(function($q) use ($proName) {
                $q->where('pro_nameTH', 'like', "%{$proName}%")
                  ->orWhere('pro_nameEN', 'like', "%{$proName}%");
            });
        }

        // Title Multi-Keyword Search (กลุ่มคำคั่นด้วยช่องว่าง + ตัวเลือก เจอทั้งหมด/เจอบางคำ)
        if ($request->filled('title_keywords')) {
            $keywords = array_values(array_filter(preg_split('/\s+/', trim($request->title_keywords))));
            $mode = $request->input('title_keywords_mode', 'all');

            if (!empty($keywords)) {
                if ($mode === 'all') {
                    $query->where(function($q) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $q->where(function($sub) use ($kw) {
                                $sub->where('pro_nameTH', 'like', "%{$kw}%")
                                    ->orWhere('pro_nameEN', 'like', "%{$kw}%");
                            });
                        }
                    });
                } else {
                    $query->where(function($q) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $q->orWhere(function($sub) use ($kw) {
                                $sub->where('pro_nameTH', 'like', "%{$kw}%")
                                    ->orWhere('pro_nameEN', 'like', "%{$kw}%");
                            });
                        }
                    });
                }
            }
        }
        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('pt_id')) {
            $query->where('pt_id', $request->pt_id);
        }
        if ($request->filled('pts_id')) {
            $query->where('pts_id', $request->pts_id);
        }
        if ($request->filled('pgroup_id')) {
            $query->where('pgroup_id', $request->pgroup_id);
        }
        if ($request->filled('ps_id')) {
            $query->where('ps_id', $request->ps_id);
        }
        if ($request->filled('pro_code')) {
            $query->where('pro_code', 'like', '%' . $request->pro_code . '%');
        }
        // Budget range validation
        $hasBudgetMin = $request->filled('budget_min');
        $hasBudgetMax = $request->filled('budget_max');

        if ($hasBudgetMin && $hasBudgetMax) {
            if ($request->budget_max < $request->budget_min) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'งบประมาณสูงสุดต้องไม่น้อยกว่างบประมาณเริ่มต้น');
            }
        }

        if ($hasBudgetMin) {
            $query->where('pro_budget', '>=', $request->budget_min);
        }
        if ($hasBudgetMax) {
            $query->where('pro_budget', '<=', $request->budget_max);
        }
        if ($request->filled('researcher_id') && $request->researcher_id !== 'pre') {
            $query->whereHas('researchers', function($rq) use ($request) {
                $rq->where('rdb_researcher.researcher_id', $request->researcher_id);
            });
        } elseif ($request->filled('researcher_name')) {
            $cleanName = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', trim($request->researcher_name));
            $searchWords = preg_split('/\s+/', $cleanName);
            $query->whereHas('researchers', function($rq) use ($searchWords) {
                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    $rq->where(function($wq) use ($word) {
                        $wq->where('researcher_fname', 'like', '%' . $word . '%')
                           ->orWhere('researcher_lname', 'like', '%' . $word . '%');
                    });
                }
            });
        }
        if ($request->filled('pro_abstract')) {
            $words = preg_split('/[\s\/,]+/', trim($request->pro_abstract));
            $query->where(function($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_abstract', 'like', '%' . $word . '%');
                }
            });
        }
        if ($request->filled('pro_keyword')) {
            $words = preg_split('/[\s\/,]+/', trim($request->pro_keyword));
            $query->where(function($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_keyword', 'like', '%' . $word . '%');
                }
            });
        }
        // Date range validation
        $hasDateStart = $request->filled('date_start');
        $hasDateEnd = $request->filled('date_end');

        if ($hasDateStart || $hasDateEnd) {
            if (!$hasDateStart || !$hasDateEnd) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'กรุณาระบุทั้งวันที่เริ่มและวันที่สิ้นสุด');
            }

            if ($request->date_end < $request->date_start) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น');
            }

            $query->whereDate('pro_date_start', '>=', $request->date_start)
                  ->whereDate('pro_date_end', '<=', $request->date_end);
        }
        if ($request->filled('pro_note')) {
            $words = preg_split('/[\s\/,]+/', trim($request->pro_note));
            $query->where(function($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_note', 'like', '%' . $word . '%');
                }
            });
        }

        $projects = $query->orderBy('pro_id', 'desc')->paginate(10);
        
        $searchTerm = $request->filled('search') ? trim($request->search) : null;
        if ($searchTerm) {
            $searchWords = preg_split('/\s+/', $searchTerm);
            
            foreach ($projects as $project) {
                $matchSources = [];
                
                if (stripos($project->pro_nameTH ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'ชื่อโครงการ (ไทย)';
                }
                if (stripos($project->pro_nameEN ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'ชื่อโครงการ (อังกฤษ)';
                }
                if (stripos($project->pro_code ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'รหัสโครงการ';
                }
                if (stripos($project->pro_keyword ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'คำสำคัญ';
                }
                if (stripos($project->pro_abstract ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'บทคัดย่อ';
                }
                
                if ($project->type && stripos($project->type->pt_name ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'ประเภททุน';
                }
                
                if ($project->typeSub && stripos($project->typeSub->pts_name ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'ประเภททุนย่อย';
                }
                
                if ($project->year && stripos($project->year->year_name ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'ปีงบประมาณ';
                }
                
                if ($project->department) {
                    if (stripos($project->department->department_nameTH ?? '', $searchTerm) !== false ||
                        stripos($project->department->department_nameEN ?? '', $searchTerm) !== false) {
                        $matchSources[] = 'หน่วยงาน';
                    }
                }
                
                $matchedResearchers = [];
                foreach ($project->rdbProjectWorks as $work) {
                    $researcher = $work->researcher;
                    if (!$researcher) continue;
                    $matched = true;
                    foreach ($searchWords as $word) {
                        if (empty($word)) continue;
                        if (stripos($researcher->researcher_fname ?? '', $word) === false &&
                            stripos($researcher->researcher_lname ?? '', $word) === false &&
                            stripos($researcher->researcher_fnameEN ?? '', $word) === false &&
                            stripos($researcher->researcher_lnameEN ?? '', $word) === false) {
                            $matched = false;
                            break;
                        }
                    }
                    if ($matched) {
                        $posName = $work->position->position_nameTH ?? 'นักวิจัย';
                        $fullName = trim(($researcher->prefix->prefix_nameTH ?? '') . $researcher->researcher_fname . ' ' . $researcher->researcher_lname);
                        $matchedResearchers[] = "นักวิจัย ({$posName}): {$fullName}";
                    }
                }
                if (!empty($matchedResearchers)) {
                    foreach ($matchedResearchers as $mr) {
                        $matchSources[] = $mr;
                    }
                }
                
                $project->match_sources = $matchSources;
            }
        }
        
        $years = RdbYear::orderBy('year_id', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $types = RdbProjectType::all();
        $typeSubs = \App\Models\RdbProjectTypeSub::all();
        $groups = RdbGroupproject::all();
        $statuses = RdbProjectStatus::all();

        return view('backend.rdb_project.index', compact('projects', 'years', 'departments', 'types', 'typeSubs', 'groups', 'statuses'));
    }

    /**
     * Export projects to CSV (Excel compatible)
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', RdbProject::class);

        $query = RdbProject::with(['year', 'type', 'typeSub', 'status', 'department', 'researchers', 'group']);

        if ($request->filled('search')) {
            $words = preg_split('/[\s\/,]+/', trim($request->search));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_nameTH', 'like', "%{$word}%")
                      ->orWhere('pro_nameEN', 'like', "%{$word}%")
                      ->orWhere('pro_code', 'like', "%{$word}%")
                      ->orWhere('pro_keyword', 'like', "%{$word}%");
                }
            });
        }
        if ($request->filled('pro_year')) {
            $query->where('pro_year', $request->pro_year);
        }
        if ($request->filled('pt_id')) {
            $query->where('pt_id', $request->pt_id);
        }
        if ($request->filled('pro_status')) {
            $query->where('pro_status', $request->pro_status);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $filename = 'projects_' . date('Y-m-d_His') . '.csv';
        $headers = $this->csvHeaders($filename);

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'รหัสโครงการ',
                'ปีงบประมาณ',
                'ชื่อโครงการ (ไทย)',
                'ชื่อโครงการ (อังกฤษ)',
                'ประเภททุน',
                'ประเภททุนย่อย',
                'งบประมาณ (บาท)',
                'หน่วยงาน/คณะ',
                'หัวหน้าโครงการ',
                'สถานะ',
                'วันที่เริ่มต้น',
                'วันที่สิ้นสุด'
            ]);

            $query->orderBy('pro_id', 'desc')->chunk(250, function ($projects) use ($handle) {
                foreach ($projects as $p) {
                    $headResearcher = $p->researchers->first();
                    $headName = $headResearcher ? (($headResearcher->prefix->prefix_nameTH ?? '') . $headResearcher->researcher_fname . ' ' . $headResearcher->researcher_lname) : '-';

                    fputcsv($handle, $this->sanitizeCsvRow([
                        $p->pro_id,
                        $p->pro_code ?? '-',
                        $p->year->year_name ?? '-',
                        strip_tags(html_entity_decode($p->pro_nameTH ?? '', ENT_QUOTES, 'UTF-8')),
                        strip_tags(html_entity_decode($p->pro_nameEN ?? '', ENT_QUOTES, 'UTF-8')),
                        $p->type->pt_name ?? '-',
                        $p->typeSub->pts_name ?? '-',
                        $p->pro_budget ?? 0,
                        $p->department->department_nameTH ?? '-',
                        $headName,
                        $p->status->ps_name ?? '-',
                        $p->pro_date_start ?? '-',
                        $p->pro_date_end ?? '-'
                    ]));
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', RdbProject::class);
        
        $groups = RdbGroupproject::all();
        $types = RdbProjectType::all();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $years = RdbYear::orderBy('year_id', 'desc')->get();
        $strategics = RdbStrategic::all();
        $statuses = RdbProjectStatus::all();
        $positions = RdbProjectPosition::all();

        return view('backend.rdb_project.create', compact('groups', 'types', 'departments', 'years', 'strategics', 'statuses', 'positions'));
    }

    /**
     * Store a newly created resource in storage (SAFE - ใช้ validated data)
     */
    public function store(StoreProjectRequest $request)
    {
        $this->authorize('create', RdbProject::class);

        try {
            $validated = $request->validated();
            
            // Extract researcher data before creating project
            $researcherId = $validated['researcher_id'] ?? null;
            $positionId = $validated['position_id'] ?? null;
            $ratio = $validated['ratio'] ?? null;
            
            // Remove researcher fields from project data
            unset($validated['researcher_id'], $validated['position_id'], $validated['ratio']);
            
            // Set user and timestamp fields
            $validated['user_created'] = auth()->id();
            $validated['user_updated'] = auth()->id();
            
            // Ensure created_at is set
            if (empty($validated['created_at'])) {
                $validated['created_at'] = now()->toDateTimeString();
            }

            // Create project
            $project = RdbProject::create($validated);

            // If researcher data is provided, create project_work record
            if ($researcherId && $positionId && $ratio !== null) {
                $work = new RdbProjectWork();
                $work->pro_id = $project->pro_id;
                $work->researcher_id = $researcherId;
                $work->position_id = $positionId;
                $work->ratio = $ratio;
                $work->user_created = auth()->id();
                $work->created_at = now()->toDateTimeString();
                $work->save();
            }

            return redirect()->route('backend.rdb_project.show', $project->pro_id)
                ->with('success', 'สร้างโครงการเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $project = RdbProject::with([
            'year', 
            'type', 
            'status', 
            'department', 
            'strategic', 
            'researchers', 
            'group',
            'parentProject',
            'subProjects',
            'rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            },
            'rdbProjectWorks.researcher',
            'rdbProjectWorks.researcher.prefix',
            'publisheds',
            'dips',
            'utilizations',
            'files',
            'createdBy',
            'updatedBy'
        ])->findOrFail($id);
        $this->authorize('view', $project);

        $project->increment('pro_count_page');

        $existingResearcherIds = $project->rdbProjectWorks->pluck('researcher_id')->toArray();
        
        $positions = RdbProjectPosition::all();
        $statuses = RdbProjectStatus::all();

        $takenPositions = $project->rdbProjectWorks->pluck('position_id')->toArray();
        $hasDirector = in_array(1, $takenPositions);
        $hasHead = in_array(2, $takenPositions);

        return view('backend.rdb_project.show', compact('project', 'positions', 'statuses', 'existingResearcherIds', 'takenPositions', 'hasDirector', 'hasHead'));
    }

    /**
     * Search researchers via AJAX for TomSelect
     */
    public function searchResearchers(Request $request)
    {
        $this->authorize('viewAny', RdbResearcher::class);
        $rawQuery = trim($request->get('q', ''));
        
        if (mb_strlen($rawQuery) < 2) {
            return response()->json(['results' => []]);
        }

        $cleanTerm = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', $rawQuery);
        $words = array_values(array_filter(preg_split('/\s+/', $cleanTerm)));

        $researchers = RdbResearcher::with(['prefix', 'department'])
            ->whereNotNull('researcher_fname')
            ->where('researcher_fname', '!=', '')
            ->where(function($q) use ($cleanTerm, $words) {
                $q->where('researcher_fname', 'like', "%{$cleanTerm}%")
                  ->orWhere('researcher_lname', 'like', "%{$cleanTerm}%")
                  ->orWhere('researcher_fnameEN', 'like', "%{$cleanTerm}%")
                  ->orWhere('researcher_lnameEN', 'like', "%{$cleanTerm}%")
                  ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$cleanTerm}%"]);

                if (count($words) > 1) {
                    $q->orWhere(function($sub) use ($words) {
                        foreach ($words as $w) {
                            $sub->where(function($wq) use ($w) {
                                $wq->where('researcher_fname', 'like', "%{$w}%")
                                   ->orWhere('researcher_lname', 'like', "%{$w}%")
                                   ->orWhere('researcher_fnameEN', 'like', "%{$w}%")
                                   ->orWhere('researcher_lnameEN', 'like', "%{$w}%");
                            });
                        }
                    });
                }
            })
            ->limit(20)
            ->get()
            ->map(function($r) {
                $prefix = $r->prefix->prefix_nameTH ?? '';
                $deptName = $r->department->department_nameTH ?? $r->department->department_nameEN ?? $r->researcher_note ?? '-';
                $fullName = "{$prefix}{$r->researcher_fname} {$r->researcher_lname} [{$deptName}]";
                return [
                    'id' => $r->researcher_id,
                    'value' => $r->researcher_id,
                    'text' => $fullName,
                    'prefix' => $prefix,
                    'fname' => $r->researcher_fname,
                    'lname' => $r->researcher_lname,
                    'department' => $deptName,
                ];
            });

        return response()->json(['results' => $researchers]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $project = RdbProject::with([
            'year', 
            'type', 
            'typeSub',
            'status', 
            'department', 
            'strategic', 
            'group',
            'createdBy',
            'updatedBy'
        ])->findOrFail($id);
        $this->authorize('update', $project); // Authorize here

        $groups = RdbGroupproject::all();
        $types = RdbProjectType::all();
        $departments = RdbDepartment::all();
        $years = RdbYear::orderBy('year_id', 'desc')->get();
        $strategics = RdbStrategic::all();
        $statuses = RdbProjectStatus::all();
        $categories = RdbDepartmentCategory::all();

        return view('backend.rdb_project.edit', compact('project', 'groups', 'types', 'departments', 'years', 'strategics', 'statuses', 'categories'));
    }

    /**
     * Update the specified resource in storage (SAFE - ใช้ validated data)
     */
    public function update(UpdateProjectRequest $request, $id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project);

        try {
            $validated = $request->validated();
            $validated['user_updated'] = auth()->id();
            
            // If ps_id is null or empty in request, don't overwrite existing ps_id
            if (!isset($validated['ps_id']) || $validated['ps_id'] === null) {
                if (!$request->has('ps_id') || $request->input('ps_id') === '') {
                    unset($validated['ps_id']);
                }
            }
            
            $project->update($validated);

            return redirect()->back()->with('success', 'แก้ไขโครงการเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update project status (ps_id) directly
     */
    public function updateStatus(UpdateProjectStatusRequest $request, $id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project);

        $validated = $request->validated();

        $project->ps_id = $validated['ps_id'];
        $project->user_updated = auth()->id();
        $project->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ปรับปรุงสถานะโครงการเรียบร้อยแล้ว',
                'status' => $project->status,
            ]);
        }

        return redirect()->back()->with('success', 'ปรับปรุงสถานะโครงการเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('delete', $project); // Enforce RBAC

        $project->data_show = 0;
        $project->user_updated = auth()->id();
        $project->save();

        return redirect()->route('backend.rdb_project.index')->with('success', 'ลบโครงการเรียบร้อยแล้ว (ซ่อนการแสดงผล)');
    }

    public function storeResearcher(StoreProjectResearcherRequest $request, $id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project); // Authorize to update project via adding researcher
        $validated = $request->validated();

        $exists = RdbProjectWork::where('pro_id', $id)->where('researcher_id', $validated['researcher_id'])->exists();
        if ($exists) {
             return redirect()->back()->with('error', 'Researcher already exists in this project.');
        }

        $currentTotalRatio = RdbProjectWork::where('pro_id', $id)->sum('ratio');
        if (($currentTotalRatio + $validated['ratio']) > 100) {
            return redirect()->back()->with('error', "Total ratio exceeds 100%. Current total: {$currentTotalRatio}%");
        }

        $work = new RdbProjectWork();
        $work->pro_id = $id;
        $work->researcher_id = $validated['researcher_id'];
        $work->position_id = $validated['position_id'];
        $work->ratio = $validated['ratio'];
        $work->user_created = auth()->id(); // Manually set
        $work->created_at = now(); // Manually set (assuming RdbProjectWork has timestamps=false)
        $work->save();

        if (in_array($validated['position_id'], [RdbProjectPosition::DIRECTOR, RdbProjectPosition::HEAD])) {
            $researcher = RdbResearcher::find($validated['researcher_id']);
            if ($researcher) {
                $project->department_id = $researcher->department_id;
                $project->depcat_id = $researcher->depcat_id;
                $project->user_updated = auth()->id();
                $project->save();
            }
        }

        return redirect()->back()->with('success', 'Researcher added successfully.');
    }

    public function updateResearcher(UpdateProjectResearcherRequest $request, $id, $rid)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project); // Authorize to update project via updating researcher
        $validated = $request->validated();

        $currentTotalRatio = RdbProjectWork::where('pro_id', $id)
                                           ->where('researcher_id', '!=', $rid)
                                           ->sum('ratio');
        
        if (($currentTotalRatio + $validated['ratio']) > 100) {
            return redirect()->back()->with('error', "Total ratio exceeds 100%. Current total (others): {$currentTotalRatio}%");
        }

        RdbProjectWork::where('pro_id', $id)
                      ->where('researcher_id', $rid)
                      ->update(array_merge($validated, ['user_updated' => auth()->id(), 'updated_at' => now()])); // Manually set updated_at

        if (in_array($validated['position_id'], [RdbProjectPosition::DIRECTOR, RdbProjectPosition::HEAD])) {
             $researcher = RdbResearcher::find($rid);
             $project = RdbProject::find($id);
             if ($researcher && $project) {
                 $project->department_id = $researcher->department_id;
                 $project->depcat_id = $researcher->depcat_id;
                 $project->user_updated = auth()->id();
                 $project->save();
             }
        }

        return redirect()->back()->with('success', 'Researcher updated successfully.');
    }

    public function destroyResearcher($id, $rid)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project); // Authorize to update project via deleting researcher

        RdbProjectWork::where('pro_id', $id)
            ->where('researcher_id', $rid)
            ->delete();

        return redirect()->back()->with('success', 'ลบนักวิจัยเรียบร้อยแล้ว');
    }



    /**
     * Toggle additional file visibility
     */
    public function toggleFileStatus($id, $fid)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project); // Authorize to update project via toggling file status
        
        $file = RdbProjectFiles::where('pro_id', $id)->where('id', $fid)->firstOrFail();
        
        $file->rf_files_show = $file->rf_files_show == 1 ? 0 : 1;
        $file->user_updated = auth()->id();
        $file->save();
        
        return response()->json([
            'success' => true,
            'status' => (int) $file->rf_files_show,
            'message' => 'ปรับปรุงสถานะการแสดงผลไฟล์เรียบร้อยแล้ว'
        ]);
    }

    /**
     * Upload abstract file - use UUID for new uploads
     */
    public function uploadAbstract(UploadProjectAbstractRequest $request, $id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project); // Authorize to update project via abstract upload
        $validated = $request->validated();

        try {
            if (!empty($project->pro_abstract_file_path) && Storage::disk('public')->exists($project->pro_abstract_file_path)) {
                Storage::disk('public')->delete($project->pro_abstract_file_path);
            }

            $file = $validated['pro_abstract_file'];
            $fileInfo = $this->fileUploadService->storeFile($file, 'abstracts');

            $project->pro_abstract_file_path = $fileInfo['path'];
            $project->pro_abstract_size = $fileInfo['size'];
            $project->pro_abstract_mime = $fileInfo['mime_type'];
            $project->pro_abstract_file = $fileInfo['safe_name'];
            $project->user_updated = auth()->id();
            $project->save();

            return redirect()->back()->with('success', 'อัปโหลดไฟล์บทคัดย่อเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['pro_abstract_file' => $e->getMessage()]);
        }
    }

    /**
     * Delete abstract file
     */
    public function deleteAbstract($id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project); // Authorize to update project via abstract deletion
        
        if ($project->pro_abstract_file_path && Storage::disk('public')->exists($project->pro_abstract_file_path)) {
            Storage::disk('public')->delete($project->pro_abstract_file_path);
        }

        if ($project->pro_abstract_file && 
            $project->pro_abstract_file !== $project->pro_abstract_file_path &&
            Storage::disk('public')->exists('uploads/projects/' . $project->pro_abstract_file)) {
            Storage::disk('public')->delete('uploads/projects/' . $project->pro_abstract_file);
        }

        $project->pro_abstract_file = null;
        $project->pro_abstract_file_path = null;
        $project->pro_abstract_size = null;
        $project->pro_abstract_mime = null;
        $project->user_updated = auth()->id();
        $project->save();

        return redirect()->back()->with('success', 'ลบไฟล์บทคัดย่อเรียบร้อยแล้ว');
    }

    public function uploadReport(UploadProjectReportRequest $request, $id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project); // Authorize to update project via report upload
        $validated = $request->validated();

        try {
            if (!empty($project->pro_file_path) && Storage::disk('public')->exists($project->pro_file_path)) {
                Storage::disk('public')->delete($project->pro_file_path);
            }

            $file = $validated['pro_file'];
            $fileInfo = $this->fileUploadService->storeFile($file, 'reports');

            $project->pro_file_path = $fileInfo['path'];
            $project->pro_file_size = $fileInfo['size'];
            $project->pro_file_mime = $fileInfo['mime_type'];
            $project->pro_file = $fileInfo['safe_name'];

            // Public visibility: 0 = เปิด, 1 = ปิด, 2 = ร่าง (Default: 0 = เปิด)
            if ($request->filled('pro_file_show') || $request->has('pro_file_show')) {
                $rawShow = $request->input('pro_file_show');
                if ($rawShow === 'true' || $rawShow === true) {
                    $project->pro_file_show = 0; // เปิด
                } elseif ($rawShow === 'false' || $rawShow === false) {
                    $project->pro_file_show = 1; // ปิด
                } else {
                    $val = (int) $rawShow;
                    $project->pro_file_show = in_array($val, [0, 1, 2], true) ? $val : 0;
                }
            } else {
                $project->pro_file_show = 0; // Default: เปิด
            }

            // หากมีการแนบไฟล์เล่มรายงานฉบับสมบูรณ์ ให้ปรับสถานะเป็น "ดำเนินการเสร็จสิ้น" ทันที
            $completedStatus = \App\Models\RdbProjectStatus::where('ps_name', 'like', '%เสร็จสิ้น%')->first();
            $project->ps_id = $completedStatus ? $completedStatus->ps_id : 2;

            $project->user_updated = auth()->id();
            $project->save();

            $statusName = $completedStatus ? $completedStatus->ps_name : 'ดำเนินการเสร็จสิ้น';
            return redirect()->back()->with('success', 'อัปโหลดรายงานฉบับสมบูรณ์เรียบร้อยแล้ว และระบบได้ปรับสถานะโครงการเป็น "' . $statusName . '" อัตโนมัติ');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['pro_file' => $e->getMessage()]);
        }
    }

    /**
     * View abstract file (with counter)
     */
    public function viewAbstract($id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('view', $project);
        $filePath = $project->getAbstractFilePath();
        
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $project->increment('pro_count_abs');
            return Storage::disk('public')->response($filePath);
        }
        if ($filePath && file_exists(public_path('storage/' . $filePath))) {
            $project->increment('pro_count_abs');
            return response()->file(public_path('storage/' . $filePath));
        }
        if ($filePath && file_exists(public_path($filePath))) {
            $project->increment('pro_count_abs');
            return response()->file(public_path($filePath));
        }

        // Support legacy folder: datafiles/{pro_id}/{pro_abstract_file}
        if (!empty($project->pro_abstract_file)) {
            $legacyRelative = 'datafiles/' . $project->pro_id . '/' . $project->pro_abstract_file;
            if (Storage::disk('public')->exists($legacyRelative)) {
                $project->increment('pro_count_abs');
                return Storage::disk('public')->response($legacyRelative);
            }
            if (file_exists(public_path($legacyRelative))) {
                $project->increment('pro_count_abs');
                return response()->file(public_path($legacyRelative));
            }
            if (file_exists(base_path('yii2_rdb-main/frontend/web/' . $legacyRelative))) {
                $project->increment('pro_count_abs');
                return response()->file(base_path('yii2_rdb-main/frontend/web/' . $legacyRelative));
            }
        }

        abort(404, 'ไม่พบไฟล์บทคัดย่อบนเซิร์ฟเวอร์');
    }

    /**
     * View full report file (with counter)
     */
    public function viewReport($id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('view', $project);
        $filePath = $project->getReportFilePath();
        
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $project->increment('pro_count_full');
            return Storage::disk('public')->response($filePath);
        }
        if ($filePath && file_exists(public_path('storage/' . $filePath))) {
            $project->increment('pro_count_full');
            return response()->file(public_path('storage/' . $filePath));
        }
        if ($filePath && file_exists(public_path($filePath))) {
            $project->increment('pro_count_full');
            return response()->file(public_path($filePath));
        }

        // Support legacy folder: datafiles/{pro_id}/{pro_file}
        if (!empty($project->pro_file)) {
            $legacyRelative = 'datafiles/' . $project->pro_id . '/' . $project->pro_file;
            if (Storage::disk('public')->exists($legacyRelative)) {
                $project->increment('pro_count_full');
                return Storage::disk('public')->response($legacyRelative);
            }
            if (file_exists(public_path($legacyRelative))) {
                $project->increment('pro_count_full');
                return response()->file(public_path($legacyRelative));
            }
            if (file_exists(base_path('yii2_rdb-main/frontend/web/' . $legacyRelative))) {
                $project->increment('pro_count_full');
                return response()->file(base_path('yii2_rdb-main/frontend/web/' . $legacyRelative));
            }
        }

        abort(404, 'ไม่พบไฟล์รายงานบนเซิร์ฟเวอร์');
    }

    /**
     * AJAX Search for Projects (Centralized)
     */
    public function search(Request $request)
    {
        $this->authorize('viewAny', RdbProject::class);
        $q = trim($request->get('q', ''));
        
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $cleanName = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', $q);
        $words = array_values(array_filter(preg_split('/\s+/', $cleanName)));

        $projects = RdbProject::with([
                'year',
                'department',
                'type',
                'typeSub',
                'rdbProjectWorks' => function($wq) {
                    $wq->orderBy('position_id', 'asc');
                },
                'rdbProjectWorks.researcher.prefix',
                'rdbProjectWorks.researcher.department',
                'rdbProjectWorks.position',
                'utilizations'
            ])
            ->withCount('utilizations')
            ->where(function($sub) use ($q, $cleanName, $words) {
                $sub->where('pro_nameTH', 'like', "%{$q}%")
                    ->orWhere('pro_nameEN', 'like', "%{$q}%")
                    ->orWhere('pro_code', 'like', "%{$q}%")
                    ->orWhereHas('type', fn($t) => $t->where('pt_name', 'like', "%{$q}%"))
                    ->orWhereHas('typeSub', fn($ts) => $ts->where('pts_name', 'like', "%{$q}%"))
                    ->orWhereHas('rdbProjectWorks.researcher', function($rq) use ($cleanName, $words) {
                        $rq->where('researcher_fname', 'like', "%{$cleanName}%")
                           ->orWhere('researcher_lname', 'like', "%{$cleanName}%")
                           ->orWhere('researcher_fnameEN', 'like', "%{$cleanName}%")
                           ->orWhere('researcher_lnameEN', 'like', "%{$cleanName}%")
                           ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$cleanName}%"]);

                        if (count($words) > 1) {
                            $rq->orWhere(function($wSub) use ($words) {
                                foreach ($words as $w) {
                                    $wSub->where(function($wq) use ($w) {
                                        $wq->where('researcher_fname', 'like', "%{$w}%")
                                           ->orWhere('researcher_lname', 'like', "%{$w}%");
                                    });
                                }
                            });
                        }
                    });
            })
            ->orderBy('pro_id', 'desc')
            ->limit(20)
            ->get()
            ->map(function($p) {
                $researchersList = $p->rdbProjectWorks->map(function($work) {
                    $r = $work->researcher;
                    if (!$r) return null;
                    $prefix = $r->prefix->prefix_nameTH ?? '';
                    return [
                        'researcher_id' => $r->researcher_id,
                        'full_name' => trim("{$prefix}{$r->researcher_fname} {$r->researcher_lname}"),
                        'position_id' => $work->position_id,
                        'position_name' => $work->position->position_nameTH ?? 'ผู้ร่วมวิจัย',
                        'ratio' => $work->ratio,
                    ];
                })->filter()->values();

                $allResearchersText = $researchersList->pluck('full_name')->implode(', ');
                $headResearcher = $researchersList->first()['full_name'] ?? '-';
                $cleanNameTH = strip_tags($p->pro_nameTH ?? '');
                $cleanNameEN = strip_tags($p->pro_nameEN ?? '');
                $yearName = $p->year->year_name ?? '-';
                $deptName = $p->department->department_nameTH ?? '';

                return [
                    'id' => $p->pro_id,
                    'value' => $p->pro_id,
                    'text' => $this->formatProjectText($p),
                    'pro_nameTH' => $cleanNameTH,
                    'pro_nameEN' => $cleanNameEN,
                    'pro_code' => $p->pro_code ?? '',
                    'year_name' => $yearName,
                    'department_name' => $deptName,
                    'head_researcher' => $headResearcher,
                    'all_researchers_text' => $allResearchersText ?: '-',
                    'researchers' => $researchersList->toArray(),
                    'utilization_rows' => $p->utilizations->map(function($u, $index) {
                         $date = $u->utz_date ? \App\Helpers\ThaiDateHelper::format($u->utz_date, false, true) : '-';
                         $dept = $u->utz_department_name ?? '-';
                         $lead = $u->utz_leading ?? '-';
                         return ($index + 1) . ". {$date} • {$dept} • {$lead}";
                    })->values()->toArray()
                ];
            });

        return response()->json(['results' => $projects]);
    }

    private function formatProjectText($project)
    {
        $year = $project->year->year_name ?? '-';
        $name = strip_tags($project->pro_nameTH);
        $typeName = $project->type ? $project->type->pt_name : '';
        if ($project->typeSub && $project->typeSub->pts_name) {
            $typeName .= ($typeName ? ' (' . $project->typeSub->pts_name . ')' : $project->typeSub->pts_name);
        }
        
        $researcherNames = [];
        $departmentName = $project->department->department_nameTH ?? '';
        if ($project->rdbProjectWorks->isNotEmpty()) {
            foreach ($project->rdbProjectWorks as $work) {
                if ($work->researcher) {
                    $prefix = $work->researcher->prefix->prefix_nameTH ?? '';
                    $researcherNames[] = trim("{$prefix}{$work->researcher->researcher_fname} {$work->researcher->researcher_lname}");
                    if (!$departmentName && $work->researcher->department) {
                        $departmentName = $work->researcher->department->department_nameTH ?? '';
                    }
                }
            }
        }
        $researchersStr = !empty($researcherNames) ? implode(', ', $researcherNames) : '-';
        
        $typeMeta = $typeName ? " [ทุน: {$typeName}]" : '';
        return "[{$year}]{$typeMeta} {$name} โดย : {$researchersStr}" . ($departmentName ? " ({$departmentName})" : "");
    }

    /**
     * AJAX search for Project Types by Year
     */
    public function searchProjectType(Request $request)
    {
        $this->authorize('viewAny', RdbProject::class);
        $yearId = $request->get('year_id');
        
        if (!$yearId) {
            // If no year selected, maybe return all? Or none? Frontend logic expects specific year usually.
            // But let's return all if no year for flexibility or empty. 
            // The frontend logic clears if no year.
            // Let's return all if year is empty, or just empty?
            // Frontend logic: if (!yearId) ... options = ... -- ทั้งหมด --
            // Let's follow the frontend logic: filter by year if present.
            // BUT wait, standard behavior: if I select a year, I want types available in that year.
            return response()->json(['results' => []]);
        }

        // Get types that have projects in this year
        $types = RdbProjectType::whereHas('rdbProjects', function($query) use ($yearId) {
            $query->where('year_id', $yearId);
        })->orderBy('pt_name')->get();

        $results = $types->map(function($t) {
            return ['id' => $t->pt_id, 'text' => $t->pt_name];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * AJAX search for Project Type Subs by Type
     */
    public function searchProjectTypeSub(Request $request)
    {
        $this->authorize('viewAny', RdbProject::class);
        $ptId = $request->get('pt_id');
        
        if (!$ptId) {
            return response()->json(['results' => []]);
        }

        $subTypes = \App\Models\RdbProjectTypeSub::where('pt_id', $ptId)
            ->orderBy('pts_name')
            ->get();

        $results = $subTypes->map(function($s) {
            return ['id' => $s->pts_id, 'text' => $s->pts_name];
        });

        return response()->json(['results' => $results]);
    }
    /**
     * AJAX search for Project Group (Parent Project when pgroup_id = 2)
     * Search for projects with pgroup_id = 1 (Main Plan) in the same year
     */
    public function searchProGroup(Request $request)
    {
        $this->authorize('viewAny', RdbProject::class);
        $yearId = $request->get('year_id');
        
        $query = RdbProject::where('pgroup_id', 1) // 1 = Main Plan
                           ->with(['year', 'rdbProjectWorks' => function($q) {
                               $q->orderBy('position_id', 'asc'); // Get Leader/Director first
                           }, 'rdbProjectWorks.researcher.department']);

        if ($yearId) {
            $query->where('year_id', $yearId);
        }

        $projects = $query->orderBy('pro_nameTH')->get();

        $results = $projects->map(function($p) {
            $year = $p->year->year_name ?? '-';
            $pName = strip_tags($p->pro_nameTH);
            
            // Base text for search and fallback
            $text = "[{$year}] {$pName}";
            
            // Extended Data
            $headName = '';
            $headDept = '';

            // Find Head/Leader
            $headWork = $p->rdbProjectWorks->first();
            if ($headWork && $headWork->researcher) {
                $headName = $headWork->researcher->researcher_fname . ' ' . $headWork->researcher->researcher_lname;
                $headDept = $headWork->researcher->department->department_nameTH ?? '';
                
                // Append to text for better searchability
                $text .= " • {$headName} ({$headDept})";
            }
            
            return [
                'id' => $p->pro_id, 
                'text' => $text,
                'p_name' => $pName,
                'year' => $year,
                'head_name' => $headName,
                'head_dept' => $headDept
            ];
        });

        return response()->json(['results' => $results]);
    }


    /**
     * Store additional project file
     */
    public function storeFile(StoreProjectFileRequest $request, $id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project);

        try {
            $fileInfo = $this->fileUploadService->storeFile($request->file('rf_files'), 'project_files');

            RdbProjectFiles::create([
                'pro_id' => $project->pro_id,
                'rf_filesname' => $request->rf_filesname,
                'rf_files' => $fileInfo['safe_name'], // Old field (legacy) can store safe name
                'prf_safe_name' => $fileInfo['safe_name'], // New field for safe name (UUID based)
                'prf_path' => $fileInfo['path'], // New field for full storage path
                'prf_size' => $fileInfo['size'],
                'prf_mime_type' => $fileInfo['mime_type'],
                'rf_note' => $request->rf_note,
                'rf_files_show' => $request->has('rf_files_show') ? 1 : 0,
                'user_created' => auth()->id(),
                'user_updated' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'เพิ่มไฟล์เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update additional project file
     */
    public function updateFile(UpdateProjectFileRequest $request, $id, $fileId)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project);
        
        $file = RdbProjectFiles::where('pro_id', $id)->where('id', $fileId)->firstOrFail();

        try {
            if ($request->hasFile('rf_files')) {
                // Delete old file if exists
                $existingFilePath = $file->getFilePath();
                if ($existingFilePath && Storage::disk('public')->exists($existingFilePath)) {
                    Storage::disk('public')->delete($existingFilePath);
                }

                $fileInfo = $this->fileUploadService->storeFile($request->file('rf_files'), 'project_files');
                
                $file->rf_files = $fileInfo['safe_name']; // Update legacy field
                $file->prf_safe_name = $fileInfo['safe_name']; // Update new safe name
                $file->prf_path = $fileInfo['path']; // Update new path
                $file->prf_size = $fileInfo['size'];
                $file->prf_mime_type = $fileInfo['mime_type']; // Corrected field name
            }

            $file->rf_filesname = $request->rf_filesname;
            $file->rf_note = $request->rf_note;
            if ($request->has('rf_files_show')) {
                $file->rf_files_show = $request->boolean('rf_files_show') ? 1 : 0;
            }
            $file->user_updated = auth()->id();
            $file->save();

            return redirect()->back()->with('success', 'แก้ไขไฟล์เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete additional project file
     */
    public function destroyFile($id, $fileId)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project);
        
        $file = RdbProjectFiles::where('pro_id', $id)->where('id', $fileId)->firstOrFail();

        // Delete File
        $existingFilePath = $file->getFilePath();
        if ($existingFilePath && Storage::disk('public')->exists($existingFilePath)) {
            Storage::disk('public')->delete($existingFilePath);
        }

        $file->delete();

        return redirect()->back()->with('success', 'ลบไฟล์เรียบร้อยแล้ว');
    }

    /**
     * Download additional project file
     */
    public function downloadFile($id, $fileId)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('view', $project);

        $file = RdbProjectFiles::where('pro_id', $id)->where('id', $fileId)->firstOrFail();

        // Count download
        $file->increment('rf_download');

        // Use the getFilePath() method from the model to resolve the correct path
        $filePath = $file->getFilePath();

        $fileContents = null;
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $fileContents = Storage::disk('public')->get($filePath);
        } elseif ($filePath && file_exists(public_path($filePath))) {
            $fileContents = file_get_contents(public_path($filePath));
        }

        if ($fileContents === null) {
            abort(404, 'ไม่พบไฟล์');
        }

        // Get the original file name with correct extension from mime type
        $extension = null;
        if (!empty($file->prf_mime_type)) {
            $mimeToExt = [
                'application/pdf' => 'pdf',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            ];
            $extension = $mimeToExt[$file->prf_mime_type] ?? null;
        }

        // Fallback to pathinfo if mime type is unknown or empty
        if (empty($extension)) {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        }

        $originalFileName = $file->rf_filesname . ($extension ? '.' . $extension : '');

        return response($fileContents, 200, [
            'Content-Type' => $file->prf_mime_type ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . $originalFileName . '";',
        ]);
    }

    /**
     * Toggle or set full report visibility (pro_file_show: 0 = เปิด, 1 = ปิด, 2 = ร่าง)
     */
    public function toggleReportStatus(Request $request, $id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project);

        if ($request->has('status')) {
            $newStatus = (int) $request->input('status');
            if (in_array($newStatus, [0, 1, 2], true)) {
                $project->pro_file_show = $newStatus;
            }
        } else {
            // Cycle: 0 (เปิด) -> 1 (ปิด) -> 2 (ร่าง) -> 0 (เปิด)
            $project->pro_file_show = match ((int) $project->pro_file_show) {
                0 => 1,
                1 => 2,
                2 => 0,
                default => 0,
            };
        }

        $project->user_updated = auth()->id();
        $project->save();

        return response()->json([
            'success' => true,
            'status' => (int) $project->pro_file_show,
            'status_label' => match ((int) $project->pro_file_show) {
                0 => 'เปิด (Public)',
                1 => 'ปิด (Concealed)',
                2 => 'ร่าง (Draft)',
                default => 'เปิด (Public)',
            },
            'message' => 'ปรับปรุงสถานะการแสดงผลรายงานฉบับสมบูรณ์เรียบร้อยแล้ว'
        ]);
    }
}