<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepMajor;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbDepartment;
use Illuminate\Http\Request;
use App\Http\Requests\Frontend\{StoreRdbDepMajorRequest, UpdateRdbDepMajorRequest};

class RdbdepmajorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbDepMajor::class);
        $query = RdbDepMajor::with(['departmentCourse', 'department', 'creator', 'updater']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('maj_nameTH', 'like', "%{$search}%")
                  ->orWhere('maj_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('maj_code', 'desc')->paginate(10);
        
        return view('frontend.rdbdepmajor.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDepMajor::with(['departmentCourse', 'department', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('frontend.rdbdepmajor.show', compact('item'));
    }


}