<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectPersonnel;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;

class RdbprojectpersonnelController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectPersonnel::with(['year', 'department', 'departmentCategory']);

        $items = $query->orderBy('pp_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectpersonnel.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectPersonnel::with(['year', 'department', 'departmentCategory'])->findOrFail($id);
        return view('frontend.rdbprojectpersonnel.show', compact('item'));
    }

    public function create()
    {
        $years = RdbYear::all();
        $departments = RdbDepartment::all();
        $categories = RdbDepartmentCategory::all();
        return view('frontend.rdbprojectpersonnel.create', compact('years', 'departments', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'pp_num' => 'nullable|integer',
            'pp_standard' => 'nullable|integer',
        ]);

        $validated['created_at'] = now();

        RdbProjectPersonnel::create($validated);

        return redirect()->route('frontend.rdbprojectpersonnel.index')->with('success', 'Personnel created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectPersonnel::findOrFail($id);
        $years = RdbYear::all();
        $departments = RdbDepartment::all();
        $categories = RdbDepartmentCategory::all();
        return view('frontend.rdbprojectpersonnel.edit', compact('item', 'years', 'departments', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectPersonnel::findOrFail($id);
        
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'pp_num' => 'nullable|integer',
            'pp_standard' => 'nullable|integer',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprojectpersonnel.index')->with('success', 'Personnel updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectPersonnel::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprojectpersonnel.index')->with('success', 'Personnel deleted successfully.');
    }
}
