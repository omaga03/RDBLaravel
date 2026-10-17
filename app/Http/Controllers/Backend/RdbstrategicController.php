<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbStrategic;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbStrategicRequest, UpdateRdbStrategicRequest};

class RdbstrategicController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RdbStrategic::class);
        $items = RdbStrategic::with(['year', 'creator', 'updater'])->paginate(10);
        return view('backend.rdbstrategic.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbStrategic::class);
        $years = \App\Models\RdbYear::orderBy('year_name', 'desc')->get();
        $item = new RdbStrategic();
        return view('backend.rdbstrategic.create', compact('years', 'item'));
    }

    public function store(StoreRdbStrategicRequest $request)
    {
        $this->authorize('create', RdbStrategic::class);
        
        $data = $request->validated();
        
        $item = new RdbStrategic();
        $item->fill($data);
        $item->user_created = auth()->id();
        $item->save();
        return redirect()->route('backend.rdbstrategic.show', $item->strategic_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbStrategic::with(['year', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbstrategic.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbStrategic::with(['year', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        $years = \App\Models\RdbYear::orderBy('year_name', 'desc')->get();
        return view('backend.rdbstrategic.edit', compact('item', 'years'));
    }

    public function update(UpdateRdbStrategicRequest $request, $id)
    {
        $item = RdbStrategic::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();
        
        $item->fill($data);
        $item->user_updated = auth()->id();
        $item->save();
        return redirect()->route('backend.rdbstrategic.show', $item->strategic_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbStrategic::findOrFail($id);
        $this->authorize('delete', $item);
        
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbstrategic.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีโครงการวิจัยอ้างอิงยุทธศาสตร์นี้อยู่');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbstrategic.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}