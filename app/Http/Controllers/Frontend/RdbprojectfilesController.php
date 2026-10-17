<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectFiles;
use App\Models\RdbProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RdbprojectfilesController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectFiles::with('project');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('rf_filesname', 'like', "%{$search}%")
                  ->orWhere('rf_note', 'like', "%{$search}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        
        return view('frontend.rdbprojectfiles.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectFiles::with('project')->findOrFail($id);
        return view('frontend.rdbprojectfiles.show', compact('item'));
    }


}
