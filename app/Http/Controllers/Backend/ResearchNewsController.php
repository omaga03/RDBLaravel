<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ResearchNews;
use Illuminate\Http\Request;

class ResearchNewsController extends Controller
{
    public function index(Request $request)
    {
        $query = ResearchNews::query()->where('data_show', 1);

        if ($request->filled('news_name')) {
            $query->where('news_name', 'like', '%' . $request->news_name . '%');
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.research_news.index', compact('items'));
    }

    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('news');
        return view('backend.research_news.create');
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('news');
        $validated = $request->validate([
            'news_name' => 'required',
        ]);
        
        $item = new ResearchNews();
        
        $cleanName = $this->cleanHtml($request->news_name);
        $cleanDetail = $this->cleanHtml($request->news_detail);
        
        $item->fill($request->all());
        $item->news_name = $cleanName;
        $item->news_detail = $cleanDetail;
        
        // Convert date to Thai format only if it's in standard format (not already Thai)
        if ($request->news_date && preg_match('/^\d{4}-\d{2}-\d{2}/', $request->news_date)) {
            $item->news_date = \App\Helpers\ThaiDateHelper::format($request->news_date, false, false);
        }

        
        // Auto-generate news_id if not provided (10-digit int, starting at 1000000000)
        if (empty($request->news_id)) {
            $lastNewsId = ResearchNews::max('news_id');
            if ($lastNewsId && $lastNewsId >= 1000000000) {
                $item->news_id = $lastNewsId + 1;
            } else {
                $item->news_id = 1000000000;
            }
        }
        
        // Handle Image Upload - use news_id in filename
        if ($request->hasFile('news_img') && $request->file('news_img')->isValid()) {
            $file = $request->file('news_img');
            $extension = $file->extension() ?: 'jpg';
            $newsIdForFile = $item->news_id ?: time();
            $filename = $newsIdForFile . '_' . time() . '.' . $extension;
            
            // Ensure directory exists
            $destinationPath = storage_path('app/public/uploads/news');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            // Move file directly
            $file->move($destinationPath, $filename);
            $item->news_img = $filename;
        }

        // Set created_at since timestamps are disabled
        $item->created_at = now();
        $item->data_show = 1;
        $item->save();
        
        return redirect()->route('backend.research_news.show', $item->id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = ResearchNews::findOrFail($id);
        $item->increment('news_count');
        return view('backend.research_news.show', compact('item'));
    }

    public function edit($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('news');
        $item = ResearchNews::findOrFail($id);
        return view('backend.research_news.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('news');
        $item = ResearchNews::findOrFail($id);
        
        $cleanName = $this->cleanHtml($request->news_name);
        $cleanDetail = $this->cleanHtml($request->news_detail);
        
        $item->fill($request->all());
        $item->news_name = $cleanName;
        $item->news_detail = $cleanDetail;

        // Convert date to Thai format only if it's in standard format (not already Thai)
        if ($request->news_date && preg_match('/^\d{4}-\d{2}-\d{2}/', $request->news_date)) {
            $item->news_date = \App\Helpers\ThaiDateHelper::format($request->news_date, false, false);
        }


        if ($request->hasFile('news_img')) {
             if ($item->news_img) {
                 \Illuminate\Support\Facades\Storage::delete('public/uploads/news/' . $item->news_img);
             }
             $file = $request->file('news_img');
             $filename = time() . '_' . $file->getClientOriginalName();
             $file->storeAs('public/uploads/news', $filename);
             $item->news_img = $filename;
        }

        $item->updated_at = now();
        $item->save();
        
        return redirect()->route('backend.research_news.show', $item->id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('news');
        $item = ResearchNews::findOrFail($id);
        $item->data_show = 0;
        $item->save();
        return redirect()->route('backend.research_news.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }

    private function cleanHtml($html)
    {
        if (empty($html)) return $html;
        $html = str_replace('<p>', '', $html);
        $html = str_replace('</p>', '<br>', $html);
        while (substr($html, -4) === '<br>') {
            $html = substr($html, 0, -4);
        }
        return $html;
    }
}
