<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectTypeSub;
use App\Models\RdbProjectType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use App\Models\RdbYear;

class RdbProjectTypeSubController extends Controller
{
    public function index()
    {
        $items = RdbProjectTypeSub::with('projectType')->orderBy('pts_id', 'desc')->paginate(10);
        return view('backend.rdbprojecttypesub.index', compact('items'));
    }

    public function create()
    {
        $projectTypes = RdbProjectType::all();
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        return view('backend.rdbprojecttypesub.create', compact('projectTypes', 'years'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pt_id' => 'required|exists:rdb_project_types,pt_id',
            'pts_name' => [
                'required',
                'max:255',
                Rule::unique('rdb_project_types_sub')->where(function ($query) use ($request) {
                    return $query->where('pt_id', $request->pt_id);
                }),
            ],
            'pts_file' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,7z|max:20480', // 20MB max
        ], [
            'pt_id.required' => 'กรุณาเลือกประเภททุนสนับสนุน',
            'pt_id.exists' => 'ไม่พบข้อมูลประเภททุนสนับสนุนที่เลือก',
            'pts_name.required' => 'กรุณากรอกชื่อโครงการทุนสนับสนุน',
            'pts_name.unique' => 'ชื่อโครงการนี้มีอยู่แล้วในประเภททุนสนับสนุนนี้',
            'pts_name.max' => 'ชื่อโครงการต้องไม่เกิน 255 ตัวอักษร',
            'pts_file.mimes' => 'ไฟล์แนบต้องเป็นนามสกุล pdf, doc, docx, zip, rar, 7z เท่านั้น',
            'pts_file.max' => 'ขนาดไฟล์ต้องไม่เกิน 20MB',
        ]);

        $input = $request->all();
        $input['user_created'] = auth()->id();
        $input['user_updated'] = auth()->id();
        $input['created_at'] = now();
        $input['updated_at'] = now();

        if ($request->hasFile('pts_file')) {
            $file = $request->file('pts_file');
            if ($file->isValid()) {
                // Store file in public/uploads/project_types_sub
                $extension = $file->getClientOriginalExtension();
                $randomString = $this->generateRandomString(50);
                $filename = 'pts' . date('YmdHis') . $randomString . '~.' . $extension;
                
                // Manual storage to avoid strict path issues in Framework/PHP 8.4
                // explicitly using the disk and full path
                Storage::disk('public')->put('uploads/project_types_sub/' . $filename, file_get_contents($file));
                
                // Store filename in DB
                $input['pts_file'] = $filename;
            }
        }

        $newItem = RdbProjectTypeSub::create($input);

        return redirect()->route('backend.rdbprojecttypesub.show', $newItem->pts_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectTypeSub::with('projectType')->findOrFail($id);
        return view('backend.rdbprojecttypesub.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectTypeSub::findOrFail($id);
        $projectTypes = RdbProjectType::all();
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        return view('backend.rdbprojecttypesub.edit', compact('item', 'projectTypes', 'years'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pt_id' => 'required|exists:rdb_project_types,pt_id',
            'pts_name' => [
                'required',
                'max:255',
                Rule::unique('rdb_project_types_sub')->where(function ($query) use ($request) {
                    return $query->where('pt_id', $request->pt_id);
                })->ignore($id, 'pts_id'),
            ],
            'pts_file' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,7z|max:20480',
        ], [
            'pt_id.required' => 'กรุณาเลือกประเภททุนสนับสนุน',
            'pt_id.exists' => 'ไม่พบข้อมูลประเภททุนสนับสนุนที่เลือก',
            'pts_name.required' => 'กรุณากรอกชื่อโครงการทุนสนับสนุน',
            'pts_name.unique' => 'ชื่อโครงการนี้มีอยู่แล้วในประเภททุนสนับสนุนนี้',
            'pts_name.max' => 'ชื่อโครงการต้องไม่เกิน 255 ตัวอักษร',
            'pts_file.mimes' => 'ไฟล์แนบต้องเป็นนามสกุล pdf, doc, docx, zip, rar, 7z เท่านั้น',
            'pts_file.max' => 'ขนาดไฟล์ต้องไม่เกิน 20MB',
        ]);

        $item = RdbProjectTypeSub::findOrFail($id);
        $input = $request->all();
        $input['user_updated'] = auth()->id();
        $input['updated_at'] = now();

        if ($request->hasFile('pts_file')) {
            $file = $request->file('pts_file');
            if ($file->isValid()) {
                // Delete old file if exists
                if ($item->pts_file && Storage::disk('public')->exists('uploads/project_types_sub/' . $item->pts_file)) {
                    Storage::disk('public')->delete('uploads/project_types_sub/' . $item->pts_file);
                }
                
                $extension = $file->getClientOriginalExtension();
                $randomString = $this->generateRandomString(50);
                $filename = 'pts' . date('YmdHis') . $randomString . '~.' . $extension;

                Storage::disk('public')->put('uploads/project_types_sub/' . $filename, file_get_contents($file));
                $input['pts_file'] = $filename;
            }
        }

        $item->update($input);

        return redirect()->route('backend.rdbprojecttypesub.show', $item->pts_id)->with('success', 'ปรับปรุงข้อมูลเรียบร้อยแล้ว');
    }

    private function generateRandomString($length = 50) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.-_~';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function destroy($id)
    {
        $item = RdbProjectTypeSub::findOrFail($id);
        
        // No data_show: Hard Delete with canDelete check
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbprojecttypesub.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีโครงการวิจัยใช้งานประเภทนี้อยู่');
        }
        
        if ($item->pts_file && Storage::disk('public')->exists('uploads/project_types_sub/' . $item->pts_file)) {
            Storage::disk('public')->delete('uploads/project_types_sub/' . $item->pts_file);
        }
        $item->delete();
        return redirect()->route('backend.rdbprojecttypesub.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
