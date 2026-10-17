<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilizeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbProjectUtilizeTypeRequest, UpdateRdbProjectUtilizeTypeRequest};

class RdbprojectutilizetypeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbProjectUtilizeType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('utz_typr_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('utz_type_index')->paginate(10);
        
        return view('frontend.rdbprojectutilizetype.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbProjectUtilizeType::findOrFail($id);
        return view('frontend.rdbprojectutilizetype.show', compact('item'));
    }


}
