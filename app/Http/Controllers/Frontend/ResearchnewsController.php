<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ResearchNews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResearchnewsController extends Controller
{
    public function index(Request $request)
    {
        $query = ResearchNews::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('news_name', 'like', "%{$search}%")
                  ->orWhere('news_detail', 'like', "%{$search}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        
        return view('frontend.researchnews.index', compact('items'));
    }

    public function show($id)
    {
        $item = ResearchNews::findOrFail($id);
        return view('frontend.researchnews.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.researchnews.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'news_type' => 'nullable|integer',
            'news_name' => 'required|string|max:255',
            'news_img' => 'nullable|image|max:5120', // Max 5MB
            'news_date' => 'nullable|date',
            'news_event_start' => 'nullable|date',
            'news_event_end' => 'nullable|date',
            'news_event_guarantee' => 'nullable|string|max:255',
            'news_detail' => 'nullable|string',
            'news_reference' => 'nullable|string|max:255',
            'news_link' => 'nullable|string|max:255',
            'news_count' => 'nullable|integer',
        ]);

        if ($request->hasFile('news_img')) {
            $path = $request->file('news_img')->store('news_images', 'public');
            $validated['news_img'] = $path;
        }

        $validated['created_at'] = now();

        ResearchNews::create($validated);

        return redirect()->route('frontend.researchnews.index')->with('success', 'News created successfully.');
    }

    public function edit($id)
    {
        $item = ResearchNews::findOrFail($id);
        return view('frontend.researchnews.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = ResearchNews::findOrFail($id);
        
        $validated = $request->validate([
            'news_type' => 'nullable|integer',
            'news_name' => 'required|string|max:255',
            'news_img' => 'nullable|image|max:5120',
            'news_date' => 'nullable|date',
            'news_event_start' => 'nullable|date',
            'news_event_end' => 'nullable|date',
            'news_event_guarantee' => 'nullable|string|max:255',
            'news_detail' => 'nullable|string',
            'news_reference' => 'nullable|string|max:255',
            'news_link' => 'nullable|string|max:255',
            'news_count' => 'nullable|integer',
        ]);

        if ($request->hasFile('news_img')) {
            if ($item->news_img) {
                Storage::disk('public')->delete($item->news_img);
            }
            $path = $request->file('news_img')->store('news_images', 'public');
            $validated['news_img'] = $path;
        }

        $item->update($validated);

        return redirect()->route('frontend.researchnews.index')->with('success', 'News updated successfully.');
    }

    public function destroy($id)
    {
        $item = ResearchNews::findOrFail($id);
        if ($item->news_img) {
            Storage::disk('public')->delete($item->news_img);
        }
        $item->delete();

        return redirect()->route('frontend.researchnews.index')->with('success', 'News deleted successfully.');
    }
}
