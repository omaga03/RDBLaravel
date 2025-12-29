<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDip;
use Illuminate\Http\Request;

class RdbDipController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbDip::with(['dipType', 'researcher', 'project']);

        // Filter: Dip Request Number
        if ($request->filled('dip_request_number')) {
            $query->where('dip_request_number', 'like', '%' . $request->dip_request_number . '%');
        }

        // Filter: Dip Name (Invention Name)
        if ($request->filled('dip_name')) {
            $query->where('dip_data2_name', 'like', '%' . $request->dip_name . '%');
        }

        // Filter: Dip Type
        if ($request->filled('dipt_id')) {
            $query->where('dipt_id', $request->dipt_id);
        }

        // Filter: Researcher (Inventor)
        if ($request->filled('researcher_id')) {
            $query->where('researcher_id', $request->researcher_id);
        }

        // Filter: Date Range (dip_request_date)
        if ($request->filled('start_date')) {
            $query->whereDate('dip_request_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('dip_request_date', '<=', $request->end_date);
        }

        // Filter: Year
        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        $items = $query->orderBy('dip_request_date', 'desc')->paginate(20);

        // Lookup data for filters
        $dipTypes = \App\Models\RdbDipType::all();
        
        // For researchers (Inventor), we only need the one currently selected (if any) to populate the TomSelect initially
        $selectedResearcher = null;
        if ($request->filled('researcher_id')) {
            $selectedResearcher = \App\Models\RdbResearcher::find($request->researcher_id);
        }

        // For years, we also only need the selected one, as they will be loaded via AJAX
        $selectedYear = null;
        if ($request->filled('year_id')) {
            $selectedYear = \App\Models\RdbYear::find($request->year_id);
        }

        return view('backend.rdb_dip.index', compact('items', 'dipTypes', 'selectedResearcher', 'selectedYear'));
    }

    /**
     * AJAX: Search Years for IP
     */
    public function searchYears()
    {
        $years = \App\Models\RdbYear::orderBy('year_name', 'desc')
            ->get()
            ->map(fn($y) => [
                'value' => $y->year_id,
                'text' => 'พ.ศ. ' . $y->year_name
            ]);

        return response()->json($years);
    }

    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('Dip');
        return view('backend.rdb_dip.create');
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('Dip');
        
        $request->validate([
            'dip_request_number' => 'required|max:255',
            'dipt_id' => 'required',
            'dip_data2_name' => 'required',
            'researcher_id' => 'required',
        ]);

        $data = $request->all();
        $data['dip_type'] = 1; // Set default to 1 as per user request
        $data['data_show'] = 1;
        $data['user_created'] = auth()->id();
        $data['user_updated'] = auth()->id();
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $item = RdbDip::create($data);

        return redirect()->route('backend.rdb_dip.show', $item->dip_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbDip::findOrFail($id);
        return view('backend.rdb_dip.show', compact('item'));
    }

    public function edit($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('Dip');
        $item = RdbDip::findOrFail($id);
        return view('backend.rdb_dip.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('Dip');
        
        $item = RdbDip::findOrFail($id);
        
        $request->validate([
            'dip_request_number' => 'required|max:255',
            'dipt_id' => 'required',
            'dip_data2_name' => 'required',
            'researcher_id' => 'required',
        ]);

        $data = $request->except(['user_created', 'created_at']); // Safeguard audit logs
        $data['dip_type'] = 1; // Ensure dip_type remains 1 as per user request
        $data['user_updated'] = auth()->id();
        $data['updated_at'] = now();

        $item->update($data);

        return redirect()->route('backend.rdb_dip.show', $item->dip_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('Dip');
        $item = RdbDip::findOrFail($id);
        $item->delete();
        return redirect()->route('backend.rdb_dip.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }

    public function uploadFile(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('Dip');
        $item = RdbDip::findOrFail($id);

        $request->validate([
            'dip_files' => 'required|file|mimes:pdf|max:20480', // limit 20MB
        ]);

        if ($request->hasFile('dip_files')) {
            // Delete old file if exists
            if ($item->dip_files && \Illuminate\Support\Facades\Storage::disk('public')->exists('uploads/dips/' . $item->dip_files)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete('uploads/dips/' . $item->dip_files);
            }

            $file = $request->file('dip_files');
            // Yii2 convention: {id}-dip{YmdHis}{random50}~.{ext}
            $filename = $item->dip_id . '-dip' . date('YmdHis') . \Illuminate\Support\Str::random(50) . '~.' . $file->getClientOriginalExtension();
            
            $path = 'uploads/dips';
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($path);
            }

            \Illuminate\Support\Facades\Storage::disk('public')->put($path . '/' . $filename, file_get_contents($file));

            $item->update([
                'dip_files' => $filename,
                'user_updated' => auth()->id(),
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'อัปโหลดไฟล์เรียบร้อยแล้ว');
        }

        return redirect()->back()->with('error', 'ไม่พบไฟล์ที่ต้องการอัปโหลด');
    }

    public function deleteFile($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('Dip');
        $item = RdbDip::findOrFail($id);

        if ($item->dip_files) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists('uploads/dips/' . $item->dip_files)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete('uploads/dips/' . $item->dip_files);
            }

            $item->update([
                'dip_files' => null,
                'user_updated' => auth()->id(),
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'ลบไฟล์เรียบร้อยแล้ว');
        }

        return redirect()->back()->with('error', 'ไม่พบไฟล์ที่ต้องการลบ');
    }
}
