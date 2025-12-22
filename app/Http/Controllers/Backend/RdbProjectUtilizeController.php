<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilize;
use Illuminate\Http\Request;

class RdbProjectUtilizeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectUtilize::with(['project.rdbProjectWorks.researcher', 'changwat']);

        // Simple Search
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($qry) use ($q) {
                $qry->where('utz_department_name', 'like', "%{$q}%")
                    ->orWhere('utz_leading', 'like', "%{$q}%")
                    ->orWhereHas('project', function($p) use ($q) {
                        $p->where('pro_nameTH', 'like', "%{$q}%");
                    });
            });
        }

        // Advanced Search
        if ($request->search_mode === 'advanced') {
            if ($request->filled('utz_department_name')) {
                $query->where('utz_department_name', 'like', '%' . $request->utz_department_name . '%');
            }
            if ($request->filled('utz_leading')) {
                $query->where('utz_leading', 'like', '%' . $request->utz_leading . '%');
            }
            // Utilize types (multi-select)
            if ($request->filled('utz_group')) {
                $groups = is_array($request->utz_group) ? $request->utz_group : [$request->utz_group];
                $query->whereIn('utz_group', $groups);
            }
            // Location filtering - cascading (province > amphoe > tambon)
            if ($request->filled('changwat')) {
                $query->whereHas('changwat', function($q) use ($request) {
                    $q->where('changwat_t', $request->changwat);
                    if ($request->filled('amphoe')) {
                        $q->where('amphoe_t', $request->amphoe);
                    }
                    if ($request->filled('tambon')) {
                        $q->where('tambon_t', $request->tambon);
                    }
                });
            }
            if ($request->filled('year_id')) {
                $query->where('utz_year_id', $request->year_id);
            }
            if ($request->filled('date_start')) {
                $query->whereDate('utz_date', '>=', $request->date_start);
            }
            if ($request->filled('date_end')) {
                $query->whereDate('utz_date', '<=', $request->date_end);
            }
        }

        $items = $query->orderBy('utz_date', 'desc')->paginate(20);

        return view('backend.rdb_projectutilize.index', compact('items'));
    }

    public function create()
    {
        return view('backend.rdb_projectutilize.create');
    }

    public function store(Request $request)
    {
        // Validation...
        $validated = $request->validate([
            'utz_department_name' => 'required',
        ]);
        RdbProjectUtilize::create($validated);
        return redirect()->route('backend.rdbprojectutilize.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }


    public function show($id)
    {
        $item = RdbProjectUtilize::with([
            'project', 
            'changwat', 
            'utilizeType',
            'createdBy.researcher',
            'updatedBy.researcher'
        ])->findOrFail($id);

        // Increment page view counter
        $item->increment('utz_count');

        return view('backend.rdb_projectutilize.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        return view('backend.rdb_projectutilize.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('backend.rdbprojectutilize.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdbprojectutilize.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }

    public function downloadFile($id, $filename)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        
        // Path to the file
        $filename = trim($filename);
        $filePath = public_path('uploads/utilize/' . $filename);
        
        if (file_exists($filePath)) {
            // Increment file open counter
            $item->increment('utz_countfile');
            
            // Return the file for viewing/downloading with no-cache headers
            return response()->file($filePath, [
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        }
        
        return abort(404, 'File not found');
    }
    
    // AJAX: Get unique provinces
    public function searchProvinces()
    {
        $provinces = \App\Models\RdbChangwat::select('changwat_t')
            ->distinct()
            ->whereNotNull('changwat_t')
            ->where('changwat_t', '!=', '')
            ->orderBy('changwat_t')
            ->pluck('changwat_t')
            ->map(fn($p) => ['value' => $p, 'text' => preg_replace('/^จ\./', '', $p)]);

        return response()->json($provinces);
    }

    // AJAX: Get amphoes by province
    public function searchAmphoes(Request $request)
    {
        $changwat = $request->get('changwat');
        if (!$changwat) {
            return response()->json([]);
        }

        $amphoes = \App\Models\RdbChangwat::select('amphoe_t')
            ->distinct()
            ->where('changwat_t', $changwat)
            ->whereNotNull('amphoe_t')
            ->where('amphoe_t', '!=', '')
            ->orderBy('amphoe_t')
            ->pluck('amphoe_t')
            ->map(fn($a) => ['value' => $a, 'text' => preg_replace('/^อ\./', '', $a)]);

        return response()->json($amphoes);
    }

    // AJAX: Get tambons by province + amphoe
    public function searchTambons(Request $request)
    {
        $changwat = $request->get('changwat');
        $amphoe = $request->get('amphoe');
        if (!$changwat || !$amphoe) {
            return response()->json([]);
        }

        $tambons = \App\Models\RdbChangwat::select('tambon_t')
            ->distinct()
            ->where('changwat_t', $changwat)
            ->where('amphoe_t', $amphoe)
            ->whereNotNull('tambon_t')
            ->where('tambon_t', '!=', '')
            ->orderBy('tambon_t')
            ->pluck('tambon_t')
            ->map(fn($t) => ['value' => $t, 'text' => preg_replace('/^ต\./', '', $t)]);

        return response()->json($tambons);
    }

    // AJAX: Get years
    public function searchYears()
    {
        $years = \App\Models\RdbYear::orderBy('year_id', 'desc')
            ->get()
            ->map(fn($y) => [
                'value' => $y->year_id, 
                'text' => 'พ.ศ. ' . ($y->year_name ?? $y->year_id)
            ]);

        return response()->json($years);
    }

    // AJAX: Get utilize types
    public function searchUtilizeTypes()
    {
        $types = \App\Models\RdbProjectUtilizeType::orderBy('utz_type_index')
            ->orderBy('utz_typr_name')
            ->get()
            ->map(fn($t) => [
                'value' => $t->utz_type_id, 
                'text' => $t->utz_typr_name
            ]);

        return response()->json($types);
    }
}
