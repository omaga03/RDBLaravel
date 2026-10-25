<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ResearchNews;
use Illuminate\Http\Request;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Backend\StoreResearchNewsRequest;
use App\Http\Requests\Backend\UpdateResearchNewsRequest;

class ResearchNewsController extends Controller
{
    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ResearchNews::class);
        $query = ResearchNews::query()->where('data_show', $request->boolean('trash') ? 0 : 1);

        $search = $request->input('search', $request->input('q', $request->input('news_name')));
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('news_name', 'like', "%{$search}%")
                  ->orWhere('news_detail', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('backend.research_news.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', ResearchNews::class);
        return view('backend.research_news.create');
    }

    public function store(StoreResearchNewsRequest $request)
    {
        $this->authorize('create', ResearchNews::class);
        $validated = $request->validated();
        
        $item = new ResearchNews();
        
        if ($request->hasFile('news_img')) {
            $fileInfo = $this->fileUploadService->storeFile($validated['news_img'], 'news');
            $item->news_safe_name = $fileInfo['safe_name'];
            $item->news_path = $fileInfo['path'];
            $item->news_size = $fileInfo['size'];
            $item->news_mime_type = $fileInfo['mime_type'];
            $validated['news_img'] = $fileInfo['original_name'];
        } else {
            unset($validated['news_img']);
        }

        $item->fill($validated);
        $item->data_show = 1;
        $item->news_count = 0;
        $item->save();
        
        return redirect()->route('backend.research_news.show', $item->id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = ResearchNews::findOrFail($id);
        $this->authorize('view', $item);
        $item->increment('news_count');
        return view('backend.research_news.show', compact('item'));
    }

    public function edit($id)
    {
        $item = ResearchNews::findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.research_news.edit', compact('item'));
    }

    public function update(UpdateResearchNewsRequest $request, $id)
    {
        $item = ResearchNews::findOrFail($id);
        $this->authorize('update', $item);
        
        $validated = $request->validated();
        
        if ($request->hasFile('news_img')) {
            if ($item->news_path && Storage::disk('public')->exists($item->news_path)) {
                Storage::disk('public')->delete($item->news_path);
            }
            
            $fileInfo = $this->fileUploadService->storeFile($validated['news_img'], 'news');
            
            $item->news_safe_name = $fileInfo['safe_name'];
            $item->news_path = $fileInfo['path'];
            $item->news_size = $fileInfo['size'];
            $item->news_mime_type = $fileInfo['mime_type'];
            $validated['news_img'] = $fileInfo['original_name'];
        } else {
            unset($validated['news_img']);
        }

        $item->fill($validated);
        $item->save();
        
        return redirect()->route('backend.research_news.show', $item->id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = ResearchNews::findOrFail($id);
        $this->authorize('delete', $item);
        $item->data_show = 0;
        $item->save();
        return redirect()->route('backend.research_news.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }

    public function restore($id)
    {
        $item = ResearchNews::withoutGlobalScope('active')->findOrFail($id);
        $this->authorize('delete', $item);
        $item->data_show = 1;
        $item->save();
        return redirect()->back()->with('success', 'กู้คืนข่าวเรียบร้อยแล้ว');
    }

    public function syncNriis(Request $request, \App\Services\NriisSyncService $syncService)
    {
        $this->authorize('create', ResearchNews::class);

        $limit = min(50, max(5, (int) $request->input('limit', 20)));
        $stats = $syncService->sync($limit);

        return redirect()->route('backend.research_news.index')
            ->with('success', "ดึงข้อมูลข่าวทุนวิจัยจาก NRIIS เรียบร้อยแล้ว (เพิ่มใหม่ {$stats['synced']} รายการ, ซ้ำ/ข้าม {$stats['skipped']} รายการ)");
    }

    public function viewNewsImage($id)
    {
        $item = ResearchNews::findOrFail($id);
        $this->authorize('view', $item);

        $filePath = $item->getNewsFilePath(oldField: 'news_img', oldDir: 'uploads/news');

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->response($filePath);
        }

        if ($filePath && file_exists(public_path($filePath))) {
            return response()->file(public_path($filePath));
        }

        abort(404, 'ไม่พบรูปภาพข่าว');
    }
}