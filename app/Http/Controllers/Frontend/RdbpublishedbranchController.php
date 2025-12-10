<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedBranch;
use Illuminate\Http\Request;

class RdbpublishedbranchController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublishedBranch::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('branch_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('branch_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedbranch.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        return view('frontend.rdbpublishedbranch.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbpublishedbranch.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
        ]);

        $validated['created_at'] = now();

        RdbPublishedBranch::create($validated);

        return redirect()->route('frontend.rdbpublishedbranch.index')->with('success', 'Published branch created successfully.');
    }

    public function edit($id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        return view('frontend.rdbpublishedbranch.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbpublishedbranch.index')->with('success', 'Published branch updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbpublishedbranch.index')->with('success', 'Published branch deleted successfully.');
    }
}
