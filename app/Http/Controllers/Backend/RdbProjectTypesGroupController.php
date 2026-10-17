<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectTypesGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\Backend\{StoreRdbProjectTypesGroupRequest, UpdateRdbProjectTypesGroupRequest};

class RdbProjectTypesGroupController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RdbProjectTypesGroup::class);
        $items = RdbProjectTypesGroup::with(['createdBy', 'updatedBy'])->orderBy('pttg_id', 'desc')->paginate(10);
        $groupList = RdbProjectTypesGroup::getGroupList();
        return view('backend.rdbprojecttypesgroup.index', compact('items', 'groupList'));
    }

    public function create()
    {
        $this->authorize('create', RdbProjectTypesGroup::class);
        $groupList = RdbProjectTypesGroup::getGroupList();
        return view('backend.rdbprojecttypesgroup.create', compact('groupList'));
    }

    public function store(StoreRdbProjectTypesGroupRequest $request)
    {
        $this->authorize('create', RdbProjectTypesGroup::class);
        
        $data = $request->validated();

        $item = new RdbProjectTypesGroup();
        $item->fill($data);
        $item->user_created = auth()->id();
        $item->user_updated = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbprojecttypesgroup.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectTypesGroup::with(['createdBy', 'updatedBy'])->findOrFail($id);
        $this->authorize('view', $item);
        $groupList = RdbProjectTypesGroup::getGroupList();
        return view('backend.rdbprojecttypesgroup.show', compact('item', 'groupList'));
    }

    public function edit($id)
    {
        $item = RdbProjectTypesGroup::with(['createdBy', 'updatedBy'])->findOrFail($id);
        $this->authorize('update', $item);
        $groupList = RdbProjectTypesGroup::getGroupList();
        return view('backend.rdbprojecttypesgroup.edit', compact('item', 'groupList'));
    }

    public function update(UpdateRdbProjectTypesGroupRequest $request, $id)
    {
        $item = RdbProjectTypesGroup::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();
        
        $item->fill($data);
        $item->user_updated = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbprojecttypesgroup.index')->with('success', 'ปรับปรุงข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectTypesGroup::findOrFail($id);
        $this->authorize('delete', $item);
        if ($item->canDelete()) {
            $item->delete();
            return redirect()->route('backend.rdbprojecttypesgroup.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        }
        return redirect()->route('backend.rdbprojecttypesgroup.index')->with('error', 'ไม่สามารถลบข้อมูลได้เนื่องจากมีการใช้งานอยู่');
    }
}