<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartmentType;
use Illuminate\Http\Request;

class RdbdepartmenttypeController extends Controller
{
    public function index()
    {
        $items = RdbDepartmentType::orderBy('tdepartment_id')->paginate(20);
        return view('backend.rdbdepartmenttype.index', compact('items'));
    }

    public function create()
    {
        $item = new RdbDepartmentType();
        return view('backend.rdbdepartmenttype.create', compact('item'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tdepartment_nameTH' => 'required|max:255|unique:rdb_department_type,tdepartment_nameTH',
        ], [
            'tdepartment_nameTH.unique' => 'ชื่อประเภทนี้มีอยู่ในระบบแล้ว',
        ]);

        $item = new RdbDepartmentType();
        $item->fill($request->all());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbdepartmenttype.show', $item->tdepartment_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbDepartmentType::findOrFail($id);
        return view('backend.rdbdepartmenttype.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbDepartmentType::findOrFail($id);
        return view('backend.rdbdepartmenttype.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tdepartment_nameTH' => 'required|max:255|unique:rdb_department_type,tdepartment_nameTH,' . $id . ',tdepartment_id',
        ], [
            'tdepartment_nameTH.unique' => 'ชื่อประเภทนี้มีอยู่ในระบบแล้ว',
        ]);

        $item = RdbDepartmentType::findOrFail($id);
        $item->fill($request->all());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbdepartmenttype.show', $item->tdepartment_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbDepartmentType::findOrFail($id);
        
        // No data_show: Hard Delete with canDelete check
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbdepartmenttype.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีหน่วยงานอยู่ในประเภทนี้');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbdepartmenttype.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
