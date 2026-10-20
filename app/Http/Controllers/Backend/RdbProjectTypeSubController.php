<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectTypeSub;
use App\Models\RdbProjectType;
use App\Models\RdbYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploadService;
use App\Http\Requests\Backend\{StoreRdbProjectTypeSubRequest, UpdateRdbProjectTypeSubRequest};

class RdbProjectTypeSubController extends Controller
{
    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index()
    {
        $this->authorize('viewAny', RdbProjectTypeSub::class);
        $items = RdbProjectTypeSub::with(['projectType', 'createdBy', 'updatedBy'])->orderBy('pts_id', 'desc')->paginate(10);
        return view('backend.rdbprojecttypesub.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbProjectTypeSub::class);
        $projectTypes = RdbProjectType::all();
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        return view('backend.rdbprojecttypesub.create', compact('projectTypes', 'years'));
    }

    public function store(StoreRdbProjectTypeSubRequest $request)
    {
        $this->authorize('create', RdbProjectTypeSub::class);
        
        $data = $request->validated();

        $item = new RdbProjectTypeSub();
        $item->fill($data);
        $item->user_created = auth()->id();
        $item->user_updated = auth()->id();
        
        if ($request->hasFile('pts_file')) {
            $fileInfo = $this->fileUploadService->storeFile($request->file('pts_file'), 'project_types_sub');
            $item->pts_safe_name = $fileInfo['safe_name'];
            $item->pts_path = $fileInfo['path'];
            $item->pts_size = $fileInfo['size'];
            $item->pts_mime_type = $fileInfo['mime_type'];
            $item->pts_file = $fileInfo['original_name'];
        }

        $item->save();

        return redirect()->route('backend.rdbprojecttypesub.show', $item->pts_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectTypeSub::with(['projectType', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbprojecttypesub.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectTypeSub::with(['projectType', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->authorize('update', $item);
        $projectTypes = RdbProjectType::all();
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        return view('backend.rdbprojecttypesub.edit', compact('item', 'projectTypes', 'years'));
    }

    public function update(UpdateRdbProjectTypeSubRequest $request, $id)
    {
        $item = RdbProjectTypeSub::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();
        
        $item->user_updated = auth()->id();
        
        if ($request->hasFile('pts_file')) {
            if ($item->pts_path && Storage::disk('public')->exists($item->pts_path)) {
                Storage::disk('public')->delete($item->pts_path);
            }
            
            $fileInfo = $this->fileUploadService->storeFile($request->file('pts_file'), 'project_types_sub');
            $item->pts_safe_name = $fileInfo['safe_name'];
            $item->pts_path = $fileInfo['path'];
            $item->pts_size = $fileInfo['size'];
            $item->pts_mime_type = $fileInfo['mime_type'];
            $item->pts_file = $fileInfo['original_name'];
        }

        $item->update($data);

        return redirect()->route('backend.rdbprojecttypesub.show', $item->pts_id)->with('success', 'ปรับปรุงข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectTypeSub::findOrFail($id);
        $this->authorize('delete', $item);
        
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbprojecttypesub.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีโครงการวิจัยใช้งานประเภทนี้อยู่');
        }
        
        if ($item->pts_path && Storage::disk('public')->exists($item->pts_path)) {
            Storage::disk('public')->delete($item->pts_path);
        }
        
        $item->delete();
        return redirect()->route('backend.rdbprojecttypesub.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }

    public function viewFile($id)
    {
        $item = RdbProjectTypeSub::findOrFail($id);
        $this->authorize('view', $item);

        $filePath = $item->getTypeSubFilePath();

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->response($filePath);
        }

        if ($filePath && file_exists(public_path('storage/' . $filePath))) {
            return response()->file(public_path('storage/' . $filePath));
        }

        if ($filePath && file_exists(public_path($filePath))) {
            return response()->file(public_path($filePath));
        }

        abort(404, 'ไม่พบไฟล์');
    }
}