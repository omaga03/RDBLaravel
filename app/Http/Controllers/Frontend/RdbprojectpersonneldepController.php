<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectPersonnelDep;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbDepMajor;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbProjectPersonnelDepRequest, UpdateRdbProjectPersonnelDepRequest};

class RdbprojectpersonneldepController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbProjectPersonnelDep::with(['year', 'department', 'departmentCourse', 'depMajor', 'departmentCategory']);

        $items = $query->orderBy('ppd_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectpersonneldep.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbProjectPersonnelDep::with(['year', 'department', 'departmentCourse', 'depMajor', 'departmentCategory'])->findOrFail($id);
        return view('frontend.rdbprojectpersonneldep.show', compact('item'));
    }


}
