@extends('layouts.app')

@section('content')
<x-page-header
    title="จัดการแบบฟอร์มเกณฑ์การประเมิน (Evaluation Form Designer)"
    icon="bi-ui-checks-grid"
    :backRoute="route('backend.project_review.index')"
    :showPrint="false"
    :breadcrumbs="[
        'แดชบอร์ด' => route('backend.site.index'),
        'ระบบประเมินเล่มรายงานวิจัย' => route('backend.project_review.index'),
        'จัดการแบบฟอร์มเกณฑ์ประเมิน' => ''
    ]"
>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary d-inline-flex justify-content-center align-items-center" style="height: 38px; font-size: 0.85rem;" onclick="toggleAllAccordions()">
            <i class="bi bi-arrows-expand me-1.5" id="toggleAllIcon"></i> <span id="toggleAllText">ขยายทั้งหมด</span>
        </button>
        <a href="{{ route('backend.project_review.templates.create') }}" target="_blank" class="btn btn-success d-inline-flex justify-content-center align-items-center shadow-sm" style="height: 38px; font-size: 0.85rem;">
            <i class="bi bi-plus-circle me-1.5"></i> สร้างแบบฟอร์มเกณฑ์ใหม่ (เปิดแท็บใหม่)
        </a>
    </div>
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

{{-- Summary Stats Bar --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border border-subtle shadow-sm bg-body">
            <div class="card-body py-2.5 px-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-body-secondary small d-block">แบบฟอร์มทั้งหมดในระบบ</span>
                    <span class="fs-5 fw-bold text-primary font-monospace" style="font-variant-numeric: tabular-nums;">
                        {{ $templates->count() }} <span class="fs-6 fw-normal text-body-secondary">แบบฟอร์ม</span>
                    </span>
                </div>
                <div class="rounded-circle p-2 bg-primary-subtle text-primary">
                    <i class="bi bi-collection fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border border-subtle shadow-sm bg-body">
            <div class="card-body py-2.5 px-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-body-secondary small d-block">สถานะเปิดใช้งาน</span>
                    <span class="fs-5 fw-bold text-success font-monospace" style="font-variant-numeric: tabular-nums;">
                        {{ $templates->where('is_active', true)->count() }} <span class="fs-6 fw-normal text-body-secondary">พร้อมใช้งาน</span>
                    </span>
                </div>
                <div class="rounded-circle p-2 bg-success-subtle text-success">
                    <i class="bi bi-check2-circle fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border border-subtle shadow-sm bg-body">
            <div class="card-body py-2.5 px-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-body-secondary small d-block">แบบฟอร์มเริ่มต้น (Default)</span>
                    <span class="fs-6 fw-bold text-warning-emphasis text-truncate d-inline-block" style="max-width: 220px;">
                        {{ $templates->firstWhere('is_default', true)?->title ?? 'ยังไม่ได้กำหนด' }}
                    </span>
                </div>
                <div class="rounded-circle p-2 bg-warning-subtle text-warning-emphasis">
                    <i class="bi bi-star-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Collapsible Templates Accordion List --}}
<div class="accordion mb-4" id="templatesAccordion">
    @forelse($templates as $tpl)
        <div class="accordion-item border border-subtle rounded-3 shadow-sm mb-3 overflow-hidden bg-body">
            <div class="accordion-header p-0" id="heading_{{ $tpl->id }}">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 bg-body-tertiary border-bottom border-subtle">
                    {{-- Left Title & Badges --}}
                    <div class="d-flex align-items-center gap-2.5 flex-grow-1 flex-wrap cursor-pointer" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $tpl->id }}" aria-expanded="false" aria-controls="collapse_{{ $tpl->id }}">
                        <span class="badge rounded-pill bg-body border text-body-secondary px-2.5 py-1 font-monospace" style="font-variant-numeric: tabular-nums;">
                            #{{ $tpl->id }}
                        </span>
                        <div class="d-inline-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold text-body-emphasis fs-6">{{ $tpl->title }}</span>
                            @if($tpl->is_default)
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                    <i class="bi bi-star-fill me-1"></i> ค่าเริ่มต้น (Default)
                                </span>
                            @endif
                            @if($tpl->is_active)
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i> เปิดใช้งาน
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                    <i class="bi bi-slash-circle me-1"></i> ปิดใช้งาน
                                </span>
                            @endif
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                {{ match($tpl->evaluation_mode) {
                                    'hybrid' => 'แบบผสมผสาน (คะแนน + ความเห็น)',
                                    'scoring_scale' => 'ระดับคะแนน (Scoring Scale)',
                                    'pass_fail' => 'ผ่าน / ปรับปรุง / ไม่ผ่าน',
                                    'qualitative_only' => 'ความเห็นเชิงคุณภาพ (ปลายเปิด)',
                                    default => $tpl->evaluation_mode
                                } }}
                            </span>
                        </div>
                    </div>

                    {{-- Right Action Buttons & Quick Stats --}}
                    <div class="d-flex align-items-center gap-2">
                        <div class="text-body-secondary small me-2 d-none d-lg-block" style="font-variant-numeric: tabular-nums;">
                            <span class="badge bg-body border text-body-secondary me-1">
                                <i class="bi bi-list-ol me-1 text-primary"></i> {{ $tpl->criteria->count() }} หัวข้อ
                            </span>
                            @if($tpl->assignments_count > 0)
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                    <i class="bi bi-clipboard2-check me-1"></i> ใช้ประเมิน {{ $tpl->assignments_count }} ครั้ง
                                </span>
                            @else
                                <span class="badge bg-body border text-body-secondary">
                                    ยังไม่ถูกนำไปใช้
                                </span>
                            @endif
                        </div>

                        {{-- Open in new tab button --}}
                        <a href="{{ route('backend.project_review.templates.edit', $tpl->id) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center" title="แก้ไขแบบฟอร์มนี้ (เปิดในแท็บใหม่)">
                            <i class="bi bi-pencil-square me-1"></i> แก้ไข <span class="d-none d-sm-inline ms-0.5">(แท็บใหม่)</span>
                        </a>

                        @if($tpl->assignments_count === 0)
                            <form action="{{ route('backend.project_review.templates.destroy', $tpl->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันลบแบบฟอร์มเกณฑ์การประเมินนี้หรือไม่?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="ลบแบบฟอร์ม">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        @endif

                        {{-- Toggle Button --}}
                        <button class="btn btn-sm btn-light border py-1 px-2.5 accordion-toggle-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $tpl->id }}" aria-expanded="false" aria-controls="collapse_{{ $tpl->id }}" title="คลิกเพื่อย่อ/ขยายข้อเกณฑ์">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Accordion Body (Collapsed by Default) --}}
            <div id="collapse_{{ $tpl->id }}" class="accordion-collapse collapse" aria-labelledby="heading_{{ $tpl->id }}" data-bs-parent="#templatesAccordion">
                <div class="accordion-body p-3 bg-body">
                    @if($tpl->description)
                        <div class="p-2.5 mb-3 bg-body-tertiary rounded border border-subtle text-body-secondary small">
                            <strong><i class="bi bi-info-circle me-1 text-primary"></i> คำชี้แจงสำหรับผู้ทรงคุณวุฒิ:</strong> {{ $tpl->description }}
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-body-emphasis small mb-0">
                            <i class="bi bi-list-task me-1 text-success"></i> รายการหัวข้อเกณฑ์การประเมิน ({{ $tpl->criteria->count() }} ข้อ)
                        </h6>
                        <span class="text-body-secondary small" style="font-variant-numeric: tabular-nums;">
                            เกณฑ์ผ่านขั้นต่ำ: <strong class="text-success">{{ number_format($tpl->pass_threshold, 2) }}%</strong>
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0 border-subtle">
                            <thead class="bg-body-secondary text-body-secondary small">
                                <tr>
                                    <th style="width: 5%;" class="text-center">ลำดับ</th>
                                    <th style="width: 22%;">หมวดหมู่</th>
                                    <th style="width: 33%;">หัวข้อเกณฑ์การประเมิน</th>
                                    <th style="width: 14%;">รูปแบบการตอบ</th>
                                    <th style="width: 18%;">ระดับคะแนน / สเกล</th>
                                    <th style="width: 8%;" class="text-end">น้ำหนัก (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tpl->criteria as $cIdx => $crit)
                                    <tr>
                                        <td class="text-center font-monospace text-body-secondary" style="font-variant-numeric: tabular-nums;">
                                            {{ $cIdx + 1 }}
                                        </td>
                                        <td class="small fw-semibold text-body-secondary">
                                            {{ $crit->category ?: '-' }}
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-body-emphasis small">{{ $crit->name }}</div>
                                            @if($crit->description)
                                                <div class="text-body-secondary small mt-0.5" style="font-size: 0.8rem;">{{ $crit->description }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($crit->item_type === 'hybrid')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">คะแนนสเกล + ความเห็น</span>
                                            @elseif($crit->item_type === 'scale')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">ระดับคะแนนอย่างเดียว</span>
                                            @elseif($crit->item_type === 'pass_fail')
                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">ผ่าน / ปรับปรุง / ไม่ผ่าน</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">ปลายเปิด (ความเห็น)</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(in_array($crit->item_type, ['scale', 'hybrid']))
                                                @php
                                                    $cMax = $crit->scale_max ?: $tpl->scale_max ?: 5;
                                                    $cStep = $crit->scale_step ?: 1;
                                                    $opts = $crit->scale_options;
                                                @endphp
                                                <div class="small fw-semibold text-primary" style="font-variant-numeric: tabular-nums;">
                                                    เต็ม {{ $cMax }} คะแนน <span class="text-body-secondary fw-normal">(ทีละ {{ $cStep }})</span>
                                                </div>
                                                <div class="small text-body-secondary font-monospace mt-0.5" style="font-size: 0.76rem; font-variant-numeric: tabular-nums;">
                                                    [ {{ implode(', ', $opts) }} ] <span class="text-body-tertiary">({{ count($opts) }} ระดับ)</span>
                                                </div>
                                            @elseif($crit->item_type === 'pass_fail')
                                                <div class="small text-body-secondary">
                                                    เลือก 1 ใน 3: ผ่าน / ต้องแก้ไข / ไม่ผ่าน
                                                </div>
                                            @else
                                                <div class="small text-body-secondary">
                                                    กล่องข้อความบรรยายความเห็น
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end font-monospace text-body-emphasis" style="font-variant-numeric: tabular-nums;">
                                            {{ number_format($crit->weight, 2) }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-body-secondary py-3 small">
                                            ยังไม่มีหัวข้อเกณฑ์ประเมินในแบบฟอร์มนี้
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-body-tertiary small fw-semibold">
                                <tr>
                                    <td colspan="5" class="text-end text-body-emphasis">รวมน้ำหนักคะแนนทั้งหมด:</td>
                                    <td class="text-end font-monospace text-success fw-bold" style="font-variant-numeric: tabular-nums;">
                                        {{ number_format($tpl->criteria->sum('weight'), 2) }}%
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card border border-subtle shadow-sm bg-body text-center py-5">
            <div class="card-body">
                <i class="bi bi-inbox fs-1 text-body-secondary d-block mb-3"></i>
                <h5 class="fw-bold text-body-emphasis">ยังไม่มีแบบฟอร์มเกณฑ์การประเมินในระบบ</h5>
                <p class="text-body-secondary small mb-3">ท่านสามารถคลิกสร้างแบบฟอร์มเกณฑ์การประเมินใหม่เพื่อเริ่มใช้งานได้ทันที</p>
                <a href="{{ route('backend.project_review.templates.create') }}" target="_blank" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i> สร้างแบบฟอร์มเกณฑ์ใหม่
                </a>
            </div>
        </div>
    @endforelse
</div>

<script>
    let isAllExpanded = false;

    function toggleAllAccordions() {
        const collapses = document.querySelectorAll('#templatesAccordion .accordion-collapse');
        const icon = document.getElementById('toggleAllIcon');
        const text = document.getElementById('toggleAllText');

        isAllExpanded = !isAllExpanded;

        collapses.forEach(el => {
            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
            if (isAllExpanded) {
                bsCollapse.show();
            } else {
                bsCollapse.hide();
            }
        });

        if (isAllExpanded) {
            icon.className = 'bi bi-arrows-collapse me-1.5';
            text.innerText = 'ย่อทั้งหมด';
        } else {
            icon.className = 'bi bi-arrows-expand me-1.5';
            text.innerText = 'ขยายทั้งหมด';
        }
    }
</script>
@endsection
