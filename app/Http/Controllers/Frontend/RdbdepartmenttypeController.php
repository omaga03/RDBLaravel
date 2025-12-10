<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartmentType;
use Illuminate\Http\Request;

class RdbdepartmenttypeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDepartmentType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tdepartment_nameTH', 'like', "%{$search}%")
                  ->orWhere('tdepartment_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('tdepartment_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdepartmenttype.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDepartmentType::findOrFail($id);
        return view('frontend.rdbdepartmenttype.show', compact('item'));
    }

    public function create()
    {
        return view('frontend.rdbdepartmenttype.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tdepartment_nameTH' => 'required|string|max:255',
            'tdepartment_nameEN' => 'nullable|string|max:255',
        ]);

        $validated['created_at'] = now();

        RdbDepartmentType::create($validated);

        return redirect()->route('frontend.rdbdepartmenttype.index')->with('success', 'Department type created successfully.');
    }

    public function edit($id)
    {
        $item = RdbDepartmentType::findOrFail($id);
        return view('frontend.rdbdepartmenttype.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDepartmentType::findOrFail($id);
        
        $validated = $request->validate([
            'tdepartment_nameTH' => 'required|string|max:255',
            'tdepartment_nameEN' => 'nullable|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbdepartmenttype.index')->with('success', 'Department type updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDepartmentType::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbdepartmenttype.index')->with('success', 'Department type deleted successfully.');
    }
}
