<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectType;
use Illuminate\Http\Request;

class RdbprojecttypeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('pt_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('pt_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojecttype.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectType::findOrFail($id);
        return view('frontend.rdbprojecttype.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbprojecttype.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pt_name' => 'required|string|max:255',
            'pt_for' => 'nullable|string|max:50',
        ]);

        $validated['created_at'] = now();

        RdbProjectType::create($validated);

        return redirect()->route('frontend.rdbprojecttype.index')->with('success', 'Project type created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectType::findOrFail($id);
        return view('frontend.rdbprojecttype.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectType::findOrFail($id);
        
        $validated = $request->validate([
            'pt_name' => 'required|string|max:255',
            'pt_for' => 'nullable|string|max:50',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprojecttype.index')->with('success', 'Project type updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectType::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprojecttype.index')->with('success', 'Project type deleted successfully.');
    }
}
