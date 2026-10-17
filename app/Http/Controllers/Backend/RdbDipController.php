<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDip;
use App\Models\RdbDipType;
use App\Models\RdbYear;
use App\Models\RdbResearcher;
use Illuminate\Http\Request;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Backend\StoreDipRequest;
use App\Http\Requests\Backend\UpdateDipRequest;
use App\Http\Requests\Backend\UploadDipFileRequest;

use App\Traits\ExportsCsvSafely;

class RdbDipController extends Controller
{
    use ExportsCsvSafely;

    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    protected function getFilteredQuery(Request $request)
    {
        $query = RdbDip::with(['dipType', 'researcher.prefix', 'project']);

        // Filter: Dip Request Number
        if ($request->filled('dip_request_number')) {
            $query->where('dip_request_number', 'like', '%' . $request->dip_request_number . '%');
        }

        // Filter: Dip Name (Invention Name)
        if ($request->filled('dip_name')) {
            $query->where('dip_data2_name', 'like', '%' . $request->dip_name . '%');
        }

        // Filter: Dip Type
        if ($request->filled('dipt_id')) {
            $query->where('dipt_id', $request->dipt_id);
        }

        // Filter: Researcher (Inventor)
        if ($request->filled('researcher_id')) {
            $query->where('researcher_id', $request->researcher_id);
        }

        // Filter: Date Range (dip_request_date)
        if ($request->filled('start_date')) {
            $query->whereDate('dip_request_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('dip_request_date', '<=', $request->end_date);
        }

        // Filter: Year (via linked project or dip_request_date)
        if ($request->filled('year_id')) {
            $yearObj = RdbYear::find($request->year_id);
            if ($yearObj) {
                $ceYear = (int)$yearObj->year_name - 543;
                $query->where(function($q) use ($request, $ceYear) {
                    $q->whereHas('project', function($pq) use ($request) {
                        $pq->where('year_id', $request->year_id);
                    })->orWhereYear('dip_request_date', $ceYear);
                });
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbDip::class);
        $query = $this->getFilteredQuery($request);

        $items = $query->orderBy('dip_request_date', 'desc')->paginate(20);

        // Lookup data for filters
        $dipTypes = RdbDipType::all();
        
        $selectedResearcher = null;
        if ($request->filled('researcher_id')) {
            $selectedResearcher = RdbResearcher::find($request->researcher_id);
        }

        $selectedYear = null;
        if ($request->filled('year_id')) {
            $selectedYear = RdbYear::find($request->year_id);
        }

        return view('backend.rdb_dip.index', compact('items', 'dipTypes', 'selectedResearcher', 'selectedYear'));
    }

    /**
     * Export IP records to CSV (Excel compatible with formula injection protection)
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', RdbDip::class);
        $query = $this->getFilteredQuery($request);

        $filename = 'intellectual_property_' . date('Y-m-d_His') . '.csv';
        $headers = $this->csvHeaders($filename);

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'เลขที่คำขอ',
                'วันที่ยื่นคำขอ',
                'ชื่อผลงาน/การประดิษฐ์',
                'ประเภททรัพย์สินทางปัญญา',
                'ผู้ประดิษฐ์/นักวิจัย',
                'เลขที่สิทธิบัตร/อนุสิทธิบัตร',
                'วันที่ประกาศโฆษณา',
                'โครงการที่เกี่ยวข้อง',
                'สถานะ'
            ]);

            $query->orderBy('dip_id', 'desc')->chunk(250, function ($items) use ($handle) {
                foreach ($items as $item) {
                    $inventor = $item->researcher ? (($item->researcher->prefix->prefix_nameTH ?? '') . $item->researcher->researcher_fname . ' ' . $item->researcher->researcher_lname) : '-';

                    fputcsv($handle, $this->sanitizeCsvRow([
                        $item->dip_id,
                        $item->dip_request_number ?? '-',
                        $item->dip_request_date ? $item->dip_request_date->format('Y-m-d') : '-',
                        strip_tags(html_entity_decode($item->dip_data2_name ?? '', ENT_QUOTES, 'UTF-8')),
                        $item->dipType->dipt_name ?? '-',
                        $inventor,
                        $item->dip_patent_number ?? ($item->dip_number ?? '-'),
                        $item->dip_publication_date ?? '-',
                        $item->project->pro_nameTH ?? '-',
                        $item->dip_data2_status ?? '-'
                    ]));
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * AJAX: Search Years for IP
     */
    public function searchYears()
    {
        $this->authorize('viewAny', RdbDip::class);
        $years = RdbYear::orderBy('year_name', 'desc')
            ->get()
            ->map(fn($y) => [
                'value' => $y->year_id,
                'text' => 'พ.ศ. ' . $y->year_name
            ]);

        return response()->json($years);
    }

    public function create()
    {
        $this->authorize('create', RdbDip::class);
        return view('backend.rdb_dip.create');
    }

    public function store(StoreDipRequest $request)
    {
        $this->authorize('create', RdbDip::class);
        
        $data = $request->validated();

        $data['dip_type'] = 1; 
        $data['data_show'] = 1;
        $data['user_created'] = auth()->id();
        $data['user_updated'] = auth()->id();

        if ($request->hasFile('dip_files')) {
            $fileInfo = $this->fileUploadService->storeFile($request->file('dip_files'), 'dips');
            $data['dip_safe_name'] = $fileInfo['safe_name'];
            $data['dip_path'] = $fileInfo['path'];
            $data['dip_size'] = $fileInfo['size'];
            $data['dip_mime_type'] = $fileInfo['mime_type'];
            $data['dip_files'] = $fileInfo['safe_name'];
        }

        if ($request->hasFile('dip_data1_files')) {
            $fileInfo = $this->fileUploadService->storeFile($request->file('dip_data1_files'), 'dips');
            $data['dip_data1_files'] = $fileInfo['safe_name'];
        }
        
        $item = RdbDip::create($data);

        return redirect()->route('backend.rdb_dip.show', $item->dip_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbDip::with(['dipType', 'researcher', 'project'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdb_dip.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbDip::with(['dipType', 'researcher', 'project'])->findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdb_dip.edit', compact('item'));
    }

    public function update(UpdateDipRequest $request, $id)
    {
        $item = RdbDip::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();

        $data['dip_type'] = 1;
        $data['user_updated'] = auth()->id();

        if ($request->hasFile('dip_files')) {
            if ($item->dip_path && Storage::disk('public')->exists($item->dip_path)) {
                Storage::disk('public')->delete($item->dip_path);
            }
            
            $fileInfo = $this->fileUploadService->storeFile($request->file('dip_files'), 'dips');
            $data['dip_safe_name'] = $fileInfo['safe_name'];
            $data['dip_path'] = $fileInfo['path'];
            $data['dip_size'] = $fileInfo['size'];
            $data['dip_mime_type'] = $fileInfo['mime_type'];
            $data['dip_files'] = $fileInfo['safe_name'];
        }
        
        $item->update($data);

        return redirect()->route('backend.rdb_dip.show', $item->dip_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbDip::findOrFail($id);
        $this->authorize('delete', $item);

        $item->data_show = 0;
        $item->user_updated = auth()->id();
        $item->save();

        return redirect()->route('backend.rdb_dip.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว (ซ่อนการแสดงผล)');
    }

    public function uploadFile(UploadDipFileRequest $request, $id)
    {
        $item = RdbDip::findOrFail($id);
        $this->authorize('update', $item);
        $validated = $request->validated();

        if ($item->dip_path && Storage::disk('public')->exists($item->dip_path)) {
            Storage::disk('public')->delete($item->dip_path);
        }

        $fileInfo = $this->fileUploadService->storeFile($validated['dip_files'], 'dips');
        
        $item->update([
            'dip_safe_name' => $fileInfo['safe_name'],
            'dip_path'      => $fileInfo['path'],
            'dip_size'      => $fileInfo['size'],
            'dip_mime_type' => $fileInfo['mime_type'],
            'dip_files'     => $fileInfo['safe_name'],
            'user_updated'  => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'อัปโหลดไฟล์เรียบร้อยแล้ว');
    }

    public function deleteFile($id)
    {
        $item = RdbDip::findOrFail($id);
        $this->authorize('update', $item);

        if ($item->dip_path && Storage::disk('public')->exists($item->dip_path)) {
            Storage::disk('public')->delete($item->dip_path);
        }
        
        $item->update([
            'dip_files' => null,
            'dip_safe_name' => null,
            'dip_path' => null,
            'dip_size' => null,
            'dip_mime_type' => null,
            'user_updated' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'ลบไฟล์เรียบร้อยแล้ว');
    }

    public function viewFile($id)
    {
        $item = RdbDip::findOrFail($id);
        $this->authorize('view', $item);

        $filePath = $item->getDipFilePath();

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->response($filePath);
        }

        if ($filePath && file_exists(public_path($filePath))) {
            return response()->file(public_path($filePath));
        }

        abort(404, 'ไม่พบไฟล์');
    }
}