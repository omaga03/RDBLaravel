<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbStrategic;
use Illuminate\Http\Request;

class RdbstrategicController extends Controller
{
    public function index()
    {
        $items = RdbStrategic::with('year')->paginate(10);
        return view('backend.rdbstrategic.index', compact('items'));
    }

    public function create()
    {
        $years = \App\Models\RdbYear::orderBy('year_name', 'desc')->get();
        $item = new RdbStrategic();
        return view('backend.rdbstrategic.create', compact('years', 'item'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'strategic_nameTH' => 'required|max:255|unique:rdb_strategic,strategic_nameTH',
        ], [
            'strategic_nameTH.unique' => 'ชื่อยุทธศาสตร์นี้มีอยู่ในระบบแล้ว',
        ]);
        $item = new RdbStrategic();
        $item->fill($request->all());
        // Note: RdbStrategic might not have user_created in fillable? Let's check model. 
        // Step 410 (Strategic Model) has user_created in fillable.
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();
        return redirect()->route('backend.rdbstrategic.show', $item->strategic_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbStrategic::with('year')->findOrFail($id);
        return view('backend.rdbstrategic.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbStrategic::findOrFail($id);
        $years = \App\Models\RdbYear::orderBy('year_name', 'desc')->get();
        return view('backend.rdbstrategic.edit', compact('item', 'years'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'strategic_nameTH' => 'required|max:255|unique:rdb_strategic,strategic_nameTH,' . $id . ',strategic_id',
        ], [
            'strategic_nameTH.unique' => 'ชื่อยุทธศาสตร์นี้มีอยู่ในระบบแล้ว',
        ]);
        $item = RdbStrategic::findOrFail($id);
        $item->fill($request->all());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();
        return redirect()->route('backend.rdbstrategic.show', $item->strategic_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbStrategic::findOrFail($id);
        
        // No data_show: Hard Delete with canDelete check
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbstrategic.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีโครงการวิจัยอ้างอิงยุทธศาสตร์นี้อยู่');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbstrategic.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
