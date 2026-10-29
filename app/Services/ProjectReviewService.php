<?php

namespace App\Services;

use App\Models\RdbProject;
use App\Models\RdbResearcher;
use App\Models\RdbReviewAssignment;
use App\Models\RdbReviewClarification;
use App\Models\RdbReviewScore;
use App\Models\RdbReviewSubmission;
use App\Models\RdbReviewTemplate;
use App\Models\User;
use App\Notifications\ReviewAssignedNotification;
use App\Notifications\ReviewFinalApprovedNotification;
use App\Notifications\ReviewRoundClosedNotification;
use Database\Seeders\RdbReviewTemplateSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProjectReviewService
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    /**
     * Auto-seeds the 3 default templates if RdbReviewTemplate::count() === 0.
     */
    public function ensureDefaultTemplatesExist(): void
    {
        if (RdbReviewTemplate::count() === 0) {
            RdbReviewTemplateSeeder::seedDefaults();
        }
    }

    /**
     * Check if user has Admin/Coordinator rights to manage project reviews.
     */
    public function canManageReview(User $user, ?RdbProject $project = null): bool
    {
        if ($user->hasRole(['The Creator', 'Admin', 'Admin Research', 'Admin Project']) || $user->hasPermission('Project.Approve')) {
            return true;
        }

        if ($project && $user->hasRole('Faculty Coordinator')) {
            $deptId = $user->getActiveRoleScope()?->department_id ?? $user->researcher?->department_id;
            if ($deptId && (int) $project->department_id === (int) $deptId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is the project creator or a researcher on the project.
     */
    public function isProjectMemberOrOwner(User $user, RdbProject $project): bool
    {
        if ((int) $user->id === (int) $project->user_created) {
            return true;
        }

        if (!empty($user->researcher_id)) {
            if ($project->relationLoaded('researchers')) {
                return $project->researchers->contains('researcher_id', $user->researcher_id);
            }

            if ($project->relationLoaded('rdbProjectWorks')) {
                return $project->rdbProjectWorks->contains('researcher_id', $user->researcher_id);
            }

            return $project->researchers()->where('rdb_researcher.researcher_id', $user->researcher_id)->exists();
        }

        return false;
    }

    /**
     * Researcher or Admin submits a draft, revised draft, or final report PDF.
     */
    public function submitReport(RdbProject $project, array $data, UploadedFile $file, User $user): RdbReviewSubmission
    {
        $fileInfo = $this->fileUploadService->storeFile($file, 'reviews');

        $submissionType = $data['submission_type'] ?? 'initial_draft';
        $latest = $project->reviewSubmissions()->orderByDesc('round_number')->orderByDesc('id')->first();

        $roundNumber = 1;
        if ($latest) {
            // If submitting final_report after round_closed or revision_required, increment round for clean audit history or keep sequential round
            $roundNumber = ((int) $latest->round_number) + 1;
        }

        $status = $submissionType === 'final_report' ? 'final_submitted' : 'submitted';

        return RdbReviewSubmission::create([
            'pro_id' => $project->pro_id,
            'round_number' => $roundNumber,
            'submission_type' => $submissionType,
            'submission_title' => $data['submission_title'] ?? ('รายงานวิจัย รอบที่ ' . $roundNumber),
            'file_name' => $fileInfo['original_name'] ?: $fileInfo['safe_name'],
            'file_path' => $fileInfo['path'],
            'file_size' => $fileInfo['size'] ?? null,
            'file_mime' => $fileInfo['mime_type'] ?? 'application/pdf',
            'page_count' => $fileInfo['page_count'] ?? null,
            'note' => $data['note'] ?? null,
            'submitted_by' => $user->id,
            'submitted_at' => now(),
            'status' => $status,
        ]);
    }

    /**
     * Admin assigns 1..N reviewers to a submission.
     * Accepts array of user IDs OR researcher IDs (or prefixed like "user:123" / "res:456").
     */
    public function assignReviewers(
        RdbReviewSubmission $submission,
        int $templateId,
        array $reviewerIds,
        ?string $dueDate,
        User $admin
    ): int {
        $assignedCount = 0;

        $project = $submission->project;
        $projectCreatorId = $project ? (int) $project->user_created : 0;
        $projectResearcherIds = $project
            ? $project->rdbProjectWorks()->pluck('researcher_id')->filter()->map(fn($id) => (string) $id)->toArray()
            : [];

        foreach ($reviewerIds as $rawId) {
            if (empty($rawId)) {
                continue;
            }

            $user = null;
            $researcherId = null;

            $rawStr = (string) $rawId;
            if (str_starts_with($rawStr, 'user:')) {
                $uid = (int) substr($rawStr, 5);
                $user = User::find($uid);
                $researcherId = $user?->researcher_id;
            } elseif (str_starts_with($rawStr, 'res:')) {
                $rid = substr($rawStr, 4);
                $researcherId = $rid;
                $user = User::where('researcher_id', $rid)->first();
            } else {
                // Try finding User by ID first, or by researcher_id
                if (is_numeric($rawStr)) {
                    $user = User::find((int) $rawStr);
                }
                if (!$user) {
                    $user = User::where('researcher_id', $rawStr)->first();
                }
                if ($user) {
                    $researcherId = $user->researcher_id;
                } else {
                    $researcher = RdbResearcher::find($rawStr);
                    if ($researcher) {
                        $researcherId = $researcher->researcher_id;
                        $user = $researcher->user;
                    }
                }
            }

            // If a researcher doesn't have a User account yet, auto-provision or skip if neither exists
            if (!$user && $researcherId) {
                $researcher = RdbResearcher::find($researcherId);
                if ($researcher) {
                    // Check if a User exists with this researcher_id
                    $user = User::where('researcher_id', $researcher->researcher_id)->first();
                }
            }

            if (!$user && !$researcherId) {
                continue;
            }

            $reviewerUserId = $user ? (int) $user->id : 0;

            // Conflict of Interest Guard: Prevent project creator or project researchers from reviewing their own project
            if ($projectCreatorId > 0 && $reviewerUserId === $projectCreatorId) {
                continue;
            }
            if (!empty($researcherId) && in_array((string) $researcherId, $projectResearcherIds, true)) {
                continue;
            }
            if ($user && !empty($user->researcher_id) && in_array((string) $user->researcher_id, $projectResearcherIds, true)) {
                continue;
            }

            // Avoid duplicate assignment for the same submission
            $existsQuery = RdbReviewAssignment::where('submission_id', $submission->id);
            if ($reviewerUserId > 0) {
                $existsQuery->where('reviewer_id', $reviewerUserId);
            } else {
                $existsQuery->where('researcher_id', $researcherId);
            }

            if ($existsQuery->exists()) {
                continue;
            }

            $assignment = RdbReviewAssignment::create([
                'submission_id' => $submission->id,
                'reviewer_id' => $reviewerUserId,
                'researcher_id' => $researcherId,
                'template_id' => $templateId,
                'assigned_by' => $admin->id,
                'assigned_at' => now(),
                'due_date' => !empty($dueDate) ? $dueDate : null,
                'status' => 'pending',
            ]);

            $assignedCount++;

            if ($user) {
                try {
                    $user->notify(new ReviewAssignedNotification($assignment));
                } catch (\Throwable $e) {
                    Log::warning('Failed to notify reviewer user #' . $user->id . ': ' . $e->getMessage());
                }
            }
        }

        if ($assignedCount > 0 && in_array($submission->status, ['submitted', 'final_submitted'], true)) {
            $submission->status = 'under_review';
            $submission->save();
        }

        return $assignedCount;
    }

    /**
     * Reviewer accepts or declines an assignment.
     */
    public function respondAssignment(
        RdbReviewAssignment $assignment,
        string $decision,
        ?string $declineReason = null
    ): RdbReviewAssignment {
        if (!in_array($decision, ['accepted', 'declined'], true)) {
            throw new \InvalidArgumentException('Invalid assignment response decision.');
        }

        $assignment->status = $decision;
        $assignment->decline_reason = $decision === 'declined' ? $declineReason : null;
        $assignment->responded_at = now();
        $assignment->save();

        return $assignment;
    }

    /**
     * Reviewer submits evaluation scores, decision, general comment, and optional annotated PDF.
     */
    public function submitEvaluation(
        RdbReviewAssignment $assignment,
        array $criteriaInputs,
        string $resultDecision,
        ?string $generalComment = null,
        ?UploadedFile $annotatedFile = null
    ): RdbReviewAssignment {
        if (!in_array($assignment->status, ['pending', 'accepted', 'completed'], true)) {
            throw new \RuntimeException('ไม่สามารถส่งผลประเมินสำหรับรายการที่ปฏิเสธแล้วได้');
        }

        return DB::transaction(function () use ($assignment, $criteriaInputs, $resultDecision, $generalComment, $annotatedFile) {
            $template = $assignment->template()->with('criteria')->first();
            $scaleMax = max(1, (int) ($template?->scale_max ?: 5));
            $criteriaList = $template ? $template->criteria : collect();

            $totalScore = 0.0;
            $maxPossibleScore = 0.0;
            $hasNumericScore = false;

            foreach ($criteriaList as $criterion) {
                $cid = $criterion->id;
                $input = $criteriaInputs[$cid] ?? [];

                $rawScore = isset($input['score']) && $input['score'] !== '' ? (float) $input['score'] : null;
                $passStatus = !empty($input['pass_status']) ? (string) $input['pass_status'] : null;
                $comment = isset($input['comment']) ? trim((string) $input['comment']) : null;

                $criterionMax = max(1, (int) ($criterion->scale_max ?: $scaleMax));

                if (in_array($criterion->item_type, ['scale', 'hybrid'], true) && $rawScore !== null) {
                    $hasNumericScore = true;
                    $weight = (float) $criterion->weight;
                    if ($weight > 0) {
                        // Weighted calculation: (score / criterionMax) * weight
                        $totalScore += ($rawScore / $criterionMax) * $weight;
                        $maxPossibleScore += $weight;
                    } else {
                        $totalScore += $rawScore;
                        $maxPossibleScore += $criterionMax;
                    }
                } elseif ($criterion->item_type === 'pass_fail' && $passStatus !== null) {
                    $weight = (float) $criterion->weight;
                    if ($weight > 0) {
                        $hasNumericScore = true;
                        $maxPossibleScore += $weight;
                        if ($passStatus === 'pass') {
                            $totalScore += $weight;
                        } elseif ($passStatus === 'revise') {
                            $totalScore += ($weight * 0.5);
                        }
                    }
                }

                RdbReviewScore::updateOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'criteria_id' => $cid,
                    ],
                    [
                        'score' => $rawScore,
                        'pass_status' => $passStatus,
                        'comment' => $comment,
                    ]
                );
            }

            $scorePercentage = ($hasNumericScore && $maxPossibleScore > 0)
                ? round(($totalScore / $maxPossibleScore) * 100, 2)
                : null;

            if ($annotatedFile) {
                if (!empty($assignment->annotated_file_path) && Storage::disk('public')->exists($assignment->annotated_file_path)) {
                    Storage::disk('public')->delete($assignment->annotated_file_path);
                }
                $fileInfo = $this->fileUploadService->storeFile($annotatedFile, 'reviews/annotated');
                $assignment->annotated_file_name = $fileInfo['original_name'] ?: $fileInfo['safe_name'];
                $assignment->annotated_file_path = $fileInfo['path'];
            }

            if (!$assignment->responded_at) {
                $assignment->responded_at = now();
            }

            $assignment->status = 'completed';
            $assignment->result_decision = $resultDecision;
            $assignment->total_score = $hasNumericScore ? round($totalScore, 2) : null;
            $assignment->max_possible_score = $hasNumericScore ? round($maxPossibleScore, 2) : null;
            $assignment->score_percentage = $scorePercentage;
            $assignment->general_comment = $generalComment;
            $assignment->submitted_at = now();
            $assignment->save();

            return $assignment;
        });
    }

    /**
     * Admin closes the evaluation round and releases blind feedback to the researcher.
     */
    public function closeRoundAndSendFeedback(
        RdbReviewSubmission $submission,
        string $nextStatus,
        ?string $adminSummary,
        User $admin
    ): RdbReviewSubmission {
        if (!in_array($nextStatus, ['round_closed', 'revision_required', 'rejected'], true)) {
            $nextStatus = 'revision_required';
        }

        $submission->status = $nextStatus;
        $submission->admin_summary = $adminSummary;
        $submission->closed_by = $admin->id;
        $submission->closed_at = now();
        $submission->save();

        $this->notifyProjectMembers($submission, new ReviewRoundClosedNotification($submission));

        return $submission;
    }

    /**
     * Researcher or Admin adds a clarification, response letter, or dispute.
     */
    public function addClarification(
        RdbReviewSubmission $submission,
        string $message,
        string $actionType,
        ?UploadedFile $attachment,
        User $user
    ): RdbReviewClarification {
        $project = $submission->project;
        $isAdmin = $this->canManageReview($user, $project);
        $senderRole = $isAdmin ? 'admin' : 'researcher';

        if (!in_array($actionType, ['clarification', 'dispute', 'admin_reply'], true)) {
            $actionType = $isAdmin ? 'admin_reply' : 'clarification';
        }

        $attachmentName = null;
        $attachmentPath = null;

        if ($attachment) {
            $fileInfo = $this->fileUploadService->storeFile($attachment, 'reviews/clarifications');
            $attachmentName = $fileInfo['original_name'] ?: $fileInfo['safe_name'];
            $attachmentPath = $fileInfo['path'];
        }

        $clarification = RdbReviewClarification::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'sender_role' => $senderRole,
            'action_type' => $actionType,
            'message' => $message,
            'attachment_name' => $attachmentName,
            'attachment_path' => $attachmentPath,
        ]);

        if ($actionType === 'dispute' && $senderRole === 'researcher') {
            $submission->status = 'disputed';
            $submission->save();
        }

        return $clarification;
    }

    /**
     * Admin approves final report and syncs file + page count + DSpace flag to RdbProject.
     */
    public function approveFinalReportAndSync(
        RdbReviewSubmission $submission,
        ?string $adminSummary,
        User $admin
    ): RdbReviewSubmission {
        return DB::transaction(function () use ($submission, $adminSummary, $admin) {
            $submission->status = 'approved';
            if ($adminSummary !== null && $adminSummary !== '') {
                $submission->admin_summary = $adminSummary;
            }
            $submission->approved_by = $admin->id;
            $submission->approved_at = now();
            $submission->save();

            $project = $submission->project;
            if ($project && !empty($submission->file_path)) {
                $safeFileName = basename($submission->file_path);
                $targetReportPath = 'reports/' . $safeFileName;

                if (Storage::disk('public')->exists($submission->file_path)) {
                    if (!Storage::disk('public')->exists('reports')) {
                        Storage::disk('public')->makeDirectory('reports', 0755, true);
                    }
                    if ($submission->file_path !== $targetReportPath && !Storage::disk('public')->exists($targetReportPath)) {
                        Storage::disk('public')->copy($submission->file_path, $targetReportPath);
                    }
                } else {
                    $targetReportPath = $submission->file_path;
                }

                $pageCount = $submission->page_count;
                if (empty($pageCount) && Storage::disk('public')->exists($submission->file_path)) {
                    $pageCount = $this->fileUploadService->countPdfPages(
                        Storage::disk('public')->path($submission->file_path)
                    );
                }

                $project->pro_file = $safeFileName;
                $project->pro_file_path = $targetReportPath;
                $project->pro_file_size = $submission->file_size;
                $project->pro_file_mime = $submission->file_mime ?? 'application/pdf';
                if ($pageCount > 0) {
                    $project->pro_page_num = $pageCount;
                }
                $project->todspace = 0;

                // Mark project status as completed (ps_id = 2) if not already completed
                $completedStatus = \App\Models\RdbProjectStatus::where('ps_name', 'like', '%เสร็จสิ้น%')->first();
                $project->ps_id = $completedStatus ? $completedStatus->ps_id : 2;
                if (empty($project->pro_finish) || $project->pro_finish === '0000-00-00') {
                    $project->pro_finish = date('Y-m-d');
                }
                $project->user_updated = $admin->id;
                $project->save();
            }

            $this->notifyProjectMembers($submission, new ReviewFinalApprovedNotification($submission));

            return $submission;
        });
    }

    /**
     * CRITICAL SINGLE-BLIND ENGINE:
     * Masks reviewer identities for researchers so they only see "ผู้ทรงคุณวุฒิคนที่ 1", etc.
     * NEVER returns reviewer_id, researcher_id, reviewer name, email, or department.
     */
    public function getMaskedAssignmentsForResearcher(RdbReviewSubmission $submission): array
    {
        // Only reveal feedback after Admin has closed the round or approved/rejected/revision_required/disputed/final_submitted
        $allowedStatuses = [
            'round_closed',
            'revision_required',
            'disputed',
            'final_submitted',
            'approved',
            'rejected',
        ];

        if (!in_array($submission->status, $allowedStatuses, true)) {
            return [];
        }

        $completedAssignments = $submission->assignments()
            ->with(['scores.criteria'])
            ->where('status', 'completed')
            ->orderBy('id', 'asc')
            ->get();

        $masked = [];
        foreach ($completedAssignments->values() as $idx => $assignment) {
            $scoresList = $assignment->scores->map(function ($scoreObj) use ($assignment) {
                return [
                    'criteria_id' => $scoreObj->criteria_id,
                    'criteria_name' => $scoreObj->criteria?->name ?? '-',
                    'category' => $scoreObj->criteria?->category ?? '',
                    'item_type' => $scoreObj->criteria?->item_type ?? 'hybrid',
                    'scale_max' => (int) ($scoreObj->criteria?->scale_max ?: ($assignment->template?->scale_max ?: 5)),
                    'score' => $scoreObj->score,
                    'pass_status' => $scoreObj->pass_status,
                    'comment' => $scoreObj->comment,
                ];
            })->values()->toArray();

            $masked[] = [
                'alias_label' => 'ผู้ทรงคุณวุฒิคนที่ ' . ($idx + 1),
                'assignment_id' => $assignment->id,
                'result_decision' => $assignment->result_decision,
                'result_decision_label' => $assignment->result_decision_label,
                'total_score' => $assignment->total_score,
                'max_possible_score' => $assignment->max_possible_score,
                'score_percentage' => $assignment->score_percentage,
                'general_comment' => $assignment->general_comment,
                'has_annotated_file' => !empty($assignment->annotated_file_path),
                'submitted_at' => $assignment->submitted_at,
                'scores' => $scoresList,
            ];
        }

        return $masked;
    }

    /**
     * Notify submitter and project researchers who have User accounts.
     */
    protected function notifyProjectMembers(RdbReviewSubmission $submission, $notification): void
    {
        $userIds = [];

        if (!empty($submission->submitted_by)) {
            $userIds[] = (int) $submission->submitted_by;
        }

        $project = $submission->project;
        if ($project) {
            if (!empty($project->user_created)) {
                $userIds[] = (int) $project->user_created;
            }

            $researcherIds = $project->rdbProjectWorks()->pluck('researcher_id')->filter()->toArray();
            if (!empty($researcherIds)) {
                $linkedUsers = User::whereIn('researcher_id', $researcherIds)->pluck('id')->toArray();
                foreach ($linkedUsers as $uid) {
                    $userIds[] = (int) $uid;
                }
            }
        }

        $userIds = array_values(array_unique(array_filter($userIds)));
        if (empty($userIds)) {
            return;
        }

        $users = User::whereIn('id', $userIds)->get();
        foreach ($users as $u) {
            try {
                $u->notify($notification);
            } catch (\Throwable $e) {
                Log::warning('Failed to notify project member #' . $u->id . ': ' . $e->getMessage());
            }
        }
    }
}
