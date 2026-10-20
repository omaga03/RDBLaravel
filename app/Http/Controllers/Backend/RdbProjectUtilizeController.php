<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectUtilize;
use App\Models\RdbChangwat;
use App\Models\RdbProject;
use App\Models\RdbProjectUtilizeType;
use App\Models\RdbPublishedCheckyear;
use App\Models\RdbYear;
use Illuminate\Http\Request;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Backend\StoreProjectUtilizeRequest;
use App\Http\Requests\Backend\UpdateProjectUtilizeRequest;

use App\Traits\ExportsCsvSafely;

class RdbProjectUtilizeController extends Controller
{
    use ExportsCsvSafely;

    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    protected function getFilteredQuery(Request $request)
    {
        $query = RdbProjectUtilize::with(['project.rdbProjectWorks.researcher', 'changwat', 'utilizeType', 'createdBy', 'updatedBy']);

        // Apply RBAC Scoping:
        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->hasRole('The Creator') && !$currentUser->hasRole('Admin') && !$currentUser->hasRole('Admin Utilize')) {
            if ($currentUser->hasRole('Faculty Coordinator')) {
                $deptId = $currentUser->getActiveRoleScope()?->department_id ?? $currentUser->researcher?->department_id;
                if ($deptId) {
                    $query->whereHas('project', function($q) use ($deptId) {
                        $q->where('department_id', $deptId);
                    });
                }
            } elseif ($currentUser->hasRole('Researcher') || $currentUser->researcher_id) {
                $researcherId = $currentUser->researcher_id;
                $query->where(function($q) use ($researcherId, $currentUser) {
                    $q->where('user_created', $currentUser->id);
                    if ($researcherId) {
                        $q->orWhereHas('project.rdbProjectWorks', function($wq) use ($researcherId) {
                            $wq->where('rdb_project_work.researcher_id', $researcherId);
                        });
                    }
                });
            }
        }

        // Workflow Status filter
        if ($request->filled('workflow_status')) {
            if ($request->workflow_status === 'unapproved') {
                $query->whereIn('workflow_status', ['draft', 'pending_review', 'rejected']);
            } else {
                $query->where('workflow_status', $request->workflow_status);
            }
        }
        
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($qry) use ($q) {
                $qry->where('utz_department_name', 'like', "%{$q}%")
                    ->orWhere('utz_leading', 'like', "%{$q}%")
                    ->orWhereHas('project', function($p) use ($q) {
                        $p->where('pro_nameTH', 'like', "%{$q}%")
                          ->orWhere('pro_code', 'like', "%{$q}%");
                    });
            });
        }

        if ($request->search_mode === 'advanced') {
            if ($request->filled('utz_department_name')) {
                $query->where('utz_department_name', 'like', '%' . $request->utz_department_name . '%');
            }
            if ($request->filled('utz_leading')) {
                $query->where('utz_leading', 'like', '%' . $request->utz_leading . '%');
            }
            
            if ($request->filled('utz_group')) {
                $group = $request->utz_group;
                $query->where(function($q) use ($group) {
                    $q->whereRaw("FIND_IN_SET(?, utz_group)", [$group]);
                });
            }

            if ($request->filled('chw_id')) {
                $query->where('chw_id', $request->chw_id);
            } elseif ($request->filled('changwat')) {
                $query->whereHas('changwat', function($q) use ($request) {
                    $q->where('changwat_t', 'like', '%' . $request->changwat . '%');
                    if ($request->filled('amphoe')) {
                        $q->where('amphoe_t', 'like', '%' . $request->amphoe . '%');
                    }
                });
            }

            if ($request->filled('year_id')) {
                $query->where('utz_year_id', $request->year_id);
            }
            if ($request->filled('date_start')) {
                $query->whereDate('utz_date', '>=', $request->date_start);
            }
            if ($request->filled('date_end')) {
                $query->whereDate('utz_date', '<=', $request->date_end);
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbProjectUtilize::class);
        $query = $this->getFilteredQuery($request);

        $items = $query->orderBy('utz_date', 'desc')->paginate(20);

        return view('backend.rdb_projectutilize.index', compact('items'));
    }

    /**
     * Export project utilizations to CSV (Excel compatible with formula injection protection)
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', RdbProjectUtilize::class);
        $query = $this->getFilteredQuery($request);

        $filename = 'project_utilizations_' . date('Y-m-d_His') . '.csv';
        $headers = $this->csvHeaders($filename);

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'รหัสโครงการ',
                'ชื่อโครงการวิจัย',
                'หน่วยงานที่นำไปใช้',
                'ผู้รับผิดชอบ/ผู้นำไปใช้',
                'ตำแหน่ง',
                'จังหวัด/พื้นที่',
                'ประเภทการใช้ประโยชน์',
                'วันที่นำไปใช้',
                'งบประมาณ/มูลค่าผลกระทบ (บาท)'
            ]);

            $query->orderBy('utz_id', 'desc')->chunk(250, function ($items) use ($handle) {
                foreach ($items as $item) {
                    fputcsv($handle, $this->sanitizeCsvRow([
                        $item->utz_id,
                        $item->project->pro_code ?? '-',
                        strip_tags(html_entity_decode($item->project->pro_nameTH ?? '-', ENT_QUOTES, 'UTF-8')),
                        strip_tags(html_entity_decode($item->utz_department_name ?? '-', ENT_QUOTES, 'UTF-8')),
                        strip_tags(html_entity_decode($item->utz_leading ?? '-', ENT_QUOTES, 'UTF-8')),
                        $item->utz_leading_position ?? '-',
                        $item->changwat->changwat_t ?? '-',
                        $item->utilizeType->utz_type_name ?? '-',
                        $item->utz_date ?? '-',
                        $item->utz_budget ?? 0
                    ]));
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function create()
    {
        $this->authorize('create', RdbProjectUtilize::class);
        return view('backend.rdb_projectutilize.create');
    }

    public function store(StoreProjectUtilizeRequest $request)
    {
        $this->authorize('create', RdbProjectUtilize::class);
        
        $data = $request->validated();

        if (isset($data['utz_budget'])) {
            $data['utz_budget'] = str_replace(',', '', $data['utz_budget']);
        }

        if (isset($data['utz_group']) && is_array($data['utz_group'])) {
            $data['utz_group'] = implode(',', $data['utz_group']);
        } else {
            $data['utz_group'] = null;
        }

        $data['user_created'] = auth()->id();
        $data['data_show'] = 1;
        
        $years = $this->calculateYears($data['utz_date'] ?? date('Y-m-d'));
        $data = array_merge($data, $years);

        if ($request->hasFile('utz_files_upload')) {
            $fileInfo = $this->fileUploadService->storeFile($request->file('utz_files_upload'), 'utilize');
            $data['uf_safe_name'] = $fileInfo['safe_name'];
            $data['uf_path'] = $fileInfo['path'];
            $data['uf_size'] = $fileInfo['size'];
            $data['uf_mime_type'] = $fileInfo['mime_type'];
            $data['utz_files'] = $fileInfo['safe_name'];
        }

        $item = RdbProjectUtilize::create($data);

        return redirect()->route('backend.rdbprojectutilize.show', $item->utz_id)->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }


    public function show($id)
    {
        $item = RdbProjectUtilize::with([
            'project', 
            'changwat', 
            'utilizeType',
            'createdBy.researcher',
            'updatedBy.researcher'
        ])->findOrFail($id);
        $this->authorize('view', $item);

        $item->increment('utz_count');

        return view('backend.rdb_projectutilize.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbProjectUtilize::with([
            'project.year', 
            'project.department',
            'project.rdbProjectWorks.researcher',
            'createdBy',
            'updatedBy'
        ])->findOrFail($id);
        $this->authorize('update', $item);
        
        return view('backend.rdb_projectutilize.edit', compact('item'));
    }

    public function update(UpdateProjectUtilizeRequest $request, $id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        $this->authorize('update', $item);
        
        $data = $request->validated();

        if (isset($data['utz_budget'])) {
            $data['utz_budget'] = str_replace(',', '', $data['utz_budget']);
        }

        if (isset($data['utz_group']) && is_array($data['utz_group'])) {
            $data['utz_group'] = implode(',', $data['utz_group']);
        } else {
            $data['utz_group'] = null;
        }

        $data['user_updated'] = auth()->id();

        if (isset($data['utz_date'])) {
            $years = $this->calculateYears($data['utz_date']);
            $data = array_merge($data, $years);
        }

        if ($request->hasFile('utz_files_upload')) {
            if ($item->uf_path && Storage::disk('public')->exists($item->uf_path)) {
                Storage::disk('public')->delete($item->uf_path);
            }
            $fileInfo = $this->fileUploadService->storeFile($request->file('utz_files_upload'), 'utilize');
            $data['uf_safe_name'] = $fileInfo['safe_name'];
            $data['uf_path'] = $fileInfo['path'];
            $data['uf_size'] = $fileInfo['size'];
            $data['uf_mime_type'] = $fileInfo['mime_type'];
            $data['utz_files'] = $fileInfo['safe_name'];
        }

        $item->update($data);

        return redirect()->route('backend.rdbprojectutilize.show', $item->utz_id)->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        $this->authorize('delete', $item);
        
        $item->data_show = 0;
        $item->user_updated = auth()->id();
        $item->save();

        return redirect()->route('backend.rdbprojectutilize.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว (ซ่อนการแสดงผล)');
    }

    public function downloadFile($id, $filename)
    {
        $item = RdbProjectUtilize::findOrFail($id);
        $this->authorize('view', $item);
        
        $filePath = null;

        // 1. Try getUtilizeFilePath from model
        $candidate = $item->getUtilizeFilePath();
        if ($candidate && (Storage::disk('public')->exists($candidate) || file_exists(public_path($candidate)))) {
            $filePath = $candidate;
        }

        // 2. Fallback to specific $filename under uploads/utilize
        if (!$filePath && $filename) {
            $safeName = basename(trim($filename));
            if ($safeName) {
                if (Storage::disk('public')->exists('uploads/utilize/' . $safeName)) {
                    $filePath = 'uploads/utilize/' . $safeName;
                } elseif (file_exists(public_path('uploads/utilize/' . $safeName))) {
                    $filePath = 'uploads/utilize/' . $safeName;
                }
            }
        }

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $item->increment('utz_countfile');
            return Storage::disk('public')->response($filePath);
        }

        if ($filePath && file_exists(public_path($filePath))) {
            $item->increment('utz_countfile');
            $fullPath = public_path($filePath);
            $mimeType = mime_content_type($fullPath) ?: 'application/pdf';
            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        }

        abort(404, 'ไม่พบไฟล์');
    }
    
    public function searchProvinces()
    {
        $this->authorize('viewAny', RdbProjectUtilize::class);
        $provinces = RdbChangwat::select('changwat_t')
            ->distinct()
            ->whereNotNull('changwat_t')
            ->where('changwat_t', '!=', '')
            ->orderByRaw("CASE WHEN changwat_t = 'จ.เพชรบูรณ์' THEN 0 WHEN changwat_t = 'จ.พิจิตร' THEN 1 ELSE 2 END")
            ->orderBy('changwat_t')
            ->pluck('changwat_t')
            ->map(fn($p) => ['value' => $p, 'text' => preg_replace('/^จ\./', '', $p)]);

        return response()->json($provinces);
    }

    public function searchAmphoes(Request $request)
    {
        $this->authorize('viewAny', RdbProjectUtilize::class);
        $changwat = $request->get('changwat');
        if (!$changwat) {
            return response()->json([]);
        }

        $amphoes = RdbChangwat::select('amphoe_t')
            ->distinct()
            ->where('changwat_t', $changwat)
            ->whereNotNull('amphoe_t')
            ->where('amphoe_t', '!=', '')
            ->orderBy('amphoe_t')
            ->pluck('amphoe_t')
            ->map(fn($a) => ['value' => $a, 'text' => preg_replace('/^อ\./', '', $a)]);

        return response()->json($amphoes);
    }
    
    public function searchLocation(Request $request)
    {
        $this->authorize('viewAny', RdbProjectUtilize::class);
        $q = $request->get('q');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $locations = RdbChangwat::where('tambon_t', 'like', "%{$q}%")
            ->orWhere('amphoe_t', 'like', "%{$q}%")
            ->orWhere('changwat_t', 'like', "%{$q}%")
            ->limit(20)
            ->get()
            ->map(fn($loc) => [
                'id' => $loc->id,
                'value' => $loc->id,
                'text' => "{$loc->tambon_t} {$loc->amphoe_t} {$loc->changwat_t}",
                'changwat' => $loc->changwat_t,
                'amphoe' => $loc->amphoe_t,
                'tambon' => $loc->tambon_t
            ]);

        return response()->json($locations);
    }

    private function calculateYears($date)
    {
        $eduYear = RdbPublishedCheckyear::where('rdbyearedu_start', '<=', $date)
            ->where('rdbyearedu_end', '>=', $date)
            ->first();

        $budYear = RdbPublishedCheckyear::where('rdbyearbud_start', '<=', $date)
            ->where('rdbyearbud_end', '>=', $date)
            ->first();

        $utz_year_edu = $eduYear->year_id ?? null;
        $utz_year_bud = $budYear->year_id ?? null;
        
        $utz_year_id = $utz_year_bud ?? $utz_year_edu;

        return [
            'utz_year_id' => $utz_year_id,
            'utz_year_bud' => $utz_year_bud,
            'utz_year_edu' => $utz_year_edu,
        ];
    }

    public function searchTambons(Request $request)
    {
        $this->authorize('viewAny', RdbProjectUtilize::class);
        $changwat = $request->get('changwat');
        $amphoe = $request->get('amphoe');
        $withId = $request->get('with_id');
        
        if (!$changwat || !$amphoe) {
            return response()->json([]);
        }

        $query = RdbChangwat::where('changwat_t', $changwat)
            ->where('amphoe_t', $amphoe)
            ->whereNotNull('tambon_t')
            ->where('tambon_t', '!=', '')
            ->orderBy('tambon_t');
        
        if ($withId) {
            $tambons = $query->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'value' => $t->id, 
                    'text' => preg_replace('/^ต\./', '', $t->tambon_t)
                ]);
        } else {
            $tambons = $query->select('tambon_t')
                ->distinct()
                ->pluck('tambon_t')
                ->map(fn($t) => ['value' => $t, 'text' => preg_replace('/^ต\./', '', $t)]);
        }

        return response()->json($tambons);
    }

    public function searchYears()
    {
        $this->authorize('viewAny', RdbProjectUtilize::class);
        $years = RdbYear::orderBy('year_id', 'desc')
            ->get()
            ->map(fn($y) => [
                'value' => $y->year_id, 
                'text' => 'พ.ศ. ' . ($y->year_name ?? $y->year_id)
            ]);

        return response()->json($years);
    }

    public function searchUtilizeTypes()
    {
        $this->authorize('viewAny', RdbProjectUtilize::class);
        $types = RdbProjectUtilizeType::orderBy('utz_type_id', 'asc')
            ->get()
            ->map(fn($t) => [
                'value' => $t->utz_type_id, 
                'text' => $t->utz_type_id . ' • ' . $t->utz_typr_name
            ]);

        return response()->json($types);
    }

    public function searchProjects(Request $request)
    {
        $this->authorize('viewAny', RdbProjectUtilize::class);
        $q = $request->get('q', '');
        
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $projects = RdbProject::with(['year', 'department', 'rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            }, 'rdbProjectWorks.researcher'])
            ->where('pro_nameTH', 'like', "%{$q}%")
            ->orWhere('pro_nameEN', 'like', "%{$q}%")
            ->orWhere('pro_code', 'like', "%{$q}%")
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($p) => [
                'value' => $p->pro_id,
                'text' => $this->formatProjectText($p)
            ]);

        return response()->json($projects);
    }

    private function formatProjectText($project)
    {
        $year = $project->year->year_name ?? '-';
        $name = strip_tags($project->pro_nameTH);
        
        $researcherName = '-';
        if ($project->rdbProjectWorks->isNotEmpty()) {
            $mainWork = $project->rdbProjectWorks->first();
            if ($mainWork && $mainWork->researcher) {
                $researcherName = $mainWork->researcher->researcher_fname . ' ' . $mainWork->researcher->researcher_lname;
            }
        }
        
        return "{$year} • {$name} • {$researcherName}";
    }
}