<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbChangwat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbChangwatRequest, UpdateRdbChangwatRequest};

class RdbchangwatController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
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
        Gate::authorize('Admin');
        $item = RdbChangwat::findOrFail($id);
        return view('frontend.rdbchangwat.show', compact('item'));
    }


}
