<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartmenttype;
use Illuminate\Http\Request;

class RdbdepartmenttypeController extends Controller
{
    public function index()
    {
        $items = RdbDepartmenttype::paginate(10);
        return view('backend.rdbdepartmenttype.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbdepartmenttype.create');
    }

    public function store(Request $request)
    {
        $item = new RdbDepartmenttype();
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbdepartmenttype.index')->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        $item = RdbDepartmenttype::findOrFail($id);
        return view('backend.rdbdepartmenttype.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbDepartmenttype::findOrFail($id);
        return view('backend.rdbdepartmenttype.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDepartmenttype::findOrFail($id);
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbdepartmenttype.index')->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDepartmenttype::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbdepartmenttype.index')->with('success', 'Deleted successfully.');
    }
}
