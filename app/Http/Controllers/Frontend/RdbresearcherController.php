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
use Illuminate\Support\Facades\DB;

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
        
        // Load projects with proper sorting and relationships (exclude refunded projects ps_id = 6 and hidden items)
        $item->load(['rdbProjects' => function($query) {
            $query->with(['year', 'type', 'typeSub', 'status'])
                  ->where('rdb_project.data_show', 1)
                  ->where('rdb_project.ps_id', '<>', \App\Models\RdbProjectStatus::REFUNDED)
                  ->orderBy('rdb_project.year_id', 'desc')
                  ->orderBy('rdb_project_work.position_id', 'asc')
                  ->orderBy('rdb_project.pro_budget', 'desc')
                  ->orderBy('rdb_project.pro_id', 'desc');
        }]);
        
        // Load publications (only visible)
        $item->load(['rdbPublisheds' => function($query) use ($id) {
            $query->with([
                'project',
                'pubtype'
            ])
            ->where('rdb_published.data_show', 1)
            ->orderByRaw('YEAR(rdb_published.pub_date) desc')
            ->orderByDesc('rdb_published_work.pubw_main')
            ->orderByRaw('FIELD(COALESCE(rdb_published_work.pubta_id, 99), 1, 4, 2, 3, 5, 99)')
            ->orderBy('rdb_published.pub_date', 'desc')
            ->orderBy('rdb_published.id', 'desc');
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
        
        // Load utilizations through projects (only visible)
        $utilizations = \App\Models\RdbProjectUtilize::where('data_show', 1)
            ->whereHas('project.rdbProjectWorks', function($q) use ($id) {
                $q->where('researcher_id', $id);
            })->with(['project', 'changwat'])
            ->orderBy('utz_date', 'desc')
            ->get();
        
        // Load intellectual properties (only visible)
        $item->load(['rdbDips' => function($query) {
            $query->with(['project', 'dipType'])
                  ->where('rdb_dip.data_show', 1)
                  ->orderByRaw('YEAR(rdb_dip.dip_request_date) desc')
                  ->orderBy('rdb_dip.dipt_id', 'asc')
                  ->orderBy('rdb_dip.dip_request_date', 'desc')
                  ->orderBy('rdb_dip.dip_id', 'desc');
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

    /**
     * PMIS API: Get research projects by PMIS teacher code and fiscal year
     */
    public function datagetresearchbypmis(Request $request)
    {
        $query = DB::table('rdb_project as p')
            ->select([
                'res.researcher_id',
                'res.tea_code',
                'res.researcher_fname',
                'res.researcher_lname',
                'd.department_nameTH',
                'pos.position_id',
                'pos.position_nameTH',
                'pw.ratio',
                'p.pro_id',
                'p_count.total_researchers',
                'y.year_name',
                'p.pro_nameTH',
                'pt.pt_name',
                'pts.pts_name',
                'ps.ps_id',
                'ps.ps_name',
                'p.pro_budget',
                'p.pro_finish',
            ])
            ->join(DB::raw('(SELECT pw_sub.pro_id, COUNT(pw_sub.researcher_id) AS total_researchers FROM rdb_project_work pw_sub GROUP BY pw_sub.pro_id) AS p_count'), 'p.pro_id', '=', 'p_count.pro_id')
            ->join('rdb_project_work as pw', 'p.pro_id', '=', 'pw.pro_id')
            ->join('rdb_researcher as res', 'pw.researcher_id', '=', 'res.researcher_id')
            ->join('rdb_project_position as pos', 'pw.position_id', '=', 'pos.position_id')
            ->leftJoin('rdb_year as y', 'p.year_id', '=', 'y.year_id')
            ->leftJoin('rdb_department as d', 'res.department_id', '=', 'd.department_id')
            ->leftJoin('rdb_project_types as pt', 'p.pt_id', '=', 'pt.pt_id')
            ->leftJoin('rdb_project_types_sub as pts', 'p.pts_id', '=', 'pts.pts_id')
            ->leftJoin('rdb_project_status as ps', 'p.ps_id', '=', 'ps.ps_id')
            ->where('p.data_show', 1)
            ->whereNotIn('p.ps_id', [6, 8]);

        if ($request->filled('pmis')) {
            $query->where('res.tea_code', trim($request->query('pmis')));
        }

        if ($request->filled('year')) {
            $query->where('y.year_name', trim($request->query('year')));
        }

        $rows = $query->orderBy('y.year_name', 'desc')
                      ->orderBy('p.pro_nameTH', 'asc')
                      ->get();

        $result = [];
        foreach ($rows as $row) {
            $resId = $row->researcher_id;
            if (!isset($result[$resId])) {
                $result[$resId] = [
                    'id' => $row->researcher_id,
                    'teacher_code' => $row->tea_code,
                    'first_name' => $row->researcher_fname,
                    'last_name' => $row->researcher_lname,
                    'faculty' => $row->department_nameTH,
                    'research_list' => [],
                ];
            }

            $result[$resId]['research_list'][] = [
                'project_id' => $row->pro_id,
                'project_title' => $row->pro_nameTH,
                'role_id' => $row->position_id,
                'role_name' => $row->position_nameTH,
                'contribution_pct' => $row->ratio,
                'team_size' => $row->total_researchers,
                'fiscal_year' => $row->year_name,
                'category' => $row->pt_name,
                'sub_category' => $row->pts_name,
                'status_id' => $row->ps_id,
                'status_name' => $row->ps_name,
                'budget_amount' => (float) $row->pro_budget,
                'project_finish' => $row->pro_finish,
            ];
        }

        return response()->json(array_values($result));
    }

    /**
     * PMIS API: Get publications by PMIS teacher code and publication year
     */
    public function datagetpublicationbypmis(Request $request)
    {
        $query = DB::table('rdb_researcher as res')
            ->select([
                'res.researcher_id',
                'res.tea_code',
                'res.researcher_fname',
                'res.researcher_lname',
                'rd.department_nameTH as res_dept',
                'pub.id as publication_id',
                'pub.pub_name as publication_title',
                'y.year_name as pub_year',
                DB::raw("CONCAT(COALESCE(pt.pubtype_subgroup, ''), ' (', COALESCE(pt.pubtype_group, ''), ' - ', COALESCE(pt.pubtype_grouptype, ''), ')') AS pub_category"),
                'pub.pub_name_journal as journal_name',
                'pub.pub_score',
                'rpp.pubta_id',
                'rpp.pubta_nameTH as role_name',
                'rpw.pubw_main',
                'rpw.pubw_bud',
                'p_count.total_authors',
            ])
            ->join('rdb_published_work as rpw', 'res.researcher_id', '=', 'rpw.researcher_id')
            ->join('rdb_published as pub', 'rpw.published_id', '=', 'pub.id')
            ->leftJoin('rdb_published_type_author as rpp', 'rpw.pubta_id', '=', 'rpp.pubta_id')
            ->leftJoin('rdb_department as rd', 'res.department_id', '=', 'rd.department_id')
            ->leftJoin('rdb_year as y', 'pub.year_id', '=', 'y.year_id')
            ->leftJoin('rdb_published_type as pt', 'pub.pubtype_id', '=', 'pt.pubtype_id')
            ->join(DB::raw('(SELECT pw_sub.published_id, COUNT(pw_sub.researcher_id) AS total_authors FROM rdb_published_work pw_sub GROUP BY pw_sub.published_id) AS p_count'), 'pub.id', '=', 'p_count.published_id')
            ->where('pub.data_show', 1);

        if ($request->filled('pmis')) {
            $query->where('res.tea_code', trim($request->query('pmis')));
        }

        if ($request->filled('year')) {
            $query->where('y.year_name', trim($request->query('year')));
        }

        $rows = $query->orderBy('y.year_name', 'desc')
                      ->orderBy('pub.pub_name', 'asc')
                      ->get();

        $result = [];
        foreach ($rows as $row) {
            $resId = $row->researcher_id;
            if (!isset($result[$resId])) {
                $result[$resId] = [
                    'id' => $row->researcher_id,
                    'teacher_code' => $row->tea_code,
                    'first_name' => $row->researcher_fname,
                    'last_name' => $row->researcher_lname,
                    'faculty' => $row->res_dept,
                    'publication_list' => [],
                ];
            }

            $result[$resId]['publication_list'][] = [
                'pub_id' => $row->publication_id,
                'pub_title' => $row->publication_title,
                'pub_year' => $row->pub_year,
                'pub_category' => $row->pub_category,
                'journal_name' => $row->journal_name,
                'role_id' => $row->pubta_id,
                'role_name' => $row->role_name,
                'is_main_affiliation' => $row->pubw_main,
                'is_budget_requester' => $row->pubw_bud,
                'team_size' => $row->total_authors,
                'quality_score' => (float) $row->pub_score,
            ];
        }

        return response()->json(array_values($result));
    }
}

