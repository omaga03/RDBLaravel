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
                  ->orWhere('news_detail', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_start')) {
            $query->whereDate('news_date', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('news_date', '<=', $request->date_end);
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        
        return view('frontend.researchnews.index', compact('items'));
    }

    public function show($id)
    {
        $item = ResearchNews::findOrFail($id);
        return view('frontend.researchnews.show', compact('item'));
    }


}
