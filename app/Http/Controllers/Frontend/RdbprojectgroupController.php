<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectGroup;
use App\Models\RdbProject;
use App\Models\RdbGroupProject;
use Illuminate\Http\Request;

class RdbprojectgroupController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectGroup::with(['project', 'groupProject']);

        if ($request->filled('search')) {
            $search = $request->search;
            // Search by project name or group name
            $query->whereHas('project', function($q) use ($search) {
                $q->where('pro_nameTH', 'like', "%{$search}%");
            })->orWhereHas('groupProject', function($q) use ($search) {
                $q->where('pgroup_nameTH', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('group_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectgroup.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectGroup::with(['project', 'groupProject'])->findOrFail($id);
        return view('frontend.rdbprojectgroup.show', compact('item'));
    }

    public function create()
    {
        $projects = RdbProject::all();
        $groups = RdbGroupProject::all();
        return view('frontend.rdbprojectgroup.create', compact('projects', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pro_id' => 'required|exists:rdb_project,pro_id',
            'pgroup_id' => 'required|exists:rdb_groupproject,pgroup_id',
        ]);

        $validated['created_at'] = now();

        RdbProjectGroup::create($validated);

        return redirect()->route('frontend.rdbprojectgroup.index')->with('success', 'Project Group assignment created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectGroup::findOrFail($id);
        $projects = RdbProject::all();
        $groups = RdbGroupProject::all();
        return view('frontend.rdbprojectgroup.edit', compact('item', 'projects', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectGroup::findOrFail($id);
        
        $validated = $request->validate([
            'pro_id' => 'required|exists:rdb_project,pro_id',
            'pgroup_id' => 'required|exists:rdb_groupproject,pgroup_id',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprojectgroup.index')->with('success', 'Project Group assignment updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectGroup::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprojectgroup.index')->with('success', 'Project Group assignment deleted successfully.');
    }
}
