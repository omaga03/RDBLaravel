<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedBranch;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\StoreRdbBranchRequest;
use App\Http\Requests\Backend\UpdateRdbBranchRequest;

class RdbbranchController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbPublishedBranch::class);
        
        $query = RdbPublishedBranch::with(['creator', 'updater']);
        
        if ($search = $request->input('search')) {
            $query->where('branch_name', 'LIKE', "%{$search}%");
        }

        $items = $query->orderBy('branch_id')->paginate(20);
        return view('backend.rdbbranch.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbPublishedBranch::class);
        $item = new RdbPublishedBranch();
        return view('backend.rdbbranch.create', compact('item'));
    }

    public function store(StoreRdbBranchRequest $request)
    {
        $this->authorize('create', RdbPublishedBranch::class);
        $item = new RdbPublishedBranch();
        $item->fill($request->validated());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbbranch.show', $item->branch_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbPublishedBranch::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbbranch.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbPublishedBranch::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdbbranch.edit', compact('item'));
    }

    public function update(UpdateRdbBranchRequest $request, $id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        $this->authorize('update', $item);
        $item->fill($request->validated());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbbranch.show', $item->branch_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        $this->authorize('delete', $item);
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbbranch.index')->with('error', 'ไม่สามารถลบข้อมูลได้ เนื่องจากมีข้อมูลอื่นเชื่อมโยงอยู่');
        }
        $item->delete();
        
        return redirect()->route('backend.rdbbranch.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}