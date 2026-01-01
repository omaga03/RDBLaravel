<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectposition;
use Illuminate\Http\Request;

class RdbprojectpositionController extends Controller
{
    public function index()
    {
        $items = RdbProjectposition::paginate(10);
        return view('backend.rdbprojectposition.index', compact('items'));
    }

    public function create()
    {
        $item = new RdbProjectposition();
        return view('backend.rdbprojectposition.create', compact('item'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'position_nameTH' => 'required|max:255|unique:rdb_project_position,position_nameTH',
        ], [
            'position_nameTH.unique' => 'ชื่อตำแหน่งนี้มีอยู่ในระบบแล้ว',
        ]);
        $item = new RdbProjectposition();
        $item->fill($request->all());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();
        return redirect()->route('backend.rdbprojectposition.show', $item->position_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectposition::findOrFail($id);
        return view('backend.rdbprojectposition.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectposition::findOrFail($id);
        return view('backend.rdbprojectposition.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'position_nameTH' => 'required|max:255|unique:rdb_project_position,position_nameTH,' . $id . ',position_id',
        ], [
            'position_nameTH.unique' => 'ชื่อตำแหน่งนี้มีอยู่ในระบบแล้ว',
        ]);
        $item = RdbProjectposition::findOrFail($id);
        $item->fill($request->all());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();
        return redirect()->route('backend.rdbprojectposition.show', $item->position_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectposition::findOrFail($id);
        
        // No data_show: Hard Delete with canDelete check
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbprojectposition.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีนักวิจัยใช้งานตำแหน่งนี้อยู่');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbprojectposition.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
