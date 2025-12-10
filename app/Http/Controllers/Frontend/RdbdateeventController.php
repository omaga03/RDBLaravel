<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDateevent;
use App\Models\RdbDateeventType;
use Illuminate\Http\Request;

class RdbdateeventController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDateevent::with('event

Type');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ev_title', 'like', "%{$search}%");
        }

        $items = $query->orderBy('ev_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdateevent.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDateevent::with('eventType')->findOrFail($id);
        return view('frontend.rdbdateevent.show', compact('item'));
    }

    public function create()
    {
        $eventTypes = RdbDateeventType::all();
        return view('frontend.rdbdateevent.create', compact('eventTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evt_id' => 'nullable|exists:rdb_dateevent_type,evt_id',
            'ev_title' => 'required|string|max:255',
            'ev_detail' => 'nullable|string',
            'ev_datestart' => 'nullable|date',
            'ev_timestart' => 'nullable',
            'ev_dateend' => 'nullable|date',
            'ev_timeend' => 'nullable',
            'ev_url' => 'nullable|string',
            'ev_status' => 'nullable|integer',
        ]);

        $validated['created_at'] = now();

        RdbDateevent::create($validated);

        return redirect()->route('frontend.rdbdateevent.index')->with('success', 'Date event created successfully.');
    }

    public function edit($id)
    {
        $item = RdbDateevent::findOrFail($id);
        $eventTypes = RdbDateeventType::all();
        return view('frontend.rdbdateevent.edit', compact('item', 'eventTypes'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDateevent::findOrFail($id);
        
        $validated = $request->validate([
            'evt_id' => 'nullable|exists:rdb_dateevent_type,evt_id',
            'ev_title' => 'required|string|max:255',
            'ev_detail' => 'nullable|string',
            'ev_datestart' => 'nullable|date',
            'ev_timestart' => 'nullable',
            'ev_dateend' => 'nullable|date',
            'ev_timeend' => 'nullable',
            'ev_url' => 'nullable|string',
            'ev_status' => 'nullable|integer',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbdateevent.index')->with('success', 'Date event updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDateevent::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbdateevent.index')->with('success', 'Date event deleted successfully.');
    }
}
