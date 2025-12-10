<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectwork;
use Illuminate\Http\Request;

class RdbprojectworkController extends Controller
{
    public function index()
    {
        $items = RdbProjectwork::paginate(10);
        return view('backend.rdbprojectwork.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbprojectwork.create');
    }

    public function store(Request $request)
    {
        $item = new RdbProjectwork();
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbprojectwork.index')->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        $item = RdbProjectwork::findOrFail($id);
        return view('backend.rdbprojectwork.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectwork::findOrFail($id);
        return view('backend.rdbprojectwork.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectwork::findOrFail($id);
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbprojectwork.index')->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectwork::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbprojectwork.index')->with('success', 'Deleted successfully.');
    }
}
