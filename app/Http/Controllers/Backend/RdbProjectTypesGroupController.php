<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectTypesGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RdbProjectTypesGroupController extends Controller
{
    public function index()
    {
        $items = RdbProjectTypesGroup::orderBy('pttg_id', 'desc')->paginate(10);
        $groupList = RdbProjectTypesGroup::getGroupList();
        return view('backend.rdbprojecttypesgroup.index', compact('items', 'groupList'));
    }

    public function create()
    {
        $groupList = RdbProjectTypesGroup::getGroupList();
        return view('backend.rdbprojecttypesgroup.create', compact('groupList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pttg_name' => 'required|max:255|unique:rdb_project_types_group,pttg_name',
            'pttg_group' => 'required|max:1',
        ], [
            'pttg_name.required' => 'กรุณากรอกชื่อกลุ่มงบประมาณ',
            'pttg_name.unique' => 'ชื่อกลุ่มงบประมาณนี้มีอยู่แล้วในระบบ',
            'pttg_name.max' => 'ชื่อกลุ่มงบประมาณต้องไม่เกิน 255 ตัวอักษร',
            'pttg_group.required' => 'กรุณากรอกรหัสกลุ่ม',
            'pttg_group.max' => 'รหัสกลุ่มต้องไม่เกิน 1 ตัวอักษร',
        ]);

        $input = $request->all();
        $input['user_created'] = auth()->id();
        $input['user_updated'] = auth()->id();
        $input['created_at'] = now();
        $input['updated_at'] = now();

        RdbProjectTypesGroup::create($input);

        return redirect()->route('backend.rdbprojecttypesgroup.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectTypesGroup::findOrFail($id);
        $groupList = RdbProjectTypesGroup::getGroupList();
        return view('backend.rdbprojecttypesgroup.show', compact('item', 'groupList'));
    }

    public function edit($id)
    {
        $item = RdbProjectTypesGroup::findOrFail($id);
        $groupList = RdbProjectTypesGroup::getGroupList();
        return view('backend.rdbprojecttypesgroup.edit', compact('item', 'groupList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pttg_name' => [
                'required',
                'max:255',
                Rule::unique('rdb_project_types_group', 'pttg_name')->ignore($id, 'pttg_id'),
            ],
            'pttg_group' => 'required|max:1',
        ], [
            'pttg_name.required' => 'กรุณากรอกชื่อกลุ่มงบประมาณ',
            'pttg_name.unique' => 'ชื่อกลุ่มงบประมาณนี้มีอยู่แล้วในระบบ',
            'pttg_name.max' => 'ชื่อกลุ่มงบประมาณต้องไม่เกิน 255 ตัวอักษร',
            'pttg_group.required' => 'กรุณากรอกรหัสกลุ่ม',
            'pttg_group.max' => 'รหัสกลุ่มต้องไม่เกิน 1 ตัวอักษร',
        ]);

        $item = RdbProjectTypesGroup::findOrFail($id);
        $input = $request->all();
        $input['user_updated'] = auth()->id();
        $input['updated_at'] = now();

        $item->update($input);

        return redirect()->route('backend.rdbprojecttypesgroup.index')->with('success', 'ปรับปรุงข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectTypesGroup::findOrFail($id);
        if ($item->canDelete()) {
            $item->delete();
            return redirect()->route('backend.rdbprojecttypesgroup.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        }
        return redirect()->route('backend.rdbprojecttypesgroup.index')->with('error', 'ไม่สามารถลบข้อมูลได้เนื่องจากมีการใช้งานอยู่');
    }
}
