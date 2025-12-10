<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepmajor;
use Illuminate\Http\Request;

class RdbdepmajorController extends Controller
{
    public function index()
    {
        $items = RdbDepmajor::paginate(10);
        return view('backend.rdbdepmajor.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbdepmajor.create');
    }

    public function store(Request $request)
    {
        $item = new RdbDepmajor();
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbdepmajor.index')->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        $item = RdbDepmajor::findOrFail($id);
        return view('backend.rdbdepmajor.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbDepmajor::findOrFail($id);
        return view('backend.rdbdepmajor.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDepmajor::findOrFail($id);
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbdepmajor.index')->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDepmajor::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbdepmajor.index')->with('success', 'Deleted successfully.');
    }
}
