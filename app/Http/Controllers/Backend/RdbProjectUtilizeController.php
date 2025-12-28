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

        // Filter by data_show = 1 (Global scope handled it, but explicit just in case if needed)
        
        // Simple Search
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($qry) use ($q) {
                $qry->where('utz_department_name', 'like', "%{$q}%")
                    ->orWhere('utz_leading', 'like', "%{$q}%")
                    ->orWhereHas('project', function($p) use ($q) {
                        $p->where('pro_nameTH', 'like', "%{$q}%")
                          ->orWhere('pro_code', 'like', "%{$q}%");
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
            
            // Utilize types (stored as comma-separated string)
            if ($request->filled('utz_group')) {
                $group = $request->utz_group;
                $query->where(function($q) use ($group) {
                    $q->whereRaw("FIND_IN_SET(?, utz_group)", [$group]);
                });
            }

            // Location filtering (using the new chw_id style if passed, or fallback)
            if ($request->filled('chw_id')) {
                $query->where('chw_id', $request->chw_id);
            } elseif ($request->filled('changwat')) {
                $query->whereHas('changwat', function($q) use ($request) {
                    $q->where('changwat_t', 'like', '%' . $request->changwat . '%');
                    if ($request->filled('amphoe')) {
                        $q->where('amphoe_t', 'like', '%' . $request->amphoe . '%');
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
        $request->validate([
            'pro_id' => 'required|exists:rdb_project,pro_id',
            'utz_department_name' => 'required|string|max:255',
            'utz_date' => 'required|date',
            'chw_id' => 'required|exists:rdb_changwat,id',
            'utz_files_upload' => 'nullable|file|mimes:pdf|max:20480', // Max 20MB
        ]);

        $data = $request->except(['utz_files_upload']);

        // Format Budget (remove commas)
        if (isset($data['utz_budget'])) {
            $data['utz_budget'] = str_replace(',', '', $data['utz_budget']);
        }

        // Handle utz_group (Multi-select)
        if (isset($data['utz_group']) && is_array($data['utz_group'])) {
            $data['utz_group'] = implode(',', $data['utz_group']);
        }

        // Set creator and default date
        $data['user_created'] = auth()->id();
        $data['utz_date'] = $data['utz_date'] ?? date('Y-m-d');

        // Year Logic
        $years = $this->calculateYears($data['utz_date']);
        $data = array_merge($data, $years);

        // Default Defaults
        $data['data_show'] = 1;
        $data['created_at'] = now();

        $item = RdbProjectUtilize::create($data);

        // Handle File Upload
        if ($request->hasFile('utz_files_upload')) {
            $file = $request->file('utz_files_upload');
            $filename = 'utz_' . $item->utz_id . '_' . time() . '.pdf';
            $file->move(public_path('uploads/utilize'), $filename);
            $item->update(['utz_files' => $filename]);
        }

        return redirect()->route('backend.rdbprojectutilize.show', $item->utz_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
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
        $item = RdbProjectUtilize::with([
            'project.year', 
            'project.department',
            'project.rdbProjectWorks.researcher'
        ])->findOrFail($id);
        
        return view('backend.rdb_projectutilize.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        
        $request->validate([
            'utz_department_name' => 'required|string|max:255',
            'utz_date' => 'required|date',
            'chw_id' => 'required|exists:rdb_changwat,id',
            'utz_files_upload' => 'nullable|file|mimes:pdf|max:20480', // Max 20MB
        ]);

        $data = $request->except(['utz_files_upload']);

        // Format Budget (remove commas)
        if (isset($data['utz_budget'])) {
            $data['utz_budget'] = str_replace(',', '', $data['utz_budget']);
        }

        // Handle utz_group (Multi-select)
        if (isset($data['utz_group']) && is_array($data['utz_group'])) {
            $data['utz_group'] = implode(',', $data['utz_group']);
        }

        $data['user_updated'] = auth()->id();

        // Year Logic (re-calculate if date changed)
        if (isset($data['utz_date'])) {
            $years = $this->calculateYears($data['utz_date']);
            $data = array_merge($data, $years);
        }

        $item->update($data);

        // Handle File Upload
        if ($request->hasFile('utz_files_upload')) {
            $file = $request->file('utz_files_upload');
            $filename = 'utz_' . $item->utz_id . '_' . time() . '.pdf';
            $file->move(public_path('uploads/utilize'), $filename);
            
            // Should we delete old file? Optional but good practice.
            // if($item->utz_files && file_exists(public_path('uploads/utilize/'.$item->utz_files))) { @unlink(...); }
            
            $item->update(['utz_files' => $filename]);
        }

        return redirect()->route('backend.rdbprojectutilize.show', $item->utz_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        
        // Soft delete by hiding (Match project standard)
        $item->data_show = 0;
        $item->user_updated = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbprojectutilize.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว (ซ่อนการแสดงผล)');
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
    
    // AJAX: Global Location Search (Tambon, Amphoe, Changwat)
    public function searchLocation(Request $request)
    {
        $q = $request->get('q');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $locations = \App\Models\RdbChangwat::where('tambon_t', 'like', "%{$q}%")
            ->orWhere('amphoe_t', 'like', "%{$q}%")
            ->orWhere('changwat_t', 'like', "%{$q}%")
            ->limit(20)
            ->get()
            ->map(fn($loc) => [
                'id' => $loc->id,
                'value' => $loc->id,
                'text' => "{$loc->tambon_t} {$loc->amphoe_t} {$loc->changwat_t}", // Space instead of >
                'changwat' => $loc->changwat_t,
                'amphoe' => $loc->amphoe_t,
                'tambon' => $loc->tambon_t
            ]);

        return response()->json($locations);
    }

    private function calculateYears($date)
    {
        // Find Education Year
        $eduYear = \App\Models\RdbPublishedCheckyear::where('rdbyearedu_start', '<=', $date)
            ->where('rdbyearedu_end', '>=', $date)
            ->first();

        // Find Budget Year
        $budYear = \App\Models\RdbPublishedCheckyear::where('rdbyearbud_start', '<=', $date)
            ->where('rdbyearbud_end', '>=', $date)
            ->first();

        $utz_year_edu = $eduYear->year_id ?? null;
        $utz_year_bud = $budYear->year_id ?? null;
        
        // utz_year_id usually follows budget year, or fallback to edu year
        $utz_year_id = $utz_year_bud ?? $utz_year_edu;

        return [
            'utz_year_id' => $utz_year_id,
            'utz_year_bud' => $utz_year_bud,
            'utz_year_edu' => $utz_year_edu,
        ];
    }

    // AJAX: Get tambons by province + amphoe
    public function searchTambons(Request $request)
    {
        $changwat = $request->get('changwat');
        $amphoe = $request->get('amphoe');
        $withId = $request->get('with_id');
        
        if (!$changwat || !$amphoe) {
            return response()->json([]);
        }

        $query = \App\Models\RdbChangwat::where('changwat_t', $changwat)
            ->where('amphoe_t', $amphoe)
            ->whereNotNull('tambon_t')
            ->where('tambon_t', '!=', '')
            ->orderBy('tambon_t');
        
        if ($withId) {
            // Return with ID for form submission
            $tambons = $query->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'value' => $t->id, 
                    'text' => preg_replace('/^ต\./', '', $t->tambon_t)
                ]);
        } else {
            // Return distinct text values (for index filtering)
            $tambons = $query->select('tambon_t')
                ->distinct()
                ->pluck('tambon_t')
                ->map(fn($t) => ['value' => $t, 'text' => preg_replace('/^ต\./', '', $t)]);
        }

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
        $types = \App\Models\RdbProjectUtilizeType::orderBy('utz_type_id', 'asc')
            ->get()
            ->map(fn($t) => [
                'value' => $t->utz_type_id, 
                'text' => $t->utz_type_id . ' • ' . $t->utz_typr_name
            ]);

        return response()->json($types);
    }

    // AJAX: Search projects for utilize form
    public function searchProjects(Request $request)
    {
        $q = $request->get('q', '');
        
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $projects = \App\Models\RdbProject::with(['year', 'department', 'rdbProjectWorks' => function($q) {
                // Get Head or Director first
                $q->orderBy('position_id', 'asc');
            }, 'rdbProjectWorks.researcher'])
            ->where('pro_nameTH', 'like', "%{$q}%")
            ->orWhere('pro_nameEN', 'like', "%{$q}%")
            ->orWhere('pro_code', 'like', "%{$q}%")
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($p) => [
                'value' => $p->pro_id,
                'text' => $this->formatProjectText($p)
            ]);

        return response()->json($projects);
    }

    private function formatProjectText($project)
    {
        $year = $project->year->year_name ?? '-';
        // Use custom project code logic or fallback to ID/Code
        // $code = $project->pro_code ?? $project->pro_id; // Code removed to match requested format "Year • Name • Researcher"
        $name = strip_tags($project->pro_nameTH);
        
        // Find main researcher
        $researcherName = '-';
        if ($project->rdbProjectWorks->isNotEmpty()) {
            $mainWork = $project->rdbProjectWorks->first();
            if ($mainWork && $mainWork->researcher) {
                $researcherName = $mainWork->researcher->researcher_fname . ' ' . $mainWork->researcher->researcher_lname;
            }
        }
        
        // $department = $project->department->department_nameTH ?? '-'; // Removed to match screenshot brevity if needed, or keep?
        // Screenshot shows: "2568 • [Name] • [Researcher]"
        // It does NOT show Department in the screenshot example clearly, but "Published" usually has it.
        // Let's stick to the Screenshot: "2568 • Title • Name"
        
        return "{$year} • {$name} • {$researcherName}";
    }
}
