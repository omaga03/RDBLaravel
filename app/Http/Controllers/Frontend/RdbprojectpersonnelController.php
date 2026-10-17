<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectPersonnel;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbProjectPersonnelRequest, UpdateRdbProjectPersonnelRequest};

class RdbprojectpersonnelController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbProjectPersonnel::with(['year', 'department', 'departmentCategory']);

        $items = $query->orderBy('pp_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectpersonnel.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbProjectPersonnel::with(['year', 'department', 'departmentCategory'])->findOrFail($id);
        return view('frontend.rdbprojectpersonnel.show', compact('item'));
    }


}
