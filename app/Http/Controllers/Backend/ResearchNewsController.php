<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ResearchNews;
use Illuminate\Http\Request;

class ResearchNewsController extends Controller
{
    public function index(Request $request)
    {
        $query = ResearchNews::query();

        if ($request->filled('news_name')) {
            $query->where('news_name', 'like', '%' . $request->news_name . '%');
        }

        $items = $query->orderBy('news_date', 'desc')->paginate(20);

        return view('backend.research_news.index', compact('items'));
    }

    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('News');
        return view('backend.research_news.create');
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('News');
        $validated = $request->validate([
            'news_name' => 'required',
        ]);
        
        $item = new ResearchNews();
        
        $cleanName = $this->cleanHtml($request->news_name);
        $cleanDetail = $this->cleanHtml($request->news_detail);
        
        $item->fill($request->all());
        $item->news_name = $cleanName;
        $item->news_detail = $cleanDetail;
        
        // Handle Image Upload if needed (Controller logic seems missing in original, adding basic support)
        if ($request->hasFile('news_img')) {
            $file = $request->file('news_img');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads/news', $filename);
            $item->news_img = $filename;
        }

        $item->save();
        
        return redirect()->route('backend.research_news.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = ResearchNews::findOrFail($id);
        return view('backend.research_news.show', compact('item'));
    }

    public function edit($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('News');
        $item = ResearchNews::findOrFail($id);
        return view('backend.research_news.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('News');
        $item = ResearchNews::findOrFail($id);
        
        $cleanName = $this->cleanHtml($request->news_name);
        $cleanDetail = $this->cleanHtml($request->news_detail);
        
        $item->fill($request->all());
        $item->news_name = $cleanName;
        $item->news_detail = $cleanDetail;

        if ($request->hasFile('news_img')) {
             if ($item->news_img) {
                 \Illuminate\Support\Facades\Storage::delete('public/uploads/news/' . $item->news_img);
             }
             $file = $request->file('news_img');
             $filename = time() . '_' . $file->getClientOriginalName();
             $file->storeAs('public/uploads/news', $filename);
             $item->news_img = $filename;
        }

        $item->save();
        
        return redirect()->route('backend.research_news.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('News');
        $item = ResearchNews::findOrFail($id);
        $item->delete();
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
