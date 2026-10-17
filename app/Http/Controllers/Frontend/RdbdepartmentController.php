<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentType;
use Illuminate\Http\Request;
use App\Http\Requests\Frontend\{StoreRdbDepartmentRequest, UpdateRdbDepartmentRequest};

class RdbdepartmentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbDepartment::class);
        $query = RdbDepartment::with(['departmentType', 'creator', 'updater']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('department_nameTH', 'like', "%{$search}%")
                  ->orWhere('department_nameEN', 'like', "%{$search}%")
                  ->orWhere('department_code', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('department_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdepartment.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDepartment::with(['departmentType', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('frontend.rdbdepartment.show', compact('item'));
    }


}