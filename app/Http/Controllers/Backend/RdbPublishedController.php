<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublished;
use Illuminate\Http\Request;

class RdbPublishedController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublished::with(['year', 'pubtype', 'researcher', 'authors']);

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
                // Exact Subtype
                $query->where('pubtype_id', $request->pubtype_id);
            } elseif ($request->filled('pubtype_grouptype')) {
                // Middle Level
                $ids = \App\Models\RdbPublishedType::where('pubtype_grouptype', $request->pubtype_grouptype)
                        ->pluck('pubtype_id');
                $query->whereIn('pubtype_id', $ids);
            } elseif ($request->filled('pubtype_group')) {
                // Top Level
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
                
                // User Request: If End Date is empty, treat as Single Day Search (Start = End)
                if (!$request->filled('date_end')) {
                    $query->whereDate('pub_date', '<=', $request->date_start);
                }
            }
            if ($request->filled('date_end')) {
                $query->whereDate('pub_date', '<=', $request->date_end);
            }

        } else {
            // Legacy
            if ($request->filled('year_id') && !$request->filled('q')) {
                $query->where('year_id', $request->year_id);
            }
        }

        $items = $query->orderBy('pub_date', 'desc')->paginate(20);
        $authorTypes = \App\Models\RdbPublishedTypeAuthor::pluck('pubta_nameTH', 'pubta_id');
        
        // Data for Filters
        $years = \App\Models\RdbYear::orderBy('year_name', 'desc')->get();
        $pubTypes = \App\Models\RdbPublishedType::all();
        $departments = \App\Models\RdbDepartment::orderBy('department_nameTH')->get();
        $branches = \App\Models\RdbPublishedBranch::orderBy('branch_name')->get();

        return view('backend.rdb_published.index', compact('items', 'authorTypes', 'years', 'pubTypes', 'departments', 'branches'));
    }

    public function create()
    {
        $pubTypes = \App\Models\RdbPublishedType::all();
        return view('backend.rdb_published.create', compact('pubTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pub_name' => 'required|string', 
            'pub_file' => 'nullable|file|mimes:pdf|max:20480',
            'pub_budget' => 'required|numeric',
            'researcher_id' => 'required|exists:rdb_researcher,researcher_id',
            'pubtype_id' => 'required|exists:rdb_published_type,pubtype_id',
            'pub_date' => 'required|date',
            'pub_score' => 'required|numeric',
        ]);

        $item = new RdbPublished();

        // Merge Abstract TH/EN
        // Merge Abstract TH/EN
        $abstractTH = $this->cleanHtml($request->pub_abstract_th ?? '');
        $abstractEN = $this->cleanHtml($request->pub_abstract_en ?? '');
        $combinedAbstract = $abstractTH . '<br><br><br><br>' . $abstractEN;
        
        // Clean HTML for Name/Journal (Remove P tags)
        $cleanName = $this->cleanHtml($request->pub_name);
        $cleanJournal = $this->cleanHtml($request->pub_name_journal);

        $request->merge([
            'pub_abstract' => $combinedAbstract,
            'pub_name' => $cleanName,
            'pub_name_journal' => $cleanJournal,
            'data_show' => 1
        ]);

        $item->fill($request->all());
        
        // 1. Auto Year Calculation
        if ($request->filled('pub_date')) {
            $date = $request->pub_date; // Y-m-d
            $carbonDate = \Carbon\Carbon::parse($date);
            $yearBE = $carbonDate->year + 543;
            
            // Default Year ID (Calendar BE)
            if (empty($item->year_id)) $item->year_id = $this->getYearId($yearBE);

            // Edu Year Lookup
            if (empty($item->year_edu)) {
                $checkEdu = \App\Models\RdbPublishedCheckyear::where('rdbyearedu_start', '<=', $date)
                                                             ->where('rdbyearedu_end', '>=', $date)
                                                             ->first();
                $item->year_edu = $checkEdu ? $checkEdu->year_id : $this->getYearId($yearBE);
            }

            // Budget Year Lookup
            if (empty($item->year_bud)) {
                $checkBud = \App\Models\RdbPublishedCheckyear::where('rdbyearbud_start', '<=', $date)
                                                             ->where('rdbyearbud_end', '>=', $date)
                                                             ->first();
               if ($checkBud) {
                   $item->year_bud = $checkBud->year_id;
               } else {
                   // Fallback: Oct 1 start
                   $budYearBE = ($carbonDate->month >= 10) ? $yearBE + 1 : $yearBE;
                   $item->year_bud = $this->getYearId($budYearBE);
               }
            }
        }

        // 2. Auto Branch & Author Pivot
        if ($request->filled('researcher_id')) {
            $researcher = \App\Models\RdbResearcher::find($request->researcher_id);
            if ($researcher) {
                // Set Branch from DepCat
                if (empty($item->branch_id)) {
                    $item->branch_id = $researcher->depcat_id; // Check schema if compatible types
                }
            }
        }

        // 3. Timestamps & User Audit
        $item->created_at = now();
        $item->updated_at = now();
        $item->user_created = auth()->id(); // Assume Auth is setup

        // item->pub_download is a counter, do not set/reset here.

        if ($request->hasFile('pub_file')) {
            $file = $request->file('pub_file');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . \Illuminate\Support\Str::random(10) . '.' . $extension;
            $destinationPath = storage_path('app/public/uploads/rdb_published');
            if (!file_exists($destinationPath)) mkdir($destinationPath, 0755, true);
            $file->move($destinationPath, $filename);
            $item->pub_file = $filename;
        }

        $item->save();

        // Sync Author Pivot (Main Researcher)
        if ($request->filled('researcher_id')) {
            $item->authors()->attach($request->researcher_id, [
                'pubw_main' => 1,
                'pubw_bud' => 1,
                // 'pubta_id' => 1 // Optional default role
            ]);
        }

        return redirect()->route('backend.rdb_published.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbPublished::findOrFail($id);
        $authorTypes = \App\Models\RdbPublishedTypeAuthor::pluck('pubta_nameTH', 'pubta_id');
        return view('backend.rdb_published.show', compact('item', 'authorTypes'));
    }

    public function edit($id)
    {
        $item = RdbPublished::findOrFail($id);
        $pubTypes = \App\Models\RdbPublishedType::all();
        return view('backend.rdb_published.edit', compact('item', 'pubTypes'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublished::findOrFail($id);
        
        $validated = $request->validate([
            'pub_name' => 'required|string',
            'pub_file' => 'nullable|file|mimes:pdf|max:20480',
            'pub_budget' => 'required|numeric',
            // 'researcher_id' => 'required', // Disabled for Edit mode as requested
            'pubtype_id' => 'required|exists:rdb_published_type,pubtype_id',
            'pub_date' => 'required|date',
            'pub_score' => 'required|numeric',
        ]);

        // Merge Abstract TH/EN
        // Merge Abstract TH/EN
        $abstractTH = $this->cleanHtml($request->pub_abstract_th ?? '');
        $abstractEN = $this->cleanHtml($request->pub_abstract_en ?? '');
        $combinedAbstract = $abstractTH . '<br><br><br><br>' . $abstractEN;
        
        // Clean HTML for Name/Journal (Remove P tags)
        $cleanName = $this->cleanHtml($request->pub_name);
        $cleanJournal = $this->cleanHtml($request->pub_name_journal);

        $request->merge([
            'pub_abstract' => $combinedAbstract,
            'pub_name' => $cleanName,
            'pub_name_journal' => $cleanJournal
        ]);

        $item->fill($request->except(['pub_file']));

        // 1. Auto Year Calculation (Update if date changed and years are empty/default?)
        // Only update logic if specifically requested or empty. 
        // User requested: "create system... waiting". Let's apply if pub_date is present.
        if ($request->filled('pub_date')) {
            $date = $request->pub_date;
            $carbonDate = \Carbon\Carbon::parse($date);
            $yearBE = $carbonDate->year + 543;

            // Overwrite or fill? User said "system determine...". 
            // Often best to overwrite if the date drives the year context.
            $item->year_id = $this->getYearId($yearBE);

            // Edu Year Lookup
            $checkEdu = \App\Models\RdbPublishedCheckyear::where('rdbyearedu_start', '<=', $date)
                                                            ->where('rdbyearedu_end', '>=', $date)
                                                            ->first();
            $item->year_edu = $checkEdu ? $checkEdu->year_id : $this->getYearId($yearBE);

            // Budget Year Lookup
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

        // 2. Auto Branch (Update)
        if ($request->filled('researcher_id')) {
            $researcher = \App\Models\RdbResearcher::find($request->researcher_id);
            if ($researcher) {
                // If branch_id is empty or we want to enforce consistency? 
                // Let's only set if empty to allow manual override, or force update? 
                // Requirement: "refer... to branch_id". Sounds like force update.
                $item->branch_id = $researcher->depcat_id;
            }
        }

        // 3. Audit
        $item->updated_at = now();
        $item->user_updated = auth()->id();

        // item->pub_download logic removed

        if ($request->hasFile('pub_file')) {
            if ($item->pub_file) {
                $oldPath = storage_path('app/public/uploads/rdb_published/' . $item->pub_file);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $file = $request->file('pub_file');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . \Illuminate\Support\Str::random(10) . '.' . $extension;
            $destinationPath = storage_path('app/public/uploads/rdb_published');
            if (!file_exists($destinationPath)) mkdir($destinationPath, 0755, true);
            $file->move($destinationPath, $filename);
            $item->pub_file = $filename;
        }

        $item->save();
        
        // Update Author Pivot: If researcher changed? 
        // Logic: Ensure this researcher is strictly main/bud.
        // Complex if multiple authors exist. 
        // Minimal logic: Update/Attach this researcher.
        if ($request->filled('researcher_id')) {
            // Check if exists
            $exists = $item->authors()->where('rdb_researcher.researcher_id', $request->researcher_id)->exists();
            if ($exists) {
                $item->authors()->updateExistingPivot($request->researcher_id, [
                    'pubw_main' => 1, 
                    'pubw_bud' => 1
                ]);
            } else {
                $item->authors()->attach($request->researcher_id, [
                    'pubw_main' => 1, 
                    'pubw_bud' => 1
                ]);
            }
        }

        return redirect()->route('backend.rdb_published.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    private function cleanHtml($html)
    {
        if (empty($html)) return $html;
        // Replace <p> with empty, </p> with <br>
        $html = str_replace('<p>', '', $html);
        $html = str_replace('</p>', '<br>', $html);
        // Remove trailing <br>
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
             
             // Head Researcher (First one after sorting by position)
             $head = $item->researchers->first();
             $headName = '-';
             if ($head) {
                 // $prefix = $head->prefix ? $head->prefix->prefix_nameTH : ''; // Removed as requested
                 $headName = "{$head->researcher_fname} {$head->researcher_lname}";
             }

             return [
                'id' => $item->pro_id, 
                'text' => "{$year} • {$item->pro_nameTH} • {$headName}"
            ];
        });
        
        return response()->json(['results' => $results]);
    }

    public function destroy($id)
    {
        $item = RdbPublished::findOrFail($id);
        $item->delete();

        return redirect()->route('backend.rdb_published.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
