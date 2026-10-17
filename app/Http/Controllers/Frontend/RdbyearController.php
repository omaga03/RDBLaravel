<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbYear;
use App\Models\RdbProject;
use Illuminate\Http\Request;

class RdbyearController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbYear::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('year_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('year_name', 'desc')->paginate(10);
        
        return view('frontend.rdbyear.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbYear::findOrFail($id);
        return view('frontend.rdbyear.show', compact('item'));
    }


}

