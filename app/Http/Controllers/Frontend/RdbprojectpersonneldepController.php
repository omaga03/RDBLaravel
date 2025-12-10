<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectPersonnelDep;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbDepMajor;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;

class RdbprojectpersonneldepController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectPersonnelDep::with(['year', 'department', 'departmentCourse', 'depMajor', 'departmentCategory']);

        $items = $query->orderBy('ppd_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectpersonneldep.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectPersonnelDep::findOrFail($id);
        return view('frontend.rdbprojectpersonneldep.show', compact('item'));
    }

    public function create()
    {
        $years = RdbYear::all();
        $departments = RdbDepartment::all();
        $courses = RdbDepartmentCourse::all();
        $majors = RdbDepMajor::all();
        $categories = RdbDepartmentCategory::all();
        return view('frontend.rdbprojectpersonneldep.create', compact('years', 'departments', 'courses', 'majors', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'depcou_id' => 'nullable|exists:rdb_department_course,depcou_id',
            'major_id' => 'nullable|exists:rdb_dep_major,maj_code', // Note: maj_code is PK of RdbDepMajor
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'pp_num' => 'nullable|integer',
            'pp_standard' => 'nullable|integer',
        ]);

        $validated['created_at'] = now();

        RdbProjectPersonnelDep::create($validated);

        return redirect()->route('frontend.rdbprojectpersonneldep.index')->with('success', 'Personnel Department created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectPersonnelDep::findOrFail($id);
        $years = RdbYear::all();
        $departments = RdbDepartment::all();
        $courses = RdbDepartmentCourse::all();
        $majors = RdbDepMajor::all();
        $categories = RdbDepartmentCategory::all();
        return view('frontend.rdbprojectpersonneldep.edit', compact('item', 'years', 'departments', 'courses', 'majors', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectPersonnelDep::findOrFail($id);
        
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'depcou_id' => 'nullable|exists:rdb_department_course,depcou_id',
            'major_id' => 'nullable|exists:rdb_dep_major,maj_code',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'pp_num' => 'nullable|integer',
            'pp_standard' => 'nullable|integer',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprojectpersonneldep.index')->with('success', 'Personnel Department updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectPersonnelDep::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprojectpersonneldep.index')->with('success', 'Personnel Department deleted successfully.');
    }
}
