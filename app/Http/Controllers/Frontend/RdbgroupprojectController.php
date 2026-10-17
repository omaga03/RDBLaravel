<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbGroupproject;
use Illuminate\Http\Request;

class RdbgroupprojectController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbGroupproject::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pgroup_nameTH', 'like', "%{$search}%")
                  ->orWhere('pgroup_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('pgroup_id', 'desc')->paginate(10);
        
        return view('frontend.rdbgroupproject.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbGroupproject::findOrFail($id);
        return view('frontend.rdbgroupproject.show', compact('item'));
    }


}
