<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbGroupproject;
use Illuminate\Http\Request;

class RdbgroupprojectController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbGroupproject::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pgroup_nameTH', 'like', "%{$search}%")
                  ->orWhere('pgroup_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('pgroup_id', 'desc')->paginate(10);
        
        return view('frontend.rdbgroupproject.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbGroupproject::findOrFail($id);
        return view('frontend.rdbgroupproject.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbgroupproject.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pgroup_nameTH' => 'required|string|max:255',
            'pgroup_nameEN' => 'nullable|string|max:255',
        ]);

        $validated['created_at'] = now();

        RdbGroupproject::create($validated);

        return redirect()->route('frontend.rdbgroupproject.index')->with('success', 'Project group created successfully.');
    }

    public function edit($id)
    {
        $item = RdbGroupproject::findOrFail($id);
        return view('frontend.rdbgroupproject.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbGroupproject::findOrFail($id);
        
        $validated = $request->validate([
            'pgroup_nameTH' => 'required|string|max:255',
            'pgroup_nameEN' => 'nullable|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbgroupproject.index')->with('success', 'Project group updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbGroupproject::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbgroupproject.index')->with('success', 'Project group deleted successfully.');
    }
}
