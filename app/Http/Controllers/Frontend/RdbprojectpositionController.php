<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectPosition;
use Illuminate\Http\Request;

class RdbprojectpositionController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectPosition::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('position_nameTH', 'like', "%{$search}%");
        }

        $items = $query->orderBy('position_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectposition.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectPosition::findOrFail($id);
        return view('frontend.rdbprojectposition.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbprojectposition.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'position_nameTH' => 'required|string|max:255',
            'position_desc' => 'nullable|string',
        ]);

        $validated['created_at'] = now();

        RdbProjectPosition::create($validated);

        return redirect()->route('frontend.rdbprojectposition.index')->with('success', 'Project position created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectPosition::findOrFail($id);
        return view('frontend.rdbprojectposition.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectPosition::findOrFail($id);
        
        $validated = $request->validate([
            'position_nameTH' => 'required|string|max:255',
            'position_desc' => 'nullable|string',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprojectposition.index')->with('success', 'Project position updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectPosition::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprojectposition.index')->with('success', 'Project position deleted successfully.');
    }
}
