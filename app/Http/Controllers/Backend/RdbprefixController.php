<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPrefix;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbPrefixRequest, UpdateRdbPrefixRequest};

class RdbprefixController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RdbPrefix::class);
        $items = RdbPrefix::with(['creator', 'updater'])->paginate(10);
        return view('backend.rdbprefix.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbPrefix::class);
        return view('backend.rdbprefix.create');
    }

    public function store(StoreRdbPrefixRequest $request)
    {
        $this->authorize('create', RdbPrefix::class);
        
        $data = $request->validated();
        
        $item = new RdbPrefix();
        $item->fill($data);
        $item->user_created = auth()->id();
        $item->save();
        return redirect()->route('backend.rdbprefix.show', $item->prefix_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbPrefix::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbprefix.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbPrefix::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdbprefix.edit', compact('item'));
    }

    public function update(UpdateRdbPrefixRequest $request, $id)
    {
        $item = RdbPrefix::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();
        
        $item->fill($data);
        $item->user_updated = auth()->id();
        $item->save();
        return redirect()->route('backend.rdbprefix.show', $item->prefix_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbPrefix::findOrFail($id);
        $this->authorize('delete', $item);
        
        // No data_show: Hard Delete with canDelete check
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbprefix.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีนักวิจัยใช้งานคำนำหน้านี้อยู่');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbprefix.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}