<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectBudget;
use App\Models\RdbProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbProjectBudgetRequest, UpdateRdbProjectBudgetRequest};

class RdbprojectbudgetController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbProjectBudget::with('project');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ckb_note', 'like', "%{$search}%");
        }

        $items = $query->orderBy('ckb_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectbudget.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbProjectBudget::with('project')->findOrFail($id);
        return view('frontend.rdbprojectbudget.show', compact('item'));
    }


}
