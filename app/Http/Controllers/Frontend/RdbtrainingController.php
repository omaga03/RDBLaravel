<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbTraining;
use Illuminate\Http\Request;

class RdbtrainingController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbTraining::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('tra_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('tra_id', 'desc')->paginate(10);
        
        return view('frontend.rdbtraining.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbTraining::findOrFail($id);
        return view('frontend.rdbtraining.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbtraining.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tra_name' => 'required|string|max:255',
            'tra_description' => 'nullable|string',
            'tra_property' => 'nullable|string',
            'tra_fee' => 'nullable|numeric',
            'tra_datetimestart' => 'nullable|date',
            'tra_datetimeend' => 'nullable|date',
            'tra_place' => 'nullable|string',
            'tra_applicant' => 'nullable|string',
            'tra_note' => 'nullable|string',
            'tra_url' => 'nullable|string',
        ]);

        $validated['created_at'] = now();

        RdbTraining::create($validated);

        return redirect()->route('frontend.rdbtraining.index')->with('success', 'Training created successfully.');
    }

    public function edit($id)
    {
        $item = RdbTraining::findOrFail($id);
        return view('frontend.rdbtraining.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbTraining::findOrFail($id);
        
        $validated = $request->validate([
            'tra_name' => 'required|string|max:255',
            'tra_description' => 'nullable|string',
            'tra_property' => 'nullable|string',
            'tra_fee' => 'nullable|numeric',
            'tra_datetimestart' => 'nullable|date',
            'tra_datetimeend' => 'nullable|date',
            'tra_place' => 'nullable|string',
            'tra_applicant' => 'nullable|string',
            'tra_note' => 'nullable|string',
            'tra_url' => 'nullable|string',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbtraining.index')->with('success', 'Training updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbTraining::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbtraining.index')->with('success', 'Training deleted successfully.');
    }
}
