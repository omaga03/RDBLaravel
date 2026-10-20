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
use Illuminate\Support\Facades\Storage;

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
                  ->orWhere('dip_publication_no', 'like', "%{$searchTerm}%");

                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('dip_data2_name', 'like', "%{$word}%");
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

        // DIP ID / Title Autocomplete Search
        if ($request->filled('dip_id') && $request->dip_id !== 'pre') {
            $query->where('dip_id', $request->dip_id);
        } elseif ($request->filled('dip_name')) {
            $dipName = trim($request->dip_name);
            $query->where('dip_data2_name', 'like', "%{$dipName}%");
        }

        // Title Multi-Keyword Search (กลุ่มคำคั่นด้วยช่องว่าง + เจอทั้งหมด / เจอบางคำ)
        if ($request->filled('title_keywords')) {
            $keywords = array_values(array_filter(preg_split('/\s+/', trim($request->title_keywords))));
            $mode = $request->input('title_keywords_mode', 'all');

            if (!empty($keywords)) {
                if ($mode === 'all') {
                    $query->where(function($q) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $q->where('dip_data2_name', 'like', "%{$kw}%");
                        }
                    });
                } else {
                    $query->where(function($q) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $q->orWhere('dip_data2_name', 'like', "%{$kw}%");
                        }
                    });
                }
            }
        }

        // Researcher / Inventor Search
        if ($request->filled('researcher_id') && $request->researcher_id !== 'pre') {
            $query->where('researcher_id', $request->researcher_id);
        } elseif ($request->filled('researcher_name')) {
            $cleanName = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', trim($request->researcher_name));
            $searchWords = array_values(array_filter(preg_split('/\s+/', $cleanName)));
            if (!empty($searchWords)) {
                $query->whereHas('researcher', function($rq) use ($searchWords) {
                    foreach ($searchWords as $word) {
                        $rq->where(function($wq) use ($word) {
                            $wq->where('researcher_fname', 'like', "%{$word}%")
                               ->orWhere('researcher_lname', 'like', "%{$word}%");
                        });
                    }
                });
            }
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
            'project.rdbProjectWorks.researcher.department',
            'project.rdbProjectWorks.position',
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

    /**
     * Search Dips for Frontend TomSelect Autocomplete
     */
    public function searchDips(Request $request)
    {
        $term = trim($request->q);
        if (empty($term) || mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $dips = RdbDip::with(['dipType', 'researcher.prefix', 'researcher.department'])
            ->where(function($q) {
                if (Schema::hasColumn('rdb_dip', 'data_show')) {
                    $q->where('data_show', 1)->orWhereNull('data_show');
                }
            })
            ->where(function($q) use ($term) {
                $q->where('dip_data2_name', 'LIKE', "%{$term}%")
                  ->orWhere('dip_request_number', 'LIKE', "%{$term}%")
                  ->orWhere('dip_patent_number', 'LIKE', "%{$term}%")
                  ->orWhere('dip_number', 'LIKE', "%{$term}%")
                  ->orWhereHas('dipType', function($tq) use ($term) {
                      $tq->where('dipt_name', 'LIKE', "%{$term}%");
                  })
                  ->orWhereHas('researcher', function($rq) use ($term) {
                      $rq->where('researcher_fname', 'LIKE', "%{$term}%")
                         ->orWhere('researcher_lname', 'LIKE', "%{$term}%")
                         ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$term}%"]);
                  });
            })
            ->orderBy('dip_id', 'desc')
            ->limit(20)
            ->get();

        $results = [];
        foreach ($dips as $d) {
            $yearPrefix = '';
            if ($d->project && $d->project->year && $d->project->year->year_name) {
                $yearPrefix = "[{$d->project->year->year_name}] ";
            }
            $type = $d->dipType ? $d->dipType->dipt_name : '';
            $num = $d->dip_request_number ? "คำขอ: " . $d->dip_request_number : ($d->dip_patent_number ? "สิทธิบัตร: " . $d->dip_patent_number : '');
            
            $resName = '';
            if ($d->researcher) {
                $resName = trim($d->researcher->researcher_fname . ' ' . $d->researcher->researcher_lname);
            }
            $dept = $d->researcher && $d->researcher->department ? $d->researcher->department->department_nameTH : '';

            $meta = array_filter([
                $type ? "ประเภท: {$type}" : '', 
                $num, 
                $dept, 
                $resName ? "ผู้ประดิษฐ์: {$resName}" : ''
            ]);
            $sub = !empty($meta) ? ' [' . implode(', ', $meta) . ']' : '';

            $cleanName = strip_tags($d->dip_data2_name ?: 'ไม่มีชื่อผลงาน');
            $results[] = [
                'value' => $d->dip_id,
                'text' => $yearPrefix . $cleanName . $sub,
                'dip_name' => $cleanName,
                'dipt_name' => $type,
                'researcher' => $resName,
            ];
        }

        return response()->json($results);
    }

    /**
     * View / stream DIP files (main patent file and attachments)
     */
    public function viewFile(Request $request, $id)
    {
        $item = RdbDip::findOrFail($id);

        if (Schema::hasColumn('rdb_dip', 'data_show')) {
            if (isset($item->data_show) && (int) $item->data_show !== 1) {
                abort(404);
            }
        }

        $field = $request->query('field', 'dip_files');
        $allowedFields = [
            'dip_files',
            'dip_data1_files',
            'dip_data2_files_con',
            'dip_data3_drawing_picture',
            'dip_data_forms_request',
            'dip_data3_filesass1',
            'dip_data3_filesass2',
            'dip_data3_filesass3',
        ];

        if (!in_array($field, $allowedFields)) {
            abort(400, 'คำขอไฟล์ไม่ถูกต้อง');
        }

        $filename = $item->{$field};
        if (empty($filename)) {
            abort(404, 'ไม่มีไฟล์เอกสารนี้');
        }

        $filePath = $item->getDipFilePath($field);

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->response($filePath);
        }

        if ($filePath && file_exists(public_path('storage/' . $filePath))) {
            return response()->file(public_path('storage/' . $filePath));
        }

        if ($filePath && file_exists(public_path($filePath))) {
            return response()->file(public_path($filePath));
        }

        // Support legacy yii2 datadip
        $legacyPath = 'datadip/' . $item->dip_id . '/' . basename($filename);
        if (file_exists(public_path($legacyPath))) {
            return response()->file(public_path($legacyPath));
        }
        if (file_exists(base_path('yii2_rdb-main/frontend/web/' . $legacyPath))) {
            return response()->file(base_path('yii2_rdb-main/frontend/web/' . $legacyPath));
        }

        abort(404, 'ไม่พบไฟล์เอกสารบนเซิร์ฟเวอร์');
    }
}
