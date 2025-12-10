<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartment;
use Illuminate\Http\Request;

class RdbdepartmentController extends Controller
{
    public function index()
    {
        $items = RdbDepartment::paginate(10);
        return view('backend.rdbdepartment.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbdepartment.create');
    }

    public function store(Request $request)
    {
        $item = new RdbDepartment();
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbdepartment.index')->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        $item = RdbDepartment::findOrFail($id);
        return view('backend.rdbdepartment.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbDepartment::findOrFail($id);
        return view('backend.rdbdepartment.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDepartment::findOrFail($id);
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbdepartment.index')->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDepartment::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbdepartment.index')->with('success', 'Deleted successfully.');
    }
}
