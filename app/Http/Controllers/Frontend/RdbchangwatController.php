<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbChangwat;
use Illuminate\Http\Request;

class RdbchangwatController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbChangwat::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('changwat_t', 'like', "%{$search}%")
                  ->orWhere('changwat_e', 'like', "%{$search}%")
                  ->orWhere('amphoe_t', 'like', "%{$search}%")
                  ->orWhere('tambon_t', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('id')->paginate(10);
        
        return view('frontend.rdbchangwat.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbChangwat::findOrFail($id);
        return view('frontend.rdbchangwat.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbchangwat.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ta_id' => 'nullable|string|max:10',
            'tambon_t' => 'nullable|string|max:255',
            'tambon_e' => 'nullable|string|max:255',
            'am_id' => 'nullable|string|max:10',
            'amphoe_t' => 'nullable|string|max:255',
            'amphoe_e' => 'nullable|string|max:255',
            'ch_id' => 'nullable|string|max:10',
            'changwat_t' => 'required|string|max:255',
            'changwat_e' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
        ]);

        $validated['created_at'] = now();

        RdbChangwat::create($validated);

        return redirect()->route('frontend.rdbchangwat.index')->with('success', 'Location created successfully.');
    }

    public function edit($id)
    {
        $item = RdbChangwat::findOrFail($id);
        return view('frontend.rdbchangwat.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbChangwat::findOrFail($id);
        
        $validated = $request->validate([
            'ta_id' => 'nullable|string|max:10',
            'tambon_t' => 'nullable|string|max:255',
            'tambon_e' => 'nullable|string|max:255',
            'am_id' => 'nullable|string|max:10',
            'amphoe_t' => 'nullable|string|max:255',
            'amphoe_e' => 'nullable|string|max:255',
            'ch_id' => 'nullable|string|max:10',
            'changwat_t' => 'required|string|max:255',
            'changwat_e' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbchangwat.index')->with('success', 'Location updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbChangwat::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbchangwat.index')->with('success', 'Location deleted successfully.');
    }
}
