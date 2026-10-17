<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbDepartmentCourseRequest, UpdateRdbDepartmentCourseRequest};

class RdbdepartmentcourseController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbDepartmentCourse::with(['department', 'departmentCategory']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('cou_name', 'like', "%{$search}%")
                  ->orWhere('cou_name_sh', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('depcou_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdepartmentcourse.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbDepartmentCourse::with(['department', 'departmentCategory'])->findOrFail($id);
        return view('frontend.rdbdepartmentcourse.show', compact('item'));
    }


}
