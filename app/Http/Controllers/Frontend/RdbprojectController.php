<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProject;
use App\Models\RdbYear;
use App\Models\RdbProjectType;
use App\Models\RdbProjectTypeSub;
use App\Models\RdbProjectStatus;
use App\Models\RdbDepartment;
use App\Models\RdbStrategic;
use App\Models\RdbResearcher;
use App\Models\RdbProjectPosition;
use App\Models\RdbGroupproject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RdbprojectController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProject::with([
            'year', 
            'type', 
            'typeSub', 
            'status', 
            'department', 
            'rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            },
            'rdbProjectWorks.researcher'
        ]);

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            // Split by space, slash, or comma for multi-word search
            $searchWords = preg_split('/[\s\/,]+/', $searchTerm);
            
            $query->where(function($q) use ($searchTerm, $searchWords) {
                // Search full term
                $q->where('pro_nameTH', 'like', "%{$searchTerm}%")
                  ->orWhere('pro_nameEN', 'like', "%{$searchTerm}%")
                  ->orWhere('pro_code', 'like', "%{$searchTerm}%")
                  ->orWhere('pro_keyword', 'like', "%{$searchTerm}%")
                  ->orWhere('pro_abstract', 'like', "%{$searchTerm}%");
                
                // Also search each word individually (OR logic)
                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_nameTH', 'like', "%{$word}%")
                      ->orWhere('pro_nameEN', 'like', "%{$word}%")
                      ->orWhere('pro_keyword', 'like', "%{$word}%")
                      ->orWhere('pro_abstract', 'like', "%{$word}%");
                }
                
                // Search in related tables
                $q->orWhereHas('type', function($tq) use ($searchTerm) {
                    $tq->where('pt_name', 'like', "%{$searchTerm}%");
                });
                
                $q->orWhereHas('year', function($yq) use ($searchTerm) {
                    $yq->where('year_name', 'like', "%{$searchTerm}%");
                });
                
                $q->orWhereHas('department', function($dq) use ($searchTerm) {
                    $dq->where('department_nameTH', 'like', "%{$searchTerm}%")
                       ->orWhere('department_nameEN', 'like', "%{$searchTerm}%");
                });
                
                // Search researchers - support multi-word name search
                $q->orWhereHas('researchers', function($rq) use ($searchWords) {
                    foreach ($searchWords as $word) {
                        if (empty($word)) continue;
                        $rq->where(function($wq) use ($word) {
                            $wq->where('researcher_fname', 'like', "%{$word}%")
                               ->orWhere('researcher_lname', 'like', "%{$word}%");
                        });
                    }
                });
            });
        }

        // Date range validation
        $hasDateStart = $request->filled('date_start');
        $hasDateEnd = $request->filled('date_end');
        
        if ($hasDateStart || $hasDateEnd) {
            // Both dates must be provided
            if (!$hasDateStart || !$hasDateEnd) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'กรุณาระบุทั้งวันที่เริ่มและวันที่สิ้นสุด');
            }
            
            // End date must be >= start date
            if ($request->date_end < $request->date_start) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น');
            }
            
            $query->whereDate('pro_date_start', '>=', $request->date_start)
                  ->whereDate('pro_date_end', '<=', $request->date_end);
        }

        // Budget filters
        if ($request->filled('budget_min')) {
            $query->where('pro_budget', '>=', $request->budget_min);
        }

        if ($request->filled('budget_max')) {
            $query->where('pro_budget', '<=', $request->budget_max);
        }

        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        if ($request->filled('pt_id')) {
            $query->where('pt_id', $request->pt_id);
        }

        if ($request->filled('pts_id')) {
            $query->where('pts_id', $request->pts_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Project Code
        if ($request->filled('pro_code')) {
            $query->where('pro_code', 'like', '%' . $request->pro_code . '%');
        }

        // Group
        if ($request->filled('pgroup_id')) {
            $query->where('pgroup_id', $request->pgroup_id);
        }

        // Researcher search
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

        // Project Name (multi-word OR search)
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

        // Abstract (multi-word OR search)
        if ($request->filled('pro_abstract')) {
            $words = preg_split('/[\s\/,]+/', trim($request->pro_abstract));
            $query->where(function($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_abstract', 'like', '%' . $word . '%');
                }
            });
        }

        // Keyword (multi-word OR search)
        if ($request->filled('pro_keyword')) {
            $words = preg_split('/[\s\/,]+/', trim($request->pro_keyword));
            $query->where(function($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_keyword', 'like', '%' . $word . '%');
                }
            });
        }

        // Note (multi-word OR search)
        if ($request->filled('pro_note')) {
            $words = preg_split('/[\s\/,]+/', trim($request->pro_note));
            $query->where(function($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_note', 'like', '%' . $word . '%');
                }
            });
        }

        // Status
        if ($request->filled('ps_id')) {
            $query->where('ps_id', $request->ps_id);
        }

        // Sorting
        $sort = $request->get('sort', 'pro_date_start');
        $direction = $request->get('direction', 'desc');
        
        // Allow sorting by specific columns only
        $allowedSorts = ['pro_id', 'pro_nameTH', 'pro_budget', 'year_id', 'created_at', 'pro_date_start'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'pro_date_start';
        }

        $projects = $query->orderBy($sort, $direction)->paginate(10);
        
        // Add match_sources for search results tooltip
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $searchWords = preg_split('/[\s\/,]+/', $searchTerm);
            
            foreach ($projects as $project) {
                $matchSources = [];
                
                // Check project name
                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    if (stripos($project->pro_nameTH ?? '', $word) !== false || 
                        stripos($project->pro_nameEN ?? '', $word) !== false) {
                        $matchSources[] = 'ชื่อโครงการ';
                        break;
                    }
                }
                
                // Check code
                if (stripos($project->pro_code ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'รหัสโครงการ';
                }
                
                // Check abstract
                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    if (stripos($project->pro_abstract ?? '', $word) !== false) {
                        $matchSources[] = 'บทคัดย่อ';
                        break;
                    }
                }
                
                // Check keyword
                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    if (stripos($project->pro_keyword ?? '', $word) !== false) {
                        $matchSources[] = 'คำสำคัญ';
                        break;
                    }
                }
                
                // Check type
                if ($project->type && stripos($project->type->pt_name ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'ประเภททุน';
                }
                
                // Check year
                if ($project->year && stripos($project->year->year_name ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'ปีงบประมาณ';
                }
                
                // Check department
                if ($project->department && 
                    (stripos($project->department->department_nameTH ?? '', $searchTerm) !== false ||
                     stripos($project->department->department_nameEN ?? '', $searchTerm) !== false)) {
                    $matchSources[] = 'หน่วยงาน';
                }
                
                // Check researchers
                $matchedResearchers = [];
                foreach ($project->researchers as $researcher) {
                    $matched = true;
                    foreach ($searchWords as $word) {
                        if (empty($word)) continue;
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
        
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $types = RdbProjectType::orderBy('pt_name')->get();
        $groups = RdbGroupproject::all();
        $statuses = RdbProjectStatus::all();

        return view('frontend.rdbproject.index', compact('projects', 'years', 'departments', 'types', 'groups', 'statuses'));
    }

    // API endpoint for getting types by year
    public function getTypesByYear(Request $request)
    {
        $yearId = $request->get('year_id');
        
        if (!$yearId) {
            return response()->json([]);
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

    public function getSubTypesByType(Request $request)
    {
        $ptId = $request->get('pt_id');
        
        if (!$ptId) {
            return response()->json([]);
        }

        $subTypes = RdbProjectTypeSub::where('pt_id', $ptId)
            ->orderBy('pts_name')
            ->get();

        $results = $subTypes->map(function($s) {
            return ['id' => $s->pts_id, 'text' => $s->pts_name];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Search researchers for Frontend TomSelect
     */
    public function searchResearchers(Request $request)
    {
        $term = trim($request->q);

        if (empty($term)) {
            return response()->json([]);
        }

        $researchers = \App\Models\RdbResearcher::with('prefix')
            ->where(function($q) use ($term) {
                $q->where('researcher_fname', 'LIKE', "%{$term}%")
                  ->orWhere('researcher_lname', 'LIKE', "%{$term}%")
                  ->orWhere('researcher_fnameEN', 'LIKE', "%{$term}%")
                  ->orWhere('researcher_lnameEN', 'LIKE', "%{$term}%");
            })
            ->limit(20)
            ->get();

        $results = [];
        foreach ($researchers as $r) {
            $prefix = $r->prefix ? $r->prefix->prefix_nameTH : '';
            $nameTH = $prefix . $r->researcher_fname . ' ' . $r->researcher_lname;
            
            $dept = $r->department ? $r->department->department_nameTH : '-';
            
            $text = "{$nameTH} [{$dept}]";

            $results[] = [
                'value' => $r->researcher_id,
                'text' => $text
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Track file download and redirect to file (Frontend)
     */
    public function downloadFile($id, $fid)
    {
        $fileRecord = \App\Models\RdbProjectFiles::where('pro_id', $id)
            ->where('id', $fid)
            ->where('rf_files_show', 1)
            ->firstOrFail();

        // Increment download count
        $fileRecord->rf_download = ($fileRecord->rf_download ?? 0) + 1;
        $fileRecord->save();

        // Redirect to actual file
        return redirect(asset('storage/uploads/project_files/' . $fileRecord->rf_files));
    }

    public function viewAbstract($id)
    {
        $project = RdbProject::findOrFail($id);
        
        if (!$project->pro_abstract_file) {
            abort(404, 'ไม่พบไฟล์บทคัดย่อ');
        }

        // Increment view count
        $project->increment('pro_count_abs');

        return redirect(asset('storage/uploads/projects/' . $project->pro_abstract_file));
    }

    public function viewReport($id)
    {
        $project = RdbProject::findOrFail($id);
        
        // Check visibility
        if (!$project->pro_file || !$project->pro_file_show) {
            abort(404, 'ไม่พบไฟล์รายงานฉบับสมบูรณ์');
        }

        // Increment view count
        $project->increment('pro_count_full');
        
        return redirect(asset('storage/uploads/projects/' . $project->pro_file));
    }

    public function show($id)
    {
        $item = RdbProject::with([
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
            'rdbProjectWorks.researcher.prefix'
        ])->findOrFail($id);
        return view('frontend.rdbproject.show', compact('item'));
    }

    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('RdbProject.Create');
        $data = $this->getDropdownData();
        return view('frontend.rdbproject.create', $data);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('RdbProject.Create');
        $validated = $this->validateRequest($request);

        DB::beginTransaction();
        try {
            // Handle File Uploads
            if ($request->hasFile('pro_abstract_file')) {
                $validated['pro_abstract_file'] = $request->file('pro_abstract_file')->store('project_abstracts', 'public');
            }
            if ($request->hasFile('pro_file')) {
                $validated['pro_file'] = $request->file('pro_file')->store('project_files', 'public');
            }

            $validated['created_at'] = now();
            $project = RdbProject::create($validated);

            // Sync Researchers (Pivot Table)
            $this->syncResearchers($project, $request);

            DB::commit();
            return redirect()->route('frontend.rdbproject.index')->with('success', 'Project created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating project: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $item = RdbProject::with('researchers')->findOrFail($id);
        $data = $this->getDropdownData();
        $data['item'] = $item;
        return view('frontend.rdbproject.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $project = RdbProject::findOrFail($id);
        $validated = $this->validateRequest($request, $id);

        DB::beginTransaction();
        try {
            // Handle File Uploads
            if ($request->hasFile('pro_abstract_file')) {
                if ($project->pro_abstract_file) Storage::disk('public')->delete($project->pro_abstract_file);
                $validated['pro_abstract_file'] = $request->file('pro_abstract_file')->store('project_abstracts', 'public');
            }
            if ($request->hasFile('pro_file')) {
                if ($project->pro_file) Storage::disk('public')->delete($project->pro_file);
                $validated['pro_file'] = $request->file('pro_file')->store('project_files', 'public');
            }

            $project->update($validated);

            // Sync Researchers (Pivot Table)
            $this->syncResearchers($project, $request);

            DB::commit();
            return redirect()->route('frontend.rdbproject.index')->with('success', 'Project updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating project: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $project = RdbProject::findOrFail($id);
        
        if ($project->pro_abstract_file) Storage::disk('public')->delete($project->pro_abstract_file);
        if ($project->pro_file) Storage::disk('public')->delete($project->pro_file);
        
        // Pivot data will be deleted automatically if cascading delete is set in DB, 
        // otherwise we should detach manually. Safe to detach.
        $project->researchers()->detach();
        
        $project->delete();

        return redirect()->route('frontend.rdbproject.index')->with('success', 'Project deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = RdbProject::with(['year', 'type', 'department', 'status']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('pro_nameTH', 'like', "%{$search}%")
                  ->orWhere('pro_nameEN', 'like', "%{$search}%");
        }
        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }
        if ($request->filled('pro_type_id')) {
            $query->where('pt_id', $request->pro_type_id); // Changed from pro_type_id to pt_id to match model relationship
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('ps_id')) {
            $query->where('ps_id', $request->ps_id);
        }

        $items = $query->orderBy('pro_id', 'desc')->get(); // Changed from 'id' to 'pro_id' to match model primary key

        $filename = "projects_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel to read Thai correctly
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Header Row
            fputcsv($file, ['ID', 'ชื่อโครงการ (TH)', 'ชื่อโครงการ (EN)', 'ปีงบประมาณ', 'ประเภท', 'หน่วยงาน', 'สถานะ', 'งบประมาณ']);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->pro_id,
                    $item->pro_nameTH,
                    $item->pro_nameEN,
                    $item->year->year_name ?? '-',
                    $item->type->pt_name ?? '-', // Changed from projectType->pro_type_name to type->pt_name to match model relationship
                    $item->department->department_nameTH ?? '-',
                    $item->status->ps_name ?? '-',
                    $item->pro_budget
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getDropdownData()
    {
        return [
            'years' => RdbYear::orderBy('year_name', 'desc')->get(),
            'projectTypes' => RdbProjectType::all(),
            'projectStatuses' => RdbProjectStatus::all(),
            'departments' => RdbDepartment::orderBy('department_nameTH')->get(),
            'strategics' => RdbStrategic::all(),
            'researchers' => RdbResearcher::orderBy('researcher_nameTH')->get(),
            'positions' => RdbProjectPosition::all(),
            'groups' => RdbGroupproject::all(),
        ];
    }

    private function validateRequest(Request $request, $id = null)
    {
        return $request->validate([
            'pro_nameTH' => 'required|string|max:255',
            'pro_nameEN' => 'nullable|string|max:255',
            'pro_code' => 'nullable|string|max:50',
            'year_id' => 'required|exists:rdb_year,year_id',
            'pt_id' => 'nullable|exists:rdb_project_type,pt_id',
            'ps_id' => 'nullable|exists:rdb_project_status,ps_id',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'strategic_id' => 'nullable|exists:rdb_strategic,strategic_id',
            'pgroup_id' => 'nullable|exists:rdb_groupproject,gp_id',
            'pro_budget' => 'nullable|numeric|min:0',
            'pro_date_start' => 'nullable|date',
            'pro_date_end' => 'nullable|date|after_or_equal:pro_date_start',
            'pro_abstract' => 'nullable|string',
            'pro_abstract_file' => 'nullable|file|max:10240|mimes:pdf,doc,docx',
            'pro_file' => 'nullable|file|max:20480|mimes:pdf,doc,docx,zip,rar', // 20MB
            'data_show' => 'nullable|integer',
            // Array validation for dynamic researchers
            'researcher_id' => 'nullable|array',
            'researcher_id.*' => 'exists:rdb_researcher,researcher_id',
            'position_id' => 'nullable|array',
            'position_id.*' => 'exists:rdb_project_position,position_id',
            'ratio' => 'nullable|array',
            'ratio.*' => 'nullable|numeric|min:0|max:100',
        ]);
    }

    private function syncResearchers(RdbProject $project, Request $request)
    {
        if ($request->has('researcher_id')) {
            $syncData = [];
            foreach ($request->researcher_id as $key => $researcherId) {
                if ($researcherId) {
                    $syncData[$researcherId] = [
                        'position_id' => $request->position_id[$key] ?? null,
                        'ratio' => $request->ratio[$key] ?? 0,
                    ];
                }
            }
            $project->researchers()->sync($syncData);
        } else {
            $project->researchers()->detach();
        }
    }
}
