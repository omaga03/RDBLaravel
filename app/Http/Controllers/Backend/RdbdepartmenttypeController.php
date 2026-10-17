<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartmentType;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbDepartmentTypeRequest, UpdateRdbDepartmentTypeRequest};

class RdbdepartmenttypeController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RdbDepartmentType::class);
        $items = RdbDepartmentType::with(['creator', 'updater'])->orderBy('tdepartment_id')->paginate(20);
        return view('backend.rdbdepartmenttype.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbDepartmentType::class);
        $item = new RdbDepartmentType();
        return view('backend.rdbdepartmenttype.create', compact('item'));
    }

    public function store(StoreRdbDepartmentTypeRequest $request)
    {
        $this->authorize('create', RdbDepartmentType::class);
        
        $data = $request->validated();
        
        $item = new RdbDepartmentType();
        $item->fill($data);
        $item->user_created = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbdepartmenttype.show', $item->tdepartment_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbDepartmentType::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbdepartmenttype.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbDepartmentType::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdbdepartmenttype.edit', compact('item'));
    }

    public function update(UpdateRdbDepartmentTypeRequest $request, $id)
    {
        $item = RdbDepartmentType::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request.validated();
        
        $item->fill($data);
        $item->user_updated = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbdepartmenttype.show', $item->tdepartment_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbDepartmentType::findOrFail($id);
        $this->authorize('delete', $item);
        
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbdepartmenttype.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีหน่วยงานอยู่ในประเภทนี้');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbdepartmenttype.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}