<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartmentType;
use Illuminate\Http\Request;
use App\Http\Requests\Frontend\{StoreRdbDepartmentTypeRequest, UpdateRdbDepartmentTypeRequest};

class RdbdepartmenttypeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbDepartmentType::class);
        $query = RdbDepartmentType::with(['creator', 'updater']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tdepartment_nameTH', 'like', "%{$search}%")
                  ->orWhere('tdepartment_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('tdepartment_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdepartmenttype.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDepartmentType::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('frontend.rdbdepartmenttype.show', compact('item'));
    }


}