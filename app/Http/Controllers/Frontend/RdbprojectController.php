<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RdbProject;
use App\Models\RdbProjectFiles;
use App\Models\RdbYear;
use App\Models\RdbDepartment;
use App\Models\RdbProjectType;
use App\Models\RdbProjectTypeSub;
use App\Models\RdbGroupproject;
use App\Models\RdbProjectStatus;
use App\Models\RdbResearcher;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RdbprojectController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbProject::with([
            'year', 
            'type', 
            'typeSub', 
            'status', 
            'department', 
            'rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            },
            'rdbProjectWorks.researcher'
        ]);

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            // Split by space, slash, or comma for multi-word search
            $searchWords = preg_split('/[\s\/,]+/', $searchTerm);
            
            $query->where(function($q) use ($searchTerm, $searchWords) {
                // Search full term
                $q->where('pro_nameTH', 'like', "%{$searchTerm}%")
                  ->orWhere('pro_nameEN', 'like', "%{$searchTerm}%")
                  ->orWhere('pro_code', 'like', "%{$searchTerm}%")
                  ->orWhere('pro_keyword', 'like', "%{$searchTerm}%")
                  ->orWhere('pro_abstract', 'like', "%{$searchTerm}%");
                
                // Also search each word individually (OR logic)
                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_nameTH', 'like', "%{$word}%")
                      ->orWhere('pro_nameEN', 'like', "%{$word}%")
                      ->orWhere('pro_keyword', 'like', "%{$word}%")
                      ->orWhere('pro_abstract', 'like', "%{$word}%");
                }
                
                // Search in related tables
                $q->orWhereHas('type', function($tq) use ($searchTerm) {
                    $tq->where('pt_name', 'like', "%{$searchTerm}%");
                });
                
                $q->orWhereHas('year', function($yq) use ($searchTerm) {
                    $yq->where('year_name', 'like', "%{$searchTerm}%");
                });
                
                $q->orWhereHas('department', function($dq) use ($searchTerm) {
                    $dq->where('department_nameTH', 'like', "%{$searchTerm}%")
                       ->orWhere('department_nameEN', 'like', "%{$searchTerm}%");
                });
                
                // Search researchers - support multi-word name search
                $q->orWhereHas('researchers', function($rq) use ($searchWords) {
                    foreach ($searchWords as $word) {
                        if (empty($word)) continue;
                        $rq->where(function($wq) use ($word) {
                            $wq->where('researcher_fname', 'like', "%{$word}%")
                               ->orWhere('researcher_lname', 'like', "%{$word}%");
                        });
                    }
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
                    ->with('error', 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น');
            }
            
            $query->whereDate('pro_date_start', '>=', $request->date_start)
                  ->whereDate('pro_date_end', '<=', $request->date_end);
        }

        // Budget filters
        // Budget filters with validation
        $hasBudgetMin = $request->filled('budget_min');
        $hasBudgetMax = $request->filled('budget_max');

        if ($hasBudgetMin && $hasBudgetMax) {
            if ($request->budget_max < $request->budget_min) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'งบประมาณสูงสุดต้องไม่น้อยกว่างบประมาณเริ่มต้น');
            }
        }

        if ($hasBudgetMin) {
            $query->where('pro_budget', '>=', $request->budget_min);
        }

        if ($hasBudgetMax) {
            $query->where('pro_budget', '<=', $request->budget_max);
        }

        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        if ($request->filled('pt_id')) {
            $query->where('pt_id', $request->pt_id);
        }

        if ($request->filled('pts_id')) {
            $query->where('pts_id', $request->pts_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Project Code
        if ($request->filled('pro_code')) {
            $query->where('pro_code', 'like', '%' . $request->pro_code . '%');
        }

        // Group
        if ($request->filled('pgroup_id')) {
            $query->where('pgroup_id', $request->pgroup_id);
        }

        // Researcher search
        if ($request->filled('researcher_id') && $request->researcher_id !== 'pre') {
            $query->whereHas('researchers', function($rq) use ($request) {
                $rq->where('rdb_researcher.researcher_id', $request->researcher_id);
            });
        } elseif ($request->filled('researcher_name')) {
            $cleanName = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', trim($request->researcher_name));
            $searchWords = preg_split('/\s+/', $cleanName);
            $query->whereHas('researchers', function($rq) use ($searchWords) {
                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    $rq->where(function($wq) use ($word) {
                        $wq->where('researcher_fname', 'like', '%' . $word . '%')
                           ->orWhere('researcher_lname', 'like', '%' . $word . '%');
                    });
                }
            });
        }

        // Project ID / Title Autocomplete Search
        if ($request->filled('pro_id') && $request->pro_id !== 'pre') {
            $query->where('pro_id', $request->pro_id);
        } elseif ($request->filled('pro_nameTH')) {
            $proName = trim($request->pro_nameTH);
            $query->where(function($q) use ($proName) {
                $q->where('pro_nameTH', 'like', "%{$proName}%")
                  ->orWhere('pro_nameEN', 'like', "%{$proName}%");
            });
        }

        // Title Multi-Keyword Search (กลุ่มคำคั่นด้วยช่องว่าง + ตัวเลือก เจอทั้งหมด/เจอบางคำ)
        if ($request->filled('title_keywords')) {
            $keywords = array_values(array_filter(preg_split('/\s+/', trim($request->title_keywords))));
            $mode = $request->input('title_keywords_mode', 'all');

            if (!empty($keywords)) {
                if ($mode === 'all') {
                    // เจอทั้งหมด (ตรงทุกคำ - AND)
                    $query->where(function($q) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $q->where(function($sub) use ($kw) {
                                $sub->where('pro_nameTH', 'like', "%{$kw}%")
                                    ->orWhere('pro_nameEN', 'like', "%{$kw}%");
                            });
                        }
                    });
                } else {
                    // เจอบางคำ (ตรงคำใดคำหนึ่ง - OR)
                    $query->where(function($q) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $q->orWhere(function($sub) use ($kw) {
                                $sub->where('pro_nameTH', 'like', "%{$kw}%")
                                    ->orWhere('pro_nameEN', 'like', "%{$kw}%");
                            });
                        }
                    });
                }
            }
        }

        // Abstract (multi-word OR search)
        if ($request->filled('pro_abstract')) {
            $words = preg_split('/[\s\/,]+/', trim($request->pro_abstract));
            $query->where(function($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_abstract', 'like', '%' . $word . '%');
                }
            });
        }

        // Keyword (multi-word OR search)
        if ($request->filled('pro_keyword')) {
            $words = preg_split('/[\s\/,]+/', trim($request->pro_keyword));
            $query->where(function($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_keyword', 'like', '%' . $word . '%');
                }
            });
        }

        // Note (multi-word OR search)
        if ($request->filled('pro_note')) {
            $words = preg_split('/[\s\/,]+/', trim($request->pro_note));
            $query->where(function($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    $q->orWhere('pro_note', 'like', '%' . $word . '%');
                }
            });
        }

        // Status
        if ($request->filled('ps_id')) {
            $query->where('ps_id', $request->ps_id);
        }

        // Sorting
        $sort = $request->get('sort', 'pro_date_start');
        $direction = $request->get('direction', 'desc');
        
        // Allow sorting by specific columns only
        $allowedSorts = ['pro_id', 'pro_nameTH', 'pro_budget', 'year_id', 'created_at', 'pro_date_start'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'pro_date_start';
        }

        $projects = $query->orderBy($sort, $direction)->paginate(10);
        
        // Add match_sources for search results tooltip
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $searchWords = preg_split('/[\s\/,]+/', $searchTerm);
            
            foreach ($projects as $project) {
                $matchSources = [];
                
                // Check project name
                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    if (stripos($project->pro_nameTH ?? '', $word) !== false || 
                        stripos($project->pro_nameEN ?? '', $word) !== false) {
                        $matchSources[] = 'ชื่อโครงการ';
                        break;
                    }
                }
                
                // Check code
                if (stripos($project->pro_code ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'รหัสโครงการ';
                }
                
                // Check abstract
                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    if (stripos($project->pro_abstract ?? '', $word) !== false) {
                        $matchSources[] = 'บทคัดย่อ';
                        break;
                    }
                }
                
                // Check keyword
                foreach ($searchWords as $word) {
                    if (empty($word)) continue;
                    if (stripos($project->pro_keyword ?? '', $word) !== false) {
                        $matchSources[] = 'คำสำคัญ';
                        break;
                    }
                }
                
                // Check type
                if ($project->type && stripos($project->type->pt_name ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'ประเภททุน';
                }
                
                // Check year
                if ($project->year && stripos($project->year->year_name ?? '', $searchTerm) !== false) {
                    $matchSources[] = 'ปีงบประมาณ';
                }
                
                // Check department
                if ($project->department && 
                    (stripos($project->department->department_nameTH ?? '', $searchTerm) !== false ||
                     stripos($project->department->department_nameEN ?? '', $searchTerm) !== false)) {
                    $matchSources[] = 'หน่วยงาน';
                }
                
                // Check researchers
                $matchedResearchers = [];
                foreach ($project->researchers as $researcher) {
                    $matched = true;
                    foreach ($searchWords as $word) {
                        if (empty($word)) continue;
                        if (stripos($researcher->researcher_fname ?? '', $word) === false &&
                            stripos($researcher->researcher_lname ?? '', $word) === false) {
                            $matched = false;
                            break;
                        }
                    }
                    if ($matched) {
                        $matchedResearchers[] = ($researcher->researcher_fname ?? '') . ' ' . ($researcher->researcher_lname ?? '');
                    }
                }
                if (!empty($matchedResearchers)) {
                    $matchSources[] = 'นักวิจัย: ' . implode(', ', $matchedResearchers);
                }
                
                $project->match_sources = $matchSources;
            }
        }
        
        $years = RdbYear::orderBy('year_name', 'desc')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();
        $types = RdbProjectType::orderBy('pt_name')->get();
        $groups = RdbGroupproject::all();
        $statuses = RdbProjectStatus::all();

        return view('frontend.rdbproject.index', compact('projects', 'years', 'departments', 'types', 'groups', 'statuses'));
    }

    // API endpoint for getting types by year
    public function getTypesByYear(Request $request)
    {
        $yearId = $request->get('year_id');
        
        if (!$yearId) {
            return response()->json([]);
        }

        // Get types that have projects in this year
        $types = RdbProjectType::whereHas('rdbProjects', function($query) use ($yearId) {
            $query->where('year_id', $yearId);
        })->orderBy('pt_name')->get();

        $results = $types->map(function($t) {
            return ['id' => $t->pt_id, 'text' => $t->pt_name];
        });

        return response()->json(['results' => $results]);
    }

    public function getSubTypesByType(Request $request)
    {
        $ptId = $request->get('pt_id');
        
        if (!$ptId) {
            return response()->json([]);
        }

        $subTypes = RdbProjectTypeSub::where('pt_id', $ptId)
            ->orderBy('pts_name')
            ->get();

        $results = $subTypes->map(function($s) {
            return ['id' => $s->pts_id, 'text' => $s->pts_name];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Search researchers for Frontend TomSelect
     */
    public function searchResearchers(Request $request)
    {
        $term = trim($request->q);

        if (empty($term)) {
            return response()->json([]);
        }

        $researchers = \App\Models\RdbResearcher::with('prefix')
            ->where(function($q) use ($term) {
                $q->where('researcher_fname', 'LIKE', "%{$term}%")
                  ->orWhere('researcher_lname', 'LIKE', "%{$term}%")
                  ->orWhere('researcher_fnameEN', 'LIKE', "%{$term}%")
                  ->orWhere('researcher_lnameEN', 'LIKE', "%{$term}%");
            })
            ->limit(20)
            ->get();

        $results = [];
        foreach ($researchers as $r) {
            $prefix = $r->prefix ? $r->prefix->prefix_nameTH : '';
            $nameTH = $prefix . $r->researcher_fname . ' ' . $r->researcher_lname;
            
            $dept = $r->department ? $r->department->department_nameTH : '-';
            
            $text = "{$nameTH} [{$dept}]";

            $results[] = [
                'value' => $r->researcher_id,
                'text' => $text
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Search projects for Frontend TomSelect Autocomplete
     */
    public function searchProjects(Request $request)
    {
        $term = trim($request->q);
        if (empty($term) || mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $projects = RdbProject::with([
                'year', 
                'department', 
                'type', 
                'typeSub', 
                'rdbProjectWorks' => function($wq) {
                    $wq->orderBy('position_id', 'asc');
                },
                'rdbProjectWorks.researcher.prefix'
            ])
            ->where(function($q) use ($term) {
                $q->where('pro_nameTH', 'LIKE', "%{$term}%")
                  ->orWhere('pro_nameEN', 'LIKE', "%{$term}%")
                  ->orWhere('pro_code', 'LIKE', "%{$term}%")
                  ->orWhereHas('type', function($tq) use ($term) {
                      $tq->where('pt_name', 'LIKE', "%{$term}%");
                  })
                  ->orWhereHas('typeSub', function($tsq) use ($term) {
                      $tsq->where('pts_name', 'LIKE', "%{$term}%");
                  })
                  ->orWhereHas('researchers', function($rq) use ($term) {
                      $rq->where('researcher_fname', 'LIKE', "%{$term}%")
                         ->orWhere('researcher_lname', 'LIKE', "%{$term}%")
                         ->orWhere('researcher_fnameEN', 'LIKE', "%{$term}%")
                         ->orWhere('researcher_lnameEN', 'LIKE', "%{$term}%")
                         ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$term}%"]);
                  });
            })
            ->orderBy('pro_id', 'desc')
            ->limit(20)
            ->get();

        $results = [];
        foreach ($projects as $p) {
            $yearPrefix = ($p->year && $p->year->year_name) ? "[{$p->year->year_name}] " : '';
            $typeName = $p->type ? $p->type->pt_name : '';
            if ($p->typeSub && $p->typeSub->pts_name) {
                $typeName .= ($typeName ? ' (' . $p->typeSub->pts_name . ')' : $p->typeSub->pts_name);
            }
            $dept = $p->department ? ($p->department->department_nameTH ?? $p->department->department_name) : '';

            $researcherNames = [];
            if ($p->rdbProjectWorks && $p->rdbProjectWorks->isNotEmpty()) {
                foreach ($p->rdbProjectWorks as $pw) {
                    if ($pw->researcher) {
                        $researcherNames[] = trim($pw->researcher->researcher_fname . ' ' . $pw->researcher->researcher_lname);
                    }
                }
            } elseif ($p->researchers && $p->researchers->isNotEmpty()) {
                foreach ($p->researchers as $r) {
                    $researcherNames[] = trim($r->researcher_fname . ' ' . $r->researcher_lname);
                }
            }
            $resText = !empty($researcherNames) ? implode(', ', array_slice($researcherNames, 0, 2)) . (count($researcherNames) > 2 ? '...' : '') : '';

            $meta = array_filter([
                $typeName ? "ทุน: {$typeName}" : '', 
                $dept, 
                $resText ? "ผู้วิจัย: {$resText}" : ''
            ]);
            $sub = !empty($meta) ? ' [' . implode(', ', $meta) . ']' : '';

            $cleanName = strip_tags($p->pro_nameTH);
            $results[] = [
                'value' => $p->pro_id,
                'text' => $yearPrefix . $cleanName . $sub,
                'pro_nameTH' => $cleanName,
                'pt_name' => $typeName,
                'researcher_name' => $resText,
            ];
        }

        return response()->json($results);
    }

    /**
     * Track file download and redirect to file (Frontend)
     */
    public function downloadFile($id, $fid)
    {
        $project = RdbProject::findOrFail($id);

        // ✅ ตรวจสอบ public status
        if ($project->data_show != 1) {
            abort(404);
        }

        $fileRecord = RdbProjectFiles::where('pro_id', $id)
            ->where('id', $fid)
            ->firstOrFail();

        // ✅ ตรวจสอบว่าไฟล์เปิดให้สาธารณะหรือไม่
        if ($fileRecord->rf_files_show != 1) {
            abort(404, 'ไฟล์นี้ไม่เปิดให้สาธารณะ');
        }

        // 🛡️ ตรวจสอบนโยบายความปลอดภัยการดาวน์โหลดไฟล์แนบ (Layer 2 Security Policy)
        $attachPolicy = SystemSetting::get('download_policy_attachments', 'members_only');
        if ($attachPolicy === 'members_only' && !auth()->check()) {
            return redirect()->route('login')->with('warning', 'กรุณาเข้าสู่ระบบก่อนเพื่อดาวน์โหลดไฟล์เอกสารแนบ');
        }
        if ($attachPolicy === 'admin_only') {
            if (!auth()->check()) {
                return redirect()->route('login')->with('warning', 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถดาวน์โหลดไฟล์นี้ได้');
            }
            $user = auth()->user();
            if (!$user->hasRole('The Creator') && !$user->hasRole('Admin')) {
                abort(403, 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถดาวน์โหลดไฟล์นี้ได้');
            }
        }

        $filePath = $fileRecord->getFilePath();
        
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $fileRecord->increment('rf_download');
            return Storage::disk('public')->download($filePath);
        } elseif ($filePath && file_exists(public_path($filePath))) {
            $fileRecord->increment('rf_download');
            return response()->download(public_path($filePath));
        }

        abort(404, 'ไฟล์ไม่พบ');
    }

    public function viewAbstract($id)
    {
        $project = RdbProject::findOrFail($id);

        // ✅ ตรวจสอบ public status
        if ($project->data_show != 1) {
            abort(404);
        }

        // 🛡️ ตรวจสอบนโยบายการเปิดอ่านบทคัดย่อ
        $absPolicy = SystemSetting::get('download_policy_abstract', 'public');
        if ($absPolicy === 'members_only' && !auth()->check()) {
            return redirect()->route('login')->with('warning', 'กรุณาเข้าสู่ระบบก่อนเพื่อเปิดอ่านบทคัดย่อ');
        }

        $filePath = $project->getAbstractFilePath();
        
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $project->increment('pro_count_abs');
            return Storage::disk('public')->response($filePath);
        } elseif ($filePath && file_exists(public_path($filePath))) {
            $project->increment('pro_count_abs');
            return response()->file(public_path($filePath));
        }

        // Support legacy folder: datafiles/{pro_id}/{pro_abstract_file}
        if (!empty($project->pro_abstract_file)) {
            $legacyRelative = 'datafiles/' . $project->pro_id . '/' . $project->pro_abstract_file;
            if (Storage::disk('public')->exists($legacyRelative)) {
                $project->increment('pro_count_abs');
                return Storage::disk('public')->response($legacyRelative);
            } elseif (file_exists(public_path($legacyRelative))) {
                $project->increment('pro_count_abs');
                return response()->file(public_path($legacyRelative));
            }
        }

        abort(404, 'ไม่พบไฟล์บทคัดย่อบนเซิร์ฟเวอร์');
    }

    public function viewReport($id)
    {
        $project = RdbProject::findOrFail($id);

        // ✅ ตรวจสอบ public status
        if ($project->data_show != 1) {
            abort(404);
        }

        // ✅ ตรวจสอบว่า report แสดงในหน้าสาธารณะหรือไม่ (0 = เปิด, 1 = ปิด, 2 = ร่าง)
        if ((int) $project->pro_file_show !== 0) {
            if ((int) $project->pro_file_show === 1) {
                abort(403, 'ไฟล์รายงานนี้ถูกปกปิด ไม่สามารถเปิดเผยข้อมูลได้');
            }
            if ((int) $project->pro_file_show === 2) {
                abort(403, 'ไฟล์รายงานนี้อยู่ในสถานะฉบับร่าง ยังไม่เปิดให้ดาวน์โหลด');
            }
            abort(404, 'ไฟล์รายงานยังไม่เปิดให้สาธารณะ');
        }

        // 🛡️ ตรวจสอบนโยบายความปลอดภัยการดาวน์โหลดเล่มรายงานฉบับสมบูรณ์ (System Policy - Layer 2 Security)
        $reportPolicy = SystemSetting::get('download_policy_full_report', 'members_only');
        if ($reportPolicy === 'members_only' && !auth()->check()) {
            $msg = SystemSetting::get('download_require_login_message', 'กรุณาเข้าสู่ระบบก่อนเพื่อดาวน์โหลดเอกสารเล่มรายงานวิจัยฉบับสมบูรณ์');
            return redirect()->route('login')->with('warning', $msg);
        }
        if ($reportPolicy === 'admin_only') {
            if (!auth()->check()) {
                return redirect()->route('login')->with('warning', 'เฉพาะผู้ดูแลระบบและเจ้าของโครงการเท่านั้น');
            }
            $user = auth()->user();
            $isOwner = $user->researcher_id && $project->researchers()->where('rdb_researcher.researcher_id', $user->researcher_id)->exists();
            if (!$user->hasRole('The Creator') && !$user->hasRole('Admin') && !$isOwner) {
                abort(403, 'เฉพาะผู้ดูแลระบบและคณะผู้วิจัยของโครงการเท่านั้นที่สามารถเข้าถึงไฟล์นี้ได้');
            }
        }

        $filePath = $project->getReportFilePath();
        
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            $project->increment('pro_count_full');
            return Storage::disk('public')->response($filePath);
        } elseif ($filePath && file_exists(public_path($filePath))) {
            $project->increment('pro_count_full');
            return response()->file(public_path($filePath));
        }

        // Support legacy folder: datafiles/{pro_id}/{pro_file}
        if (!empty($project->pro_file)) {
            $legacyRelative = 'datafiles/' . $project->pro_id . '/' . $project->pro_file;
            if (Storage::disk('public')->exists($legacyRelative)) {
                $project->increment('pro_count_full');
                return Storage::disk('public')->response($legacyRelative);
            } elseif (file_exists(public_path($legacyRelative))) {
                $project->increment('pro_count_full');
                return response()->file(public_path($legacyRelative));
            }
        }

        abort(404, 'ไม่พบไฟล์รายงานบนเซิร์ฟเวอร์');
    }

    public function show($id)
    {
        $item = RdbProject::with([
            'year', 
            'type', 
            'typeSub',
            'status', 
            'department', 
            'strategic', 
            'researchers', 
            'group',
            'rdbProjectWorks' => function($q) {
                $q->orderBy('position_id', 'asc');
            },
            'rdbProjectWorks.researcher.prefix',
            'rdbProjectWorks.researcher.department',
            'rdbProjectWorks.position',
            'files',
            'utilizations',
            'publisheds',
            'dips',
            'createdBy.researcher',
            'updatedBy.researcher'
        ])->findOrFail($id);
        return view('frontend.rdbproject.show', compact('item'));
    }
}
