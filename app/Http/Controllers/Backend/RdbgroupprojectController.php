<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbGroupproject;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbGroupprojectRequest, UpdateRdbGroupprojectRequest};

class RdbgroupprojectController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RdbGroupproject::class);
        $items = RdbGroupproject::with(['creator', 'updater'])->paginate(10);
        return view('backend.rdbgroupproject.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbGroupproject::class);
        $item = new RdbGroupproject();
        return view('backend.rdbgroupproject.create', compact('item'));
    }

    public function store(StoreRdbGroupprojectRequest $request)
    {
        $this->authorize('create', RdbGroupproject::class);
        
        $data = $request->validated();
        
        $item = new RdbGroupproject();
        $item->fill($data);
        $item->user_created = auth()->id();
        $item->save();
        
        return redirect()->route('backend.rdbgroupproject.show', $item->pgroup_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbGroupproject::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbgroupproject.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbGroupproject::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdbgroupproject.edit', compact('item'));
    }

    public function update(UpdateRdbGroupprojectRequest $request, $id)
    {
        $item = RdbGroupproject::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();
        
        $item->fill($data);
        $item->user_updated = auth()->id();
        $item->save();
        return redirect()->route('backend.rdbgroupproject.show', $item->pgroup_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbGroupproject::findOrFail($id);
        $this->authorize('delete', $item);
        
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbgroupproject.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีโครงการวิจัยอยู่ในกลุ่มนี้');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbgroupproject.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}