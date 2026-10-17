<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectPosition;
use Illuminate\Http\Request;

class RdbprojectpositionController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectPosition::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('position_nameTH', 'like', "%{$search}%");
        }

        $items = $query->orderBy('position_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectposition.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectPosition::findOrFail($id);
        return view('frontend.rdbprojectposition.show', compact('item'));
    }


}
