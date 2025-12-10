<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedBranch;
use Illuminate\Http\Request;

class RdbbranchController extends Controller
{
    public function index()
    {
        $items = RdbPublishedBranch::paginate(10);
        return view('backend.rdbbranch.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbbranch.create');
    }

    public function store(Request $request)
    {
        $item = new RdbPublishedBranch();
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbbranch.index')->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        return view('backend.rdbbranch.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        return view('backend.rdbbranch.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbbranch.index')->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbbranch.index')->with('success', 'Deleted successfully.');
    }
}
