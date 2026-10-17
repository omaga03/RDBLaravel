<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectGroup;
use App\Models\RdbProject;
use App\Models\RdbGroupProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbProjectGroupRequest, UpdateRdbProjectGroupRequest};

class RdbprojectgroupController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbProjectGroup::with(['project', 'groupProject']);

        if ($request->filled('search')) {
            $search = $request->search;
            // Search by project name or group name
            $query->whereHas('project', function($q) use ($search) {
                $q->where('pro_nameTH', 'like', "%{$search}%");
            })->orWhereHas('groupProject', function($q) use ($search) {
                $q->where('pgroup_nameTH', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('group_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectgroup.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbProjectGroup::with(['project', 'groupProject'])->findOrFail($id);
        return view('frontend.rdbprojectgroup.show', compact('item'));
    }


}
