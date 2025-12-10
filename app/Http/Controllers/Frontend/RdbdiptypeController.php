<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDipType;
use Illuminate\Http\Request;

class RdbdiptypeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDipType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('dipt_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('dipt_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdiptype.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDipType::findOrFail($id);
        return view('frontend.rdbdiptype.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbdiptype.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dipt_name' => 'required|string|max:255',
        ]);

        $validated['created_at'] = now();

        RdbDipType::create($validated);

        return redirect()->route('frontend.rdbdiptype.index')->with('success', 'DIP type created successfully.');
    }

    public function edit($id)
    {
        $item = RdbDipType::findOrFail($id);
        return view('frontend.rdbdiptype.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDipType::findOrFail($id);
        
        $validated = $request->validate([
            'dipt_name' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbdiptype.index')->with('success', 'DIP type updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDipType::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbdiptype.index')->with('success', 'DIP type deleted successfully.');
    }
}
