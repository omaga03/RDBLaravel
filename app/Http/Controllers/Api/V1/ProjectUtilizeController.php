<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProjectUtilizeResource;
use App\Models\RdbProjectUtilize;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectUtilizeController extends Controller
{
    /**
     * Display a listing of research utilizations with flexible query filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RdbProjectUtilize::with([
            'project.department',
            'project.year',
            'changwat',
        ])
        ->where('data_show', 1);

        // Filter by Department ID (from linked project)
        if ($request->filled('dep')) {
            $depId = trim($request->query('dep'));
            $query->whereHas('project', function ($pq) use ($depId) {
                $pq->where('department_id', $depId);
            });
        } elseif ($request->filled('department_id')) {
            $depId = trim($request->query('department_id'));
            $query->whereHas('project', function ($pq) use ($depId) {
                $pq->where('department_id', $depId);
            });
        }

        // Filter by Fiscal Year (utz_year_bud or project year)
        if ($request->filled('year')) {
            $yearVal = trim($request->query('year'));
            $query->where(function ($yq) use ($yearVal) {
                $yq->where('utz_year_bud', $yearVal)
                   ->orWhere('utz_year_id', $yearVal)
                   ->orWhereHas('project', function ($pq) use ($yearVal) {
                       $pq->where('year_id', $yearVal)
                          ->orWhereHas('year', function ($sub) use ($yearVal) {
                              $sub->where('year_name', $yearVal);
                          });
                   });
            });
        }

        // Filter by Project ID
        if ($request->filled('pro_id')) {
            $query->where('pro_id', trim($request->query('pro_id')));
        }

        // Filter by Utilization Type ID (utz_group contains comma-separated IDs)
        if ($request->filled('type_id')) {
            $typeId = trim($request->query('type_id'));
            $query->whereRaw("FIND_IN_SET(?, utz_group)", [$typeId]);
        }

        // Keyword Search
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($sq) use ($search) {
                $sq->where('utz_department_name', 'like', "%{$search}%")
                   ->orWhere('utz_department_address', 'like', "%{$search}%")
                   ->orWhere('utz_leading', 'like', "%{$search}%")
                   ->orWhere('utz_detail', 'like', "%{$search}%")
                   ->orWhereHas('project', function ($pq) use ($search) {
                       $pq->where('pro_nameTH', 'like', "%{$search}%")
                          ->orWhere('pro_nameEN', 'like', "%{$search}%")
                          ->orWhere('pro_code', 'like', "%{$search}%");
                   });
            });
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'utz_id');
        $sortOrder = strtolower($request->query('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (in_array($sortBy, ['utz_id', 'utz_date', 'utz_budget', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderByDesc('utz_id');
        }

        // Pagination
        $perPage = $request->query('per_page', 20);
        if ($perPage === 'all' || (int) $perPage > 100) {
            $perPage = ($perPage === 'all') ? 500 : 100;
        }

        $items = $query->paginate((int) $perPage)->withQueryString();

        return ProjectUtilizeResource::collection($items);
    }

    /**
     * Display the specified research utilization record.
     */
    public function show(string $id): ProjectUtilizeResource
    {
        $item = RdbProjectUtilize::with([
            'project.department',
            'project.year',
            'changwat',
        ])
        ->where('data_show', 1)
        ->findOrFail($id);

        return new ProjectUtilizeResource($item);
    }
}
