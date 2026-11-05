<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ResearchConferenceResource;
use App\Models\ResearchConference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResearchConferenceApiController extends Controller
{
    /**
     * Display a listing of public research conferences.
     *
     * GET /api/v1/conferences
     * Supported filters:
     * - q / search: Search in title, detail, venue
     * - venue: Filter by venue
     * - deadline: Filter by deadline text/date
     * - sort_by: id, con_count, created_at (default: created_at)
     * - sort_order: asc, desc (default: desc)
     * - per_page / limit / n: Items per page (default: 20, max: 100)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ResearchConference::query()
            ->where(function ($q) {
                $q->where('data_show', 1)->orWhereNull('data_show');
            });

        // Keyword Search
        $search = $request->query('search', $request->query('q'));
        if ($search !== null && $search !== '') {
            $search = trim($search);
            $query->where(function ($sq) use ($search) {
                $sq->where('con_name', 'like', "%{$search}%")
                   ->orWhere('con_detail', 'like', "%{$search}%")
                   ->orWhere('con_venue', 'like', "%{$search}%");
            });
        }

        // Venue Filter
        if ($request->filled('venue')) {
            $query->where('con_venue', 'like', '%' . trim($request->query('venue')) . '%');
        }

        // Deadline Filter
        if ($request->filled('deadline')) {
            $query->where('con_sub_deadline', 'like', '%' . trim($request->query('deadline')) . '%');
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = strtolower($request->query('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'con_count', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderByDesc('created_at');
        }

        // Pagination / Limit
        $limitParam = $request->query('per_page', $request->query('limit', $request->query('n', 20)));
        $perPage = min(100, max(1, (int) $limitParam));

        $conferences = $query->paginate($perPage)->withQueryString();

        return ResearchConferenceResource::collection($conferences);
    }

    /**
     * Display the specified research conference item.
     *
     * GET /api/v1/conferences/{id}
     */
    public function show(string $id): ResearchConferenceResource
    {
        $conference = ResearchConference::query()
            ->where(function ($q) {
                $q->where('data_show', 1)->orWhereNull('data_show');
            })
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('con_id', $id);
            })
            ->firstOrFail();

        // Increment view count safely
        try {
            $conference->increment('con_count');
        } catch (\Throwable $e) {
            // Non-blocking
        }

        return new ResearchConferenceResource($conference);
    }
}
