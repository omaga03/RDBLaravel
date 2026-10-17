<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbDip;
use App\Models\RdbDipType;
use App\Models\RdbDepartment;
use App\Models\RdbProject;
use App\Models\RdbResearcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RdbdipController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDip::with([
            'dipType', 
            'project', 
            'researcher',
            'researcher.prefix',
            'researcher.department'
        ]);

        if (Schema::hasColumn('rdb_dip', 'data_show')) {
            $query->where(function($q) {
                $q->where('data_show', 1)->orWhereNull('data_show');
            });
        }

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $searchWords = preg_split('/[\s\/,]+/', $searchTerm);

            $query->where(function($q) use ($searchTerm, $searchWords) {
                $q->where('dip_request_number', 'like', "%{$searchTerm}%")
                  ->orWhere('dip_number', 'like', "%{$searchTerm}%")
                  ->orWhere('dip_patent_number', 'like', "%{$searchTerm}%")
                  ->orWhere('dip_data2_name', 'like', "%{$searchTerm}%")
                  ->orWhere('dip_name', 'like', "%{$searchTerm}%")
                  ->orWhere('dip_publication_no', 'like', "%{$searchTerm}%");

                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('dip_data2_name', 'like', "%{$word}%")
                      ->orWhere('dip_name', 'like', "%{$word}%");
                }

                $q->orWhereHas('project', function($query) use ($searchTerm) {
                    $query->where('pro_nameTH', 'like', "%{$searchTerm}%")
                          ->orWhere('pro_nameEN', 'like', "%{$searchTerm}%")
                          ->orWhere('pro_code', 'like', "%{$searchTerm}%");
                });

                $q->orWhereHas('researcher', function($query) use ($searchWords) {
                    foreach ($searchWords as $word) {
                        if (empty($word)) continue;
                        $query->where(function($wq) use ($word) {
                            $wq->where('researcher_fname', 'like', "%{$word}%")
                               ->orWhere('researcher_lname', 'like', "%{$word}%")
                               ->orWhere('researcher_fnameEN', 'like', "%{$word}%")
                               ->orWhere('researcher_lnameEN', 'like', "%{$word}%");
                        });
                    }
                });
            });
        }

        // DIP Type filter
        if ($request->filled('dipt_id')) {
            $query->where('dipt_id', $request->dipt_id);
        }

        // Department filter (via researcher or project)
        if ($request->filled('department_id')) {
            $deptId = $request->department_id;
            $query->where(function($q) use ($deptId) {
                $q->whereHas('researcher', function($rq) use ($deptId) {
                    $rq->where('department_id', $deptId);
                })->orWhereHas('project', function($pq) use ($deptId) {
                    $pq->where('department_id', $deptId);
                });
            });
        }

        // Date range validation
        $hasDateStart = $request->filled('date_start');
        $hasDateEnd = $request->filled('date_end');
        
        if ($hasDateStart || $hasDateEnd) {
            if (!$hasDateStart || !$hasDateEnd) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'กรุณาระบุทั้งวันที่เริ่มและวันที่สิ้นสุด');
            }
            
            if ($request->date_end < $request->date_start) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น');
            }
            
            $query->whereDate('dip_request_date', '>=', $request->date_start)
                  ->whereDate('dip_request_date', '<=', $request->date_end);
        }

        $project = null;
        if ($request->filled('pro_id')) {
            $query->where('pro_id', $request->pro_id);
            $project = RdbProject::find($request->pro_id);
        }

        // Sort by date DESC, then ID DESC
        $query->orderBy('dip_request_date', 'desc')
              ->orderBy('dip_id', 'desc');

        $items = $query->paginate(10);

        // Add match sources if search
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            foreach ($items as $item) {
                $matches = [];
                if (stripos($item->dip_data2_name ?? '', $searchTerm) !== false || stripos($item->dip_name ?? '', $searchTerm) !== false) {
                    $matches[] = 'ชื่อผลงาน';
                }
                if (stripos($item->dip_request_number ?? '', $searchTerm) !== false) {
                    $matches[] = 'เลขที่คำขอ';
                }
                if (stripos($item->dip_patent_number ?? '', $searchTerm) !== false) {
                    $matches[] = 'เลขที่สิทธิบัตร/ทะเบียน';
                }
                if ($item->researcher && (stripos($item->researcher->researcher_fname ?? '', $searchTerm) !== false || stripos($item->researcher->researcher_lname ?? '', $searchTerm) !== false)) {
                    $matches[] = 'ผู้ประดิษฐ์';
                }
                if ($item->project && stripos($item->project->pro_nameTH ?? '', $searchTerm) !== false) {
                    $matches[] = 'โครงการวิจัย';
                }
                $item->match_sources = $matches;
            }
        }

        $dipTypes = RdbDipType::orderBy('dipt_name')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        
        return view('frontend.rdbdip.index', compact('items', 'project', 'dipTypes', 'departments'));
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
