<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilize;
use App\Models\RdbProjectUtilizeType;
use App\Models\RdbDepartment;
use App\Models\RdbProject;
use App\Models\RdbChangwat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class RdbprojectutilizeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectUtilize::with([
            'project', 
            'project.department',
            'project.rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            },
            'project.rdbProjectWorks.researcher',
            'project.rdbProjectWorks.researcher.prefix',
            'changwat',
            'utilizeType'
        ]);

        if (Schema::hasColumn('rdb_project_utilize', 'data_show')) {
            $query->where(function($q) {
                $q->where('data_show', 1)->orWhereNull('data_show');
            });
        }

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $searchWords = preg_split('/[\s\/,]+/', $searchTerm);

            $query->where(function($q) use ($searchTerm, $searchWords) {
                $q->where('utz_detail', 'like', "%{$searchTerm}%")
                  ->orWhere('utz_leading', 'like', "%{$searchTerm}%")
                  ->orWhere('utz_department_name', 'like', "%{$searchTerm}%");

                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('utz_department_name', 'like', "%{$word}%")
                      ->orWhere('utz_detail', 'like', "%{$word}%");
                }

                $q->orWhereHas('project', function($query) use ($searchTerm, $searchWords) {
                    $query->where('pro_nameTH', 'like', "%{$searchTerm}%")
                          ->orWhere('pro_nameEN', 'like', "%{$searchTerm}%")
                          ->orWhere('pro_code', 'like', "%{$searchTerm}%")
                          ->orWhereHas('rdbProjectWorks', function($q) use ($searchWords) {
                              $q->whereHas('researcher', function($resQuery) use ($searchWords) {
                                  foreach ($searchWords as $word) {
                                      if (empty($word)) continue;
                                      $resQuery->where('researcher_fname', 'like', "%{$word}%")
                                               ->orWhere('researcher_lname', 'like', "%{$word}%");
                                  }
                              });
                          });
                });
            });
        }

        // Filter by Utilize Type
        if ($request->filled('utz_group')) {
            $query->where('utz_group', $request->utz_group);
        }

        // Title / Utilize Autocomplete Search
        if ($request->filled('utz_id') && $request->utz_id !== 'pre') {
            $query->where('utz_id', $request->utz_id);
        } elseif ($request->filled('utz_name')) {
            $utzName = trim($request->utz_name);
            $query->where(function($q) use ($utzName) {
                $q->where('utz_department_name', 'like', "%{$utzName}%")
                  ->orWhere('utz_detail', 'like', "%{$utzName}%")
                  ->orWhereHas('project', function($pq) use ($utzName) {
                      $pq->where('pro_nameTH', 'like', "%{$utzName}%")
                         ->orWhere('pro_nameEN', 'like', "%{$utzName}%");
                  });
            });
        }

        // Title Multi-Keyword Search (กลุ่มคำคั่นด้วยช่องว่าง + เจอทั้งหมด / เจอบางคำ)
        if ($request->filled('title_keywords')) {
            $keywords = array_values(array_filter(preg_split('/\s+/', trim($request->title_keywords))));
            $mode = $request->input('title_keywords_mode', 'all');

            if (!empty($keywords)) {
                if ($mode === 'all') {
                    $query->where(function($q) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $q->where(function($sub) use ($kw) {
                                $sub->where('utz_department_name', 'like', "%{$kw}%")
                                    ->orWhere('utz_detail', 'like', "%{$kw}%")
                                    ->orWhereHas('project', function($pq) use ($kw) {
                                        $pq->where('pro_nameTH', 'like', "%{$kw}%")
                                           ->orWhere('pro_nameEN', 'like', "%{$kw}%");
                                    });
                            });
                        }
                    });
                } else {
                    $query->where(function($q) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $q->orWhere(function($sub) use ($kw) {
                                $sub->where('utz_department_name', 'like', "%{$kw}%")
                                    ->orWhere('utz_detail', 'like', "%{$kw}%")
                                    ->orWhereHas('project', function($pq) use ($kw) {
                                        $pq->where('pro_nameTH', 'like', "%{$kw}%")
                                           ->orWhere('pro_nameEN', 'like', "%{$kw}%");
                                    });
                            });
                        }
                    });
                }
            }
        }

        // Researcher / Person Search
        if ($request->filled('researcher_id') && $request->researcher_id !== 'pre') {
            $resId = $request->researcher_id;
            $query->whereHas('project.rdbProjectWorks', function($q) use ($resId) {
                $q->where('researcher_id', $resId);
            });
        } elseif ($request->filled('researcher_name')) {
            $cleanName = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', trim($request->researcher_name));
            $searchWords = array_values(array_filter(preg_split('/\s+/', $cleanName)));
            if (!empty($searchWords)) {
                $query->where(function($q) use ($searchWords) {
                    $q->whereHas('project.rdbProjectWorks.researcher', function($rq) use ($searchWords) {
                        foreach ($searchWords as $word) {
                            $rq->where(function($wq) use ($word) {
                                $wq->where('researcher_fname', 'like', "%{$word}%")
                                   ->orWhere('researcher_lname', 'like', "%{$word}%");
                            });
                        }
                    })->orWhere(function($lq) use ($searchWords) {
                        foreach ($searchWords as $word) {
                            $lq->where('utz_leading', 'like', "%{$word}%");
                        }
                    });
                });
            }
        }

        // Filter by Department (of Project)
        if ($request->filled('department_id')) {
            $deptId = $request->department_id;
            $query->whereHas('project', function($pq) use ($deptId) {
                $pq->where('department_id', $deptId);
            });
        }

        // Location Filters (ต. อ. จ.)
        if ($request->filled('chw_id')) {
            $query->where('chw_id', $request->chw_id);
        } elseif ($request->filled('changwat')) {
            $query->whereHas('changwat', function($q) use ($request) {
                $q->where('changwat_t', 'like', '%' . $request->changwat . '%');
                if ($request->filled('amphoe')) {
                    $q->where('amphoe_t', 'like', '%' . $request->amphoe . '%');
                }
                if ($request->filled('tambon')) {
                    $q->where('tambon_t', 'like', '%' . $request->tambon . '%');
                }
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
            
            $query->whereDate('utz_date', '>=', $request->date_start)
                  ->whereDate('utz_date', '<=', $request->date_end);
        }

        $project = null;
        if ($request->filled('pro_id')) {
            $query->where('pro_id', $request->pro_id);
            $project = RdbProject::find($request->pro_id);
        }

        // Sort by Date DESC, then utz_id DESC
        $query->orderBy('utz_date', 'desc')
              ->orderBy('utz_id', 'desc');

        $items = $query->paginate(10);

        // Add match sources if search
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            foreach ($items as $item) {
                $matches = [];
                if (stripos($item->utz_department_name ?? '', $searchTerm) !== false) {
                    $matches[] = 'หน่วยงาน';
                }
                if (stripos($item->utz_leading ?? '', $searchTerm) !== false) {
                    $matches[] = 'ผู้แทน/ผู้ลงนาม';
                }
                if ($item->project && stripos($item->project->pro_nameTH ?? '', $searchTerm) !== false) {
                    $matches[] = 'โครงการวิจัย';
                }
                if (stripos($item->utz_detail ?? '', $searchTerm) !== false) {
                    $matches[] = 'รายละเอียด';
                }
                $item->match_sources = $matches;
            }
        }

        $utilizeTypes = RdbProjectUtilizeType::all();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        
        return view('frontend.rdbprojectutilize.index', compact('items', 'project', 'utilizeTypes', 'departments'));
    }

    public function show($id)
    {
        $item = RdbProjectUtilize::with([
            'project',
            'project.department',
            'project.type',
            'project.typeSub',
            'project.rdbProjectWorks' => function($query) {
                $query->orderBy('position_id', 'asc');
            },
            'project.rdbProjectWorks.researcher',
            'project.rdbProjectWorks.researcher.prefix',
            'project.rdbProjectWorks.researcher.department',
            'project.rdbProjectWorks.position',
            'changwat',
            'utilizeType'
        ])->findOrFail($id);

        if (Schema::hasColumn('rdb_project_utilize', 'utz_count')) {
            $item->increment('utz_count');
        }

        return view('frontend.rdbprojectutilize.show', compact('item'));
    }

    /**
     * Search Utilizes for Frontend TomSelect Autocomplete
     */
    public function searchUtilizes(Request $request)
    {
        $term = trim($request->q);
        if (empty($term) || mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $items = RdbProjectUtilize::with([
                'project.year', 
                'project.type', 
                'project.rdbProjectWorks.researcher.prefix', 
                'utilizeType'
            ])
            ->where(function($q) {
                if (Schema::hasColumn('rdb_project_utilize', 'data_show')) {
                    $q->where('data_show', 1)->orWhereNull('data_show');
                }
            })
            ->where(function($q) use ($term) {
                $q->where('utz_department_name', 'LIKE', "%{$term}%")
                  ->orWhere('utz_leading', 'LIKE', "%{$term}%")
                  ->orWhere('utz_detail', 'LIKE', "%{$term}%")
                  ->orWhereHas('utilizeType', function($tq) use ($term) {
                      $tq->where('utz_typr_name', 'LIKE', "%{$term}%");
                  })
                  ->orWhereHas('project', function($pq) use ($term) {
                      $pq->where('pro_nameTH', 'LIKE', "%{$term}%")
                         ->orWhere('pro_nameEN', 'LIKE', "%{$term}%")
                         ->orWhere('pro_code', 'LIKE', "%{$term}%")
                         ->orWhereHas('type', function($ptq) use ($term) {
                             $ptq->where('pt_name', 'LIKE', "%{$term}%");
                         })
                         ->orWhereHas('researchers', function($rq) use ($term) {
                             $rq->where('researcher_fname', 'LIKE', "%{$term}%")
                                ->orWhere('researcher_lname', 'LIKE', "%{$term}%")
                                ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$term}%"]);
                         });
                  });
            })
            ->orderBy('utz_id', 'desc')
            ->limit(20)
            ->get();

        $results = [];
        foreach ($items as $item) {
            $projName = $item->project ? strip_tags($item->project->pro_nameTH) : '';
            $deptName = $item->utz_department_name ?: '';
            $uTypeName = $item->utilizeType ? $item->utilizeType->utz_typr_name : '';
            $yearPrefix = ($item->project && $item->project->year && $item->project->year->year_name) ? "[{$item->project->year->year_name}] " : '';

            $researcherNames = [];
            if ($item->project && $item->project->rdbProjectWorks) {
                foreach ($item->project->rdbProjectWorks as $pw) {
                    if ($pw->researcher) {
                        $researcherNames[] = trim($pw->researcher->researcher_fname . ' ' . $pw->researcher->researcher_lname);
                    }
                }
            }
            $resText = !empty($researcherNames) ? implode(', ', array_slice($researcherNames, 0, 2)) . (count($researcherNames) > 2 ? '...' : '') : ($item->utz_leading ? "ผู้แทน: {$item->utz_leading}" : '');

            $meta = array_filter([
                $uTypeName ? "ด้าน: {$uTypeName}" : '', 
                $deptName ? "หน่วยงาน: {$deptName}" : '', 
                $resText ? "นักวิจัย: {$resText}" : ''
            ]);
            $sub = !empty($meta) ? ' [' . implode(', ', $meta) . ']' : '';

            $results[] = [
                'value' => $item->utz_id,
                'text' => $yearPrefix . ($projName ?: $deptName) . $sub,
                'utz_name' => $projName ?: $deptName,
                'utz_type' => $uTypeName,
                'researcher' => $resText,
            ];
        }

        return response()->json($results);
    }

    /**
     * View / stream utilization file
     */
    public function viewFile(Request $request, $id)
    {
        $item = RdbProjectUtilize::findOrFail($id);

        if (Schema::hasColumn('rdb_project_utilize', 'data_show')) {
            if (isset($item->data_show) && (int) $item->data_show !== 1) {
                abort(404);
            }
        }

        $filename = $request->query('filename') ?: $item->utz_files;
        if (empty($filename) && empty($item->uf_path)) {
            abort(404, 'ไม่มีไฟล์เอกสารนี้');
        }

        $filePath = $item->getUtilizeFilePath($filename);

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $item->increment('utz_countfile');
            return Storage::disk('public')->response($filePath);
        }

        if ($filePath && file_exists(public_path('storage/' . $filePath))) {
            $item->increment('utz_countfile');
            return response()->file(public_path('storage/' . $filePath));
        }

        if ($filePath && file_exists(public_path($filePath))) {
            $item->increment('utz_countfile');
            return response()->file(public_path($filePath));
        }

        // Support legacy yii2 datautilize
        $safeName = basename(trim($filename));
        if ($safeName) {
            $legacyPath = 'datautilize/' . $item->utz_id . '/' . $safeName;
            if (file_exists(public_path($legacyPath))) {
                $item->increment('utz_countfile');
                return response()->file(public_path($legacyPath));
            }
            if (file_exists(base_path('yii2_rdb-main/frontend/web/' . $legacyPath))) {
                $item->increment('utz_countfile');
                return response()->file(base_path('yii2_rdb-main/frontend/web/' . $legacyPath));
            }
        }

        abort(404, 'ไม่พบไฟล์เอกสารบนเซิร์ฟเวอร์');
    }

    public function searchProvinces()
    {
        $provinces = RdbChangwat::select('changwat_t')
            ->distinct()
            ->whereNotNull('changwat_t')
            ->where('changwat_t', '!=', '')
            ->orderByRaw("CASE WHEN changwat_t = 'จ.เพชรบูรณ์' THEN 0 WHEN changwat_t = 'จ.พิจิตร' THEN 1 ELSE 2 END")
            ->orderBy('changwat_t')
            ->pluck('changwat_t')
            ->map(fn($p) => ['value' => $p, 'text' => preg_replace('/^จ\./', '', $p)]);

        return response()->json($provinces);
    }

    public function searchAmphoes(Request $request)
    {
        $changwat = $request->get('changwat');
        if (!$changwat) {
            return response()->json([]);
        }

        $amphoes = RdbChangwat::select('amphoe_t')
            ->distinct()
            ->where('changwat_t', $changwat)
            ->whereNotNull('amphoe_t')
            ->where('amphoe_t', '!=', '')
            ->orderBy('amphoe_t')
            ->pluck('amphoe_t')
            ->map(fn($a) => ['value' => $a, 'text' => preg_replace('/^อ\./', '', $a)]);

        return response()->json($amphoes);
    }

    public function searchTambons(Request $request)
    {
        $changwat = $request->get('changwat');
        $amphoe = $request->get('amphoe');
        
        if (!$changwat || !$amphoe) {
            return response()->json([]);
        }

        $tambons = RdbChangwat::where('changwat_t', $changwat)
            ->where('amphoe_t', $amphoe)
            ->whereNotNull('tambon_t')
            ->where('tambon_t', '!=', '')
            ->orderBy('tambon_t')
            ->select('tambon_t')
            ->distinct()
            ->pluck('tambon_t')
            ->map(fn($t) => ['value' => $t, 'text' => preg_replace('/^ต\./', '', $t)]);

        return response()->json($tambons);
    }
}
