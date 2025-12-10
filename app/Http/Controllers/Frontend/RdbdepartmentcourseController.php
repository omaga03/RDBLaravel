<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;

class RdbdepartmentcourseController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDepartmentCourse::with(['department', 'departmentCategory']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('cou_name', 'like', "%{$search}%")
                  ->orWhere('cou_name_sh', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('depcou_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdepartmentcourse.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDepartmentCourse::with(['department', 'departmentCategory'])->findOrFail($id);
        return view('frontend.rdbdepartmentcourse.show', compact('item'));
    }

    public function create()
    {
        $departments = RdbDepartment::all();
        $categories = RdbDepartmentCategory::all();
        return view('frontend.rdbdepartmentcourse.create', compact('departments', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'cou_name' => 'required|string|max:255',
            'cou_name_sh' => 'nullable|string|max:100',
        ]);

        $validated['created_at'] = now();

        RdbDepartmentCourse::create($validated);

        return redirect()->route('frontend.rdbdepartmentcourse.index')->with('success', 'Course created successfully.');
    }

    public function edit($id)
    {
        $item = RdbDepartmentCourse::findOrFail($id);
        $departments = RdbDepartment::all();
        $categories = RdbDepartmentCategory::all();
        return view('frontend.rdbdepartmentcourse.edit', compact('item', 'departments', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDepartmentCourse::findOrFail($id);
        
        $validated = $request->validate([
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'cou_name' => 'required|string|max:255',
            'cou_name_sh' => 'nullable|string|max:100',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbdepartmentcourse.index')->with('success', 'Course updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDepartmentCourse::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbdepartmentcourse.index')->with('success', 'Course deleted successfully.');
    }
}
