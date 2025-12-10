<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedWork;
use App\Models\RdbPublished;
use App\Models\RdbResearcher;
use App\Models\RdbPublishedTypeAuthor;
use Illuminate\Http\Request;

class RdbpublishedworkController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublishedWork::with(['published', 'researcher', 'publishedTypeAuthor']);

        $items = $query->orderBy('published_id', 'desc')->paginate(10);
        
        return view('frontend.rdbpublishedwork.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbPublishedWork::with(['published', 'researcher', 'publishedTypeAuthor'])->findOrFail($id);
        return view('frontend.rdbpublishedwork.show', compact('item'));
    }

    public function create()
    {
        $publisheds = RdbPublished::all();
        $researchers = RdbResearcher::all();
        $authors = RdbPublishedTypeAuthor::all();
        return view('frontend.rdbpublishedwork.create', compact('publisheds', 'researchers', 'authors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'published_id' => 'required|exists:rdb_published,published_id',
            'researcher_id' => 'nullable|exists:rdb_researcher,researcher_id',
            'pubta_id' => 'nullable|exists:rdb_published_type_author,pubta_id',
            'pubw_main' => 'nullable|string|max:1',
            'pubw_bud' => 'nullable|numeric',
        ]);

        $validated['created_at'] = now();

        RdbPublishedWork::create($validated);

        return redirect()->route('frontend.rdbpublishedwork.index')->with('success', 'Published Work created successfully.');
    }

    public function edit($id)
    {
        $item = RdbPublishedWork::findOrFail($id);
        $publisheds = RdbPublished::all();
        $researchers = RdbResearcher::all();
        $authors = RdbPublishedTypeAuthor::all();
        return view('frontend.rdbpublishedwork.edit', compact('item', 'publisheds', 'researchers', 'authors'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedWork::findOrFail($id);
        
        $validated = $request->validate([
            'researcher_id' => 'nullable|exists:rdb_researcher,researcher_id',
            'pubta_id' => 'nullable|exists:rdb_published_type_author,pubta_id',
            'pubw_main' => 'nullable|string|max:1',
            'pubw_bud' => 'nullable|numeric',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbpublishedwork.index')->with('success', 'Published Work updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbPublishedWork::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbpublishedwork.index')->with('success', 'Published Work deleted successfully.');
    }
}
