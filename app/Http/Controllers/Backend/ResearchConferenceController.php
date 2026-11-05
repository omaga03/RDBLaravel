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
        $query = ResearchConference::query()->where('data_show', $request->boolean('trash') ? 0 : 1);

        $search = $request->input('search', $request->input('q'));
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('con_name', 'like', "%{$search}%")
                  ->orWhere('con_detail', 'like', "%{$search}%")
                  ->orWhere('con_venue', 'like', "%{$search}%");
            });
        }

        if ($request->filled('con_name')) {
            $query->where('con_name', 'like', '%' . $request->input('con_name') . '%');
        }

        if ($request->filled('con_venue')) {
            $query->where('con_venue', 'like', '%' . $request->input('con_venue') . '%');
        }

        // Advanced date range filtering
        $parseDate = function ($dateStr) {
            if (empty($dateStr)) {
                return null;
            }
            $dateStr = trim($dateStr);
            // Matches d/m/Y or d-m-Y
            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $dateStr, $m)) {
                $y = (int)$m[3] > 2400 ? (int)$m[3] - 543 : (int)$m[3];
                return sprintf('%04d-%02d-%02d', $y, (int)$m[2], (int)$m[1]);
            }
            // Matches Y-m-d or Y/m/d
            if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $dateStr, $m)) {
                $y = (int)$m[1] > 2400 ? (int)$m[1] - 543 : (int)$m[1];
                return sprintf('%04d-%02d-%02d', $y, (int)$m[2], (int)$m[3]);
            }
            return null;
        };

        $startDate = $parseDate($request->input('date_start'));
        $endDate = $parseDate($request->input('date_end'));
        $dateType = $request->input('date_type', 'created_at');

        if ($startDate && $endDate && $endDate < $startDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        if ($startDate || $endDate) {
            if ($dateType === 'event_date') {
                $query->where(function ($eq) use ($startDate, $endDate) {
                    $startYearBE = $startDate ? ((int)date('Y', strtotime($startDate)) + 543) : null;
                    $endYearBE = $endDate ? ((int)date('Y', strtotime($endDate)) + 543) : null;

                    if ($startYearBE && $endYearBE) {
                        for ($y = $startYearBE; $y <= $endYearBE; $y++) {
                            $eq->orWhere('con_even_date', 'like', "%{$y}%");
                        }
                    } elseif ($startYearBE) {
                        $eq->where('con_even_date', 'like', "%{$startYearBE}%");
                    } elseif ($endYearBE) {
                        $eq->where('con_even_date', 'like', "%{$endYearBE}%");
                    }
                });
            } else {
                if ($startDate && $endDate) {
                    $query->whereDate('created_at', '>=', $startDate)
                          ->whereDate('created_at', '<=', $endDate);
                } elseif ($startDate) {
                    $query->whereDate('created_at', '>=', $startDate);
                } elseif ($endDate) {
                    $query->whereDate('created_at', '<=', $endDate);
                }
            }
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

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
            $fileInfo = $this->fileUploadService->storeImageAsWebp($request->file('con_img'), 'conference');
            $data['con_safe_name'] = $fileInfo['safe_name'];
            $data['con_path'] = $fileInfo['path'];
            $data['con_size'] = $fileInfo['size'];
            $data['con_mime_type'] = $fileInfo['mime_type'];
            $origName = $fileInfo['original_name'];
            $data['con_img'] = mb_strlen($origName) > 30 ? (mb_substr(pathinfo($origName, PATHINFO_FILENAME), 0, 25) . '.webp') : $origName;
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
            if ($item->con_path && Storage::disk('public')->exists($item->con_path)) {
                Storage::disk('public')->delete($item->con_path);
            }
            
            $fileInfo = $this->fileUploadService->storeImageAsWebp($request->file('con_img'), 'conference');
            
            $data['con_safe_name'] = $fileInfo['safe_name'];
            $data['con_path'] = $fileInfo['path'];
            $data['con_size'] = $fileInfo['size'];
            $data['con_mime_type'] = $fileInfo['mime_type'];
            $origName = $fileInfo['original_name'];
            $data['con_img'] = mb_strlen($origName) > 30 ? (mb_substr(pathinfo($origName, PATHINFO_FILENAME), 0, 25) . '.webp') : $origName;
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
        return redirect()->route('backend.research_conference.index')->with('success', 'ย้ายข่าวงานประชุมไปที่ถังขยะเรียบร้อยแล้ว');
    }

    public function restore($id)
    {
        $item = ResearchConference::withoutGlobalScope('active')->findOrFail($id);
        $this->authorize('delete', $item);
        $item->data_show = 1;
        $item->save();
        return redirect()->back()->with('success', 'กู้คืนข่าวงานประชุมเรียบร้อยแล้ว');
    }

    public function forceDelete($id)
    {
        $item = ResearchConference::withoutGlobalScope('active')->findOrFail($id);
        $this->authorize('forceDelete', $item);

        if ($item->con_path && Storage::disk('public')->exists($item->con_path)) {
            Storage::disk('public')->delete($item->con_path);
        }

        $item->delete();
        return redirect()->back()->with('success', 'ลบข่าวงานประชุมอย่างถาวรเรียบร้อยแล้ว');
    }

    public function testSync(Request $request, \App\Services\FacebookConferenceSyncService $syncService)
    {
        $this->authorize('viewAny', ResearchConference::class);

        $limit = min(15, max(1, (int) $request->input('limit', 3)));
        $fbid = $request->filled('fbid') ? trim($request->input('fbid')) : null;
        $cookie = $request->filled('cookie') ? trim($request->input('cookie')) : null;
        $testResult = null;

        if ($request->has('run')) {
            $testResult = $syncService->testSync($limit, $fbid, $cookie);
        }

        return view('backend.research_conference.test_sync', compact('testResult', 'limit', 'fbid', 'cookie'));
    }

    public function syncFacebook(Request $request, \App\Services\FacebookConferenceSyncService $syncService)
    {
        $this->authorize('create', ResearchConference::class);

        $limit = min(20, max(1, (int) $request->input('limit', 8)));
        $result = $syncService->sync($limit);

        if (!empty($result['error'])) {
            return redirect()->route('backend.research_conference.index')
                ->with('sync_report', $result)
                ->with('error', $result['error']);
        }

        $stats = $result['stats'];
        if ($stats['synced'] > 0) {
            return redirect()->route('backend.research_conference.index')
                ->with('sync_report', $result)
                ->with('success', "ดึงข้อมูลงานประชุมวิชาการจาก Facebook สำเร็จ ({$stats['synced']} รายการ, ซ้ำ {$stats['skipped']} รายการ)");
        }

        return redirect()->route('backend.research_conference.index')
            ->with('sync_report', $result)
            ->with($stats['skipped'] > 0 ? 'info' : 'warning', $stats['message'] ?: 'ไม่พบรายการใหม่จาก Facebook');
    }

    public function rss(Request $request)
    {
        $n = (int) $request->input('n', 20);
        $items = ResearchConference::query()
            ->where('data_show', 1)
            ->orderBy('created_at', 'desc')
            ->limit($n)
            ->get();

        $siteTitle = 'ศูนย์ข่าววิจัย สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์';
        $siteDescription = 'ข่าวการประชุมวิชาการ สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์';
        $siteLink = url('/');

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<rss version=\"2.0\"><channel>\n";
        $xml .= "    <title>" . htmlspecialchars($siteTitle, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</title>\n";
        $xml .= "    <link>" . htmlspecialchars($siteLink, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</link>\n";
        $xml .= "    <description>" . htmlspecialchars($siteDescription, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</description>\n";
        $xml .= "    <language>th-TH</language>\n";
        $xml .= "    <generator>PCRU RDB Research RSS Generator</generator>\n";

        foreach ($items as $row) {
            $title = $row->con_name ?: '';
            $link = route('backend.research_conference.show', $row->id);
            $imgUrl = $row->image_url ?: '';
            $pubDate = $row->created_at ? $row->created_at->format('Y-m-d') : '';
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
            $xml .= "        <newsCount>" . (int) $row->con_count . "</newsCount>\n";
            $xml .= "        <description><![CDATA[" . $description . "]]></description>\n";
            $xml .= "    </item>\n";
        }

        $xml .= "</channel></rss>";

        return response($xml, 200)->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }

    public function viewConferenceImage($id)
    {
        $item = ResearchConference::findOrFail($id);
        $this->authorize('view', $item);

        $filePath = $item->getConferenceFilePath();

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->response($filePath);
        }

        if ($filePath && file_exists(public_path($filePath))) {
            return response()->file(public_path($filePath));
        }

        abort(404, 'ไม่พบรูปภาพงานประชุม');
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