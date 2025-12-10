<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectBudget;
use App\Models\RdbProject;
use Illuminate\Http\Request;

class RdbprojectbudgetController extends Controller
{
    public function index(Request $request)
    {
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
        $item = RdbProjectBudget::with('project')->findOrFail($id);
        return view('frontend.rdbprojectbudget.show', compact('item'));
    }

    public function create()
    {
        $projects = RdbProject::all();
        return view('frontend.rdbprojectbudget.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pro_id' => 'nullable|exists:rdb_project,pro_id',
            'ckb_annuity' => 'nullable|numeric',
            'ckb_note' => 'nullable|string',
            'ckb_status' => 'nullable|integer',
        ]);

        $validated['created_at'] = now();

        RdbProjectBudget::create($validated);

        return redirect()->route('frontend.rdbprojectbudget.index')->with('success', 'Budget created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectBudget::findOrFail($id);
        $projects = RdbProject::all();
        return view('frontend.rdbprojectbudget.edit', compact('item', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectBudget::findOrFail($id);
        
        $validated = $request->validate([
            'pro_id' => 'nullable|exists:rdb_project,pro_id',
            'ckb_annuity' => 'nullable|numeric',
            'ckb_note' => 'nullable|string',
            'ckb_status' => 'nullable|integer',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprojectbudget.index')->with('success', 'Budget updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectBudget::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprojectbudget.index')->with('success', 'Budget deleted successfully.');
    }
}
