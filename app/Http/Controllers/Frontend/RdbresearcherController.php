<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbResearcher;
use App\Models\RdbPrefix;
use App\Models\RdbDepartment;
use App\Models\RdbResearcherStatus;
use App\Models\RdbDepMajor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RdbresearcherController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbResearcher::with(['prefix', 'department', 'status']);

        if ($request->filled('search') || $request->filled('q')) {
            $rawSearch = trim($request->search ?? $request->q);
            $cleanSearch = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', $rawSearch);
            $words = array_values(array_filter(preg_split('/\s+/', $cleanSearch)));

            $query->where(function($q) use ($rawSearch, $cleanSearch, $words) {
                $q->where('researcher_fname', 'like', "%{$cleanSearch}%")
                  ->orWhere('researcher_lname', 'like', "%{$cleanSearch}%")
                  ->orWhere('researcher_fnameEN', 'like', "%{$cleanSearch}%")
                  ->orWhere('researcher_lnameEN', 'like', "%{$cleanSearch}%")
                  ->orWhere('researcher_email', 'like', "%{$rawSearch}%")
                  ->orWhere('researcher_note', 'like', "%{$rawSearch}%")
                  ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$cleanSearch}%"]);

                if (count($words) > 1) {
                    $q->orWhere(function($sub) use ($words) {
                        foreach ($words as $word) {
                            $sub->where(function($wq) use ($word) {
                                $wq->where('researcher_fname', 'like', "%{$word}%")
                                   ->orWhere('researcher_lname', 'like', "%{$word}%")
                                   ->orWhere('researcher_fnameEN', 'like', "%{$word}%")
                                   ->orWhere('researcher_lnameEN', 'like', "%{$word}%");
                            });
                        }
                    });
                }
            });
        }

        if ($request->filled('note') || $request->filled('researcher_note')) {
            $note = trim($request->note ?? $request->researcher_note);
            $query->where(function($q) use ($note) {
                $q->where('researcher_note', 'like', "%{$note}%")
                  ->orWhere('researcher_email', 'like', "%{$note}%");
            });
        }

        if ($request->filled('department_id') || $request->filled('department')) {
            $query->where('department_id', $request->department_id ?? $request->department);
        }

        $items = $query->orderBy('researcher_id', 'desc')->paginate(12);
        
        $departments = RdbDepartment::orderBy('department_nameTH')->get();

        return view('frontend.rdbresearcher.index', compact('items', 'departments'));
    }

    public function show($id)
    {
        $item = RdbResearcher::with([
            'prefix', 
            'department',
            'status'
        ])->findOrFail($id);
        
        // Load educations
        $item->load(['rdbResearcherEducations' => function($query) {
            $query->orderBy('reedu_year', 'desc')->orderBy('reedu_id', 'desc');
        }]);
        
        // Load projects with proper sorting and relationships
        $item->load(['rdbProjects' => function($query) {
            $query->with(['year', 'type', 'typeSub', 'status'])
                  ->orderBy('year_id', 'desc')
                  ->orderBy('pro_budget', 'desc')
                  ->orderBy('pro_id', 'desc');
        }]);
        
        // Load publications
        $item->load(['rdbPublisheds' => function($query) use ($id) {
            $query->with([
                'project',
                'pubtype'
            ])
            ->orderBy('pub_date', 'desc')
            ->orderBy('id', 'desc');
        }]);
        
        // Load author types for publications manually
        $authorTypeIds = $item->rdbPublisheds->pluck('pivot.pubta_id')->filter()->unique();
        if ($authorTypeIds->isNotEmpty()) {
            $authorTypes = \App\Models\RdbPublishedTypeAuthor::whereIn('pubta_id', $authorTypeIds)->get()->keyBy('pubta_id');
            foreach ($item->rdbPublisheds as $pub) {
                if ($pub->pivot && $pub->pivot->pubta_id && isset($authorTypes[$pub->pivot->pubta_id])) {
                    $pub->authorType = $authorTypes[$pub->pivot->pubta_id];
                }
            }
        }
        
        // Load utilizations through projects
        $utilizations = \App\Models\RdbProjectUtilize::whereHas('project.rdbProjectWorks', function($q) use ($id) {
            $q->where('researcher_id', $id);
        })->with(['project', 'changwat'])
          ->orderBy('utz_date', 'desc')
          ->get();
        
        // Load intellectual properties
        $item->load(['rdbDips' => function($query) {
            $query->with(['project', 'dipType'])
                  ->orderBy('dip_request_date', 'desc')
                  ->orderBy('dip_id', 'desc');
        }]);
        
        // Load Project Positions lookup
        $projectPositions = \App\Models\RdbProjectPosition::all()->keyBy('position_id');

        return view('frontend.rdbresearcher.show', compact('item', 'utilizations', 'projectPositions'));
    }

    public function export(Request $request)
    {
        $query = RdbResearcher::with(['prefix', 'department', 'status']);

        if ($request->filled('search') || $request->filled('q')) {
            $rawSearch = trim($request->search ?? $request->q);
            $cleanSearch = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', $rawSearch);
            $query->where(function($q) use ($cleanSearch) {
                $q->where('researcher_fname', 'like', "%{$cleanSearch}%")
                  ->orWhere('researcher_lname', 'like', "%{$cleanSearch}%")
                  ->orWhere('researcher_fnameEN', 'like', "%{$cleanSearch}%")
                  ->orWhere('researcher_lnameEN', 'like', "%{$cleanSearch}%")
                  ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$cleanSearch}%"]);
            });
        }
        if ($request->filled('department_id') || $request->filled('department')) {
            $query->where('department_id', $request->department_id ?? $request->department);
        }
        if ($request->filled('note') || $request->filled('researcher_note')) {
            $note = trim($request->note ?? $request->researcher_note);
            $query->where(function($q) use ($note) {
                $q->where('researcher_note', 'like', "%{$note}%")
                  ->orWhere('researcher_email', 'like', "%{$note}%");
            });
        }

        $items = $query->orderBy('researcher_id', 'desc')->get();

        $filename = "researchers_" . date('Y-m-d_H-i-s') . ".csv";
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
            
            fputcsv($file, ['ID', 'คำนำหน้า', 'ชื่อ (TH)', 'นามสกุล (TH)', 'หน่วยงาน', 'สถานะ']);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->researcher_id,
                    $item->prefix->prefix_nameTH ?? '-',
                    $item->researcher_fname,
                    $item->researcher_lname,
                    $item->department->department_nameTH ?? '-',
                    $item->status->rst_name ?? '-'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

