<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbResearcherStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbResearcherStatusRequest, UpdateRdbResearcherStatusRequest};

class RdbresearcherstatusController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbResearcherStatus::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('restatus_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('restatus_id', 'desc')->paginate(10);
        
        return view('frontend.rdbresearcherstatus.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbResearcherStatus::findOrFail($id);
        return view('frontend.rdbresearcherstatus.show', compact('item'));
    }


}
