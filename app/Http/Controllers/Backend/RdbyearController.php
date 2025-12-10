<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbYear;
use Illuminate\Http\Request;

class RdbyearController extends Controller
{
    public function index()
    {
        $items = RdbYear::paginate(10);
        return view('backend.rdbyear.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbyear.create');
    }

    public function store(Request $request)
    {
        $item = new RdbYear();
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbyear.index')->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        $item = RdbYear::findOrFail($id);
        return view('backend.rdbyear.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbYear::findOrFail($id);
        return view('backend.rdbyear.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbYear::findOrFail($id);
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbyear.index')->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbYear::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbyear.index')->with('success', 'Deleted successfully.');
    }
}
