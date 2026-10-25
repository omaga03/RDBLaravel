<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedPersonnelDep;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbDepMajor;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;

class RdbPublishedPersonnelDepController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbPublishedPersonnelDep::class);
        $query = RdbPublishedPersonnelDep::with(['year', 'department', 'departmentCourse', 'depMajor', 'departmentCategory']);

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
                    $dq->where('department_nameTH', 'like', "%{$search}%");
                });
            });
        }

        $items = $query->orderBy('year_id', 'desc')->orderBy('department_id')->paginate(15)->withQueryString();
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();

        return view('backend.rdbpublishedpersonneldep.index', compact('items', 'years', 'departments'));
    }

    public function create()
    {
        $this->authorize('create', RdbPublishedPersonnelDep::class);
        $item = new RdbPublishedPersonnelDep();
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $courses = RdbDepartmentCourse::orderBy('depcou_name')->get();
        $majors = RdbDepMajor::orderBy('maj_nameTH')->get();
        $categories = RdbDepartmentCategory::all();

        return view('backend.rdbpublishedpersonneldep.create', compact('item', 'years', 'departments', 'courses', 'majors', 'categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', RdbPublishedPersonnelDep::class);
        $validated = $request->validate([
            'year_id' => 'required|exists:rdb_year,year_id',
            'department_id' => 'required|exists:rdb_department,department_id',
            'depcou_id' => 'nullable|integer',
            'major_id' => 'nullable|string|max:50',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'personnel_num' => 'required|numeric|min:0',
            'personnel_numedu' => 'nullable|numeric|min:0',
            'personnel_numbud' => 'nullable|numeric|min:0',
        ]);

        $item = new RdbPublishedPersonnelDep();
        $item->fill($validated);
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbpublishedpersonneldep.index')->with('success', 'บันทึกข้อมูลจำนวนบุคลากร (ระดับคณะ/หลักสูตร) เรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbPublishedPersonnelDep::with(['year', 'department', 'departmentCourse', 'depMajor', 'departmentCategory'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbpublishedpersonneldep.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbPublishedPersonnelDep::findOrFail($id);
        $this->authorize('update', $item);
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $courses = RdbDepartmentCourse::orderBy('depcou_name')->get();
        $majors = RdbDepMajor::orderBy('maj_nameTH')->get();
        $categories = RdbDepartmentCategory::all();

        return view('backend.rdbpublishedpersonneldep.edit', compact('item', 'years', 'departments', 'courses', 'majors', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedPersonnelDep::findOrFail($id);
        $this->authorize('update', $item);

        $validated = $request->validate([
            'year_id' => 'required|exists:rdb_year,year_id',
            'department_id' => 'required|exists:rdb_department,department_id',
            'depcou_id' => 'nullable|integer',
            'major_id' => 'nullable|string|max:50',
            'depcat_id' => 'nullable|exists:rdb_department_category,depcat_id',
            'personnel_num' => 'required|numeric|min:0',
            'personnel_numedu' => 'nullable|numeric|min:0',
            'personnel_numbud' => 'nullable|numeric|min:0',
        ]);

        $item->fill($validated);
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbpublishedpersonneldep.index')->with('success', 'อัปเดตข้อมูลจำนวนบุคลากร (ระดับคณะ/หลักสูตร) เรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbPublishedPersonnelDep::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();

        return redirect()->route('backend.rdbpublishedpersonneldep.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
