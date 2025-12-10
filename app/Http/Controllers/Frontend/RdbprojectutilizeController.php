<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilize;
use App\Models\RdbProject;
use App\Models\RdbChangwat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RdbprojectutilizeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectUtilize::with([
            'project', 
            'project.department',
            'project.rdbProjectWorks.researcher',
            'changwat'
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('utz_detail', 'like', "%{$search}%")
                  ->orWhere('utz_leading', 'like', "%{$search}%")
                  ->orWhere('utz_department_name', 'like', "%{$search}%")
                  ->orWhereHas('project', function($query) use ($search) {
                      $query->where('pro_nameTH', 'like', "%{$search}%")
                            ->orWhereHas('rdbProjectWorks', function($q) use ($search) {
                                $q->whereHas('researcher', function($resQuery) use ($search) {
                                    $resQuery->where('researcher_fname', 'like', "%{$search}%")
                                             ->orWhere('researcher_lname', 'like', "%{$search}%")
                                             ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$search}%"]);
                                });
                            });
                  });
            });
        }

        // Date range validation
        $hasDateStart = $request->filled('date_start');
        $hasDateEnd = $request->filled('date_end');
        
        if ($hasDateStart || $hasDateEnd) {
            // Both dates must be provided
            if (!$hasDateStart || !$hasDateEnd) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'กรุณาระบุทั้งวันที่เริ่มและวันที่สิ้นสุด');
            }
            
            // End date must be >= start date
            if ($request->date_end < $request->date_start) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'วันที่สิ้นสุดต้องมากกว่าหรือเท่ากับวันที่เริ่ม');
            }
            
            $query->whereDate('utz_date', '>=', $request->date_start)
                  ->whereDate('utz_date', '<=', $request->date_end);
        }

        $project = null;
        if ($request->filled('pro_id')) {
            $query->where('pro_id', $request->pro_id);
            $project = RdbProject::find($request->pro_id);
        }

        // Sort by Date DESC, then Project Name ASC
        $query->join('rdb_project', 'rdb_project_utilize.pro_id', '=', 'rdb_project.pro_id')
              ->select('rdb_project_utilize.*')
              ->orderBy('rdb_project_utilize.utz_date', 'desc')
              ->orderBy('rdb_project.pro_nameTH', 'asc');

        $items = $query->paginate(10);
        
        return view('frontend.rdbprojectutilize.index', compact('items', 'project'));
    }

    public function show($id)
    {
        $item = RdbProjectUtilize::with([
            'project',
            'project.department',
            'project.type',
            'project.typeSub',
            'project.rdbProjectWorks' => function($query) {
                $query->orderBy('position_id', 'asc');
            },
            'project.rdbProjectWorks.researcher',
            'project.rdbProjectWorks.researcher.prefix',
            'changwat'
        ])->findOrFail($id);
        return view('frontend.rdbprojectutilize.show', compact('item'));
    }

    public function create()
    {
        $projects = RdbProject::all();
        $changwats = RdbChangwat::all();
        return view('frontend.rdbprojectutilize.create', compact('projects', 'changwats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pro_id' => 'required|exists:rdb_project,pro_id',
            'utz_year_id' => 'nullable|integer',
            'utz_year_bud' => 'nullable|integer',
            'utz_year_edu' => 'nullable|integer',
            'utz_date' => 'nullable|date',
            'utz_leading' => 'nullable|string|max:255',
            'utz_leading_position' => 'nullable|string|max:255',
            'utz_department_name' => 'nullable|string|max:255',
            'chw_id' => 'nullable|exists:rdb_changwat,chw_id',
            'utz_group' => 'nullable|integer',
            'utz_group_qa' => 'nullable|integer',
            'utz_detail' => 'nullable|string',
            'utz_budget' => 'nullable|numeric',
            'utz_count' => 'nullable|integer',
            'utz_countfile' => 'nullable|integer',
            'utz_files' => 'nullable|file|max:10240', // Max 10MB
            'data_show' => 'nullable|integer',
        ]);

        if ($request->hasFile('utz_files')) {
            $path = $request->file('utz_files')->store('utilize_files', 'public');
            $validated['utz_files'] = $path;
        }

        $validated['created_at'] = now();

        RdbProjectUtilize::create($validated);

        return redirect()->route('frontend.rdbprojectutilize.index')->with('success', 'Project Utilization created successfully.');
    }

    public function edit($id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        $projects = RdbProject::all();
        $changwats = RdbChangwat::all();
        return view('frontend.rdbprojectutilize.edit', compact('item', 'projects', 'changwats'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        
        $validated = $request->validate([
            'pro_id' => 'required|exists:rdb_project,pro_id',
            'utz_year_id' => 'nullable|integer',
            'utz_year_bud' => 'nullable|integer',
            'utz_year_edu' => 'nullable|integer',
            'utz_date' => 'nullable|date',
            'utz_leading' => 'nullable|string|max:255',
            'utz_leading_position' => 'nullable|string|max:255',
            'utz_department_name' => 'nullable|string|max:255',
            'chw_id' => 'nullable|exists:rdb_changwat,chw_id',
            'utz_group' => 'nullable|integer',
            'utz_group_qa' => 'nullable|integer',
            'utz_detail' => 'nullable|string',
            'utz_budget' => 'nullable|numeric',
            'utz_count' => 'nullable|integer',
            'utz_countfile' => 'nullable|integer',
            'utz_files' => 'nullable|file|max:10240',
            'data_show' => 'nullable|integer',
        ]);

        if ($request->hasFile('utz_files')) {
            if ($item->utz_files) {
                Storage::disk('public')->delete($item->utz_files);
            }
            $path = $request->file('utz_files')->store('utilize_files', 'public');
            $validated['utz_files'] = $path;
        }

        $item->update($validated);

        return redirect()->route('frontend.rdbprojectutilize.index')->with('success', 'Project Utilization updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        if ($item->utz_files) {
            Storage::disk('public')->delete($item->utz_files);
        }
        $item->delete();

        return redirect()->route('frontend.rdbprojectutilize.index')->with('success', 'Project Utilization deleted successfully.');
    }
}
