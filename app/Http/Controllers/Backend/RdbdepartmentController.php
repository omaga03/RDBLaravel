<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentType;
use Illuminate\Http\Request;

class RdbdepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDepartment::with('departmentType');

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('department_nameTH', 'LIKE', "%{$search}%")
                  ->orWhere('department_code', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->orderBy('department_id')->paginate(20);
        return view('backend.rdbdepartment.index', compact('items'));
    }

    public function create()
    {
        $departmentTypes = RdbDepartmentType::orderBy('tdepartment_id')->get();
        $item = new RdbDepartment();
        return view('backend.rdbdepartment.create', compact('departmentTypes', 'item'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_code' => 'nullable|max:20|unique:rdb_department,department_code',
            'department_nameTH' => 'required|max:255|unique:rdb_department,department_nameTH',
        ], [
            'department_code.unique' => 'รหัสหน่วยงานนี้มีอยู่ในระบบแล้ว',
            'department_nameTH.unique' => 'ชื่อหน่วยงานนี้มีอยู่ในระบบแล้ว',
        ]);

        $item = new RdbDepartment();
        $item->fill($request->all());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbdepartment.show', $item->department_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbDepartment::with(['departmentType', 'creator', 'updater'])->findOrFail($id);
        return view('backend.rdbdepartment.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbDepartment::findOrFail($id);
        $departmentTypes = RdbDepartmentType::orderBy('tdepartment_id')->get();
        return view('backend.rdbdepartment.edit', compact('item', 'departmentTypes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'department_code' => 'nullable|max:20|unique:rdb_department,department_code,' . $id . ',department_id',
            'department_nameTH' => 'required|max:255|unique:rdb_department,department_nameTH,' . $id . ',department_id',
        ], [
            'department_code.unique' => 'รหัสหน่วยงานนี้มีอยู่ในระบบแล้ว',
            'department_nameTH.unique' => 'ชื่อหน่วยงานนี้มีอยู่ในระบบแล้ว',
        ]);

        $item = RdbDepartment::findOrFail($id);
        $item->fill($request->all());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbdepartment.show', $item->department_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbDepartment::findOrFail($id);
        $item->delete();
        
        return redirect()->route('backend.rdbdepartment.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
