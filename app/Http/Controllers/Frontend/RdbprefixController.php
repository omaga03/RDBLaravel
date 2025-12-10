<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPrefix;
use Illuminate\Http\Request;

class RdbprefixController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPrefix::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('prefix_nameTH', 'like', "%{$search}%")
                  ->orWhere('prefix_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('prefix_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprefix.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPrefix::findOrFail($id);
        return view('frontend.rdbprefix.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbprefix.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prefix_nameTH' => 'required|string|max:50',
            'prefix_nameEN' => 'nullable|string|max:50',
        ]);

        $validated['created_at'] = now();

        RdbPrefix::create($validated);

        return redirect()->route('frontend.rdbprefix.index')->with('success', 'Prefix created successfully.');
    }

    public function edit($id)
    {
        $item = RdbPrefix::findOrFail($id);
        return view('frontend.rdbprefix.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPrefix::findOrFail($id);
        
        $validated = $request->validate([
            'prefix_nameTH' => 'required|string|max:50',
            'prefix_nameEN' => 'nullable|string|max:50',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprefix.index')->with('success', 'Prefix updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbPrefix::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprefix.index')->with('success', 'Prefix deleted successfully.');
    }
}

