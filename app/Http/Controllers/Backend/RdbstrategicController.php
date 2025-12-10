<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbStrategic;
use Illuminate\Http\Request;

class RdbstrategicController extends Controller
{
    public function index()
    {
        $items = RdbStrategic::paginate(10);
        return view('backend.rdbstrategic.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdbstrategic.create');
    }

    public function store(Request $request)
    {
        $item = new RdbStrategic();
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbstrategic.index')->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        $item = RdbStrategic::findOrFail($id);
        return view('backend.rdbstrategic.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbStrategic::findOrFail($id);
        return view('backend.rdbstrategic.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbStrategic::findOrFail($id);
        $item->fill($request->all());
        $item->save();
        return redirect()->route('backend.rdbstrategic.index')->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbStrategic::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbstrategic.index')->with('success', 'Deleted successfully.');
    }
}
