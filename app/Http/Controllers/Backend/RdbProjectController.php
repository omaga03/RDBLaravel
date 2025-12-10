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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RdbProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RdbProject::with('status');

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
        $project = RdbProject::findOrFail($id);
        return view('backend.rdb_project.show', compact('project'));
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
}
