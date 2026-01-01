<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbYear;
use Illuminate\Http\Request;

class RdbyearController extends Controller
{
    public function index()
    {
        $items = RdbYear::orderBy('year_name', 'desc')->paginate(10);
        return view('backend.rdbyear.index', compact('items'));
    }

    public function create()
    {
        $item = new RdbYear();
        return view('backend.rdbyear.create', compact('item'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'year_name' => 'required|max:4|unique:rdb_year,year_name',
        ], [
            'year_name.unique' => 'ปีงบประมาณนี้มีอยู่ในระบบแล้ว',
        ]);
        $item = new RdbYear();
        $item->fill($request->all());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();
        return redirect()->route('backend.rdbyear.show', $item->year_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbYear::findOrFail($id);
        return view('backend.rdbyear.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbYear::findOrFail($id);
        return view('backend.rdbyear.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'year_name' => 'required|max:4|unique:rdb_year,year_name,' . $id . ',year_id',
        ], [
            'year_name.unique' => 'ปีงบประมาณนี้มีอยู่ในระบบแล้ว',
        ]);
        $item = RdbYear::findOrFail($id);
        $item->fill($request->all());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();
        return redirect()->route('backend.rdbyear.show', $item->year_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbYear::findOrFail($id);
        
        // No data_show: Hard Delete with canDelete check
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbyear.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีโครงการวิจัยในปีงบประมาณนี้อยู่');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbyear.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
