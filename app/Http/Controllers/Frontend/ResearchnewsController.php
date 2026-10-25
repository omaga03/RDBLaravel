<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ResearchNews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResearchnewsController extends Controller
{
    public function index(Request $request)
    {
        $query = ResearchNews::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('news_name', 'like', "%{$search}%")
                  ->orWhere('news_detail', 'like', "%{$search}%")
                  ->orWhere('news_reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('news_type')) {
            $query->where('news_type', $request->news_type);
        }

        // Date range validation
        $hasDateStart = $request->filled('date_start');
        $hasDateEnd = $request->filled('date_end');
        
        if ($hasDateStart || $hasDateEnd) {
            if (!$hasDateStart || !$hasDateEnd) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'กรุณาระบุทั้งวันที่เริ่มและวันที่สิ้นสุด');
            }
            
            if ($request->date_end < $request->date_start) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'วันที่สิ้นสุดต้องมากกว่าหรือเท่ากับวันที่เริ่ม');
            }
            
            $query->whereDate('news_date', '>=', $request->date_start)
                  ->whereDate('news_date', '<=', $request->date_end);
        }

        $items = $query->orderBy('news_date', 'desc')->orderBy('id', 'desc')->paginate(10);
        $newsTypes = ResearchNews::distinct()->whereNotNull('news_type')->where('news_type', '!=', '')->pluck('news_type');
        
        return view('frontend.researchnews.index', compact('items', 'newsTypes'));
    }

    public function show($id)
    {
        $item = ResearchNews::findOrFail($id);
        $item->increment('news_count');
        $recentNews = ResearchNews::where('id', '!=', $item->id)->orderBy('news_date', 'desc')->orderBy('id', 'desc')->take(4)->get();
        return view('frontend.researchnews.show', compact('item', 'recentNews'));
    }

    /**
     * RSS 2.0 Feed for Research News
     */
    public function rss(Request $request)
    {
        $limit = min(50, max(1, (int) $request->input('n', 20)));

        $items = ResearchNews::where(function($q) {
                $q->where('data_show', 1)->orWhereNull('data_show');
            })
            ->orderBy('news_date', 'desc')
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();

        $siteTitle = 'ศูนย์ข่าววิจัย สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์';
        $siteDescription = 'ข่าวประกาศและกิจกรรมด้านการวิจัย สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์';
        $siteLink = url('/');
        
        $firstDate = $items->first()?->news_date;
        $lastBuildDate = now()->toRssString();
        if ($firstDate) {
            try {
                $lastBuildDate = \Carbon\Carbon::parse($firstDate)->toRssString();
            } catch (\Throwable $e) {}
        }

        $xml = view('frontend.feeds.news_rss', compact('items', 'siteTitle', 'siteDescription', 'siteLink', 'lastBuildDate'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=utf-8'
        ]);
    }
}
