@extends('layouts.app')

@section('content')
@php
    $submission = $assignment->submission;
    $project = $submission?->project;
    $template = $assignment->template;
    $criteriaGrouped = $template ? $template->criteria->groupBy(fn($item) => $item->category ?: 'หัวข้อการประเมินทั่วไป') : collect();
    $scaleMax = max(1, (int) ($template->scale_max ?? 5));
@endphp

<x-page-header
    title="แบบประเมินเล่ม/ร่างรายงานวิจัยฉบับสมบูรณ์"
    icon="bi-journal-check"
    :backRoute="route('backend.project_review.my_reviews')"
    :showPrint="true"
    :breadcrumbs="[
        'แดชบอร์ด' => route('backend.site.index'),
        'ระบบประเมินเล่มรายงานวิจัย' => route('backend.project_review.index'),
        'งานประเมินที่ได้รับมอบหมาย' => route('backend.project_review.my_reviews'),
        'แบบประเมินเล่มรายงานวิจัย' => ''
    ]"
/>

@if(session('success'))
    <div class="alert alert-success border border-success-subtle shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-check-circle-fill fs-5 me-2 text-success-emphasis"></i>
        <div>{{ session('success') }}</div>
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

{{-- Project & Submission Info Card + PDF Preview --}}
<div class="card shadow-sm border border-secondary-subtle bg-body mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle px-2.5 py-1.5" style="font-variant-numeric: tabular-nums;">
                        รอบการประเมินที่ {{ $submission->round_number ?? 1 }}
                    </span>
                    @if($project)
                        <span class="badge bg-body-secondary text-body border border-secondary-subtle font-monospace" style="font-variant-numeric: tabular-nums;">
                            {{ $project->pro_code ?: 'ID:' . $project->pro_id }}
                        </span>
                        @if($project->year)
                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle" style="font-variant-numeric: tabular-nums;">
                                ปีงบประมาณ {{ $project->year->year_name }}
                            </span>
                        @endif
                    @endif
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                        <i class="bi bi-shield-check me-1"></i>ปกปิดรายชื่อผู้ทรงคุณวุฒิ (Single-Blind Peer Review)
                    </span>
                </div>
                <h5 class="fw-bold text-body-emphasis mb-1">
                    {{ $project ? strip_tags(html_entity_decode($project->pro_nameTH, ENT_QUOTES, 'UTF-8')) : ($submission->submission_title ?? 'โครงการวิจัย') }}
                </h5>
                @if($project && $project->pro_nameEN)
                    <div class="text-body-secondary small mb-2">
                        {{ strip_tags(html_entity_decode($project->pro_nameEN, ENT_QUOTES, 'UTF-8')) }}
                    </div>
                @endif
                <div class="small text-body-secondary d-flex flex-wrap gap-3">
                    <span><strong>เอกสารที่ส่งประเมิน:</strong> {{ $submission->submission_title ?? '-' }} ({{ $submission->submission_type_label ?? '-' }})</span>
                    @if($submission && $submission->page_count)
                        <span style="font-variant-numeric: tabular-nums;"><strong>จำนวนหน้า:</strong> {{ number_format($submission->page_count) }} หน้า</span>
                    @endif
                    @if($assignment->due_date)
                        <span style="font-variant-numeric: tabular-nums;"><strong>กำหนดส่งผลประเมิน:</strong> {{ \App\Helpers\ThaiDateHelper::format($assignment->due_date, false, true) }}</span>
                    @endif
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($submission && $submission->file_path)
                    <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#pdfPreviewCollapse">
                        <i class="bi bi-eye me-1.5"></i> เปิด/ซ่อนตัวอย่างไฟล์ PDF ในหน้านี้
                    </button>
                    <a href="{{ route('backend.project_review.submission.file', $submission->id) }}" target="_blank" class="btn btn-primary d-inline-flex align-items-center">
                        <i class="bi bi-file-earmark-pdf me-1.5"></i> เปิดอ่านเล่มรายงานวิจัยฉบับเต็ม
                    </a>
                @endif
            </div>
        </div>

        @if($submission && $submission->file_path)
            <div class="collapse mt-3" id="pdfPreviewCollapse">
                <div class="ratio ratio-16x9 border border-secondary-subtle rounded overflow-hidden bg-body-tertiary">
                    <iframe src="{{ route('backend.project_review.submission.file', $submission->id) }}" title="ตัวอย่างเล่มรายงานวิจัย"></iframe>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- STEP 1: Accept / Decline Assignment Banner (When status === 'pending') --}}
@if($assignment->status === 'pending')
    <div class="card shadow-sm border border-warning-subtle bg-warning-subtle mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold text-warning-emphasis mb-1">
                        <i class="bi bi-envelope-paper-fill me-1.5"></i> ขอเรียนเชิญเป็นผู้ทรงคุณวุฒิประเมินเล่มรายงานวิจัยฉบับสมบูรณ์
                    </h5>
                    <p class="text-body mb-0 small">
                        กรุณากดยืนยัน <strong>"ตอบรับเป็นผู้ทรงคุณวุฒิ"</strong> เพื่อดำเนินการประเมินผล หรือหากท่านไม่สะดวกสามารถกดปฏิเสธพร้อมระบุเหตุผลเพื่อให้เจ้าหน้าที่มอบหมายผู้ทรงคุณวุฒิท่านอื่นแทนได้ทันที
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form action="{{ route('backend.project_review.respond', $assignment->id) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="decision" value="accepted">
                        <button type="submit" class="btn btn-success fw-semibold px-4">
                            <i class="bi bi-check2-circle me-1.5"></i> ตอบรับเป็นผู้ทรงคุณวุฒิประเมินเล่ม
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#declineReasonCollapse">
                        <i class="bi bi-x-circle me-1"></i> ไม่สะดวกรับประเมิน (ปฏิเสธ)
                    </button>
                </div>
            </div>

            <div class="collapse mt-3 pt-3 border-top border-warning-subtle" id="declineReasonCollapse">
                <form action="{{ route('backend.project_review.respond', $assignment->id) }}" method="POST" class="bg-body p-3 rounded border border-danger-subtle">
                    @csrf
                    <input type="hidden" name="decision" value="declined">
                    <label class="form-label fw-semibold text-danger-emphasis small">
                        โปรดระบุเหตุผลที่ไม่สะดวกรับประเมิน (เช่น ติดภาระงานเร่งด่วน, มีผลประโยชน์ทับซ้อน, ไม่ตรงสาขาความเชี่ยวชาญ) <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex gap-2">
                        <input type="text" name="decline_reason" class="form-control form-control-sm border-secondary-subtle" required placeholder="ระบุเหตุผลในการปฏิเสธรับงานประเมิน...">
                        <button type="submit" class="btn btn-sm btn-danger text-nowrap px-3">ยืนยันการปฏิเสธ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@elseif($assignment->status === 'declined')
    <div class="alert alert-danger border border-danger-subtle shadow-sm mb-4">
        <h6 class="fw-bold mb-1"><i class="bi bi-x-octagon-fill me-1.5"></i> ท่านได้ปฏิเสธการประเมินรายการนี้แล้ว</h6>
        <div class="small">เหตุผลที่ระบุ: {{ $assignment->decline_reason }}</div>
    </div>
@endif

{{-- STEP 2: Evaluation Form --}}
@if(in_array($assignment->status, ['pending', 'accepted', 'completed'], true))
    <form action="{{ route('backend.project_review.submit_evaluation', $assignment->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <x-card title="{{ $template->title ?? 'แบบประเมินเล่มรายงานวิจัยฉบับสมบูรณ์' }}" icon="bi bi-ui-checks" color="primary">
            @if($template && $template->description)
                <div class="alert alert-info border border-info-subtle small mb-4">
                    <i class="bi bi-info-circle-fill me-1.5"></i> {{ $template->description }}
                </div>
            @endif

            @foreach($criteriaGrouped as $categoryName => $criteriaItems)
                <div class="mb-4">
                    <div class="bg-body-secondary px-3 py-2 rounded-top border border-secondary-subtle fw-bold text-body-emphasis">
                        <i class="bi bi-bookmark-check text-primary me-1.5"></i> {{ $categoryName }}
                    </div>
                    <div class="border border-top-0 border-secondary-subtle rounded-bottom p-3 bg-body">
                        @foreach($criteriaItems as $criterion)
                            @php
                                $saved = $existingScores[$criterion->id] ?? null;
                                $savedScore = old("criteria.{$criterion->id}.score", $saved?->score);
                                $savedPass = old("criteria.{$criterion->id}.pass_status", $saved?->pass_status);
                                $savedComment = old("criteria.{$criterion->id}.comment", $saved?->comment);
                            @endphp
                            <div class="{{ !$loop->last ? 'pb-3 mb-3 border-bottom border-secondary-subtle' : '' }}">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                    <div>
                                        <span class="fw-semibold text-body">{{ $criterion->name }}</span>
                                        @if($criterion->is_required)
                                            <span class="text-danger">*</span>
                                        @endif
                                        @if($criterion->description)
                                            <div class="text-body-secondary small">{{ $criterion->description }}</div>
                                        @endif
                                    </div>
                                    @if($criterion->weight > 0)
                                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle" style="font-variant-numeric: tabular-nums;">
                                            น้ำหนัก {{ number_format($criterion->weight, 0) }}%
                                        </span>
                                    @endif
                                </div>

                                {{-- Scale Radio Options --}}
                                @if(in_array($criterion->item_type, ['scale', 'hybrid'], true))
                                    @php
                                        $cMax = $criterion->scale_max ?: $scaleMax;
                                        $cStep = $criterion->scale_step ?: 1;
                                        $scaleOpts = $criterion->scale_options;
                                    @endphp
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2 p-2 rounded bg-body-tertiary border border-secondary-subtle">
                                        <span class="small fw-semibold text-body-secondary me-1">ระดับคะแนน (เต็ม {{ $cMax }} คะแนน):</span>
                                        @foreach($scaleOpts as $s)
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input" type="radio"
                                                    name="criteria[{{ $criterion->id }}][score]"
                                                    id="crit_{{ $criterion->id }}_score_{{ $s }}"
                                                    value="{{ $s }}"
                                                    @checked($savedScore !== null && (float) $savedScore === (float) $s)
                                                    @required($criterion->is_required && $criterion->item_type === 'scale')>
                                                <label class="form-check-label fw-semibold small px-1" for="crit_{{ $criterion->id }}_score_{{ $s }}" style="font-variant-numeric: tabular-nums;">
                                                    {{ $s }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Pass / Revise / Fail Radio Options --}}
                                @if($criterion->item_type === 'pass_fail')
                                    <div class="d-flex align-items-center flex-wrap gap-3 mb-2 p-2 rounded bg-body-tertiary border border-secondary-subtle">
                                        <span class="small fw-semibold text-body-secondary">ผลการพิจารณา:</span>
                                        <div class="form-check form-check-inline mb-0">
                                            <input class="form-check-input" type="radio" name="criteria[{{ $criterion->id }}][pass_status]" id="crit_{{ $criterion->id }}_pass" value="pass" @checked($savedPass === 'pass')>
                                            <label class="form-check-label small fw-semibold text-success-emphasis" for="crit_{{ $criterion->id }}_pass">ผ่าน (Pass)</label>
                                        </div>
                                        <div class="form-check form-check-inline mb-0">
                                            <input class="form-check-input" type="radio" name="criteria[{{ $criterion->id }}][pass_status]" id="crit_{{ $criterion->id }}_revise" value="revise" @checked($savedPass === 'revise')>
                                            <label class="form-check-label small fw-semibold text-warning-emphasis" for="crit_{{ $criterion->id }}_revise">ควรปรับปรุงแก้ไข (Revise)</label>
                                        </div>
                                        <div class="form-check form-check-inline mb-0">
                                            <input class="form-check-input" type="radio" name="criteria[{{ $criterion->id }}][pass_status]" id="crit_{{ $criterion->id }}_fail" value="fail" @checked($savedPass === 'fail')>
                                            <label class="form-check-label small fw-semibold text-danger-emphasis" for="crit_{{ $criterion->id }}_fail">ไม่ผ่าน (Fail)</label>
                                        </div>
                                    </div>
                                @endif

                                {{-- Qualitative Comment per Criterion --}}
                                @if(in_array($criterion->item_type, ['open_ended', 'hybrid', 'pass_fail'], true))
                                    <div>
                                        <textarea name="criteria[{{ $criterion->id }}][comment]" rows="2" class="form-control form-control-sm border-secondary-subtle" placeholder="ข้อคิดเห็น / ข้อเสนอแนะเพื่อการพัฒนาปรับปรุงในประเด็นนี้...">{{ $savedComment }}</textarea>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Overall Decision & Comprehensive Feedback --}}
            <div class="p-4 rounded-3 bg-body-secondary border border-secondary-subtle mt-4">
                <h6 class="fw-bold text-body-emphasis mb-3">
                    <i class="bi bi-award-fill text-primary me-1.5"></i> สรุปผลการประเมินเล่มรายงานวิจัยในภาพรวม <span class="text-danger">*</span>
                </h6>

                @php
                    $selectedDecision = old('result_decision', $assignment->result_decision);
                @endphp
                <div class="row g-3 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <label class="card h-100 border border-success-subtle bg-body p-3 cursor-pointer">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="result_decision" value="pass" @checked($selectedDecision === 'pass') required>
                                <span class="form-check-label fw-bold text-success-emphasis d-block">
                                    1. เห็นชอบให้ผ่าน (Pass)
                                </span>
                                <small class="text-body-secondary d-block mt-1">ผ่านการประเมินโดยไม่ต้องแก้ไข</small>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label class="card h-100 border border-info-subtle bg-body p-3 cursor-pointer">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="result_decision" value="minor_revision" @checked($selectedDecision === 'minor_revision') required>
                                <span class="form-check-label fw-bold text-info-emphasis d-block">
                                    2. ผ่านแบบมีเงื่อนไขแก้ไขเล็กน้อย
                                </span>
                                <small class="text-body-secondary d-block mt-1">ปรับปรุงแก้ไขตามข้อเสนอแนะ (Minor Revision)</small>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label class="card h-100 border border-warning-subtle bg-body p-3 cursor-pointer">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="result_decision" value="major_revision" @checked($selectedDecision === 'major_revision') required>
                                <span class="form-check-label fw-bold text-warning-emphasis d-block">
                                    3. ให้ปรับปรุงแก้ไขและส่งประเมินซ้ำ
                                </span>
                                <small class="text-body-secondary d-block mt-1">แก้ไขในสาระสำคัญ (Major Revision)</small>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label class="card h-100 border border-danger-subtle bg-body p-3 cursor-pointer">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="result_decision" value="reject" @checked($selectedDecision === 'reject') required>
                                <span class="form-check-label fw-bold text-danger-emphasis d-block">
                                    4. ไม่เห็นชอบให้ผ่าน (Reject)
                                </span>
                                <small class="text-body-secondary d-block mt-1">ไม่ผ่านเกณฑ์มาตรฐานงานวิจัย</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">ข้อคิดเห็นและข้อเสนอแนะในภาพรวม (General Comments & Recommendations)</label>
                    <textarea name="general_comment" rows="4" class="form-control border-secondary-subtle bg-body" placeholder="ระบุข้อคิดเห็นในภาพรวม จุดเด่นของงานวิจัย และประเด็นสำคัญที่ผู้วิจัยควรปรับปรุงแก้ไข...">{{ old('general_comment', $assignment->general_comment) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        แนบไฟล์เล่มรายงานที่มีการเขียนคอมเมนต์ / ข้อเสนอแนะเพิ่มเติม (PDF หรือ Word, ถ้ามี)
                    </label>
                    <input type="file" name="annotated_file" class="form-control border-secondary-subtle bg-body" accept=".pdf,.doc,.docx">
                    <div class="form-text small text-body-secondary">
                        <i class="bi bi-shield-lock me-1"></i>ระบบจะเปลี่ยนชื่อไฟล์เป็นนามแฝงอัตโนมัติ (<code>reviewer_feedback_{{ $assignment->id }}.pdf</code>) เมื่อส่งให้นักวิจัยดาวน์โหลด เพื่อคุ้มครองความเป็นส่วนตัวของผู้ทรงคุณวุฒิ
                    </div>
                    @if($assignment->annotated_file_path)
                        <div class="mt-2">
                            <a href="{{ route('backend.project_review.assignment.annotated_file', $assignment->id) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-paperclip me-1"></i> เปิดดูไฟล์แนบเดิมที่อัปโหลดไว้
                            </a>
                        </div>
                    @endif
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('backend.project_review.my_reviews') }}" class="btn btn-secondary px-4">ย้อนกลับ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-send-check me-1.5"></i> บันทึกและส่งผลการประเมินเล่มรายงานวิจัย
                    </button>
                </div>
            </div>
        </x-card>
    </form>
@endif
@endsection
