<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ResearchConference;
use Illuminate\Http\Request;

class ResearchConferenceController extends Controller
{
    public function index(Request $request)
    {
        $query = ResearchConference::query()->where('data_show', 1);

        if ($request->filled('con_name')) {
            $query->where('con_name', 'like', '%' . $request->con_name . '%');
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.research_conference.index', compact('items'));
    }

    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('news');
        return view('backend.research_conference.create');
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('news');
        $validated = $request->validate([
            'con_name' => 'required',
        ]);
        
        $item = new ResearchConference();
        
        $cleanName = $this->cleanHtml($request->con_name);
        $cleanDetail = $this->cleanHtml($request->con_detail);
        
        $item->fill($request->all());
        $item->con_name = $cleanName;
        $item->con_detail = $cleanDetail;
        
        // Auto-generate con_id if not provided (18-digit string, starting at 100000000000000000)
        if (empty($request->con_id)) {
            $lastConId = ResearchConference::max('con_id');
            if ($lastConId && strlen($lastConId) >= 18) {
                $item->con_id = bcadd($lastConId, '1');
            } else {
                $item->con_id = '100000000000000000';
            }
        }
        
        // Handle Image Upload - use timestamp only (avoid long con_id in filename)
        if ($request->hasFile('con_img') && $request->file('con_img')->isValid()) {
            $file = $request->file('con_img');
            $extension = $file->extension() ?: 'jpg';
            $filename = 'conf_' . time() . '.' . $extension;
            
            // Ensure directory exists
            $destinationPath = storage_path('app/public/uploads/conference');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            // Move file directly
            $file->move($destinationPath, $filename);
            $item->con_img = $filename;
        }

        // Set created_at since timestamps are disabled
        $item->created_at = now();
        $item->data_show = 1;
        $item->con_count = 0;
        $item->save();
        
        return redirect()->route('backend.research_conference.show', $item->id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = ResearchConference::findOrFail($id);
        $item->increment('con_count');
        return view('backend.research_conference.show', compact('item'));
    }

    public function edit($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('news');
        $item = ResearchConference::findOrFail($id);
        return view('backend.research_conference.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('news');
        $item = ResearchConference::findOrFail($id);
        
        $cleanName = $this->cleanHtml($request->con_name);
        $cleanDetail = $this->cleanHtml($request->con_detail);
        
        $item->fill($request->all());
        $item->con_name = $cleanName;
        $item->con_detail = $cleanDetail;

        if ($request->hasFile('con_img')) {
             if ($item->con_img) {
                 \Illuminate\Support\Facades\Storage::delete('public/uploads/conference/' . $item->con_img);
             }
             $file = $request->file('con_img');
             $filename = time() . '_' . $file->getClientOriginalName();
             $file->storeAs('public/uploads/conference', $filename);
             $item->con_img = $filename;
        }

        $item->updated_at = now();
        $item->save();
        
        return redirect()->route('backend.research_conference.show', $item->id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('news');
        $item = ResearchConference::findOrFail($id);
        $item->data_show = 0;
        $item->save();
        return redirect()->route('backend.research_conference.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }

    private function cleanHtml($html)
    {
        if (empty($html)) return $html;
        
        // Decode HTML entities (e.g., &ldquo; &rdquo; → " ")
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        $html = str_replace('<p>', '', $html);
        $html = str_replace('</p>', '<br>', $html);
        while (substr($html, -4) === '<br>') {
            $html = substr($html, 0, -4);
        }
        return $html;
    }
}
