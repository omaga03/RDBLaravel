<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbResearcherEducation;
use App\Models\RdbResearcher;
use Illuminate\Http\Request;

class RdbresearchereducationController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbResearcherEducation::with('researcher');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('reedu_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('reedu_id', 'desc')->paginate(10);
        
        return view('frontend.rdbresearchereducation.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbResearcherEducation::with('researcher')->findOrFail($id);
        return view('frontend.rdbresearchereducation.show', compact('item'));
    }

    public function create()
    {
        $researchers = RdbResearcher::all();
        return view('frontend.rdbresearchereducation.create', compact('researchers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'researcher_id' => 'nullable|exists:rdb_researcher,researcher_id',
            'reedu_status' => 'nullable|string|max:50',
            'reedu_year' => 'nullable|integer',
            'reedu_type' => 'nullable|string|max:100',
            'reedu_name' => 'required|string|max:255',
            'reedu_department' => 'nullable|string|max:255',
            'reedu_major' => 'nullable|string|max:255',
            'reedu_cational' => 'nullable|string|max:255',
        ]);

        $validated['created_at'] = now();

        RdbResearcherEducation::create($validated);

        return redirect()->route('frontend.rdbresearchereducation.index')->with('success', 'Education created successfully.');
    }

    public function edit($id)
    {
        $item = RdbResearcherEducation::findOrFail($id);
        $researchers = RdbResearcher::all();
        return view('frontend.rdbresearchereducation.edit', compact('item', 'researchers'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbResearcherEducation::findOrFail($id);
        
        $validated = $request->validate([
            'researcher_id' => 'nullable|exists:rdb_researcher,researcher_id',
            'reedu_status' => 'nullable|string|max:50',
            'reedu_year' => 'nullable|integer',
            'reedu_type' => 'nullable|string|max:100',
            'reedu_name' => 'required|string|max:255',
            'reedu_department' => 'nullable|string|max:255',
            'reedu_major' => 'nullable|string|max:255',
            'reedu_cational' => 'nullable|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbresearchereducation.index')->with('success', 'Education updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbResearcherEducation::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbresearchereducation.index')->with('success', 'Education deleted successfully.');
    }
}
