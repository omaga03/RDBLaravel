<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectTypeSub;
use App\Models\RdbProjectType;
use Illuminate\Http\Request;
use App\Http\Requests\Frontend\{StoreRdbProjectTypeSubRequest, UpdateRdbProjectTypeSubRequest};

class RdbprojecttypesubController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbProjectTypeSub::class);
        $query = RdbProjectTypeSub::with(['projectType', 'createdBy', 'updatedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('pts_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('pts_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojecttypesub.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectTypeSub::with(['projectType', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('frontend.rdbprojecttypesub.show', compact('item'));
    }


}