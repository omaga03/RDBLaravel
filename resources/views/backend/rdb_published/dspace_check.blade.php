@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backend.rdb_published.index') }}" class="text-decoration-none">จัดการผลงานตีพิมพ์</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backend.rdb_published.dspace_index') }}" class="text-decoration-none">ส่งออก DSpace</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ตรวจสอบจำนวนหน้า & สถานะไฟล์ PDF</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('backend.rdb_published.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                <i class="bi bi-arrow-left me-1.5"></i> กลับหน้ารายการ
            </a>
        </div>
    </div>

    <!-- Mode Navigation Tabs -->
    <ul class="nav nav-pills mb-3 gap-2">
        <li class="nav-item">
            <a class="nav-link bg-body border border-subtle text-body fw-semibold shadow-sm d-inline-flex align-items-center hover-primary" href="{{ route('backend.rdb_published.dspace_index') }}">
                <i class="bi bi-box-arrow-up-right me-2 text-primary"></i> ระบบส่งออก DSpace (Batch Mode)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active fw-semibold shadow-sm d-inline-flex align-items-center" href="{{ route('backend.rdb_published.dspace_check') }}">
                <i class="bi bi-file-earmark-check me-2"></i> ตรวจสอบจำนวนหน้า &amp; สถานะไฟล์ PDF (Check Mode)
            </a>
        </li>
    </ul>

    <!-- PDF Engine Status Notification -->
    @if(!empty($hasPdftotext))
        <div class="card shadow-sm border border-success-subtle bg-body mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2.5 rounded-3 bg-success-subtle text-success">
                            <i class="bi bi-cpu fs-4"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-2 py-1">
                                    <i class="bi bi-shield-check me-1"></i> เอนจิน Poppler (pdftotext) พร้อมใช้งาน
                                </span>
                                <span class="badge bg-secondary-subtle text-body-secondary border border-subtle">ความแม่นยำสูง 99.99%</span>
                            </div>
                            <div class="small text-body-secondary mt-1">
                                ระบบใช้งานเอนจินไบนารีระดับ OS สำหรับแยกสกัดและนับหน้าไฟล์ PDF โดยรองรับเอกสาร PDF ทุกเวอร์ชันและไฟล์ที่มีการเข้ารหัสความปลอดภัย (AES-128 / Protected PDF)
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge font-monospace text-body-secondary bg-body-tertiary border border-subtle px-2.5 py-1.5" title="{{ $pdftotextBinary }}">
                            <i class="bi bi-terminal me-1"></i> {{ basename($pdftotextBinary) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm border border-warning-subtle bg-body mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="p-2.5 rounded-3 bg-warning-subtle text-warning-emphasis">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                <i class="bi bi-info-circle me-1"></i> กำลังใช้เอนจินสำรอง (Pure PHP Parser)
                            </span>
                            <span class="text-body-secondary small">แนะนำให้ติดตั้ง <code>poppler-utils</code> บนเซิร์ฟเวอร์เพื่อความแม่นยำ 99.99%</span>
                        </div>
                        <div class="small text-body-secondary mt-2">
                            ระบบกำลังนับหน้า PDF ด้วย PHP Parser ภายใน หากพบไฟล์ PDF ที่มีการเข้ารหัสขั้นสูง (เช่น AES-128 / Secured PDF) อาจทำให้อ่านจำนวนหน้าไม่ได้ แนะนำให้ผู้ดูแลระบบติดตั้ง <code>pdftotext</code> (ชุดเครื่องมือ Poppler):
                        </div>
                        <div class="mt-2 p-2.5 rounded-2 bg-body-tertiary border border-subtle small font-monospace text-body">
                            <div class="mb-1"><span class="text-body-secondary fw-semibold">Ubuntu / Debian:</span> <code>sudo apt update && sudo apt install -y poppler-utils</code></div>
                            <div class="mb-1"><span class="text-body-secondary fw-semibold">RHEL / CentOS / Alma:</span> <code>sudo dnf install -y poppler-utils</code></div>
                            <div><span class="text-body-secondary fw-semibold">หรือระบุใน .env:</span> <code>PDFTOTEXT_PATH="/usr/bin/pdftotext"</code></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Stat Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2.4">
            <div class="card shadow-sm border h-100 bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-medium">ทั้งหมดที่มีไฟล์</span>
                            <h4 class="mb-0 fw-bold text-body mt-1" style="font-variant-numeric: tabular-nums;">{{ number_format($totalFiles) }}</h4>
                        </div>
                        <div class="bg-primary-subtle text-primary p-2.5 rounded-3">
                            <i class="bi bi-file-earmark-pdf fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2.4">
            <div class="card shadow-sm border h-100 bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-medium">นับหน้าแล้ว</span>
                            <h4 class="mb-0 fw-bold text-success mt-1" style="font-variant-numeric: tabular-nums;">{{ number_format($countedFiles) }}</h4>
                        </div>
                        <div class="bg-success-subtle text-success p-2.5 rounded-3">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2.4">
            <div class="card shadow-sm border h-100 bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-medium">ยังไม่นับหน้า</span>
                            <h4 class="mb-0 fw-bold text-danger mt-1" style="font-variant-numeric: tabular-nums;">{{ number_format($uncountedFiles) }}</h4>
                        </div>
                        <div class="bg-danger-subtle text-danger p-2.5 rounded-3">
                            <i class="bi bi-dash-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-xl-2.4">
            <div class="card shadow-sm border h-100 bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-medium">บทความสมบูรณ์</span>
                            <h4 class="mb-0 fw-bold text-success mt-1" style="font-variant-numeric: tabular-nums;">{{ number_format($status1Count) }}</h4>
                        </div>
                        <div class="bg-success-subtle text-success p-2.5 rounded-3">
                            <i class="bi bi-file-earmark-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-2.4">
            <div class="card shadow-sm border h-100 bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-medium">ตัวอย่างบทความ</span>
                            <h4 class="mb-0 fw-bold text-warning-emphasis mt-1" style="font-variant-numeric: tabular-nums;">{{ number_format($status2Count) }}</h4>
                        </div>
                        <div class="bg-warning-subtle text-warning-emphasis p-2.5 rounded-3">
                            <i class="bi bi-file-earmark-text fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Auto-Check Bar -->
    <div class="card shadow-sm border mb-4 bg-body">
        <div class="card-body p-3">
            <form action="{{ route('backend.rdb_published.dspace_check') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-body-tertiary border-subtle"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="ค้นหาชื่อบทความ, วารสาร, หรือชื่อไฟล์...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- ทุกสถานะ --</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>บทความสมบูรณ์</option>
                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>ตัวอย่างบทความ</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>ยังไม่กำหนด</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="page_filter" class="form-select form-select-sm">
                        <option value="">-- จำนวนหน้าทั้งหมด --</option>
                        <option value="uncounted" {{ request('page_filter') === 'uncounted' ? 'selected' : '' }}>ยังไม่นับหน้า</option>
                        <option value="counted" {{ request('page_filter') === 'counted' ? 'selected' : '' }}>นับหน้าแล้ว</option>
                        <option value="low" {{ request('page_filter') === 'low' ? 'selected' : '' }}>น้อยกว่า 5 หน้า (&lt; 5)</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach($allowedPerPage as $val)
                            <option value="{{ $val }}" {{ $val == $perPage ? 'selected' : '' }}>หน้าละ {{ $val }} รายการ</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> กรอง
                    </button>
                    @if(request()->hasAny(['q', 'status', 'page_filter', 'per_page']))
                        <a href="{{ route('backend.rdb_published.dspace_check') }}" class="btn btn-outline-secondary btn-sm" title="ล้างตัวกรอง">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>

            <hr class="my-3">

            <!-- Auto-Check Row -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center shadow-sm" id="btn-auto-check">
                        <i class="bi bi-play-circle-fill me-1.5"></i> เช็คอัตโนมัติทั้งหมดในหน้านี้
                    </button>
                    <span class="text-body-secondary small">
                        <i class="bi bi-clock me-1"></i> (ประมวลผลทีละรายการ ช่วงห่าง 0.5 วินาที)
                    </span>
                </div>
                <div id="auto-check-progress-container" class="d-none align-items-center gap-2 flex-grow-1 max-w-400 ms-md-3">
                    <div class="progress flex-grow-1" style="height: 8px;">
                        <div id="auto-check-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                    </div>
                    <span id="auto-check-status-text" class="small text-body-secondary fw-semibold text-nowrap" style="font-variant-numeric: tabular-nums;">0 / 0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border mb-4 bg-body">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-list-check me-2 text-primary"></i>รายการตรวจสอบเอกสารและจำนวนหน้า
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2" style="font-variant-numeric: tabular-nums;">
                    {{ number_format($items->total()) }} รายการ
                </span>
            </h5>
            <small class="text-body-secondary">
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle me-1">&lt; 5 หน้า</span> แถบสีเหลืองแจ้งเตือนบทความที่อาจเป็นเพียงตัวอย่าง
            </small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="check-table">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 65px;">ID</th>
                            <th style="width: 130px;">ตัวอย่างไฟล์</th>
                            <th>ข้อมูลบทความ / แหล่งตีพิมพ์</th>
                            <th style="width: 240px;">การจัดการสถานะ</th>
                            <th class="text-center" style="width: 140px;">จำนวนหน้า & สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                        @php
                            $numPages = !empty($row->pub_numfile) ? (int)$row->pub_numfile : 0;
                            $isLowPage = ($numPages > 0 && $numPages < 5);
                            $viewFileUrl = route('backend.rdb_published.view_file', $row->id);
                        @endphp
                        <tr id="row-{{ $row->id }}" class="{{ $isLowPage ? 'table-warning bg-opacity-25' : '' }}">
                            <td class="text-center fw-bold text-body-secondary" style="font-variant-numeric: tabular-nums;">
                                {{ $row->id }}
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm btn-preview-pdf d-inline-flex align-items-center justify-content-center"
                                            data-id="{{ $row->id }}"
                                            data-title="{{ $row->pub_name }}"
                                            data-url="{{ $viewFileUrl }}"
                                            title="เปิดดูไฟล์ในกล่องข้อความ">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> ดูตัวอย่าง
                                    </button>
                                    <a href="{{ $viewFileUrl }}" target="_blank" class="btn btn-light btn-sm border text-body-secondary d-inline-flex align-items-center justify-content-center" title="เปิดแท็บใหม่">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> แท็บใหม่
                                    </a>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('backend.rdb_published.show', $row->id) }}" class="fw-semibold text-decoration-none text-body hover-primary d-block mb-1" target="_blank">
                                    {{ $row->pub_name }}
                                </a>
                                @if($row->pub_name_journal)
                                    <div class="small text-body-secondary mb-1">
                                        <i class="bi bi-journal-bookmark me-1 text-primary"></i>{{ $row->pub_name_journal }}
                                    </div>
                                @endif
                                <div class="d-flex flex-wrap gap-2 align-items-center small text-body-secondary">
                                    @if($row->researcher)
                                        <span><i class="bi bi-person me-1"></i>{{ $row->researcher->res_name }}</span>
                                    @endif
                                    @if($row->department)
                                        <span><i class="bi bi-building me-1"></i>{{ $row->department->department_nameTH }}</span>
                                    @endif
                                    <span class="text-truncate max-w-250 text-body-tertiary" title="{{ $row->pub_file }}">
                                        <i class="bi bi-paperclip me-0.5"></i>{{ $row->pub_file }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1.5">
                                    <!-- ปุ่มนับหน้า -->
                                    <button type="button" 
                                            class="btn btn-outline-secondary btn-sm text-start btn-check-page d-inline-flex align-items-center justify-content-between"
                                            data-id="{{ $row->id }}">
                                        <span><i class="bi bi-arrow-repeat me-1.5 icon-spin-target"></i>นับหน้า PDF</span>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill small">Auto</span>
                                    </button>

                                    <!-- ปุ่มสถานะ 1: บทความสมบูรณ์ -->
                                    <button type="button" 
                                            class="btn btn-sm btn-status-1 text-start d-inline-flex align-items-center justify-content-between {{ $row->todspace == 1 ? 'btn-success text-white active' : 'btn-outline-success' }}"
                                            data-id="{{ $row->id }}"
                                            data-status="1">
                                        <span><i class="bi bi-check-circle-fill me-1.5"></i>บทความสมบูรณ์</span>
                                        @if($row->todspace == 1)
                                            <i class="bi bi-check2"></i>
                                        @endif
                                    </button>

                                    <!-- ปุ่มสถานะ 2: ตัวอย่างบทความ -->
                                    <button type="button" 
                                            class="btn btn-sm btn-status-2 text-start d-inline-flex align-items-center justify-content-between {{ $row->todspace == 2 ? 'btn-warning text-dark active' : 'btn-outline-warning text-body' }}"
                                            data-id="{{ $row->id }}"
                                            data-status="2">
                                        <span><i class="bi bi-star-fill me-1.5 text-warning"></i>ตัวอย่างบทความ</span>
                                        @if($row->todspace == 2)
                                            <i class="bi bi-check2"></i>
                                        @endif
                                    </button>
                                </div>
                            </td>
                            <td class="text-center">
                                <!-- Badge จำนวนหน้า -->
                                <div class="mb-2">
                                    @if(!empty($row->pub_numfile) && $row->pub_numfile > 0)
                                        <span class="badge badge-page-count {{ $row->pub_numfile < 5 ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-success-subtle text-success-emphasis border border-success-subtle' }} px-2.5 py-1.5 fs-7" style="font-variant-numeric: tabular-nums;">
                                            <i class="bi bi-file-earmark-check me-1"></i><span class="num-text">{{ $row->pub_numfile }}</span> หน้า
                                        </span>
                                    @else
                                        <span class="badge badge-page-count bg-danger-subtle text-danger-emphasis border border-danger-subtle px-2.5 py-1.5 fs-7" style="font-variant-numeric: tabular-nums;">
                                            <i class="bi bi-dash-circle me-1"></i><span class="num-text">ยังไม่นับ</span>
                                        </span>
                                    @endif
                                </div>

                                <!-- Badge สถานะ todspace -->
                                <div>
                                    @if($row->todspace == 1)
                                        <span class="badge badge-todspace-status bg-success-subtle text-success-emphasis border border-success-subtle">บทความสมบูรณ์</span>
                                    @elseif($row->todspace == 2)
                                        <span class="badge badge-todspace-status bg-warning-subtle text-warning-emphasis border border-warning-subtle">ตัวอย่างบทความ</span>
                                    @else
                                        <span class="badge badge-todspace-status bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">ยังไม่กำหนด</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-body-tertiary"></i>
                                ไม่พบรายการผลงานตีพิมพ์ตามเงื่อนไขที่เลือก
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 bg-body-tertiary">
                <small class="text-body-secondary">
                    แสดงรายการที่ {{ number_format($items->firstItem()) }} ถึง {{ number_format($items->lastItem()) }} จากทั้งหมด {{ number_format($items->total()) }} รายการ
                </small>
                <div>
                    {{ $items->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: PDF Quick Preview -->
<div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-labelledby="pdfPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 90vw;">
        <div class="modal-content shadow-lg border">
            <div class="modal-header py-2.5 bg-body-tertiary">
                <h5 class="modal-title fs-6 fw-bold text-body text-truncate max-w-700" id="pdfPreviewModalLabel">
                    <i class="bi bi-file-earmark-pdf me-2 text-primary"></i>ตัวอย่างไฟล์เอกสาร
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="pdfPreviewFrame" src="" class="w-100 h-100 border-0"></iframe>
            </div>
            <div class="modal-footer py-2 bg-body-tertiary d-flex justify-content-between">
                <a href="#" id="modalOpenExternalBtn" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i> เปิดดูเต็มหน้าต่างใหม่
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<style>
.spin {
    animation: spin 1s linear infinite;
    display: inline-block;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}
.max-w-400 {
    max-width: 400px;
}
.max-w-250 {
    max-width: 250px;
}
.max-w-700 {
    max-width: 700px;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = "{{ csrf_token() }}";
    const countPageUrl = "{{ route('backend.rdb_published.dspace_ajax_count_page') }}";
    const updateItemStatusUrl = "{{ route('backend.rdb_published.dspace_ajax_update_status') }}";

    // 1. PDF Preview Modal
    const previewModalEl = document.getElementById('pdfPreviewModal');
    const previewModal = new bootstrap.Modal(previewModalEl);
    const previewFrame = document.getElementById('pdfPreviewFrame');
    const previewTitle = document.getElementById('pdfPreviewModalLabel');
    const modalOpenExternalBtn = document.getElementById('modalOpenExternalBtn');

    document.querySelectorAll('.btn-preview-pdf').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            const title = this.getAttribute('data-title');
            
            previewTitle.innerHTML = '<i class="bi bi-file-earmark-pdf me-2 text-primary"></i>' + title;
            previewFrame.src = url;
            modalOpenExternalBtn.href = url;
            previewModal.show();
        });
    });

    previewModalEl.addEventListener('hidden.bs.modal', function () {
        previewFrame.src = '';
    });

    // 2. Count PDF Page (Single Item)
    function executeCountPage(btn, callback) {
        const id = btn.getAttribute('data-id');
        const row = document.getElementById('row-' + id);
        const icon = btn.querySelector('.icon-spin-target');
        const badgePage = row.querySelector('.badge-page-count');
        const numText = badgePage.querySelector('.num-text');

        btn.disabled = true;
        if (icon) icon.classList.add('spin');
        numText.textContent = 'กำลังนับ...';

        fetch(countPageUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (icon) icon.classList.remove('spin');
            btn.disabled = false;

            if (data.success) {
                numText.textContent = data.pages;
                badgePage.className = 'badge badge-page-count px-2.5 py-1.5 fs-7 border';
                if (data.pages < 5) {
                    badgePage.classList.add('bg-warning-subtle', 'text-warning-emphasis', 'border-warning-subtle');
                    row.classList.add('table-warning', 'bg-opacity-25');
                } else {
                    badgePage.classList.add('bg-success-subtle', 'text-success-emphasis', 'border-success-subtle');
                    row.classList.remove('table-warning', 'bg-opacity-25');
                }
            } else {
                numText.textContent = 'ไม่สำเร็จ';
                badgePage.className = 'badge badge-page-count bg-danger-subtle text-danger-emphasis border border-danger-subtle px-2.5 py-1.5 fs-7';
            }

            if (typeof callback === 'function') {
                callback(data);
            }
        })
        .catch(err => {
            console.error(err);
            if (icon) icon.classList.remove('spin');
            btn.disabled = false;
            numText.textContent = 'เกิดข้อผิดพลาด';
            if (typeof callback === 'function') {
                callback({ success: false });
            }
        });
    }

    document.querySelectorAll('.btn-check-page').forEach(btn => {
        btn.addEventListener('click', function () {
            executeCountPage(this);
        });
    });

    // 3. Update Status (1: สมบูรณ์, 2: ตัวอย่าง)
    function executeUpdateStatus(btn) {
        const id = btn.getAttribute('data-id');
        const status = btn.getAttribute('data-status');
        const row = document.getElementById('row-' + id);
        const statusBadge = row.querySelector('.badge-todspace-status');
        const btn1 = row.querySelector('.btn-status-1');
        const btn2 = row.querySelector('.btn-status-2');

        btn.disabled = true;

        fetch(updateItemStatusUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id, status: parseInt(status) })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.success) {
                // Update Badge
                statusBadge.textContent = data.status_text;
                statusBadge.className = 'badge badge-todspace-status border';
                if (data.status === 1) {
                    statusBadge.classList.add('bg-success-subtle', 'text-success-emphasis', 'border-success-subtle');
                    btn1.className = 'btn btn-sm btn-status-1 text-start d-inline-flex align-items-center justify-content-between btn-success text-white active';
                    btn2.className = 'btn btn-sm btn-status-2 text-start d-inline-flex align-items-center justify-content-between btn-outline-warning text-body';
                } else {
                    statusBadge.classList.add('bg-warning-subtle', 'text-warning-emphasis', 'border-warning-subtle');
                    btn1.className = 'btn btn-sm btn-status-1 text-start d-inline-flex align-items-center justify-content-between btn-outline-success';
                    btn2.className = 'btn btn-sm btn-status-2 text-start d-inline-flex align-items-center justify-content-between btn-warning text-dark active';
                }
            } else {
                alert('เกิดข้อผิดพลาด: ' + (data.message || 'บันทึกไม่สำเร็จ'));
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้');
        });
    }

    document.querySelectorAll('.btn-status-1, .btn-status-2').forEach(btn => {
        btn.addEventListener('click', function () {
            executeUpdateStatus(this);
        });
    });

    // 4. Auto Check All
    const btnAutoCheck = document.getElementById('btn-auto-check');
    const progressContainer = document.getElementById('auto-check-progress-container');
    const progressBar = document.getElementById('auto-check-progress-bar');
    const progressText = document.getElementById('auto-check-status-text');

    if (btnAutoCheck) {
        btnAutoCheck.addEventListener('click', function () {
            const buttons = Array.from(document.querySelectorAll('.btn-check-page'));
            if (buttons.length === 0) {
                alert('ไม่มีรายการที่ต้องตรวจสอบในหน้านี้');
                return;
            }

            if (!confirm('ยืนยันเริ่มการตรวจสอบจำนวนหน้าทั้งหมด ' + buttons.length + ' รายการในหน้านี้?')) {
                return;
            }

            btnAutoCheck.disabled = true;
            btnAutoCheck.innerHTML = '<i class="bi bi-arrow-repeat spin me-1.5"></i> กำลังตรวจสอบอัตโนมัติ...';
            progressContainer.classList.remove('d-none');
            progressContainer.classList.add('d-flex');

            let index = 0;
            const total = buttons.length;

            function processNext() {
                if (index >= total) {
                    progressBar.style.width = '100%';
                    progressText.textContent = total + ' / ' + total + ' เสร็จสมบูรณ์!';
                    btnAutoCheck.disabled = false;
                    btnAutoCheck.innerHTML = '<i class="bi bi-check2-all me-1.5"></i> ตรวจสอบครบแล้ว';
                    setTimeout(() => {
                        btnAutoCheck.innerHTML = '<i class="bi bi-play-circle-fill me-1.5"></i> เช็คอัตโนมัติทั้งหมดในหน้านี้';
                    }, 4000);
                    return;
                }

                const currentBtn = buttons[index];
                const currentId = currentBtn.getAttribute('data-id');

                const percent = Math.round(((index + 1) / total) * 100);
                progressBar.style.width = percent + '%';
                progressText.textContent = (index + 1) + ' / ' + total;

                executeCountPage(currentBtn, function () {
                    index++;
                    setTimeout(processNext, 500); // 500ms delay between items
                });
            }

            processNext();
        });
    }
});
</script>
@endpush
@endsection
