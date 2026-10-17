<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbDepartmentCategoryRequest, UpdateRdbDepartmentCategoryRequest};

class RdbdepartmentcategoryController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbDepartmentCategory::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('depcat_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('depcat_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdepartmentcategory.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbDepartmentCategory::findOrFail($id);
        return view('frontend.rdbdepartmentcategory.show', compact('item'));
    }


}
