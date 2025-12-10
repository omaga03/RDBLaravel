<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectStatus;
use Illuminate\Http\Request;

class RdbprojectstatusController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectStatus::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ps_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('ps_rank')->paginate(10);
        
        return view('frontend.rdbprojectstatus.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectStatus::findOrFail($id);
        return view('frontend.rdbprojectstatus.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbprojectstatus.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ps_name' => 'required|string|max:100',
            'ps_color' => 'nullable|string|max:20',
            'ps_icon' => 'nullable|string|max:50',
            'ps_rank' => 'nullable|integer',
        ]);

        $validated['created_at'] = now();

        RdbProjectStatus::create($validated);

        return redirect()->route('frontend.rdbprojectstatus.index')->with('success', 'Project status created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectStatus::findOrFail($id);
        return view('frontend.rdbprojectstatus.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectStatus::findOrFail($id);
        
        $validated = $request->validate([
            'ps_name' => 'required|string|max:100',
            'ps_color' => 'nullable|string|max:20',
            'ps_icon' => 'nullable|string|max:50',
            'ps_rank' => 'nullable|integer',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprojectstatus.index')->with('success', 'Project status updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectStatus::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprojectstatus.index')->with('success', 'Project status deleted successfully.');
    }
}
