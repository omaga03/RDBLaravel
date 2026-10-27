<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Http\Resources\Api\V1\PublicationResource;
use App\Http\Resources\Api\V1\ResearcherPortfolioResource;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\RdbResearcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResearcherController extends Controller
{
    /**
     * Get researcher profile by PMIS code or ID.
     */
    public function show(string $id): JsonResponse
    {
        $researcher = RdbResearcher::with(['department', 'prefix', 'status'])
            ->where(function ($q) use ($id) {
                $q->where('tea_code', $id)
                  ->orWhere('researcher_id', $id);
            })
            ->firstOrFail();

        return response()->json([
            'data' => new ResearcherPortfolioResource($researcher),
        ]);
    }

    /**
     * Get combined portfolio (projects + publications) by PMIS code.
     */
    public function portfolio(Request $request, string $pmis): JsonResponse
    {
        $researcher = RdbResearcher::with(['department', 'prefix', 'status'])
            ->where('tea_code', $pmis)
            ->first();

        if (!$researcher) {
            return response()->json(['message' => 'ไม่พบข้อมูลนักวิจัยที่มีรหัส PMIS นี้'], 404);
        }

        $year = $request->query('year');

        // Fetch projects
        $projectQuery = RdbProject::with([
            'year',
            'department',
            'type',
            'typeSub',
            'status',
            'group',
            'works.researcher.prefix',
            'works.researcher.department',
            'works.position',
        ])
        ->where('data_show', 1)
        ->whereHas('researchers', function ($q) use ($pmis) {
            $q->where('tea_code', $pmis);
        });

        if (!empty($year)) {
            $projectQuery->whereHas('year', function ($yq) use ($year) {
                $yq->where('year_name', $year);
            });
        }

        $projects = $projectQuery->orderByDesc('pro_id')->get();

        // Fetch publications
        $pubQuery = RdbPublished::with([
            'year',
            'department',
            'pubtype',
            'project.year',
            'project.type',
            'publishedWorks.researcher.prefix',
            'publishedWorks.researcher.department',
            'publishedWorks.authorType',
        ])
        ->where('data_show', 1)
        ->whereHas('publishedWorks.researcher', function ($aq) use ($pmis) {
            $aq->where('tea_code', $pmis);
        });

        if (!empty($year)) {
            $pubQuery->where(function ($yq) use ($year) {
                $yq->where('year_bud', $year)
                   ->orWhere('year_edu', $year)
                   ->orWhereHas('year', function ($sub) use ($year) {
                       $sub->where('year_name', $year);
                   });
            });
        }

        $publications = $pubQuery->orderByDesc('id')->get();

        return response()->json([
            'researcher' => new ResearcherPortfolioResource($researcher),
            'projects_count' => $projects->count(),
            'publications_count' => $publications->count(),
            'projects' => ProjectResource::collection($projects),
            'publications' => PublicationResource::collection($publications),
        ]);
    }
}
