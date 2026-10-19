@extends('layouts.app')

@section('content')
<style>
    /* Print Styles */
    @media print {
        .d-print-none { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .bg-primary, .bg-success, .bg-info, .bg-warning, .bg-danger, .bg-secondary, .bg-dark {
            background-color: transparent !important;
            color: #000 !important;
            border: 1px solid #dee2e6;
        }
        .badge { border: 1px solid #000; color: #000 !important; }
    }
</style>

<div class="container-fluid py-4">
    <!-- Header & Action Buttons -->
    <x-page-header 
        title="รายละเอียดการใช้ประโยชน์ (Utilization Details)"
        icon="bi-rocket-takeoff"
        :backRoute="route('backend.rdbprojectutilize.index')"
        :editRoute="auth()->user()->can('update', $item) ? route('backend.rdbprojectutilize.edit', $item->utz_id) : null"
        :deleteRoute="auth()->user()->can('delete', $item) ? route('backend.rdbprojectutilize.destroy', $item->utz_id) : null"
        :canDelete="auth()->user()->can('delete', $item)"
        :showPrint="true"
        :breadcrumbs="[
            'แดชบอร์ด' => route('backend.site.index'),
            'จัดการการใช้ประโยชน์' => route('backend.rdbprojectutilize.index'),
            'รายละเอียดผลงาน' => ''
        ]"
    >
        @if($item->workflow_status === 'published' && auth()->user()->can('requestUnlock', $item))
            <button type="button" class="btn btn-warning d-inline-flex justify-content-center align-items-center" style="min-width: 105px; height: 38px; font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#requestUnlockModal" data-id="{{ $item->utz_id }}" data-type="utilize" data-title="การนำไปใช้ประโยชน์: {{ $item->utz_department_name }}">
                <i class="bi bi-unlock me-1.5"></i> ขอแก้ไข
            </button>
        @elseif($item->workflow_status === 'unlock_requested')
            @if(auth()->user()->can('approveUnlock', $item))
                <form action="{{ route('backend.workflow.approve_unlock', ['type' => 'utilize', 'id' => $item->utz_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันอนุมัติให้แก้ไข และย้ายผลงานนี้ไปยังฉบับร่างหรือไม่?');">
                    @csrf
                    <button type="submit" class="btn btn-success d-inline-flex justify-content-center align-items-center" style="height: 38px; font-size: 0.85rem;">
                        <i class="bi bi-unlock me-1.5"></i> อนุมัติย้ายไปฉบับร่าง
                    </button>
                </form>
            @else
                <span class="btn btn-warning disabled d-inline-flex justify-content-center align-items-center" style="height: 38px; font-size: 0.85rem;">
                    <i class="bi bi-hourglass-split me-1.5"></i> รอ Admin อนุมัติการขอแก้ไข
                </span>
            @endif
        @elseif(in_array($item->workflow_status, ['draft', 'revision_required']))
            <form action="{{ route('backend.workflow.submit', ['type' => 'utilize', 'id' => $item->utz_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันส่งผลงานนี้เข้าสู่กระบวนการตรวจรับ?');">
                @csrf
                <button type="submit" class="btn btn-primary d-inline-flex justify-content-center align-items-center" style="height: 38px; font-size: 0.85rem;">
                    <i class="bi bi-send me-1.5"></i> ส่งตรวจสอบ
                </button>
            </form>
        @endif
    </x-page-header>

    @if($item->workflow_status === 'unlock_requested')
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
            <i class="bi bi-unlock-fill fs-3 me-3 text-warning-emphasis"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">ผลงานนี้อยู่ระหว่างยื่นคำขอเปิดสิทธิ์แก้ไข (รอการอนุมัติจากผู้ดูแลระบบ)</h6>
                <div class="small"><strong>เหตุผลที่ระบุ:</strong> {{ $item->unlock_reason }} (ยื่นเมื่อ {{ \Carbon\Carbon::parse($item->unlock_requested_at)->format('d/m/Y H:i') }})</div>
            </div>
        </div>
    @elseif($item->workflow_status === 'draft' && $item->reviewed_at)
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
            <i class="bi bi-info-circle-fill fs-3 me-3 text-info"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">ผลงานถูกย้ายมาที่ "ฉบับร่าง" เพื่อให้ท่านดำเนินการแก้ไข</h6>
                <div class="small">เมื่อแก้ไขข้อมูลเรียบร้อยแล้ว กรุณากดปุ่ม <strong>"ส่งตรวจสอบ"</strong> เพื่อส่งให้ผู้ดูแลระบบอนุมัติเผยแพร่อีกครั้ง</div>
            </div>
        </div>
    @elseif($item->workflow_status === 'revision_required')
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-octagon-fill fs-3 me-3 text-danger"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">ผู้ดูแลระบบส่งผลงานกลับมาให้แก้ไขเพิ่มเติม</h6>
                <div class="small"><strong>ข้อเสนอแนะ:</strong> {{ $item->rejection_remarks }}</div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- General Info Card -->
            <div class="card shadow-sm mb-4 border">
                <div class="card-header text-white p-3 p-md-4 d-print-none border-0" style="background: var(--rdb-gradient-primary);">
                    <span class="small text-white-50"><i class="bi bi-rocket-takeoff me-1"></i> ข้อมูลการนำไปใช้ประโยชน์</span>
                    <h4 class="card-title mb-0 mt-1 fw-semibold text-white text-break" style="font-size: 1.15rem; line-height: 1.55;">{{ html_entity_decode($item->utz_department_name) }}</h4>
                </div>
                <div class="card-body">
                    
                    @if($item->utz_group)
                        <div class="mb-4">
                            @php
                                $groupIds = array_map('trim', explode(',', $item->utz_group));
                                $types = \App\Models\RdbProjectUtilizeType::whereIn('utz_type_id', $groupIds)->get();
                            @endphp
                            @foreach($types as $type)
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle me-1 shadow-sm">{{ $type->utz_typr_name }}</span>
                            @endforeach
                        </div>
                    @endif

                    <h6 class="fw-bold border-bottom pb-2 mb-3">รายละเอียดการได้รับประโยชน์</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            @if($item->utz_date)
                            <tr>
                                <th style="width: 35%;">วันที่ใช้ประโยชน์:</th>
                                <td>{{ \App\Helpers\ThaiDateHelper::format($item->utz_date) }}</td>
                            </tr>
                            @endif
                            @if($item->utilizeType)
                            <tr>
                                <th>กลุ่มการใช้ประโยชน์:</th>
                                <td>{{ $item->utilizeType->utz_typr_name }}</td>
                            </tr>
                            @endif
                            @if($item->utz_leading)
                            <tr>
                                <th>ผู้ประสานงาน / ผู้รับประโยชน์:</th>
                                <td>
                                    <strong>{{ $item->utz_leading }}</strong>
                                    @if($item->utz_leading_position)
                                        <div class="text-muted small">{{ $item->utz_leading_position }}</div>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if($item->utz_department_name)
                            <tr>
                                <th>หน่วยงานที่นำไปใช้:</th>
                                <td>{{ $item->utz_department_name }}</td>
                            </tr>
                            @endif
                            @if($item->utz_department_address)
                            <tr>
                                <th>ที่อยู่หน่วยงาน:</th>
                                <td>{{ $item->utz_department_address }}</td>
                            </tr>
                            @endif
                            @if($item->changwat)
                            <tr>
                                <th>ที่ตั้ง / จังหวัด:</th>
                                <td>
                                    <i class="bi bi-geo-alt text-danger"></i> {{ $item->changwat->tambon_t }}, {{ $item->changwat->amphoe_t }}, {{ $item->changwat->changwat_t }}
                                </td>
                            </tr>
                            @endif
                            @if($item->utz_year_bud || $item->utz_year_edu)
                            <tr>
                                <th>ปีงบประมาณ / ปีการศึกษา:</th>
                                <td>
                                    @if($item->utz_year_bud) <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">งบประมาณ: {{ $item->utz_year_bud }}</span> @endif
                                    @if($item->utz_year_edu) <span class="badge bg-dark-subtle text-dark-emphasis border border-dark-subtle">การศึกษา: {{ $item->utz_year_edu }}</span> @endif
                                </td>
                            </tr>
                            @endif
                            @if($item->utz_budget && $item->utz_budget > 0)
                            <tr>
                                <th>งบประมาณที่เกี่ยวข้อง:</th>
                                <td class="fw-bold text-success">{{ number_format($item->utz_budget, 2) }} บาท</td>
                            </tr>
                            @endif
                            <tr>
                                <th>จำนวนเข้าชม:</th>
                                <td>{{ number_format($item->utz_count ?? 0) }} ครั้ง</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detail Content Card -->
            @if($item->utz_detail)
            <div class="card shadow-sm mb-4 border">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-2.5 px-3 border-0">
                    <h6 class="mb-0 fw-semibold text-body"><i class="bi bi-file-text-fill text-primary me-2"></i>รายละเอียดการดำเนินงาน (Utilization Details)</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText('utilize-backend-detail-text', this)">
                        <i class="bi bi-clipboard me-1"></i> คัดลอกรายละเอียด
                    </button>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="abstract-box p-3 p-md-4 border border-start border-4 border-primary rounded-3 bg-body-tertiary shadow-xs">
                        <div id="utilize-backend-detail-text" class="abstract-content abstract-indent">
                            {!! strip_tags($item->utz_detail, '<p><br><b><strong><i><em><u><ul><ol><li><a><span>') !!}
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Note Card -->
            @if($item->utz_note)
            <div class="card shadow-sm mb-4 border">
                <div class="card-header bg-body-tertiary py-2.5 px-3 border-0">
                    <h6 class="mb-0 fw-semibold text-body"><i class="bi bi-sticky text-primary me-2"></i>หมายเหตุ (Note)</h6>
                </div>
                <div class="card-body p-3">
                    <div class="abstract-box p-3 border border-start border-4 border-info rounded-3 bg-body-tertiary shadow-xs">
                        <div class="text-body-secondary small" style="line-height: 1.7;">
                            {!! nl2br(e($item->utz_note)) !!}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- File Attachment -->
            <div class="card shadow-sm mb-4 d-print-none border">
                <div class="card-header bg-body-tertiary py-3 border-0">
                    <h5 class="mb-0 fw-bold text-body"><i class="bi bi-paperclip text-primary me-2"></i>ไฟล์แนบ (File Attachment)</h5>
                </div>
                <div class="card-body text-center p-4 border-top">
                    @if($item->utz_files)
                        <div class="mb-3">
                            <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                        @foreach(explode(',', $item->utz_files) as $file)
                            @if(trim($file))
                                @php
                                    $trimmedFile = trim($file);
                                    $ext = pathinfo($trimmedFile, PATHINFO_EXTENSION);
                                    $nameOnly = pathinfo($trimmedFile, PATHINFO_FILENAME);
                                    $displayName = (mb_strlen($nameOnly) > 15) 
                                        ? mb_substr($nameOnly, 0, 15) . '...' . ($ext ? '.' . $ext : '')
                                        : $trimmedFile;
                                @endphp
                                <a href="{{ route('backend.rdbprojectutilize.download', ['id' => $item->utz_id, 'filename' => $trimmedFile]) }}" 
                                   target="_blank" 
                                   class="btn btn-outline-primary btn-sm w-100 mb-1"
                                   title="{{ $trimmedFile }}">
                                    <i class="bi bi-eye me-1"></i> {{ $displayName }}
                                </a>
                            @endif
                        @endforeach
                        </div>
                        @if($item->utz_countfile)
                            <p class="text-muted small mb-0"><i class="bi bi-download me-1"></i>ดาวน์โหลดรวม {{ number_format($item->utz_countfile) }} ครั้ง</p>
                        @endif
                    @else
                        <div class="text-muted py-3">
                             <i class="bi bi-file-earmark-x" style="font-size: 2.5rem;"></i>
                             <div class="mt-2">ไม่มีไฟล์แนบ</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Project -->
            @if($item->project)
            <div class="card shadow-sm mb-4 border">
                <div class="card-header text-white border-0" style="background: var(--rdb-gradient-primary);">
                    <h5 class="mb-0"><i class="bi bi-folder-symlink me-2"></i>โครงการวิจัยต้นแบบ</h5>
                </div>
                <div class="card-body border-top">
                    <h6 class="fw-bold text-success mb-2">{{ $item->project->pro_nameTH }}</h6>
                    <div class="small mb-3">รหัสโครงการ: {{ $item->project->pro_code ?? '-' }}</div>
                    <div class="d-grid shadow-sm mt-2">
                        <a href="{{ route('backend.rdb_project.show', $item->pro_id) }}" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-box-arrow-up-right me-1"></i> ดูรายละเอียดโครงการ
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Map Card -->
            @if($item->changwat && $item->changwat->lat && $item->changwat->long)
            <div class="card shadow-sm mb-4 border">
                <div class="card-header text-white border-0" style="background: var(--rdb-gradient-primary);">
                    <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>พิกัดสถานที่</h5>
                </div>
                <div class="card-body p-0 border-top">
                    <div class="ratio ratio-16x9">
                        <iframe style="border:0;" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"
                            src="https://maps.google.com/maps?q={{ $item->changwat->lat }},{{ $item->changwat->long }}&z=12&output=embed">
                        </iframe>
                    </div>
                </div>
            </div>
            @endif

            <!-- Metadata / System Info -->
            <div class="mb-4">
                @php
                    $createdByName = $item->createdBy?->researcher ? ($item->createdBy->researcher->researcher_fname . ' ' . $item->createdBy->researcher->researcher_lname) : ($item->createdBy?->username ?? '-');
                    $updatedByName = $item->updatedBy?->researcher ? ($item->updatedBy->researcher->researcher_fname . ' ' . $item->updatedBy->researcher->researcher_lname) : ($item->updatedBy?->username ?? '-');
                @endphp
                 <x-system-info :created_at="$item->created_at" :created_by="$createdByName" :updated_at="$item->updated_at" :updated_by="$updatedByName" />
            </div>
        </div>
    </div>

    <!-- Bottom Actions -->
    <x-action-buttons 
        :backRoute="route('backend.rdbprojectutilize.index')"
        :editRoute="auth()->user()->can('update', $item) ? route('backend.rdbprojectutilize.edit', $item->utz_id) : null"
        :deleteRoute="auth()->user()->can('delete', $item) ? route('backend.rdbprojectutilize.destroy', $item->utz_id) : null"
        :canDelete="auth()->user()->can('delete', $item)"
        :showPrint="true"
    />
</div>

<!-- Request Unlock Modal -->
<div class="modal fade" id="requestUnlockModal" tabindex="-1" aria-labelledby="requestUnlockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow border-0">
            <form id="requestUnlockForm" method="POST" action="">
                @csrf
                <div class="modal-header bg-warning text-dark py-3">
                    <h6 class="modal-title fw-bold" id="requestUnlockModalLabel">
                        <i class="bi bi-unlock-fill me-1"></i> ยื่นคำขอเปิดสิทธิ์แก้ไขผลงานที่เผยแพร่แล้ว
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info small py-2 px-3 mb-3 border-0 bg-info-subtle text-info-emphasis">
                        <i class="bi bi-info-circle me-1"></i> ผลงานที่เผยแพร่แล้วถูกล็อกการแก้ไขตามระเบียบ หากได้รับการอนุมัติจาก Admin <strong>ระบบจะย้ายผลงานนี้ไปยัง "ฉบับร่าง"</strong> เพื่อให้ท่านดำเนินการแก้ไขและส่งตรวจใหม่อีกครั้ง
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-body-secondary small">รายการผลงาน:</label>
                        <div class="fw-bold text-body" id="modalUnlockItemTitle">-</div>
                    </div>
                    <div class="mb-3">
                        <label for="unlockReason" class="form-label fw-bold text-body small">
                            ระบุเหตุผลความจำเป็นในการแก้ไขผลงาน <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="unlockReason" name="reason" rows="4" placeholder="ระบุเหตุผลในการขอแก้ไขข้อมูล..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-sm btn-warning px-3 fw-bold">
                        <i class="bi bi-send me-1"></i> ส่งคำขอเปิดสิทธิ์แก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const unlockModal = document.getElementById('requestUnlockModal');
    if (unlockModal) {
        unlockModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const type = button.getAttribute('data-type');
            const id = button.getAttribute('data-id');
            const title = button.getAttribute('data-title');

            document.getElementById('modalUnlockItemTitle').textContent = title || ('ID #' + id);
            document.getElementById('requestUnlockForm').action = '/backend/workflow/' + type + '/' + id + '/request-unlock';
        });
    }
});
</script>
@endpush
@endsection
