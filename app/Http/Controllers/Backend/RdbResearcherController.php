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

class RdbResearcherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RdbResearcher::with('prefix');

        if ($request->filled('researcher_fname')) {
            $query->where('researcher_fname', 'like', '%' . $request->researcher_fname . '%');
        }
        if ($request->filled('researcher_lname')) {
            $query->where('researcher_lname', 'like', '%' . $request->researcher_lname . '%');
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $researchers = $query->orderBy('researcher_id', 'desc')->paginate(10);
        
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

        if ($request->hasFile('fileimg')) {
            $file = $request->file('fileimg');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads/researchers', $filename);
            $researcher->researcher_picture = $filename;
        }

        $researcher->save();

        return redirect()->route('backend.rdb_researcher.index')->with('success', 'Researcher created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $researcher = RdbResearcher::findOrFail($id);
        return view('backend.rdb_researcher.show', compact('researcher'));
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

        if ($request->hasFile('fileimg')) {
            // Delete old image
            if ($researcher->researcher_picture) {
                Storage::delete('public/uploads/researchers/' . $researcher->researcher_picture);
            }
            $file = $request->file('fileimg');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads/researchers', $filename);
            $researcher->researcher_picture = $filename;
        }

        $researcher->save();

        return redirect()->route('backend.rdb_researcher.index')->with('success', 'Researcher updated successfully.');
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
}
