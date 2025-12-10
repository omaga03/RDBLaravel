<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedBranchper;
use App\Models\RdbYear;
use App\Models\RdbPublishedBranch;
use Illuminate\Http\Request;

class RdbpublishedbranchperController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublishedBranchper::with(['year', 'branch']);

        $items = $query->orderBy('branchper_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedbranchper.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPublishedBranchper::with(['year', 'branch'])->findOrFail($id);
        return view('frontend.rdbpublishedbranchper.show', compact('item'));
    }

    public function create()
    {
        $years = RdbYear::all();
        $branches = RdbPublishedBranch::all();
        return view('frontend.rdbpublishedbranchper.create', compact('years', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'branch_id' => 'nullable|exists:rdb_published_branch,branch_id',
            'branchper_percent' => 'nullable|numeric',
        ]);

        $validated['created_at'] = now();

        RdbPublishedBranchper::create($validated);

        return redirect()->route('frontend.rdbpublishedbranchper.index')->with('success', 'Branch percentage created successfully.');
    }

    public function edit($id)
    {
        $item = RdbPublishedBranchper::findOrFail($id);
        $years = RdbYear::all();
        $branches = RdbPublishedBranch::all();
        return view('frontend.rdbpublishedbranchper.edit', compact('item', 'years', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedBranchper::findOrFail($id);
        
        $validated = $request->validate([
            'year_id' => 'nullable|exists:rdb_year,year_id',
            'branch_id' => 'nullable|exists:rdb_published_branch,branch_id',
            'branchper_percent' => 'nullable|numeric',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbpublishedbranchper.index')->with('success', 'Branch percentage updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbPublishedBranchper::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbpublishedbranchper.index')->with('success', 'Branch percentage deleted successfully.');
    }
}
