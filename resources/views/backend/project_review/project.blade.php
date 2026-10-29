@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

<x-page-header
    title="การประเมินเล่ม/ร่างรายงานวิจัยฉบับสมบูรณ์"
    icon="bi-clipboard2-check"
    :backRoute="route('backend.rdb_project.show', $project->pro_id)"
    :showPrint="true"
    :breadcrumbs="[
        'แดชบอร์ด' => route('backend.site.index'),
        'ระบบประเมินเล่มรายงานวิจัย' => route('backend.project_review.index'),
        'รายละเอียดโครงการ' => route('backend.rdb_project.show', $project->pro_id),
        'พื้นที่ประเมินเล่มรายงานวิจัย' => ''
    ]"
>
    <a href="{{ route('backend.project_review.index') }}" class="btn btn-outline-primary d-inline-flex justify-content-center align-items-center" style="height: 38px; font-size: 0.85rem;">
        <i class="bi bi-list-check me-1.5"></i> รายการประเมินทั้งหมด
    </a>
</x-page-header>

@if(session('success'))
    <div class="alert alert-success border border-success-subtle shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-check-circle-fill fs-5 me-2 text-success-emphasis"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif
@if(session('warning'))
    <div class="alert alert-warning border border-warning-subtle shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-exclamation-circle-fill fs-5 me-2 text-warning-emphasis"></i>
        <div>{{ session('warning') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger border border-danger-subtle shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger-emphasis"></i>
        <div>{{ session('error') }}</div>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger border border-danger-subtle shadow-sm mb-4">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon me-1"></i> กรุณาตรวจสอบข้อมูล:</div>
        <ul class="mb-0 small">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Project Summary Banner Card --}}
<div class="card shadow-sm border border-secondary-subtle bg-body mb-4">
    <div class="card-body p-4">
        <div class="row g-3 align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle font-monospace px-2.5 py-1.5" style="font-variant-numeric: tabular-nums;">
                        {{ $project->pro_code ?: 'ID: ' . $project->pro_id }}
                    </span>
                    @if($project->year)
                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2.5 py-1.5" style="font-variant-numeric: tabular-nums;">
                            <i class="bi bi-calendar3 me-1"></i>ปีงบประมาณ {{ $project->year->year_name }}
                        </span>
                    @endif
                    @if($project->department)
                        <span class="badge bg-body-tertiary text-body border border-secondary-subtle px-2.5 py-1.5">
                            <i class="bi bi-building me-1"></i>{{ $project->department->department_nameTH }}
                        </span>
                    @endif
                    @if($project->status)
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1.5">
                            {{ $project->status->ps_name }}
                        </span>
                    @endif
                </div>
                <h5 class="fw-bold text-body-emphasis mb-1">
                    {{ strip_tags(html_entity_decode($project->pro_nameTH, ENT_QUOTES, 'UTF-8')) }}
                </h5>
                @if($project->pro_nameEN)
                    <div class="text-body-secondary small mb-2">
                        {{ strip_tags(html_entity_decode($project->pro_nameEN, ENT_QUOTES, 'UTF-8')) }}
                    </div>
                @endif
                <div class="small text-body-secondary d-flex flex-wrap gap-3 mt-2">
                    <div>
                        <strong class="text-body">คณะผู้วิจัย:</strong>
                        @if($project->rdbProjectWorks && $project->rdbProjectWorks->isNotEmpty())
                            @foreach($project->rdbProjectWorks as $work)
                                @if($work->researcher)
                                    <span class="me-2">
                                        {{ ($work->researcher->prefix->prefix_nameTH ?? '') . $work->researcher->researcher_fname . ' ' . $work->researcher->researcher_lname }}
                                        @if($work->position)
                                            <span class="text-body-secondary">({{ $work->position->position_nameTH }})</span>
                                        @endif
                                    </span>
                                @endif
                            @endforeach
                        @else
                            <span>-</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="p-3 rounded-3 bg-body-secondary border border-secondary-subtle">
                    <div class="small fw-bold text-body-secondary text-uppercase mb-1">สถานะไฟล์เล่มสมบูรณ์ในฐานข้อมูลโครงการ</div>
                    @if(!empty($project->pro_file))
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle mb-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>ซิงค์ไฟล์เล่มสมบูรณ์แล้ว
                                </span>
                                <div class="small text-body-secondary" style="font-variant-numeric: tabular-nums;">
                                    จำนวนหน้า: <strong>{{ $project->pro_page_num ? number_format($project->pro_page_num) . ' หน้า' : 'ไม่ระบุ' }}</strong>
                                </div>
                            </div>
                            <a href="{{ route('backend.rdb_project.view_report', $project->pro_id) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-file-earmark-pdf me-1"></i> เปิดไฟล์เล่มจริง
                            </a>
                        </div>
                    @else
                        <div class="d-flex align-items-center gap-2 text-body-secondary small">
                            <i class="bi bi-hourglass-split text-warning-emphasis fs-5"></i>
                            <span>อยู่ระหว่างกระบวนการประเมินเล่มรายงาน (เมื่ออนุมัติขั้นสุดท้าย ระบบจะซิงค์ไฟล์เข้าโครงการอัตโนมัติ)</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Upload / Submit New Report Round Card (for Researcher or Admin) --}}
@if($isMember || $isAdmin)
    @php
        $nextRoundNumber = ($submissions->max('round_number') ?? 0) + 1;
        $latestSub = $submissions->first();
        $defaultSubType = !$latestSub ? 'initial_draft' : ($latestSub->status === 'round_closed' ? 'final_report' : 'revised_draft');
        $defaultTitle = match($defaultSubType) {
            'initial_draft' => "ร่างรายงานวิจัยฉบับสมบูรณ์ (รอบที่ {$nextRoundNumber})",
            'final_report' => "เล่มรายงานวิจัยฉบับสมบูรณ์ขั้นสุดท้าย (รอบที่ {$nextRoundNumber})",
            default => "ร่างรายงานวิจัยฉบับแก้ไขตามข้อเสนอแนะ (รอบที่ {$nextRoundNumber})",
        };
    @endphp
    <x-card title="ส่งเล่ม / ร่างรายงานวิจัยฉบับสมบูรณ์ (รอบที่ {{ $nextRoundNumber }})" icon="bi bi-cloud-arrow-up" color="primary">
        <form action="{{ route('backend.project_review.submit_report', $project->pro_id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label fw-semibold">ประเภทการส่งเอกสาร <span class="text-danger">*</span></label>
                <select name="submission_type" class="form-select border-secondary-subtle" required>
                    <option value="initial_draft" @selected(old('submission_type', $defaultSubType) === 'initial_draft')>1. ส่งร่างรายงานวิจัยฉบับสมบูรณ์เพื่อรับการประเมิน (Initial Draft)</option>
                    <option value="revised_draft" @selected(old('submission_type', $defaultSubType) === 'revised_draft')>2. ส่งเล่มรายงานฉบับแก้ไขตามข้อเสนอแนะผู้ทรงคุณวุฒิ (Revised Draft)</option>
                    <option value="final_report" @selected(old('submission_type', $defaultSubType) === 'final_report')>3. ส่งเล่มรายงานวิจัยฉบับสมบูรณ์ขั้นสุดท้ายเพื่อขออนุมัติปิดโครงการ (Final Report)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">ชื่อเอกสาร / หัวข้อการส่งประเมิน <span class="text-danger">*</span></label>
                <input type="text" name="submission_title" class="form-control border-secondary-subtle" required value="{{ old('submission_title', $defaultTitle) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">ไฟล์เล่มรายงานวิจัย (PDF หรือ Word, ไม่เกิน 50 MB) <span class="text-danger">*</span></label>
                <input type="file" name="report_file" class="form-control border-secondary-subtle" accept=".pdf,.doc,.docx" required>
                <div class="form-text small">ระบบจะนับจำนวนหน้า PDF อัตโนมัติด้วยสถาปัตยกรรม 4-Tier Engine ทันทีที่อัปโหลด</div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">หมายเหตุ / สรุปประเด็นที่ได้ปรับปรุงแก้ไข (ถ้ามี)</label>
                <textarea name="note" rows="2" class="form-control border-secondary-subtle" placeholder="ระบุรายละเอียดเพิ่มเติม หรือสรุปหัวข้อที่ได้ปรับปรุงแก้ไขตามคำแนะนำของผู้ทรงคุณวุฒิ...">{{ old('note') }}</textarea>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-cloud-upload me-1.5"></i> อัปโหลดและส่งเล่มรายงานวิจัย (รอบที่ {{ $nextRoundNumber }})
                </button>
            </div>
        </form>
    </x-card>
@endif

{{-- Submissions Timeline by Round --}}
@if($submissions->isEmpty())
    <div class="card shadow-sm border border-secondary-subtle bg-body">
        <div class="card-body text-center py-5">
            <i class="bi bi-journal-arrow-up fs-1 text-body-secondary opacity-50 d-block mb-3"></i>
            <h6 class="fw-semibold text-body">ยังไม่มีประวัติการส่งเล่มรายงานวิจัยเพื่อรับการประเมิน</h6>
            <p class="text-body-secondary small mb-0">กรุณาใช้แบบฟอร์มด้านบนเพื่ออัปโหลดร่างรายงานวิจัยฉบับสมบูรณ์สำหรับรอบที่ 1</p>
        </div>
    </div>
@else
    @foreach($submissions as $submission)
        @php
            $statusBadge = match($submission->status) {
                'approved' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                'under_review' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                'submitted', 'final_submitted' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                'revision_required', 'round_closed' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                'disputed', 'rejected' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                default => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
            };
            $maskedReviews = $maskedReviewsBySubmission[$submission->id] ?? [];
        @endphp

        <div class="card shadow-sm border border-secondary-subtle bg-body mb-4" id="round-{{ $submission->round_number }}">
            {{-- Round Header --}}
            <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-primary text-white px-3 py-1.5 fs-6" style="font-variant-numeric: tabular-nums;">
                            รอบที่ {{ $submission->round_number }}
                        </span>
                        <h5 class="mb-0 fw-bold text-body-emphasis">{{ $submission->submission_title }}</h5>
                        <span class="badge bg-body-secondary text-body border border-secondary-subtle">
                            {{ $submission->submission_type_label }}
                        </span>
                        <span class="badge {{ $statusBadge }} border px-2.5 py-1.5">
                            {{ $submission->status_label }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if($submission->page_count)
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1.5" style="font-variant-numeric: tabular-nums;">
                                <i class="bi bi-file-earmark-pdf me-1"></i>{{ number_format($submission->page_count) }} หน้า
                            </span>
                        @endif
                        <span class="small text-body-secondary" style="font-variant-numeric: tabular-nums;">
                            <i class="bi bi-clock me-1"></i>ส่งเมื่อ {{ $submission->submitted_at ? \App\Helpers\ThaiDateHelper::format($submission->submitted_at, false, true) . ' ' . $submission->submitted_at->format('H:i น.') : '-' }}
                        </span>
                        @if($submission->file_path)
                            <a href="{{ route('backend.project_review.submission.file', $submission->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-arrow-down me-1"></i> เปิดดู / ดาวน์โหลดเล่มที่ส่ง
                            </a>
                        @endif
                    </div>
                </div>
                @if($submission->note)
                    <div class="mt-2 p-2.5 rounded bg-body border border-secondary-subtle small text-body">
                        <strong class="text-body-secondary"><i class="bi bi-chat-left-text me-1"></i>หมายเหตุจากผู้ส่ง:</strong> {{ $submission->note }}
                    </div>
                @endif
            </div>

            <div class="card-body p-4">
                {{-- ===================================================================== --}}
                {{-- SECTION A: ADMIN RESEARCH CONTROL PANEL (Visible ONLY to $isAdmin)    --}}
                {{-- ===================================================================== --}}
                @if($isAdmin)
                    <div class="p-3 rounded-3 bg-body-secondary border border-secondary-subtle mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom border-secondary-subtle">
                            <h6 class="fw-bold text-primary-emphasis mb-0">
                                <i class="bi bi-shield-lock-fill me-1.5"></i> แผงควบคุมเจ้าหน้าที่บริหารงานวิจัย (Admin Control Panel — ข้อมูลรายชื่อผู้ทรงคุณวุฒิเป็นความลับเฉพาะเจ้าหน้าที่)
                            </h6>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                <i class="bi bi-eye-slash me-1"></i>นักวิจัยจะไม่เห็นชื่อจริงของผู้ทรงคุณวุฒิ (Single-Blind)
                            </span>
                        </div>

                        {{-- 1. Assign Reviewers Form --}}
                        <form action="{{ route('backend.project_review.assign', $submission->id) }}" method="POST" class="row g-3 align-items-end mb-4">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">เลือกแบบฟอร์มเกณฑ์การประเมิน <span class="text-danger">*</span></label>
                                <select name="template_id" class="form-select form-select-sm border-secondary-subtle" required>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}" @selected($tpl->is_default)>
                                            {{ $tpl->title }} ({{ $tpl->criteria->count() }} ข้อ)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">ค้นหาและเลือกผู้ทรงคุณวุฒิ (เลือกได้ 1 ถึงหลายท่าน) <span class="text-danger">*</span></label>
                                <select name="reviewer_ids[]" id="reviewer_select_{{ $submission->id }}" class="form-select form-select-sm reviewer-tomselect" multiple placeholder="พิมพ์ชื่อ-นามสกุลผู้ทรงคุณวุฒิ (อย่างน้อย 2 ตัวอักษร)...">
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">กำหนดส่งผลประเมิน</label>
                                <div class="d-flex gap-2">
                                    <input type="date" name="due_date" class="form-control form-control-sm border-secondary-subtle" value="{{ now()->addDays(14)->format('Y-m-d') }}" style="font-variant-numeric: tabular-nums;">
                                    <button type="submit" class="btn btn-sm btn-primary text-nowrap px-3">
                                        <i class="bi bi-person-plus me-1"></i> มอบหมาย
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- 2. Assigned Reviewers Tracking Table (Admin Only) --}}
                        <div class="table-responsive bg-body rounded border border-secondary-subtle mb-3">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="bg-body-tertiary text-body-secondary small">
                                    <tr>
                                        <th>ลำดับนามแฝง</th>
                                        <th>ชื่อผู้ทรงคุณวุฒิ (เฉพาะ Admin)</th>
                                        <th>แบบฟอร์มเกณฑ์</th>
                                        <th class="text-center">สถานะตอบรับ</th>
                                        <th class="text-center">ผลการประเมิน & คะแนน</th>
                                        <th class="text-center">ไฟล์แนบ</th>
                                        <th class="text-end">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($submission->assignments as $aIdx => $assignment)
                                        @php
                                            $realReviewerName = $assignment->researcher
                                                ? (($assignment->researcher->prefix->prefix_nameTH ?? '') . $assignment->researcher->researcher_fname . ' ' . $assignment->researcher->researcher_lname)
                                                : ($assignment->reviewer->name ?? $assignment->reviewer->username ?? 'User #' . $assignment->reviewer_id);
                                            $deptName = $assignment->researcher?->department?->department_nameTH
                                                ?? $assignment->reviewer?->researcher?->department?->department_nameTH
                                                ?? '';
                                            $asgnBadge = match($assignment->status) {
                                                'completed' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                                'accepted' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                                'pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                                'declined' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                                default => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                                            };
                                            $decBadge = match($assignment->result_decision) {
                                                'pass' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                                'minor_revision' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                                'major_revision' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                                'reject' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                                default => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="small fw-semibold text-body-secondary" style="font-variant-numeric: tabular-nums;">
                                                ผู้ประเมิน #{{ $aIdx + 1 }}
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-body small">{{ $realReviewerName }}</div>
                                                @if($deptName)
                                                    <div class="text-body-secondary" style="font-size: 0.75rem;">{{ $deptName }}</div>
                                                @endif
                                            </td>
                                            <td class="small text-body-secondary">{{ $assignment->template->title ?? '-' }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $asgnBadge }} border">{{ $assignment->status_label }}</span>
                                                @if($assignment->status === 'declined' && $assignment->decline_reason)
                                                    <div class="alert alert-danger py-1 px-2 mt-1 mb-0 small text-start">
                                                        <strong>เหตุผลที่ปฏิเสธ:</strong> {{ $assignment->decline_reason }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($assignment->status === 'completed')
                                                    <div><span class="badge {{ $decBadge }} border">{{ $assignment->result_decision_label }}</span></div>
                                                    @if($assignment->total_score !== null)
                                                        <div class="small fw-bold text-body mt-1" style="font-variant-numeric: tabular-nums;">
                                                            {{ number_format($assignment->total_score, 2) }} / {{ number_format($assignment->max_possible_score, 2) }}
                                                            ({{ number_format($assignment->score_percentage, 2) }}%)
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-body-secondary small">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($assignment->annotated_file_path)
                                                    <a href="{{ route('backend.project_review.assignment.annotated_file', $assignment->id) }}" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2">
                                                        <i class="bi bi-paperclip me-1"></i>ไฟล์คอมเมนต์
                                                    </a>
                                                @else
                                                    <span class="text-body-secondary small">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <a href="{{ route('backend.project_review.evaluate', $assignment->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="ดูหรือกรอกใบประเมิน">
                                                        <i class="bi bi-journal-check me-1"></i>ใบประเมิน
                                                    </a>
                                                    @if($assignment->status !== 'completed')
                                                        <form action="{{ route('backend.project_review.assignment.remove', $assignment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันยกเลิกการมอบหมายผู้ทรงคุณวุฒิท่านนี้หรือไม่?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="ยกเลิกการมอบหมาย">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-body-secondary py-3 small">
                                                ยังไม่ได้มอบหมายผู้ทรงคุณวุฒิในรอบนี้ กรุณาเลือกผู้ทรงคุณวุฒิด้านบน
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- 3. Admin Actions: Close Round (Single-Blind Release) & Final Approval Gate --}}
                        <div class="row g-3">
                            <div class="col-lg-7">
                                <div class="p-3 rounded bg-body border border-secondary-subtle h-100">
                                    <h6 class="fw-bold text-body-emphasis mb-2">
                                        <i class="bi bi-send-check text-primary me-1.5"></i> สรุปปิดรอบการประเมิน & ส่งผลประเมินแบบปกปิดรายชื่อ (Single-Blind) ให้นักวิจัย
                                    </h6>
                                    <form action="{{ route('backend.project_review.close_round', $submission->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold">มติผลการพิจารณาในรอบนี้ <span class="text-danger">*</span></label>
                                            <select name="next_status" class="form-select form-select-sm border-secondary-subtle" required>
                                                <option value="revision_required" @selected($submission->status === 'revision_required')>ให้ปรับปรุงแก้ไขตามข้อเสนอแนะผู้ทรงคุณวุฒิ (Revision Required)</option>
                                                <option value="round_closed" @selected($submission->status === 'round_closed')>ผ่านการประเมิน รอส่งเล่มสมบูรณ์ขั้นสุดท้าย (Round Closed / Passed)</option>
                                                <option value="rejected" @selected($submission->status === 'rejected')>ไม่ผ่านการประเมิน (Rejected)</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold">สรุปมติ / ข้อสังเกตเพิ่มเติมจากเจ้าหน้าที่บริหารงานวิจัย</label>
                                            <textarea name="admin_summary" rows="2" class="form-control form-control-sm border-secondary-subtle" placeholder="สรุปภาพรวมหรือคำแนะนำเพิ่มเติมถึงนักวิจัย...">{{ $submission->admin_summary }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="bi bi-megaphone me-1"></i> สรุปปิดรอบและส่งผลประเมิน (Single-Blind) ให้นักวิจัย
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="p-3 rounded bg-success-subtle border border-success-subtle h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-success-emphasis mb-2">
                                            <i class="bi bi-patch-check-fill me-1.5"></i> อนุมัติเล่มรายงานวิจัยฉบับสมบูรณ์ขั้นสุดท้าย (Final Approval & Auto-Sync)
                                        </h6>
                                        <p class="small text-body-secondary mb-2">
                                            เมื่อนักวิจัยส่งเล่มสมบูรณ์ที่แก้ไขครบถ้วนแล้ว การกดอนุมัติขั้นสุดท้ายจะคัดลอกไฟล์ไปที่ <code>rdb_project.pro_file</code> พร้อมบันทึกจำนวนหน้า ({{ $submission->page_count ?? 'นับอัตโนมัติ' }} หน้า) และปรับสถานะโครงการเป็นเสร็จสิ้นทันที
                                        </p>
                                    </div>
                                    <form action="{{ route('backend.project_review.approve_final', $submission->id) }}" method="POST" onsubmit="return confirm('ยืนยันการอนุมัติเล่มรายงานวิจัยฉบับสมบูรณ์ขั้นสุดท้าย และซิงค์ไฟล์เข้าสู่ข้อมูลโครงการวิจัยหรือไม่?');">
                                        @csrf
                                        <div class="mb-2">
                                            <input type="text" name="admin_summary" class="form-control form-control-sm border-success-subtle bg-body" value="{{ $submission->admin_summary }}" placeholder="บันทึกหมายเหตุการอนุมัติเล่มสมบูรณ์ (ถ้ามี)...">
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-success w-100 fw-semibold">
                                            <i class="bi bi-check2-all me-1.5"></i> อนุมัติเล่มสมบูรณ์ขั้นสุดท้าย & ซิงค์เข้าโครงการวิจัย
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ===================================================================== --}}
                {{-- SECTION B: SINGLE-BLIND EVALUATION RESULTS (Masked Reviewer Aliases)  --}}
                {{-- ===================================================================== --}}
                <div class="mb-4">
                    <h6 class="fw-bold text-body-emphasis mb-3 d-flex align-items-center">
                        <i class="bi bi-person-Rolodex text-primary me-2"></i>
                        ผลการประเมินจากผู้ทรงคุณวุฒิ (ระบบปกปิดรายชื่อผู้ทรงคุณวุฒิ Single-Blind Peer Review)
                    </h6>

                    @if($submission->admin_summary)
                        <div class="alert alert-info border border-info-subtle shadow-sm mb-3">
                            <div class="fw-bold text-info-emphasis mb-1">
                                <i class="bi bi-chat-square-quote-fill me-1.5"></i> สรุปมติ/ข้อสังเกตจากเจ้าหน้าที่บริหารงานวิจัย:
                            </div>
                            <div class="small text-body">{{ $submission->admin_summary }}</div>
                        </div>
                    @endif

                    @if(empty($maskedReviews))
                        <div class="p-4 rounded bg-body-tertiary border border-secondary-subtle text-center text-body-secondary small">
                            <i class="bi bi-hourglass-split me-1"></i>
                            อยู่ระหว่างดำเนินการประเมินโดยผู้ทรงคุณวุฒิ หรือรอเจ้าหน้าที่สรุปผลการประเมินในรอบนี้
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($maskedReviews as $review)
                                @php
                                    $rDecBadge = match($review['result_decision'] ?? '') {
                                        'pass' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                        'minor_revision' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                        'major_revision' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                        'reject' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                        default => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                                    };
                                @endphp
                                <div class="col-12">
                                    <div class="card border border-secondary-subtle bg-body shadow-sm">
                                        <div class="card-header bg-body-secondary border-bottom border-secondary-subtle py-2.5 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                {{-- CRITICAL SINGLE-BLIND: Only display alias_label, NEVER reviewer name or ID --}}
                                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle px-2.5 py-1.5 fw-bold">
                                                    <i class="bi bi-person-check-fill me-1"></i>{{ $review['alias_label'] }}
                                                </span>
                                                <span class="badge {{ $rDecBadge }} border px-2.5 py-1.5">
                                                    {{ $review['result_decision_label'] }}
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                @if($review['score_percentage'] !== null)
                                                    <span class="small fw-bold text-body-emphasis" style="font-variant-numeric: tabular-nums;">
                                                        คะแนนรวม: {{ number_format($review['total_score'], 2) }} / {{ number_format($review['max_possible_score'], 2) }}
                                                        ({{ number_format($review['score_percentage'], 2) }}%)
                                                    </span>
                                                @endif
                                                @if(!empty($review['has_annotated_file']))
                                                    <a href="{{ route('backend.project_review.assignment.annotated_file', $review['assignment_id']) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0.5 px-2.5">
                                                        <i class="bi bi-file-earmark-pdf me-1"></i> ดาวน์โหลดไฟล์แนบข้อเสนอแนะ ({{ $review['alias_label'] }})
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-body p-3">
                                            @if(!empty($review['general_comment']))
                                                <div class="p-3 rounded bg-body-tertiary border border-secondary-subtle mb-3">
                                                    <div class="small fw-bold text-body-secondary mb-1">ข้อคิดเห็นและข้อเสนอแนะภาพรวม:</div>
                                                    <div class="text-body small" style="white-space: pre-line;">{{ $review['general_comment'] }}</div>
                                                </div>
                                            @endif

                                            @if(!empty($review['scores']))
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0 border-secondary-subtle">
                                                        <thead class="bg-body-tertiary text-body-secondary small">
                                                            <tr>
                                                                <th style="width: 22%;">หมวดหมู่</th>
                                                                <th style="width: 35%;">ประเด็นการประเมิน</th>
                                                                <th style="width: 15%;" class="text-center">คะแนน / ผลประเมิน</th>
                                                                <th>ข้อเสนอแนะรายข้อ</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($review['scores'] as $sc)
                                                                <tr>
                                                                    <td class="small text-body-secondary">{{ $sc['category'] ?: '-' }}</td>
                                                                    <td class="small fw-semibold text-body">{{ $sc['criteria_name'] }}</td>
                                                                    <td class="text-center small fw-bold" style="font-variant-numeric: tabular-nums;">
                                                                        @if($sc['score'] !== null)
                                                                            <span class="text-primary-emphasis">{{ (float)$sc['score'] == (int)$sc['score'] ? (int)$sc['score'] : number_format($sc['score'], 1) }}</span>
                                                                            <span class="text-body-secondary fw-normal" style="font-size: 0.78rem;">/ {{ $sc['scale_max'] ?? 5 }}</span>
                                                                        @elseif(!empty($sc['pass_status']))
                                                                            @if($sc['pass_status'] === 'pass')
                                                                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">ผ่าน</span>
                                                                            @elseif($sc['pass_status'] === 'revise')
                                                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">ควรแก้ไข</span>
                                                                            @else
                                                                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">ไม่ผ่าน</span>
                                                                            @endif
                                                                        @else
                                                                            <span class="text-body-secondary">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="small text-body">{{ $sc['comment'] ?: '-' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ===================================================================== --}}
                {{-- SECTION C: CLARIFICATION / DISPUTE / REPLY THREAD                     --}}
                {{-- ===================================================================== --}}
                <div class="pt-3 border-top border-secondary-subtle">
                    <h6 class="fw-bold text-body-emphasis mb-3">
                        <i class="bi bi-chat-dots text-warning-emphasis me-1.5"></i>
                        การชี้แจงผลการแก้ไข / ทักท้วงผลการประเมิน (Clarification & Dispute Resolution)
                    </h6>

                    @if($submission->clarifications->isNotEmpty())
                        <div class="d-flex flex-column gap-2 mb-3">
                            @foreach($submission->clarifications as $clar)
                                @php
                                    $isFromAdmin = $clar->sender_role === 'admin';
                                    $actionBadge = match($clar->action_type) {
                                        'dispute' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                        'admin_reply' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                        default => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    };
                                @endphp
                                <div class="p-3 rounded border {{ $isFromAdmin ? 'bg-body-secondary border-primary-subtle' : 'bg-body border-secondary-subtle' }}">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ $actionBadge }} border">{{ $clar->action_type_label }}</span>
                                            <span class="fw-semibold small text-body">
                                                {{ $isFromAdmin ? 'เจ้าหน้าที่บริหารงานวิจัย' : ($clar->user->name ?? 'ผู้วิจัย') }}
                                            </span>
                                        </div>
                                        <span class="text-body-secondary small" style="font-variant-numeric: tabular-nums;">
                                            {{ $clar->created_at ? \App\Helpers\ThaiDateHelper::format($clar->created_at, false, true) . ' ' . $clar->created_at->format('H:i น.') : '' }}
                                        </span>
                                    </div>
                                    <div class="small text-body" style="white-space: pre-line;">{{ $clar->message }}</div>
                                    @if(!empty($clar->attachment_path))
                                        <div class="mt-2">
                                            <a href="{{ route('backend.project_review.clarification.attachment', $clar->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0.5 px-2">
                                                <i class="bi bi-paperclip me-1"></i>{{ $clar->attachment_name ?: 'เปิดดูไฟล์แนบคำชี้แจง' }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('backend.project_review.clarification.store', $submission->id) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end bg-body-tertiary p-3 rounded border border-secondary-subtle">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">ประเภทข้อความ</label>
                            <select name="action_type" class="form-select form-select-sm border-secondary-subtle">
                                @if($isAdmin && !$isMember)
                                    <option value="admin_reply">ข้อความตอบกลับจากเจ้าหน้าที่</option>
                                @else
                                    <option value="clarification">ชี้แจงรายละเอียดการปรับปรุงแก้ไข</option>
                                    <option value="dispute">ทักท้วงผลการประเมิน / ขอคำวินิจฉัยเพิ่มเติม</option>
                                    @if($isAdmin)
                                        <option value="admin_reply">ข้อความตอบกลับจากเจ้าหน้าที่</option>
                                    @endif
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">ข้อความชี้แจง / ความเห็น <span class="text-danger">*</span></label>
                            <input type="text" name="message" class="form-control form-control-sm border-secondary-subtle" required placeholder="ระบุรายละเอียดคำชี้แจง หรือประเด็นที่ต้องการสื่อสารกับเจ้าหน้าที่บริหารงานวิจัย...">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <div class="flex-grow-1">
                                <label class="form-label small fw-semibold">ไฟล์แนบ (ถ้ามี)</label>
                                <input type="file" name="attachment" class="form-control form-control-sm border-secondary-subtle" accept=".pdf,.doc,.docx,.jpg,.png">
                            </div>
                            <button type="submit" class="btn btn-sm btn-warning align-self-end text-nowrap px-3">
                                <i class="bi bi-send me-1"></i> ส่งข้อความ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

@if($isAdmin)
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.reviewer-tomselect').forEach(function (el) {
                new TomSelect(el, {
                    valueField: 'value',
                    labelField: 'text',
                    searchField: ['full_name', 'department', 'text'],
                    plugins: ['remove_button'],
                    maxOptions: 30,
                    load: function (query, callback) {
                        if (!query || query.length < 2) return callback();
                        const url = "{{ route('backend.project_review.search_reviewers') }}?q=" + encodeURIComponent(query);
                        fetch(url, { headers: { 'Accept': 'application/json' } })
                            .then(response => response.json())
                            .then(json => callback(json.results || []))
                            .catch(() => callback());
                    },
                    render: {
                        option: function (item, escape) {
                            return `<div class="py-1 px-2">
                                <div class="fw-semibold text-body">${escape(item.full_name)}</div>
                                <div class="small text-body-secondary d-flex justify-content-between">
                                    <span>${escape(item.department)}</span>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle" style="font-variant-numeric: tabular-nums;">กำลังประเมิน ${escape(item.active_reviews_count)} โครงการ</span>
                                </div>
                            </div>`;
                        },
                        item: function (item, escape) {
                            return `<div>${escape(item.full_name || item.text)}</div>`;
                        }
                    }
                });
            });
        });
    </script>
@endif
@endsection
