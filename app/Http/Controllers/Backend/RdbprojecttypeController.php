<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjecttype;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbProjectTypeRequest, UpdateRdbProjectTypeRequest};

class RdbprojecttypeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbProjecttype::class);
        $query = RdbProjecttype::with(['projectTypeGroup', 'masterType', 'year', 'creator', 'updater']);

        $search = $request->input('search', $request->input('q'));
        if ($search !== null && $search !== '') {
            $query->where('pt_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('pt_id', 'desc')->paginate(10)->withQueryString();
        return view('backend.rdbprojecttype.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbProjecttype::class);
        $item = new RdbProjecttype();
        $groupList = \App\Models\RdbProjectTypesGroup::all();
        $masterList = \App\Models\RdbProjectTypeMaster::orderBy('order_index')->get();
        $yearList = \App\Models\RdbYear::orderBy('year_name', 'desc')->get();
        $forList = RdbProjecttype::getPtforlist();
        $createList = RdbProjecttype::getCreatelist();
        return view('backend.rdbprojecttype.create', compact('item', 'groupList', 'masterList', 'yearList', 'forList', 'createList'));
    }

    public function store(StoreRdbProjectTypeRequest $request)
    {
        $this->authorize('create', RdbProjecttype::class);
        
        $data = $request->validated();
        
        $item = new RdbProjecttype();
        $item->fill($data);
        $item->user_created = auth()->id();
        $item->save();
        
        return redirect()->route('backend.rdbprojecttype.show', $item->pt_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbProjecttype::with(['projectTypeGroup', 'masterType', 'year', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        $forList = RdbProjecttype::getPtforlist();
        $createList = RdbProjecttype::getCreatelist();
        return view('backend.rdbprojecttype.show', compact('item', 'forList', 'createList'));
    }

    public function edit($id)
    {
        $item = RdbProjecttype::with(['projectTypeGroup', 'masterType', 'year', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        $groupList = \App\Models\RdbProjectTypesGroup::all();
        $masterList = \App\Models\RdbProjectTypeMaster::orderBy('order_index')->get();
        $yearList = \App\Models\RdbYear::orderBy('year_name', 'desc')->get();
        $forList = RdbProjecttype::getPtforlist();
        $createList = RdbProjecttype::getCreatelist();
        return view('backend.rdbprojecttype.edit', compact('item', 'groupList', 'masterList', 'yearList', 'forList', 'createList'));
    }

    public function update(UpdateRdbProjectTypeRequest $request, $id)
    {
        $item = RdbProjecttype::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();
        
        $item->user_updated = auth()->id();
        $item->update($data);
        
        return redirect()->route('backend.rdbprojecttype.show', $item->pt_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjecttype::findOrFail($id);
        $this->authorize('delete', $item);
        
        if (!$item->canDelete()) {
            return redirect()->route('backend.rdbprojecttype.show', $id)
                             ->with('error', 'ไม่สามารถลบได้เนื่องจากมีโครงการวิจัยหรือประเภทย่อยอยู่');
        }
        
        $item->delete();
        return redirect()->route('backend.rdbprojecttype.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}