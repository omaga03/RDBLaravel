<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartment;
use App\Models\RdbProject;
use App\Models\RdbResearcher;
use App\Models\RdbReviewAssignment;
use App\Models\RdbReviewClarification;
use App\Models\RdbReviewCriteria;
use App\Models\RdbReviewSubmission;
use App\Models\RdbReviewTemplate;
use App\Models\RdbYear;
use App\Models\User;
use App\Services\ProjectReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RdbProjectReviewController extends Controller
{
    public function __construct(
        protected ProjectReviewService $reviewService
    ) {}

    /**
     * GET /backend/project-reviews
     * Admin Hub & Researcher Overview
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $this->reviewService->ensureDefaultTemplatesExist();

        $isAdmin = $this->reviewService->canManageReview($user);
        $statusFilter = $request->input('status');
        $yearFilter = $request->input('year_id');
        $deptFilter = $request->input('department_id');
        $search = trim((string) $request->input('search', ''));

        $submissionsQuery = RdbReviewSubmission::with([
            'project.year',
            'project.department',
            'project.type',
            'project.rdbProjectWorks.researcher.prefix',
            'submitter',
        ])->orderByDesc('updated_at')->orderByDesc('id');

        if ($isAdmin) {
            $submissionsQuery->with([
                'assignments.reviewer.researcher.prefix',
                'assignments.researcher.prefix',
            ]);
        } else {
            // Strict Single-Blind Data Minimization: Never load reviewer_id, researcher_id, or reviewer relations for non-admin users
            $submissionsQuery->with(['assignments:id,submission_id,status']);
        }

        if (!$isAdmin) {
            if ($user->hasRole('Faculty Coordinator')) {
                $deptId = $user->getActiveRoleScope()?->department_id ?? $user->researcher?->department_id;
                $submissionsQuery->whereHas('project', function ($q) use ($deptId, $user) {
                    $q->where('department_id', $deptId)
                      ->orWhere('user_created', $user->id);
                });
            } else {
                $researcherId = $user->researcher_id;
                $submissionsQuery->where(function ($q) use ($user, $researcherId) {
                    $q->where('submitted_by', $user->id)
                      ->orWhereHas('project', function ($pq) use ($user, $researcherId) {
                          $pq->where('user_created', $user->id);
                          if ($researcherId) {
                              $pq->orWhereHas('rdbProjectWorks', function ($wq) use ($researcherId) {
                                  $wq->where('researcher_id', $researcherId);
                              });
                          }
                      });
                });
            }
        }

        if (!empty($statusFilter)) {
            $submissionsQuery->where('status', $statusFilter);
        }

        if (!empty($yearFilter)) {
            $submissionsQuery->whereHas('project', fn($q) => $q->where('year_id', $yearFilter));
        }

        if (!empty($deptFilter)) {
            $submissionsQuery->whereHas('project', fn($q) => $q->where('department_id', $deptFilter));
        }

        if ($search !== '') {
            $submissionsQuery->where(function ($q) use ($search) {
                $q->where('submission_title', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($pq) use ($search) {
                      $pq->where('pro_nameTH', 'like', "%{$search}%")
                         ->orWhere('pro_nameEN', 'like', "%{$search}%")
                         ->orWhere('pro_code', 'like', "%{$search}%");
                  });
            });
        }

        $submissions = $submissionsQuery->paginate(15)->withQueryString();

        // Stats Summary
        $statsBase = RdbReviewSubmission::query();
        if (!$isAdmin && !$user->hasRole('Faculty Coordinator')) {
            $researcherId = $user->researcher_id;
            $statsBase->where(function ($q) use ($user, $researcherId) {
                $q->where('submitted_by', $user->id)
                  ->orWhereHas('project', function ($pq) use ($user, $researcherId) {
                      $pq->where('user_created', $user->id);
                      if ($researcherId) {
                          $pq->orWhereHas('rdbProjectWorks', fn($wq) => $wq->where('researcher_id', $researcherId));
                      }
                  });
            });
        }

        $stats = [
            'total' => (clone $statsBase)->count(),
            'submitted' => (clone $statsBase)->whereIn('status', ['submitted', 'final_submitted'])->count(),
            'under_review' => (clone $statsBase)->where('status', 'under_review')->count(),
            'revision_required' => (clone $statsBase)->whereIn('status', ['round_closed', 'revision_required', 'disputed'])->count(),
            'approved' => (clone $statsBase)->where('status', 'approved')->count(),
            'my_pending_reviews' => RdbReviewAssignment::where(function ($q) use ($user) {
                $q->where('reviewer_id', $user->id);
                if (!empty($user->researcher_id)) {
                    $q->orWhere('researcher_id', $user->researcher_id);
                }
            })->whereIn('status', ['pending', 'accepted'])->count(),
        ];

        $years = RdbYear::orderByDesc('year_id')->get();
        $departments = RdbDepartment::orderBy('department_nameTH')->get();

        return view('backend.project_review.index', compact(
            'submissions',
            'stats',
            'isAdmin',
            'years',
            'departments'
        ));
    }

    /**
     * GET /backend/project-reviews/my-reviews
     * Reviewer's Assigned Tasks Workspace
     */
    public function myReviews(Request $request)
    {
        $user = auth()->user();
        $statusFilter = $request->input('status');

        $query = RdbReviewAssignment::with([
            'submission.project.year',
            'submission.project.department',
            'submission.project.type',
            'template.criteria',
        ])->where(function ($q) use ($user) {
            $q->where('reviewer_id', $user->id);
            if (!empty($user->researcher_id)) {
                $q->orWhere('researcher_id', $user->researcher_id);
            }
        })->orderByRaw("CASE WHEN status IN ('pending', 'accepted') THEN 0 ELSE 1 END")
          ->orderByDesc('assigned_at')
          ->orderByDesc('id');

        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        $assignments = $query->paginate(15)->withQueryString();

        $baseCount = RdbReviewAssignment::where(function ($q) use ($user) {
            $q->where('reviewer_id', $user->id);
            if (!empty($user->researcher_id)) {
                $q->orWhere('researcher_id', $user->researcher_id);
            }
        });

        $stats = [
            'pending' => (clone $baseCount)->where('status', 'pending')->count(),
            'accepted' => (clone $baseCount)->where('status', 'accepted')->count(),
            'completed' => (clone $baseCount)->where('status', 'completed')->count(),
            'declined' => (clone $baseCount)->where('status', 'declined')->count(),
        ];

        return view('backend.project_review.my_reviews', compact('assignments', 'stats'));
    }

    /**
     * GET /backend/project-reviews/templates
     * Form Template Designer - Index & Overview
     */
    public function templates()
    {
        $user = auth()->user();
        if (!$this->reviewService->canManageReview($user)) {
            abort(403, 'เฉพาะผู้ดูแลระบบหรือเจ้าหน้าที่บริหารงานวิจัยเท่านั้นที่สามารถจัดการแม่แบบฟอร์มประเมินได้');
        }

        $this->reviewService->ensureDefaultTemplatesExist();

        $templates = RdbReviewTemplate::with(['criteria', 'creator'])
            ->withCount('assignments')
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return view('backend.project_review.templates', compact('templates'));
    }

    /**
     * GET /backend/project-reviews/templates/create
     * Dedicated Form Template Builder (New Tab / Page)
     */
    public function createTemplate()
    {
        $user = auth()->user();
        if (!$this->reviewService->canManageReview($user)) {
            abort(403, 'ไม่มีสิทธิ์สร้างแม่แบบฟอร์มประเมิน');
        }

        return view('backend.project_review.template_create');
    }

    /**
     * GET /backend/project-reviews/templates/{id}/edit
     * Dedicated Form Template Editor (New Tab / Page)
     */
    public function editTemplate($id)
    {
        $user = auth()->user();
        if (!$this->reviewService->canManageReview($user)) {
            abort(403, 'ไม่มีสิทธิ์แก้ไขแม่แบบฟอร์มประเมิน');
        }

        $template = RdbReviewTemplate::with('criteria')->withCount('assignments')->findOrFail($id);

        return view('backend.project_review.template_edit', compact('template'));
    }

    /**
     * POST /backend/project-reviews/templates
     */
    public function storeTemplate(Request $request)
    {
        $user = auth()->user();
        if (!$this->reviewService->canManageReview($user)) {
            abort(403, 'ไม่มีสิทธิ์สร้างแม่แบบฟอร์มประเมิน');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'evaluation_mode' => 'required|in:scoring_scale,pass_fail,qualitative_only,hybrid',
            'scale_max' => 'nullable|integer|min:0|max:100',
            'pass_threshold' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'criteria' => 'required|array|min:1',
            'criteria.*.category' => 'nullable|string|max:150',
            'criteria.*.name' => 'required|string|max:255',
            'criteria.*.description' => 'nullable|string',
            'criteria.*.item_type' => 'required|in:scale,pass_fail,open_ended,hybrid',
            'criteria.*.scale_max' => 'nullable|integer|min:1|max:100',
            'criteria.*.scale_step' => 'nullable|integer|min:1|max:100',
            'criteria.*.weight' => 'nullable|numeric|min:0|max:100',
            'criteria.*.is_required' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated, $request, $user) {
            $isDefault = $request->boolean('is_default');
            if ($isDefault) {
                RdbReviewTemplate::query()->update(['is_default' => false]);
            }

            $template = RdbReviewTemplate::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'evaluation_mode' => $validated['evaluation_mode'],
                'scale_max' => (int) ($validated['scale_max'] ?? 5),
                'pass_threshold' => (float) ($validated['pass_threshold'] ?? 70.00),
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
                'is_default' => $isDefault,
                'created_by' => $user->id,
            ]);

            foreach (array_values($validated['criteria']) as $idx => $item) {
                RdbReviewCriteria::create([
                    'template_id' => $template->id,
                    'category' => $item['category'] ?? null,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'item_type' => $item['item_type'],
                    'scale_max' => !empty($item['scale_max']) ? (int) $item['scale_max'] : null,
                    'scale_step' => !empty($item['scale_step']) ? max(1, (int) $item['scale_step']) : 1,
                    'weight' => (float) ($item['weight'] ?? 0),
                    'is_required' => isset($item['is_required']) ? (bool) $item['is_required'] : true,
                    'order_seq' => $idx + 1,
                ]);
            }
        });

        return redirect()->route('backend.project_review.templates')
            ->with('success', 'สร้างแม่แบบฟอร์มประเมินเรียบร้อยแล้ว');
    }

    /**
     * PUT /backend/project-reviews/templates/{id}
     */
    public function updateTemplate(Request $request, $id)
    {
        $user = auth()->user();
        if (!$this->reviewService->canManageReview($user)) {
            abort(403, 'ไม่มีสิทธิ์แก้ไขแม่แบบฟอร์มประเมิน');
        }

        $template = RdbReviewTemplate::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'evaluation_mode' => 'required|in:scoring_scale,pass_fail,qualitative_only,hybrid',
            'scale_max' => 'nullable|integer|min:0|max:100',
            'pass_threshold' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'criteria' => 'nullable|array',
            'criteria.*.id' => 'nullable|integer',
            'criteria.*.category' => 'nullable|string|max:150',
            'criteria.*.name' => 'required_with:criteria|string|max:255',
            'criteria.*.description' => 'nullable|string',
            'criteria.*.item_type' => 'required_with:criteria|in:scale,pass_fail,open_ended,hybrid',
            'criteria.*.scale_max' => 'nullable|integer|min:1|max:100',
            'criteria.*.scale_step' => 'nullable|integer|min:1|max:100',
            'criteria.*.weight' => 'nullable|numeric|min:0|max:100',
            'criteria.*.is_required' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($template, $validated, $request) {
            $isDefault = $request->boolean('is_default');
            if ($isDefault) {
                RdbReviewTemplate::where('id', '!=', $template->id)->update(['is_default' => false]);
            }

            $template->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'evaluation_mode' => $validated['evaluation_mode'],
                'scale_max' => (int) ($validated['scale_max'] ?? $template->scale_max),
                'pass_threshold' => (float) ($validated['pass_threshold'] ?? $template->pass_threshold),
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $template->is_active,
                'is_default' => $isDefault,
            ]);

            if (!empty($validated['criteria'])) {
                $keptIds = [];
                foreach (array_values($validated['criteria']) as $idx => $item) {
                    $criterionId = !empty($item['id']) ? (int) $item['id'] : null;
                    $existing = $criterionId
                        ? RdbReviewCriteria::where('template_id', $template->id)->where('id', $criterionId)->first()
                        : null;

                    $cData = [
                        'category' => $item['category'] ?? null,
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'item_type' => $item['item_type'],
                        'scale_max' => !empty($item['scale_max']) ? (int) $item['scale_max'] : null,
                        'scale_step' => !empty($item['scale_step']) ? max(1, (int) $item['scale_step']) : 1,
                        'weight' => (float) ($item['weight'] ?? 0),
                        'is_required' => isset($item['is_required']) ? (bool) $item['is_required'] : true,
                        'order_seq' => $idx + 1,
                    ];

                    if ($existing) {
                        $existing->update($cData);
                        $keptIds[] = $existing->id;
                    } else {
                        $cData['template_id'] = $template->id;
                        $created = RdbReviewCriteria::create($cData);
                        $keptIds[] = $created->id;
                    }
                }

                // Only delete criteria that have no scores attached
                RdbReviewCriteria::where('template_id', $template->id)
                    ->whereNotIn('id', $keptIds)
                    ->whereDoesntHave('scores')
                    ->delete();
            }
        });

        return redirect()->route('backend.project_review.templates')
            ->with('success', 'อัปเดตแม่แบบฟอร์มประเมินเรียบร้อยแล้ว');
    }

    /**
     * DELETE /backend/project-reviews/templates/{id}
     */
    public function destroyTemplate($id)
    {
        $user = auth()->user();
        if (!$this->reviewService->canManageReview($user)) {
            abort(403, 'ไม่มีสิทธิ์ลบแม่แบบฟอร์มประเมิน');
        }

        $template = RdbReviewTemplate::withCount('assignments')->findOrFail($id);
        if ($template->assignments_count > 0) {
            return redirect()->back()->with('error', 'ไม่สามารถลบแม่แบบฟอร์มที่มีการนำไปใช้งานประเมินแล้วได้ (สามารถปิดการใช้งานแทนได้)');
        }

        $template->delete();

        return redirect()->route('backend.project_review.templates')
            ->with('success', 'ลบแม่แบบฟอร์มประเมินเรียบร้อยแล้ว');
    }

    /**
     * GET /backend/project-reviews/project/{pro_id}
     * Project Review Workspace (Admin & Researcher View)
     */
    public function project($pro_id)
    {
        $user = auth()->user();
        $this->reviewService->ensureDefaultTemplatesExist();

        $project = RdbProject::with([
            'year',
            'department',
            'type',
            'typeSub',
            'status',
            'researchers.prefix',
            'rdbProjectWorks.researcher.prefix',
            'rdbProjectWorks.position',
        ])->findOrFail($pro_id);

        $isAdmin = $this->reviewService->canManageReview($user, $project);
        $isMember = $this->reviewService->isProjectMemberOrOwner($user, $project);

        if (!$isAdmin && !$isMember) {
            abort(403, 'ท่านไม่มีสิทธิ์เข้าถึงพื้นที่การประเมินเล่มรายงานของโครงการวิจัยนี้');
        }

        $submissionsQuery = RdbReviewSubmission::with([
            'submitter',
            'closer',
            'approver',
            'clarifications.user.researcher.prefix',
        ])->where('pro_id', $project->pro_id)
          ->orderByDesc('round_number')
          ->orderByDesc('id');

        if ($isAdmin) {
            $submissionsQuery->with([
                'assignments.reviewer.researcher.prefix',
                'assignments.reviewer.researcher.department',
                'assignments.researcher.prefix',
                'assignments.researcher.department',
                'assignments.template.criteria',
                'assignments.scores.criteria',
            ]);
        }

        $submissions = $submissionsQuery->get();

        // Build Single-Blind Masked Reviews for Researcher (and preview for Admin)
        $maskedReviewsBySubmission = [];
        foreach ($submissions as $sub) {
            $maskedReviewsBySubmission[$sub->id] = $this->reviewService->getMaskedAssignmentsForResearcher($sub);
        }

        $templates = RdbReviewTemplate::with('criteria')
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        return view('backend.project_review.project', compact(
            'project',
            'submissions',
            'maskedReviewsBySubmission',
            'templates',
            'isAdmin',
            'isMember'
        ));
    }

    /**
     * POST /backend/project-reviews/project/{pro_id}/submit
     * Upload draft / revised / final report
     */
    public function submitReport(Request $request, $pro_id)
    {
        $user = auth()->user();
        $project = RdbProject::with('researchers')->findOrFail($pro_id);

        $isAdmin = $this->reviewService->canManageReview($user, $project);
        $isMember = $this->reviewService->isProjectMemberOrOwner($user, $project);

        if (!$isAdmin && !$isMember) {
            abort(403, 'ท่านไม่มีสิทธิ์ส่งเล่มรายงานวิจัยสำหรับโครงการนี้');
        }

        $validated = $request->validate([
            'submission_type' => 'required|in:initial_draft,revised_draft,final_report',
            'submission_title' => 'required|string|max:255',
            'report_file' => 'required|file|mimes:pdf,doc,docx|max:51200',
            'note' => 'nullable|string|max:5000',
        ], [
            'submission_title.required' => 'กรุณาระบุชื่อเอกสาร/หัวข้อการส่งประเมิน',
            'report_file.required' => 'กรุณาแนบไฟล์เล่มรายงานวิจัย (PDF หรือ Word)',
            'report_file.mimes' => 'รองรับเฉพาะไฟล์ PDF (.pdf) หรือ Word (.doc, .docx) เท่านั้น',
            'report_file.max' => 'ขนาดไฟล์ต้องไม่เกิน 50 MB',
        ]);

        try {
            $submission = $this->reviewService->submitReport(
                $project,
                $validated,
                $request->file('report_file'),
                $user
            );

            return redirect()->route('backend.project_review.project', $project->pro_id)
                ->with('success', "ส่งไฟล์เล่มรายงาน (รอบที่ {$submission->round_number}) เข้าสู่ระบบประเมินเรียบร้อยแล้ว");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์: ' . $e->getMessage());
        }
    }

    /**
     * POST /backend/project-reviews/submission/{id}/assign
     * Admin assigns 1..N reviewers
     */
    public function assignReviewers(Request $request, $id)
    {
        $user = auth()->user();
        $submission = RdbReviewSubmission::with('project')->findOrFail($id);

        if (!$this->reviewService->canManageReview($user, $submission->project)) {
            abort(403, 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถมอบหมายผู้ทรงคุณวุฒิได้');
        }

        $validated = $request->validate([
            'template_id' => 'required|exists:rdb_review_template,id',
            'reviewer_ids' => 'required|array|min:1',
            'due_date' => 'nullable|string',
        ], [
            'template_id.required' => 'กรุณาเลือกแบบฟอร์มการประเมิน',
            'reviewer_ids.required' => 'กรุณาเลือกผู้ทรงคุณวุฒิอย่างน้อย 1 ท่าน',
        ]);

        $dueDate = $validated['due_date'] ?? null;
        if (!empty($dueDate) && class_exists(\App\Helpers\ThaiDateHelper::class)) {
            $iso = \App\Helpers\ThaiDateHelper::toIsoDate($dueDate);
            if ($iso) {
                $dueDate = $iso;
            }
        }

        $count = $this->reviewService->assignReviewers(
            $submission,
            (int) $validated['template_id'],
            $validated['reviewer_ids'],
            $dueDate,
            $user
        );

        if ($count === 0) {
            return redirect()->back()->with('warning', 'ผู้ทรงคุณวุฒิที่เลือกได้รับมอบหมายในรอบนี้ไปแล้ว หรือไม่พบบัญชีผู้ใช้');
        }

        return redirect()->route('backend.project_review.project', $submission->pro_id)
            ->with('success', "มอบหมายผู้ทรงคุณวุฒิจำนวน {$count} ท่าน เรียบร้อยแล้ว");
    }

    /**
     * DELETE /backend/project-reviews/assignment/{id}
     * Admin removes pending/declined assignment
     */
    public function removeAssignment($id)
    {
        $user = auth()->user();
        $assignment = RdbReviewAssignment::with('submission.project')->findOrFail($id);

        if (!$this->reviewService->canManageReview($user, $assignment->submission?->project)) {
            abort(403, 'ไม่มีสิทธิ์ยกเลิกการมอบหมายผู้ทรงคุณวุฒิ');
        }

        if ($assignment->status === 'completed') {
            return redirect()->back()->with('error', 'ไม่สามารถลบรายการที่ผู้ทรงคุณวุฒิส่งผลประเมินเรียบร้อยแล้วได้');
        }

        $proId = $assignment->submission->pro_id;
        $assignment->delete();

        return redirect()->route('backend.project_review.project', $proId)
            ->with('success', 'ยกเลิกการมอบหมายผู้ทรงคุณวุฒิเรียบร้อยแล้ว');
    }

    /**
     * POST /backend/project-reviews/submission/{id}/close-round
     * Admin closes round & releases blind feedback to researcher
     */
    public function closeRound(Request $request, $id)
    {
        $user = auth()->user();
        $submission = RdbReviewSubmission::with('project')->findOrFail($id);

        if (!$this->reviewService->canManageReview($user, $submission->project)) {
            abort(403, 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถสรุปปิดรอบการประเมินได้');
        }

        $validated = $request->validate([
            'next_status' => 'required|in:round_closed,revision_required,rejected',
            'admin_summary' => 'nullable|string|max:10000',
        ]);

        $this->reviewService->closeRoundAndSendFeedback(
            $submission,
            $validated['next_status'],
            $validated['admin_summary'] ?? null,
            $user
        );

        return redirect()->route('backend.project_review.project', $submission->pro_id)
            ->with('success', 'สรุปผลการประเมินและส่งผลการประเมิน (แบบปกปิดรายชื่อผู้ประเมิน) ให้ผู้วิจัยเรียบร้อยแล้ว');
    }

    /**
     * POST /backend/project-reviews/submission/{id}/clarification
     * Researcher or Admin posts clarification / dispute
     */
    public function storeClarification(Request $request, $id)
    {
        $user = auth()->user();
        $submission = RdbReviewSubmission::with('project.researchers')->findOrFail($id);

        $isAdmin = $this->reviewService->canManageReview($user, $submission->project);
        $isMember = $submission->project && $this->reviewService->isProjectMemberOrOwner($user, $submission->project);

        if (!$isAdmin && !$isMember) {
            abort(403, 'ไม่มีสิทธิ์ส่งคำชี้แจงในโครงการนี้');
        }

        $validated = $request->validate([
            'action_type' => 'required|in:clarification,dispute,admin_reply',
            'message' => 'required|string|max:10000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:20480',
        ], [
            'message.required' => 'กรุณาระบุข้อความคำชี้แจงหรือข้อคิดเห็น',
        ]);

        $this->reviewService->addClarification(
            $submission,
            $validated['message'],
            $validated['action_type'],
            $request->file('attachment'),
            $user
        );

        return redirect()->route('backend.project_review.project', $submission->pro_id)
            ->with('success', 'บันทึกข้อความคำชี้แจงเรียบร้อยแล้ว');
    }

    /**
     * POST /backend/project-reviews/submission/{id}/approve-final
     * Admin approves final report & syncs to rdb_project.pro_file
     */
    public function approveFinal(Request $request, $id)
    {
        $user = auth()->user();
        $submission = RdbReviewSubmission::with('project')->findOrFail($id);

        if (!$this->reviewService->canManageReview($user, $submission->project)) {
            abort(403, 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถอนุมัติเล่มรายงานฉบับสมบูรณ์ได้');
        }

        $validated = $request->validate([
            'admin_summary' => 'nullable|string|max:10000',
        ]);

        $this->reviewService->approveFinalReportAndSync(
            $submission,
            $validated['admin_summary'] ?? null,
            $user
        );

        return redirect()->route('backend.project_review.project', $submission->pro_id)
            ->with('success', 'อนุมัติเล่มรายงานวิจัยฉบับสมบูรณ์ และซิงค์ไฟล์พร้อมจำนวนหน้าเข้าสู่ข้อมูลโครงการวิจัยเรียบร้อยแล้ว');
    }

    /**
     * GET /backend/project-reviews/assignment/{id}/evaluate
     * Reviewer's Evaluation Form Page
     */
    public function evaluate($id)
    {
        $user = auth()->user();
        $assignment = RdbReviewAssignment::with([
            'submission.project.year',
            'submission.project.department',
            'submission.project.type',
            'submission.project.rdbProjectWorks.researcher.prefix',
            'submission.project.rdbProjectWorks.position',
            'template.criteria',
            'scores',
        ])->findOrFail($id);

        $isAssignedReviewer = ((int) $assignment->reviewer_id === (int) $user->id)
            || (!empty($user->researcher_id) && (string) $assignment->researcher_id === (string) $user->researcher_id);
        $isAdmin = $this->reviewService->canManageReview($user, $assignment->submission?->project);

        if (!$isAssignedReviewer && !$isAdmin) {
            abort(403, 'ท่านไม่ได้รับมอบหมายให้ประเมินเล่มรายงานวิจัยรายการนี้');
        }

        $existingScores = $assignment->scores->keyBy('criteria_id');

        return view('backend.project_review.evaluate', compact(
            'assignment',
            'existingScores',
            'isAssignedReviewer',
            'isAdmin'
        ));
    }

    /**
     * POST /backend/project-reviews/assignment/{id}/respond
     * Reviewer accepts or declines with reason
     */
    public function respondAssignment(Request $request, $id)
    {
        $user = auth()->user();
        $assignment = RdbReviewAssignment::with('submission.project')->findOrFail($id);

        $isAssignedReviewer = ((int) $assignment->reviewer_id === (int) $user->id)
            || (!empty($user->researcher_id) && (string) $assignment->researcher_id === (string) $user->researcher_id);
        $isAdmin = $this->reviewService->canManageReview($user, $assignment->submission?->project);

        if (!$isAssignedReviewer && !$isAdmin) {
            abort(403, 'ท่านไม่มีสิทธิ์ตอบรับหรือปฏิเสธงานประเมินนี้');
        }

        $validated = $request->validate([
            'decision' => 'required|in:accepted,declined',
            'decline_reason' => 'required_if:decision,declined|nullable|string|max:2000',
        ], [
            'decline_reason.required_if' => 'กรุณาระบุเหตุผลในการปฏิเสธการประเมิน',
        ]);

        $this->reviewService->respondAssignment(
            $assignment,
            $validated['decision'],
            $validated['decline_reason'] ?? null
        );

        if ($validated['decision'] === 'declined') {
            return redirect()->route('backend.project_review.my_reviews')
                ->with('success', 'บันทึกการปฏิเสธรับงานประเมินเรียบร้อยแล้ว');
        }

        return redirect()->route('backend.project_review.evaluate', $assignment->id)
            ->with('success', 'ตอบรับการเป็นผู้ทรงคุณวุฒิประเมินเล่มรายงานวิจัยเรียบร้อยแล้ว');
    }

    /**
     * POST /backend/project-reviews/assignment/{id}/evaluate
     * Reviewer submits evaluation form
     */
    public function submitEvaluation(Request $request, $id)
    {
        $user = auth()->user();
        $assignment = RdbReviewAssignment::with(['submission.project', 'template.criteria'])->findOrFail($id);

        $isAssignedReviewer = ((int) $assignment->reviewer_id === (int) $user->id)
            || (!empty($user->researcher_id) && (string) $assignment->researcher_id === (string) $user->researcher_id);
        $isAdmin = $this->reviewService->canManageReview($user, $assignment->submission?->project);

        if (!$isAssignedReviewer && !$isAdmin) {
            abort(403, 'ท่านไม่มีสิทธิ์ส่งผลการประเมินรายการนี้');
        }

        $validated = $request->validate([
            'result_decision' => 'required|in:pass,minor_revision,major_revision,reject',
            'general_comment' => 'nullable|string|max:20000',
            'criteria' => 'nullable|array',
            'annotated_file' => 'nullable|file|mimes:pdf,doc,docx|max:51200',
        ], [
            'result_decision.required' => 'กรุณาเลือกสรุปผลการประเมินในภาพรวม',
            'annotated_file.mimes' => 'ไฟล์แนบข้อเสนอแนะรองรับเฉพาะ PDF หรือ Word เท่านั้น',
        ]);

        try {
            $this->reviewService->submitEvaluation(
                $assignment,
                $validated['criteria'] ?? [],
                $validated['result_decision'],
                $validated['general_comment'] ?? null,
                $request->file('annotated_file')
            );

            return redirect()->route('backend.project_review.evaluate', $assignment->id)
                ->with('success', 'บันทึกและส่งผลการประเมินเล่มรายงานวิจัยเรียบร้อยแล้ว ขอขอบพระคุณเป็นอย่างสูง');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาดในการบันทึกผลประเมิน: ' . $e->getMessage());
        }
    }

    /**
     * GET /backend/project-reviews/submission/{id}/file
     * View/Download submission PDF (authorized for Admin, Project Owner/Member, or Assigned Reviewer)
     */
    public function downloadSubmissionFile($id)
    {
        $user = auth()->user();
        $submission = RdbReviewSubmission::with(['project.researchers', 'assignments'])->findOrFail($id);

        $isAdmin = $this->reviewService->canManageReview($user, $submission->project);
        $isMember = $submission->project && $this->reviewService->isProjectMemberOrOwner($user, $submission->project);
        $isReviewer = $submission->assignments->contains(function ($a) use ($user) {
            return ((int) $a->reviewer_id === (int) $user->id)
                || (!empty($user->researcher_id) && (string) $a->researcher_id === (string) $user->researcher_id);
        });

        if (!$isAdmin && !$isMember && !$isReviewer) {
            abort(403, 'ท่านไม่มีสิทธิ์เปิดดูไฟล์รายงานฉบับนี้');
        }

        $filePath = $submission->file_path;
        if (empty($filePath) || !Storage::disk('public')->exists($filePath)) {
            return response()->view('errors.404', ['message' => 'ไม่พบไฟล์เล่มรายงานวิจัยบนเซิร์ฟเวอร์'], 404);
        }

        $fullPath = Storage::disk('public')->path($filePath);
        $mime = $submission->file_mime ?: 'application/pdf';
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-\.\x{0E00}-\x{0E7F}]/u', '_', $submission->file_name ?: basename($filePath));

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $safeFilename . '"',
        ]);
    }

    /**
     * GET /backend/project-reviews/assignment/{id}/annotated-file
     * View/Download reviewer's annotated file.
     * CRITICAL SINGLE-BLIND: When downloaded by Researcher, filename MUST be anonymized to reviewer_feedback_{assignment_id}.pdf!
     */
    public function downloadAnnotatedFile($id)
    {
        $user = auth()->user();
        $assignment = RdbReviewAssignment::with(['submission.project.researchers'])->findOrFail($id);
        $submission = $assignment->submission;

        $isAdmin = $this->reviewService->canManageReview($user, $submission?->project);
        $isAssignedReviewer = ((int) $assignment->reviewer_id === (int) $user->id)
            || (!empty($user->researcher_id) && (string) $assignment->researcher_id === (string) $user->researcher_id);
        $isMember = $submission?->project && $this->reviewService->isProjectMemberOrOwner($user, $submission->project);

        if (!$isAdmin && !$isAssignedReviewer && !$isMember) {
            abort(403, 'ท่านไม่มีสิทธิ์เปิดดูไฟล์แนบข้อเสนอแนะนี้');
        }

        // If user is only a project member (and not admin or the reviewer themselves), ensure round has been closed
        if (!$isAdmin && !$isAssignedReviewer && $isMember) {
            $allowedStatuses = ['round_closed', 'revision_required', 'disputed', 'final_submitted', 'approved', 'rejected'];
            if (!in_array($submission->status, $allowedStatuses, true)) {
                abort(403, 'ยังไม่เปิดเผยไฟล์ประเมินจนกว่าเจ้าหน้าที่จะสรุปผลการประเมินในรอบนี้');
            }
        }

        $filePath = $assignment->annotated_file_path;
        if (empty($filePath) || !Storage::disk('public')->exists($filePath)) {
            return response()->view('errors.404', ['message' => 'ไม่พบไฟล์แนบข้อเสนอแนะของผู้ทรงคุณวุฒิบนเซิร์ฟเวอร์'], 404);
        }

        $fullPath = Storage::disk('public')->path($filePath);
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) ?: 'pdf';
        $mime = $ext === 'pdf' ? 'application/pdf' : 'application/octet-stream';

        // Anonymize filename for researchers to protect Single-Blind anonymity
        if (!$isAdmin && !$isAssignedReviewer) {
            $downloadFilename = "reviewer_feedback_{$assignment->id}.{$ext}";
        } else {
            $downloadFilename = preg_replace(
                '/[^a-zA-Z0-9_\-\.\x{0E00}-\x{0E7F}]/u',
                '_',
                $assignment->annotated_file_name ?: "reviewer_feedback_{$assignment->id}.{$ext}"
            );
        }

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $downloadFilename . '"',
        ]);
    }

    /**
     * GET /backend/project-reviews/clarification/{id}/attachment
     * View/Download clarification attachment (authorized for Admin or Project Owner/Member).
     */
    public function downloadClarificationAttachment($id)
    {
        $user = auth()->user();
        $clarification = RdbReviewClarification::with(['submission.project.researchers'])->findOrFail($id);
        $project = $clarification->submission?->project;

        $isAdmin = $this->reviewService->canManageReview($user, $project);
        $isMember = $project && $this->reviewService->isProjectMemberOrOwner($user, $project);

        if (!$isAdmin && !$isMember) {
            abort(403, 'ท่านไม่มีสิทธิ์เปิดดูไฟล์แนบคำชี้แจงนี้');
        }

        $filePath = $clarification->attachment_path;
        if (empty($filePath) || !Storage::disk('public')->exists($filePath)) {
            return response()->view('errors.404', ['message' => 'ไม่พบไฟล์แนบคำชี้แจงบนเซิร์ฟเวอร์'], 404);
        }

        $fullPath = Storage::disk('public')->path($filePath);
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) ?: 'pdf';
        $mime = match ($ext) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-\.\x{0E00}-\x{0E7F}]/u', '_', $clarification->attachment_name ?: basename($filePath));

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $safeFilename . '"',
        ]);
    }

    /**
     * GET /backend/project-reviews/search-reviewers
     * AJAX endpoint for TomSelect to search internal researchers/users returning active review workload count.
     */
    public function searchReviewers(Request $request)
    {
        $user = auth()->user();
        if (!$this->reviewService->canManageReview($user)) {
            return response()->json(['results' => []], 403);
        }

        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $cleanTerm = preg_replace('/^(นาย|นางสาว|นาง|ดร\.|ผศ\.ดร\.|รศ\.ดร\.|ศ\.ดร\.|ผศ\.|รศ\.|ศ\.|อาจารย์|อ\.)\s*/u', '', $q);
        $words = array_values(array_filter(preg_split('/\s+/', $cleanTerm)));

        $researchers = RdbResearcher::with(['prefix', 'department', 'user'])
            ->where(function ($query) use ($q, $cleanTerm, $words) {
                $query->where('researcher_fname', 'like', "%{$cleanTerm}%")
                      ->orWhere('researcher_lname', 'like', "%{$cleanTerm}%")
                      ->orWhere('researcher_fnameEN', 'like', "%{$cleanTerm}%")
                      ->orWhere('researcher_lnameEN', 'like', "%{$cleanTerm}%")
                      ->orWhere('researcher_email', 'like', "%{$q}%")
                      ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$cleanTerm}%"]);

                if (count($words) > 1) {
                    $query->orWhere(function ($sub) use ($words) {
                        foreach ($words as $w) {
                            $sub->where(function ($wq) use ($w) {
                                $wq->where('researcher_fname', 'like', "%{$w}%")
                                   ->orWhere('researcher_lname', 'like', "%{$w}%")
                                   ->orWhere('researcher_fnameEN', 'like', "%{$w}%")
                                   ->orWhere('researcher_lnameEN', 'like', "%{$w}%");
                            });
                        }
                    });
                }
            })
            ->limit(25)
            ->get();

        // Preload active workload counts to avoid N+1 queries
        $userIds = $researchers->pluck('user.id')->filter()->values()->toArray();
        $resIds = $researchers->pluck('researcher_id')->filter()->values()->toArray();

        $activeByUser = !empty($userIds)
            ? RdbReviewAssignment::whereIn('reviewer_id', $userIds)
                ->whereIn('status', ['pending', 'accepted'])
                ->select('reviewer_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('reviewer_id')
                ->pluck('cnt', 'reviewer_id')
            : collect();

        $activeByRes = !empty($resIds)
            ? RdbReviewAssignment::whereIn('researcher_id', $resIds)
                ->whereIn('status', ['pending', 'accepted'])
                ->select('researcher_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('researcher_id')
                ->pluck('cnt', 'researcher_id')
            : collect();

        $results = $researchers->map(function ($r) use ($activeByUser, $activeByRes) {
            $prefix = $r->prefix->prefix_nameTH ?? '';
            $fullName = trim("{$prefix}{$r->researcher_fname} {$r->researcher_lname}");
            $deptName = $r->department->department_nameTH ?? 'ไม่ระบุหน่วยงาน';
            $linkedUser = $r->user;

            $activeCount = 0;
            if ($linkedUser && isset($activeByUser[$linkedUser->id])) {
                $activeCount = (int) $activeByUser[$linkedUser->id];
            } elseif (isset($activeByRes[$r->researcher_id])) {
                $activeCount = (int) $activeByRes[$r->researcher_id];
            }

            // Use user ID if linked, otherwise res:{researcher_id}
            $value = $linkedUser ? (string) $linkedUser->id : ('res:' . $r->researcher_id);

            return [
                'id' => $value,
                'value' => $value,
                'user_id' => $linkedUser?->id,
                'researcher_id' => $r->researcher_id,
                'text' => "{$fullName} ({$deptName}) — กำลังประเมิน {$activeCount} โครงการ",
                'full_name' => $fullName,
                'department' => $deptName,
                'active_reviews_count' => $activeCount,
                'has_user_account' => (bool) $linkedUser,
            ];
        })->values();

        return response()->json(['results' => $results]);
    }
}
