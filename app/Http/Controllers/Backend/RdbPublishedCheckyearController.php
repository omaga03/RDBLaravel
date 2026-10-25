<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedCheckyear;
use App\Models\RdbYear;
use App\Helpers\ThaiDateHelper;
use Illuminate\Http\Request;

class RdbPublishedCheckyearController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbPublishedCheckyear::class);
        $query = RdbPublishedCheckyear::with('year');

        $search = $request->input('search', $request->input('q'));
        if ($search !== null && $search !== '') {
            $query->whereHas('year', function ($yq) use ($search) {
                $yq->where('year_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        $items = $query->orderBy('year_id', 'desc')->paginate(15)->withQueryString();
        $years = RdbYear::orderBy('year_name', 'desc')->get();

        return view('backend.rdbpublishedcheckyear.index', compact('items', 'years'));
    }

    public function create()
    {
        $this->authorize('create', RdbPublishedCheckyear::class);
        $item = new RdbPublishedCheckyear();
        $years = RdbYear::orderBy('year_name', 'desc')->get();

        return view('backend.rdbpublishedcheckyear.create', compact('item', 'years'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', RdbPublishedCheckyear::class);

        $request->merge([
            'rdbyearedu_start' => ThaiDateHelper::toIsoDate($request->input('rdbyearedu_start')),
            'rdbyearedu_end'   => ThaiDateHelper::toIsoDate($request->input('rdbyearedu_end')),
            'rdbyearbud_start' => ThaiDateHelper::toIsoDate($request->input('rdbyearbud_start')),
            'rdbyearbud_end'   => ThaiDateHelper::toIsoDate($request->input('rdbyearbud_end')),
        ]);

        $validated = $request->validate([
            'year_id' => 'required|exists:rdb_year,year_id',
            'rdbyearedu_start' => 'required|date',
            'rdbyearedu_end' => 'required|date|after_or_equal:rdbyearedu_start',
            'rdbyearbud_start' => 'required|date',
            'rdbyearbud_end' => 'required|date|after_or_equal:rdbyearbud_start',
        ]);

        $item = new RdbPublishedCheckyear();
        $item->fill($validated);
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbpublishedcheckyear.index')->with('success', 'บันทึกข้อมูลกำหนดระยะเวลาปีการศึกษา/ปีงบประมาณเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbPublishedCheckyear::with('year')->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbpublishedcheckyear.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbPublishedCheckyear::findOrFail($id);
        $this->authorize('update', $item);
        $years = RdbYear::orderBy('year_name', 'desc')->get();

        return view('backend.rdbpublishedcheckyear.edit', compact('item', 'years'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedCheckyear::findOrFail($id);
        $this->authorize('update', $item);

        $request->merge([
            'rdbyearedu_start' => ThaiDateHelper::toIsoDate($request->input('rdbyearedu_start')),
            'rdbyearedu_end'   => ThaiDateHelper::toIsoDate($request->input('rdbyearedu_end')),
            'rdbyearbud_start' => ThaiDateHelper::toIsoDate($request->input('rdbyearbud_start')),
            'rdbyearbud_end'   => ThaiDateHelper::toIsoDate($request->input('rdbyearbud_end')),
        ]);

        $validated = $request->validate([
            'year_id' => 'required|exists:rdb_year,year_id',
            'rdbyearedu_start' => 'required|date',
            'rdbyearedu_end' => 'required|date|after_or_equal:rdbyearedu_start',
            'rdbyearbud_start' => 'required|date',
            'rdbyearbud_end' => 'required|date|after_or_equal:rdbyearbud_start',
        ]);

        $item->fill($validated);
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbpublishedcheckyear.index')->with('success', 'อัปเดตข้อมูลกำหนดระยะเวลาเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbPublishedCheckyear::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();

        return redirect()->route('backend.rdbpublishedcheckyear.index')->with('success', 'ลบข้อมูลกำหนดระยะเวลาเรียบร้อยแล้ว');
    }
}
