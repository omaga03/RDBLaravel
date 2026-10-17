<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedWork;
use App\Models\RdbPublished;
use App\Models\RdbResearcher;
use App\Models\RdbPublishedTypeAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbPublishedWorkRequest, UpdateRdbPublishedWorkRequest};

class RdbpublishedworkController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbPublishedWork::with(['published', 'researcher', 'publishedTypeAuthor']);

        $items = $query->orderBy('published_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedwork.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbPublishedWork::with(['published', 'researcher', 'publishedTypeAuthor'])->findOrFail($id);
        return view('frontend.rdbpublishedwork.show', compact('item'));
    }


}
