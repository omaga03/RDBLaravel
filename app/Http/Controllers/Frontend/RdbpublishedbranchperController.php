<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedBranchper;
use App\Models\RdbYear;
use App\Models\RdbPublishedBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbPublishedBranchperRequest, UpdateRdbPublishedBranchperRequest};

class RdbpublishedbranchperController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbPublishedBranchper::with(['year', 'branch']);

        $items = $query->orderBy('branchper_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedbranchper.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbPublishedBranchper::with(['year', 'branch'])->findOrFail($id);
        return view('frontend.rdbpublishedbranchper.show', compact('item'));
    }


}
