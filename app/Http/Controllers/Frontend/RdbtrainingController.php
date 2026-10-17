<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbTraining;
use Illuminate\Http\Request;
use App\Http\Requests\Frontend\{StoreRdbTrainingRequest, UpdateRdbTrainingRequest};

class RdbtrainingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbTraining::class);
        $query = RdbTraining::with(['creator', 'updater']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('tra_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('tra_id', 'desc')->paginate(10);
        
        return view('frontend.rdbtraining.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbTraining::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('frontend.rdbtraining.show', compact('item'));
    }


}