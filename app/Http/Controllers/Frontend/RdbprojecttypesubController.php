<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectTypeSub;
use App\Models\RdbProjectType;
use Illuminate\Http\Request;

class RdbprojecttypesubController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectTypeSub::with('projectType');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('prot_sub_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('prot_sub_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojecttypesub.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectTypeSub::with('projectType')->findOrFail($id);
        return view('frontend.rdbprojecttypesub.show', compact('item'));
    }

    public function create()
    {
        $projectTypes = RdbProjectType::all();
        return view('frontend.rdbprojecttypesub.create', compact('projectTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prot_id' => 'nullable|exists:rdb_project_type,prot_id',
            'prot_sub_name' => 'required|string|max:255',
        ]);

        $validated['created_at'] = now();

        RdbProjectTypeSub::create($validated);

        return redirect()->route('frontend.rdbprojecttypesub.index')->with('success', 'Project Type Sub created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectTypeSub::findOrFail($id);
        $projectTypes = RdbProjectType::all();
        return view('frontend.rdbprojecttypesub.edit', compact('item', 'projectTypes'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectTypeSub::findOrFail($id);
        
        $validated = $request->validate([
            'prot_id' => 'nullable|exists:rdb_project_type,prot_id',
            'prot_sub_name' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprojecttypesub.index')->with('success', 'Project Type Sub updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectTypeSub::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprojecttypesub.index')->with('success', 'Project Type Sub deleted successfully.');
    }
}
