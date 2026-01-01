<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDipType;
use Illuminate\Http\Request;

class RdbDipTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RdbDipType::query();

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
        $item = new RdbDipType();
        return view('backend.rdbdiptype.create', compact('item'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dipt_name' => 'required|string|max:255|unique:rdb_dip_type,dipt_name',
        ]);

        $item = new RdbDipType();
        $input = $request->all();
        
        $item->fill($input);
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->updated_at = now();
        
        $item->save();

        return redirect()->route('backend.rdbdiptype.show', $item->dipt_id)
                         ->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = RdbDipType::findOrFail($id);
        return view('backend.rdbdiptype.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = RdbDipType::findOrFail($id);
        return view('backend.rdbdiptype.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'dipt_name' => 'required|string|max:255|unique:rdb_dip_type,dipt_name,'.$id.',dipt_id',
        ]);

        $item = RdbDipType::findOrFail($id);
        $input = $request->all();
        $input['user_updated'] = auth()->id();
        $input['updated_at'] = now();
        
        $item->update($input);

        return redirect()->route('backend.rdbdiptype.show', $item->dipt_id)
                         ->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = RdbDipType::findOrFail($id);
        
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
