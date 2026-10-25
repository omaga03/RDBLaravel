<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedBranchper;
use App\Models\RdbPublishedBranch;
use App\Models\RdbYear;
use Illuminate\Http\Request;

class RdbPublishedBranchperController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbPublishedBranchper::class);
        $query = RdbPublishedBranchper::with(['year', 'branch']);

        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $search = $request->input('search', $request->input('q'));
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('year', function ($yq) use ($search) {
                    $yq->where('year_name', 'like', "%{$search}%");
                })->orWhereHas('branch', function ($bq) use ($search) {
                    $bq->where('branch_name', 'like', "%{$search}%");
                });
            });
        }

        $items = $query->orderBy('year_id', 'desc')->orderBy('branch_id')->paginate(15)->withQueryString();
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $branches = RdbPublishedBranch::orderBy('branch_name')->get();

        return view('backend.rdbpublishedbranchper.index', compact('items', 'years', 'branches'));
    }

    public function create()
    {
        $this->authorize('create', RdbPublishedBranchper::class);
        $item = new RdbPublishedBranchper();
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $branches = RdbPublishedBranch::orderBy('branch_name')->get();
        return view('backend.rdbpublishedbranchper.create', compact('item', 'years', 'branches'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', RdbPublishedBranchper::class);
        $validated = $request->validate([
            'year_id' => 'required|exists:rdb_year,year_id',
            'branch_id' => 'required|exists:rdb_published_branch,branch_id',
            'branchper_percent' => 'required|numeric|min:0|max:100',
        ]);

        $item = new RdbPublishedBranchper();
        $item->fill($validated);
        $item->user_created = auth()->id();
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbpublishedbranchper.index')->with('success', 'บันทึกข้อมูลสัดส่วนสาขาเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbPublishedBranchper::with(['year', 'branch'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbpublishedbranchper.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbPublishedBranchper::findOrFail($id);
        $this->authorize('update', $item);
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $branches = RdbPublishedBranch::orderBy('branch_name')->get();
        return view('backend.rdbpublishedbranchper.edit', compact('item', 'years', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedBranchper::findOrFail($id);
        $this->authorize('update', $item);

        $validated = $request->validate([
            'year_id' => 'required|exists:rdb_year,year_id',
            'branch_id' => 'required|exists:rdb_published_branch,branch_id',
            'branchper_percent' => 'required|numeric|min:0|max:100',
        ]);

        $item->fill($validated);
        $item->user_updated = auth()->id();
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbpublishedbranchper.index')->with('success', 'ปรับปรุงข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbPublishedBranchper::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();

        return redirect()->route('backend.rdbpublishedbranchper.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
