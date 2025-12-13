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
        $groups = RdbGroupproject::all();
        $types = RdbProjectType::all();
        $departments = RdbDepartment::all();
        $years = RdbYear::orderBy('year_id', 'desc')->get();
        $strategics = RdbStrategic::all();
        $statuses = RdbProjectStatus::all();

        return view('backend.rdb_project.create', compact('groups', 'types', 'departments', 'years', 'strategics', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('Project'); // Enforce RBAC

        $request->validate([
            'pro_nameTH' => 'required',
            'year_id' => 'required',
            'pro_abstract_file' => 'nullable|file|mimes:pdf|max:20480', // 20MB max
            'pro_file' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $project = new RdbProject();
        $project->fill($request->all());

        if ($request->hasFile('pro_abstract_file')) {
            $file = $request->file('pro_abstract_file');
            $filename = time() . '_abs_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads/projects', $filename);
            $project->pro_abstract_file = $filename;
        }

        if ($request->hasFile('pro_file')) {
            $file = $request->file('pro_file');
            $filename = time() . '_full_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads/projects', $filename);
            $project->pro_file = $filename;
        }

        $project->save();

        return redirect()->route('backend.rdb_project.index')->with('success', 'Project created successfully.');
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

        // Fetch all researchers (for Edit mode) but identify existing ones
        $existingResearcherIds = $project->rdbProjectWorks->pluck('researcher_id')->toArray();
        $allResearchers = RdbResearcher::with(['prefix', 'department'])
            ->whereNotNull('researcher_fname')
            ->where('researcher_fname', '!=', '')
            ->get();
        
        $positions = RdbProjectPosition::all();

        // Identify taken unique positions (1=Director, 2=Head)
        $takenPositions = $project->rdbProjectWorks->pluck('position_id')->toArray();
        $hasDirector = in_array(1, $takenPositions);
        $hasHead = in_array(2, $takenPositions);

        return view('backend.rdb_project.show', compact('project', 'allResearchers', 'positions', 'existingResearcherIds', 'takenPositions', 'hasDirector', 'hasHead'));
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
        \Illuminate\Support\Facades\Gate::authorize('Project'); // Enforce RBAC

        $project = RdbProject::findOrFail($id);
        
        $request->validate([
            'pro_nameTH' => 'required',
            'year_id' => 'required',
            'pro_abstract_file' => 'nullable|file|mimes:pdf|max:20480',
            'pro_file' => 'nullable|file|mimes:pdf|max:20480',
        ]);
        
        $project->fill($request->all());

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

        return redirect()->route('backend.rdb_project.index')->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('Project'); // Enforce RBAC

        $project = RdbProject::findOrFail($id);
        $project->delete();

        return redirect()->route('backend.rdb_project.index')->with('success', 'Project deleted successfully.');
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
        $work->save();

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
                'rf_files_show' => 1 // Default show
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
}
