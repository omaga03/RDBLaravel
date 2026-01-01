<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjecttype;
use Illuminate\Http\Request;

class RdbprojecttypeController extends Controller
{
    public function index()
    {
        $items = RdbProjecttype::with(['projectTypeGroup', 'year'])->orderBy('pt_id', 'desc')->paginate(10);
        return view('backend.rdbprojecttype.index', compact('items'));
    }

    public function create()
    {
        $item = new RdbProjecttype();
        $groupList = \App\Models\RdbProjectTypesGroup::all();
        $yearList = \App\Models\RdbYear::orderBy('year_name', 'desc')->get();
        $forList = RdbProjecttype::getPtforlist();
        $createList = RdbProjecttype::getCreatelist();
        return view('backend.rdbprojecttype.create', compact('item', 'groupList', 'yearList', 'forList', 'createList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pt_name' => [
                'required',
                'max:255',
                \Illuminate\Validation\Rule::unique('rdb_project_types')->where(function ($query) use ($request) {
                    return $query->where('year_id', $request->year_id);
                }),
            ],
            'year_id' => 'required|exists:rdb_year,year_id',
            'pt_for' => 'nullable|string|max:1',
            'pt_created' => 'nullable|string|max:1',
            'pt_type' => 'nullable|string|max:1',
            'pt_utz' => 'nullable|string|max:1',
            'pttg_id' => 'nullable|exists:rdb_project_types_group,pttg_id',
        ], [
            'pt_name.unique' => 'ชื่อประเภททุนนี้มีอยู่ในปีงบประมาณนี้แล้ว',
            'year_id.required' => 'กรุณาเลือกปีงบประมาณ',
            'year_id.exists' => 'ไม่พบปีงบประมาณที่เลือก',
            'pttg_id.exists' => 'ไม่พบกลุ่มประเภททุนที่เลือก',
        ]);
        
        $item = new RdbProjecttype();
        $input = $request->all();
        
        $item->fill($input);
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();
        
        return redirect()->route('backend.rdbprojecttype.show', $item->pt_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjecttype::with(['projectTypeGroup', 'year'])->findOrFail($id);
        $forList = RdbProjecttype::getPtforlist();
        $createList = RdbProjecttype::getCreatelist();
        return view('backend.rdbprojecttype.show', compact('item', 'forList', 'createList'));
    }

    public function edit($id)
    {
        $item = RdbProjecttype::findOrFail($id);
        $groupList = \App\Models\RdbProjectTypesGroup::all();
        $yearList = \App\Models\RdbYear::orderBy('year_name', 'desc')->get();
        $forList = RdbProjecttype::getPtforlist();
        $createList = RdbProjecttype::getCreatelist();
        return view('backend.rdbprojecttype.edit', compact('item', 'groupList', 'yearList', 'forList', 'createList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pt_name' => [
                'required',
                'max:255',
                \Illuminate\Validation\Rule::unique('rdb_project_types')->where(function ($query) use ($request) {
                    return $query->where('year_id', $request->year_id);
                })->ignore($id, 'pt_id'),
            ],
            'year_id' => 'required|exists:rdb_year,year_id',
            'pt_for' => 'nullable|string|max:1',
            'pt_created' => 'nullable|string|max:1',
            'pt_type' => 'nullable|string|max:1',
            'pt_utz' => 'nullable|string|max:1',
            'pttg_id' => 'nullable|exists:rdb_project_types_group,pttg_id',
        ], [
            'pt_name.unique' => 'ชื่อประเภททุนนี้มีอยู่ในปีงบประมาณนี้แล้ว',
            'year_id.required' => 'กรุณาเลือกปีงบประมาณ',
            'year_id.exists' => 'ไม่พบปีงบประมาณที่เลือก',
            'pttg_id.exists' => 'ไม่พบกลุ่มประเภททุนที่เลือก',
        ]);

        $item = RdbProjecttype::findOrFail($id);
        $input = $request->all();
        $input['user_updated'] = auth()->id();
        $input['updated_at'] = now();
        
        $item->update($input);
        
        return redirect()->route('backend.rdbprojecttype.show', $item->pt_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjecttype::findOrFail($id);
        
        // No data_show: Hard Delete with canDelete check
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbprojecttype.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีโครงการวิจัยหรือประเภทย่อยอยู่');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbprojecttype.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
