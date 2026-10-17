<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDateeventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbDateeventTypeRequest, UpdateRdbDateeventTypeRequest};

class RdbdateeventtypeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
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
        Gate::authorize('Admin');
        $item = RdbDateeventType::findOrFail($id);
        return view('frontend.rdbdateeventtype.show', compact('item'));
    }


}
