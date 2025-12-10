<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbResearcherStatus;
use Illuminate\Http\Request;

class RdbresearcherstatusController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbResearcherStatus::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('restatus_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('restatus_id', 'desc')->paginate(10);
        
        return view('frontend.rdbresearcherstatus.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbResearcherStatus::findOrFail($id);
        return view('frontend.rdbresearcherstatus.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbresearcherstatus.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'restatus_name' => 'required|string|max:255',
        ]);

        $validated['created_at'] = now();

        RdbResearcherStatus::create($validated);

        return redirect()->route('frontend.rdbresearcherstatus.index')->with('success', 'Researcher status created successfully.');
    }

    public function edit($id)
    {
        $item = RdbResearcherStatus::findOrFail($id);
        return view('frontend.rdbresearcherstatus.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbResearcherStatus::findOrFail($id);
        
        $validated = $request->validate([
            'restatus_name' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbresearcherstatus.index')->with('success', 'Researcher status updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbResearcherStatus::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbresearcherstatus.index')->with('success', 'Researcher status deleted successfully.');
    }
}
