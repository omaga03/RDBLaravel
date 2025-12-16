<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbResearcher;
use App\Models\RdbPrefix;
use App\Models\RdbDepartment;
use App\Models\RdbDepMajor;
use App\Models\RdbDepartmentCategory;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbResearcherStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RdbResearcherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RdbResearcher::with(['prefix', 'department']);

        if ($request->filled('researcher_fname')) {
            $query->where('researcher_fname', 'like', '%' . $request->researcher_fname . '%');
        }
        if ($request->filled('researcher_lname')) {
            $query->where('researcher_lname', 'like', '%' . $request->researcher_lname . '%');
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $researchers = $query->leftJoin('rdb_department', 'rdb_researcher.department_id', '=', 'rdb_department.department_id')
                             ->select('rdb_researcher.*')
                             ->orderByRaw('rdb_department.department_nameTH IS NULL ASC') // Push NULLs to bottom
                             ->orderBy('rdb_department.department_nameTH', 'asc')
                             ->orderBy('rdb_researcher.researcher_fname', 'asc')
                             ->paginate(10);
        
        $departments = RdbDepartment::all(); // For filter dropdown

        return view('backend.rdb_researcher.index', compact('researchers', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $prefixes = RdbPrefix::all();
        $departments = RdbDepartment::all();
        $majors = RdbDepMajor::all();
        $categories = RdbDepartmentCategory::all();
        $courses = RdbDepartmentCourse::all();
        $statuses = RdbResearcherStatus::all();

        return view('backend.rdb_researcher.create', compact('prefixes', 'departments', 'majors', 'categories', 'courses', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('Project'); // Enforce RBAC

        $request->validate([
            'researcher_fname' => 'required|string|max:255',
            'researcher_lname' => 'required|string|max:255',
            'researcher_email' => 'nullable|email|unique:rdb_researcher,researcher_email',
            'fileimg' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $researcher = new RdbResearcher();
        $researcher->fill($request->all());

        // Process Code ID from Citizen ID if provided
        if ($request->filled('citizen_id')) {
            $citizenId = str_replace('-', '', $request->citizen_id);
            if (!$this->isValidThaiCitizenId($citizenId)) {
                return redirect()->back()->withInput()->with('error', 'หมายเลขบัตรประจำตัวประชาชนไม่ถูกต้อง');
            }
            $researcher->researcher_codeid = $this->generateCodeId($citizenId);
        }

        $researcher->save();

        if ($request->hasFile('fileimg')) {
            $file = $request->file('fileimg');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = $researcher->researcher_id . '-' . Str::random(5) . '.' . $extension;
            
            $destinationPath = storage_path('app/public/uploads/researchers');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);

            $researcher->researcher_picture = $filename;
            $researcher->save();
        }

        return redirect()->route('backend.rdb_researcher.show', $researcher->researcher_id)->with('success', 'Researcher created successfully.');
    }

    // ... (index, show, edit methods remain unchanged, assume they are outside this block or I need to be careful with range)
    // Actually, I am replacing store and update. But wait, I can't replace scattered methods easily with one block unless I replace the whole file or be very specific.
    // I will replace Store first. Then Update. Then add Helper.

    /**
     * Helper to generate Researcher Code ID
     * Logic: MD5(Key + CleanCitizenID)
     */
    private function generateCodeId($citizenId)
    {
        $key = ';b0ypc]try<ok-';
        $cleanId = str_replace('-', '', $citizenId);
        return md5($key . $cleanId);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $researcher = RdbResearcher::with([
            'prefix', 
            'department', 
            'major', 
            'departmentCategory', 
            'departmentCourse', 
            'status', 
            'rdbResearcherEducations'
        ])->findOrFail($id);
        
        // Load projects with proper sorting and relationships
        $researcher->load(['rdbProjects' => function($query) {
            $query->with(['year', 'type', 'typeSub', 'status'])
                  ->orderBy('year_id', 'desc')
                  ->orderBy('pro_budget', 'desc')
                  ->orderBy('pro_id', 'desc');
        }]);
        
        // Load publications
        $researcher->load(['rdbPublisheds' => function($query) use ($id) {
            $query->with([
                'project',
                'pubtype'
            ])
            ->orderBy('pub_date', 'desc')
            ->orderBy('id', 'desc');
        }]);
        
        // Load author types for publications manually
        $authorTypeIds = $researcher->rdbPublisheds->pluck('pivot.pubta_id')->filter()->unique();
        if ($authorTypeIds->isNotEmpty()) {
            $authorTypes = \App\Models\RdbPublishedTypeAuthor::whereIn('pubta_id', $authorTypeIds)->get()->keyBy('pubta_id');
            foreach ($researcher->rdbPublisheds as $pub) {
                if ($pub->pivot && $pub->pivot->pubta_id && isset($authorTypes[$pub->pivot->pubta_id])) {
                    $pub->authorType = $authorTypes[$pub->pivot->pubta_id];
                }
            }
        }
        
        // Load utilizations through projects
        $utilizations = \App\Models\RdbProjectUtilize::whereHas('project.rdbProjectWorks', function($q) use ($id) {
            $q->where('researcher_id', $id);
        })->with(['project', 'changwat'])
          ->orderBy('utz_date', 'desc')
          ->get();
        
        // Load intellectual properties
        $researcher->load(['rdbDips' => function($query) {
            $query->with(['project', 'dipType'])
                  ->orderBy('dip_request_date', 'desc')
                  ->orderBy('dip_id', 'desc');
        }]);
        
        // Load Project Positions lookup
        $projectPositions = \App\Models\RdbProjectPosition::all()->keyBy('position_id');

        return view('backend.rdb_researcher.show', compact('researcher', 'utilizations', 'projectPositions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $researcher = RdbResearcher::findOrFail($id);
        $prefixes = RdbPrefix::all();
        $departments = RdbDepartment::all();
        $majors = RdbDepMajor::all();
        $categories = RdbDepartmentCategory::all();
        $courses = RdbDepartmentCourse::all();
        $statuses = RdbResearcherStatus::all();

        return view('backend.rdb_researcher.edit', compact('researcher', 'prefixes', 'departments', 'majors', 'categories', 'courses', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('Project'); // Enforce RBAC

        $researcher = RdbResearcher::findOrFail($id);
        
        $request->validate([
            'researcher_fname' => 'required|string|max:255',
            'researcher_lname' => 'required|string|max:255',
            'researcher_email' => 'nullable|email|unique:rdb_researcher,researcher_email,' . $id . ',researcher_id',
            'fileimg' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $researcher->fill($request->all());
        
        // Process Code ID from Citizen ID if provided
        if ($request->filled('citizen_id')) {
            $citizenId = str_replace('-', '', $request->citizen_id);
            if (!$this->isValidThaiCitizenId($citizenId)) {
                return redirect()->back()->withInput()->with('error', 'หมายเลขบัตรประจำตัวประชาชนไม่ถูกต้อง');
            }
            $researcher->researcher_codeid = $this->generateCodeId($citizenId);
        }

        if ($request->hasFile('fileimg')) {
            // Delete old image using direct unlink to match move() logic
            if ($researcher->researcher_picture) {
                $oldPath = storage_path('app/public/uploads/researchers/' . $researcher->researcher_picture);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $file = $request->file('fileimg');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = $researcher->researcher_id . '-' . Str::random(5) . '.' . $extension;
            
            $destinationPath = storage_path('app/public/uploads/researchers');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);

            $researcher->researcher_picture = $filename;
        }

        $researcher->save();

        return redirect()->route('backend.rdb_researcher.show', $id)->with('success', 'Researcher updated successfully.');
    }

    public function updateCodeId(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('Project'); // Enforce RBAC

        $request->validate([
            'citizen_id' => 'required|string',
        ]);

        // Validate Thai Citizen ID
        $citizenId = str_replace('-', '', $request->citizen_id);
        if (!$this->isValidThaiCitizenId($citizenId)) {
            return redirect()->back()->with('error', 'หมายเลขบัตรประจำตัวประชาชนไม่ถูกต้อง (Invalid Thai Citizen ID)');
        }

        // Generate Code ID (Standard)
        $newCodeId = $this->generateCodeId($citizenId);
        
        // Check for Uniqueness (Duplicate Check)
        $duplicate = RdbResearcher::where('researcher_codeid', $newCodeId)
                                  ->where('researcher_id', '!=', $id)
                                  ->exists();

        if ($duplicate) {
            return redirect()->back()->with('error', 'ไม่สามารถบันทึกได้ : หมายเลขบัตรประชาชนนี้ถูกลงทะเบียนไว้แล้วในระบบ (Duplicate Data)');
        }

        $researcher = RdbResearcher::findOrFail($id);
        $researcher->researcher_codeid = $newCodeId;
        $researcher->save();

        return redirect()->back()->with('success', 'Researcher Code ID updated successfully.');
    }

    public function updateImage(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('Project'); // Enforce RBAC

        $request->validate([
            'fileimg' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $researcher = RdbResearcher::findOrFail($id);

        if ($request->hasFile('fileimg')) {
            // Delete old image using direct unlink to match move() logic
            if ($researcher->researcher_picture) {
                $oldPath = storage_path('app/public/uploads/researchers/' . $researcher->researcher_picture);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $file = $request->file('fileimg');
            $extension = $file->getClientOriginalExtension() ?: 'jpg'; // Fallback ext
            $filename = $researcher->researcher_id . '-' . Str::random(5) . '.' . $extension;
            
            // Store using direct move to bypass Storage facade issues
            $destinationPath = storage_path('app/public/uploads/researchers');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            
            $researcher->researcher_picture = $filename;
            $researcher->save();
        }

        return redirect()->back()->with('success', 'Profile picture updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('Project'); // Enforce RBAC

        $researcher = RdbResearcher::findOrFail($id);
        $researcher->delete();

        return redirect()->route('backend.rdb_researcher.index')->with('success', 'Researcher deleted successfully.');
    }
    /**
     * Validate Thai Citizen ID
     */
    private function isValidThaiCitizenId($id)
    {
        if (strlen($id) != 13) return false;
        
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$id[$i] * (13 - $i);
        }
        
        $check = (11 - ($sum % 11)) % 10;
        
        return $check == (int)$id[12];
    }
}
