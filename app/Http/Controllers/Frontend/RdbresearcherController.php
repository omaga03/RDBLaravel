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
            'department'
        ])->findOrFail($id);
        
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

    public function create()
    {
        Gate::authorize('RdbResearcher.Create');
        $data = $this->getDropdownData();
        return view('frontend.rdbresearcher.create', $data);
    }

    public function store(Request $request)
    {
        Gate::authorize('RdbResearcher.Create');
        $validated = $this->validateRequest($request);

        if ($request->hasFile('researcher_picture')) {
            $path = $request->file('researcher_picture')->store('researcher_pictures', 'public');
            $validated['researcher_picture'] = $path;
        }

        $validated['created_at'] = now();

        RdbResearcher::create($validated);

        return redirect()->route('frontend.rdbresearcher.index')->with('success', 'Researcher created successfully.');
    }

    public function edit($id)
    {
        Gate::authorize('RdbResearcher.Update');
        $item = RdbResearcher::findOrFail($id);
        $data = $this->getDropdownData();
        $data['item'] = $item;
        return view('frontend.rdbresearcher.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $item = RdbResearcher::findOrFail($id);
        $validated = $this->validateRequest($request, $id);

        if ($request->hasFile('researcher_picture')) {
            if ($item->researcher_picture) {
                Storage::disk('public')->delete($item->researcher_picture);
            }
            $path = $request->file('researcher_picture')->store('researcher_pictures', 'public');
            $validated['researcher_picture'] = $path;
        }

        $item->update($validated);

        return redirect()->route('frontend.rdbresearcher.index')->with('success', 'Researcher updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbResearcher::findOrFail($id);
        if ($item->researcher_picture) {
            Storage::disk('public')->delete($item->researcher_picture);
        }
        $item->delete();

        return redirect()->route('frontend.rdbresearcher.index')->with('success', 'Researcher deleted successfully.');
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

    private function getDropdownData()
    {
        return [
            'prefixes' => RdbPrefix::all(),
            'departments' => RdbDepartment::orderBy('department_nameTH')->get(),
            'statuses' => RdbResearcherStatus::all(),
            'majors' => RdbDepMajor::orderBy('maj_nameTH')->get(),
        ];
    }

    private function validateRequest(Request $request, $id = null)
    {
        return $request->validate([
            'prefix_id' => 'required|exists:rdb_prefix,prefix_id',
            'researcher_fname' => 'required|string|max:255',
            'researcher_lname' => 'required|string|max:255',
            'researcher_fnameEN' => 'nullable|string|max:255',
            'researcher_lnameEN' => 'nullable|string|max:255',
            'researcher_gender' => 'nullable|string|in:M,F',
            'department_id' => 'required|exists:rdb_department,department_id',
            'maj_id' => 'nullable|exists:rdb_dep_major,maj_id',
            'restatus_id' => 'nullable|exists:rdb_researcher_status,restatus_id',
            'researcher_email' => 'nullable|email|max:255',
            'researcher_tel' => 'nullable|string|max:50',
            'researcher_mobile' => 'nullable|string|max:50',
            'researcher_picture' => 'nullable|image|max:5120', // 5MB
            'scopus_authorId' => 'nullable|string|max:255',
            'orcid' => 'nullable|string|max:255',
            'researcher_note' => 'nullable|string',
        ]);
    }
}
