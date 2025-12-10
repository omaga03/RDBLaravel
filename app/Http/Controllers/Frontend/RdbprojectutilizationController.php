<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilization;
use Illuminate\Http\Request;

class RdbprojectutilizationController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectUtilization::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('uti_nameTH', 'like', "%{$search}%");
        }

        $items = $query->orderBy('uti_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectutilization.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectUtilization::findOrFail($id);
        return view('frontend.rdbprojectutilization.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbprojectutilization.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'uti_nameTH' => 'required|string|max:255',
        ]);

        $validated['created_at'] = now();

        RdbProjectUtilization::create($validated);

        return redirect()->route('frontend.rdbprojectutilization.index')->with('success', 'Project utilization created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectUtilization::findOrFail($id);
        return view('frontend.rdbprojectutilization.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectUtilization::findOrFail($id);
        
        $validated = $request->validate([
            'uti_nameTH' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprojectutilization.index')->with('success', 'Project utilization updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectUtilization::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprojectutilization.index')->with('success', 'Project utilization deleted successfully.');
    }
}
