<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedPersonnel;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;

class RdbpublishedpersonnelController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublishedPersonnel::with(['year', 'department', 'departmentCategory']);

        $items = $query->orderBy('personnel_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedpersonnel.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPublishedPersonnel::with(['year', 'department', 'departmentCategory'])->findOrFail($id);
        return view('frontend.rdbpublishedpersonnel.show', compact('item'));
    }

    public function create()
    {
        $years = RdbYear::all();
        $departments = RdbDepartment::all();
        $categories = RdbDepartmentCategory::all();
        return view('frontend.rdbpublishedpersonnel.create', compact('years', 'departments', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'personnel_num' => 'nullable|integer',
            'personnel_numedu' => 'nullable|integer',
            'personnel_numbud' => 'nullable|integer',
        ]);

        $validated['created_at'] = now();

        RdbPublishedPersonnel::create($validated);

        return redirect()->route('frontend.rdbpublishedpersonnel.index')->with('success', 'Personnel created successfully.');
    }

    public function edit($id)
    {
        $item = RdbPublishedPersonnel::findOrFail($id);
        $years = RdbYear::all();
        $departments = RdbDepartment::all();
        $categories = RdbDepartmentCategory::all();
        return view('frontend.rdbpublishedpersonnel.edit', compact('item', 'years', 'departments', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedPersonnel::findOrFail($id);
        
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'personnel_num' => 'nullable|integer',
            'personnel_numedu' => 'nullable|integer',
            'personnel_numbud' => 'nullable|integer',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbpublishedpersonnel.index')->with('success', 'Personnel updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbPublishedPersonnel::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbpublishedpersonnel.index')->with('success', 'Personnel deleted successfully.');
    }
}
