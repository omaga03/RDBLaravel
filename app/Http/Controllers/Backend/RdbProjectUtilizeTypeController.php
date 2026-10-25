<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilizeType;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbProjectUtilizeTypeRequest, UpdateRdbProjectUtilizeTypeRequest};

class RdbProjectUtilizeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbProjectUtilizeType::class);
        $query = RdbProjectUtilizeType::with(['creator', 'updater']);

        // Search logic
        $search = $request->input('search', $request->input('q'));
        if ($search !== null && $search !== '') {
            $query->where('utz_typr_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('utz_type_id', 'desc')->paginate(10)->withQueryString();
        return view('backend.rdbprojectutilizetype.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', RdbProjectUtilizeType::class);
        $item = new RdbProjectUtilizeType();
        return view('backend.rdbprojectutilizetype.create', compact('item'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRdbProjectUtilizeTypeRequest $request)
    {
        $this->authorize('create', RdbProjectUtilizeType::class);
        $item = new RdbProjectUtilizeType();
        $validated = $request->validated();
        
        $item->fill($validated);
        $item->user_created = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbprojectutilizetype.show', $item->utz_type_id)
                         ->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = RdbProjectUtilizeType::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbprojectutilizetype.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = RdbProjectUtilizeType::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdbprojectutilizetype.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRdbProjectUtilizeTypeRequest $request, $id)
    {
        $item = RdbProjectUtilizeType::findOrFail($id);
        $this->authorize('update', $item);
        $validated = $request->validated();
        
        $item->fill($validated);
        $item->user_updated = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbprojectutilizetype.show', $item->utz_type_id)
                         ->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = RdbProjectUtilizeType::findOrFail($id);
        $this->authorize('delete', $item);
        
        // Dependency check
        if (\App\Models\RdbProjectUtilize::where('utz_type_id', $id)->exists()) {
             return redirect()->route('backend.rdbprojectutilizetype.show', $id)
                              ->with('error', 'ไม่สามารถลบได้เนื่องจากมีการใช้งานในข้อมูลการนำไปใช้ประโยชน์แล้ว');
        }

        $item->delete();

        return redirect()->route('backend.rdbprojectutilizetype.index')
                         ->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}