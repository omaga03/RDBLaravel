<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedType;
use Illuminate\Http\Request;

class RdbpublishedtypeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublishedType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pubtype_group', 'like', "%{$search}%")
                  ->orWhere('pubtype_subgroup', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('pubtype_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedtype.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPublishedType::findOrFail($id);
        return view('frontend.rdbpublishedtype.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbpublishedtype.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pubtype_group' => 'nullable|string|max:255',
            'pubtype_grouptype' => 'nullable|string|max:100',
            'pubtype_subgroup' => 'nullable|string|max:255',
            'pubtype_score' => 'nullable|numeric',
        ]);

        $validated['created_at'] = now();

        RdbPublishedType::create($validated);

        return redirect()->route('frontend.rdbpublishedtype.index')->with('success', 'Published type created successfully.');
    }

    public function edit($id)
    {
        $item = RdbPublishedType::findOrFail($id);
        return view('frontend.rdbpublishedtype.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedType::findOrFail($id);
        
        $validated = $request->validate([
            'pubtype_group' => 'nullable|string|max:255',
            'pubtype_grouptype' => 'nullable|string|max:100',
            'pubtype_subgroup' => 'nullable|string|max:255',
            'pubtype_score' => 'nullable|numeric',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbpublishedtype.index')->with('success', 'Published type updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbPublishedType::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbpublishedtype.index')->with('success', 'Published type deleted successfully.');
    }
}
