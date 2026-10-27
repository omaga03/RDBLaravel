<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DipResource;
use App\Models\RdbDip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DipController extends Controller
{
    /**
     * Display a listing of intellectual properties (DIP) with flexible query filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RdbDip::with([
            'dipType',
            'project',
            'researcher.prefix',
            'researcher.department',
        ])
        ->where('data_show', 1);

        // Filter by Department ID (from linked researcher or project)
        if ($request->filled('dep')) {
            $depId = trim($request->query('dep'));
            $query->where(function ($dq) use ($depId) {
                $dq->whereHas('researcher', function ($rq) use ($depId) {
                    $rq->where('department_id', $depId);
                })
                ->orWhereHas('project', function ($pq) use ($depId) {
                    $pq->where('department_id', $depId);
                });
            });
        } elseif ($request->filled('department_id')) {
            $depId = trim($request->query('department_id'));
            $query->where(function ($dq) use ($depId) {
                $dq->whereHas('researcher', function ($rq) use ($depId) {
                    $rq->where('department_id', $depId);
                })
                ->orWhereHas('project', function ($pq) use ($depId) {
                    $pq->where('department_id', $depId);
                });
            });
        }

        // Filter by IP Type (dipt_id or type_id)
        if ($request->filled('dipt_id')) {
            $query->where('dipt_id', trim($request->query('dipt_id')));
        } elseif ($request->filled('type_id')) {
            $query->where('dipt_id', trim($request->query('type_id')));
        }

        // Filter by Researcher PMIS Code
        if ($request->filled('pmis')) {
            $pmis = trim($request->query('pmis'));
            $query->whereHas('researcher', function ($rq) use ($pmis) {
                $rq->where('tea_code', $pmis);
            });
        }

        // Filter by Year
        if ($request->filled('year')) {
            $yearVal = trim($request->query('year'));
            // If year is Buddhist e.g. 2567, AD is 2024
            $adYear = (int) $yearVal > 2400 ? (int) $yearVal - 543 : (int) $yearVal;
            $beYear = (int) $yearVal > 2400 ? (int) $yearVal : (int) $yearVal + 543;

            $query->where(function ($yq) use ($adYear, $beYear) {
                $yq->whereYear('dip_request_date', $adYear)
                   ->orWhereYear('dip_data1_datestart', $adYear)
                   ->orWhereYear('dip_publication_date', $adYear)
                   ->orWhere('dip_number', 'like', "%{$beYear}%")
                   ->orWhere('dip_number', 'like', "%{$adYear}%");
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('dip_data2_status', 'like', '%' . trim($request->query('status')) . '%');
        }

        // Keyword Search
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($sq) use ($search) {
                $sq->where('dip_data2_name', 'like', "%{$search}%")
                   ->orWhere('dip_number', 'like', "%{$search}%")
                   ->orWhere('dip_request_number', 'like', "%{$search}%")
                   ->orWhere('dip_patent_number', 'like', "%{$search}%")
                   ->orWhere('dip_data2_patent', 'like', "%{$search}%")
                   ->orWhere('dip_data2_assertion', 'like', "%{$search}%")
                   ->orWhere('dip_data2_conclusion', 'like', "%{$search}%")
                   ->orWhereHas('researcher', function ($rq) use ($search) {
                       $rq->where('researcher_fname', 'like', "%{$search}%")
                          ->orWhere('researcher_lname', 'like', "%{$search}%")
                          ->orWhere('tea_code', 'like', "%{$search}%");
                   });
            });
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'dip_id');
        $sortOrder = strtolower($request->query('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (in_array($sortBy, ['dip_id', 'dip_request_date', 'dip_number', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderByDesc('dip_id');
        }

        // Pagination
        $perPage = $request->query('per_page', 20);
        if ($perPage === 'all' || (int) $perPage > 100) {
            $perPage = ($perPage === 'all') ? 500 : 100;
        }

        $items = $query->paginate((int) $perPage)->withQueryString();

        return DipResource::collection($items);
    }

    /**
     * Display the specified intellectual property record.
     */
    public function show(string $id): DipResource
    {
        $item = RdbDip::with([
            'dipType',
            'project',
            'researcher.prefix',
            'researcher.department',
        ])
        ->where('data_show', 1)
        ->findOrFail($id);

        return new DipResource($item);
    }
}
