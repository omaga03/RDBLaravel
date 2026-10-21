<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbChangwat;
use Illuminate\Http\Request;

class RdbChangwatController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbChangwat::class);
        $query = RdbChangwat::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('changwat_t', 'like', "%{$search}%")
                  ->orWhere('changwat_e', 'like', "%{$search}%")
                  ->orWhere('amphoe_t', 'like', "%{$search}%")
                  ->orWhere('amphoe_e', 'like', "%{$search}%")
                  ->orWhere('tambon_t', 'like', "%{$search}%")
                  ->orWhere('tambon_e', 'like', "%{$search}%");
            });
        }

        if ($request->filled('changwat')) {
            $query->where('changwat_t', $request->changwat);
        }

        $items = $query->orderBy('changwat_t')->orderBy('amphoe_t')->orderBy('tambon_t')->paginate(20);
        $provinces = RdbChangwat::select('changwat_t')->distinct()->orderBy('changwat_t')->pluck('changwat_t');

        return view('backend.rdbchangwat.index', compact('items', 'provinces'));
    }

    public function create()
    {
        $this->authorize('create', RdbChangwat::class);
        $item = new RdbChangwat();
        return view('backend.rdbchangwat.create', compact('item'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', RdbChangwat::class);
        $validated = $request->validate([
            'changwat_t' => 'required|string|max:100',
            'changwat_e' => 'nullable|string|max:100',
            'amphoe_t' => 'required|string|max:100',
            'amphoe_e' => 'nullable|string|max:100',
            'tambon_t' => 'required|string|max:100',
            'tambon_e' => 'nullable|string|max:100',
            'lat' => 'nullable|numeric|between:-90,90',
            'long' => 'nullable|numeric|between:-180,180',
            'ch_id' => 'nullable|integer',
            'am_id' => 'nullable|integer',
            'ta_id' => 'nullable|integer',
        ]);

        $item = new RdbChangwat();
        $item->fill($validated);
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbchangwat.index')->with('success', 'บันทึกข้อมูลพื้นที่และพิกัดเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbChangwat::findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbchangwat.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbChangwat::findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdbchangwat.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbChangwat::findOrFail($id);
        $this->authorize('update', $item);

        $validated = $request->validate([
            'changwat_t' => 'required|string|max:100',
            'changwat_e' => 'nullable|string|max:100',
            'amphoe_t' => 'required|string|max:100',
            'amphoe_e' => 'nullable|string|max:100',
            'tambon_t' => 'required|string|max:100',
            'tambon_e' => 'nullable|string|max:100',
            'lat' => 'nullable|numeric|between:-90,90',
            'long' => 'nullable|numeric|between:-180,180',
            'ch_id' => 'nullable|integer',
            'am_id' => 'nullable|integer',
            'ta_id' => 'nullable|integer',
        ]);

        $item->fill($validated);
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbchangwat.index')->with('success', 'ปรับปรุงข้อมูลพื้นที่และพิกัดเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbChangwat::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();

        return redirect()->route('backend.rdbchangwat.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
