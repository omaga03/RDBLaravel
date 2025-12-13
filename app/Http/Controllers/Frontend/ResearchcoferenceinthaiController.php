<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ResearchCoferenceinthai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResearchcoferenceinthaiController extends Controller
{
    public function index(Request $request)
    {
        $query = ResearchCoferenceinthai::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('con_name', 'like', "%{$search}%")
                  ->orWhere('con_venue', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_start')) {
            $query->whereDate('con_even_date', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('con_even_date', '<=', $request->date_end);
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        
        return view('frontend.researchcoferenceinthai.index', compact('items'));
    }

    public function show($id)
    {
        $item = ResearchCoferenceinthai::findOrFail($id);
        return view('frontend.researchcoferenceinthai.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.researchcoferenceinthai.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'con_name' => 'required|string|max:255',
            'con_detail' => 'nullable|string',
            'con_even_date' => 'nullable|date',
            'con_sub_deadline' => 'nullable|date',
            'con_venue' => 'nullable|string|max:255',
            'con_website' => 'nullable|string|max:255',
            'con_img' => 'nullable|image|max:5120', // Max 5MB
            'con_site_img' => 'nullable|image|max:5120',
            'con_count' => 'nullable|integer',
        ]);

        if ($request->hasFile('con_img')) {
            $path = $request->file('con_img')->store('conference_images', 'public');
            $validated['con_img'] = $path;
        }

        if ($request->hasFile('con_site_img')) {
            $path = $request->file('con_site_img')->store('conference_images', 'public');
            $validated['con_site_img'] = $path;
        }

        $validated['created_at'] = now();

        ResearchCoferenceinthai::create($validated);

        return redirect()->route('frontend.researchcoferenceinthai.index')->with('success', 'Conference created successfully.');
    }

    public function edit($id)
    {
        $item = ResearchCoferenceinthai::findOrFail($id);
        return view('frontend.researchcoferenceinthai.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = ResearchCoferenceinthai::findOrFail($id);
        
        $validated = $request->validate([
            'con_name' => 'required|string|max:255',
            'con_detail' => 'nullable|string',
            'con_even_date' => 'nullable|date',
            'con_sub_deadline' => 'nullable|date',
            'con_venue' => 'nullable|string|max:255',
            'con_website' => 'nullable|string|max:255',
            'con_img' => 'nullable|image|max:5120',
            'con_site_img' => 'nullable|image|max:5120',
            'con_count' => 'nullable|integer',
        ]);

        if ($request->hasFile('con_img')) {
            if ($item->con_img) {
                Storage::disk('public')->delete($item->con_img);
            }
            $path = $request->file('con_img')->store('conference_images', 'public');
            $validated['con_img'] = $path;
        }

        if ($request->hasFile('con_site_img')) {
            if ($item->con_site_img) {
                Storage::disk('public')->delete($item->con_site_img);
            }
            $path = $request->file('con_site_img')->store('conference_images', 'public');
            $validated['con_site_img'] = $path;
        }

        $item->update($validated);

        return redirect()->route('frontend.researchcoferenceinthai.index')->with('success', 'Conference updated successfully.');
    }

    public function destroy($id)
    {
        $item = ResearchCoferenceinthai::findOrFail($id);
        if ($item->con_img) {
            Storage::disk('public')->delete($item->con_img);
        }
        if ($item->con_site_img) {
            Storage::disk('public')->delete($item->con_site_img);
        }
        $item->delete();

        return redirect()->route('frontend.researchcoferenceinthai.index')->with('success', 'Conference deleted successfully.');
    }
}
