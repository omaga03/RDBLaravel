<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbStrategic;
use Illuminate\Http\Request;

class RdbstrategicController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbStrategic::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('strategic_nameTH', 'like', "%{$search}%")
                  ->orWhere('strategic_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('strategic_id', 'desc')->paginate(10);
        
        return view('frontend.rdbstrategic.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbStrategic::findOrFail($id);
        return view('frontend.rdbstrategic.show', compact('item'));
    }


}

