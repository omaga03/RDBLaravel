<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjecttype;
use Illuminate\Http\Request;

class RdbprojecttypeController extends Controller
{
    public function index()
    {
        $items = RdbProjecttype::paginate(10);
        return view('backend.rdbprojecttype.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbprojecttype.create');
    }

    public function store(Request $request)
    {
        $item = new RdbProjecttype();
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbprojecttype.index')->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        $item = RdbProjecttype::findOrFail($id);
        return view('backend.rdbprojecttype.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjecttype::findOrFail($id);
        return view('backend.rdbprojecttype.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjecttype::findOrFail($id);
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbprojecttype.index')->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjecttype::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbprojecttype.index')->with('success', 'Deleted successfully.');
    }
}
