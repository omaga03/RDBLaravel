<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbYear;
use App\Models\RdbProject;
use Illuminate\Http\Request;

class RdbyearController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbYear::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('year_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('year_name', 'desc')->paginate(10);
        
        return view('frontend.rdbyear.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbYear::findOrFail($id);
        return view('frontend.rdbyear.show', compact('item'));
    }

    public function create()
    {
        // Auto-increment year_name
        $maxYear = RdbYear::max('year_name');
        $nextYear = $maxYear ? $maxYear + 1 : date('Y') + 543; // Buddhist year
        
        return view('frontend.rdbyear.create', compact('nextYear'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year_name' => 'required|integer|unique:rdb_year,year_name',
        ]);

        $validated['created_at'] = now();

        RdbYear::create($validated);

        return redirect()->route('frontend.rdbyear.index')->with('success', 'Year created successfully.');
    }

    public function edit($id)
    {
        $item = RdbYear::findOrFail($id);
        return view('frontend.rdbyear.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbYear::findOrFail($id);
        
        $validated = $request->validate([
            'year_name' => 'required|integer|unique:rdb_year,year_name,' . $id . ',year_id',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbyear.index')->with('success', 'Year updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbYear::findOrFail($id);
        
        // Check if year is used in projects
        $projectCount = RdbProject::where('year_id', $id)->count();
        
        if ($projectCount > 0) {
            return redirect()->route('frontend.rdbyear.index')
                ->with('error', 'Cannot delete: This year is used in ' . $projectCount . ' projects.');
        }
        
        $item->delete();

        return redirect()->route('frontend.rdbyear.index')->with('success', 'Year deleted successfully.');
    }
}

