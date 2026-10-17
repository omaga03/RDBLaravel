<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDipType;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbDipTypeRequest, UpdateRdbDipTypeRequest};

class RdbDipTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbDipType::class);
        $query = RdbDipType::with(['creator', 'updater']);

        // Search logic
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where('dipt_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('dipt_id', 'desc')->paginate(10);
        return view('backend.rdbdiptype.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', RdbDipType::class);
        $item = new RdbDipType();
        return view('backend.rdbdiptype.create', compact('item'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRdbDipTypeRequest $request)
    {
        $this->authorize('create', RdbDipType::class);
        
        $validated = $request->validated();
        
        $item = new RdbDipType();
        $item->fill($validated);
        $item->user_created = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbdiptype.show', $item->dipt_id)
                         ->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = RdbDipType::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbdiptype.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = RdbDipType::with(['creator', 'updater'])->findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdbdiptype.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRdbDipTypeRequest $request, $id)
    {
        $item = RdbDipType::findOrFail($id);
        $this->authorize('update', $item);
        
        $validated = $request->validated();
        
        $item->fill($validated);
        $item->user_updated = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbdiptype.show', $item->dipt_id)
                         ->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = RdbDipType::findOrFail($id);
        $this->authorize('delete', $item);
        
        // Dependency check
        if (\App\Models\RdbDip::where('dipt_id', $id)->exists()) {
             return redirect()->route('backend.rdbdiptype.show', $id)
                              ->with('error', 'ไม่สามารถลบได้เนื่องจากมีการใช้งานในข้อมูลทรัพย์สินทางปัญญาแล้ว');
        }

        $item->delete();

        return redirect()->route('backend.rdbdiptype.index')
                         ->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}