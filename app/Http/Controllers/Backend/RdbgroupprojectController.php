<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbGroupproject;
use Illuminate\Http\Request;

class RdbgroupprojectController extends Controller
{
    public function index()
    {
        $items = RdbGroupproject::paginate(10);
        return view('backend.rdbgroupproject.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbgroupproject.create');
    }

    public function store(Request $request)
    {
        $item = new RdbGroupproject();
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbgroupproject.index')->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        $item = RdbGroupproject::findOrFail($id);
        return view('backend.rdbgroupproject.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbGroupproject::findOrFail($id);
        return view('backend.rdbgroupproject.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbGroupproject::findOrFail($id);
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbgroupproject.index')->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbGroupproject::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbgroupproject.index')->with('success', 'Deleted successfully.');
    }
}
