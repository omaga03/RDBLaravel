<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDip;
use App\Models\RdbDipType;
use App\Models\RdbProject;
use App\Models\RdbResearcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RdbdipController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDip::with(['dipType', 'project', 'researcher']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('dip_request_number', 'like', "%{$search}%")
                  ->orWhere('dip_number', 'like', "%{$search}%")
                  ->orWhere('dip_patent_number', 'like', "%{$search}%")
                  ->orWhere('dip_data2_name', 'like', "%{$search}%")
                  ->orWhereHas('project', function($query) use ($search) {
                      $query->where('pro_nameTH', 'like', "%{$search}%");
                  })
                  ->orWhereHas('researcher', function($query) use ($search) {
                      $query->where('researcher_fname', 'like', "%{$search}%")
                            ->orWhere('researcher_lname', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$search}%"]);
                  });
            });
        }

        // Date range validation
        $hasDateStart = $request->filled('date_start');
        $hasDateEnd = $request->filled('date_end');
        
        if ($hasDateStart || $hasDateEnd) {
            // Both dates must be provided
            if (!$hasDateStart || !$hasDateEnd) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'กรุณาระบุทั้งวันที่เริ่มและวันที่สิ้นสุด');
            }
            
            // End date must be >= start date
            if ($request->date_end < $request->date_start) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'วันที่สิ้นสุดต้องมากกว่าหรือเท่ากับวันที่เริ่ม');
            }
            
            $query->whereDate('dip_request_date', '>=', $request->date_start)
                  ->whereDate('dip_request_date', '<=', $request->date_end);
        }

        $project = null;
        if ($request->filled('pro_id')) {
            $query->where('pro_id', $request->pro_id);
            $project = RdbProject::find($request->pro_id);
        }

        // Sort by date DESC, then name ASC
        $query->orderBy('dip_request_date', 'desc')
              ->orderBy('dip_data2_name', 'asc');

        $items = $query->paginate(10);
        
        return view('frontend.rdbdip.index', compact('items', 'project'));
    }

    public function show($id)
    {
        $item = RdbDip::with([
            'dipType', 
            'project',
            'project.department',
            'project.type',
            'project.typeSub',
            'project.rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            },
            'project.rdbProjectWorks.researcher',
            'project.rdbProjectWorks.researcher.prefix',
            'researcher',
            'researcher.prefix',
            'researcher.department'
        ])->findOrFail($id);
        return view('frontend.rdbdip.show', compact('item'));
    }

    public function create()
    {
        $dipTypes = RdbDipType::all();
        $projects = RdbProject::all();
        $researchers = RdbResearcher::all();
        return view('frontend.rdbdip.create', compact('dipTypes', 'projects', 'researchers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dip_type' => 'nullable|integer',
            'dipt_id' => 'nullable|exists:rdb_dip_type,dipt_id',
            'pro_id' => 'nullable|exists:rdb_project,pro_id',
            'researcher_id' => 'nullable|exists:rdb_researcher,researcher_id',
            'dip_request_number' => 'nullable|string|max:255',
            'dip_request_date' => 'nullable|date',
            'dip_request_dateget' => 'nullable|date',
            'dip_number' => 'nullable|string|max:255',
            'dip_publication_date' => 'nullable|date',
            'dip_publication_no' => 'nullable|string|max:255',
            'dip_patent_number' => 'nullable|string|max:255',
            'dip_startdate' => 'nullable|date',
            'dip_enddate' => 'nullable|date',
            'dip_note' => 'nullable|string',
            'data_show' => 'nullable|integer',
            // File validations
            'dip_files' => 'nullable|file|max:10240',
            'dip_data1_files' => 'nullable|file|max:10240',
            'dip_data2_files_con' => 'nullable|file|max:10240',
            'dip_data3_filesass1' => 'nullable|file|max:10240',
            'dip_data3_filesass2' => 'nullable|file|max:10240',
            'dip_data3_filesass3' => 'nullable|file|max:10240',
            'dip_data3_drawing_picture' => 'nullable|file|max:10240',
            'dip_data_forms_request' => 'nullable|file|max:10240',
        ]);

        // Handle File Uploads
        $fileFields = [
            'dip_files', 'dip_data1_files', 'dip_data2_files_con', 
            'dip_data3_filesass1', 'dip_data3_filesass2', 'dip_data3_filesass3', 
            'dip_data3_drawing_picture', 'dip_data_forms_request'
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('dip_files', 'public');
                $validated[$field] = $path;
            }
        }

        $validated['created_at'] = now();

        RdbDip::create($validated);

        return redirect()->route('frontend.rdbdip.index')->with('success', 'DIP record created successfully.');
    }

    public function edit($id)
    {
        $item = RdbDip::findOrFail($id);
        $dipTypes = RdbDipType::all();
        $projects = RdbProject::all();
        $researchers = RdbResearcher::all();
        return view('frontend.rdbdip.edit', compact('item', 'dipTypes', 'projects', 'researchers'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbDip::findOrFail($id);
        
        $validated = $request->validate([
            'dip_type' => 'nullable|integer',
            'dipt_id' => 'nullable|exists:rdb_dip_type,dipt_id',
            'pro_id' => 'nullable|exists:rdb_project,pro_id',
            'researcher_id' => 'nullable|exists:rdb_researcher,researcher_id',
            'dip_request_number' => 'nullable|string|max:255',
            'dip_request_date' => 'nullable|date',
            'dip_request_dateget' => 'nullable|date',
            'dip_number' => 'nullable|string|max:255',
            'dip_publication_date' => 'nullable|date',
            'dip_publication_no' => 'nullable|string|max:255',
            'dip_patent_number' => 'nullable|string|max:255',
            'dip_startdate' => 'nullable|date',
            'dip_enddate' => 'nullable|date',
            'dip_note' => 'nullable|string',
            'data_show' => 'nullable|integer',
            // File validations
            'dip_files' => 'nullable|file|max:10240',
            'dip_data1_files' => 'nullable|file|max:10240',
            'dip_data2_files_con' => 'nullable|file|max:10240',
            'dip_data3_filesass1' => 'nullable|file|max:10240',
            'dip_data3_filesass2' => 'nullable|file|max:10240',
            'dip_data3_filesass3' => 'nullable|file|max:10240',
            'dip_data3_drawing_picture' => 'nullable|file|max:10240',
            'dip_data_forms_request' => 'nullable|file|max:10240',
        ]);

        // Handle File Uploads
        $fileFields = [
            'dip_files', 'dip_data1_files', 'dip_data2_files_con', 
            'dip_data3_filesass1', 'dip_data3_filesass2', 'dip_data3_filesass3', 
            'dip_data3_drawing_picture', 'dip_data_forms_request'
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file
                if ($item->$field) {
                    Storage::disk('public')->delete($item->$field);
                }
                $path = $request->file($field)->store('dip_files', 'public');
                $validated[$field] = $path;
            }
        }

        $item->update($validated);

        return redirect()->route('frontend.rdbdip.index')->with('success', 'DIP record updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbDip::findOrFail($id);
        
        $fileFields = [
            'dip_files', 'dip_data1_files', 'dip_data2_files_con', 
            'dip_data3_filesass1', 'dip_data3_filesass2', 'dip_data3_filesass3', 
            'dip_data3_drawing_picture', 'dip_data_forms_request'
        ];

        foreach ($fileFields as $field) {
            if ($item->$field) {
                Storage::disk('public')->delete($item->$field);
            }
        }

        $item->delete();

        return redirect()->route('frontend.rdbdip.index')->with('success', 'DIP record deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = RdbDip::with(['dipType', 'project', 'researcher']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('dip_request_number', 'like', "%{$search}%")
                  ->orWhere('dip_number', 'like', "%{$search}%")
                  ->orWhere('dip_patent_number', 'like', "%{$search}%")
                  ->orWhere('dip_data2_name', 'like', "%{$search}%")
                  ->orWhereHas('project', function($query) use ($search) {
                      $query->where('pro_nameTH', 'like', "%{$search}%");
                  })
                  ->orWhereHas('researcher', function($query) use ($search) {
                      $query->where('researcher_fname', 'like', "%{$search}%")
                            ->orWhere('researcher_lname', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$search}%"]);
                  });
            });
        }

        // Date range validation (Same as index but skipping redirect for stream, just filter if valid)
        if ($request->filled('date_start') && $request->filled('date_end')) {
             if ($request->date_end >= $request->date_start) {
                $query->whereDate('dip_request_date', '>=', $request->date_start)
                      ->whereDate('dip_request_date', '<=', $request->date_end);
             }
        }

        if ($request->filled('pro_id')) {
            $query->where('pro_id', $request->pro_id);
        }

        // Sort by date DESC, then name ASC
        $query->orderBy('dip_request_date', 'desc')
              ->orderBy('dip_data2_name', 'asc');

        $items = $query->get();

        $filename = "ip_data_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            fputcsv($file, [
                'ID', 
                'เลขที่คำขอ', 
                'วันที่ยื่นคำขอ',
                'เลขที่ทะเบียน', 
                'ชื่อผลงาน', 
                'ประเภท', 
                'โครงการวิจัย',
                'ผู้ทรงสิทธิ/ผู้ประดิษฐ์'
            ]);

            foreach ($items as $item) {
                // Determine name (logic from view)
                $name = $item->dip_data2_name ?? '-';
                
                // Determine researcher name
                $researcherName = '-';
                if ($item->researcher) {
                    $researcherName = ($item->researcher->prefix->prefix_nameTH ?? '') . 
                                      $item->researcher->researcher_fname . ' ' . 
                                      $item->researcher->researcher_lname;
                }

                fputcsv($file, [
                    $item->dip_id,
                    $item->dip_request_number ?? '-',
                    $item->dip_request_date ? \Carbon\Carbon::parse($item->dip_request_date)->format('d/m/Y') : '-',
                    $item->dip_patent_number ?? '-', // Actually dip_patent_number or dip_number based on view logic? View uses both.
                    $name,
                    $item->dipType->dipt_name ?? '-',
                    $item->project->pro_nameTH ?? '-',
                    $researcherName
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
