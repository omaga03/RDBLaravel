<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicationResource;
use App\Models\RdbPublished;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicationController extends Controller
{
    /**
     * Display a listing of publications with flexible query filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RdbPublished::with([
            'year',
            'department',
            'pubtype',
            'project.year',
            'project.type',
            'publishedWorks.researcher.prefix',
            'publishedWorks.researcher.department',
            'publishedWorks.authorType',
        ])
        ->where('data_show', 1);

        // Filter by Department ID
        if ($request->filled('dep')) {
            $query->where('department_id', trim($request->query('dep')));
        } elseif ($request->filled('department_id')) {
            $query->where('department_id', trim($request->query('department_id')));
        }

        // Filter by Year (ID or year_name e.g. 2568)
        if ($request->filled('year')) {
            $yearVal = trim($request->query('year'));
            $query->where(function ($yq) use ($yearVal) {
                $yq->where('year_bud', $yearVal)
                   ->orWhere('year_edu', $yearVal)
                   ->orWhere('year_id', $yearVal)
                   ->orWhereHas('year', function ($sub) use ($yearVal) {
                       $sub->where('year_name', $yearVal);
                   });
            });
        }

        // Filter by PMIS code
        if ($request->filled('pmis')) {
            $pmis = trim($request->query('pmis'));
            $query->whereHas('authors', function ($aq) use ($pmis) {
                $aq->where('tea_code', $pmis);
            });
        }

        // Keyword search
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($sq) use ($search) {
                $sq->where('pub_name', 'like', "%{$search}%")
                   ->orWhere('pub_name_journal', 'like', "%{$search}%")
                   ->orWhere('pub_keyword', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = strtolower($request->query('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (in_array($sortBy, ['id', 'pub_score', 'pub_date', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderByDesc('id');
        }

        $perPage = $request->query('per_page', 20);
        if ($perPage === 'all' || (int) $perPage > 100) {
            $perPage = ($perPage === 'all') ? 500 : 100;
        }

        $publications = $query->paginate((int) $perPage)->withQueryString();

        return PublicationResource::collection($publications);
    }

    /**
     * Display the specified publication.
     */
    public function show(string $id): PublicationResource
    {
        $publication = RdbPublished::with([
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
        ->findOrFail($id);

        return new PublicationResource($publication);
    }
}
