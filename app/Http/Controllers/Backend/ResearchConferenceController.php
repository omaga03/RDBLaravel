<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ResearchConference;
use Illuminate\Http\Request;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Backend\StoreResearchConferenceRequest;
use App\Http\Requests\Backend\UpdateResearchConferenceRequest;

class ResearchConferenceController extends Controller
{
    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    public function index(Request $request)
    {
        $this->authorize('viewAny', ResearchConference::class);
        $query = ResearchConference::query()->where('data_show', 1);

        if ($request->filled('con_name')) {
            $query->where('con_name', 'like', '%' . $request->con_name . '%');
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.research_conference.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', ResearchConference::class);
        return view('backend.research_conference.create');
    }

    public function store(StoreResearchConferenceRequest $request)
    {
        $this->authorize('create', ResearchConference::class);
        $data = $request->validated();
        
        $item = new ResearchConference();
        
        $data['con_name'] = $this->cleanHtml($data['con_name']);
        if (isset($data['con_detail'])) {
            $data['con_detail'] = $this->cleanHtml($data['con_detail']);
        }
        
        if (empty($data['con_id'])) {
            $lastConId = ResearchConference::max('con_id');
            if ($lastConId && strlen($lastConId) >= 18) {
                $data['con_id'] = bcadd($lastConId, '1');
            } else {
                $data['con_id'] = '100000000000000000';
            }
        }
        
        if ($request->hasFile('con_img')) {
            $fileInfo = $this->fileUploadService->storeFile($request->file('con_img'), 'conference');
            $data['con_safe_name'] = $fileInfo['safe_name'];
            $data['con_path'] = $fileInfo['path'];
            $data['con_size'] = $fileInfo['size'];
            $data['con_mime_type'] = $fileInfo['mime_type'];
            $data['con_img'] = $request->file('con_img')->getClientOriginalName();
        }

        $item->fill($data);
        $item->data_show = 1;
        $item->con_count = 0;
        $item->save();
        
        return redirect()->route('backend.research_conference.show', $item->id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = ResearchConference::findOrFail($id);
        $this->authorize('view', $item);
        $item->increment('con_count');
        return view('backend.research_conference.show', compact('item'));
    }

    public function edit($id)
    {
        $item = ResearchConference::findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.research_conference.edit', compact('item'));
    }

    public function update(UpdateResearchConferenceRequest $request, $id)
    {
        $item = ResearchConference::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();
        
        $data['con_name'] = $this->cleanHtml($data['con_name']);
        if (isset($data['con_detail'])) {
            $data['con_detail'] = $this->cleanHtml($data['con_detail']);
        }

        if ($request->hasFile('con_img')) {
            if ($item->con_path && Storage::exists($item->con_path)) {
                Storage::delete($item->con_path);
            }
            
            $fileInfo = $this->fileUploadService->storeFile($request->file('con_img'), 'conference');
            
            $data['con_safe_name'] = $fileInfo['safe_name'];
            $data['con_path'] = $fileInfo['path'];
            $data['con_size'] = $fileInfo['size'];
            $data['con_mime_type'] = $fileInfo['mime_type'];
            $data['con_img'] = $request->file('con_img')->getClientOriginalName();
        }

        $item->fill($data);
        $item->save();
        
        return redirect()->route('backend.research_conference.show', $item->id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = ResearchConference::findOrFail($id);
        $this->authorize('delete', $item);
        $item->data_show = 0;
        $item->save();
        return redirect()->route('backend.research_conference.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }

    public function viewConferenceImage($id)
    {
        $item = ResearchConference::findOrFail($id);
        $this->authorize('view', $item);

        $filePath = $item->getConferenceFilePath();

        if (!$filePath || !Storage::exists($filePath)) {
            abort(404, 'ไม่พบรูปภาพงานประชุม');
        }

        return Storage::response($filePath);
    }

    private function cleanHtml($html)
    {
        if (empty($html)) return $html;
        
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        $html = str_replace('<p>', '', $html);
        $html = str_replace('</p>', '<br>', $html);
        while (substr($html, -4) === '<br>') {
            $html = substr($html, 0, -4);
        }
        return $html;
    }
}