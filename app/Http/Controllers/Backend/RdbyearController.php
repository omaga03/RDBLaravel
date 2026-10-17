<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbYear;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbyearRequest, UpdateRdbyearRequest};

class RdbyearController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RdbYear::class);
        $items = RdbYear::with(['creator', 'updater'])->orderBy('year_name', 'desc')->paginate(10);
        return view('backend.rdbyear.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbYear::class);
        $item = new RdbYear();
        return view('backend.rdbyear.create', compact('item'));
    }

    public function store(StoreRdbyearRequest $request)
    {
        $this->authorize('create', RdbYear::class);
        
        $data = $request->validated();
        
        $item = new RdbYear();
        $item->fill($data);
        $item->user_created = auth()->id();
        $item->save();
        return redirect()->route('backend.rdbyear.show', $item->year_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbYear::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbyear.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbYear::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdbyear.edit', compact('item'));
    }

    public function update(UpdateRdbyearRequest $request, $id)
    {
        $item = RdbYear::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();
        
        $item->fill($data);
        $item->user_updated = auth()->id();
        $item->save();
        return redirect()->route('backend.rdbyear.show', $item->year_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbYear::findOrFail($id);
        $this->authorize('delete', $item);
        
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbyear.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีโครงการวิจัยในปีงบประมาณนี้อยู่');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbyear.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}