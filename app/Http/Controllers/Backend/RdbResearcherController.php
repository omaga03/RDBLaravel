<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbResearcher;
use App\Models\RdbPrefix;
use App\Models\RdbDepartment;
use App\Models\RdbDepMajor;
use App\Models\RdbDepartmentCategory;
use App\Models\RdbDepartmentCourse;
use App\Models\RdbResearcherStatus;
use App\Models\User;
use App\Models\AuthAssignment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\FileUploadService;
use App\Http\Requests\Backend\StoreResearcherRequest;
use App\Http\Requests\Backend\UpdateResearcherRequest;
use App\Http\Requests\Backend\UpdateResearcherCodeIdRequest;
use App\Http\Requests\Backend\UpdateResearcherImageRequest;

use App\Traits\ExportsCsvSafely;

class RdbResearcherController extends Controller
{
    use ExportsCsvSafely;

    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbResearcher::class);
        $query = RdbResearcher::with(['prefix', 'department', 'departmentCategory', 'creator', 'updater', 'user']);

        if ($request->filled('search') || $request->filled('q')) {
            $rawQ = trim($request->search ?? $request->q);
            $cleanQ = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', $rawQ);
            $words = array_values(array_filter(preg_split('/\s+/', $cleanQ)));

            $query->where(function($w) use ($rawQ, $cleanQ, $words) {
                $w->where('rdb_researcher.researcher_fname', 'like', "%{$cleanQ}%")
                  ->orWhere('rdb_researcher.researcher_lname', 'like', "%{$cleanQ}%")
                  ->orWhere('rdb_researcher.researcher_fnameEN', 'like', "%{$cleanQ}%")
                  ->orWhere('rdb_researcher.researcher_lnameEN', 'like', "%{$cleanQ}%")
                  ->orWhere('rdb_researcher.researcher_email', 'like', "%{$rawQ}%")
                  ->orWhere('rdb_researcher.researcher_note', 'like', "%{$rawQ}%")
                  ->orWhereRaw("CONCAT(rdb_researcher.researcher_fname, ' ', rdb_researcher.researcher_lname) LIKE ?", ["%{$cleanQ}%"]);

                if (count($words) > 1) {
                    $w->orWhere(function($sub) use ($words) {
                        foreach ($words as $word) {
                            $sub->where(function($wq) use ($word) {
                                $wq->where('rdb_researcher.researcher_fname', 'like', "%{$word}%")
                                   ->orWhere('rdb_researcher.researcher_lname', 'like', "%{$word}%")
                                   ->orWhere('rdb_researcher.researcher_fnameEN', 'like', "%{$word}%")
                                   ->orWhere('rdb_researcher.researcher_lnameEN', 'like', "%{$word}%");
                            });
                        }
                    });
                }
            });
        }
        
        if ($request->filled('department_id') || $request->filled('department')) {
            $query->where('rdb_researcher.department_id', $request->department_id ?? $request->department);
        }

        if ($request->filled('depcat_id')) {
            $query->where('rdb_researcher.depcat_id', $request->depcat_id);
        }

        if ($request->filled('note') || $request->filled('researcher_note')) {
            $note = trim($request->note ?? $request->researcher_note);
            $query->where('rdb_researcher.researcher_note', 'like', "%{$note}%");
        }

        $researchers = $query->leftJoin('rdb_department', 'rdb_researcher.department_id', '=', 'rdb_department.department_id')
                             ->select('rdb_researcher.*')
                             ->orderByRaw('rdb_department.department_nameTH IS NULL ASC')
                             ->orderBy('rdb_department.department_nameTH', 'asc')
                             ->orderBy('rdb_researcher.researcher_fname', 'asc')
                             ->paginate(15);
        
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $categories = RdbDepartmentCategory::orderBy('depcat_name')->get();

        return view('backend.rdb_researcher.index', compact('researchers', 'departments', 'categories'));
    }

    /**
     * Export researchers to CSV (Excel compatible)
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', RdbResearcher::class);

        $query = RdbResearcher::with(['prefix', 'department', 'status']);

        if ($request->filled('q') || $request->filled('search')) {
            $search = trim($request->q ?? $request->search);
            $query->where(function($w) use ($search) {
                $w->where('researcher_fname', 'like', "%{$search}%")
                  ->orWhere('researcher_lname', 'like', "%{$search}%")
                  ->orWhere('researcher_fnameEN', 'like', "%{$search}%")
                  ->orWhere('researcher_lnameEN', 'like', "%{$search}%")
                  ->orWhere('researcher_email', 'like', "%{$search}%")
                  ->orWhere('researcher_codeid', 'like', "%{$search}%");
            });
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('note') || $request->filled('researcher_note')) {
            $note = trim($request->note ?? $request->researcher_note);
            $query->where('researcher_note', 'like', "%{$note}%");
        }

        $filename = 'researchers_' . date('Y-m-d_His') . '.csv';
        $headers = $this->csvHeaders($filename);

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'Code ID',
                'คำนำหน้า',
                'ชื่อ (ไทย)',
                'นามสกุล (ไทย)',
                'ชื่อ (อังกฤษ)',
                'นามสกุล (อังกฤษ)',
                'สังกัด/หน่วยงาน',
                'อีเมล',
                'โทรศัพท์',
                'มือถือ',
                'Scopus Author ID',
                'h-index',
                'ORCID'
            ]);

            $query->orderBy('researcher_fname', 'asc')->chunk(250, function ($researchers) use ($handle) {
                foreach ($researchers as $r) {
                    fputcsv($handle, $this->sanitizeCsvRow([
                        $r->researcher_id,
                        $r->researcher_codeid ?? '-',
                        $r->prefix->prefix_nameTH ?? '',
                        $r->researcher_fname,
                        $r->researcher_lname,
                        $r->researcher_fnameEN ?? '-',
                        $r->researcher_lnameEN ?? '-',
                        $r->department->department_nameTH ?? '-',
                        $r->researcher_email ?? '-',
                        $r->researcher_tel ?? '-',
                        $r->researcher_mobile ?? '-',
                        $r->scopus_authorId ?? '-',
                        $r->researcher_hindex ?? 0,
                        $r->orcid ?? '-'
                    ]));
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', RdbResearcher::class);
        $prefixes = RdbPrefix::all();
        $departments = RdbDepartment::all();
        $majors = RdbDepMajor::all();
        $categories = RdbDepartmentCategory::all();
        $courses = RdbDepartmentCourse::all();
        $statuses = RdbResearcherStatus::all();

        return view('backend.rdb_researcher.create', compact('prefixes', 'departments', 'majors', 'categories', 'courses', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreResearcherRequest $request)
    {
        $this->authorize('create', RdbResearcher::class);

        $data = $request->validated();
        
        if (!empty($data['citizen_id'])) {
            $citizenId = str_replace('-', '', $data['citizen_id']);
            if (!$this->isValidThaiCitizenId($citizenId)) {
                return redirect()->back()->withInput()->with('error', 'หมายเลขบัตรประจำตัวประชาชนไม่ถูกต้อง');
            }
            $data['researcher_codeid'] = $this->generateCodeId($citizenId);
        }

        if ($request->hasFile('fileimg')) {
            $fileInfo = $this->fileUploadService->storeFile($request->file('fileimg'), 'researchers');
            $data['rf_safe_name'] = $fileInfo['safe_name'];
            $data['rf_path'] = $fileInfo['path'];
            $data['rf_size'] = $fileInfo['size'];
            $data['rf_mime_type'] = $fileInfo['mime_type'];
            $data['researcher_picture'] = $fileInfo['original_name'];
        }

        $researcher = new RdbResearcher();
        $researcher->fill($data);
        $researcher->user_created = auth()->id();

        $researcher->save();

        return redirect()->route('backend.rdb_researcher.show', $researcher->researcher_id)->with('success', 'Researcher created successfully.');
    }

    /**
     * Helper to generate Researcher Code ID
     */
    private function generateCodeId($citizenId)
    {
        $key = ';b0ypc]try<ok-';
        $cleanId = str_replace('-', '', $citizenId);
        return md5($key . $cleanId);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $researcher = RdbResearcher::with([
            'prefix', 
            'department', 
            'major', 
            'departmentCategory', 
            'departmentCourse', 
            'status', 
            'rdbResearcherEducations',
            'creator', 'updater'
        ])->findOrFail($id);
        $this->authorize('view', $researcher);
        
        $researcher->load(['rdbProjects' => function($query) {
            $query->with(['year', 'type', 'typeSub', 'status'])
                  ->orderBy('year_id', 'desc')
                  ->orderBy('pro_budget', 'desc')
                  ->orderBy('pro_id', 'desc');
        }]);
        
        $researcher->load(['rdbPublisheds' => function($query) use ($id) {
            $query->with([
                'project',
                'pubtype'
            ])
            ->orderBy('pub_date', 'desc')
            ->orderBy('id', 'desc');
        }]);
        
        $authorTypeIds = $researcher->rdbPublisheds->pluck('pivot.pubta_id')->filter()->unique();
        if ($authorTypeIds->isNotEmpty()) {
            $authorTypes = \App\Models\RdbPublishedTypeAuthor::whereIn('pubta_id', $authorTypeIds)->get()->keyBy('pubta_id');
            foreach ($researcher->rdbPublisheds as $pub) {
                if ($pub->pivot && $pub->pivot->pubta_id && isset($authorTypes[$pub->pivot->pubta_id])) {
                    $pub->authorType = $authorTypes[$pub->pivot->pubta_id];
                }
            }
        }
        
        $utilizations = \App\Models\RdbProjectUtilize::whereHas('project.rdbProjectWorks', function($q) use ($id) {
            $q->where('researcher_id', $id);
        })->with(['project', 'changwat'])
          ->orderBy('utz_date', 'desc')
          ->get();
        
        $researcher->load(['rdbDips' => function($query) {
            $query->with(['project', 'dipType'])
                  ->orderBy('dip_request_date', 'desc')
                  ->orderBy('dip_id', 'desc');
        }]);
        
        $projectPositions = \App\Models\RdbProjectPosition::all()->keyBy('position_id');

        return view('backend.rdb_researcher.show', compact('researcher', 'utilizations', 'projectPositions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $researcher = RdbResearcher::with([
            'prefix', 
            'department', 
            'major', 
            'departmentCategory', 
            'departmentCourse', 
            'status',
            'creator', 'updater'
        ])->findOrFail($id);
        $this->authorize('update', $researcher);
        $prefixes = RdbPrefix::all();
        $departments = RdbDepartment::all();
        $majors = RdbDepMajor::all();
        $categories = RdbDepartmentCategory::all();
        $courses = RdbDepartmentCourse::all();
        $statuses = RdbResearcherStatus::all();

        return view('backend.rdb_researcher.edit', compact('researcher', 'prefixes', 'departments', 'majors', 'categories', 'courses', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResearcherRequest $request, $id)
    {
        $researcher = RdbResearcher::findOrFail($id);
        $this->authorize('update', $researcher);
        
        $data = $request->validated();
        
        if (!empty($data['citizen_id'])) {
            $citizenId = str_replace('-', '', $data['citizen_id']);
            if (!$this->isValidThaiCitizenId($citizenId)) {
                return redirect()->back()->withInput()->with('error', 'หมายเลขบัตรประจำตัวประชาชนไม่ถูกต้อง');
            }
            $data['researcher_codeid'] = $this->generateCodeId($citizenId);
        }

        if ($request->hasFile('fileimg')) {
            if ($researcher->rf_path && Storage::disk('public')->exists($researcher->rf_path)) {
                Storage::disk('public')->delete($researcher->rf_path);
            }

            $fileInfo = $this->fileUploadService->storeFile($request->file('fileimg'), 'researchers');
            $data['rf_safe_name'] = $fileInfo['safe_name'];
            $data['rf_path'] = $fileInfo['path'];
            $data['rf_size'] = $fileInfo['size'];
            $data['rf_mime_type'] = $fileInfo['mime_type'];
            $data['researcher_picture'] = $fileInfo['original_name'];
        }

        $researcher->fill($data);
        $researcher->user_updated = auth()->id();
        
        $researcher->save();

        return redirect()->route('backend.rdb_researcher.show', $id)->with('success', 'Researcher updated successfully.');
    }

    public function updateCodeId(UpdateResearcherCodeIdRequest $request, $id)
    {
        $researcher = RdbResearcher::findOrFail($id);
        $this->authorize('update', $researcher);

        $citizenId = str_replace('-', '', $request->citizen_id);
        if (!$this->isValidThaiCitizenId($citizenId)) {
            return redirect()->back()->with('error', 'หมายเลขบัตรประจำตัวประชาชนไม่ถูกต้อง (Invalid Thai Citizen ID)');
        }

        $newCodeId = $this->generateCodeId($citizenId);
        
        $duplicate = RdbResearcher::where('researcher_codeid', $newCodeId)
                                  ->where('researcher_id', '!=', $id)
                                  ->exists();

        if ($duplicate) {
            return redirect()->back()->with('error', 'ไม่สามารถบันทึกได้ : หมายเลขบัตรประชาชนนี้ถูกลงทะเบียนไว้แล้วในระบบ (Duplicate Data)');
        }

        $researcher->researcher_codeid = $newCodeId;
        $researcher->save();

        return redirect()->back()->with('success', 'Researcher Code ID updated successfully.');
    }

    public function updateImage(UpdateResearcherImageRequest $request, $id)
    {
        $researcher = RdbResearcher::findOrFail($id);
        $this->authorize('update', $researcher);

        if ($request->hasFile('fileimg')) {
            if ($researcher->rf_path && Storage::disk('public')->exists($researcher->rf_path)) {
                Storage::disk('public')->delete($researcher->rf_path);
            }
            
            $fileInfo = $this->fileUploadService->storeFile($request->file('fileimg'), 'researchers');
            
            $researcher->rf_safe_name = $fileInfo['safe_name'];
            $researcher->rf_path = $fileInfo['path'];
            $researcher->rf_size = $fileInfo['size'];
            $researcher->rf_mime_type = $fileInfo['mime_type'];
            $researcher->researcher_picture = $fileInfo['original_name'];
            $researcher->save();
        }

        return redirect()->back()->with('success', 'Profile picture updated successfully.');
    }

    public function viewProfileImage($id)
    {
        $researcher = RdbResearcher::findOrFail($id);
        $this->authorize('view', $researcher);

        $filePath = $researcher->getProfileFilePath();

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->response($filePath);
        }

        if ($filePath && file_exists(public_path($filePath))) {
            return response()->file(public_path($filePath));
        }

        abort(404, 'ไม่พบรูปภาพโปรไฟล์');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $researcher = RdbResearcher::findOrFail($id);
        $this->authorize('delete', $researcher);

        $researcher->delete();

        return redirect()->route('backend.rdb_researcher.index')->with('success', 'ลบข้อมูลนักวิจัยเรียบร้อยแล้ว');
    }

    /**
     * Sync h-index data from Scopus API
     */
    public function syncScopus($id)
    {
        $researcher = RdbResearcher::findOrFail($id);
        $this->authorize('update', $researcher);

        if (!$researcher->scopus_authorId) {
            return redirect()->back()->with('error', 'ไม่พบ Scopus Author ID สำหรับนักวิจัยท่านนี้');
        }

        $apiKey = config('services.scopus.key');
        $instToken = config('services.scopus.insttoken');

        if (!$apiKey) {
            return redirect()->back()->with('error', 'ระบบยังไม่ได้ตั้งค่า Scopus API Key ในไฟล์ .env');
        }

        try {
            $url = "https://api.elsevier.com/content/author/author_id/{$researcher->scopus_authorId}";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $headers = [
                "X-ELS-APIKey: {$apiKey}",
                "Accept: application/json"
            ];
            if ($instToken) {
                $headers[] = "X-ELS-Insttoken: {$instToken}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return redirect()->back()->with('error', 'cURL Error: ' . $curlError);
            }
            
            if ($httpCode == 401) {
                return redirect()->back()->with('error', 'ข้อผิดพลาด (401): API Key ไม่มีสิทธิ์เข้าถึง กรุณาใช้ VPN มหาวิทยาลัย หรือเพิ่ม Inst Token');
            }
            
            if ($httpCode != 200) {
                return redirect()->back()->with('error', "Scopus API Error (HTTP {$httpCode}): " . Str::limit($response, 100));
            }
            
            $data = json_decode($response, true);
            $hIndex = $data['author-retrieval-response'][0]['h-index'] ?? 0;

            $researcher->researcher_hindex = (int)$hIndex;
            $researcher->scopus_synced_at = now();
            $researcher->save();

            return redirect()->back()->with('success', 'อัปเดตข้อมูล Scopus (h-index: ' . $hIndex . ') สำเร็จ');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
    /**
     * Validate Thai Citizen ID
     */
    private function isValidThaiCitizenId($id)
    {
        if (strlen($id) != 13) return false;
        
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$id[$i] * (13 - $i);
        }
        
        $check = (11 - ($sum % 11)) % 10;
        
        return $check == (int)$id[12];
    }

    /**
     * Login As (Impersonate) a researcher directly from the researcher list or detail page.
     */
    public function impersonate(Request $request, $id)
    {
        $currentUser = auth()->user();
        if (!$currentUser->hasRole('The Creator') && $currentUser->id !== 129 && !session()->has('impersonator_id')) {
            abort(403, 'เฉพาะผู้ดูแลระบบสูงสุด (Super Admin) เท่านั้นที่สามารถสวมสิทธิ์ผู้ใช้งานได้');
        }

        $researcher = RdbResearcher::with(['user', 'prefix'])->findOrFail($id);
        $targetUser = $researcher->user;

        if (!$targetUser && !empty($researcher->researcher_email)) {
            $targetUser = User::where('email', $researcher->researcher_email)->first();
            if ($targetUser && empty($targetUser->researcher_id)) {
                $targetUser->researcher_id = $researcher->researcher_id;
                $targetUser->save();
            }
        }

        if (!$targetUser) {
            $username = $researcher->researcher_codeid ?: ('researcher_' . $researcher->researcher_id);
            if (User::where('username', $username)->exists()) {
                $username = 'res_' . $researcher->researcher_id . '_' . Str::random(4);
            }
            $targetUser = User::create([
                'username' => $username,
                'email' => $researcher->researcher_email ?: ($username . '@pcru.ac.th'),
                'password_hash' => Hash::make(Str::random(16)),
                'status' => 10,
                'researcher_id' => $researcher->researcher_id,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
            AuthAssignment::create([
                'item_name' => 'Researcher',
                'user_id' => $targetUser->id,
                'created_at' => time(),
            ]);
        }

        if ($targetUser->hasRole('The Creator') || $targetUser->id === 129) {
            return redirect()->back()->with('error', 'ไม่สามารถสวมสิทธิ์ผู้ดูแลระบบสูงสุดด้วยกันเองได้');
        }

        if ($targetUser->id === $currentUser->id) {
            return redirect()->back()->with('warning', 'ท่านกำลังใช้งานบัญชีนี้อยู่แล้ว');
        }

        if (!session()->has('impersonator_id')) {
            session()->put('impersonator_id', $currentUser->id);
            session()->put('impersonator_username', $currentUser->username);
        }

        session()->put('impersonated_user_id', $targetUser->id);
        session()->forget('active_role_scope_id');

        Auth::login($targetUser);

        $fullName = trim(($researcher->prefix->prefix_nameTH ?? '') . ' ' . $researcher->researcher_fname . ' ' . $researcher->researcher_lname);

        AuditLog::record(
            event: 'impersonate',
            auditable: $targetUser,
            remarks: "Admin '{$currentUser->username}' สวมสิทธิ์เข้าใช้งานเป็นนักวิจัย '{$fullName}' ({$targetUser->username})"
        );

        return redirect()->route('backend.site.index')
            ->with('success', "ท่านกำลังเข้าใช้งานในฐานะนักวิจัย: '{$fullName}' ({$targetUser->username})");
    }
}