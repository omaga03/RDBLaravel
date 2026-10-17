<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedType;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\{StoreRdbPublishedTypeRequest, UpdateRdbPublishedTypeRequest};

class RdbPublishedTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbPublishedType::class);
        $query = RdbPublishedType::query();

        // Search logic
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('pubtype_group', 'like', "%{$search}%")
                  ->orWhere('pubtype_grouptype', 'like', "%{$search}%")
                  ->orWhere('pubtype_subgroup', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('pubtype_id', 'desc')->paginate(10);
        return view('backend.rdbpublishedtype.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', RdbPublishedType::class);
        $item = new RdbPublishedType();
        return view('backend.rdbpublishedtype.create', compact('item'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRdbPublishedTypeRequest $request)
    {
        $this->authorize('create', RdbPublishedType::class);
        $validated = $request->validated();
        
        $item = new RdbPublishedType();
        $item->fill($validated);
        $item->save();

        return redirect()->route('backend.rdbpublishedtype.show', $item->pubtype_id)
                         ->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = RdbPublishedType::findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbpublishedtype.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = RdbPublishedType::findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdbpublishedtype.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRdbPublishedTypeRequest $request, $id)
    {
        $item = RdbPublishedType::findOrFail($id);
        $this->authorize('update', $item);
        
        $validated = $request->validated();
        
        $item->update($validated);

        return redirect()->route('backend.rdbpublishedtype.show', $item->pubtype_id)
                         ->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = RdbPublishedType::findOrFail($id);
        $this->authorize('delete', $item);
        
        if (\App\Models\RdbPublished::where('pubtype_id', $id)->exists()) {
             return redirect()->route('backend.rdbpublishedtype.show', $id)
                              ->with('error', 'ไม่สามารถลบได้เนื่องจากมีการใช้งานในข้อมูลการตีพิมพ์แล้ว');
        }

        $item->delete();

        return redirect()->route('backend.rdbpublishedtype.index')
                         ->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}