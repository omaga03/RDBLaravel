<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectWork;
use App\Models\RdbProject;
use App\Models\RdbResearcher;
use App\Models\RdbProjectPosition;
use Illuminate\Http\Request;

class RdbprojectworkController extends Controller
{
    public function index(Request $request)
    {
        // Note: pro_id is PK, but it might be a composite key or foreign key in reality.
        // Assuming pro_id is unique for this table for now based on model definition.
        $query = RdbProjectWork::with(['project', 'researcher', 'position']);

        $items = $query->orderBy('pro_id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectwork.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectWork::with(['project', 'researcher', 'position'])->findOrFail($id);
        return view('frontend.rdbprojectwork.show', compact('item'));
    }


}
