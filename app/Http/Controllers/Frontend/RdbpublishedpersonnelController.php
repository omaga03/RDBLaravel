<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedPersonnel;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbPublishedPersonnelRequest, UpdateRdbPublishedPersonnelRequest};


class RdbpublishedpersonnelController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbPublishedPersonnel::with(['year', 'department', 'departmentCategory']);

        $items = $query->orderBy('personnel_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedpersonnel.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbPublishedPersonnel::with(['year', 'department', 'departmentCategory'])->findOrFail($id);
        return view('frontend.rdbpublishedpersonnel.show', compact('item'));
    }


}
