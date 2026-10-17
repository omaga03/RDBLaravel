<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectWork;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbProjectWorkRequest, UpdateRdbProjectWorkRequest};

class RdbprojectworkController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RdbProjectWork::class);
        $items = RdbProjectWork::with(['project', 'researcher', 'position', 'creator', 'updater'])->paginate(10);
        return view('backend.rdbprojectwork.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbProjectWork::class);
        $projects = \App\Models\RdbProject::select('pro_id', 'pro_nameTH')->get();
        $researchers = \App\Models\RdbResearcher::select('researcher_id', 'researcher_fname', 'researcher_lname')->get();
        $positions = \App\Models\RdbProjectPosition::all();
        $item = new RdbProjectWork();
        return view('backend.rdbprojectwork.create', compact('projects', 'researchers', 'positions', 'item'));
    }

    public function store(StoreRdbProjectWorkRequest $request)
    {
        $this->authorize('create', RdbProjectWork::class);
        $item = new RdbProjectWork();
        $item->fill($request->validated());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();
        return redirect()->route('backend.rdbprojectwork.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectWork::with(['project', 'researcher', 'position', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbprojectwork.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectWork::with(['project', 'researcher', 'position', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        $projects = \App\Models\RdbProject::select('pro_id', 'pro_nameTH')->get();
        $researchers = \App\Models\RdbResearcher::select('researcher_id', 'researcher_fname', 'researcher_lname')->get();
        $positions = \App\Models\RdbProjectPosition::all();
        return view('backend.rdbprojectwork.edit', compact('item', 'projects', 'researchers', 'positions'));
    }

    public function update(UpdateRdbProjectWorkRequest $request, $id)
    {
        $item = RdbProjectwork::findOrFail($id);
        $this->authorize('update', $item);
        $item->fill($request->validated());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();
        return redirect()->route('backend.rdbprojectwork.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectwork::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();
        return redirect()->route('backend.rdbprojectwork.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}