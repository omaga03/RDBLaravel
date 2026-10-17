<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartment;
use App\Models\RdbDepartmentType;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbDepartmentRequest, UpdateRdbDepartmentRequest};

class RdbdepartmentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbDepartment::class);
        $query = RdbDepartment::with('departmentType');

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('department_nameTH', 'LIKE', "%{$search}%")
                  ->orWhere('department_code', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->orderBy('department_id')->paginate(20);
        return view('backend.rdbdepartment.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbDepartment::class);
        $departmentTypes = RdbDepartmentType::orderBy('tdepartment_id')->get();
        $item = new RdbDepartment();
        return view('backend.rdbdepartment.create', compact('departmentTypes', 'item'));
    }

    public function store(StoreRdbDepartmentRequest $request)
    {
        $this->authorize('create', RdbDepartment::class);
        
        $data = $request->validated();

        $item = new RdbDepartment();
        $item->fill($data);
        $item->user_created = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbdepartment.show', $item->department_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbDepartment::with(['departmentType', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbdepartment.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbDepartment::with(['departmentType', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        $departmentTypes = RdbDepartmentType::orderBy('tdepartment_id')->get();
        return view('backend.rdbdepartment.edit', compact('item', 'departmentTypes'));
    }

    public function update(UpdateRdbDepartmentRequest $request, $id)
    {
        $item = RdbDepartment::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();
        
        $item->fill($data);
        $item->user_updated = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbdepartment.show', $item->department_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbDepartment::findOrFail($id);
        $this->authorize('delete', $item);
        
        if (!$item->canDelete()) {
             return redirect()->route('backend.rdbdepartment.show', $id)
                              ->with('error', 'ไม่สามารถลบข้อมูลได้เนื่องมีการใช้งานอยู่');
        }

        $item->delete();
        
        return redirect()->route('backend.rdbdepartment.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}