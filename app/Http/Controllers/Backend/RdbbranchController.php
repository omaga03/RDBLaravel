<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedBranch;
use Illuminate\Http\Request;

class RdbbranchController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublishedBranch::query();
        
        if ($search = $request->input('search')) {
            $query->where('branch_name', 'LIKE', "%{$search}%");
        }

        $items = $query->orderBy('branch_id')->paginate(20);
        return view('backend.rdbbranch.index', compact('items'));
    }

    public function create()
    {
        $item = new RdbPublishedBranch();
        return view('backend.rdbbranch.create', compact('item'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_name' => 'required|max:255|unique:rdb_published_branch,branch_name',
        ], [
            'branch_name.unique' => 'ชื่อสาขาการวิจัยนี้มีอยู่ในระบบแล้ว',
        ]);

        $item = new RdbPublishedBranch();
        $item->fill($request->all());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbbranch.show', $item->branch_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        return view('backend.rdbbranch.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        return view('backend.rdbbranch.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'branch_name' => 'required|max:255|unique:rdb_published_branch,branch_name,' . $id . ',branch_id',
        ], [
            'branch_name.unique' => 'ชื่อสาขาการวิจัยนี้มีอยู่ในระบบแล้ว',
        ]);

        $item = RdbPublishedBranch::findOrFail($id);
        $item->fill($request->all());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbbranch.show', $item->branch_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbPublishedBranch::findOrFail($id);
        $item->delete();
        
        return redirect()->route('backend.rdbbranch.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
