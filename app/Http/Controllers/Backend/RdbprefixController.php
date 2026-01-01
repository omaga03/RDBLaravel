<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPrefix;
use Illuminate\Http\Request;

class RdbprefixController extends Controller
{
    public function index()
    {
        $items = RdbPrefix::paginate(10);
        return view('backend.rdbprefix.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbprefix.create');
    }

    public function store(Request $request)
    {
        $item = new RdbPrefix();
        $item->fill($request->all());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();
        return redirect()->route('backend.rdbprefix.show', $item->prefix_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbPrefix::findOrFail($id);
        return view('backend.rdbprefix.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbPrefix::findOrFail($id);
        return view('backend.rdbprefix.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPrefix::findOrFail($id);
        $item->fill($request->all());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();
        return redirect()->route('backend.rdbprefix.show', $item->prefix_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbPrefix::findOrFail($id);
        
        // No data_show: Hard Delete with canDelete check
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbprefix.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีนักวิจัยใช้งานคำนำหน้านี้อยู่');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbprefix.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
