<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilize;
use Illuminate\Http\Request;

class RdbProjectUtilizeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectUtilize::with(['project', 'changwat']);

        if ($request->filled('utz_department_name')) {
            $query->where('utz_department_name', 'like', '%' . $request->utz_department_name . '%');
        }

        $items = $query->orderBy('utz_date', 'desc')->paginate(20);

        return view('backend.rdb_projectutilize.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdb_projectutilize.create');
    }

    public function store(Request $request)
    {
        // Validation...
        $validated = $request->validate([
            'utz_department_name' => 'required',
        ]);
        RdbProjectUtilize::create($validated);
        return redirect()->route('backend.rdbprojectutilize.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }


    public function show($id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        return view('backend.rdb_projectutilize.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        return view('backend.rdb_projectutilize.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('backend.rdbprojectutilize.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbprojectutilize.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
