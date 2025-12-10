<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectposition;
use Illuminate\Http\Request;

class RdbprojectpositionController extends Controller
{
    public function index()
    {
        $items = RdbProjectposition::paginate(10);
        return view('backend.rdbprojectposition.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbprojectposition.create');
    }

    public function store(Request $request)
    {
        $item = new RdbProjectposition();
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbprojectposition.index')->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        $item = RdbProjectposition::findOrFail($id);
        return view('backend.rdbprojectposition.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectposition::findOrFail($id);
        return view('backend.rdbprojectposition.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectposition::findOrFail($id);
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbprojectposition.index')->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectposition::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbprojectposition.index')->with('success', 'Deleted successfully.');
    }
}
