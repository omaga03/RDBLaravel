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
        $query = ResearchNews::query()->where('data_show', 1);

        if ($request->filled('news_name')) {
            $query->where('news_name', 'like', '%' . $request->news_name . '%');
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(20);

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