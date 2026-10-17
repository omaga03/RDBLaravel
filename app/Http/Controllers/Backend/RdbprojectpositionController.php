<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectposition;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbProjectPositionRequest, UpdateRdbProjectPositionRequest};

class RdbprojectpositionController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RdbProjectposition::class);
        $items = RdbProjectposition::with(['creator', 'updater'])->paginate(10);
        return view('backend.rdbprojectposition.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbProjectposition::class);
        $item = new RdbProjectposition();
        return view('backend.rdbprojectposition.create', compact('item'));
    }

    public function store(StoreRdbProjectPositionRequest $request)
    {
        $this->authorize('create', RdbProjectposition::class);
        
        $data = $request->validated();
        
        $item = new RdbProjectposition();
        $item->fill($data);
        $item->user_created = auth()->id();
        $item->save();
        return redirect()->route('backend.rdbprojectposition.show', $item->position_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectposition::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbprojectposition.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectposition::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdbprojectposition.edit', compact('item'));
    }

    public function update(UpdateRdbProjectPositionRequest $request, $id)
    {
        $item = RdbProjectposition::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();
        
        $item->fill($data);
        $item->user_updated = auth()->id();
        $item->save();
        return redirect()->route('backend.rdbprojectposition.show', $item->position_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectposition::findOrFail($id);
        $this->authorize('delete', $item);
        
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbprojectposition.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีนักวิจัยใช้งานตำแหน่งนี้อยู่');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbprojectposition.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}