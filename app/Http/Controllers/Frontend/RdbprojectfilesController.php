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

    public function create()
    {
        $projects = RdbProject::all();
        return view('frontend.rdbprojectfiles.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pro_id' => 'required|exists:rdb_project,pro_id',
            'rf_files' => 'nullable|file|max:10240', // Max 10MB
            'rf_note' => 'nullable|string',
            'rf_download' => 'nullable|integer',
            'rf_files_show' => 'nullable|integer',
        ]);

        if ($request->hasFile('rf_files')) {
            $file = $request->file('rf_files');
            $path = $file->store('project_files', 'public');
            $validated['rf_files'] = $path;
            $validated['rf_filesname'] = $file->getClientOriginalName();
        }

        $validated['created_at'] = now();

        RdbProjectFiles::create($validated);

        return redirect()->route('frontend.rdbprojectfiles.index')->with('success', 'Project File created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectFiles::findOrFail($id);
        $projects = RdbProject::all();
        return view('frontend.rdbprojectfiles.edit', compact('item', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectFiles::findOrFail($id);
        
        $validated = $request->validate([
            'pro_id' => 'required|exists:rdb_project,pro_id',
            'rf_files' => 'nullable|file|max:10240',
            'rf_note' => 'nullable|string',
            'rf_download' => 'nullable|integer',
            'rf_files_show' => 'nullable|integer',
        ]);

        if ($request->hasFile('rf_files')) {
            if ($item->rf_files) {
                Storage::disk('public')->delete($item->rf_files);
            }
            $file = $request->file('rf_files');
            $path = $file->store('project_files', 'public');
            $validated['rf_files'] = $path;
            $validated['rf_filesname'] = $file->getClientOriginalName();
        }

        $item->update($validated);

        return redirect()->route('frontend.rdbprojectfiles.index')->with('success', 'Project File updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectFiles::findOrFail($id);
        if ($item->rf_files) {
            Storage::disk('public')->delete($item->rf_files);
        }
        $item->delete();

        return redirect()->route('frontend.rdbprojectfiles.index')->with('success', 'Project File deleted successfully.');
    }
}
