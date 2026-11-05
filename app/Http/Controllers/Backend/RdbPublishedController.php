<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublished;
use App\Models\RdbResearcher;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\StorePublishedRequest;
use App\Http\Requests\Backend\UpdatePublishedRequest;
use App\Http\Requests\Backend\StorePublishedAuthorRequest;
use App\Http\Requests\Backend\UpdatePublishedAuthorRequest;
use App\Http\Requests\Backend\UploadPublishedFileRequest;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Storage;

use App\Traits\ExportsCsvSafely;

class RdbPublishedController extends Controller
{
    use ExportsCsvSafely;

    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    protected function getFilteredQuery(Request $request)
    {
        $query = RdbPublished::with(['year', 'pubtype', 'researcher.prefix', 'authors.prefix', 'project', 'department', 'createdBy', 'updatedBy']);

        if ($request->boolean('trash')) {
            $query->withoutGlobalScope('active')->where('data_show', 0);
        }

        // Apply RBAC Scoping:
        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->hasRole('The Creator') && !$currentUser->hasRole('Admin') && !$currentUser->hasRole('Admin Published') && !$currentUser->hasRole('Admin Research')) {
            if ($currentUser->hasRole('Published')) {
                $publishedAdminIds = \App\Models\User::whereHas('roles', function ($q) {
                    $q->where('item_name', 'Published');
                })->pluck('id');
                $query->whereIn('user_created', $publishedAdminIds);
            } elseif ($currentUser->hasRole('Faculty Coordinator')) {
                $deptId = $currentUser->getActiveRoleScope()?->department_id ?? $currentUser->researcher?->department_id;
                if ($deptId) {
                    $query->where('department_id', $deptId);
                }
            } else {
                $researcherId = $currentUser->researcher_id;
                $query->where(function($q) use ($researcherId, $currentUser) {
                    $q->where('user_created', $currentUser->id);
                    if ($researcherId) {
                        $q->orWhere('researcher_id', $researcherId)
                          ->orWhereHas('authors', function($aq) use ($researcherId) {
                              $aq->where('rdb_published_work.researcher_id', $researcherId);
                          });
                    }
                });
            }
        }

        // Workflow Status filter (e.g. Drafts & Pending)
        if ($request->filled('workflow_status')) {
            if ($request->workflow_status === 'unapproved') {
                $query->whereIn('workflow_status', ['draft', 'pending_review', 'rejected']);
            } else {
                $query->where('workflow_status', $request->workflow_status);
            }
        }

        // 1. Simple Search (Keyword 'search' or 'q')
        if ($request->filled('search') || $request->filled('q')) {
            $q = trim($request->search ?? $request->q);
            $query->where(function($sub) use ($q) {
                $sub->where('pub_name', 'like', "%{$q}%")
                    ->orWhere('pub_name_journal', 'like', "%{$q}%")
                    ->orWhere('pub_abstract', 'like', "%{$q}%")
                    ->orWhere('pub_keyword', 'like', "%{$q}%")
                    ->orWhere('pub_note', 'like', "%{$q}%")
                    ->orWhereHas('researcher', function($r) use ($q) {
                        $r->where('researcher_fname', 'like', "%{$q}%")
                          ->orWhere('researcher_lname', 'like', "%{$q}%");
                    })
                    ->orWhereHas('authors', function($a) use ($q) {
                        $a->where('researcher_fname', 'like', "%{$q}%")
                          ->orWhere('researcher_lname', 'like', "%{$q}%");
                    })
                    ->orWhereHas('project', function($p) use ($q) {
                        $p->where('pro_nameTH', 'like', "%{$q}%")
                          ->orWhere('pro_nameEN', 'like', "%{$q}%");
                    })
                    ->orWhereHas('department', function($d) use ($q) {
                        $d->where('department_nameTH', 'like', "%{$q}%");
                    })
                    ->orWhereHas('pubtype', function($t) use ($q) {
                        $t->where('pubtype_group', 'like', "%{$q}%")
                          ->orWhere('pubtype_grouptype', 'like', "%{$q}%")
                          ->orWhere('pubtype_subgroup', 'like', "%{$q}%");
                    });
            });
        }

        // 2. Advanced Search Filters
        if ($request->filled('pub_id') && $request->pub_id !== 'pre') {
            $query->where('id', $request->pub_id);
        } elseif ($request->filled('pub_name')) {
            $pubName = trim($request->pub_name);
            $query->where(function($q) use ($pubName) {
                $q->where('pub_name', 'like', "%{$pubName}%")
                  ->orWhere('pub_name_journal', 'like', "%{$pubName}%");
            });
        }

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

        // Year Filter with Year Type
        if ($request->filled('year_id')) {
            $yearCol = 'year_id'; // Default: Calendar
            if ($request->year_type == 'budget') $yearCol = 'year_bud';
            if ($request->year_type == 'education') $yearCol = 'year_edu';
            $query->where($yearCol, $request->year_id);
        }

        // Publication Type Hierarchy
        if ($request->filled('pubtype_id')) {
            $query->where('pubtype_id', $request->pubtype_id);
        } elseif ($request->filled('pubtype_grouptype')) {
            $ids = \App\Models\RdbPublishedType::where('pubtype_grouptype', $request->pubtype_grouptype)
                    ->pluck('pubtype_id');
            $query->whereIn('pubtype_id', $ids);
        } elseif ($request->filled('pubtype_group')) {
            $ids = \App\Models\RdbPublishedType::where('pubtype_group', $request->pubtype_group)
                    ->pluck('pubtype_id');
            $query->whereIn('pubtype_id', $ids);
        }

        // Researcher & Project
        if ($request->filled('researcher_id') && $request->researcher_id !== 'pre') {
            $query->where(function($q) use ($request) {
                $q->where('researcher_id', $request->researcher_id)
                  ->orWhereHas('authors', function($a) use ($request) {
                      $a->where('rdb_researcher.researcher_id', $request->researcher_id);
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
        if ($request->filled('pro_id')) {
            $query->where('pro_id', $request->pro_id);
        }

        // Department & Branch
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Score
        if ($request->filled('pub_score')) {
            $query->where('pub_score', $request->pub_score);
        }

        // Budget Range
        if ($request->filled('budget_min')) {
            $query->where('pub_budget', '>=', $request->budget_min);
        }
        if ($request->filled('budget_max')) {
            $query->where('pub_budget', '<=', $request->budget_max);
        }

        // Date Range
        if ($request->filled('date_start')) {
            $query->whereDate('pub_date', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('pub_date', '<=', $request->date_end);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbPublished::class);

        $hasDateStart = $request->filled('date_start');
        $hasDateEnd = $request->filled('date_end');
        if ($hasDateStart || $hasDateEnd) {
            if (!$hasDateStart || !$hasDateEnd) {
                return redirect()->back()->withInput()->with('error', 'กรุณาระบุทั้งวันที่เริ่มและวันที่สิ้นสุด');
            }
            if ($request->date_end < $request->date_start) {
                return redirect()->back()->withInput()->with('error', 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น');
            }
        }

        $query = $this->getFilteredQuery($request);

        $items = $query->orderBy('pub_date', 'desc')->paginate(20);
        $authorTypes = \App\Models\RdbPublishedTypeAuthor::pluck('pubta_nameTH', 'pubta_id');
        
        $years = \App\Models\RdbYear::orderBy('year_name', 'desc')->get();
        $pubTypes = \App\Models\RdbPublishedType::all();
        $departments = \App\Models\RdbDepartment::orderBy('department_nameTH')->get();
        $branches = \App\Models\RdbPublishedBranch::orderBy('branch_name')->get();

        return view('backend.rdb_published.index', compact('items', 'authorTypes', 'years', 'pubTypes', 'departments', 'branches'));
    }

    /**
     * Export publications to CSV (Excel compatible with formula injection protection)
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', RdbPublished::class);
        $query = $this->getFilteredQuery($request);

        $filename = 'publications_' . date('Y-m-d_His') . '.csv';
        $headers = $this->csvHeaders($filename);

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'ชื่อผลงานตีพิมพ์',
                'วารสาร/แหล่งตีพิมพ์',
                'ประเภทผลงาน',
                'กลุ่มประเภท',
                'นักวิจัยหลัก',
                'คณะผู้ร่วมวิจัย',
                'ปี พ.ศ.',
                'วันที่ตีพิมพ์',
                'หน่วยงาน/คณะ',
                'โครงการที่เกี่ยวข้อง',
                'ค่าน้ำหนัก (Score)',
                'งบประมาณ (บาท)'
            ]);

            $query->orderBy('id', 'desc')->chunk(250, function ($items) use ($handle) {
                foreach ($items as $item) {
                    $mainResearcher = $item->researcher ? (($item->researcher->prefix->prefix_nameTH ?? '') . $item->researcher->researcher_fname . ' ' . $item->researcher->researcher_lname) : '-';
                    $coAuthors = $item->authors->map(function ($a) {
                        return ($a->prefix->prefix_nameTH ?? '') . $a->researcher_fname . ' ' . $a->researcher_lname;
                    })->implode(', ');

                    fputcsv($handle, $this->sanitizeCsvRow([
                        $item->id,
                        strip_tags(html_entity_decode($item->pub_name ?? '', ENT_QUOTES, 'UTF-8')),
                        strip_tags(html_entity_decode($item->pub_name_journal ?? '', ENT_QUOTES, 'UTF-8')),
                        $item->pubtype->pubtype_name ?? '-',
                        $item->pubtype->pubtype_group ?? '-',
                        $mainResearcher,
                        $coAuthors ?: '-',
                        $item->year->year_name ?? '-',
                        $item->pub_date ?? '-',
                        $item->department->department_nameTH ?? '-',
                        $item->project->pro_nameTH ?? '-',
                        $item->pub_score ?? 0,
                        $item->pub_budget ?? 0,
                    ]));
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function create()
    {
        $this->authorize('create', RdbPublished::class);
        $pubTypes = \App\Models\RdbPublishedType::all();
        $authorTypes = \App\Models\RdbPublishedTypeAuthor::pluck('pubta_nameTH', 'pubta_id');
        return view('backend.rdb_published.create', compact('pubTypes', 'authorTypes'));
    }

    public function store(StorePublishedRequest $request)
    {
        $this->authorize('create', RdbPublished::class);
        
        $data = $request->validated();

        $abstractTH = $this->cleanHtml($request->pub_abstract_th ?? '');
        $abstractEN = $this->cleanHtml($request->pub_abstract_en ?? '');
        $data['pub_abstract'] = $abstractTH . '<br><br><br><br>' . $abstractEN;
        
        $data['pub_name'] = $this->cleanHtml($data['pub_name']);
        if ($request->filled('pub_name_journal')) {
            $data['pub_name_journal'] = $this->cleanHtml($request->pub_name_journal);
        }

        $data['data_show'] = 1;
        $data['user_created'] = auth()->id();
        $data['user_updated'] = auth()->id(); // It's good practice to set both on creation

        if ($request->hasFile('pub_file')) {
            $fileInfo = $this->fileUploadService->storeFile($request->file('pub_file'), 'published');
            $data['pf_safe_name'] = $fileInfo['safe_name'];
            $data['pf_path'] = $fileInfo['path'];
            $data['pf_size'] = $fileInfo['size'];
            $data['pf_mime_type'] = $fileInfo['mime_type'];
            $data['pub_file'] = $fileInfo['safe_name'];
            if (!empty($fileInfo['page_count'])) {
                $data['pub_numfile'] = $fileInfo['page_count'];
            }
            if (!empty($fileInfo['page_count'])) {
                $data['pub_numfile'] = $fileInfo['page_count'];
            }
        }
        
        $item = new RdbPublished();
        $item->fill($data);
        
        if ($request->filled('pub_date')) {
            $date = $request->pub_date;
            $carbonDate = \Carbon\Carbon::parse($date);
            $yearBE = $carbonDate->year + 543;
            
            if (empty($item->year_id)) $item->year_id = $this->getYearId($yearBE);
            if (empty($item->year_edu)) {
                $checkEdu = \App\Models\RdbPublishedCheckyear::where('rdbyearedu_start', '<=', $date)
                                                             ->where('rdbyearedu_end', '>=', $date)
                                                             ->first();
                $item->year_edu = $checkEdu ? $checkEdu->year_id : $this->getYearId($yearBE);
            }
            if (empty($item->year_bud)) {
                $checkBud = \App\Models\RdbPublishedCheckyear::where('rdbyearbud_start', '<=', $date)
                                                             ->where('rdbyearbud_end', '>=', $date)
                                                             ->first();
               $item->year_bud = $checkBud ? $checkBud->year_id : $this->getYearId(($carbonDate->month >= 10) ? $yearBE + 1 : $yearBE);
            }
        }

        if ($request->filled('researcher_id')) {
            $researcher = \App\Models\RdbResearcher::find($request->researcher_id);
            if ($researcher) {
                if (empty($item->branch_id)) $item->branch_id = $researcher->depcat_id;
                $item->department_id = $researcher->department_id;
                $item->depcou_id = $researcher->depcou_id;
            }
        }

        $item->save();

        // สร้างภาพประกอบหน้าแรก .webp หากมีไฟล์แนบ
        if (!empty($item->pub_file) || !empty($item->pf_path)) {
            try {
                app(\App\Services\PdfCoverService::class)->generatePublishedCover($item, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Generate published cover failed: ' . $e->getMessage());
            }
        }

        if ($request->filled('researcher_id')) {
            $item->authors()->attach($request->researcher_id, [
                'pubw_main' => 1,
                'pubw_bud' => $request->boolean('pubw_bud'),
                'pubta_id' => $request->pubta_id ?? 1,
                'user_created' => auth()->id(),
                'user_updated' => auth()->id(),
            ]);
            $this->updateDepartmentFromMainOffice($item);
        }

        return redirect()->route('backend.rdb_published.show', $item->id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbPublished::with(['year', 'pubtype', 'researcher', 'authors', 'project', 'department', 'branch', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->authorize('view', $item);
        
        $item->increment('pub_download');

        $authorTypes = \App\Models\RdbPublishedTypeAuthor::pluck('pubta_nameTH', 'pubta_id');
        return view('backend.rdb_published.show', compact('item', 'authorTypes'));
    }

    public function edit($id)
    {
        $item = RdbPublished::with(['year', 'pubtype', 'researcher', 'authors', 'project', 'department', 'branch', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->authorize('update', $item);
        $pubTypes = \App\Models\RdbPublishedType::all();
        return view('backend.rdb_published.edit', compact('item', 'pubTypes'));
    }

    public function update(UpdatePublishedRequest $request, $id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();

        $abstractTH = $this->cleanHtml($request->pub_abstract_th ?? '');
        $abstractEN = $this->cleanHtml($request->pub_abstract_en ?? '');
        $data['pub_abstract'] = $abstractTH . '<br><br><br><br>' . $abstractEN;

        $data['pub_name'] = $this->cleanHtml($data['pub_name']);
        if ($request->filled('pub_name_journal')) {
            $data['pub_name_journal'] = $this->cleanHtml($request->pub_name_journal);
        }

        $data['user_updated'] = auth()->id();

        if ($request->hasFile('pub_file')) {
            if ($item->pf_path && Storage::disk('public')->exists($item->pf_path)) {
                Storage::disk('public')->delete($item->pf_path);
            }

            $fileInfo = $this->fileUploadService->storeFile($request->file('pub_file'), 'published');
            $data['pf_safe_name'] = $fileInfo['safe_name'];
            $data['pf_path'] = $fileInfo['path'];
            $data['pf_size'] = $fileInfo['size'];
            $data['pf_mime_type'] = $fileInfo['mime_type'];
            $data['pub_file'] = $fileInfo['safe_name'];
            if (!empty($fileInfo['page_count'])) {
                $data['pub_numfile'] = $fileInfo['page_count'];
            }
        }

        $item->fill($data);

        if ($request->filled('pub_date')) {
            $date = $request->pub_date;
            $carbonDate = \Carbon\Carbon::parse($date);
            $yearBE = $carbonDate->year + 543;

            $item->year_id = $this->getYearId($yearBE);

            $checkEdu = \App\Models\RdbPublishedCheckyear::where('rdbyearedu_start', '<=', $date)
                                                            ->where('rdbyearedu_end', '>=', $date)
                                                            ->first();
            $item->year_edu = $checkEdu ? $checkEdu->year_id : $this->getYearId($yearBE);
            
            $checkBud = \App\Models\RdbPublishedCheckyear::where('rdbyearbud_start', '<=', $date)
                                                            ->where('rdbyearbud_end', '>=', $date)
                                                            ->first();
            if ($checkBud) {
                $item->year_bud = $checkBud->year_id;
            } else {
                $budYearBE = ($carbonDate->month >= 10) ? $yearBE + 1 : $yearBE;
                $item->year_bud = $this->getYearId($budYearBE);
            }
        }
        
        if ($request->filled('researcher_id')) {
            $this->updateDepartmentFromMainOffice($item);
        }
        
        $item->save();

        // สร้างภาพประกอบหน้าแรก .webp หากมีไฟล์แนบ
        if (!empty($item->pub_file) || !empty($item->pf_path)) {
            try {
                app(\App\Services\PdfCoverService::class)->generatePublishedCover($item, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Generate published cover failed: ' . $e->getMessage());
            }
        }
        
        return redirect()->route('backend.rdb_published.show', $item->id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    private function cleanHtml($html)
    {
        if (empty($html)) return $html;
        $html = str_replace('<p>', '', $html);
        $html = str_replace('</p>', '<br>', $html);
        while (substr($html, -4) === '<br>') {
            $html = substr($html, 0, -4);
        }
        return $html;
    }

    private function getYearId($yearName)
    {
        $year = \App\Models\RdbYear::where('year_name', $yearName)->first();
        return $year ? $year->year_id : null;
    }

    public function searchResearcher(Request $request)
    {
        $this->authorize('viewAny', RdbPublished::class);
        $rawQuery = trim($request->get('q', ''));
        
        if (mb_strlen($rawQuery) < 2) {
            return response()->json(['results' => []]);
        }

        $cleanTerm = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', $rawQuery);
        $words = array_values(array_filter(preg_split('/\s+/', $cleanTerm)));

        $query = \App\Models\RdbResearcher::with(['prefix', 'department'])
            ->where(function($sub) use ($cleanTerm, $words) {
                $sub->where('researcher_fname', 'like', "%{$cleanTerm}%")
                    ->orWhere('researcher_lname', 'like', "%{$cleanTerm}%")
                    ->orWhere('researcher_fnameEN', 'like', "%{$cleanTerm}%")
                    ->orWhere('researcher_lnameEN', 'like', "%{$cleanTerm}%")
                    ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$cleanTerm}%"]);

                if (count($words) > 1) {
                    $sub->orWhere(function($wSub) use ($words) {
                        foreach ($words as $w) {
                            $wSub->where(function($wq) use ($w) {
                                $wq->where('researcher_fname', 'like', "%{$w}%")
                                   ->orWhere('researcher_lname', 'like', "%{$w}%")
                                   ->orWhere('researcher_fnameEN', 'like', "%{$w}%")
                                   ->orWhere('researcher_lnameEN', 'like', "%{$w}%");
                            });
                        }
                    });
                }
            });
        
        $results = $query->limit(30)->get()->map(function($item) {
            $prefix = $item->prefix ? $item->prefix->prefix_nameTH : '';
            $dept = $item->department ? $item->department->department_nameTH : '-';
            return [
                'id' => $item->researcher_id,
                'value' => $item->researcher_id,
                'text' => "{$prefix}{$item->researcher_fname} {$item->researcher_lname} [{$dept}]",
                'prefix' => $prefix,
                'fname' => $item->researcher_fname,
                'lname' => $item->researcher_lname,
                'department' => $dept,
            ];
        });
        
        return response()->json(['results' => $results]);
    }

    public function searchProject(Request $request)
    {
        $this->authorize('viewAny', RdbPublished::class);
        $q = trim($request->get('q', ''));
        
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $cleanName = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', $q);
        $words = array_values(array_filter(preg_split('/\s+/', $cleanName)));

        $query = \App\Models\RdbProject::with([
            'year',
            'department',
            'rdbProjectWorks' => function($wq) {
                $wq->orderBy('position_id', 'asc');
            },
            'rdbProjectWorks.researcher.prefix',
            'rdbProjectWorks.position'
        ])->where(function($sub) use ($q, $cleanName, $words) {
            $sub->where('pro_nameTH', 'like', "%{$q}%")
                ->orWhere('pro_nameEN', 'like', "%{$q}%")
                ->orWhere('pro_code', 'like', "%{$q}%")
                ->orWhereHas('rdbProjectWorks.researcher', function($rq) use ($cleanName, $words) {
                    $rq->where('researcher_fname', 'like', "%{$cleanName}%")
                       ->orWhere('researcher_lname', 'like', "%{$cleanName}%")
                       ->orWhere('researcher_fnameEN', 'like', "%{$cleanName}%")
                       ->orWhere('researcher_lnameEN', 'like', "%{$cleanName}%")
                       ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$cleanName}%"]);

                    if (count($words) > 1) {
                        $rq->orWhere(function($wSub) use ($words) {
                            foreach ($words as $w) {
                                $wSub->where(function($wq) use ($w) {
                                    $wq->where('researcher_fname', 'like', "%{$w}%")
                                       ->orWhere('researcher_lname', 'like', "%{$w}%");
                                });
                            }
                        });
                    }
                });
        });
        
        $results = $query->orderBy('year_id', 'desc')->limit(20)->get()->map(function($item) {
            $year = $item->year ? $item->year->year_name : '-';
            $deptName = $item->department ? $item->department->department_nameTH : '';

            $researchersList = $item->rdbProjectWorks->map(function($work) {
                $r = $work->researcher;
                if (!$r) return null;
                $prefix = $r->prefix->prefix_nameTH ?? '';
                return [
                    'researcher_id' => $r->researcher_id,
                    'full_name' => trim("{$prefix}{$r->researcher_fname} {$r->researcher_lname}"),
                    'position_id' => $work->position_id,
                    'position_name' => $work->position->position_nameTH ?? 'ผู้ร่วมวิจัย',
                    'ratio' => $work->ratio,
                ];
            })->filter()->values();

            $allResearchersText = $researchersList->pluck('full_name')->implode(', ');
            $headName = $researchersList->first()['full_name'] ?? '-';
            $cleanNameTH = strip_tags($item->pro_nameTH ?? '');
            $cleanNameEN = strip_tags($item->pro_nameEN ?? '');

            return [
                'id' => $item->pro_id,
                'value' => $item->pro_id,
                'text' => "[{$year}] {$cleanNameTH} • ผู้วิจัย: " . ($allResearchersText ?: $headName),
                'pro_nameTH' => $cleanNameTH,
                'pro_nameEN' => $cleanNameEN,
                'pro_code' => $item->pro_code ?? '',
                'year_name' => $year,
                'department_name' => $deptName,
                'head_researcher' => $headName,
                'all_researchers_text' => $allResearchersText ?: '-',
                'researchers' => $researchersList->toArray(),
            ];
        });
        
        return response()->json(['results' => $results]);
    }

    // --- Author Management ---

    public function storeAuthor(StorePublishedAuthorRequest $request, $id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('update', $item);
        $validated = $request->validated();

        if ($validated['pubta_id'] == 1) {
             $exists = $item->authors()->wherePivot('pubta_id', 1)->exists();
             if ($exists) {
                 return back()->with('error', 'มีผู้เขียนหลัก (First Author) อยู่แล้ว สามารถมีได้เพียงคนเดียว');
             }
        }

        if ($request->filled('pubw_main') && $validated['pubw_main'] == 1) {
            $exists = $item->authors()->wherePivot('pubw_main', 1)->exists();
            if ($exists) {
                return back()->with('error', 'มีหน่วยงานหลัก (Main Office) อยู่แล้ว สามารถมีได้เพียงคนเดียว');
            }
        }

        if ($request->filled('pubw_bud') && $validated['pubw_bud'] == 1) {
            $exists = $item->authors()->wherePivot('pubw_bud', 1)->exists();
            if ($exists) {
                return back()->with('error', 'มีผู้ได้รับการสนับสนุนงบประมาณอยู่แล้ว สามารถมีได้เพียงคนเดียว');
            }
        }

        if ($item->authors()->where('rdb_published_work.researcher_id', $validated['researcher_id'])->exists()) {
             return back()->with('error', 'นักวิจัยท่านนี้มีชื่ออยู่ในรายการแล้ว');
        }

        $item->authors()->attach($validated['researcher_id'], [
            'pubta_id' => $validated['pubta_id'],
            'pubw_main' => $validated['pubw_main'] ?? 0,
            'pubw_bud' => $validated['pubw_bud'] ?? 0,
            'user_created' => auth()->id(),
            'user_updated' => auth()->id()
        ]);

        $this->updateDepartmentFromMainOffice($item);

        $researcher = RdbResearcher::with('prefix')->find($validated['researcher_id']);
        $authorType = \App\Models\RdbPublishedTypeAuthor::find($validated['pubta_id']);
        $researcherName = $researcher ? trim(($researcher->prefix->prefix_nameTH ?? '') . $researcher->researcher_fname . ' ' . $researcher->researcher_lname) : "ID: {$validated['researcher_id']}";
        $typeName = $authorType ? $authorType->pubta_nameTH : "ประเภท ID: {$validated['pubta_id']}";

        \App\Models\AuditLog::record(
            event: 'update',
            auditable: $item,
            oldValues: null,
            newValues: [
                'researcher_id' => $validated['researcher_id'],
                'researcher_name' => $researcherName,
                'author_type' => $typeName,
                'is_main_office' => !empty($validated['pubw_main']) ? 'ใช่' : 'ไม่ใช่',
                'is_budget_support' => !empty($validated['pubw_bud']) ? 'ใช่' : 'ไม่ใช่',
            ],
            remarks: "เพิ่มผู้แต่งผลงาน: {$researcherName} ({$typeName})",
            diffSummary: "เพิ่มผู้แต่ง: {$researcherName} ({$typeName})"
        );

        return back()->with('success', 'เพิ่มผู้แต่งเรียบร้อยแล้ว');
    }

    public function updateAuthor(UpdatePublishedAuthorRequest $request, $id, $researcher_id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('update', $item);

        $validated = $request->validated();

        if ($validated['pubta_id'] == 1) {
             $exists = $item->authors()
                            ->where('rdb_published_work.researcher_id', '!=', $researcher_id)
                            ->wherePivot('pubta_id', 1)
                            ->exists();
             if ($exists) {
                 return back()->with('error', 'มีผู้เขียนหลัก (First Author) อยู่แล้ว สามารถมีได้เพียงคนเดียว');
             }
        }

        if ($request->filled('pubw_main') && $validated['pubw_main'] == 1) {
             $exists = $item->authors()
                            ->where('rdb_published_work.researcher_id', '!=', $researcher_id)
                            ->wherePivot('pubw_main', 1)
                            ->exists();
            if ($exists) {
                return back()->with('error', 'มีหน่วยงานหลัก (Main Office) อยู่แล้ว สามารถมีได้เพียงคนเดียว');
            }
        }

        if ($request->filled('pubw_bud') && $validated['pubw_bud'] == 1) {
             $exists = $item->authors()
                            ->where('rdb_published_work.researcher_id', '!=', $researcher_id)
                            ->wherePivot('pubw_bud', 1)
                            ->exists();
            if ($exists) {
                return back()->with('error', 'มีผู้ได้รับการสนับสนุนงบประมาณอยู่แล้ว สามารถมีได้เพียงคนเดียว');
            }
        }

        $oldAuthor = $item->authors()->where('rdb_published_work.researcher_id', $researcher_id)->first();
        $oldWork = $oldAuthor?->pivot;
        $oldType = $oldWork ? \App\Models\RdbPublishedTypeAuthor::find($oldWork->pubta_id) : null;
        $newType = \App\Models\RdbPublishedTypeAuthor::find($validated['pubta_id']);
        $researcher = RdbResearcher::with('prefix')->find($researcher_id);
        $researcherName = $researcher ? trim(($researcher->prefix->prefix_nameTH ?? '') . $researcher->researcher_fname . ' ' . $researcher->researcher_lname) : "ID: {$researcher_id}";

        $item->authors()->updateExistingPivot($researcher_id, [
            'pubta_id' => $validated['pubta_id'],
            'pubw_main' => $request->input('pubw_main', 0),
            'pubw_bud' => $request->input('pubw_bud', 0),
            'user_updated' => auth()->id(),
        ]);

        $this->updateDepartmentFromMainOffice($item);

        \App\Models\AuditLog::record(
            event: 'update',
            auditable: $item,
            oldValues: [
                'author_type' => $oldType?->pubta_nameTH ?? ($oldWork?->pubta_id ?? '-'),
                'is_main_office' => !empty($oldWork?->pubw_main) ? 'ใช่' : 'ไม่ใช่',
                'is_budget_support' => !empty($oldWork?->pubw_bud) ? 'ใช่' : 'ไม่ใช่',
            ],
            newValues: [
                'author_type' => $newType?->pubta_nameTH ?? $validated['pubta_id'],
                'is_main_office' => !empty($validated['pubw_main']) ? 'ใช่' : 'ไม่ใช่',
                'is_budget_support' => !empty($validated['pubw_bud']) ? 'ใช่' : 'ไม่ใช่',
            ],
            remarks: "ปรับปรุงข้อมูลผู้แต่ง: {$researcherName}",
            diffSummary: "แก้ไขข้อมูลผู้แต่ง: {$researcherName} (เป็น " . ($newType?->pubta_nameTH ?? '') . ")"
        );

        return back()->with('success', 'ปรับปรุงข้อมูลผู้แต่งเรียบร้อยแล้ว');
    }

    public function destroyAuthor($id, $researcher_id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('update', $item);

        $oldAuthor = $item->authors()->where('rdb_published_work.researcher_id', $researcher_id)->first();
        $oldWork = $oldAuthor?->pivot;
        $oldType = $oldWork ? \App\Models\RdbPublishedTypeAuthor::find($oldWork->pubta_id) : null;
        $researcher = RdbResearcher::with('prefix')->find($researcher_id);
        $researcherName = $researcher ? trim(($researcher->prefix->prefix_nameTH ?? '') . $researcher->researcher_fname . ' ' . $researcher->researcher_lname) : "ID: {$researcher_id}";

        $item->authors()->detach($researcher_id);

        \App\Models\AuditLog::record(
            event: 'update',
            auditable: $item,
            oldValues: [
                'researcher_id' => $researcher_id,
                'researcher_name' => $researcherName,
                'author_type' => $oldType?->pubta_nameTH ?? ($oldWork?->pubta_id ?? '-'),
            ],
            newValues: null,
            remarks: "ลบผู้แต่งออกจากผลงานตีพิมพ์: {$researcherName}",
            diffSummary: "ลบผู้แต่ง: {$researcherName}"
        );

        return back()->with('success', 'ลบผู้แต่งออกจากรายการเรียบร้อยแล้ว');
    }

    public function uploadFile(UploadPublishedFileRequest $request, $id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('update', $item);

        $validated = $request->validated();

        if ($item->pf_path && Storage::disk('public')->exists($item->pf_path)) {
            Storage::disk('public')->delete($item->pf_path);
        }

        $fileInfo = $this->fileUploadService->storeFile($validated['pub_file'], 'published');

        $item->pf_safe_name = $fileInfo['safe_name'];
        $item->pf_path = $fileInfo['path'];
        $item->pf_size = $fileInfo['size'];
        $item->pf_mime_type = $fileInfo['mime_type'];
        $item->pub_file = $fileInfo['safe_name'];
        if (!empty($fileInfo['page_count'])) {
            $item->pub_numfile = $fileInfo['page_count'];
        }
        $item->user_updated = auth()->id();
        $item->save();

        // สร้างภาพประกอบหน้าแรก .webp หากมีไฟล์แนบ
        if (!empty($item->pub_file) || !empty($item->pf_path)) {
            try {
                app(\App\Services\PdfCoverService::class)->generatePublishedCover($item, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Generate published cover failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'อัปโหลดไฟล์เรียบร้อยแล้ว');
    }

    public function deleteFile($id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('update', $item);

        if ($item->pf_path && Storage::disk('public')->exists($item->pf_path)) {
            Storage::disk('public')->delete($item->pf_path);
        }

        $legacyPath = 'uploads/published/' . $item->pub_file;
        if ($item->pub_file && Storage::disk('public')->exists($legacyPath)) {
            Storage::disk('public')->delete($legacyPath);
        } elseif ($item->pub_file && file_exists(public_path($legacyPath))) {
            @unlink(public_path($legacyPath));
        }
        
        $item->pub_file = null;
        $item->pf_safe_name = null;
        $item->pf_path = null;
        $item->pf_size = null;
        $item->pf_mime_type = null;
        $item->pub_numfile = null;
        $item->user_updated = auth()->id();
        $item->save();

        // สร้างภาพประกอบหน้าแรก .webp หากมีไฟล์แนบ
        if (!empty($item->pub_file) || !empty($item->pf_path)) {
            try {
                app(\App\Services\PdfCoverService::class)->generatePublishedCover($item, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Generate published cover failed: ' . $e->getMessage());
            }
        }
        
        return back()->with('success', 'ลบไฟล์เรียบร้อยแล้ว');
    }

    public function viewFile($id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('view', $item);
        
        $filePath = $item->getPublishedFilePath();

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $item->increment('pub_download');
            return Storage::disk('public')->response($filePath);
        }

        if ($filePath && file_exists(public_path('storage/' . $filePath))) {
            $item->increment('pub_download');
            return response()->file(public_path('storage/' . $filePath));
        }

        if ($filePath && file_exists(public_path($filePath))) {
            $item->increment('pub_download');
            return response()->file(public_path($filePath));
        }

        // Support legacy yii2 datapublished
        if (!empty($item->pub_file)) {
            $legacyUploaded = 'uploads/datapublished/' . $item->id . '/' . basename($item->pub_file);
            if (Storage::disk('public')->exists($legacyUploaded)) {
                $item->increment('pub_download');
                return Storage::disk('public')->response($legacyUploaded);
            }
            $legacyPath = 'datapublished/' . $item->id . '/' . basename($item->pub_file);
            if (file_exists(public_path($legacyPath))) {
                $item->increment('pub_download');
                return response()->file(public_path($legacyPath));
            }
            if (file_exists(base_path('yii2_rdb-main/frontend/web/' . $legacyPath))) {
                $item->increment('pub_download');
                return response()->file(base_path('yii2_rdb-main/frontend/web/' . $legacyPath));
            }
        }

        abort(404, 'ไม่พบไฟล์');
    }

    public function destroy($id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('delete', $item);
        
        $item->data_show = 0;
        $item->save();

        // สร้างภาพประกอบหน้าแรก .webp หากมีไฟล์แนบ
        if (!empty($item->pub_file) || !empty($item->pf_path)) {
            try {
                app(\App\Services\PdfCoverService::class)->generatePublishedCover($item, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Generate published cover failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('backend.rdb_published.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว (Soft Delete)');
    }

    public function restore($id)
    {
        $item = RdbPublished::withoutGlobalScope('active')->findOrFail($id);
        $this->authorize('delete', $item);

        $item->data_show = 1;
        $item->user_updated = auth()->id();
        $item->save();

        // สร้างภาพประกอบหน้าแรก .webp หากมีไฟล์แนบ
        if (!empty($item->pub_file) || !empty($item->pf_path)) {
            try {
                app(\App\Services\PdfCoverService::class)->generatePublishedCover($item, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Generate published cover failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'กู้คืนข้อมูลการตีพิมพ์เรียบร้อยแล้ว');
    }

    private function updateDepartmentFromMainOffice(RdbPublished $item)
    {
        $mainOfficeAuthor = $item->authors()->wherePivot('pubw_main', 1)->first();
        
        if ($mainOfficeAuthor) {
            $item->department_id = $mainOfficeAuthor->department_id;
            $item->depcou_id = $mainOfficeAuthor->depcou_id;
            $item->branch_id = $mainOfficeAuthor->depcat_id;
            $item->save();

        // สร้างภาพประกอบหน้าแรก .webp หากมีไฟล์แนบ
        if (!empty($item->pub_file) || !empty($item->pf_path)) {
            try {
                app(\App\Services\PdfCoverService::class)->generatePublishedCover($item, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Generate published cover failed: ' . $e->getMessage());
            }
        }
        }
    }

    /**
     * Export publication to DSpace SAF ZIP package
     */
    public function exportDspace($id, \App\Services\DSpaceExportService $dspaceService)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Research'));
        $canDspace = $isSuperAdmin || ($user && ($user->hasRole(['Admin Published', 'Admin Library']) || $user->hasPermission('DSpace.Export')));
        if (!$canDspace) {
            abort(403, 'ไม่มีสิทธิ์ส่งออกแพ็กเกจ DSpace');
        }

        $published = RdbPublished::findOrFail($id);
        $this->authorize('view', $published);

        try {
            $zipPath = $dspaceService->exportPublished($published);
            if (!$zipPath || !file_exists($zipPath)) {
                return redirect()->back()->with('error', 'ไม่สามารถสร้างแพ็กเกจ DSpace สำหรับผลงานนี้ได้');
            }

            \App\Models\AuditLog::record(
                event: 'export',
                auditable: $published,
                oldValues: null,
                newValues: ['dspace_package' => basename($zipPath)],
                remarks: 'ส่งออกแพ็กเกจ DSpace สำหรับนำเข้าคลังสารสนเทศ',
                diffSummary: 'ส่งออกไฟล์ ZIP แพ็กเกจ DSpace'
            );

            return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการส่งออก DSpace: ' . $e->getMessage());
        }
    }

    /**
     * DSpace Export Index Page for Publications (Batch Mode)
     */
    public function dspaceIndex(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Research'));
        $canDspace = $isSuperAdmin || ($user && ($user->hasRole(['Admin Published', 'Admin Library']) || $user->hasPermission('DSpace.Export')));
        if (!$canDspace) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงการส่งออก DSpace (เฉพาะผู้ดูแลระบบและเจ้าหน้าที่คลังปัญญา)');
        }

        $this->authorize('viewAny', RdbPublished::class);

        $perPage = (int) $request->get('per_page', 25);
        $allowedPerPage = [1, 5, 10, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 25;
        }

        $statusFilter = $request->get('status', 'unexported'); // unexported, exported, all

        $baseQuery = RdbPublished::query()
            ->where('data_show', 1)
            ->whereNotNull('pub_file')
            ->where('pub_file', '<>', '')
            ->where('pub_file', '<>', '0');

        $totalWithFile = (clone $baseQuery)->count();
        $exportedCount = (clone $baseQuery)->where('todspace', 1)->count();
        $unexportedCount = (clone $baseQuery)->where(function ($q) {
            $q->whereNull('todspace')->orWhere('todspace', '<>', 1);
        })->count();

        $query = clone $baseQuery;
        if ($statusFilter === 'unexported') {
            $query->where(function ($q) {
                $q->whereNull('todspace')->orWhere('todspace', '<>', 1);
            });
        } elseif ($statusFilter === 'exported') {
            $query->where('todspace', 1);
        }

        $publishedIds = $query->orderBy('id', 'asc')->pluck('id')->toArray();
        $chunks = array_chunk($publishedIds, $perPage);

        return view('backend.rdb_published.dspace', compact(
            'chunks',
            'perPage',
            'allowedPerPage',
            'publishedIds',
            'statusFilter',
            'totalWithFile',
            'exportedCount',
            'unexportedCount'
        ));
    }

    /**
     * Export batch of publications to DSpace SAF ZIP package
     */
    public function exportDspaceBatch(Request $request, \App\Services\DSpaceExportService $dspaceService)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Research'));
        $canDspace = $isSuperAdmin || ($user && ($user->hasRole(['Admin Published', 'Admin Library']) || $user->hasPermission('DSpace.Export')));
        if (!$canDspace) {
            abort(403, 'ไม่มีสิทธิ์ส่งออกแพ็กเกจ DSpace');
        }

        $this->authorize('viewAny', RdbPublished::class);

        $ids = $request->input('ids');
        if (empty($ids) && $request->filled('ids_json')) {
            $ids = json_decode($request->input('ids_json'), true);
        }

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'ไม่พบรหัสผลงานตีพิมพ์ที่ต้องการส่งออก');
        }

        $partLabel = $request->input('part_label', '1');

        try {
            $zipPath = $dspaceService->exportPublishedBatch($ids, $partLabel);
            if (!$zipPath || !file_exists($zipPath)) {
                return redirect()->back()->with('error', 'ไม่สามารถสร้างไฟล์ ZIP ส่งออกได้');
            }

            return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการส่งออก DSpace: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Update DSpace export status for a batch of publications
     */
    public function updateDspaceBatchStatus(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Research'));
        $canDspace = $isSuperAdmin || ($user && ($user->hasRole(['Admin Published', 'Admin Library']) || $user->hasPermission('DSpace.Export')));
        if (!$canDspace) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ปรับปรุงสถานะ DSpace'], 403);
        }

        $ids = $request->input('ids');
        if (is_string($ids)) {
            $decoded = json_decode($ids, true);
            $ids = is_array($decoded) ? $decoded : explode(',', $ids);
        }

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'ข้อมูลรหัสไม่ถูกต้อง'], 422);
        }

        try {
            $items = RdbPublished::whereIn('id', $ids)->get();
            foreach ($items as $item) {
                $item->todspace = 1;
                $item->user_updated = auth()->id();
                $item->save();
            }

            return response()->json([
                'success' => true,
                'message' => "อัปเดตสถานะสำเร็จ จำนวน {$items->count()} รายการ",
                'updated_count' => $items->count()
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    /**
     * DSpace PDF Verification & Pre-export Check (Matching legacy exalldatatodspacecheck)
     */
    public function dspaceCheck(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Research'));
        $canDspace = $isSuperAdmin || ($user && ($user->hasRole(['Admin Published', 'Admin Library']) || $user->hasPermission('DSpace.Export')));
        if (!$canDspace) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงการตรวจสอบข้อมูล DSpace (เฉพาะผู้ดูแลระบบและเจ้าหน้าที่คลังปัญญา)');
        }

        $this->authorize('viewAny', RdbPublished::class);

        $perPage = (int) $request->get('per_page', 50);
        $allowedPerPage = [25, 50, 100];
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $baseQuery = RdbPublished::query()
            ->where('data_show', 1)
            ->whereNotNull('pub_file')
            ->where('pub_file', '<>', '')
            ->where('pub_file', '<>', '0');

        // Overall stats
        $totalFiles = (clone $baseQuery)->count();
        $countedFiles = (clone $baseQuery)->whereNotNull('pub_numfile')->where('pub_numfile', '>', 0)->count();
        $uncountedFiles = (clone $baseQuery)->where(function ($q) {
            $q->whereNull('pub_numfile')->orWhere('pub_numfile', '<=', 0);
        })->count();
        $status1Count = (clone $baseQuery)->where('todspace', 1)->count();
        $status2Count = (clone $baseQuery)->where('todspace', 2)->count();

        // Filtered Query
        $query = RdbPublished::with(['department', 'researcher.prefix'])
            ->where('data_show', 1)
            ->whereNotNull('pub_file')
            ->where('pub_file', '<>', '')
            ->where('pub_file', '<>', '0');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('pub_name', 'like', "%{$q}%")
                    ->orWhere('pub_file', 'like', "%{$q}%")
                    ->orWhere('pub_name_journal', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $st = $request->status;
            if ($st === '1') {
                $query->where('todspace', 1);
            } elseif ($st === '2') {
                $query->where('todspace', 2);
            } elseif ($st === '0' || $st === 'pending') {
                $query->where(function ($q) {
                    $q->whereNull('todspace')->orWhereNotIn('todspace', [1, 2]);
                });
            }
        }

        if ($request->filled('page_filter')) {
            $pf = $request->page_filter;
            if ($pf === 'uncounted') {
                $query->where(function ($q) {
                    $q->whereNull('pub_numfile')->orWhere('pub_numfile', '<=', 0);
                });
            } elseif ($pf === 'counted') {
                $query->whereNotNull('pub_numfile')->where('pub_numfile', '>', 0);
            } elseif ($pf === 'low') {
                $query->whereNotNull('pub_numfile')->where('pub_numfile', '>', 0)->where('pub_numfile', '<', 5);
            }
        }

        $items = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        $dspaceService = app(\App\Services\DSpaceExportService::class);
        $pdftotextBinary = $dspaceService->getPdftotextBinary();
        $hasPdftotext = !empty($pdftotextBinary);

        return view('backend.rdb_published.dspace_check', compact(
            'items',
            'perPage',
            'allowedPerPage',
            'totalFiles',
            'countedFiles',
            'uncountedFiles',
            'status1Count',
            'status2Count',
            'hasPdftotext',
            'pdftotextBinary'
        ));
    }

    /**
     * AJAX: Count PDF pages for a single publication
     */
    public function ajaxCountPdfPage(Request $request, \App\Services\DSpaceExportService $dspaceService)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Research'));
        $canDspace = $isSuperAdmin || ($user && ($user->hasRole(['Admin Published', 'Admin Library']) || $user->hasPermission('DSpace.Export')));
        if (!$canDspace) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $id = $request->input('id');
        if (empty($id)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรหัสผลงาน'], 422);
        }

        $model = RdbPublished::find($id);
        if (!$model || empty($model->pub_file)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูลหรือไม่มีไฟล์แนบ'], 404);
        }

        $diskPath = $dspaceService->resolvePublishedFileOnDisk($model);
        if (!$diskPath || !file_exists($diskPath)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบไฟล์จริงในระบบเซิร์ฟเวอร์'], 404);
        }

        try {
            $pageCount = $dspaceService->countPdfPages($diskPath);
            if ($pageCount > 0) {
                $model->pub_numfile = $pageCount;
                $model->save();

                return response()->json([
                    'success' => true,
                    'pages' => $pageCount,
                    'message' => 'บันทึกสำเร็จ'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถนับหน้าได้ (ไฟล์อาจถูกบีบอัดหรือเข้ารหัส)'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Update single publication DSpace status (1: บทความสมบูรณ์, 2: ตัวอย่างบทความ)
     */
    public function ajaxUpdateItemStatus(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Research'));
        $canDspace = $isSuperAdmin || ($user && ($user->hasRole(['Admin Published', 'Admin Library']) || $user->hasPermission('DSpace.Export')));
        if (!$canDspace) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $id = $request->input('id');
        $status = (int) $request->input('status');

        if (empty($id)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรหัสผลงาน'], 422);
        }

        if (!in_array($status, [1, 2], true)) {
            return response()->json(['success' => false, 'message' => 'สถานะไม่ถูกต้อง (ต้องเป็น 1 หรือ 2)'], 422);
        }

        $model = RdbPublished::find($id);
        if (!$model) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูล'], 404);
        }

        $model->todspace = $status;
        if ($model->save()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'status_text' => $status === 1 ? 'บทความสมบูรณ์' : 'ตัวอย่างบทความ'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'บันทึกไม่สำเร็จ'], 500);
    }

    /**
     * Regenerate PDF cover image for published article file
     */
    public function regenerateCover($id, \App\Services\PdfCoverService $coverService)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('update', $item);

        $path = $coverService->generatePublishedCover($item, true);
        if ($path) {
            return back()->with('success', 'ดึงภาพหน้าปกไฟล์ใหม่เรียบร้อยแล้ว');
        }

        return back()->with('warning', 'ไม่สามารถสร้างภาพหน้าปกได้ หรือไม่พบไฟล์เอกสารบนเซิร์ฟเวอร์');
    }
}