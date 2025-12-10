<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbNacc;
use App\Models\RdbProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RdbnaccController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbNacc::with('project');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nacc_note', 'like', "%{$search}%");
        }

        $items = $query->orderBy('nacc_id', 'desc')->paginate(10);
        
        return view('frontend.rdbnacc.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbNacc::with('project')->findOrFail($id);
        return view('frontend.rdbnacc.show', compact('item'));
    }

    public function create()
    {
        $projects = RdbProject::all();
        return view('frontend.rdbnacc.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pro_id' => 'required|exists:rdb_project,pro_id',
            'nacc_files' => 'nullable|file|max:10240', // Max 10MB
            'nacc_note' => 'nullable|string',
            'nacc_download' => 'nullable|integer',
            'data_show' => 'nullable|integer',
        ]);

        if ($request->hasFile('nacc_files')) {
            $path = $request->file('nacc_files')->store('nacc_files', 'public');
            $validated['nacc_files'] = $path;
        }

        $validated['created_at'] = now();

        RdbNacc::create($validated);

        return redirect()->route('frontend.rdbnacc.index')->with('success', 'NACC record created successfully.');
    }

    public function edit($id)
    {
        $item = RdbNacc::findOrFail($id);
        $projects = RdbProject::all();
        return view('frontend.rdbnacc.edit', compact('item', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbNacc::findOrFail($id);
        
        $validated = $request->validate([
            'pro_id' => 'required|exists:rdb_project,pro_id',
            'nacc_files' => 'nullable|file|max:10240',
            'nacc_note' => 'nullable|string',
            'nacc_download' => 'nullable|integer',
            'data_show' => 'nullable|integer',
        ]);

        if ($request->hasFile('nacc_files')) {
            // Delete old file if exists
            if ($item->nacc_files) {
                Storage::disk('public')->delete($item->nacc_files);
            }
            $path = $request->file('nacc_files')->store('nacc_files', 'public');
            $validated['nacc_files'] = $path;
        }

        $item->update($validated);

        return redirect()->route('frontend.rdbnacc.index')->with('success', 'NACC record updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbNacc::findOrFail($id);
        if ($item->nacc_files) {
            Storage::disk('public')->delete($item->nacc_files);
        }
        $item->delete();

        return redirect()->route('frontend.rdbnacc.index')->with('success', 'NACC record deleted successfully.');
    }
}
