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
        $result = $syncService->sync($limit);

        if (!empty($result['error'])) {
            return redirect()->route('backend.research_news.index')
                ->with('error', $result['error']);
        }

        $stats = $result['stats'];
        return redirect()->route('backend.research_news.index')
            ->with('sync_report', $result)
            ->with('success', "ดึงข้อมูลข่าวทุนวิจัยจาก NRIIS เรียบร้อยแล้ว (เพิ่มใหม่ {$stats['synced']} รายการ, ซ้ำ/ข้าม {$stats['skipped']} รายการ)");
    }

    public function rss(Request $request)
    {
        $n = (int) $request->input('n', 20);
        $items = ResearchNews::query()
            ->where('data_show', 1)
            ->orderBy('created_at', 'desc')
            ->limit($n)
            ->get();

        $siteTitle = 'ศูนย์ข่าววิจัย สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์';
        $siteDescription = 'ข่าวประกาศและกิจกรรมด้านการวิจัย สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์';
        $siteLink = url('/');

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<rss version=\"2.0\"><channel>\n";
        $xml .= "    <title>" . htmlspecialchars($siteTitle, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</title>\n";
        $xml .= "    <link>" . htmlspecialchars($siteLink, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</link>\n";
        $xml .= "    <description>" . htmlspecialchars($siteDescription, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</description>\n";
        $xml .= "    <language>th-TH</language>\n";
        $xml .= "    <generator>PCRU RDB Research RSS Generator</generator>\n";

        foreach ($items as $row) {
            $title = $row->news_name ?: '';
            $link = route('backend.research_news.show', $row->id);
            $imgUrl = $row->image_url ?: '';
            $pubDate = $row->news_date;
            $description = $title;
            if (!empty($imgUrl)) {
                $description = '<p><img src="' . htmlspecialchars($imgUrl, ENT_QUOTES | ENT_XML1, 'UTF-8') . '" alt="' . htmlspecialchars($title, ENT_QUOTES | ENT_XML1, 'UTF-8') . '"></p><p>' . htmlspecialchars($title, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</p>';
            }

            $xml .= "    <item>\n";
            $xml .= "        <title>" . htmlspecialchars($title, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</title>\n";
            $xml .= "        <link>" . htmlspecialchars($link, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</link>\n";
            $xml .= "        <guid isPermaLink=\"true\">" . htmlspecialchars($link, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</guid>\n";
            $xml .= "        <imgUrl>" . htmlspecialchars($imgUrl, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</imgUrl>\n";
            $xml .= "        <pubDate>" . htmlspecialchars((string) $pubDate, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</pubDate>\n";
            $xml .= "        <newsCount>" . (int) $row->news_count . "</newsCount>\n";
            $xml .= "        <description><![CDATA[" . $description . "]]></description>\n";
            $xml .= "    </item>\n";
        }

        $xml .= "</channel></rss>";

        return response($xml, 200)->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }

    public function monthlyReport(Request $request)
    {
        $this->authorize('viewAny', ResearchNews::class);
        $month = $request->input('month', now()->subMonth()->format('Y-m'));

        $nriis = ResearchNews::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])->get();
        $conferences = \App\Models\ResearchConference::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])->get();

        return view('backend.research_news.reports', compact('nriis', 'conferences', 'month'));
    }

    public function viewNewsImage($id)
    {
        $item = ResearchNews::findOrFail($id);
        $this->authorize('view', $item);

        $filePath = $item->getNewsFilePath();

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->response($filePath);
        }

        if ($filePath && file_exists(public_path($filePath))) {
            return response()->file(public_path($filePath));
        }

        abort(404, 'ไม่พบรูปภาพข่าว');
    }
}