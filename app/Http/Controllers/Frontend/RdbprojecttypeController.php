<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectType;
use Illuminate\Http\Request;
use App\Http\Requests\Frontend\{StoreRdbProjectTypeRequest, UpdateRdbProjectTypeRequest};

class RdbprojecttypeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbProjectType::class);
        $query = RdbProjectType::with(['year', 'projectTypeGroup', 'creator', 'updater']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('pt_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('pt_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojecttype.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectType::with(['year', 'projectTypeGroup', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('frontend.rdbprojecttype.show', compact('item'));
    }


}