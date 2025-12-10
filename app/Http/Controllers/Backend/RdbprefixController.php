<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPrefix;
use Illuminate\Http\Request;

class RdbprefixController extends Controller
{
    public function index()
    {
        $items = RdbPrefix::paginate(10);
        return view('backend.rdbprefix.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbprefix.create');
    }

    public function store(Request $request)
    {
        $item = new RdbPrefix();
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbprefix.index')->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        $item = RdbPrefix::findOrFail($id);
        return view('backend.rdbprefix.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbPrefix::findOrFail($id);
        return view('backend.rdbprefix.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPrefix::findOrFail($id);
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbprefix.index')->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbPrefix::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbprefix.index')->with('success', 'Deleted successfully.');
    }
}
