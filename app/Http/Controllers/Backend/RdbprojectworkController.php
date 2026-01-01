<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectwork;
use Illuminate\Http\Request;

class RdbprojectworkController extends Controller
{
    public function index()
    {
        $items = RdbProjectwork::with(['project', 'researcher', 'position'])->paginate(10);
        return view('backend.rdbprojectwork.index', compact('items'));
    }

    public function create()
    {
        $projects = \App\Models\RdbProject::select('pro_id', 'pro_nameTH')->get();
        $researchers = \App\Models\RdbResearcher::select('researcher_id', 'researcher_fname', 'researcher_lname')->get();
        $positions = \App\Models\RdbProjectPosition::all();
        $item = new RdbProjectwork(); // Pass empty item for form
        return view('backend.rdbprojectwork.create', compact('projects', 'researchers', 'positions', 'item'));
    }

    public function store(Request $request)
    {
        $item = new RdbProjectwork();
        $item->fill($request->all());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();
        return redirect()->route('backend.rdbprojectwork.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectwork::with(['project', 'researcher', 'position'])->findOrFail($id);
        return view('backend.rdbprojectwork.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectwork::findOrFail($id);
        $projects = \App\Models\RdbProject::select('pro_id', 'pro_nameTH')->get();
        $researchers = \App\Models\RdbResearcher::select('researcher_id', 'researcher_fname', 'researcher_lname')->get();
        $positions = \App\Models\RdbProjectPosition::all();
        return view('backend.rdbprojectwork.edit', compact('item', 'projects', 'researchers', 'positions'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectwork::findOrFail($id);
        $item->fill($request->all());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();
        return redirect()->route('backend.rdbprojectwork.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectwork::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbprojectwork.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
