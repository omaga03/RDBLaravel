<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDip;
use Illuminate\Http\Request;

class RdbDipController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDip::with(['dipType', 'researcher', 'project']);

        // Filter: Dip Request Number
        if ($request->filled('dip_request_number')) {
            $query->where('dip_request_number', 'like', '%' . $request->dip_request_number . '%');
        }

        $items = $query->orderBy('dip_request_date', 'desc')->paginate(20);

        return view('backend.rdb_dip.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdb_dip.create');
    }

    public function store(Request $request)
    {
        // Validation logic here
        $validated = $request->validate([
            'dip_request_number' => 'required',
        ]);
        RdbDip::create($validated);
        return redirect()->route('backend.rdb_dip.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbDip::findOrFail($id);
        return view('backend.rdb_dip.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbDip::findOrFail($id);
        return view('backend.rdb_dip.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDip::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('backend.rdb_dip.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbDip::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdb_dip.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
