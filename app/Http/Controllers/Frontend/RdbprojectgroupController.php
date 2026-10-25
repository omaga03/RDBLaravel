<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProject;
use App\Models\RdbProjectStatus;
use App\Models\RdbYear;
use Illuminate\Http\Request;

/**
 * แผนงานวิจัย (Project Group) — สาธารณะ
 * โครงการแม่ (pgroup_id = 1) พร้อมโครงการย่อย (pro_group) และงบประมาณรวม
 */
class RdbprojectgroupController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProject::with(['year', 'department', 'type', 'status'])
            ->where('pgroup_id', 1)
            ->whereNotNull('pro_group')
            ->where('pro_group', '<>', '')
            ->where(fn($q) => $q->whereNull('ps_id')->orWhere('ps_id', '<>', RdbProjectStatus::REFUNDED))
            ->withCount(['subProjects'])
            ->selectRaw('rdb_project.*, (SELECT COALESCE(SUM(s.pro_budget),0) FROM rdb_project s WHERE s.pro_group = rdb_project.pro_id AND s.data_show = 1) as group_budget');

        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(fn($q) => $q->where('pro_nameTH', 'like', "%{$s}%")->orWhere('pro_nameEN', 'like', "%{$s}%"));
        }

        $items = $query->orderByDesc('year_id')->orderBy('pro_nameTH')->paginate(15)->withQueryString();
        $years = RdbYear::orderBy('year_name', 'desc')->get();

        return view('frontend.rdbprojectgroup.index', compact('items', 'years'));
    }

    public function show($id)
    {
        $item = RdbProject::with(['year', 'department', 'type', 'status'])
            ->where('pgroup_id', 1)
            ->findOrFail($id);

        $subProjects = RdbProject::with(['department', 'status', 'rdbProjectWorks.researcher.prefix'])
            ->where('pro_group', $item->pro_id)
            ->where('pro_id', '<>', $item->pro_id)
            ->orderBy('pro_nameTH')
            ->get();

        $totalBudget = (float) $item->pro_budget + (float) $subProjects->sum('pro_budget');

        return view('frontend.rdbprojectgroup.show', compact('item', 'subProjects', 'totalBudget'));
    }
}
