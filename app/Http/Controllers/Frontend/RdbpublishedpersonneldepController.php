<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedPersonnelDep;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbDepMajor;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;

class RdbpublishedpersonneldepController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublishedPersonnelDep::with(['year', 'department', 'departmentCourse', 'depMajor', 'departmentCategory']);

        $items = $query->orderBy('perpd_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedpersonneldep.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPublishedPersonnelDep::findOrFail($id);
        return view('frontend.rdbpublishedpersonneldep.show', compact('item'));
    }

    public function create()
    {
        $years = RdbYear::all();
        $departments = RdbDepartment::all();
        $courses = RdbDepartmentCourse::all();
        $majors = RdbDepMajor::all();
        $categories = RdbDepartmentCategory::all();
        return view('frontend.rdbpublishedpersonneldep.create', compact('years', 'departments', 'courses', 'majors', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'depcou_id' => 'nullable|exists:rdb_department_course,depcou_id',
            'major_id' => 'nullable|exists:rdb_dep_major,maj_code',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'personnel_num' => 'nullable|integer',
            'personnel_numedu' => 'nullable|integer',
            'personnel_numbud' => 'nullable|integer',
        ]);

        $validated['created_at'] = now();

        RdbPublishedPersonnelDep::create($validated);

        return redirect()->route('frontend.rdbpublishedpersonneldep.index')->with('success', 'Published Personnel Department created successfully.');
    }

    public function edit($id)
    {
        $item = RdbPublishedPersonnelDep::findOrFail($id);
        $years = RdbYear::all();
        $departments = RdbDepartment::all();
        $courses = RdbDepartmentCourse::all();
        $majors = RdbDepMajor::all();
        $categories = RdbDepartmentCategory::all();
        return view('frontend.rdbpublishedpersonneldep.edit', compact('item', 'years', 'departments', 'courses', 'majors', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedPersonnelDep::findOrFail($id);
        
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'depcou_id' => 'nullable|exists:rdb_department_course,depcou_id',
            'major_id' => 'nullable|exists:rdb_dep_major,maj_code',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'personnel_num' => 'nullable|integer',
            'personnel_numedu' => 'nullable|integer',
            'personnel_numbud' => 'nullable|integer',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbpublishedpersonneldep.index')->with('success', 'Published Personnel Department updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbPublishedPersonnelDep::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbpublishedpersonneldep.index')->with('success', 'Published Personnel Department deleted successfully.');
    }
}
