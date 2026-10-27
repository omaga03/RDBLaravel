<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LegacyApiController extends Controller
{
    /**
     * Legacy API: jsonprojectall
     * Supports:
     * - Master data (all active projects)
     * - ?dep={department_id}
     * - ?year={fiscal_year_th} (e.g. 2567, 2569)
     * - ?dep={id}&year={year}
     * - Graceful fallback for concatenated query strings (e.g. dep=2year=2567)
     */
    public function jsonprojectall(Request $request): JsonResponse
    {
        $dep = $request->query('dep');
        $year = $request->query('year');

        // Graceful handling of malformed queries like ?dep=2year=2567
        if (!$year && $dep && preg_match('/^(\d+)year=(\d+)$/i', (string) $dep, $matches)) {
            $dep = $matches[1];
            $year = $matches[2];
        }

        $query = RdbProject::with([
            'year',
            'department',
            'type',
            'typeSub',
            'status',
            'group',
            'rdbProjectWorks' => function ($q) {
                $q->orderBy('position_id', 'asc')->orderBy('ratio', 'desc');
            },
            'rdbProjectWorks.researcher.prefix',
            'rdbProjectWorks.position'
        ])
        ->where('data_show', 1)
        ->whereNotIn('ps_id', [6, 8]);

        if (!empty($dep)) {
            $query->where('department_id', trim($dep));
        }

        if (!empty($year)) {
            $yearVal = trim($year);
            $query->whereHas('year', function ($yq) use ($yearVal) {
                $yq->where('year_name', $yearVal);
            });
        }

        if ($request->filled('pmis')) {
            $pmis = trim($request->query('pmis'));
            $query->whereHas('rdbProjectWorks.researcher', function ($rq) use ($pmis) {
                $rq->where('tea_code', $pmis);
            });
        }

        $projects = $query->orderByDesc('pro_id')->get();

        $result = [];
        foreach ($projects as $p) {
            $researchTeam = [];
            if ($p->rdbProjectWorks) {
                foreach ($p->rdbProjectWorks as $pw) {
                    $r = $pw->researcher;
                    $fullName = $r ? trim("{$r->researcher_fname} {$r->researcher_lname}") : '';
                    $researchTeam[] = [
                        'pmis' => (string) ($r?->tea_code ?? ''),
                        'full_name' => $fullName,
                        'contribution_percentage' => (string) ($pw->ratio ?? '0'),
                        'project_role' => (string) ($pw->position?->position_nameTH ?? ''),
                    ];
                }
            }

            $fundingSource = $p->group?->pgroup_nameTH ?? $p->type?->pt_name ?? 'โครงการวิจัยเดี่ยว';
            $fundingType = $p->type?->pt_name ?? '';
            $fundingSubType = $p->typeSub?->pts_name ?? $fundingType;

            $result[] = [
                'id' => (string) $p->pro_id,
                'project_code' => (string) ($p->pro_code ?? ''),
                'fiscal_year' => (string) ($p->year?->year_name ?? ''),
                'budget_amount' => number_format((float) $p->pro_budget, 2, '.', ''),
                'status_label' => (string) ($p->status?->ps_name ?? ''),
                'title_th' => (string) ($p->pro_nameTH ?? ''),
                'title_en' => (string) ($p->pro_nameEN ?? ''),
                'funding_source' => (string) $fundingSource,
                'funding_type' => (string) $fundingType,
                'department_name' => (string) ($p->department?->department_nameTH ?? ''),
                'abstract' => (string) ($p->pro_abstract ?? ''),
                'keywords' => (string) ($p->pro_keyword ?? ''),
                'start_date' => $p->pro_date_start ? date('Y-m-d', strtotime($p->pro_date_start)) : null,
                'end_date' => $p->pro_date_end ? date('Y-m-d', strtotime($p->pro_date_end)) : null,
                'finish_date' => $p->pro_finish ? date('Y-m-d', strtotime($p->pro_finish)) : null,
                'remarks' => (string) ($p->pro_comment ?? $p->pro_note ?? ''),
                'funding_sub_type' => (string) $fundingSubType,
                'research_team' => $researchTeam,
            ];
        }

        return response()->json($result);
    }

    /**
     * Legacy API: jsonpublisheddataall
     * Supports:
     * - Master data (all active publications)
     * - ?dep={department_id}
     * - ?year={publication_year_th} (e.g. 2568, 2570)
     * - ?dep={id}&year={year}
     */
    public function jsonpublisheddataall(Request $request): JsonResponse
    {
        $dep = $request->query('dep');
        $year = $request->query('year');

        if (!$year && $dep && preg_match('/^(\d+)year=(\d+)$/i', (string) $dep, $matches)) {
            $dep = $matches[1];
            $year = $matches[2];
        }

        $query = RdbPublished::with([
            'year',
            'department',
            'pubtype',
            'project.year',
            'project.type',
        ])
        ->where('data_show', 1);

        if (!empty($dep)) {
            $query->where('department_id', trim($dep));
        }

        if (!empty($year)) {
            $yearVal = trim($year);
            $query->where(function ($yq) use ($yearVal) {
                $yq->where('year_bud', $yearVal)
                   ->orWhere('year_edu', $yearVal)
                   ->orWhere('year_id', $yearVal)
                   ->orWhereHas('year', function ($sub) use ($yearVal) {
                       $sub->where('year_name', $yearVal);
                   });
            });
        }

        if ($request->filled('pmis')) {
            $pmis = trim($request->query('pmis'));
            $query->whereHas('authors', function ($aq) use ($pmis) {
                $aq->where('tea_code', $pmis);
            });
        }

        $publications = $query->orderByDesc('id')->get();

        // Preload all author works for these publications in one single query to prevent N+1
        $pubIds = $publications->pluck('id')->filter()->values();
        $authorWorksByPub = [];
        if ($pubIds->isNotEmpty()) {
            $authorWorks = DB::table('rdb_published_work as pw')
                ->leftJoin('rdb_researcher as res', 'pw.researcher_id', '=', 'res.researcher_id')
                ->leftJoin('rdb_department as d', 'res.department_id', '=', 'd.department_id')
                ->leftJoin('rdb_published_type_author as ta', 'pw.pubta_id', '=', 'ta.pubta_id')
                ->whereIn('pw.published_id', $pubIds)
                ->select([
                    'pw.published_id',
                    'pw.researcher_id',
                    'res.tea_code',
                    'res.researcher_fname',
                    'res.researcher_lname',
                    'd.department_nameTH',
                    'ta.pubta_nameTH',
                    'pw.pubw_main',
                    'pw.pubw_bud'
                ])
                ->orderBy('pw.pubw_bud', 'desc')
                ->orderBy('pw.pubw_main', 'desc')
                ->orderBy('pw.pubta_id', 'asc')
                ->get();

            foreach ($authorWorks as $aw) {
                $authorWorksByPub[$aw->published_id][] = [
                    'publication_id' => (string) $aw->published_id,
                    'researcher_id' => (string) $aw->researcher_id,
                    'pmis' => (string) ($aw->tea_code ?? ''),
                    'full_name' => trim("{$aw->researcher_fname} {$aw->researcher_lname}"),
                    'department_name' => (string) ($aw->department_nameTH ?? ''),
                    'author_role' => (string) ($aw->pubta_nameTH ?? ''),
                    'is_main_affiliation' => (string) ($aw->pubw_main ?? '0'),
                    'allocated_budget' => (string) ($aw->pubw_bud ?? '0'),
                ];
            }
        }

        $result = [];
        foreach ($publications as $pub) {
            $projectDetails = null;
            if ($pub->project) {
                $projectDetails = [
                    'id' => (string) $pub->project->pro_id,
                    'fiscal_year' => (string) ($pub->project->year?->year_name ?? ''),
                    'funding_type' => (string) ($pub->project->type?->pt_name ?? ''),
                    'title_th' => (string) ($pub->project->pro_nameTH ?? ''),
                    'budget_amount' => number_format((float) $pub->project->pro_budget, 2, '.', ''),
                    'remarks' => (string) ($pub->project->pro_comment ?? $pub->project->pro_note ?? ''),
                ];
            }

            $pt = $pub->pubtype;
            $pubTypeSubgroup = '';
            if ($pt) {
                $groupStr = array_filter([$pt->pubtype_group, $pt->pubtype_grouptype]);
                $suffix = !empty($groupStr) ? ' (' . implode(' - ', $groupStr) . ')' : '';
                $pubTypeSubgroup = ($pt->pubtype_subgroup ?? '') . $suffix;
            }

            $yearName = (string) ($pub->year?->year_name ?? $pub->year_bud ?? $pub->year_edu ?? '');

            $result[] = [
                'id' => (string) $pub->id,
                'project_id' => (string) ($pub->pro_id ?? ''),
                'project_details' => $projectDetails,
                'year_name' => $yearName,
                'pubtypesubgroup' => $pubTypeSubgroup,
                'publication_title' => (string) ($pub->pub_name ?? ''),
                'publication_abstract' => (string) ($pub->pub_abstract ?? ''),
                'publication_keywords' => (string) ($pub->pub_keyword ?? ''),
                'authors' => $authorWorksByPub[$pub->id] ?? [],
                'department_id' => (string) ($pub->department_id ?? ''),
                'department_nameTH' => (string) ($pub->department?->department_nameTH ?? ''),
                'journal_source' => (string) ($pub->pub_name_journal ?? ''),
                'issue_date_start' => $pub->pub_date ? date('Y-m-d', strtotime($pub->pub_date)) : '',
                'issue_date_end' => $pub->pub_date_end ? date('Y-m-d', strtotime($pub->pub_date_end)) : '',
                'quality_score' => number_format((float) ($pub->pub_score ?? 0), 2, '.', ''),
                'internal_note' => (string) ($pub->pub_note ?? ''),
                'abstract' => (string) ($pub->pub_abstract ?? ''),
                'keywords' => (string) ($pub->pub_keyword ?? ''),
            ];
        }

        return response()->json($result);
    }
}
