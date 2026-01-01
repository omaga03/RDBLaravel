<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilizeType;
use Illuminate\Http\Request;

class RdbProjectUtilizeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RdbProjectUtilizeType::query();

        // Search logic
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where('utz_typr_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('utz_type_id', 'desc')->paginate(10);
        return view('backend.rdbprojectutilizetype.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $item = new RdbProjectUtilizeType();
        return view('backend.rdbprojectutilizetype.create', compact('item'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'utz_typr_name' => 'required|string|max:255|unique:rdb_project_utilize_type,utz_typr_name',
            'utz_type_index' => 'nullable|integer',
        ]);

        $item = new RdbProjectUtilizeType();
        $input = $request->all();
        
        $item->fill($input);
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->updated_at = now();
        
        $item->save();

        return redirect()->route('backend.rdbprojectutilizetype.show', $item->utz_type_id)
                         ->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = RdbProjectUtilizeType::findOrFail($id);
        return view('backend.rdbprojectutilizetype.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = RdbProjectUtilizeType::findOrFail($id);
        return view('backend.rdbprojectutilizetype.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'utz_typr_name' => 'required|string|max:255|unique:rdb_project_utilize_type,utz_typr_name,'.$id.',utz_type_id',
            'utz_type_index' => 'nullable|integer',
        ]);

        $item = RdbProjectUtilizeType::findOrFail($id);
        $input = $request->all();
        $input['user_updated'] = auth()->id();
        $input['updated_at'] = now();
        
        $item->update($input);

        return redirect()->route('backend.rdbprojectutilizetype.show', $item->utz_type_id)
                         ->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = RdbProjectUtilizeType::findOrFail($id);
        
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
