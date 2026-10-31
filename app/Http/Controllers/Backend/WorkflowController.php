<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\RejectWorkflowItemRequest;
use App\Http\Requests\Backend\RequestUnlockWorkflowItemRequest;
use App\Models\AuditLog;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\RdbDip;
use App\Models\RdbProjectUtilize;
use App\Models\User;
use App\Notifications\ResearchItemApprovedNotification;
use App\Notifications\ResearchItemRejectedNotification;
use App\Notifications\ResearchItemSubmittedNotification;
use App\Notifications\ResearchItemUnlockRequestedNotification;
use App\Notifications\ResearchItemUnlockApprovedNotification;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    private function resolveModel(string $type, $id)
    {
        return match ($type) {
            'project' => RdbProject::withoutGlobalScopes()->findOrFail($id),
            'published' => RdbPublished::withoutGlobalScopes()->findOrFail($id),
            'dip' => RdbDip::withoutGlobalScopes()->findOrFail($id),
            'utilize' => RdbProjectUtilize::withoutGlobalScopes()->findOrFail($id),
            default => abort(404, 'ไม่พบประเภทโมดูลที่ระบุ'),
        };
    }

    /**
     * Review Queue View for Domain Admins and Faculty Coordinators
     */
    public function queue(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasRole('The Creator') && $user->id !== 129 && !$user->hasRole('Admin') && !$user->hasRole('Admin Research') && !$user->hasRole('Admin Project') && !$user->hasRole('Admin Published') && !$user->hasRole('Admin Dip') && !$user->hasRole('Admin Utilize') && !$user->hasRole('Faculty Coordinator')) {
            abort(403, 'เฉพาะผู้ดูแลระบบและผู้ประสานงานเท่านั้นที่สามารถเข้าถึงคิวการตรวจรับงานได้');
        }

        $activeScope = $user->getActiveRoleScope();
        $deptId = ($activeScope && $activeScope->role_name === 'Faculty Coordinator') ? $activeScope->department_id : null;

        // 1. Projects Queue
        $projectQuery = RdbProject::withoutGlobalScopes()->where('workflow_status', 'pending_review')->with(['researchers', 'department']);
        if ($deptId) {
            $projectQuery->where('department_id', $deptId);
        }
        $projects = $projectQuery->orderBy('submitted_at', 'asc')->paginate(10, ['*'], 'projects_page');

        // 2. Publications Queue
        $pubQuery = RdbPublished::withoutGlobalScopes()->where('workflow_status', 'pending_review')->with(['researcher', 'department', 'pubtype']);
        if ($deptId) {
            $pubQuery->where('department_id', $deptId);
        }
        $publications = $pubQuery->orderBy('submitted_at', 'asc')->paginate(10, ['*'], 'pubs_page');

        // 3. IP / DIP Queue
        $dipQuery = RdbDip::withoutGlobalScopes()->where('workflow_status', 'pending_review')->with(['researcher', 'dipType']);
        $dips = $dipQuery->orderBy('submitted_at', 'asc')->paginate(10, ['*'], 'dips_page');

        // 4. Utilizations Queue
        $utilizeQuery = RdbProjectUtilize::withoutGlobalScopes()->where('workflow_status', 'pending_review')->with(['project', 'utilizeType']);
        $utilizes = $utilizeQuery->orderBy('submitted_at', 'asc')->paginate(10, ['*'], 'utilizes_page');

        // 5. Unlock Requests Queue (All models with workflow_status = unlock_requested)
        $unlockProjQuery = RdbProject::withoutGlobalScopes()->where('workflow_status', 'unlock_requested')->with(['researchers', 'department']);
        $unlockPubQuery = RdbPublished::withoutGlobalScopes()->where('workflow_status', 'unlock_requested')->with(['researcher', 'department', 'pubtype']);
        $unlockDipQuery = RdbDip::withoutGlobalScopes()->where('workflow_status', 'unlock_requested')->with(['researcher', 'dipType']);
        $unlockUtzQuery = RdbProjectUtilize::withoutGlobalScopes()->where('workflow_status', 'unlock_requested')->with(['project', 'utilizeType']);
        if ($deptId) {
            $unlockProjQuery->where('department_id', $deptId);
            $unlockPubQuery->where('department_id', $deptId);
        }
        $unlockProjects = $unlockProjQuery->orderBy('unlock_requested_at', 'desc')->get();
        $unlockPublications = $unlockPubQuery->orderBy('unlock_requested_at', 'desc')->get();
        $unlockDips = $unlockDipQuery->orderBy('unlock_requested_at', 'desc')->get();
        $unlockUtilizes = $unlockUtzQuery->orderBy('unlock_requested_at', 'desc')->get();
        $totalUnlocks = $unlockProjects->count() + $unlockPublications->count() + $unlockDips->count() + $unlockUtilizes->count();

        $activeTab = $request->get('tab', 'projects');

        return view('backend.workflow.queue', compact(
            'projects', 'publications', 'dips', 'utilizes', 
            'unlockProjects', 'unlockPublications', 'unlockDips', 'unlockUtilizes', 'totalUnlocks',
            'activeTab', 'deptId'
        ));
    }

    /**
     * Submit an item for review (Draft / Revision Required -> Pending Review)
     */
    public function submit(Request $request, string $type, $id)
    {
        $item = $this->resolveModel($type, $id);

        $item->workflow_status = 'pending_review';
        $item->submitted_at = now();
        $item->save();

        AuditLog::record(
            event: 'submit',
            auditable: $item,
            remarks: 'ส่งผลงานเข้าสู่กระบวนการตรวจรับ (Pending Review)'
        );

        // Notify admins
        try {
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('item_name', ['The Creator', 'Admin', 'Admin Research', 'Admin Project', 'Admin Published', 'Admin Dip', 'Admin Utilize']);
            })->orWhere('id', 129)->get();

            foreach ($admins as $admin) {
                $admin->notify(new ResearchItemSubmittedNotification($item, $type));
            }
        } catch (\Throwable $e) {
            \Log::warning('Submit notification warning: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'ส่งผลงานเข้าสู่กระบวนการตรวจรับเรียบร้อยแล้ว');
    }

    /**
     * Approve an item (Pending Review -> Published)
     */
    public function approve(Request $request, string $type, $id)
    {
        $item = $this->resolveModel($type, $id);

        $item->workflow_status = 'published';
        $item->reviewed_by = auth()->id();
        $item->reviewed_at = now();
        $item->rejection_remarks = null;
        $item->unlock_reason = null;
        if (isset($item->data_show)) {
            $item->data_show = 1;
        }
        $item->save();

        AuditLog::record(
            event: 'approve',
            auditable: $item,
            remarks: 'อนุมัติผลงานเพื่อเผยแพร่สู่สาธารณะ (Published)'
        );

        // Notify author if researcher has user account
        try {
            $researcherId = $item->researcher_id ?? ($item->researchers?->first()?->researcher_id ?? null);
            $author = null;
            if ($researcherId) {
                $author = User::where('researcher_id', $researcherId)->first();
            }
            if (!$author && $item->user_created) {
                $author = User::find($item->user_created);
            }
            if ($author) {
                $author->notify(new ResearchItemApprovedNotification($item, $type));
            }
        } catch (\Throwable $e) {
            \Log::warning('Approve notification warning: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'อนุมัติผลงานและเผยแพร่สู่สาธารณะเรียบร้อยแล้ว');
    }

    /**
     * Reject an item with remarks (Pending Review -> Revision Required)
     */
    public function reject(RejectWorkflowItemRequest $request, string $type, $id)
    {
        $validated = $request->validated();

        $item = $this->resolveModel($type, $id);

        $item->workflow_status = 'revision_required';
        $item->reviewed_by = auth()->id();
        $item->reviewed_at = now();
        $item->rejection_remarks = trim($validated['remarks']);
        $item->save();

        AuditLog::record(
            event: 'reject',
            auditable: $item,
            remarks: "ส่งกลับแก้ไข: {$item->rejection_remarks}"
        );

        // Notify author
        try {
            $researcherId = $item->researcher_id ?? ($item->researchers?->first()?->researcher_id ?? null);
            $author = null;
            if ($researcherId) {
                $author = User::where('researcher_id', $researcherId)->first();
            }
            if (!$author && $item->user_created) {
                $author = User::find($item->user_created);
            }
            if ($author) {
                $author->notify(new ResearchItemRejectedNotification($item, $type, $item->rejection_remarks));
            }
        } catch (\Throwable $e) {
            \Log::warning('Reject notification warning: ' . $e->getMessage());
        }

        return redirect()->back()->with('warning', 'ส่งผลงานกลับไปให้นักวิจัยแก้ไขเรียบร้อยแล้ว');
    }

    /**
     * Request Unlock to Edit for a Published Item (Published -> Unlock Requested)
     */
    public function requestUnlock(RequestUnlockWorkflowItemRequest $request, string $type, $id)
    {
        $validated = $request->validated();

        $item = $this->resolveModel($type, $id);
        $this->authorize('requestUnlock', $item);

        $item->workflow_status = 'unlock_requested';
        $item->unlock_reason = trim($validated['reason']);
        $item->unlock_requested_at = now();
        $item->save();

        AuditLog::record(
            event: 'request_unlock',
            auditable: $item,
            remarks: "ยื่นคำขอเปิดสิทธิ์แก้ไข: {$item->unlock_reason}"
        );

        // Notify domain admins
        try {
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('item_name', ['The Creator', 'Admin', 'Admin Research', 'Admin Project', 'Admin Published', 'Admin Dip', 'Admin Utilize']);
            })->orWhere('id', 129)->get();

            foreach ($admins as $admin) {
                $admin->notify(new ResearchItemUnlockRequestedNotification($item, $type, $item->unlock_reason));
            }
        } catch (\Throwable $e) {
            \Log::warning('Unlock requested notification warning: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'ส่งคำขอเปิดสิทธิ์แก้ไขไปยังผู้ดูแลระบบเรียบร้อยแล้ว เมื่อได้รับการอนุมัติ ผลงานจะถูกย้ายไปยังฉบับร่าง');
    }

    /**
     * Approve Unlock and Move Item to Draft (Unlock Requested / Published -> Draft)
     */
    public function approveUnlock(Request $request, string $type, $id)
    {
        $item = $this->resolveModel($type, $id);
        $this->authorize('approveUnlock', $item);

        // User requirement: ย้ายงานนั้น ๆ ไปไว้ที่ ร่าง เพื่อรอตรวจสอบอีกที
        $item->workflow_status = 'draft';
        $item->reviewed_by = auth()->id();
        $item->reviewed_at = now();
        $item->save();

        AuditLog::record(
            event: 'unlock_approved',
            auditable: $item,
            remarks: 'ผู้ดูแลระบบอนุมัติคำขอแก้ไข และย้ายผลงานกลับไปเป็นฉบับร่าง (Draft) เพื่อรอตรวจสอบอีกที'
        );

        // Notify author
        try {
            $researcherId = $item->researcher_id ?? ($item->researchers?->first()?->researcher_id ?? null);
            $author = null;
            if ($researcherId) {
                $author = User::where('researcher_id', $researcherId)->first();
            }
            if (!$author && $item->user_created) {
                $author = User::find($item->user_created);
            }
            if ($author) {
                $author->notify(new ResearchItemUnlockApprovedNotification($item, $type));
            }
        } catch (\Throwable $e) {
            \Log::warning('Unlock approved notification warning: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'อนุมัติคำขอแก้ไขเรียบร้อยแล้ว ผลงานถูกย้ายไปยัง "ฉบับร่าง" เพื่อให้นักวิจัยดำเนินการแก้ไขและส่งตรวจใหม่');
    }

    /**
     * Reject Unlock Request (Unlock Requested -> Published)
     */
    public function rejectUnlock(Request $request, string $type, $id)
    {
        $item = $this->resolveModel($type, $id);
        $this->authorize('approveUnlock', $item);

        $item->workflow_status = 'published';
        $item->save();

        AuditLog::record(
            event: 'unlock_rejected',
            auditable: $item,
            remarks: 'ผู้ดูแลระบบปฏิเสธคำขอแก้ไข คงสถานะเผยแพร่ตามเดิม'
        );

        return redirect()->back()->with('warning', 'ปฏิเสธคำขอเปิดสิทธิ์แก้ไข คงสถานะเผยแพร่ตามเดิม');
    }
}
