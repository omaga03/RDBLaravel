<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentType;
use Illuminate\Http\Request;

class RdbdepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDepartment::with('departmentType');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('department_nameTH', 'like', "%{$search}%")
                  ->orWhere('department_nameEN', 'like', "%{$search}%")
                  ->orWhere('department_code', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('department_id', 'desc')->paginate(10);
        
        return view('frontend.rdbdepartment.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbDepartment::with('departmentType')->findOrFail($id);
        return view('frontend.rdbdepartment.show', compact('item'));
    }

    public function create()
    {
        $departmentTypes = RdbDepartmentType::all();
        return view('frontend.rdbdepartment.create', compact('departmentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_code' => 'nullable|string|max:10',
            'department_nameTH' => 'required|string|max:255',
            'department_nameEN' => 'nullable|string|max:255',
            'tdepartment_id' => 'nullable|exists:rdb_department_type,tdepartment_id',
            'department_color' => 'nullable|string|max:20',
        ]);

        $validated['created_at'] = now();

        RdbDepartment::create($validated);

        return redirect()->route('frontend.rdbdepartment.index')->with('success', 'Department created successfully.');
    }

    public function edit($id)
    {
        $item = RdbDepartment::findOrFail($id);
        $departmentTypes = RdbDepartmentType::all();
        return view('frontend.rdbdepartment.edit', compact('item', 'departmentTypes'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDepartment::findOrFail($id);
        
        $validated = $request->validate([
            'department_code' => 'nullable|string|max:10',
            'department_nameTH' => 'required|string|max:255',
            'department_nameEN' => 'nullable|string|max:255',
            'tdepartment_id' => 'nullable|exists:rdb_department_type,tdepartment_id',
            'department_color' => 'nullable|string|max:20',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbdepartment.index')->with('success', 'Department updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDepartment::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbdepartment.index')->with('success', 'Department deleted successfully.');
    }
}

