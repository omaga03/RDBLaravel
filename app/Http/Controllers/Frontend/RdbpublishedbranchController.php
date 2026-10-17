<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedBranch;
use Illuminate\Http\Request;
use App\Http\Requests\Frontend\{StoreRdbPublishedBranchRequest, UpdateRdbPublishedBranchRequest};

class RdbpublishedbranchController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbPublishedBranch::class);
        $query = RdbPublishedBranch::with(['creator', 'updater']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('branch_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('branch_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedbranch.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPublishedBranch::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('frontend.rdbpublishedbranch.show', compact('item'));
    }


}