<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploadService;
use App\Http\Requests\Backend\{StoreRdbProjectFilesRequest, UpdateRdbProjectFilesRequest};

class RdbprojectfilesController extends Controller
{
    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    public function index()
    {
        $this->authorize('viewAny', RdbProjectFiles::class);
        $items = RdbProjectFiles::with(['project', 'creator', 'updater'])->paginate(10);
        return view('backend.rdbprojectfiles.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbProjectFiles::class);
        $projects = \App\Models\RdbProject::select('pro_id', 'pro_nameTH')->get();
        $item = new RdbProjectFiles();
        return view('backend.rdbprojectfiles.create', compact('projects', 'item'));
    }

    public function store(StoreRdbProjectFilesRequest $request)
    {
        $this->authorize('create', RdbProjectFiles::class);
        $validated = $request->validated();
        
        $item = new RdbProjectFiles();
        
        if ($request->hasFile('rf_files')) {
            $fileInfo = $this->fileUploadService->storeFile($validated['rf_files'], 'project_files');
            $item->prf_safe_name = $fileInfo['safe_name'];
            $item->prf_path = $fileInfo['path'];
            $item->prf_size = $fileInfo['size'];
            $item->prf_mime_type = $fileInfo['mime_type'];
            $item->rf_files = $fileInfo['original_name'];
        }

        $item->pro_id = $validated['pro_id'];
        $item->rf_filesname = $validated['rf_filesname'];
        $item->rf_note = $validated['rf_note'] ?? null;
        $item->rf_files_show = $validated['rf_files_show'] ?? 0;
        $item->rf_download = 0;

        $item->user_created = auth()->id();
        
        $item->save();
        return redirect()->route('backend.rdbprojectfiles.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectFiles::with(['project', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbprojectfiles.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectFiles::with(['project', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        $projects = \App\Models\RdbProject::select('pro_id', 'pro_nameTH')->get();
        return view('backend.rdbprojectfiles.edit', compact('item', 'projects'));
    }

    public function update(UpdateRdbProjectFilesRequest $request, $id)
    {
        $item = RdbProjectFiles::findOrFail($id);
        $this->authorize('update', $item);
        $validated = $request->validated();

        if ($request->hasFile('rf_files')) {
            if ($item->prf_path && Storage::disk('public')->exists($item->prf_path)) {
                Storage::disk('public')->delete($item->prf_path);
            }
            $fileInfo = $this->fileUploadService->storeFile($validated['rf_files'], 'project_files');
            $item->prf_safe_name = $fileInfo['safe_name'];
            $item->prf_path = $fileInfo['path'];
            $item->prf_size = $fileInfo['size'];
            $item->prf_mime_type = $fileInfo['mime_type'];
            $item->rf_files = $fileInfo['original_name'];
        }

        $item->pro_id = $validated['pro_id'];
        $item->rf_filesname = $validated['rf_filesname'];
        $item->rf_note = $validated['rf_note'] ?? null;
        $item->rf_files_show = $validated['rf_files_show'] ?? 0;
        
        $item->user_updated = auth()->id();
        
        $item->save();
        return redirect()->route('backend.rdbprojectfiles.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectFiles::findOrFail($id);
        $this->authorize('delete', $item);
        
        if ($item->prf_path && Storage::disk('public')->exists($item->prf_path)) {
            Storage::disk('public')->delete($item->prf_path);
        }
        if ($item->rf_files && $item->rf_files != $item->prf_safe_name) {
            foreach (['uploads/datafiles/' . $item->pro_id . '/' . $item->rf_files, 'uploads/datafiles/' . $item->rf_files, 'uploads/project_files/' . $item->rf_files] as $cand) {
                if (Storage::disk('public')->exists($cand)) {
                    Storage::disk('public')->delete($cand);
                }
            }
        }

        $item->delete();
        return redirect()->route('backend.rdbprojectfiles.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}