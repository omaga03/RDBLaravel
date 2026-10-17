<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublished;
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

        // Apply 'manage own group data' filter for general admins (not 'The Creator')
        if (auth()->check() && auth()->user()->hasRole('Published') && !auth()->user()->hasRole('The Creator')) {
            // Get all user IDs that have the 'Published' role
            $publishedAdminIds = \App\Models\User::whereHas('roles', function ($q) {
                $q->where('item_name', 'Published');
            })->pluck('id');

            $query->whereIn('user_created', $publishedAdminIds);
        }

        // 1. Simple Search (Keyword 'q')
        if ($request->filled('q')) {
            $q = $request->q;
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
        if ($request->filled('search_mode') && $request->search_mode == 'advanced') {
            
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
            if ($request->filled('researcher_id')) {
                $query->where(function($q) use ($request) {
                    $q->where('researcher_id', $request->researcher_id)
                      ->orWhereHas('authors', function($a) use ($request) {
                          $a->where('rdb_researcher.researcher_id', $request->researcher_id);
                      });
                });
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
                
                if (!$request->filled('date_end')) {
                    $query->whereDate('pub_date', '<=', $request->date_start);
                }
            }
            if ($request->filled('date_end')) {
                $query->whereDate('pub_date', '<=', $request->date_end);
            }

        } else {
            if ($request->filled('year_id') && !$request->filled('q')) {
                $query->where('year_id', $request->year_id);
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbPublished::class);
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
            $data['pub_file'] = $fileInfo['original_name'];
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
            if ($item->pf_path && Storage::exists($item->pf_path)) {
                Storage::delete($item->pf_path);
            }

            $fileInfo = $this->fileUploadService->storeFile($request->file('pub_file'), 'published');
            $data['pf_safe_name'] = $fileInfo['safe_name'];
            $data['pf_path'] = $fileInfo['path'];
            $data['pf_size'] = $fileInfo['size'];
            $data['pf_mime_type'] = $fileInfo['mime_type'];
            $data['pub_file'] = $fileInfo['original_name'];
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
        $q = $request->get('q', '');
        
        $query = \App\Models\RdbResearcher::with(['prefix', 'department']);
        
        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('researcher_fname', 'like', "%{$q}%")
                    ->orWhere('researcher_lname', 'like', "%{$q}%");
            });
        }
        
        $results = $query->limit(30)->get()->map(function($item) {
            $prefix = $item->prefix ? $item->prefix->prefix_nameTH : '';
            $dept = $item->department ? $item->department->department_nameTH : '';
            return [
                'id' => $item->researcher_id, 
                'text' => "{$prefix}{$item->researcher_fname} {$item->researcher_lname} [{$dept}]"
            ];
        });
        
        return response()->json(['results' => $results]);
    }

    public function searchProject(Request $request)
    {
        $this->authorize('viewAny', RdbPublished::class);
        $q = $request->get('q', '');
        
        $query = \App\Models\RdbProject::with(['year', 'researchers' => function($q) {
            $q->orderBy('rdb_project_work.position_id', 'asc')
              ->with('prefix');
        }]);
        
        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('pro_nameTH', 'like', "%{$q}%")
                    ->orWhere('pro_nameEN', 'like', "%{$q}%");
            });
        }
        
        $results = $query->orderBy('year_id', 'desc')->limit(10)->get()->map(function($item) {
             $year = $item->year ? $item->year->year_name : '-';
             
             $head = $item->researchers->first();
             $headName = '-';
             if ($head) {
                 $headName = "{$head->researcher_fname} {$head->researcher_lname}";
             }

             return [
                'id' => $item->pro_id, 
                'text' => "{$year} • {$item->pro_nameTH} • {$headName}"
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

        $item->authors()->updateExistingPivot($researcher_id, [
            'pubta_id' => $validated['pubta_id'],
            'pubw_main' => $request->input('pubw_main', 0),
            'pubw_bud' => $request->input('pubw_bud', 0),
            'user_updated' => auth()->id(),
        ]);

        $this->updateDepartmentFromMainOffice($item);

        return back()->with('success', 'ปรับปรุงข้อมูลผู้แต่งเรียบร้อยแล้ว');
    }

    public function destroyAuthor($id, $researcher_id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('update', $item);
        $item->authors()->detach($researcher_id);
        return back()->with('success', 'ลบผู้แต่งออกจากรายการเรียบร้อยแล้ว');
    }

    public function uploadFile(UploadPublishedFileRequest $request, $id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('update', $item);

        $validated = $request->validated();

        if ($item->pf_path && Storage::exists($item->pf_path)) {
            Storage::delete($item->pf_path);
        }

        $fileInfo = $this->fileUploadService->storeFile($validated['pub_file'], 'published');

        $item->pf_safe_name = $fileInfo['safe_name'];
        $item->pf_path = $fileInfo['path'];
        $item->pf_size = $fileInfo['size'];
        $item->pf_mime_type = $fileInfo['mime_type'];
        $item->pub_file = $fileInfo['original_name'];
        $item->user_updated = auth()->id();
        $item->save();

        return back()->with('success', 'อัปโหลดไฟล์เรียบร้อยแล้ว');
    }

    public function deleteFile($id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('update', $item);

        if ($item->pf_path && Storage::exists($item->pf_path)) {
            Storage::delete($item->pf_path);
        }

        $legacyPath = 'uploads/published/' . $item->pub_file;
        if ($item->pub_file && Storage::exists($legacyPath)) {
            Storage::delete($legacyPath);
        }
        
        $item->pub_file = null;
        $item->pf_safe_name = null;
        $item->pf_path = null;
        $item->pf_size = null;
        $item->pf_mime_type = null;
        $item->user_updated = auth()->id();
        $item->save();
        
        return back()->with('success', 'ลบไฟล์เรียบร้อยแล้ว');
    }

    public function viewFile($id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('view', $item);
        
        $filePath = $item->getPublishedFilePath();

        if (!$filePath || !Storage::exists($filePath)) {
            abort(404, 'ไม่พบไฟล์');
        }
        
        $item->increment('pub_download');
        
        return Storage::response($filePath);
    }

    public function destroy($id)
    {
        $item = RdbPublished::findOrFail($id);
        $this->authorize('delete', $item);
        
        $item->data_show = 0;
        $item->save();

        return redirect()->route('backend.rdb_published.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว (Soft Delete)');
    }

    private function updateDepartmentFromMainOffice(RdbPublished $item)
    {
        $mainOfficeAuthor = $item->authors()->wherePivot('pubw_main', 1)->first();
        
        if ($mainOfficeAuthor) {
            $item->department_id = $mainOfficeAuthor->department_id;
            $item->depcou_id = $mainOfficeAuthor->depcou_id;
            $item->branch_id = $mainOfficeAuthor->depcat_id;
            $item->save();
        }
    }
}