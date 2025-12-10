<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartmentCategory;
use Illuminate\Http\Request;

class RdbdepartmentcategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDepartmentCategory::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('depcat_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('depcat_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdepartmentcategory.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDepartmentCategory::findOrFail($id);
        return view('frontend.rdbdepartmentcategory.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbdepartmentcategory.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'depcat_name' => 'required|string|max:255',
        ]);

        $validated['created_at'] = now();

        RdbDepartmentCategory::create($validated);

        return redirect()->route('frontend.rdbdepartmentcategory.index')->with('success', 'Department category created successfully.');
    }

    public function edit($id)
    {
        $item = RdbDepartmentCategory::findOrFail($id);
        return view('frontend.rdbdepartmentcategory.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDepartmentCategory::findOrFail($id);
        
        $validated = $request->validate([
            'depcat_name' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbdepartmentcategory.index')->with('success', 'Department category updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDepartmentCategory::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbdepartmentcategory.index')->with('success', 'Department category deleted successfully.');
    }
}
