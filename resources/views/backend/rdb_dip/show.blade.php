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
        title="รายละเอียดทรัพย์สินทางปัญญา (IP Details)"
        icon="bi-patch-check"
        :backRoute="route('backend.rdb_dip.index')"
        :editRoute="auth()->user()->can('update', $item) ? route('backend.rdb_dip.edit', $item->dip_id) : null"
        :deleteRoute="auth()->user()->can('delete', $item) ? route('backend.rdb_dip.destroy', $item->dip_id) : null"
        :canDelete="auth()->user()->can('delete', $item)"
        :showPrint="true"
        :breadcrumbs="[
            'แดชบอร์ด' => route('backend.site.index'),
            'จัดการทรัพย์สินทางปัญญา' => route('backend.rdb_dip.index'),
            'รายละเอียดผลงาน' => ''
        ]"
    >
        @if($item->workflow_status === 'published' && auth()->user()->can('requestUnlock', $item))
            <button type="button" class="btn btn-warning d-inline-flex justify-content-center align-items-center" style="min-width: 105px; height: 38px; font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#requestUnlockModal" data-id="{{ $item->dip_id }}" data-type="dip" data-title="{{ $item->dip_name ?: ($item->dip_data2_name ?? 'ทรัพย์สินทางปัญญา') }}">
                <i class="bi bi-unlock me-1.5"></i> ขอแก้ไข
            </button>
        @elseif($item->workflow_status === 'unlock_requested')
            @if(auth()->user()->can('approveUnlock', $item))
                <form action="{{ route('backend.workflow.approve_unlock', ['type' => 'dip', 'id' => $item->dip_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันอนุมัติให้แก้ไข และย้ายผลงานนี้ไปยังฉบับร่างหรือไม่?');">
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
            <form action="{{ route('backend.workflow.submit', ['type' => 'dip', 'id' => $item->dip_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันส่งผลงานนี้เข้าสู่กระบวนการตรวจรับ?');">
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
            <!-- General Information -->
            <div class="card shadow-sm mb-4 border">
                <div class="card-header text-white p-3 p-md-4 d-print-none border-0" style="background: var(--rdb-gradient-primary);">
                    <span class="small text-white-50"><i class="bi bi-award me-1"></i> ข้อมูลทรัพย์สินทางปัญญา</span>
                    <h4 class="card-title mb-0 mt-1 fw-semibold text-white text-break" style="font-size: 1.15rem; line-height: 1.55;">{{ $item->dip_data2_name ?? ($item->dip_name ?? 'ทรัพย์สินทางปัญญา') }}</h4>
                    @if($item->dip_nameEN)
                        <p class="text-white-50 small mb-0 mt-1 text-break">{{ $item->dip_nameEN }}</p>
                    @endif
                </div>
                <div class="card-body">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">รายละเอียดเพิ่มเติม</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless">
                            @if($item->dip_request_date)
                            <tr>
                                <th style="width: 30%;">ปีที่ขอรับ:</th>
                                <td><span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">{{ $item->dip_request_date->year + 543 }}</span></td>
                            </tr>
                            @endif
                            @if($item->dipType)
                            <tr>
                                <th>ประเภทผลงาน:</th>
                                <td>{{ $item->dipType->dipt_name }}</td>
                            </tr>
                            @endif
                            @if($item->dip_request_number)
                            <tr>
                                <th>เลขที่คำขอ:</th>
                                <td class="fw-bold text-primary">{{ $item->dip_request_number }}</td>
                            </tr>
                            @endif
                            @if($item->dip_request_date)
                            <tr>
                                <th>วันที่ยื่นคำขอ:</th>
                                <td>{{ \App\Helpers\ThaiDateHelper::format($item->dip_request_date, false, true) }}</td>
                            </tr>
                            @endif
                            @if(($item->researcher && $item->researcher->department) || ($item->project && $item->project->department))
                            <tr>
                                <th>หน่วยงาน:</th>
                                <td>
                                    @if($item->researcher && $item->researcher->department)
                                        {{ $item->researcher->department->department_nameTH }}
                                    @elseif($item->project && $item->project->department)
                                        {{ $item->project->department->department_nameTH }}
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if($item->researcher)
                            <tr>
                                <th>ผู้ประดิษฐ์/นักวิจัย:</th>
                                <td>
                                    @php
                                        $canManageDipRes = auth()->user()->hasRole(['Admin', 'The Creator', 'Admin Research', 'Admin Dip', 'Dip']) || auth()->user()->id === 129;
                                        $isSelfDipRes = (int)auth()->user()->researcher_id === (int)$item->researcher_id;
                                    @endphp
                                    @if($canManageDipRes || $isSelfDipRes)
                                        <a href="{{ route('backend.rdb_researcher.show', $item->researcher_id) }}" class="fw-bold text-decoration-none">
                                            {{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                                            @if($isSelfDipRes)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.72rem;">(คุณ)</span>
                                            @endif
                                        </a>
                                    @else
                                        <span class="fw-bold text-body">
                                            {{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                                        </span>
                                    @endif
                                    @if($item->researcher->department)
                                        <span class="text-body-secondary ms-1">({{ $item->researcher->department->department_nameTH }})</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if($item->project)
                            <tr>
                                <th>โครงการวิจัยที่เกี่ยวข้อง:</th>
                                <td>
                                    <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" class="fw-bold text-decoration-none text-body hover-primary">
                                        <i class="bi bi-folder-symlink me-1 text-primary"></i> {{ $item->project->pro_nameTH }}
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @if($item->dip_patent_number)
                            <tr>
                                <th>เลขที่สิทธิบัตร (Patent No.):</th>
                                <td class="fw-bold text-success">{{ $item->dip_patent_number }}</td>
                            </tr>
                            @endif
                            @if($item->dip_publication_no)
                            <tr>
                                <th>เลขที่ประกาศโฆษณา:</th>
                                <td>{{ $item->dip_publication_no }} @if($item->dip_publication_date) (เมื่อ {{ \App\Helpers\ThaiDateHelper::format($item->dip_publication_date, false, true) }}) @endif</td>
                            </tr>
                            @endif
                            @if($item->dip_data2_agent)
                            <tr>
                                <th>เลขที่สิทธิบัตร/อนุสิทธิบัตร (Legacy):</th>
                                <td><span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">{{ $item->dip_data2_agent }}</span></td>
                            </tr>
                            @endif
                            @if($item->dip_data2_status)
                            <tr>
                                <th>สถานะล่าสุด:</th>
                                <td>{{ $item->dip_data2_status }}</td>
                            </tr>
                            @endif
                            @if($item->dip_data2_dateend)
                            <tr>
                                <th>วันครบกำหนดดูแล:</th>
                                <td>{{ \App\Helpers\ThaiDateHelper::format($item->dip_data2_dateend, false, true) }}</td>
                            </tr>
                            @endif
                            @if($item->dip_url)
                            <tr>
                                <th>ลิงก์ที่เกี่ยวข้อง:</th>
                                <td><a href="{{ $item->dip_url }}" target="_blank" class="text-decoration-none"><i class="bi bi-link-45deg"></i> เปิดลิงก์ภายนอก</a></td>
                            </tr>
                            @endif
                        </table>
                    </div>

                    @if($item->dip_note || $item->dip_data2_conclusion || $item->dip_data2_assertion)
                    <div class="mt-4">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                            <h6 class="fw-bold mb-0 text-body">
                                <i class="bi bi-file-text-fill me-1 text-primary"></i> บทสรุปการประดิษฐ์และรายละเอียด (Invention Summary & Details)
                            </h6>
                        </div>

                        @if($item->dip_data2_conclusion)
                            @php
                                $dipConclusionParts = \App\Helpers\AbstractHelper::split($item->dip_data2_conclusion);
                            @endphp

                            @if(count($dipConclusionParts) >= 2 && !empty(trim(strip_tags($dipConclusionParts[0]))) && !empty(trim(strip_tags($dipConclusionParts[1]))))
                                <div class="abstract-box p-3 p-md-4 border border-start border-4 border-primary rounded-3 mb-3 bg-body-tertiary shadow-xs">
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                                <i class="bi bi-lightbulb me-1"></i> บทสรุปการประดิษฐ์ (ภาษาไทย)
                                            </span>
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.7rem;">TH</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText('dip-backend-conclusion-th', this)">
                                            <i class="bi bi-clipboard me-1"></i> คัดลอกบทสรุป
                                        </button>
                                    </div>
                                    <div id="dip-backend-conclusion-th" class="abstract-content abstract-indent">
                                        {!! nl2br(e($dipConclusionParts[0])) !!}
                                    </div>
                                </div>

                                <div class="abstract-box p-3 p-md-4 border border-start border-4 border-info rounded-3 mb-3 bg-body-tertiary shadow-xs">
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                                <i class="bi bi-translate me-1"></i> Invention Summary (English)
                                            </span>
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.7rem;">EN</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText('dip-backend-conclusion-en', this)">
                                            <i class="bi bi-clipboard me-1"></i> Copy Summary
                                        </button>
                                    </div>
                                    <div id="dip-backend-conclusion-en" class="abstract-content abstract-indent">
                                        {!! nl2br(e($dipConclusionParts[1])) !!}
                                    </div>
                                </div>
                            @else
                                <div class="abstract-box p-3 p-md-4 border border-start border-4 border-primary rounded-3 mb-3 bg-body-tertiary shadow-xs">
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                            <i class="bi bi-lightbulb me-1"></i> บทสรุปการประดิษฐ์ / สรุปผล
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText('dip-backend-conclusion-text', this)">
                                            <i class="bi bi-clipboard me-1"></i> คัดลอกบทสรุป
                                        </button>
                                    </div>
                                    <div id="dip-backend-conclusion-text" class="abstract-content abstract-indent">
                                        {!! nl2br(e($item->dip_data2_conclusion)) !!}
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($item->dip_data2_assertion)
                            <div class="abstract-box p-3 p-md-4 border border-start border-4 border-warning rounded-3 mb-3 bg-body-tertiary shadow-xs">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                        <i class="bi bi-shield-check me-1"></i> ข้อถือสิทธิ (Patent Claims)
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText('dip-backend-assertion-text', this)">
                                        <i class="bi bi-clipboard me-1"></i> คัดลอกข้อถือสิทธิ
                                    </button>
                                </div>
                                <div id="dip-backend-assertion-text" class="abstract-content abstract-indent">
                                    {!! nl2br(e($item->dip_data2_assertion)) !!}
                                </div>
                            </div>
                        @endif

                        @if($item->dip_note)
                            <div class="abstract-box p-3 p-md-4 border border-start border-4 border-info rounded-3 mb-3 bg-body-tertiary shadow-xs">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                        <i class="bi bi-chat-left-text me-1"></i> รายละเอียด / หมายเหตุ (Note)
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText('dip-backend-note-text', this)">
                                        <i class="bi bi-clipboard me-1"></i> คัดลอกหมายเหตุ
                                    </button>
                                </div>
                                <div id="dip-backend-note-text" class="abstract-content abstract-indent">
                                    {!! nl2br(e($item->dip_note)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- File Attachment -->
            <div class="card shadow-sm mb-4 d-print-none border">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3 border-0">
                    <h5 class="mb-0 fw-bold text-body"><i class="bi bi-paperclip text-primary me-2"></i>ไฟล์แนบ</h5>
                    @if(auth()->user()->can('update', $item))
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#fileModal">
                        <i class="bi bi-gear me-1"></i> จัดการ
                    </button>
                    @endif
                </div>
                <div class="card-body text-center p-4 border-top">
                    <div class="mb-3">
                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <div class="text-muted py-2">
                        <i class="bi bi-file-earmark-check"></i> เอกสารทรัพย์สินทางปัญญาหลัก
                    </div>
                    <div class="d-grid gap-2 mt-3">
                        @if($item->dip_files || $item->dip_path)
                            <a href="{{ route('backend.rdb_dip.view_file', $item->dip_id) }}" target="_blank" class="btn btn-primary d-flex align-items-center justify-content-center">
                                <i class="bi bi-eye me-2"></i> เปิดดูไฟล์
                            </a>
                        @else
                            <button class="btn btn-outline-secondary" disabled>
                                <i class="bi bi-file-earmark-x me-1"></i> ยังไม่มีไฟล์แนบ
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Related Project -->
            @if($item->project)
            <div class="card shadow-sm mb-4 border">
                <div class="card-header text-white border-0" style="background: var(--rdb-gradient-primary);">
                    <h5 class="mb-0"><i class="bi bi-folder-symlink me-2"></i>โครงการที่เกี่ยวข้อง</h5>
                </div>
                <div class="card-body border-top">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td colspan="2">
                                <h6 class="fw-bold text-primary mb-1">{{ $item->project->pro_nameTH }}</h6>
                                <div class="small text-muted mb-3">รหัส: {{ $item->project->pro_code ?? '-' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="d-grid">
                                <a href="{{ route('backend.rdb_project.show', $item->pro_id) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> ดูรายละเอียดโครงการ
                                </a>
                            </td>
                        </tr>
                    </table>
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
        :backRoute="route('backend.rdb_dip.index')"
        :editRoute="auth()->user()->can('update', $item) ? route('backend.rdb_dip.edit', $item->dip_id) : null"
        :deleteRoute="auth()->user()->can('delete', $item) ? route('backend.rdb_dip.destroy', $item->dip_id) : null"
        :canDelete="auth()->user()->can('delete', $item)"
        :showPrint="true"
    />
</div>

<!-- Modal Manage File (Relocated to root of section for proper DOM stacking context) -->
<div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-primary">
            <div class="modal-header text-white" style="background: var(--rdb-gradient-primary);">
                <h5 class="modal-title" id="fileModalLabel"><i class="bi bi-folder-fill me-2"></i>จัดการไฟล์แนบหลัก</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Current File Status -->
                <div class="mb-4 text-center p-3 rounded border" style="background-color: rgba(255, 255, 255, 0.03);">
                    <h6 class="fw-bold mb-3 border-bottom pb-2 text-primary">สถานะไฟล์ปัจจุบัน</h6>
                    @if($item->dip_files)
                        <div class="d-flex justify-content-between align-items-center px-2">
                            <div class="text-truncate" style="max-width: 200px;">
                                <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                                <small class="text-muted fw-bold">{{ $item->dip_files }}</small>
                            </div>
                            <form action="{{ route('backend.rdb_dip.delete_file', $item->dip_id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบไฟล์นี้?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i> ลบไฟล์
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-muted small italic">ยังไม่มีการอัปโหลดไฟล์ไฟล์ PDF</div>
                    @endif
                </div>

                <!-- Upload Form -->
                <form action="{{ route('backend.rdb_dip.upload_file', $item->dip_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="dip_files" class="form-label fw-bold">เลือกไฟล์ใหม่ <span class="text-muted fw-normal">(เฉพาะ PDF เท่านั้น)</span></label>
                        <input type="file" name="dip_files" id="dip_files" class="form-control border-primary bg-transparent text-reset" accept=".pdf" required>
                        <div class="form-text mt-2 small text-muted"><i class="bi bi-info-circle me-1"></i> ขนาดไฟล์ที่อนุญาตไม่เกิน 20MB</div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary shadow-sm">
                            <i class="bi bi-upload me-1"></i> {{ $item->dip_files ? 'อัปโหลดเปลี่ยนไฟล์ใหม่' : 'เริ่มอัปโหลดไฟล์' }}
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
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
