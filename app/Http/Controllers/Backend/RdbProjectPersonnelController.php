<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectPersonnel;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;

class RdbProjectPersonnelController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbProjectPersonnel::class);
        $query = RdbProjectPersonnel::with(['year', 'department', 'departmentCategory']);

        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $search = $request->input('search', $request->input('q'));
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('year', function ($yq) use ($search) {
                    $yq->where('year_name', 'like', "%{$search}%");
                })->orWhereHas('department', function ($dq) use ($search) {
                    $dq->where('department_nameTH', 'like', "%{$search}%")
                       ->orWhere('department_nameEN', 'like', "%{$search}%");
                });
            });
        }

        $items = $query->orderBy('year_id', 'desc')->orderBy('department_id')->paginate(15)->withQueryString();
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();

        return view('backend.rdbprojectpersonnel.index', compact('items', 'years', 'departments'));
    }

    public function create()
    {
        $this->authorize('create', RdbProjectPersonnel::class);
        $item = new RdbProjectPersonnel();
        $item->pp_standard = 60000;
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $categories = RdbDepartmentCategory::all();

        return view('backend.rdbprojectpersonnel.create', compact('item', 'years', 'departments', 'categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', RdbProjectPersonnel::class);
        $validated = $request->validate([
            'year_id' => 'required|exists:rdb_year,year_id',
            'department_id' => 'required|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'pp_num' => 'required|numeric|min:0',
            'pp_standard' => 'nullable|numeric|min:0',
        ]);

        $item = new RdbProjectPersonnel();
        $item->fill($validated);
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbprojectpersonnel.index')->with('success', 'บันทึกข้อมูลจำนวนบุคลากรเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectPersonnel::with(['year', 'department', 'departmentCategory'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbprojectpersonnel.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectPersonnel::findOrFail($id);
        $this->authorize('update', $item);
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $categories = RdbDepartmentCategory::all();

        return view('backend.rdbprojectpersonnel.edit', compact('item', 'years', 'departments', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectPersonnel::findOrFail($id);
        $this->authorize('update', $item);

        $validated = $request->validate([
            'year_id' => 'required|exists:rdb_year,year_id',
            'department_id' => 'required|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'pp_num' => 'required|numeric|min:0',
            'pp_standard' => 'nullable|numeric|min:0',
        ]);

        $item->fill($validated);
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbprojectpersonnel.index')->with('success', 'ปรับปรุงข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectPersonnel::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();

        return redirect()->route('backend.rdbprojectpersonnel.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
