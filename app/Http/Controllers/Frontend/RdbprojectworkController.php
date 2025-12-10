<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectWork;
use App\Models\RdbProject;
use App\Models\RdbResearcher;
use App\Models\RdbProjectPosition;
use Illuminate\Http\Request;

class RdbprojectworkController extends Controller
{
    public function index(Request $request)
    {
        // Note: pro_id is PK, but it might be a composite key or foreign key in reality.
        // Assuming pro_id is unique for this table for now based on model definition.
        $query = RdbProjectWork::with(['project', 'researcher', 'position']);

        $items = $query->orderBy('pro_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectwork.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectWork::with(['project', 'researcher', 'position'])->findOrFail($id);
        return view('frontend.rdbprojectwork.show', compact('item'));
    }

    public function create()
    {
        $projects = RdbProject::all();
        $researchers = RdbResearcher::all();
        $positions = RdbProjectPosition::all();
        return view('frontend.rdbprojectwork.create', compact('projects', 'researchers', 'positions'));
    }

    public function store(Request $request)
    {
        // Note: If pro_id is not auto-increment and is a FK, we might need to handle it differently.
        // But assuming standard CRUD for now.
        $validated = $request->validate([
            'pro_id' => 'required|exists:rdb_project,pro_id', // Assuming pro_id comes from project selection if it's the PK
            'researcher_id' => 'nullable|exists:rdb_researcher,researcher_id',
            'ratio' => 'nullable|numeric',
            'position_id' => 'nullable|exists:rdb_project_position,position_id',
        ]);

        $validated['created_at'] = now();

        RdbProjectWork::create($validated);

        return redirect()->route('frontend.rdbprojectwork.index')->with('success', 'Project Work created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectWork::findOrFail($id);
        $projects = RdbProject::all();
        $researchers = RdbResearcher::all();
        $positions = RdbProjectPosition::all();
        return view('frontend.rdbprojectwork.edit', compact('item', 'projects', 'researchers', 'positions'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectWork::findOrFail($id);
        
        $validated = $request->validate([
            // pro_id usually shouldn't be changed if it's PK
            'researcher_id' => 'nullable|exists:rdb_researcher,researcher_id',
            'ratio' => 'nullable|numeric',
            'position_id' => 'nullable|exists:rdb_project_position,position_id',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprojectwork.index')->with('success', 'Project Work updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectWork::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprojectwork.index')->with('success', 'Project Work deleted successfully.');
    }
}
