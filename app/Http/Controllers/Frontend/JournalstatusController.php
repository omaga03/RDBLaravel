<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\JournalStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JournalstatusController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalStatus::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('jou_name', 'like', "%{$search}%")
                  ->orWhere('jou_respon', 'like', "%{$search}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        
        return view('frontend.journalstatus.index', compact('items'));
    }

    public function show($id)
    {
        $item = JournalStatus::findOrFail($id);
        return view('frontend.journalstatus.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.journalstatus.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jou_name' => 'required|string|max:255',
            'jou_respon' => 'nullable|string|max:255',
            'jou_email' => 'nullable|email|max:255',
            'jou_files' => 'nullable|file|max:10240',
            'jou_status' => 'nullable|integer',
            'jou_note' => 'nullable|string',
            'data_show' => 'nullable|integer',
        ]);

        if ($request->hasFile('jou_files')) {
            $file = $request->file('jou_files');
            $path = $file->store('journal_files', 'public');
            $validated['jou_files'] = $path;
            $validated['jou_filesname'] = $file->getClientOriginalName();
        }

        $validated['created_at'] = now();

        JournalStatus::create($validated);

        return redirect()->route('frontend.journalstatus.index')->with('success', 'Journal Status created successfully.');
    }

    public function edit($id)
    {
        $item = JournalStatus::findOrFail($id);
        return view('frontend.journalstatus.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = JournalStatus::findOrFail($id);
        
        $validated = $request->validate([
            'jou_name' => 'required|string|max:255',
            'jou_respon' => 'nullable|string|max:255',
            'jou_email' => 'nullable|email|max:255',
            'jou_files' => 'nullable|file|max:10240',
            'jou_status' => 'nullable|integer',
            'jou_note' => 'nullable|string',
            'data_show' => 'nullable|integer',
        ]);

        if ($request->hasFile('jou_files')) {
            if ($item->jou_files) {
                Storage::disk('public')->delete($item->jou_files);
            }
            $file = $request->file('jou_files');
            $path = $file->store('journal_files', 'public');
            $validated['jou_files'] = $path;
            $validated['jou_filesname'] = $file->getClientOriginalName();
        }

        $item->update($validated);

        return redirect()->route('frontend.journalstatus.index')->with('success', 'Journal Status updated successfully.');
    }

    public function destroy($id)
    {
        $item = JournalStatus::findOrFail($id);
        if ($item->jou_files) {
            Storage::disk('public')->delete($item->jou_files);
        }
        $item->delete();

        return redirect()->route('frontend.journalstatus.index')->with('success', 'Journal Status deleted successfully.');
    }
}
