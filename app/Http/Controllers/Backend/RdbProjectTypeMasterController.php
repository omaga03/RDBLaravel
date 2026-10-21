<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectTypeMaster;
use App\Models\RdbProjectTypesGroup;
use Illuminate\Http\Request;

class RdbProjectTypeMasterController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbProjectTypeMaster::class);
        $query = RdbProjectTypeMaster::with(['projectTypeGroup', 'projectTypes'])
            ->withCount('projectTypes');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('master_pt_name', 'like', "%{$search}%");
        }

        if ($request->filled('pttg_id')) {
            $query->where('pttg_id', $request->pttg_id);
        }

        $items = $query->orderBy('order_index')->orderBy('master_pt_id')->paginate(15);
        $groups = RdbProjectTypesGroup::orderBy('pttg_id')->get();

        return view('backend.rdbprojecttypesmaster.index', compact('items', 'groups'));
    }

    public function create()
    {
        $this->authorize('create', RdbProjectTypeMaster::class);
        $item = new RdbProjectTypeMaster();
        $item->order_index = (RdbProjectTypeMaster::max('order_index') ?? 0) + 1;
        $groups = RdbProjectTypesGroup::orderBy('pttg_id')->get();
        return view('backend.rdbprojecttypesmaster.create', compact('item', 'groups'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', RdbProjectTypeMaster::class);
        $validated = $request->validate([
            'master_pt_name' => 'required|string|max:255',
            'pttg_id' => 'nullable|exists:rdb_project_types_group,pttg_id',
            'order_index' => 'nullable|integer',
            'color_code' => 'nullable|string|max:20',
        ]);

        $item = RdbProjectTypeMaster::create($validated);

        return redirect()->route('backend.rdbprojecttypesmaster.index')->with('success', 'บันทึกหมวดหมู่ทุนหลักเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjectTypeMaster::with(['projectTypeGroup', 'projectTypes'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbprojecttypesmaster.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectTypeMaster::findOrFail($id);
        $this->authorize('update', $item);
        $groups = RdbProjectTypesGroup::orderBy('pttg_id')->get();
        return view('backend.rdbprojecttypesmaster.edit', compact('item', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectTypeMaster::findOrFail($id);
        $this->authorize('update', $item);

        $validated = $request->validate([
            'master_pt_name' => 'required|string|max:255',
            'pttg_id' => 'nullable|exists:rdb_project_types_group,pttg_id',
            'order_index' => 'nullable|integer',
            'color_code' => 'nullable|string|max:20',
        ]);

        $item->update($validated);

        return redirect()->route('backend.rdbprojecttypesmaster.index')->with('success', 'ปรับปรุงข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectTypeMaster::withCount('projectTypes')->findOrFail($id);
        $this->authorize('delete', $item);

        if ($item->project_types_count > 0) {
            return redirect()->route('backend.rdbprojecttypesmaster.index')->with('error', 'ไม่สามารถลบได้เนื่องจากมีประเภททุนวิจัยผูกอยู่กับหมวดหมู่นี้ (' . $item->project_types_count . ' รายการ)');
        }

        $item->delete();
        return redirect()->route('backend.rdbprojecttypesmaster.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
