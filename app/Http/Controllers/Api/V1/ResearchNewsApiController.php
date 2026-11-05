<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ResearchNewsResource;
use App\Models\ResearchNews;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResearchNewsApiController extends Controller
{
    /**
     * Display a listing of public research news.
     *
     * GET /api/v1/news
     * Supported filters:
     * - q / search: Search in title and details
     * - type / news_type: Filter by news category
     * - date / year: Filter by date or year
     * - sort_by: id, news_date, news_count, created_at (default: created_at)
     * - sort_order: asc, desc (default: desc)
     * - per_page / limit / n: Items per page (default: 20, max: 100)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ResearchNews::query()
            ->where(function ($q) {
                $q->where('data_show', 1)->orWhereNull('data_show');
            });

        // Keyword Search
        $search = $request->query('search', $request->query('q'));
        if ($search !== null && $search !== '') {
            $search = trim($search);
            $query->where(function ($sq) use ($search) {
                $sq->where('news_name', 'like', "%{$search}%")
                   ->orWhere('news_detail', 'like', "%{$search}%");
            });
        }

        // News Type Filter
        $type = $request->query('type', $request->query('news_type'));
        if ($type !== null && $type !== '') {
            $query->where('news_type', trim($type));
        }

        // Date / Year Filter
        $date = $request->query('date', $request->query('year'));
        if ($date !== null && $date !== '') {
            $date = trim($date);
            $query->where(function ($dq) use ($date) {
                $dq->where('news_date', 'like', "{$date}%")
                   ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') like ?", ["{$date}%"]);
            });
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = strtolower($request->query('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'news_date', 'news_count', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderByDesc('created_at');
        }

        // Pagination / Limit
        $limitParam = $request->query('per_page', $request->query('limit', $request->query('n', 20)));
        $perPage = min(100, max(1, (int) $limitParam));

        $news = $query->paginate($perPage)->withQueryString();

        return ResearchNewsResource::collection($news);
    }

    /**
     * Display the specified research news item.
     *
     * GET /api/v1/news/{id}
     */
    public function show(string $id): ResearchNewsResource
    {
        $news = ResearchNews::query()
            ->where(function ($q) {
                $q->where('data_show', 1)->orWhereNull('data_show');
            })
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('news_id', $id);
            })
            ->firstOrFail();

        // Increment view count safely
        try {
            $news->increment('news_count');
        } catch (\Throwable $e) {
            // Non-blocking
        }

        return new ResearchNewsResource($news);
    }
}
