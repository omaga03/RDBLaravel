<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedTypeAuthor;
use Illuminate\Http\Request;

class RdbpublishedtypeauthorController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublishedTypeAuthor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pubta_nameTH', 'like', "%{$search}%")
                  ->orWhere('pubta_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('pubta_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedtypeauthor.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPublishedTypeAuthor::findOrFail($id);
        return view('frontend.rdbpublishedtypeauthor.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbpublishedtypeauthor.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pubta_nameTH' => 'required|string|max:255',
            'pubta_nameEN' => 'nullable|string|max:255',
            'pubta_score' => 'nullable|numeric',
        ]);

        $validated['created_at'] = now();

        RdbPublishedTypeAuthor::create($validated);

        return redirect()->route('frontend.rdbpublishedtypeauthor.index')->with('success', 'Author type created successfully.');
    }

    public function edit($id)
    {
        $item = RdbPublishedTypeAuthor::findOrFail($id);
        return view('frontend.rdbpublishedtypeauthor.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedTypeAuthor::findOrFail($id);
        
        $validated = $request->validate([
            'pubta_nameTH' => 'required|string|max:255',
            'pubta_nameEN' => 'nullable|string|max:255',
            'pubta_score' => 'nullable|numeric',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbpublishedtypeauthor.index')->with('success', 'Author type updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbPublishedTypeAuthor::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbpublishedtypeauthor.index')->with('success', 'Author type deleted successfully.');
    }
}
