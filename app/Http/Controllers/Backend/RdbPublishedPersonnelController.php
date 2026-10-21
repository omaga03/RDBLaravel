<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedPersonnel;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;

class RdbPublishedPersonnelController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbPublishedPersonnel::class);
        $query = RdbPublishedPersonnel::with(['year', 'department', 'departmentCategory']);

        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $items = $query->orderBy('year_id', 'desc')->orderBy('department_id')->paginate(15);
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();

        return view('backend.rdbpublishedpersonnel.index', compact('items', 'years', 'departments'));
    }

    public function create()
    {
        $this->authorize('create', RdbPublishedPersonnel::class);
        $item = new RdbPublishedPersonnel();
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $categories = RdbDepartmentCategory::all();

        return view('backend.rdbpublishedpersonnel.create', compact('item', 'years', 'departments', 'categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', RdbPublishedPersonnel::class);
        $validated = $request->validate([
            'year_id' => 'required|exists:rdb_year,year_id',
            'department_id' => 'required|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'personnel_num' => 'required|numeric|min:0',
            'personnel_numedu' => 'nullable|numeric|min:0',
            'personnel_numbud' => 'nullable|numeric|min:0',
        ]);

        $item = new RdbPublishedPersonnel();
        $item->fill($validated);
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbpublishedpersonnel.index')->with('success', 'บันทึกข้อมูลจำนวนบุคลากร (ตีพิมพ์) เรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbPublishedPersonnel::with(['year', 'department', 'departmentCategory'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbpublishedpersonnel.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbPublishedPersonnel::findOrFail($id);
        $this->authorize('update', $item);
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $categories = RdbDepartmentCategory::all();

        return view('backend.rdbpublishedpersonnel.edit', compact('item', 'years', 'departments', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedPersonnel::findOrFail($id);
        $this->authorize('update', $item);

        $validated = $request->validate([
            'year_id' => 'required|exists:rdb_year,year_id',
            'department_id' => 'required|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'personnel_num' => 'required|numeric|min:0',
            'personnel_numedu' => 'nullable|numeric|min:0',
            'personnel_numbud' => 'nullable|numeric|min:0',
        ]);

        $item->fill($validated);
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbpublishedpersonnel.index')->with('success', 'ปรับปรุงข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbPublishedPersonnel::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();

        return redirect()->route('backend.rdbpublishedpersonnel.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
