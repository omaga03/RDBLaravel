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
        $query = RdbDateevent::with('eventType');

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


}
