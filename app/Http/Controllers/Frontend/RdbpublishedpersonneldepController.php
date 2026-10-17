<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedPersonnelDep;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbDepMajor;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbPublishedPersonnelDepRequest, UpdateRdbPublishedPersonnelDepRequest};

class RdbpublishedpersonneldepController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbPublishedPersonnelDep::with(['year', 'department', 'departmentCourse', 'depMajor', 'departmentCategory']);

        $items = $query->orderBy('perpd_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedpersonneldep.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbPublishedPersonnelDep::findOrFail($id);
        return view('frontend.rdbpublishedpersonneldep.show', compact('item'));
    }


}
