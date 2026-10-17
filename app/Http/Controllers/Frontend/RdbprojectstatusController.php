<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbProjectStatusRequest, UpdateRdbProjectStatusRequest};

class RdbprojectstatusController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbProjectStatus::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ps_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('ps_rank')->paginate(10);
        
        return view('frontend.rdbprojectstatus.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbProjectStatus::findOrFail($id);
        return view('frontend.rdbprojectstatus.show', compact('item'));
    }


}
