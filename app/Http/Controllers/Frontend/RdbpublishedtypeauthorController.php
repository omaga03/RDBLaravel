<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedTypeAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbPublishedTypeAuthorRequest, UpdateRdbPublishedTypeAuthorRequest};

class RdbpublishedtypeauthorController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbPublishedTypeAuthor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pubta_nameTH', 'like', "%{$search}%")
                  ->orWhere('pubta_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('pubta_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedtypeauthor.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbPublishedTypeAuthor::findOrFail($id);
        return view('frontend.rdbpublishedtypeauthor.show', compact('item'));
    }


}
