<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbGroupproject;
use Illuminate\Http\Request;

class RdbgroupprojectController extends Controller
{
    public function index()
    {
        $items = RdbGroupproject::paginate(10);
        return view('backend.rdbgroupproject.index', compact('items'));
    }

    public function create()
    {
        $item = new RdbGroupproject();
        return view('backend.rdbgroupproject.create', compact('item'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pgroup_nameTH' => 'required|max:255|unique:rdb_groupproject,pgroup_nameTH',
        ], [
            'pgroup_nameTH.unique' => 'ชื่อกลุ่มโครงการนี้มีอยู่ในระบบแล้ว',
        ]);
        $item = new RdbGroupproject();
        $item->fill($request->all());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();
        return redirect()->route('backend.rdbgroupproject.show', $item->pgroup_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbGroupproject::findOrFail($id);
        return view('backend.rdbgroupproject.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbGroupproject::findOrFail($id);
        return view('backend.rdbgroupproject.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pgroup_nameTH' => 'required|max:255|unique:rdb_groupproject,pgroup_nameTH,' . $id . ',pgroup_id',
        ], [
            'pgroup_nameTH.unique' => 'ชื่อกลุ่มโครงการนี้มีอยู่ในระบบแล้ว',
        ]);
        $item = RdbGroupproject::findOrFail($id);
        $item->fill($request->all());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();
        return redirect()->route('backend.rdbgroupproject.show', $item->pgroup_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbGroupproject::findOrFail($id);
        
        // No data_show: Hard Delete with canDelete check
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbgroupproject.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีโครงการวิจัยอยู่ในกลุ่มนี้');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbgroupproject.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
