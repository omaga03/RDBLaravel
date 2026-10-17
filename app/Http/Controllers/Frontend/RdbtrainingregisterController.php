<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbTrainingRegister;
use App\Models\RdbTraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Frontend\{StoreRdbTrainingRegisterRequest, UpdateRdbTrainingRegisterRequest};

class RdbtrainingregisterController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = RdbTrainingRegister::with('training');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('treg_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('treg_id', 'desc')->paginate(10);
        
        return view('frontend.rdbtrainingregister.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = RdbTrainingRegister::with('training')->findOrFail($id);
        return view('frontend.rdbtrainingregister.show', compact('item'));
    }


}
