<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedCheckyear;
use App\Models\RdbYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbPublishedCheckyearRequest, UpdateRdbPublishedCheckyearRequest};

class RdbpublishedcheckyearController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbPublishedCheckyear::with('year');

        $items = $query->orderBy('id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedcheckyear.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbPublishedCheckyear::with('year')->findOrFail($id);
        return view('frontend.rdbpublishedcheckyear.show', compact('item'));
    }


}
