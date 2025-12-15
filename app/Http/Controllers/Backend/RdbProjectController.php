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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class RdbProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RdbProject::with([
            'year', 
            'type', 
            'status', 
            'department', 
            'rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            },
            'rdbProjectWorks.researcher'
        ]);

        if ($request->filled('pro_nameTH')) {
            $query->where('pro_nameTH', 'like', '%' . $request->pro_nameTH . '%');
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
        if ($request->filled('ps_id')) {
            $query->where('ps_id', $request->ps_id);
        }

        $projects = $query->orderBy('pro_id', 'desc')->paginate(10);
        
        // Pass dropdown data for search form
        $years = RdbYear::orderBy('year_id', 'desc')->get();
        $departments = RdbDepartment::all();
        $types = RdbProjectType::all();
        $statuses = RdbProjectStatus::all();

        return view('backend.rdb_project.index', compact('projects', 'years', 'departments', 'types', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('Project');
        
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('Project');

        $request->validate([
            'pro_nameTH' => 'required',
            'year_id' => 'required',
            'researcher_id' => 'required|exists:rdb_researcher,researcher_id',
            'ratio' => 'required|numeric|min:0|max:100',
            'position_id' => 'required|exists:rdb_project_position,position_id',
        ]);

        $project = new RdbProject();
        $project->fill($request->except(['researcher_id', 'ratio', 'position_id', 'pro_abstract_th', 'pro_abstract_en']));
        
        // Combine abstract Thai and English with separator
        $abstractTH = $request->input('pro_abstract_th', '');
        $abstractEN = $request->input('pro_abstract_en', '');
        if ($abstractTH || $abstractEN) {
            $project->pro_abstract = $abstractTH . '<br><br><br><br>' . $abstractEN;
        }
        
        // Set defaults
        $project->data_show = 1;
        $project->created_at = now();

        $project->user_created = auth()->id();

        // Sync Department Info from Researcher if likely Head/Director (Initial Create)
        // Note: For create, we assume the first researcher added is significant.
        if ($request->researcher_id) {
            $researcher = RdbResearcher::find($request->researcher_id);
            if ($researcher) {
                $project->department_id = $researcher->department_id;
                $project->depcou_id = $researcher->depcou_id;
                $project->major_id = $researcher->maj_id;
            }
        }

        $project->save();

        // Create project work for the researcher
        if ($request->researcher_id) {
            RdbProjectWork::create([
                'pro_id' => $project->pro_id,
                'researcher_id' => $request->researcher_id,
                'position_id' => $request->position_id ?? 1,
                'ratio' => $request->ratio ?? 100,
                'user_created' => auth()->id(),
            ]);
        }

        return redirect()->route('backend.rdb_project.show', $project->pro_id)
            ->with('success', 'บันทึกข้อมูลโครงการวิจัยเรียบร้อยแล้ว');
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
            'rdbProjectWorks.researcher.prefix'
        ])->findOrFail($id);

        // Increment page view count
        $project->increment('pro_count_page');

        // Fetch existing researcher IDs for duplicate check (sent to JS)
        $existingResearcherIds = $project->rdbProjectWorks->pluck('researcher_id')->toArray();
        
        $positions = RdbProjectPosition::all();

        // Identify taken unique positions (1=Director, 2=Head)
        $takenPositions = $project->rdbProjectWorks->pluck('position_id')->toArray();
        $hasDirector = in_array(1, $takenPositions);
        $hasHead = in_array(2, $takenPositions);

        return view('backend.rdb_project.show', compact('project', 'positions', 'existingResearcherIds', 'takenPositions', 'hasDirector', 'hasHead'));
    }

    /**
     * Search researchers via AJAX for TomSelect
     */
    public function searchResearchers(Request $request)
    {
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
                $fullName = ($r->prefix->prefix_nameTH ?? '') . $r->researcher_fname . ' ' . $r->researcher_lname . ' (' . $deptName . ')';
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
        $project = RdbProject::findOrFail($id);
        $groups = RdbGroupproject::all();
        $types = RdbProjectType::all();
        $departments = RdbDepartment::all();
        $years = RdbYear::orderBy('year_id', 'desc')->get();
        $strategics = RdbStrategic::all();
        $statuses = RdbProjectStatus::all();

        return view('backend.rdb_project.edit', compact('project', 'groups', 'types', 'departments', 'years', 'strategics', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Gate::authorize('Project');

        $project = RdbProject::findOrFail($id);
        
        $request->validate([
            'pro_nameTH' => 'required',
            'year_id' => 'required',
            'pro_abstract_file' => 'nullable|file|mimes:pdf|max:20480',
            'pro_file' => 'nullable|file|mimes:pdf|max:20480',
        ]);
        
        $project->fill($request->except(['pro_abstract_th', 'pro_abstract_en', 'pro_abstract_file', 'pro_file']));
        
        // Combine abstract Thai and English with separator
        $abstractTH = $request->input('pro_abstract_th', '');
        $abstractEN = $request->input('pro_abstract_en', '');
        if ($abstractTH || $abstractEN) {
            $project->pro_abstract = $abstractTH . '<br><br><br><br>' . $abstractEN;
        }
        
        $project->user_updated = auth()->id();

        if ($request->hasFile('pro_abstract_file')) {
            // Delete old file
            if ($project->pro_abstract_file) {
                Storage::delete('public/uploads/projects/' . $project->pro_abstract_file);
            }
            $file = $request->file('pro_abstract_file');
            $filename = time() . '_abs_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads/projects', $filename);
            $project->pro_abstract_file = $filename;
        }

        if ($request->hasFile('pro_file')) {
            // Delete old file
            if ($project->pro_file) {
                Storage::delete('public/uploads/projects/' . $project->pro_file);
            }
            $file = $request->file('pro_file');
            $filename = time() . '_full_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads/projects', $filename);
            $project->pro_file = $filename;
        }

        $project->save();

        return redirect()->route('backend.rdb_project.show', $project->pro_id)
            ->with('success', 'แก้ไขข้อมูลโครงการวิจัยเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('Project'); // Enforce RBAC

        $project = RdbProject::findOrFail($id);
        
        // Soft delete by hiding
        $project->data_show = 0;
        $project->user_updated = auth()->id();
        $project->save();

        return redirect()->route('backend.rdb_project.index')->with('success', 'ลบโครงการเรียบร้อยแล้ว (ซ่อนการแสดงผล)');
    }

    public function storeResearcher(Request $request, $id)
    {
        $project = RdbProject::findOrFail($id);
        $request->validate([
            'researcher_id' => 'required',
            'position_id' => 'required',
            'ratio' => 'required|numeric|min:0|max:100',
        ]);

        // Prevent duplicate researcher
        $exists = RdbProjectWork::where('pro_id', $id)->where('researcher_id', $request->researcher_id)->exists();
        if ($exists) {
             return redirect()->back()->with('error', 'Researcher already exists in this project.');
        }

        // Validate Total Ratio <= 100
        $currentTotalRatio = RdbProjectWork::where('pro_id', $id)->sum('ratio');
        if (($currentTotalRatio + $request->ratio) > 100) {
            return redirect()->back()->with('error', "Total ratio exceeds 100%. Current total: {$currentTotalRatio}%");
        }

        // Use direct DB insert or model create because RdbProjectWork PK structure is non-standard
        // But let's try strict model create
        $work = new RdbProjectWork();
        $work->pro_id = $id;
        $work->researcher_id = $request->researcher_id;
        $work->position_id = $request->position_id;
        $work->ratio = $request->ratio;
        $work->ratio = $request->ratio;
        $work->save();

        // Sync Department Info if Position is Director (1) or Head (2)
        if (in_array($request->position_id, [1, 2])) {
            $researcher = RdbResearcher::find($request->researcher_id);
            if ($researcher) {
                $project->department_id = $researcher->department_id;
                $project->depcou_id = $researcher->depcou_id;
                $project->major_id = $researcher->maj_id;
                $project->user_updated = auth()->id();
                $project->save();
            }
        }

        return redirect()->back()->with('success', 'Researcher added successfully.');
    }

    public function updateResearcher(Request $request, $id, $rid)
    {
        $request->validate([
            'position_id' => 'required',
            'ratio' => 'required|numeric|min:0|max:100',
        ]);

        // Validate Total Ratio <= 100 (Exclude current researcher's old ratio)
        $currentTotalRatio = RdbProjectWork::where('pro_id', $id)
                                           ->where('researcher_id', '!=', $rid)
                                           ->sum('ratio');
        
        if (($currentTotalRatio + $request->ratio) > 100) {
            return redirect()->back()->with('error', "Total ratio exceeds 100%. Current total (others): {$currentTotalRatio}%");
        }

        // Update pivot
        RdbProjectWork::where('pro_id', $id)
                      ->where('researcher_id', $rid)
                      ->update([
                          'position_id' => $request->position_id,
                          'ratio' => $request->ratio
                      ]);

        // Sync Department Info if Position is Director (1) or Head (2)
        if (in_array($request->position_id, [1, 2])) {
             $researcher = RdbResearcher::find($rid);
             // Verify project again to be safe
             $project = RdbProject::find($id);
             if ($researcher && $project) {
                 $project->department_id = $researcher->department_id;
                 $project->depcou_id = $researcher->depcou_id;
                 $project->major_id = $researcher->maj_id;
                 $project->user_updated = auth()->id();
                 $project->save();
             }
        }

        return redirect()->back()->with('success', 'Researcher updated successfully.');
    }

    public function destroyResearcher($id, $rid)
    {
        Gate::authorize('Project'); // Assuming project permission covers this

        RdbProjectWork::where('pro_id', $id)
            ->where('researcher_id', $rid)
            ->delete();

        return redirect()->back()->with('success', 'ลบนักวิจัยเรียบร้อยแล้ว');
    }

    /**
     * Store a newly created file in storage.
     */
    public function storeFile(Request $request, $id)
    {
        Gate::authorize('Project');

        $request->validate([
            'rf_filesname' => 'required|string|max:255',
            'rf_files' => 'required|file|mimes:pdf|max:20480', // PDF only, Max 20MB
        ]);

        $project = RdbProject::findOrFail($id);

        if ($request->hasFile('rf_files')) {
            $file = $request->file('rf_files');
            if (!$file->isValid()) {
                return redirect()->back()->withErrors(['rf_files' => 'File upload failed: ' . $file->getErrorMessage()]);
            }
            
            // Format: PRO_ID-rfTIMESTAMP + Random(40) . ext
            $filename = $project->pro_id . '-rf' . date('YmdHis') . \Illuminate\Support\Str::random(40) . '.' . $file->getClientOriginalExtension();
            
            $path = 'uploads/project_files';
            // Ensure directory exists
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($path);
            }

            // Upload (Use put+file_get_contents to avoid stream path issues)
            \Illuminate\Support\Facades\Storage::disk('public')->put($path . '/' . $filename, file_get_contents($file));

            RdbProjectFiles::create([
                'pro_id' => $id,
                'rf_filesname' => $request->rf_filesname,
                'rf_files' => $filename,
                'rf_note' => $request->rf_note,
                'user_created' => auth()->id(),
                'rf_files_show' => $request->has('rf_files_show') ? 1 : 0
            ]);
        }

        return redirect()->back()->with('success', 'เพิ่มไฟล์เรียบร้อยแล้ว');
    }

    /**
     * Update the specified file in storage.
     */
    public function updateFile(Request $request, $id, $fid)
    {
        Gate::authorize('Project');

        $request->validate([
            'rf_filesname' => 'required|string|max:255',
            'rf_files' => 'nullable|file|mimes:pdf|max:20480', // PDF only
        ]);

        $fileRecord = RdbProjectFiles::where('pro_id', $id)->where('id', $fid)->firstOrFail();
        $project = RdbProject::findOrFail($id);

        $data = [
            'rf_filesname' => $request->rf_filesname,
            'rf_note' => $request->rf_note,
            'user_updated' => auth()->id(),
        ];

        if ($request->hasFile('rf_files')) {
            // Delete old file
            if ($fileRecord->rf_files && Storage::disk('public')->exists('uploads/project_files/' . $fileRecord->rf_files)) {
                Storage::disk('public')->delete('uploads/project_files/' . $fileRecord->rf_files);
            }
            $file = $request->file('rf_files');
            
            if (!$file->isValid()) {
                return redirect()->back()->withErrors(['rf_files' => 'File upload failed: ' . $file->getErrorMessage()]);
            }

            // Generate Name: ID-rfTIMESTAMP + Random(40) . ext (Matches Yii2 pattern)
            $fileName = $id . '-rf' . date('YmdHis') . \Illuminate\Support\Str::random(40) . '.' . $file->getClientOriginalExtension();
            $currDate = date('Y-m-d H:i:s');
            
            $path = 'uploads/project_files';
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($path);
            }

            // Upload (Use put+file_get_contents to avoid stream path issues)
            \Illuminate\Support\Facades\Storage::disk('public')->put($path . '/' . $fileName, file_get_contents($file));
            $data['rf_files'] = $fileName;
        }

        $fileRecord->update($data);

        return redirect()->back()->with('success', 'แก้ไขไฟล์เรียบร้อยแล้ว');
    }

    /**
     * Remove the specified file from storage.
     */
    public function destroyFile($id, $fid)
    {
        Gate::authorize('Project');

        $fileRecord = RdbProjectFiles::where('pro_id', $id)->where('id', $fid)->firstOrFail();

        // Delete file from storage
        if ($fileRecord->rf_files && Storage::disk('public')->exists('uploads/project_files/' . $fileRecord->rf_files)) {
            Storage::disk('public')->delete('uploads/project_files/' . $fileRecord->rf_files);
        }

        $fileRecord->delete();

        return redirect()->back()->with('success', 'ลบไฟล์เรียบร้อยแล้ว');
    }

    /**
     * Track file download and redirect to file
     */
    public function downloadFile($id, $fid)
    {
        $fileRecord = RdbProjectFiles::where('pro_id', $id)->where('id', $fid)->firstOrFail();

        // Increment download count
        $fileRecord->rf_download = ($fileRecord->rf_download ?? 0) + 1;
        $fileRecord->save();

        // Redirect to actual file
        return redirect(asset('storage/uploads/project_files/' . $fileRecord->rf_files));
    }

    /**
     * Toggle full report visibility
     */
    public function toggleReportStatus($id)
    {
        Gate::authorize('Project');
        $project = RdbProject::findOrFail($id);
        $project->pro_file_show = $project->pro_file_show == 1 ? 0 : 1;
        $project->user_updated = auth()->id();
        $project->save();
        return response()->json(['success' => true, 'status' => $project->pro_file_show]);
    }

    /**
     * Toggle additional file visibility
     */
    public function toggleFileStatus($id, $fid)
    {
        Gate::authorize('Project');
        \Illuminate\Support\Facades\Log::info("Toggling file status for Project: $id, File: $fid");
        
        $file = RdbProjectFiles::where('pro_id', $id)->where('id', $fid)->firstOrFail();
        \Illuminate\Support\Facades\Log::info("Old Status: " . $file->rf_files_show);
        
        $file->rf_files_show = $file->rf_files_show == 1 ? 0 : 1;
        $file->user_updated = auth()->id();
        $file->save();
        
        \Illuminate\Support\Facades\Log::info("New Status: " . $file->rf_files_show);
        
        return response()->json(['success' => true, 'status' => $file->rf_files_show]);
    }

    /**
     * Upload abstract file
     */
    public function uploadAbstract(Request $request, $id)
    {
        Gate::authorize('Project');
        
        $request->validate([
            'pro_abstract_file' => 'required|file|mimes:pdf|max:20480',
        ]);

        $project = RdbProject::findOrFail($id);

        // Delete old file if exists
        if ($project->pro_abstract_file && Storage::disk('public')->exists('uploads/projects/' . $project->pro_abstract_file)) {
            Storage::disk('public')->delete('uploads/projects/' . $project->pro_abstract_file);
        }

        $file = $request->file('pro_abstract_file');
        $filename = $id . '-abs' . date('YmdHis') . \Illuminate\Support\Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        $path = 'uploads/projects';
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }
        
        Storage::disk('public')->put($path . '/' . $filename, file_get_contents($file));
        
        $project->pro_abstract_file = $filename;
        $project->user_updated = auth()->id();
        $project->save();

        return redirect()->back()->with('success', 'อัปโหลดไฟล์บทคัดย่อเรียบร้อยแล้ว');
    }

    /**
     * Delete abstract file
     */
    public function deleteAbstract($id)
    {
        Gate::authorize('Project');
        
        $project = RdbProject::findOrFail($id);

        if ($project->pro_abstract_file && Storage::disk('public')->exists('uploads/projects/' . $project->pro_abstract_file)) {
            Storage::disk('public')->delete('uploads/projects/' . $project->pro_abstract_file);
        }

        $project->pro_abstract_file = null;
        $project->user_updated = auth()->id();
        $project->save();

        return redirect()->back()->with('success', 'ลบไฟล์บทคัดย่อเรียบร้อยแล้ว');
    }

    /**
     * Upload full report file
     */
    public function uploadReport(Request $request, $id)
    {
        Gate::authorize('Project');
        
        $request->validate([
            'pro_file' => 'required|file|mimes:pdf|max:20480',
        ]);

        $project = RdbProject::findOrFail($id);

        // Delete old file if exists
        if ($project->pro_file && Storage::disk('public')->exists('uploads/projects/' . $project->pro_file)) {
            Storage::disk('public')->delete('uploads/projects/' . $project->pro_file);
        }

        $file = $request->file('pro_file');
        $filename = $id . '-full' . date('YmdHis') . \Illuminate\Support\Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        $path = 'uploads/projects';
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }
        
        Storage::disk('public')->put($path . '/' . $filename, file_get_contents($file));
        
        $project->pro_file = $filename;
        $project->ps_id = 2; // Set status to 'ดำเนินการเสร็จสิ้น'
        $project->pro_finish = now()->format('Y-m-d');
        $project->pro_file_show = $request->has('pro_file_show') ? 1 : 0;
        $project->user_updated = auth()->id();
        $project->save();

        return redirect()->back()->with('success', 'อัปโหลดไฟล์รายงานฉบับสมบูรณ์เรียบร้อยแล้ว (สถานะ: ดำเนินการเสร็จสิ้น)');
    }

    /**
     * Delete full report file
     */
    public function deleteReport($id)
    {
        Gate::authorize('Project');
        
        $project = RdbProject::findOrFail($id);

        if ($project->pro_file && Storage::disk('public')->exists('uploads/projects/' . $project->pro_file)) {
            Storage::disk('public')->delete('uploads/projects/' . $project->pro_file);
        }

        $project->pro_file = null;
        $project->user_updated = auth()->id();
        $project->save();

        return redirect()->back()->with('success', 'ลบไฟล์รายงานฉบับสมบูรณ์เรียบร้อยแล้ว');
    }

    /**
     * View abstract file (with counter)
     */
    public function viewAbstract($id)
    {
        $project = RdbProject::findOrFail($id);
        
        if (!$project->pro_abstract_file) {
            abort(404, 'ไม่พบไฟล์บทคัดย่อ');
        }

        // Increment view count
        $project->pro_count_abs = ($project->pro_count_abs ?? 0) + 1;
        $project->save();

        return redirect(asset('storage/uploads/projects/' . $project->pro_abstract_file));
    }

    /**
     * View full report file (with counter)
     */
    public function viewReport($id)
    {
        $project = RdbProject::findOrFail($id);
        
        if (!$project->pro_file) {
            abort(404, 'ไม่พบไฟล์รายงานฉบับสมบูรณ์');
        }

        // Increment view count
        $project->pro_count_full = ($project->pro_count_full ?? 0) + 1;
        $project->save();

        return redirect(asset('storage/uploads/projects/' . $project->pro_file));
    }

    /**
     * Search project types for TomSelect AJAX
     */
    public function searchProjectType(Request $request)
    {
        $q = $request->get('q', '');
        $yearId = $request->get('year_id');
        
        $query = RdbProjectType::query();
        
        if ($q) {
            $query->where('pt_name', 'like', "%{$q}%");
        }
        if ($yearId) {
            $query->where('year_id', $yearId);
        }
        
        $results = $query->limit(30)->get()->map(function($item) {
            return ['id' => $item->pt_id, 'text' => $item->pt_name];
        });
        
        return response()->json(['results' => $results]);
    }

    /**
     * Search project type sub for TomSelect AJAX
     */
    public function searchProjectTypeSub(Request $request)
    {
        $q = $request->get('q', '');
        $ptId = $request->get('pt_id');
        
        $query = \App\Models\RdbProjectTypeSub::query();
        
        if ($q) {
            $query->where('pts_name', 'like', "%{$q}%");
        }
        if ($ptId) {
            $query->where('pt_id', $ptId);
        }
        
        $results = $query->limit(30)->get()->map(function($item) {
            return ['id' => $item->pts_id, 'text' => $item->pts_name];
        });
        
        return response()->json(['results' => $results]);
    }

    /**
     * Search pro group (parent projects) for TomSelect AJAX
     */
    public function searchProGroup(Request $request)
    {
        $q = $request->get('q', '');
        $yearId = $request->get('year_id');
        
        $query = RdbProject::where('pgroup_id', 1); // Group projects only
        
        if ($q) {
            $query->where('pro_nameTH', 'like', "%{$q}%");
        }
        if ($yearId) {
            $query->where('year_id', $yearId);
        }
        
        $results = $query->with('year')->limit(30)->get()->map(function($item) {
            $yearName = $item->year ? $item->year->year_name : '--';
            return ['id' => $item->pro_id, 'text' => "[{$yearName}] {$item->pro_nameTH}"];
        });
        
        return response()->json(['results' => $results]);
    }

    /**
     * Search researchers for TomSelect AJAX
     */
    public function searchResearcher(Request $request)
    {
        $q = $request->get('q', '');
        
        $query = RdbResearcher::with(['prefix', 'department']);
        
        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('researcher_fname', 'like', "%{$q}%")
                    ->orWhere('researcher_lname', 'like', "%{$q}%");
            });
        }
        
        $results = $query->limit(30)->get()->map(function($item) {
            $prefix = $item->prefix ? $item->prefix->prefix_nameTH : '';
            $dept = $item->department ? $item->department->department_nameTH : '';
            return [
                'id' => $item->researcher_id, 
                'text' => "{$prefix}{$item->researcher_fname} {$item->researcher_lname} [{$dept}]"
            ];
        });
        
        return response()->json(['results' => $results]);
    }

    /**
     * Search department courses for TomSelect AJAX
     */
    public function searchDepcou(Request $request)
    {
        $q = $request->get('q', '');
        $departmentId = $request->get('department_id');
        
        $query = \App\Models\RdbDepartmentCourse::with('department');
        
        if ($q) {
            $query->where('cou_name', 'like', "%{$q}%");
        }
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $results = $query->limit(30)->get()->map(function($item) {
            $deptName = $item->department ? $item->department->department_nameTH : '';
            return ['id' => $item->depcou_id, 'text' => "{$item->cou_name} [{$deptName}]"];
        });
        
        return response()->json(['results' => $results]);
    }

    /**
     * Search majors for TomSelect AJAX
     */
    public function searchMajor(Request $request)
    {
        $q = $request->get('q', '');
        $depcouId = $request->get('depcou_id');
        
        $query = \App\Models\RdbDepMajor::with('department');
        
        if ($q) {
            $query->where('maj_nameTH', 'like', "%{$q}%");
        }
        if ($depcouId) {
            $query->where('depcou_id', $depcouId);
        }
        
        $results = $query->limit(30)->get()->map(function($item) {
            $deptName = $item->department ? $item->department->department_nameTH : '';
            return ['id' => $item->maj_id, 'text' => "{$item->maj_nameTH} [{$deptName}]"];
        });
        
        return response()->json(['results' => $results]);
    }
}
