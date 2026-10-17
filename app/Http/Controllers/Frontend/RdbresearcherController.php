<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbResearcher;
use App\Models\RdbPrefix;
use App\Models\RdbDepartment;
use App\Models\RdbResearcherStatus;
use App\Models\RdbDepMajor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RdbresearcherController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbResearcher::with(['prefix', 'department', 'status']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('researcher_fname', 'like', "%{$search}%")
                  ->orWhere('researcher_lname', 'like', "%{$search}%")
                  ->orWhere('researcher_fnameEN', 'like', "%{$search}%")
                  ->orWhere('researcher_lnameEN', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        $items = $query->orderBy('researcher_id', 'desc')->paginate(12);
        
        $departments = RdbDepartment::orderBy('department_nameTH')->get();

        return view('frontend.rdbresearcher.index', compact('items', 'departments'));
    }

    public function show($id)
    {
        $item = RdbResearcher::with([
            'prefix', 
            'department',
            'status'
        ])->findOrFail($id);
        
        // Load educations
        $item->load(['rdbResearcherEducations' => function($query) {
            $query->orderBy('reedu_year', 'desc')->orderBy('reedu_id', 'desc');
        }]);
        
        // Load projects with proper sorting and relationships
        $item->load(['rdbProjects' => function($query) {
            $query->with(['year', 'type', 'typeSub', 'status'])
                  ->orderBy('year_id', 'desc')
                  ->orderBy('pro_budget', 'desc')
                  ->orderBy('pro_id', 'desc');
        }]);
        
        // Load publications
        $item->load(['rdbPublisheds' => function($query) use ($id) {
            $query->with([
                'project',
                'pubtype'
            ])
            ->orderBy('pub_date', 'desc')
            ->orderBy('id', 'desc');
        }]);
        
        // Load author types for publications manually
        $authorTypeIds = $item->rdbPublisheds->pluck('pivot.pubta_id')->filter()->unique();
        if ($authorTypeIds->isNotEmpty()) {
            $authorTypes = \App\Models\RdbPublishedTypeAuthor::whereIn('pubta_id', $authorTypeIds)->get()->keyBy('pubta_id');
            foreach ($item->rdbPublisheds as $pub) {
                if ($pub->pivot && $pub->pivot->pubta_id && isset($authorTypes[$pub->pivot->pubta_id])) {
                    $pub->authorType = $authorTypes[$pub->pivot->pubta_id];
                }
            }
        }
        
        // Load utilizations through projects
        $utilizations = \App\Models\RdbProjectUtilize::whereHas('project.rdbProjectWorks', function($q) use ($id) {
            $q->where('researcher_id', $id);
        })->with(['project', 'changwat'])
          ->orderBy('utz_date', 'desc')
          ->get();
        
        // Load intellectual properties
        $item->load(['rdbDips' => function($query) {
            $query->with(['project', 'dipType'])
                  ->orderBy('dip_request_date', 'desc')
                  ->orderBy('dip_id', 'desc');
        }]);
        
        // Load Project Positions lookup
        $projectPositions = \App\Models\RdbProjectPosition::all()->keyBy('position_id');

        return view('frontend.rdbresearcher.show', compact('item', 'utilizations', 'projectPositions'));
    }

    public function export(Request $request)
    {
        $query = RdbResearcher::with(['prefix', 'department', 'status']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('researcher_nameTH', 'like', "%{$search}%")
                  ->orWhere('researcher_surnameTH', 'like', "%{$search}%");
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $items = $query->orderBy('id', 'desc')->get();

        $filename = "researchers_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            fputcsv($file, ['ID', 'คำนำหน้า', 'ชื่อ (TH)', 'นามสกุล (TH)', 'หน่วยงาน', 'สถานะ']);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->researcher_id,
                    $item->prefix->prefix_nameTH ?? '-',
                    $item->researcher_nameTH,
                    $item->researcher_surnameTH,
                    $item->department->department_nameTH ?? '-',
                    $item->status->rst_name ?? '-'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

