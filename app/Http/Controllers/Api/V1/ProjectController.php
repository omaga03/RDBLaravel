<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProjectRequest;
use App\Http\Requests\Api\V1\UpdateProjectRequest;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\RdbProject;
use App\Models\RdbProjectWork;
use App\Models\RdbYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects with flexible query filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RdbProject::with([
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
        ->where('data_show', 1);

        // Filter by Department ID
        if ($request->filled('dep')) {
            $query->where('department_id', trim($request->query('dep')));
        } elseif ($request->filled('department_id')) {
            $query->where('department_id', trim($request->query('department_id')));
        }

        // Filter by Fiscal Year (ID or year_name e.g. 2567, 2568)
        if ($request->filled('year')) {
            $yearVal = trim($request->query('year'));
            $query->where(function ($yq) use ($yearVal) {
                $yq->where('year_id', $yearVal)
                   ->orWhereHas('year', function ($sub) use ($yearVal) {
                       $sub->where('year_name', $yearVal);
                   });
            });
        }

        // Filter by PMIS code
        if ($request->filled('pmis')) {
            $pmis = trim($request->query('pmis'));
            $query->whereHas('works.researcher', function ($rq) use ($pmis) {
                $rq->where('tea_code', $pmis);
            });
        }

        // Keyword Search
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($sq) use ($search) {
                $sq->where('pro_nameTH', 'like', "%{$search}%")
                   ->orWhere('pro_nameEN', 'like', "%{$search}%")
                   ->orWhere('pro_code', 'like', "%{$search}%")
                   ->orWhere('pro_keyword', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'pro_id');
        $sortOrder = strtolower($request->query('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (in_array($sortBy, ['pro_id', 'pro_budget', 'pro_date_start', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderByDesc('pro_id');
        }

        // Pagination or Full List
        $perPage = $request->query('per_page', 20);
        if ($perPage === 'all' || (int) $perPage > 100) {
            $perPage = ($perPage === 'all') ? 500 : 100;
        }

        $projects = $query->paginate((int) $perPage)->withQueryString();

        return ProjectResource::collection($projects);
    }

    /**
     * Display the specified project.
     */
    public function show(string $id): ProjectResource
    {
        $project = RdbProject::with([
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
        ->findOrFail($id);

        return new ProjectResource($project);
    }

    /**
     * Store a newly created project with team members in storage.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $user = $request->user();
        if ($user && method_exists($user, 'tokenCan')) {
            if (!$user->tokenCan('*') && !$user->tokenCan('project:write')) {
                return response()->json(['message' => 'Token ไม่มีสิทธิ์ในการสร้างโครงการวิจัย'], 403);
            }
        }

        $data = $request->validated();

        // Resolve year_id
        $yearInput = $data['fiscal_year'];
        $yearId = null;
        if (is_numeric($yearInput)) {
            $yearRecord = RdbYear::where('year_id', $yearInput)->orWhere('year_name', $yearInput)->first();
            $yearId = $yearRecord ? $yearRecord->year_id : $yearInput;
        }

        $project = DB::transaction(function () use ($data, $yearId, $user) {
            $pro = new RdbProject();
            $pro->pro_nameTH = $data['title_th'];
            $pro->pro_nameEN = $data['title_en'] ?? null;
            $pro->year_id = $yearId;
            $pro->pro_budget = $data['budget_amount'];
            $pro->department_id = $data['department_id'];
            $pro->pt_id = $data['pt_id'] ?? null;
            $pro->pts_id = $data['pts_id'] ?? null;
            $pro->pgroup_id = $data['pgroup_id'] ?? 3; // default: โครงการวิจัยเดี่ยว
            $pro->ps_id = $data['status_id'] ?? 1;
            $pro->pro_abstract = $data['abstract'] ?? null;
            $pro->pro_keyword = $data['keywords'] ?? null;
            $pro->pro_date_start = $data['start_date'] ?? null;
            $pro->pro_date_end = $data['end_date'] ?? null;
            $pro->pro_finish = $data['finish_date'] ?? null;
            $pro->pro_note = $data['remarks'] ?? null;
            $pro->data_show = 1;
            $pro->workflow_status = 'approved';
            $pro->user_created = $user?->id ?? 1;
            $pro->user_updated = $user?->id ?? 1;
            $pro->save();

            // Insert team members into rdb_project_work
            if (!empty($data['research_team'])) {
                foreach ($data['research_team'] as $member) {
                    RdbProjectWork::create([
                        'pro_id' => $pro->pro_id,
                        'researcher_id' => $member['researcher_id'],
                        'ratio' => $member['contribution_percentage'],
                        'position_id' => $member['position_id'] ?? 1,
                        'user_created' => $user?->id ?? 1,
                        'user_updated' => $user?->id ?? 1,
                    ]);
                }
            }

            return $pro;
        });

        $project->load([
            'year',
            'department',
            'type',
            'typeSub',
            'status',
            'group',
            'works.researcher.prefix',
            'works.researcher.department',
            'works.position',
        ]);

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(UpdateProjectRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        if ($user && method_exists($user, 'tokenCan')) {
            if (!$user->tokenCan('*') && !$user->tokenCan('project:write')) {
                return response()->json(['message' => 'Token ไม่มีสิทธิ์ในการแก้ไขโครงการวิจัย'], 403);
            }
        }

        $project = RdbProject::where('data_show', 1)->findOrFail($id);
        $data = $request->validated();

        DB::transaction(function () use ($project, $data, $user) {
            if (isset($data['title_th'])) $project->pro_nameTH = $data['title_th'];
            if (isset($data['title_en'])) $project->pro_nameEN = $data['title_en'];
            if (isset($data['budget_amount'])) $project->pro_budget = $data['budget_amount'];
            if (isset($data['department_id'])) $project->department_id = $data['department_id'];
            if (isset($data['pt_id'])) $project->pt_id = $data['pt_id'];
            if (isset($data['pts_id'])) $project->pts_id = $data['pts_id'];
            if (isset($data['pgroup_id'])) $project->pgroup_id = $data['pgroup_id'];
            if (isset($data['status_id'])) $project->ps_id = $data['status_id'];
            if (isset($data['abstract'])) $project->pro_abstract = $data['abstract'];
            if (isset($data['keywords'])) $project->pro_keyword = $data['keywords'];
            if (isset($data['start_date'])) $project->pro_date_start = $data['start_date'];
            if (isset($data['end_date'])) $project->pro_date_end = $data['end_date'];
            if (isset($data['finish_date'])) $project->pro_finish = $data['finish_date'];
            if (isset($data['remarks'])) $project->pro_note = $data['remarks'];

            if (isset($data['fiscal_year'])) {
                $yearRecord = RdbYear::where('year_id', $data['fiscal_year'])
                    ->orWhere('year_name', $data['fiscal_year'])
                    ->first();
                $project->year_id = $yearRecord ? $yearRecord->year_id : $data['fiscal_year'];
            }

            $project->user_updated = $user?->id ?? $project->user_updated;
            $project->save();

            // Update research team if provided
            if (isset($data['research_team'])) {
                RdbProjectWork::where('pro_id', $project->pro_id)->delete();
                foreach ($data['research_team'] as $member) {
                    RdbProjectWork::create([
                        'pro_id' => $project->pro_id,
                        'researcher_id' => $member['researcher_id'],
                        'ratio' => $member['contribution_percentage'],
                        'position_id' => $member['position_id'] ?? 1,
                        'user_created' => $user?->id ?? 1,
                        'user_updated' => $user?->id ?? 1,
                    ]);
                }
            }
        });

        $project->load([
            'year',
            'department',
            'type',
            'typeSub',
            'status',
            'group',
            'works.researcher.prefix',
            'works.researcher.department',
            'works.position',
        ]);

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified project from storage (soft-deletion).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if ($user && method_exists($user, 'tokenCan')) {
            if (!$user->tokenCan('*') && !$user->tokenCan('rdb:admin')) {
                return response()->json(['message' => 'Token ไม่มีสิทธิ์ในการลบโครงการวิจัย (ต้องการสิทธิ์ rdb:admin)'], 403);
            }
        }

        $project = RdbProject::where('data_show', 1)->findOrFail($id);
        $project->data_show = 0;
        $project->user_updated = $user?->id ?? $project->user_updated;
        $project->save();

        return response()->json([
            'message' => 'ลบโครงการวิจัยสำเร็จ',
            'id' => (string) $project->pro_id,
        ]);
    }
}
