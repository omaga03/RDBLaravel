<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedType;
use Illuminate\Http\Request;

class RdbpublishedtypeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublishedType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pubtype_group', 'like', "%{$search}%")
                  ->orWhere('pubtype_subgroup', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('pubtype_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedtype.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPublishedType::findOrFail($id);
        return view('frontend.rdbpublishedtype.show', compact('item'));
    }


}
