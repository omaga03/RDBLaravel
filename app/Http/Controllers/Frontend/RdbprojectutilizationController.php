<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RdbprojectutilizationController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbProjectUtilization::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('uti_nameTH', 'like', "%{$search}%");
        }

        $items = $query->orderBy('uti_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectutilization.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbProjectUtilization::findOrFail($id);
        return view('frontend.rdbprojectutilization.show', compact('item'));
    }


}
