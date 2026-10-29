@extends('layouts.app')

@section('content')
<x-page-header
    title="งานประเมินเล่มรายงานวิจัยที่ได้รับมอบหมาย"
    icon="bi-person-check"
    :backRoute="route('backend.project_review.index')"
    :showPrint="false"
    :breadcrumbs="[
        'แดชบอร์ด' => route('backend.site.index'),
        'ระบบประเมินเล่มรายงานวิจัย' => route('backend.project_review.index'),
        'งานประเมินที่ได้รับมอบหมาย' => ''
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

{{-- 4 Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm border border-secondary-subtle bg-body">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-body-secondary small fw-semibold mb-1">รอตอบรับการประเมิน</div>
                        <h3 class="fw-bold text-warning-emphasis mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['pending'] ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle p-2 rounded-3">
                        <i class="bi bi-envelope-exclamation fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm border border-secondary-subtle bg-body">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-body-secondary small fw-semibold mb-1">กำลังดำเนินการประเมิน</div>
                        <h3 class="fw-bold text-primary-emphasis mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['accepted'] ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle p-2 rounded-3">
                        <i class="bi bi-journal-code fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm border border-secondary-subtle bg-body">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-body-secondary small fw-semibold mb-1">ส่งผลประเมินเสร็จสิ้น</div>
                        <h3 class="fw-bold text-success-emphasis mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['completed'] ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle p-2 rounded-3">
                        <i class="bi bi-check2-circle fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm border border-secondary-subtle bg-body">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-body-secondary small fw-semibold mb-1">ปฏิเสธรับงานประเมิน</div>
                        <h3 class="fw-bold text-danger-emphasis mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['declined'] ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle p-2 rounded-3">
                        <i class="bi bi-x-circle fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Pills --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('backend.project_review.my_reviews') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
        ทั้งหมด
    </a>
    <a href="{{ route('backend.project_review.my_reviews', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-secondary' }}" style="font-variant-numeric: tabular-nums;">
        รอตอบรับ ({{ $stats['pending'] ?? 0 }})
    </a>
    <a href="{{ route('backend.project_review.my_reviews', ['status' => 'accepted']) }}" class="btn btn-sm {{ request('status') === 'accepted' ? 'btn-primary' : 'btn-outline-secondary' }}" style="font-variant-numeric: tabular-nums;">
        กำลังประเมิน ({{ $stats['accepted'] ?? 0 }})
    </a>
    <a href="{{ route('backend.project_review.my_reviews', ['status' => 'completed']) }}" class="btn btn-sm {{ request('status') === 'completed' ? 'btn-success' : 'btn-outline-secondary' }}" style="font-variant-numeric: tabular-nums;">
        ประเมินเสร็จสิ้น ({{ $stats['completed'] ?? 0 }})
    </a>
    <a href="{{ route('backend.project_review.my_reviews', ['status' => 'declined']) }}" class="btn btn-sm {{ request('status') === 'declined' ? 'btn-danger' : 'btn-outline-secondary' }}" style="font-variant-numeric: tabular-nums;">
        ปฏิเสธรับงาน ({{ $stats['declined'] ?? 0 }})
    </a>
</div>

<x-card title="รายการเล่มรายงานวิจัยที่ได้รับมอบหมายให้ประเมิน" icon="bi bi-person-workspace" color="primary">
    @if($assignments->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-journal-check fs-1 text-body-secondary opacity-50 d-block mb-3"></i>
            <h6 class="fw-semibold text-body">ยังไม่มีงานประเมินเล่มรายงานวิจัยที่ได้รับมอบหมาย</h6>
            <p class="text-body-secondary small mb-0">เมื่อเจ้าหน้าที่บริหารงานวิจัยมอบหมายเล่มรายงานให้ท่านประเมิน รายการจะแสดงที่นี่พร้อมการแจ้งเตือน</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-body-secondary small text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 90px;">รอบที่</th>
                        <th>โครงการวิจัย & เอกสารที่ส่งประเมิน</th>
                        <th>แบบฟอร์มเกณฑ์ที่ใช้</th>
                        <th class="text-center">กำหนดส่งผล</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">ผลการประเมินของท่าน</th>
                        <th class="text-end" style="width: 190px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                        @php
                            $sub = $assignment->submission;
                            $project = $sub?->project;
                            $stBadge = match($assignment->status) {
                                'completed' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                'accepted' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                'pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                'declined' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                default => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                            };
                            $decisionBadge = match($assignment->result_decision) {
                                'pass' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                'minor_revision' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                'major_revision' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                'reject' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                default => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                            };
                        @endphp
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle" style="font-variant-numeric: tabular-nums;">
                                    รอบที่ {{ $sub->round_number ?? 1 }}
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
                                    </div>
                                    <div class="fw-semibold text-body mb-1">
                                        {{ strip_tags(html_entity_decode($project->pro_nameTH, ENT_QUOTES, 'UTF-8')) }}
                                    </div>
                                @endif
                                @if($sub)
                                    <div class="small text-body-secondary d-flex align-items-center gap-2 flex-wrap">
                                        <span><i class="bi bi-file-earmark-text me-1"></i>{{ $sub->submission_title }}</span>
                                        @if($sub->page_count)
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-variant-numeric: tabular-nums;">
                                                {{ number_format($sub->page_count) }} หน้า
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="fw-medium text-body small">{{ $assignment->template->title ?? 'แบบประเมินมาตรฐาน' }}</span>
                            </td>
                            <td class="text-center small" style="font-variant-numeric: tabular-nums;">
                                @if($assignment->due_date)
                                    <span class="text-body fw-medium">{{ \App\Helpers\ThaiDateHelper::format($assignment->due_date, false, true) }}</span>
                                @else
                                    <span class="text-body-secondary">ไม่ระบุ</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $stBadge }} border px-2.5 py-1.5">
                                    {{ $assignment->status_label }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($assignment->status === 'completed')
                                    <div class="mb-1">
                                        <span class="badge {{ $decisionBadge }} border">{{ $assignment->result_decision_label }}</span>
                                    </div>
                                    @if($assignment->score_percentage !== null)
                                        <span class="small fw-bold text-body" style="font-variant-numeric: tabular-nums;">
                                            {{ number_format($assignment->score_percentage, 2) }}%
                                        </span>
                                    @endif
                                @elseif($assignment->status === 'declined')
                                    <span class="small text-danger-emphasis">{{ Str::limit($assignment->decline_reason, 45) }}</span>
                                @else
                                    <span class="text-body-secondary small">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('backend.project_review.evaluate', $assignment->id) }}" class="btn btn-sm {{ $assignment->status === 'pending' ? 'btn-warning' : 'btn-primary' }} d-inline-flex align-items-center">
                                    <i class="bi bi-journal-check me-1.5"></i>
                                    {{ $assignment->status === 'pending' ? 'ตอบรับ / ประเมินผล' : ($assignment->status === 'completed' ? 'ดู/แก้ไขผลประเมิน' : 'เข้าสู่หน้าประเมิน') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($assignments->hasPages())
            <div class="mt-3 d-flex justify-content-end">
                {{ $assignments->links() }}
            </div>
        @endif
    @endif
</x-card>
@endsection
