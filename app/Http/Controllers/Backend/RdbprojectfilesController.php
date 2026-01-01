<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectfiles;
use Illuminate\Http\Request;

class RdbprojectfilesController extends Controller
{
    public function index()
    {
        $items = RdbProjectfiles::with('project')->paginate(10);
        return view('backend.rdbprojectfiles.index', compact('items'));
    }

    public function create()
    {
        // Files are usually added via project, but if we allow creating here:
        $projects = \App\Models\RdbProject::select('pro_id', 'pro_nameTH')->get();
        $item = new RdbProjectfiles();
        return view('backend.rdbprojectfiles.create', compact('projects', 'item'));
    }

    public function store(Request $request)
    {
        $item = new RdbProjectfiles();
        $item->fill($request->all());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();
        return redirect()->route('backend.rdbprojectfiles.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectfiles::with('project')->findOrFail($id);
        return view('backend.rdbprojectfiles.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectfiles::findOrFail($id);
        $projects = \App\Models\RdbProject::select('pro_id', 'pro_nameTH')->get();
        return view('backend.rdbprojectfiles.edit', compact('item', 'projects')); // Projects possibly needed if we allow changing project
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectfiles::findOrFail($id);
        $item->fill($request->all());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();
        return redirect()->route('backend.rdbprojectfiles.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectfiles::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbprojectfiles.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
