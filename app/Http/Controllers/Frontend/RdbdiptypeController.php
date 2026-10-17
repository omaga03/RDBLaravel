<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDipType;
use Illuminate\Http\Request;

class RdbdiptypeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDipType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('dipt_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('dipt_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdiptype.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDipType::findOrFail($id);
        return view('frontend.rdbdiptype.show', compact('item'));
    }


}
