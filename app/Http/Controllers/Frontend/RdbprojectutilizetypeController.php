<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilizeType;
use Illuminate\Http\Request;

class RdbprojectutilizetypeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectUtilizeType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('utz_typr_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('utz_type_index')->paginate(10);
        
        return view('frontend.rdbprojectutilizetype.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectUtilizeType::findOrFail($id);
        return view('frontend.rdbprojectutilizetype.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbprojectutilizetype.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'utz_typr_name' => 'required|string|max:255',
            'utz_type_index' => 'nullable|integer',
        ]);

        $validated['created_at'] = now();

        RdbProjectUtilizeType::create($validated);

        return redirect()->route('frontend.rdbprojectutilizetype.index')->with('success', 'Utilization type created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectUtilizeType::findOrFail($id);
        return view('frontend.rdbprojectutilizetype.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectUtilizeType::findOrFail($id);
        
        $validated = $request->validate([
            'utz_typr_name' => 'required|string|max:255',
            'utz_type_index' => 'nullable|integer',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbprojectutilizetype.index')->with('success', 'Utilization type updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectUtilizeType::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbprojectutilizetype.index')->with('success', 'Utilization type deleted successfully.');
    }
}
