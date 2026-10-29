@extends('layouts.app')

@section('content')
<x-page-header
    title="ระบบประเมินเล่ม/ร่างรายงานวิจัยฉบับสมบูรณ์"
    icon="bi-clipboard2-check"
    :showPrint="false"
    :breadcrumbs="[
        'แดชบอร์ด' => route('backend.site.index'),
        'จัดการโครงการวิจัย' => route('backend.rdb_project.index'),
        'ระบบประเมินเล่มรายงานวิจัย' => ''
    ]"
>
    <a href="{{ route('backend.project_review.my_reviews') }}" class="btn btn-success d-inline-flex justify-content-center align-items-center shadow-sm" style="height: 38px; font-size: 0.85rem;">
        <i class="bi bi-person-check me-1.5"></i> งานประเมินของฉัน
        @if(($stats['my_pending_reviews'] ?? 0) > 0)
            <span class="badge rounded-pill bg-danger ms-2" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['my_pending_reviews']) }}</span>
        @endif
    </a>
    @if($isAdmin)
        <a href="{{ route('backend.project_review.templates') }}" class="btn btn-primary d-inline-flex justify-content-center align-items-center shadow-sm" style="height: 38px; font-size: 0.85rem;">
            <i class="bi bi-ui-checks-grid me-1.5"></i> ออกแบบฟอร์มเกณฑ์ประเมิน
        </a>
    @endif
</x-page-header>

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

{{-- 5 Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="card h-100 shadow-sm border border-secondary-subtle bg-body position-relative overflow-hidden">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-body-secondary small fw-semibold mb-1">รายการส่งประเมินทั้งหมด</div>
                        <h3 class="fw-bold text-body-emphasis mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['total'] ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle p-2 rounded-3">
                        <i class="bi bi-files fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card h-100 shadow-sm border border-secondary-subtle bg-body position-relative overflow-hidden">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-body-secondary small fw-semibold mb-1">รอมอบหมาย / รอตรวจ</div>
                        <h3 class="fw-bold text-info-emphasis mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['submitted'] ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle p-2 rounded-3">
                        <i class="bi bi-inbox fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card h-100 shadow-sm border border-secondary-subtle bg-body position-relative overflow-hidden">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-body-secondary small fw-semibold mb-1">กำลังดำเนินการประเมิน</div>
                        <h3 class="fw-bold text-primary-emphasis mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['under_review'] ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle p-2 rounded-3">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-xl">
        <div class="card h-100 shadow-sm border border-secondary-subtle bg-body position-relative overflow-hidden">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-body-secondary small fw-semibold mb-1">แจ้งผล / รอแก้ไข / ชี้แจง</div>
                        <h3 class="fw-bold text-warning-emphasis mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['revision_required'] ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle p-2 rounded-3">
                        <i class="bi bi-arrow-repeat fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl">
        <div class="card h-100 shadow-sm border border-secondary-subtle bg-body position-relative overflow-hidden">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-body-secondary small fw-semibold mb-1">อนุมัติฉบับสมบูรณ์แล้ว</div>
                        <h3 class="fw-bold text-success-emphasis mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['approved'] ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle p-2 rounded-3">
                        <i class="bi bi-patch-check-fill fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Search & Filter Card --}}
<x-card title="ค้นหาและกรองรายการส่งเล่มรายงานวิจัย" icon="bi bi-funnel" color="primary">
    <form method="GET" action="{{ route('backend.project_review.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="search" class="form-label small fw-semibold text-body-secondary">คำค้นหา (ชื่อโครงการ / รหัสโครงการ / หัวข้อที่ส่ง)</label>
            <div class="input-group">
                <span class="input-group-text bg-body-tertiary border-secondary-subtle"><i class="bi bi-search text-body-secondary"></i></span>
                <input type="text" name="search" id="search" class="form-control border-secondary-subtle" value="{{ request('search') }}" placeholder="ระบุชื่อโครงการวิจัย หรือรหัสโครงการ...">
            </div>
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label small fw-semibold text-body-secondary">สถานะการประเมิน</label>
            <select name="status" id="status" class="form-select border-secondary-subtle">
                <option value="">-- ทุกสถานะ --</option>
                <option value="submitted" @selected(request('status') === 'submitted')>ส่งเล่มประเมินแล้ว (รอมอบหมาย)</option>
                <option value="under_review" @selected(request('status') === 'under_review')>กำลังประเมิน</option>
                <option value="round_closed" @selected(request('status') === 'round_closed')>แจ้งผลการประเมินแล้ว</option>
                <option value="revision_required" @selected(request('status') === 'revision_required')>รอการแก้ไขปรับปรุง</option>
                <option value="disputed" @selected(request('status') === 'disputed')>ยื่นคำชี้แจง/โต้แย้ง</option>
                <option value="final_submitted" @selected(request('status') === 'final_submitted')>ส่งฉบับแก้ไขสมบูรณ์แล้ว</option>
                <option value="approved" @selected(request('status') === 'approved')>อนุมัติฉบับสมบูรณ์</option>
                <option value="rejected" @selected(request('status') === 'rejected')>ไม่ผ่านการประเมิน</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="year_id" class="form-label small fw-semibold text-body-secondary">ปีงบประมาณ</label>
            <select name="year_id" id="year_id" class="form-select border-secondary-subtle" style="font-variant-numeric: tabular-nums;">
                <option value="">-- ทุกปี --</option>
                @foreach($years as $y)
                    <option value="{{ $y->year_id }}" @selected((string) request('year_id') === (string) $y->year_id)>
                        {{ $y->year_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="department_id" class="form-label small fw-semibold text-body-secondary">หน่วยงาน / คณะ</label>
            <select name="department_id" id="department_id" class="form-select border-secondary-subtle">
                <option value="">-- ทุกหน่วยงาน --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->department_id }}" @selected((string) request('department_id') === (string) $dept->department_id)>
                        {{ $dept->department_nameTH }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('backend.project_review.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-counterclockwise me-1"></i> ล้างตัวกรอง
            </a>
            <button type="submit" class="btn btn-primary btn-sm px-4">
                <i class="bi bi-search me-1"></i> ค้นหา
            </button>
        </div>
    </form>
</x-card>

{{-- Submissions Table --}}
<x-card title="รายการส่งเล่ม/ร่างรายงานวิจัยฉบับสมบูรณ์" icon="bi bi-table" color="primary">
    @if($submissions->isEmpty())
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-clipboard2-x fs-1 text-body-secondary opacity-50"></i>
            </div>
            <h6 class="fw-semibold text-body">ยังไม่มีรายการส่งเล่มรายงานวิจัยเพื่อประเมิน</h6>
            <p class="text-body-secondary small mb-3">ท่านสามารถเข้าสู่หน้ารายละเอียดโครงการวิจัยเพื่ออัปโหลดร่างรายงานฉบับสมบูรณ์เข้าสู่ระบบประเมินได้ทันที</p>
            <a href="{{ route('backend.rdb_project.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-folder2-open me-1"></i> ไปที่รายการโครงการวิจัย
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-body-secondary small text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 85px;">รอบที่</th>
                        <th>โครงการวิจัย</th>
                        <th>หัวข้อที่ส่ง & ไฟล์รายงาน</th>
                        <th class="text-center">สถานะการประเมิน</th>
                        <th>ความคืบหน้าการประเมิน</th>
                        <th class="text-center">วันที่ส่ง</th>
                        <th class="text-end" style="width: 140px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $sub)
                        @php
                            $project = $sub->project;
                            $statusBadge = match($sub->status) {
                                'approved' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                'under_review' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                'submitted', 'final_submitted' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                'revision_required', 'round_closed' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                'disputed', 'rejected' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                default => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                            };
                            $statusIcon = match($sub->status) {
                                'approved' => 'bi-check-circle-fill',
                                'under_review' => 'bi-hourglass-split',
                                'submitted', 'final_submitted' => 'bi-send-check',
                                'revision_required', 'round_closed' => 'bi-exclamation-circle',
                                'disputed', 'rejected' => 'bi-x-octagon-fill',
                                default => 'bi-info-circle',
                            };
                            $totalAssign = $sub->assignments->count();
                            $completedAssign = $sub->assignments->where('status', 'completed')->count();
                            $declinedAssign = $sub->assignments->where('status', 'declined')->count();
                        @endphp
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle px-2.5 py-1.5" style="font-variant-numeric: tabular-nums;">
                                    รอบที่ {{ $sub->round_number }}
                                </span>
                            </td>
                            <td>
                                @if($project)
                                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                        <span class="badge bg-body-secondary text-body border border-secondary-subtle font-monospace" style="font-variant-numeric: tabular-nums;">
                                            {{ $project->pro_code ?: 'ID:' . $project->pro_id }}
                                        </span>
                                        @if($project->year)
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle" style="font-variant-numeric: tabular-nums;">
                                                ปี {{ $project->year->year_name }}
                                            </span>
                                        @endif
                                        @if($project->department)
                                            <span class="text-body-secondary small">
                                                <i class="bi bi-building me-1"></i>{{ $project->department->department_nameTH }}
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('backend.project_review.project', $project->pro_id) }}" class="fw-semibold text-body text-decoration-none d-block">
                                        {{ strip_tags(html_entity_decode($project->pro_nameTH, ENT_QUOTES, 'UTF-8')) }}
                                    </a>
                                @else
                                    <span class="text-body-secondary">ไม่พบข้อมูลโครงการ (#{{ $sub->pro_id }})</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-body mb-1">{{ $sub->submission_title }}</div>
                                <div class="d-flex align-items-center gap-2 flex-wrap small">
                                    <span class="badge bg-body-tertiary text-body-secondary border border-secondary-subtle">
                                        <i class="bi bi-tag me-1"></i>{{ $sub->submission_type_label }}
                                    </span>
                                    @if($sub->page_count)
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-variant-numeric: tabular-nums;">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>{{ number_format($sub->page_count) }} หน้า
                                        </span>
                                    @endif
                                    @if($sub->file_path)
                                        <a href="{{ route('backend.project_review.submission.file', $sub->id) }}" target="_blank" class="text-primary text-decoration-none">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>เปิดไฟล์
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $statusBadge }} border px-2.5 py-1.5">
                                    <i class="bi {{ $statusIcon }} me-1"></i>{{ $sub->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($isAdmin)
                                    {{-- Admin Only: Sees count and real reviewer names --}}
                                    <div class="small fw-semibold text-body mb-1" style="font-variant-numeric: tabular-nums;">
                                        ส่งผลแล้ว {{ $completedAssign }}/{{ $totalAssign }} ท่าน
                                        @if($declinedAssign > 0)
                                            <span class="text-danger ms-1">(ปฏิเสธ {{ $declinedAssign }})</span>
                                        @endif
                                    </div>
                                    @if($totalAssign > 0)
                                        <div class="d-flex flex-column gap-1">
                                            @foreach($sub->assignments as $asgn)
                                                @php
                                                    $revName = $asgn->researcher
                                                        ? (($asgn->researcher->prefix->prefix_nameTH ?? '') . $asgn->researcher->researcher_fname . ' ' . $asgn->researcher->researcher_lname)
                                                        : ($asgn->reviewer->name ?? $asgn->reviewer->username ?? 'ผู้ประเมิน #' . $asgn->id);
                                                    $asgnColor = match($asgn->status) {
                                                        'completed' => 'text-success-emphasis',
                                                        'accepted' => 'text-primary-emphasis',
                                                        'declined' => 'text-danger-emphasis',
                                                        default => 'text-body-secondary',
                                                    };
                                                @endphp
                                                <div class="small {{ $asgnColor }}">
                                                    <i class="bi bi-person-badge me-1"></i>{{ $revName }}
                                                    <span class="text-body-secondary">({{ $asgn->status_label }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-body-secondary small">ยังไม่ได้มอบหมายผู้ทรงคุณวุฒิ</span>
                                    @endif
                                @else
                                    {{-- CRITICAL SINGLE-BLIND: Non-Admin / Researcher NEVER sees reviewer names or IDs --}}
                                    @if($totalAssign > 0)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle" style="font-variant-numeric: tabular-nums;">
                                            <i class="bi bi-shield-lock me-1"></i>ประเมินเสร็จสิ้น {{ $completedAssign }}/{{ $totalAssign }} ท่าน (Single-Blind)
                                        </span>
                                    @else
                                        <span class="text-body-secondary small">รอดำเนินการมอบหมายผู้ประเมิน</span>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center small text-body-secondary" style="font-variant-numeric: tabular-nums;">
                                @if($sub->submitted_at)
                                    {{ \App\Helpers\ThaiDateHelper::format($sub->submitted_at, false, true) }}
                                    <div class="text-body-secondary" style="font-size: 0.75rem;">{{ $sub->submitted_at->format('H:i น.') }}</div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('backend.project_review.project', $sub->pro_id) }}" class="btn btn-sm btn-primary d-inline-flex align-items-center">
                                    <i class="bi bi-folder2-open me-1.5"></i> เปิดดู / จัดการ
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($submissions->hasPages())
            <div class="mt-3 d-flex justify-content-end">
                {{ $submissions->links() }}
            </div>
        @endif
    @endif
</x-card>
@endsection
