<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilize;
use App\Models\RdbProjectUtilizeType;
use App\Models\RdbDepartment;
use App\Models\RdbProject;
use App\Models\RdbChangwat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RdbprojectutilizeController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProjectUtilize::with([
            'project', 
            'project.department',
            'project.rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            },
            'project.rdbProjectWorks.researcher',
            'project.rdbProjectWorks.researcher.prefix',
            'changwat',
            'utilizeType'
        ]);

        if (Schema::hasColumn('rdb_project_utilize', 'data_show')) {
            $query->where(function($q) {
                $q->where('data_show', 1)->orWhereNull('data_show');
            });
        }

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $searchWords = preg_split('/[\s\/,]+/', $searchTerm);

            $query->where(function($q) use ($searchTerm, $searchWords) {
                $q->where('utz_detail', 'like', "%{$searchTerm}%")
                  ->orWhere('utz_leading', 'like', "%{$searchTerm}%")
                  ->orWhere('utz_department_name', 'like', "%{$searchTerm}%");

                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('utz_department_name', 'like', "%{$word}%")
                      ->orWhere('utz_detail', 'like', "%{$word}%");
                }

                $q->orWhereHas('project', function($query) use ($searchTerm, $searchWords) {
                    $query->where('pro_nameTH', 'like', "%{$searchTerm}%")
                          ->orWhere('pro_nameEN', 'like', "%{$searchTerm}%")
                          ->orWhere('pro_code', 'like', "%{$searchTerm}%")
                          ->orWhereHas('rdbProjectWorks', function($q) use ($searchWords) {
                              $q->whereHas('researcher', function($resQuery) use ($searchWords) {
                                  foreach ($searchWords as $word) {
                                      if (empty($word)) continue;
                                      $resQuery->where('researcher_fname', 'like', "%{$word}%")
                                               ->orWhere('researcher_lname', 'like', "%{$word}%");
                                  }
                              });
                          });
                });
            });
        }

        // Filter by Utilize Type
        if ($request->filled('utz_group')) {
            $query->where('utz_group', $request->utz_group);
        }

        // Filter by Department (of Project)
        if ($request->filled('department_id')) {
            $deptId = $request->department_id;
            $query->whereHas('project', function($pq) use ($deptId) {
                $pq->where('department_id', $deptId);
            });
        }

        // Date range validation
        $hasDateStart = $request->filled('date_start');
        $hasDateEnd = $request->filled('date_end');
        
        if ($hasDateStart || $hasDateEnd) {
            if (!$hasDateStart || !$hasDateEnd) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'กรุณาระบุทั้งวันที่เริ่มและวันที่สิ้นสุด');
            }
            
            if ($request->date_end < $request->date_start) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น');
            }
            
            $query->whereDate('utz_date', '>=', $request->date_start)
                  ->whereDate('utz_date', '<=', $request->date_end);
        }

        $project = null;
        if ($request->filled('pro_id')) {
            $query->where('pro_id', $request->pro_id);
            $project = RdbProject::find($request->pro_id);
        }

        // Sort by Date DESC, then utz_id DESC
        $query->orderBy('utz_date', 'desc')
              ->orderBy('utz_id', 'desc');

        $items = $query->paginate(10);

        // Add match sources if search
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            foreach ($items as $item) {
                $matches = [];
                if (stripos($item->utz_department_name ?? '', $searchTerm) !== false) {
                    $matches[] = 'หน่วยงาน';
                }
                if (stripos($item->utz_leading ?? '', $searchTerm) !== false) {
                    $matches[] = 'ผู้แทน/ผู้ลงนาม';
                }
                if ($item->project && stripos($item->project->pro_nameTH ?? '', $searchTerm) !== false) {
                    $matches[] = 'โครงการวิจัย';
                }
                if (stripos($item->utz_detail ?? '', $searchTerm) !== false) {
                    $matches[] = 'รายละเอียด';
                }
                $item->match_sources = $matches;
            }
        }

        $utilizeTypes = RdbProjectUtilizeType::all();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        
        return view('frontend.rdbprojectutilize.index', compact('items', 'project', 'utilizeTypes', 'departments'));
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
            'project.rdbProjectWorks.researcher.department',
            'project.rdbProjectWorks.position',
            'changwat',
            'utilizeType'
        ])->findOrFail($id);

        if (Schema::hasColumn('rdb_project_utilize', 'utz_count')) {
            $item->increment('utz_count');
        }

        return view('frontend.rdbprojectutilize.show', compact('item'));
    }
}
