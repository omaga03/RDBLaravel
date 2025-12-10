<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbStrategic;
use Illuminate\Http\Request;

class RdbstrategicController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbStrategic::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('strategic_nameTH', 'like', "%{$search}%")
                  ->orWhere('strategic_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('strategic_id', 'desc')->paginate(10);
        
        return view('frontend.rdbstrategic.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbStrategic::findOrFail($id);
        return view('frontend.rdbstrategic.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbstrategic.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'strategic_nameTH' => 'required|string|max:255',
            'strategic_nameEN' => 'nullable|string|max:255',
        ]);

        RdbStrategic::create($validated);

        return redirect()->route('frontend.rdbstrategic.index')->with('success', 'Strategic plan created successfully.');
    }

    public function edit($id)
    {
        $item = RdbStrategic::findOrFail($id);
        return view('frontend.rdbstrategic.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbStrategic::findOrFail($id);
        
        $validated = $request->validate([
            'strategic_nameTH' => 'required|string|max:255',
            'strategic_nameEN' => 'nullable|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbstrategic.index')->with('success', 'Strategic plan updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbStrategic::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbstrategic.index')->with('success', 'Strategic plan deleted successfully.');
    }
}

