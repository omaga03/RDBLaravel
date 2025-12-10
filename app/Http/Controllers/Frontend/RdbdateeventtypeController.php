<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDateeventType;
use Illuminate\Http\Request;

class RdbdateeventtypeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDateeventType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('evt_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('evt_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdateeventtype.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDateeventType::findOrFail($id);
        return view('frontend.rdbdateeventtype.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbdateeventtype.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evt_name' => 'required|string|max:255',
            'evt_color' => 'nullable|string|max:20',
        ]);

        $validated['created_at'] = now();

        RdbDateeventType::create($validated);

        return redirect()->route('frontend.rdbdateeventtype.index')->with('success', 'Event type created successfully.');
    }

    public function edit($id)
    {
        $item = RdbDateeventType::findOrFail($id);
        return view('frontend.rdbdateeventtype.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDateeventType::findOrFail($id);
        
        $validated = $request->validate([
            'evt_name' => 'required|string|max:255',
            'evt_color' => 'nullable|string|max:20',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbdateeventtype.index')->with('success', 'Event type updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDateeventType::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbdateeventtype.index')->with('success', 'Event type deleted successfully.');
    }
}
