<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedType;
use Illuminate\Http\Request;

class RdbPublishedTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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
        $item = new RdbPublishedType();
        return view('backend.rdbpublishedtype.create', compact('item'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pubtype_group' => 'required|string|max:255',
            'pubtype_grouptype' => 'nullable|string|max:255',
            'pubtype_subgroup' => 'nullable|string|max:255',
            'pubtype_score' => 'nullable|numeric|between:0,99.99',
        ]);

        $item = new RdbPublishedType();
        $input = $request->all();
        
        $item->fill($input);
        // Model has no standard timestamps, but fillable suggests created_at/updated_at. 
        // If DB table has them, uncomment below. Based on Model definition $timestamps = false, we'll respect that unless columns exist.
        // Assuming fillable is correct and columns exist but maybe not managed by Eloquent automatically due to some reason or just simple CRUD.
        // Let's manually set them if they are in fillable.
        $item->created_at = now();
        $item->updated_at = now();
        
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
        return view('backend.rdbpublishedtype.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = RdbPublishedType::findOrFail($id);
        return view('backend.rdbpublishedtype.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'pubtype_group' => 'required|string|max:255',
            'pubtype_grouptype' => 'nullable|string|max:255',
            'pubtype_subgroup' => 'nullable|string|max:255',
            'pubtype_score' => 'nullable|numeric|between:0,99.99',
        ]);

        $item = RdbPublishedType::findOrFail($id);
        $input = $request->all();
        $input['updated_at'] = now();
        
        $item->update($input);

        return redirect()->route('backend.rdbpublishedtype.show', $item->pubtype_id)
                         ->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = RdbPublishedType::findOrFail($id);
        
        // Check for dependencies if necessary (e.g. check if used in rdb_published)
        if (\App\Models\RdbPublished::where('pubtype_id', $id)->exists()) {
             return redirect()->route('backend.rdbpublishedtype.show', $id)
                              ->with('error', 'ไม่สามารถลบได้เนื่องจากมีการใช้งานในข้อมูลการตีพิมพ์แล้ว');
        }

        $item->delete();

        return redirect()->route('backend.rdbpublishedtype.index')
                         ->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
