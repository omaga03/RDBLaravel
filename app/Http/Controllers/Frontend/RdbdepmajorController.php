<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepMajor;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbDepartment;
use Illuminate\Http\Request;

class RdbdepmajorController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDepMajor::with(['departmentCourse', 'department']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('maj_nameTH', 'like', "%{$search}%")
                  ->orWhere('maj_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('maj_code', 'desc')->paginate(10);
        
        return view('frontend.rdbdepmajor.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDepMajor::with(['departmentCourse', 'department'])->findOrFail($id);
        return view('frontend.rdbdepmajor.show', compact('item'));
    }

    public function create()
    {
        $courses = RdbDepartmentCourse::all();
        $departments = RdbDepartment::all();
        return view('frontend.rdbdepmajor.create', compact('courses', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'maj_id' => 'nullable|string|max:20',
            'depcou_id' => 'nullable|exists:rdb_department_course,depcou_id',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'maj_nameTH' => 'required|string|max:255',
            'maj_nameEN' => 'nullable|string|max:255',
        ]);

        $validated['created_at'] = now();

        RdbDepMajor::create($validated);

        return redirect()->route('frontend.rdbdepmajor.index')->with('success', 'Major created successfully.');
    }

    public function edit($id)
    {
        $item = RdbDepMajor::findOrFail($id);
        $courses = RdbDepartmentCourse::all();
        $departments = RdbDepartment::all();
        return view('frontend.rdbdepmajor.edit', compact('item', 'courses', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDepMajor::findOrFail($id);
        
        $validated = $request->validate([
            'maj_id' => 'nullable|string|max:20',
            'depcou_id' => 'nullable|exists:rdb_department_course,depcou_id',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'maj_nameTH' => 'required|string|max:255',
            'maj_nameEN' => 'nullable|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbdepmajor.index')->with('success', 'Major updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDepMajor::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbdepmajor.index')->with('success', 'Major deleted successfully.');
    }
}
