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
}
