<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepmajor;
use App\Models\RdbDepartment;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbDepMajorRequest, UpdateRdbDepMajorRequest};

class RdbdepmajorController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RdbDepmajor::class);
        $items = RdbDepmajor::with('department')
            ->join('rdb_department', 'rdb_dep_major.department_id', '=', 'rdb_department.department_id')
            ->orderBy('rdb_department.department_nameTH', 'asc')
            ->select('rdb_dep_major.*')
            ->paginate(20);
        return view('backend.rdbdepmajor.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbDepmajor::class);
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $item = new RdbDepmajor();
        return view('backend.rdbdepmajor.create', compact('departments', 'item'));
    }

    public function store(StoreRdbDepMajorRequest $request)
    {
        $this->authorize('create', RdbDepmajor::class);
        $item = new RdbDepmajor();
        $item->fill($request->validated());
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbdepmajor.show', $item->maj_code)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbDepmajor::with('department')->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbdepmajor.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbDepmajor::with('department')->findOrFail($id);
        $this->authorize('update', $item);
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        return view('backend.rdbdepmajor.edit', compact('item', 'departments'));
    }

    public function update(UpdateRdbDepMajorRequest $request, $id)
    {
        $item = RdbDepmajor::findOrFail($id);
        $this->authorize('update', $item);
        $item->fill($request->validated());
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbdepmajor.show', $item->maj_code)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbDepmajor::findOrFail($id);
        $this->authorize('delete', $item);
        
        // No data_show: Hard Delete with canDelete check
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbdepmajor.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีนักวิจัยอยู่ในสาขานี้');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbdepmajor.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}