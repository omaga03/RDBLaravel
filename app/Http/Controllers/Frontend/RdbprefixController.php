<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPrefix;
use Illuminate\Http\Request;

class RdbprefixController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPrefix::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('prefix_nameTH', 'like', "%{$search}%")
                  ->orWhere('prefix_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('prefix_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprefix.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPrefix::findOrFail($id);
        return view('frontend.rdbprefix.show', compact('item'));
    }


}

