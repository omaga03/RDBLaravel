<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedCheckyear;
use App\Models\RdbYear;
use Illuminate\Http\Request;

class RdbpublishedcheckyearController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublishedCheckyear::with('year');

        $items = $query->orderBy('id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedcheckyear.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPublishedCheckyear::with('year')->findOrFail($id);
        return view('frontend.rdbpublishedcheckyear.show', compact('item'));
    }

    public function create()
    {
        $years = RdbYear::all();
        return view('frontend.rdbpublishedcheckyear.create', compact('years'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'rdbyearedu_start' => 'nullable|integer',
            'rdbyearedu_end' => 'nullable|integer',
            'rdbyearbud_start' => 'nullable|integer',
            'rdbyearbud_end' => 'nullable|integer',
        ]);

        $validated['created_at'] = now();

        RdbPublishedCheckyear::create($validated);

        return redirect()->route('frontend.rdbpublishedcheckyear.index')->with('success', 'Check year created successfully.');
    }

    public function edit($id)
    {
        $item = RdbPublishedCheckyear::findOrFail($id);
        $years = RdbYear::all();
        return view('frontend.rdbpublishedcheckyear.edit', compact('item', 'years'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedCheckyear::findOrFail($id);
        
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'rdbyearedu_start' => 'nullable|integer',
            'rdbyearedu_end' => 'nullable|integer',
            'rdbyearbud_start' => 'nullable|integer',
            'rdbyearbud_end' => 'nullable|integer',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbpublishedcheckyear.index')->with('success', 'Check year updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbPublishedCheckyear::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbpublishedcheckyear.index')->with('success', 'Check year deleted successfully.');
    }
}
