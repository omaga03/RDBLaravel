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
            'status', 
            'department', 
            'rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            },
            'rdbProjectWorks.researcher',
            'createdBy',
            'updatedBy'
        ]);

        // Apply 'manage own group data' filter for general admins (not 'The Creator')
        if (auth()->check() && auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('The Creator')) {
            // Get all user IDs that have the 'Admin' role
            $adminIds = \App\Models\User::whereHas('roles', function ($q) {
                $q->where('item_name', 'Admin');
            })->pluck('id');

            $query->whereIn('user_created', $adminIds);
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

        if ($request->filled('pro_nameTH')) {
            $words = preg_split('/[\s\/,]+/', trim($request->pro_nameTH));
            $query->where(function($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    $q->orWhere(function($wq) use ($word) {
                        $wq->where('pro_nameTH', 'like', '%' . $word . '%')
                           ->orWhere('pro_nameEN', 'like', '%' . $word . '%');
                    });
                }
            });
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
        if ($request->filled('date_start')) {
            $query->where('pro_date_start', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->where('pro_date_end', '<=', $request->date_end);
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
                foreach ($project->researchers as $researcher) {
                    $matched = true;
                    foreach ($searchWords as $word) {
                        if (stripos($researcher->researcher_fname ?? '', $word) === false &&
                            stripos($researcher->researcher_lname ?? '', $word) === false) {
                            $matched = false;
                            break;
                        }
                    }
                    if ($matched) {
                        $matchedResearchers[] = ($researcher->researcher_fname ?? '') . ' ' . ($researcher->researcher_lname ?? '');
                    }
                }
                if (!empty($matchedResearchers)) {
                    $matchSources[] = 'นักวิจัย: ' . implode(', ', $matchedResearchers);
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
        $this->authorize('viewAny', RdbResearcher::class); // Assuming general researcher view permission
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $researchers = RdbResearcher::with(['prefix', 'department'])
            ->whereNotNull('researcher_fname')
            ->where('researcher_fname', '!=', '')
            ->where(function($q) use ($query) {
                $q->where('researcher_fname', 'like', "%{$query}%")
                  ->orWhere('researcher_lname', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function($r) {
                $deptName = $r->department->department_nameTH ?? $r->department->department_nameEN ?? $r->researcher_note ?? '-';
                $fullName = ($r->prefix->prefix_nameTH ?? '') . $r->researcher_fname . ' ' . $r->researcher_lname . ' [' . $deptName . ']';
                return [
                    'value' => $r->researcher_id,
                    'text' => $fullName
                ];
            });

        return response()->json($researchers);
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
    public function updateStatus(Request $request, $id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project);

        $validated = $request->validate([
            'ps_id' => 'required|integer|exists:rdb_project_status,ps_id',
        ], [
            'ps_id.required' => 'กรุณาเลือกสถานะโครงการ',
            'ps_id.integer' => 'รหัสสถานะโครงการต้องเป็นตัวเลข',
            'ps_id.exists' => 'ไม่พบข้อมูลสถานะโครงการที่เลือก',
        ]);

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
        
        return response()->json(['success' => true, 'status' => $file->rf_files_show]);
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

    /**
     * Upload full report file
     */
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
            $project->user_updated = auth()->id();
            $project->save();

            return redirect()->back()->with('success', 'อัปโหลดไฟล์รายงานเรียบร้อยแล้ว');
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
        $this->authorize('view', $project); // Authorize viewing project to view its abstract
        $filePath = $project->getAbstractFilePath();
        
        $fileContents = null;
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $fileContents = Storage::disk('public')->get($filePath);
        } elseif ($filePath && file_exists(public_path($filePath))) {
            $fileContents = file_get_contents(public_path($filePath));
        }

        if ($fileContents === null) {
            abort(404, 'ไม่พบไฟล์บทคัดย่อ');
        }

        $project->increment('pro_count_abs');

        $mimeType = $project->pro_abstract_mime ?? 'application/pdf'; // Fallback to PDF
        
        return response($fileContents, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . ($project->pro_abstract_file ?? 'abstract') . '.' . pathinfo($filePath, PATHINFO_EXTENSION) . '";',
        ]);
    }

    /**
     * View full report file (with counter)
     */
    public function viewReport($id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('view', $project); // Authorize viewing project to view its report
        $filePath = $project->getReportFilePath();
        
        $fileContents = null;
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $fileContents = Storage::disk('public')->get($filePath);
        } elseif ($filePath && file_exists(public_path($filePath))) {
            $fileContents = file_get_contents(public_path($filePath));
        }

        if ($fileContents === null) {
            abort(404, 'ไม่พบไฟล์รายงาน');
        }

        $project->increment('pro_count_full');

        $mimeType = $project->pro_file_mime ?? 'application/pdf'; // Fallback to PDF
        
        return response($fileContents, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . ($project->pro_file ?? 'report') . '.' . pathinfo($filePath, PATHINFO_EXTENSION) . '";',
        ]);
    }

    /**
     * AJAX Search for Projects (Centralized)
     */
    public function search(Request $request)
    {
        $this->authorize('viewAny', RdbProject::class); // Authorize searching projects
        $q = $request->get('q', '');
        
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $projects = RdbProject::with(['year', 'department', 'rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            }, 'rdbProjectWorks.researcher', 'utilizations'])
            ->withCount('utilizations')
            ->where('pro_nameTH', 'like', "%{$q}%")
            ->orWhere('pro_nameEN', 'like', "%{$q}%")
            ->orWhere('pro_code', 'like', "%{$q}%")
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($p) => [
                'id' => $p->pro_id,
                'value' => $p->pro_id,
                'text' => $this->formatProjectText($p),
                'utilization_rows' => $p->utilizations->map(function($u, $index) {
                     $date = $u->utz_date ? \App\Helpers\ThaiDateHelper::format($u->utz_date, false, true) : '-';
                     $dept = $u->utz_department_name ?? '-';
                     $lead = $u->utz_leading ?? '-';
                     return ($index + 1) . ". {$date} • {$dept} • {$lead}";
                })->values()->toArray()
            ]);

        return response()->json(['results' => $projects]);
    }

    private function formatProjectText($project)
    {
        $year = $project->year->year_name ?? '-';
        $name = strip_tags($project->pro_nameTH);
        
        $researcherName = '-';
        $departmentName = '';
        if ($project->rdbProjectWorks->isNotEmpty()) {
            $mainWork = $project->rdbProjectWorks->first();
            if ($mainWork && $mainWork->researcher) {
                $researcherName = $mainWork->researcher->researcher_fname . ' ' . $mainWork->researcher->researcher_lname;
                $departmentName = $mainWork->researcher->department->department_nameTH ?? '';
            }
        }
        
        return "[{$year}] {$name} โดย : {$researcherName} " . ($departmentName ? "({$departmentName})" : "");
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
    public function storeFile(Request $request, $id)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project);

        $request->validate([
            'rf_filesname' => 'required|string|max:255',
            'rf_files' => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png',
            'rf_note' => 'nullable|string',
            'rf_files_show' => 'nullable|boolean',
        ]);

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
    public function updateFile(Request $request, $id, $fileId)
    {
        $project = RdbProject::findOrFail($id);
        $this->authorize('update', $project);
        
        $file = RdbProjectFiles::where('pro_id', $id)->where('id', $fileId)->firstOrFail();

        $request->validate([
            'rf_filesname' => 'required|string|max:255',
            'rf_files' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png',
            'rf_note' => 'nullable|string',
        ]);

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
}