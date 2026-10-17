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

class RdbDipController extends Controller
{
    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbDip::class);
        $query = RdbDip::with(['dipType', 'researcher', 'project']);

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

        // Filter: Year
        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

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
            if ($item->dip_path && Storage::exists($item->dip_path)) {
                Storage::delete($item->dip_path);
            }
            
            $fileInfo = $this->fileUploadService->storeFile($request->file('dip_files'), 'dips');
            $data['dip_safe_name'] = $fileInfo['safe_name'];
            $data['dip_path'] = $fileInfo['path'];
            $data['dip_size'] = $fileInfo['size'];
            $data['dip_mime_type'] = $fileInfo['mime_type'];
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

        if ($item->dip_path && Storage::exists($item->dip_path)) {
            Storage::delete($item->dip_path);
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

        if ($item->dip_path && Storage::exists($item->dip_path)) {
            Storage::delete($item->dip_path);
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

        if (!$filePath || !Storage::exists($filePath)) {
            abort(404, 'ไม่พบไฟล์');
        }

        return Storage::response($filePath);
    }
}