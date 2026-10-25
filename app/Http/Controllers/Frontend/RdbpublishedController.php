<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublished;
use App\Models\RdbYear;
use App\Models\RdbResearcher;
use App\Models\RdbDepartment;
use App\Models\RdbPublishedType;
use App\Models\RdbProject;
use App\Models\RdbPublishedTypeAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RdbpublishedController extends Controller
{
    protected function buildFilteredQuery(Request $request)
    {
        $query = RdbPublished::with(['year', 'researcher', 'researcher.prefix', 'department', 'pubtype', 'project', 'authors', 'authors.prefix'])
            ->where('data_show', 1);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pub_name', 'like', "%{$search}%")
                  ->orWhere('pub_name_journal', 'like', "%{$search}%")
                  ->orWhere('pub_keyword', 'like', "%{$search}%")
                  ->orWhere('pub_note', 'like', "%{$search}%")
                  ->orWhereHas('researcher', function($query) use ($search) {
                      $query->where('researcher_fname', 'like', "%{$search}%")
                            ->orWhere('researcher_lname', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$search}%"]);
                  })
                  ->orWhereHas('authors', function($query) use ($search) {
                      $query->where('researcher_fname', 'like', "%{$search}%")
                            ->orWhere('researcher_lname', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$search}%"]);
                  })
                  ->orWhereHas('project', function($query) use ($search) {
                      $query->where('pro_nameTH', 'like', "%{$search}%");
                  });
            });
        }

        // Publication ID / Title Autocomplete Search
        if ($request->filled('pub_id') && $request->pub_id !== 'pre') {
            $query->where('id', $request->pub_id);
        } elseif ($request->filled('pub_name')) {
            $pubName = trim($request->pub_name);
            $query->where(function($q) use ($pubName) {
                $q->where('pub_name', 'like', "%{$pubName}%")
                  ->orWhere('pub_name_journal', 'like', "%{$pubName}%");
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
                                $sub->where('pub_name', 'like', "%{$kw}%")
                                    ->orWhere('pub_name_journal', 'like', "%{$kw}%");
                            });
                        }
                    });
                } else {
                    $query->where(function($q) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $q->orWhere(function($sub) use ($kw) {
                                $sub->where('pub_name', 'like', "%{$kw}%")
                                    ->orWhere('pub_name_journal', 'like', "%{$kw}%");
                            });
                        }
                    });
                }
            }
        }

        // Researcher / Author Search
        if ($request->filled('researcher_id') && $request->researcher_id !== 'pre') {
            $resId = $request->researcher_id;
            $query->where(function($q) use ($resId) {
                $q->where('researcher_id', $resId)
                  ->orWhereHas('authors', function($aq) use ($resId) {
                      $aq->where('rdb_researcher.researcher_id', $resId);
                  });
            });
        } elseif ($request->filled('researcher_name')) {
            $cleanName = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', trim($request->researcher_name));
            $searchWords = array_values(array_filter(preg_split('/\s+/', $cleanName)));
            if (!empty($searchWords)) {
                $query->where(function($q) use ($searchWords) {
                    $q->whereHas('researcher', function($rq) use ($searchWords) {
                        foreach ($searchWords as $word) {
                            $rq->where(function($wq) use ($word) {
                                $wq->where('researcher_fname', 'like', "%{$word}%")
                                   ->orWhere('researcher_lname', 'like', "%{$word}%");
                            });
                        }
                    })->orWhereHas('authors', function($aq) use ($searchWords) {
                        foreach ($searchWords as $word) {
                            $aq->where(function($wq) use ($word) {
                                $wq->where('researcher_fname', 'like', "%{$word}%")
                                   ->orWhere('researcher_lname', 'like', "%{$word}%");
                            });
                        }
                    });
                });
            }
        }

        // Department filter
        if ($request->filled('department_id')) {
            $deptId = $request->department_id;
            $query->where(function($q) use ($deptId) {
                $q->where('department_id', $deptId)
                  ->orWhereHas('researcher', function($rq) use ($deptId) {
                      $rq->where('department_id', $deptId);
                  })
                  ->orWhereHas('authors', function($aq) use ($deptId) {
                      $aq->where('department_id', $deptId);
                  });
            });
        }

        if ($request->filled('date_start') && $request->filled('date_end')) {
            $query->whereDate('pub_date', '>=', $request->date_start)
                  ->whereDate('pub_date', '<=', $request->date_end);
        }

        if ($request->filled('pro_id')) {
            $query->where('pro_id', $request->pro_id);
        }

        if ($request->filled('pubtype_id')) {
            $query->where('pubtype_id', $request->pubtype_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('pub_score')) {
            $query->where('pub_score', $request->pub_score);
        }

        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        return $query;
    }

    public function index(Request $request)
    {
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
        }

        $query = $this->buildFilteredQuery($request);

        $project = null;
        if ($request->filled('pro_id')) {
            $project = RdbProject::find($request->pro_id);
        }
        
        // Sort by date DESC, then name ASC
        $query->orderBy('pub_date', 'desc')
              ->orderBy('pub_name', 'asc');
        
        $items = $query->paginate(10);
        
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $pubTypes = RdbPublishedType::orderBy('pubtype_group')->orderBy('pubtype_subgroup')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();

        return view('frontend.rdbpublished.index', compact('items', 'years', 'project', 'pubTypes', 'departments'));
    }

    /**
     * Search publications for Frontend TomSelect Autocomplete
     */
    public function searchPublications(Request $request)
    {
        $term = trim($request->q);
        if (empty($term) || mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $pubs = RdbPublished::with(['year', 'pubtype', 'department', 'researcher.prefix', 'authors.prefix'])
            ->where(function($q) use ($term) {
                $q->where('pub_name', 'LIKE', "%{$term}%")
                  ->orWhere('pub_name_journal', 'LIKE', "%{$term}%")
                  ->orWhereHas('pubtype', function($tq) use ($term) {
                      $tq->where('pubtype_group', 'LIKE', "%{$term}%")
                         ->orWhere('pubtype_grouptype', 'LIKE', "%{$term}%")
                         ->orWhere('pubtype_subgroup', 'LIKE', "%{$term}%");
                  })
                  ->orWhereHas('researcher', function($rq) use ($term) {
                      $rq->where('researcher_fname', 'LIKE', "%{$term}%")
                         ->orWhere('researcher_lname', 'LIKE', "%{$term}%")
                         ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$term}%"]);
                  })
                  ->orWhereHas('authors', function($aq) use ($term) {
                      $aq->where('researcher_fname', 'LIKE', "%{$term}%")
                         ->orWhere('researcher_lname', 'LIKE', "%{$term}%")
                         ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$term}%"]);
                  });
            })
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $results = [];
        foreach ($pubs as $p) {
            $yearPrefix = ($p->year && $p->year->year_name) ? "[{$p->year->year_name}] " : '';
            $type = $p->pubtype ? ($p->pubtype->pubtype_subgroup ?? $p->pubtype->pubtype_group) : '';
            $dept = $p->department ? ($p->department->department_nameTH ?? $p->department->department_name) : '';

            $authorNames = [];
            if ($p->authors && $p->authors->isNotEmpty()) {
                foreach ($p->authors as $a) {
                    $authorNames[] = trim($a->researcher_fname . ' ' . $a->researcher_lname);
                }
            } elseif ($p->researcher) {
                $authorNames[] = trim($p->researcher->researcher_fname . ' ' . $p->researcher->researcher_lname);
            }
            $authText = !empty($authorNames) ? implode(', ', array_slice($authorNames, 0, 2)) . (count($authorNames) > 2 ? '...' : '') : '';

            $meta = array_filter([
                $type ? "ประเภท: {$type}" : '', 
                $dept, 
                $authText ? "ผู้ประพันธ์: {$authText}" : ''
            ]);
            $sub = !empty($meta) ? ' [' . implode(', ', $meta) . ']' : '';

            $cleanName = strip_tags($p->pub_name);
            $results[] = [
                'value' => $p->id,
                'text' => $yearPrefix . $cleanName . $sub,
                'pub_name' => $cleanName,
                'pubtype' => $type,
                'author' => $authText,
            ];
        }

        return response()->json($results);
    }

    public function show($id)
    {
        $item = RdbPublished::with([
            'year', 
            'researcher', 
            'researcher.prefix',
            'department', 
            'pubtype', 
            'branch',
            'project',
            'project.type',
            'project.typeSub',
            'project.department',
            'project.rdbProjectWorks.researcher.prefix',
            'authors',
            'authors.prefix',
            'authors.department'
        ])->findOrFail($id);
        
        // Load author types manually
        $authorTypeIds = $item->authors->pluck('pivot.pubta_id')->filter()->unique();
        if ($authorTypeIds->isNotEmpty()) {
            $authorTypes = RdbPublishedTypeAuthor::whereIn('pubta_id', $authorTypeIds)->get()->keyBy('pubta_id');
            foreach ($item->authors as $author) {
                if ($author->pivot && $author->pivot->pubta_id && isset($authorTypes[$author->pivot->pubta_id])) {
                    $author->authorType = $authorTypes[$author->pivot->pubta_id];
                }
            }
        }
        
        return view('frontend.rdbpublished.show', compact('item'));
    }

    public function viewFile($id)
    {
        $item = RdbPublished::findOrFail($id);

        if ($item->data_show != 1) {
            abort(404);
        }

        $filePath = $item->getPublishedFilePath();

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $item->increment('pub_download');
            return Storage::disk('public')->response($filePath);
        }

        if ($filePath && file_exists(public_path($filePath))) {
            $item->increment('pub_download');
            return response()->file(public_path($filePath));
        }

        if ($filePath && file_exists(public_path('storage/' . $filePath))) {
            $item->increment('pub_download');
            return response()->file(public_path('storage/' . $filePath));
        }

        // Support legacy folder: datapublished/{id}/{pub_file}
        if (!empty($item->pub_file)) {
            $legacyRelative = 'datapublished/' . $item->id . '/' . $item->pub_file;
            if (Storage::disk('public')->exists($legacyRelative)) {
                $item->increment('pub_download');
                return Storage::disk('public')->response($legacyRelative);
            } elseif (file_exists(public_path($legacyRelative))) {
                $item->increment('pub_download');
                return response()->file(public_path($legacyRelative));
            }
        }

        abort(404, 'ไม่พบไฟล์เอกสารบนเซิร์ฟเวอร์ (ยังไม่ได้นำไฟล์จริงมาติดตั้งในเครื่องนี้)');
    }

    public function export(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $items = $query->orderBy('pub_date', 'desc')->orderBy('id', 'desc')->get();

        $filename = "published_works_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $sanitize = function ($val) {
            if ($val === null) return '';
            $str = trim(strip_tags((string) $val));
            if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                return "'" . $str;
            }
            return $str;
        };

        $callback = function() use ($items, $sanitize) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, ['ID', 'ชื่อผลงาน', 'วารสาร/แหล่งเผยแพร่', 'ปี พ.ศ.', 'ประเภทการตีพิมพ์', 'คณะผู้แต่ง', 'หน่วยงาน', 'วันที่ตีพิมพ์', 'งบประมาณ']);

            foreach ($items as $item) {
                $authorList = [];
                if ($item->authors && $item->authors->isNotEmpty()) {
                    foreach ($item->authors as $author) {
                        $prefix = $author->prefix->prefix_nameTH ?? '';
                        $authorList[] = trim($prefix . $author->researcher_fname . ' ' . $author->researcher_lname);
                    }
                } elseif ($item->researcher) {
                    $prefix = $item->researcher->prefix->prefix_nameTH ?? '';
                    $authorList[] = trim($prefix . $item->researcher->researcher_fname . ' ' . $item->researcher->researcher_lname);
                }

                $pubTypeStr = '-';
                if ($item->pubtype) {
                    $pubTypeStr = trim(($item->pubtype->pubtype_group ?? '') . ($item->pubtype->pubtype_subgroup ? ' - ' . $item->pubtype->pubtype_subgroup : ''));
                }

                fputcsv($file, [
                    $item->id,
                    $sanitize($item->pub_name),
                    $sanitize($item->pub_name_journal),
                    $sanitize($item->year->year_name ?? '-'),
                    $sanitize($pubTypeStr ?: '-'),
                    $sanitize(!empty($authorList) ? implode(', ', $authorList) : '-'),
                    $sanitize($item->department->department_nameTH ?? '-'),
                    $sanitize($item->pub_date ?? '-'),
                    $item->pub_budget ?? 0
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

