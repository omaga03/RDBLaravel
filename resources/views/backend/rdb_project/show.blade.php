@extends('layouts.app')

@section('content')
<!-- TomSelect CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    /* TomSelect Dark Mode Support */
    [data-bs-theme="dark"] .ts-control {
        background-color: #212529 !important;
        border-color: #495057 !important;
        color: #fff !important;
    }
    [data-bs-theme="dark"] .ts-dropdown {
        background-color: #343a40 !important;
        border-color: #495057 !important;
        color: #fff !important;
    }
    [data-bs-theme="dark"] .ts-dropdown .option {
        color: #fff !important;
    }
    [data-bs-theme="dark"] .ts-dropdown .option:hover,
    [data-bs-theme="dark"] .ts-dropdown .active {
        background-color: #0d6efd !important;
        color: #fff !important;
    }
    [data-bs-theme="dark"] .ts-control .item {
        color: #fff !important;
    }
    [data-bs-theme="dark"] .ts-wrapper.single .ts-control:after {
        border-color: #fff transparent transparent transparent !important;
    }
    /* Input Text Colors in Dark Mode */
    [data-bs-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-select {
        color: #e9ecef !important;
        background-color: #2b3035;
        border-color: #495057;
    }
    [data-bs-theme="dark"] .form-control::placeholder {
        color: #adb5bd;
    }
    [data-bs-theme="dark"] .ts-control input {
        color: #e9ecef !important;
    }
</style>
<div class="py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="mb-0"><i class="bi bi-folder2-open"></i> รายละเอียดโครงการวิจัย (Project Details)</h2>
                <div class="d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-primary d-print-none">
                        <i class="bi bi-printer"></i> พิมพ์
                    </button>
                    <a href="{{ route('backend.rdb_project.index') }}" class="btn btn-secondary d-print-none">
                        <i class="bi bi-arrow-left"></i> ย้อนกลับ
                    </a>
                    <a href="{{ route('backend.rdb_project.edit', $project->pro_id) }}" class="btn btn-warning d-print-none">
                        <i class="bi bi-pencil"></i> แก้ไขข้อมูล
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8 order-1 order-lg-1">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-print-none">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> ข้อมูลทั่วไป (General Information)</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-primary fw-bold mb-3">{!! $project->pro_nameTH !!}</h4>
                    @if($project->pro_nameEN)
                        <h5 class="text-muted mb-4">{!! $project->pro_nameEN !!}</h5>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-12">
                            @if($project->pro_code)
                                <span class="badge bg-secondary me-2">รหัส: {{ $project->pro_code }}</span>
                            @endif
                            @if($project->year)
                                <span class="badge bg-info text-dark me-2">ปีงบประมาณ: {{ $project->year->year_name }}</span>
                            @endif
                            @if($project->status)
                                <span class="badge bg-primary">สถานะ: {{ $project->status->ps_name }}</span>
                            @endif
                        </div>

                        <div class="col-md-12 mt-4">
                            <h6 class="fw-bold border-bottom pb-2">รายละเอียดเพิ่มเติม</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th style="width: 30%;">ประเภททุน:</th>
                                    <td>
                                        {{ $project->type->pt_name ?? '-' }}
                                        @if($project->typeSub)
                                            <small class="text-muted">({{ $project->typeSub->pts_name }})</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>งบประมาณ:</th>
                                    <td class="text-success fw-bold">{{ number_format($project->pro_budget, 2) }} บาท</td>
                                </tr>
                                <tr>
                                    <th>ระยะเวลา:</th>
                                    <td>
                                        {{ \App\Helpers\ThaiDateHelper::format($project->pro_date_start) }}
                                        ถึง
                                        {{ \App\Helpers\ThaiDateHelper::format($project->pro_date_end) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>หน่วยงาน:</th>
                                    <td>{{ $project->department->department_nameTH ?? '-' }}</td>
                                </tr>
                                @if($project->strategic)
                                <tr>
                                    <th>ยุทธศาสตร์:</th>
                                    <td>{{ $project->strategic->strategic_name ?? '-' }}</td>
                                </tr>
                                @endif
                                @if($project->pro_keyword)
                                <tr>
                                    <th>คำสำคัญ:</th>
                                    <td>{{ $project->pro_keyword }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>จำนวนการเข้าดู:</th>
                                    <td><span class="badge bg-secondary">{{ number_format($project->pro_count_page ?? 0) }}</span> ครั้ง</td>
                                </tr>
                            </table>
                        </div>

                        @if($project->pro_abstract)
                        <div class="col-md-12 mt-3">
                            <h6 class="fw-bold border-bottom pb-2">บทคัดย่อ (Abstract)</h6>
                            @php
                                $separator = '<br><br><br><br>';
                                if (!str_contains($project->pro_abstract, $separator) && str_contains($project->pro_abstract, '<br><br><br>')) {
                                    $separator = '<br><br><br>';
                                }
                                $abstractParts = explode($separator, $project->pro_abstract);
                            @endphp

                            <style>
                                [data-bs-theme="dark"] .bg-abstract {
                                    background-color: #2b3035 !important;
                                    color: #dee2e6;
                                }
                                [data-bs-theme="light"] .bg-abstract {
                                    background-color: #f8f9fa !important;
                                    color: #212529;
                                }
                                @media print {
                                    .bg-abstract {
                                        border: 1px solid #dee2e6 !important;
                                        background-color: #fff !important;
                                        color: #000 !important;
                                    }
                                    .d-print-none { display: none !important; }
                                    .card { border: none !important; shadow: none !important; }
                                }
                            </style>

                            @if(!empty($abstractParts[0]))
                            <div class="mb-3">
                                <span class="badge bg-primary mb-2 d-print-none">บทคัดย่อภาษาไทย</span>
                                <strong class="d-none d-print-block">บทคัดย่อภาษาไทย</strong>
                                <div class="bg-abstract p-3 rounded border" style="min-height: 100px; height: auto; overflow: visible; overflow-wrap: break-word; word-wrap: break-word;">
                                    {!! $abstractParts[0] !!}
                                </div>
                            </div>
                            @endif

                            @if(!empty($abstractParts[1]))
                            <div>
                                <span class="badge bg-info text-dark mb-2 d-print-none">บทคัดย่อภาษาอังกฤษ</span>
                                <strong class="d-none d-print-block">บทคัดย่อภาษาอังกฤษ</strong>
                                <div class="bg-abstract p-3 rounded border" style="min-height: 100px; height: auto; overflow: visible; overflow-wrap: break-word; word-wrap: break-word;">
                                    {!! $abstractParts[1] !!}
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Files (Mobile Only - shows on < lg) - Placed before Researchers on mobile -->
            <div class="d-lg-none d-print-none">
                @include('backend.rdb_project._files_card')
            </div>

            <!-- นักวิจัย -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center d-print-none">
                    <h5 class="mb-0"><i class="bi bi-people"></i> คณะผู้วิจัย (Researchers)</h5>
                    <button type="button" class="btn btn-light btn-sm text-success fw-bold" data-bs-toggle="modal" data-bs-target="#researcherModal">
                        <i class="bi bi-gear-fill"></i> จัดการข้อมูล (Manage)
                    </button>
                </div>
                <div class="card-header bg-success text-white d-none d-print-block">
                     <h5 class="mb-0">คณะผู้วิจัย (Researchers)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>ตำแหน่งในโครงการ</th>
                                    <th>สัดส่วน (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->rdbProjectWorks as $work)
                                <tr>
                                    <td>
                                        @if($work->researcher)
                                            {{ $work->researcher->prefix->prefix_nameTH ?? '' }}{{ $work->researcher->researcher_fname }} {{ $work->researcher->researcher_lname }}
                                            <small class="text-muted d-block">
                                                {{ $work->researcher->department->department_nameTH ?? $work->researcher->department->department_nameEN ?? $work->researcher->researcher_note ?? '-' }}
                                            </small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        {{ $work->position->position_nameTH ?? '-' }}
                                    </td>
                                    <td>{{ $work->ratio }}%</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูลนักวิจัย</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="2" class="text-end">รวมสัดส่วน (Total Ratio):</td>
                                    <td>{{ $project->rdbProjectWorks->sum('ratio') }}%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>


            <!-- Additional Files (File Project) -->
            <div class="card shadow-sm mb-4 d-print-none">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-paperclip"></i> ข้อมูลเอกสารเพิ่มเติม (Additional Documents)</h5>
                    <button type="button" class="btn btn-light btn-sm text-success fw-bold" data-bs-toggle="modal" data-bs-target="#fileModal">
                        <i class="bi bi-gear-fill"></i> จัดการข้อมูล (Manage)
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ชื่อเอกสาร</th>
                                    <th>หมายเหตุ</th>
                                    <th>ดาวน์โหลด</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->files as $file)
                                <tr>
                                    <td>
                                        {{ $file->rf_filesname }}
                                        <span class="badge bg-secondary ms-1" title="จำนวนดาวน์โหลด">
                                            <i class="bi bi-download"></i> {{ $file->rf_download ?? 0 }}
                                        </span>
                                    </td>
                                    <td>{{ $file->rf_note ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('backend.rdb_project.file.download', [$project->pro_id, $file->id]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">ไม่มีเอกสารเพิ่มเติม</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Bottom Action Buttons -->
            <div class="d-flex justify-content-end gap-2 mb-4 d-print-none">
                <button onclick="window.print()" class="btn btn-outline-dark">
                    <i class="bi bi-printer"></i> พิมพ์
                </button>
                 <a href="{{ route('backend.rdb_project.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
                <a href="{{ route('backend.rdb_project.edit', $project->pro_id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> แก้ไขข้อมูล
                </a>
            </div>
        </div>

        <div class="col-lg-4 order-2 order-lg-2">
            <!-- Files (Desktop Only - shows on >= lg) -->
            <div class="d-none d-lg-block d-print-none">
                @include('backend.rdb_project._files_card')
            </div>

            <!-- Project Files Modal -->
            <div class="modal fade" id="projectFilesModal" tabindex="-1" aria-labelledby="projectFilesModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-secondary text-white">
                            <h5 class="modal-title" id="projectFilesModalLabel"><i class="bi bi-file-earmark-arrow-up"></i> จัดการไฟล์เอกสารโครงการ (Manage Project Files)</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Abstract File -->
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-file-earmark-text"></i> ไฟล์บทคัดย่อ (Abstract File)</span>
                                    @if($project->pro_abstract_file)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> มีไฟล์แล้ว</span>
                                    @else
                                        <span class="badge bg-secondary">ยังไม่มีไฟล์</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if($project->pro_abstract_file)
                                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background: rgba(0,0,0,0.05);">
                                            <div class="text-truncate me-2" style="max-width: 70%;">
                                                <i class="bi bi-file-earmark-pdf text-danger fs-4 me-2"></i>
                                                <small class="text-muted">{{ $project->pro_abstract_file }}</small>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <a href="{{ asset('storage/uploads/projects/' . $project->pro_abstract_file) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 32px;">
                                                    <i class="bi bi-eye me-1"></i> ดู
                                                </a>
                                                <form action="{{ route('backend.rdb_project.delete_abstract', $project->pro_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันลบไฟล์บทคัดย่อ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 32px;">
                                                        <i class="bi bi-trash me-1"></i> ลบ
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                    <form action="{{ route('backend.rdb_project.upload_abstract', $project->pro_id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="input-group">
                                            <input type="file" name="pro_abstract_file" class="form-control form-control-sm" accept=".pdf" required>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="bi bi-upload"></i> {{ $project->pro_abstract_file ? 'เปลี่ยนไฟล์' : 'อัปโหลด' }}
                                            </button>
                                        </div>
                                        <small class="text-muted">รองรับเฉพาะไฟล์ PDF ขนาดไม่เกิน 20MB</small>
                                    </form>
                                </div>
                            </div>

                            <!-- Full Report File -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-file-earmark-richtext"></i> ไฟล์รายงานฉบับสมบูรณ์ (Full Report)</span>
                                    @if($project->pro_file)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> มีไฟล์แล้ว</span>
                                    @else
                                        <span class="badge bg-secondary">ยังไม่มีไฟล์</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if($project->pro_file)
                                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background: rgba(0,0,0,0.05);">
                                            <div class="text-truncate me-2" style="max-width: 70%;">
                                                <i class="bi bi-file-earmark-pdf text-danger fs-4 me-2"></i>
                                                <small class="text-muted">{{ $project->pro_file }}</small>
                                            </div>
                                            <div class="d-flex gap-1 align-items-center">
                                                <div class="form-check form-switch m-0 me-2 d-flex align-items-center" title="แสดงผลไฟล์นี้">
                                                    <input class="form-check-input toggle-report-status" type="checkbox" data-id="{{ $project->pro_id }}" role="button" {{ $project->pro_file_show ? 'checked' : '' }}>
                                                </div>
                                                <a href="{{ asset('storage/uploads/projects/' . $project->pro_file) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 32px;">
                                                    <i class="bi bi-eye me-1"></i> ดู
                                                </a>
                                                <form action="{{ route('backend.rdb_project.delete_report', $project->pro_id) }}" method="POST" class="d-inline-flex m-0" onsubmit="return confirm('ยืนยันลบไฟล์รายงานฉบับสมบูรณ์?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 32px;">
                                                        <i class="bi bi-trash me-1"></i> ลบ
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                    <form action="{{ route('backend.rdb_project.upload_report', $project->pro_id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="input-group">
                                            <input type="file" name="pro_file" class="form-control form-control-sm" accept=".pdf" required>
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="bi bi-upload"></i> {{ $project->pro_file ? 'เปลี่ยนไฟล์' : 'อัปโหลด' }}
                                            </button>
                                        </div>
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="pro_file_show" value="1" id="proFileShow" checked>
                                            <label class="form-check-label small text-muted" for="proFileShow">แสดงผลไฟล์ (Visible)</label>
                                        </div>
                                        <small class="text-muted">รองรับเฉพาะไฟล์ PDF ขนาดไม่เกิน 20MB</small>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ ปิด</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadata -->
            <div class="card shadow-sm d-print-none">
                <div class="card-header bg-info text-dark">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> ประวัติการแก้ไข (History)</h5>
                </div>
                <div class="card-body small">
                    @php
                        function getUserName($user) {
                            if(!$user) return '-';
                            if($user->researcher) {
                                $r = $user->researcher;
                                return $r->researcher_fname . ' ' . $r->researcher_lname;
                            }
                            return $user->username ?? $user->email ?? '-';
                        }
                    @endphp
                    <p class="mb-2"><strong>สร้างเมื่อ:</strong> {{ \App\Helpers\ThaiDateHelper::formatDateTime($project->created_at) }} โดย {{ getUserName($project->createdBy) }}</p>
                    <p class="mb-0"><strong>แก้ไขล่าสุด:</strong> {{ \App\Helpers\ThaiDateHelper::formatDateTime($project->updated_at) }} โดย {{ getUserName($project->updatedBy) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Modal Manage Researchers -->
<div class="modal fade" id="researcherModal" tabindex="-1" aria-labelledby="researcherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="researcherModalLabel"><i class="bi bi-people-fill"></i> จัดการนักวิจัยในโครงการ (Manage Researchers)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add/Edit Form -->
                <div class="card mb-3 border">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3" id="formTitle"><i class="bi bi-person-plus"></i> เพิ่มนักวิจัยใหม่ (Add New)</h6>
                        <form id="researcherForm" method="POST" action="{{ route('backend.rdb_project.researcher.store', $project->pro_id) }}">
                            @csrf
                            <input type="hidden" name="_method" id="formMethod" value="POST">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small">นักวิจัย (Researcher)</label>
                                    <select name="researcher_id" id="researcherSelect" class="form-select form-select-sm" required>
                                        <option value="">-- พิมพ์เพื่อค้นหานักวิจัย --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">ตำแหน่ง (Position)</label>
                                    <select name="position_id" id="positionSelect" class="form-select form-select-sm" required>
                                        <option value="">-- เลือก --</option>
                                        @foreach($positions as $p)
                                            <option value="{{ $p->position_id }}">{{ $p->position_nameTH }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">สัดส่วน (%)</label>
                                    @php
                                        $remainingRatio = max(0, 100 - $project->rdbProjectWorks->sum('ratio'));
                                    @endphp
                                    <input type="number" name="ratio" id="ratioInput" class="form-control form-control-sm" min="0" max="100" value="{{ $remainingRatio }}" required>
                                </div>
                                <div class="col-md-3 d-flex align-items-end gap-1 flex-wrap">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill" id="submitBtn">✔️ บันทึก</button>
                                    <button type="button" class="btn btn-secondary btn-sm d-none flex-fill" id="cancelEditBtn">❌ ยกเลิก</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ชื่อ-นามสกุล</th>
                                <th>ตำแหน่ง</th>
                                <th>สัดส่วน</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($project->rdbProjectWorks as $work)
                                <tr>
                                    <td>
                                        @if($work->researcher)
                                            {{ $work->researcher->prefix->prefix_nameTH ?? '' }}{{ $work->researcher->researcher_fname }} {{ $work->researcher->researcher_lname }}
                                            <small class="text-muted d-block">
                                                {{ $work->researcher->department->department_nameTH ?? $work->researcher->department->department_nameEN ?? $work->researcher->researcher_note ?? '-' }}
                                            </small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($work->position)
                                            {{ $work->position->position_nameTH }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $work->ratio }}%</td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-warning btn-sm edit-btn"
                                            data-rid="{{ $work->researcher_id }}"
                                            data-pid="{{ $work->position_id }}"
                                            data-ratio="{{ $work->ratio }}"
                                            data-name="{{ ($work->researcher->prefix->prefix_nameTH ?? '') . $work->researcher->researcher_fname . ' ' . $work->researcher->researcher_lname }}"
                                            data-action="{{ route('backend.rdb_project.researcher.update', [$project->pro_id, $work->researcher_id]) }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('backend.rdb_project.researcher.destroy', [$project->pro_id, $work->researcher_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันลบ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">ไม่มีข้อมูล</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">รวมสัดส่วน (Total Ratio):</td>
                                <td>{{ $project->rdbProjectWorks->sum('ratio') }}%</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Modal Manage Files -->
<div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileModalLabel"><i class="bi bi-folder-fill"></i> จัดการข้อมูลเอกสารเพิ่มเติม (Manage Files)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add/Edit File Form -->
                <div class="card mb-3 border">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3" id="fileFormTitle"><i class="bi bi-file-earmark-plus"></i> เพิ่มเอกสารใหม่ (Add New File)</h6>
                        <form id="fileForm" method="POST" action="{{ route('backend.rdb_project.file.store', $project->pro_id) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="_method" id="fileFormMethod" value="POST">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small">ชื่อเอกสาร (File Name)</label>
                                    <input type="text" name="rf_filesname" id="fileNameInput" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">ไฟล์ (PDF Only)</label>
                                    <input type="file" name="rf_files" id="fileInput" class="form-control form-control-sm" accept=".pdf" required>
                                    <small class="text-muted d-none" id="currentFileDisplay"></small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">หมายเหตุ (Note)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="rf_note" id="fileNoteInput" class="form-control">
                                        <button type="submit" class="btn btn-primary" id="fileSubmitBtn"><i class="bi bi-plus-circle"></i> เพิ่ม</button>
                                        <button type="button" class="btn btn-secondary d-none" id="fileCancelBtn">ยกเลิก</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2 mt-0">
                                <div class="col-12">
                                     <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="rf_files_show" value="1" id="rfFilesShow" checked>
                                        <label class="form-check-label small" for="rfFilesShow">แสดงผลไฟล์ให้คนทั่วไปเห็น (Public Visible)</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- File List Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ชื่อเอกสาร</th>
                                <th>หมายเหตุ</th>
                                <th class="text-center" style="width: 100px;">สถานะ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($project->files as $file)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $file->rf_filesname }}</div>
                                        <small class="text-muted d-block">
                                            @php
                                                $fname = $file->rf_files;
                                                // Check if name is long enough to truncate
                                                if(strlen($fname) > 40) {
                                                    $ext = pathinfo($fname, PATHINFO_EXTENSION);
                                                    $nameOnly = pathinfo($fname, PATHINFO_FILENAME);
                                                    // Display: Start(30)...End(5).ext
                                                    $shortName = substr($nameOnly, 0, 30) . '...' . substr($nameOnly, -5) . '.' . $ext;
                                                } else {
                                                    $shortName = $fname;
                                                }
                                            @endphp
                                            <a href="{{ asset('storage/uploads/project_files/' . $file->rf_files) }}" target="_blank" class="text-reset text-decoration-none">
                                                <i class="bi bi-paperclip"></i> {{ $shortName }}
                                            </a>
                                        </small>
                                    </td>
                                    <td>{{ $file->rf_note ?? '-' }}</td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input toggle-file-status" type="checkbox" data-pid="{{ $project->pro_id }}" data-fid="{{ $file->id }}" role="button" {{ $file->rf_files_show ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-warning btn-sm edit-file-btn"
                                            data-fid="{{ $file->id }}"
                                            data-name="{{ $file->rf_filesname }}"
                                            data-note="{{ $file->rf_note }}"
                                            data-filename="{{ $file->rf_files }}"
                                            data-action="{{ route('backend.rdb_project.file.update', [$project->pro_id, $file->id]) }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('backend.rdb_project.file.destroy', [$project->pro_id, $file->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันลบไฟล์นี้?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">ไม่มีข้อมูล</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Researcher Management Logic ---
        const form = document.getElementById('researcherForm');
        const formMethod = document.getElementById('formMethod');
        const submitBtn = document.getElementById('submitBtn');
        const cancelBtn = document.getElementById('cancelEditBtn');
        const formTitle = document.getElementById('formTitle');
        const researcherSelect = document.getElementById('researcherSelect');
        const positionSelect = document.getElementById('positionSelect');
        const ratioInput = document.getElementById('ratioInput');
        const defaultAction = "{{ route('backend.rdb_project.researcher.store', $project->pro_id) }}";

        // JSON Data
        const existingResearcherIds = @json($existingResearcherIds);
        const takenPositions = @json($takenPositions);
        const defaultPositionId = {{ !$hasHead ? 2 : 4 }}; // 2=Head, 4=Co-researcher

        // Flag to prevent existing check during programmatic update
        let isProgrammatic = false;

        // Initialize TomSelect with AJAX loading
        let tomSelectControl;
        if(researcherSelect) {
            tomSelectControl = new TomSelect("#researcherSelect",{
                create: false,
                openOnFocus: false,
                maxOptions: 10,
                valueField: 'value',
                labelField: 'text',
                searchField: 'text',
                placeholder: "-- พิมพ์เพื่อค้นหานักวิจัย --",
                loadThrottle: 300,
                load: function(query, callback) {
                    if(!query.length || query.length < 2) return callback();
                    
                    fetch('{{ route("backend.rdb_project.search_researchers") }}?q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(json => {
                            // Filter out existing researchers
                            const filtered = json.filter(item => !existingResearcherIds.includes(parseInt(item.value)));
                            callback(filtered);
                        })
                        .catch(() => callback());
                },
                render: {
                    option: function(data, escape) {
                        return '<div>' + escape(data.text) + '</div>';
                    },
                    item: function(data, escape) {
                        return '<div>' + escape(data.text) + '</div>';
                    },
                    no_results: function(data, escape) {
                        return '<div class="no-results p-2 text-muted">ไม่พบข้อมูล (พิมพ์อย่างน้อย 2 ตัวอักษร)</div>';
                    }
                }
            });

            // Force close if search is empty
            tomSelectControl.on('type', function(str) {
                if(str.trim() === "") {
                    this.close();
                }
            });

            // Prevent selection of disabled items
             tomSelectControl.on('item_add', function(value, item) {
                if(isProgrammatic) return; // Skip check if setting programmatically

                if(existingResearcherIds.includes(parseInt(value))) {
                    tomSelectControl.removeItem(value, true); // Silent remove
                    alert('นักวิจัยนี้มีอยู่ในโครงการแล้ว (Researcher already exists)');
                }
            });
        }
        
        // Initial setup for Add Mode
        Array.from(positionSelect.options).forEach(opt => opt.disabled = false);
        takenPositions.forEach(posId => {
            if(posId == 1 || posId == 2) {
                const opt = positionSelect.querySelector(`option[value="${posId}"]`);
                if(opt) opt.disabled = true;
            }
        });
        positionSelect.value = defaultPositionId;

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const rid = this.dataset.rid;
                const pid = this.dataset.pid;
                const ratio = this.dataset.ratio;
                const action = this.dataset.action;

                // Set Form to Edit Mode
                form.action = action;
                formMethod.value = "PUT";
                
                // For TomSelect, we need to add the option first since it's remote-loaded
                if(tomSelectControl) {
                    isProgrammatic = true; // Enable flag
                    // Get the researcher name from data attribute
                    const researcherName = this.dataset.name || 'นักวิจัย';
                    // Add the option if it doesn't exist
                    tomSelectControl.addOption({value: rid, text: researcherName});
                    tomSelectControl.setValue(rid);
                    isProgrammatic = false; // Disable flag
                    tomSelectControl.disable(); 
                } else {
                     researcherSelect.value = rid;
                     researcherSelect.disabled = true; 
                }

                // Handle Position Logic for Edit
                // Enable all first
                Array.from(positionSelect.options).forEach(opt => opt.disabled = false);
                // Disable occupied unique positions IF they are NOT the current one
                // Unique Roles: 1=Director, 2=Head
                takenPositions.forEach(posId => {
                    if((posId == 1 || posId == 2) && posId != pid) {
                        const opt = positionSelect.querySelector(`option[value="${posId}"]`);
                        if(opt) opt.disabled = true;
                    }
                });
                
                // Add a hidden input for researcher_id since disabled inputs aren't submitted
                let hiddenInput = document.getElementById('hidden_researcher_id');
                if(!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'researcher_id';
                    hiddenInput.id = 'hidden_researcher_id';
                    form.appendChild(hiddenInput);
                }
                hiddenInput.value = rid;

                positionSelect.value = pid;
                ratioInput.value = ratio;

                submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> บันทึก';
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-warning');
                
                cancelBtn.classList.remove('d-none');
                formTitle.innerHTML = '<i class="bi bi-pencil-square"></i> แก้ไขข้อมูล (Edit)';
            });
        });

        cancelBtn.addEventListener('click', function() {
            // Reset to Add Mode
            form.action = defaultAction;
            formMethod.value = "POST";
            form.reset();
            // Restore calculated remaining ratio
            ratioInput.value = "{{ $remainingRatio }}";
            
            if(tomSelectControl) {
                tomSelectControl.enable();
                tomSelectControl.clear();
            } else {
                researcherSelect.disabled = false;
            }

            // Reset Position Logic for Add
            Array.from(positionSelect.options).forEach(opt => opt.disabled = false);
            takenPositions.forEach(posId => {
                if(posId == 1 || posId == 2) {
                    const opt = positionSelect.querySelector(`option[value="${posId}"]`);
                    if(opt) opt.disabled = true;
                }
            });
            positionSelect.value = defaultPositionId;

            if(document.getElementById('hidden_researcher_id')) {
                document.getElementById('hidden_researcher_id').remove();
            }

            submitBtn.innerHTML = '<i class="bi bi-plus-circle"></i> เพิ่ม';
            submitBtn.classList.add('btn-primary');
            submitBtn.classList.remove('btn-warning');
            
            cancelBtn.classList.add('d-none');
            formTitle.innerHTML = '<i class="bi bi-person-plus"></i> เพิ่มนักวิจัยใหม่ (Add New)';
            
            // Re-enable TomSelect if it exists
            if(tomSelectControl) {
                tomSelectControl.enable();
                tomSelectControl.clear();
            }
        });


        // --- File Management Logic ---
        const fileForm = document.getElementById('fileForm');
        const fileFormMethod = document.getElementById('fileFormMethod');
        const fileSubmitBtn = document.getElementById('fileSubmitBtn');
        const fileCancelBtn = document.getElementById('fileCancelBtn');
        const fileFormTitle = document.getElementById('fileFormTitle');
        const fileNameInput = document.getElementById('fileNameInput');
        const fileNoteInput = document.getElementById('fileNoteInput');
        const fileInput = document.getElementById('fileInput');
        const currentFileDisplay = document.getElementById('currentFileDisplay');
        const defaultFileAction = "{{ route('backend.rdb_project.file.store', $project->pro_id) }}";

        document.querySelectorAll('.edit-file-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const fid = this.dataset.fid;
                const name = this.dataset.name;
                const note = this.dataset.note;
                const filename = this.dataset.filename;
                const action = this.dataset.action;

                // Set Form to Edit Mode
                fileForm.action = action;
                fileFormMethod.value = "PUT";
                
                fileNameInput.value = name;
                fileNoteInput.value = note || '';
                
                // Show current filename
                currentFileDisplay.classList.remove('d-none');
                currentFileDisplay.innerHTML = `<i class="bi bi-paperclip"></i> ไฟล์ปัจจุบัน: ${filename} (เลือกใหม่เพื่อเปลี่ยน)`;
                fileInput.required = false; // Not required on edit

                fileSubmitBtn.innerHTML = '<i class="bi bi-check-circle"></i> บันทึก';
                fileSubmitBtn.classList.remove('btn-primary');
                fileSubmitBtn.classList.add('btn-warning');
                
                fileCancelBtn.classList.remove('d-none');
                fileFormTitle.innerHTML = '<i class="bi bi-pencil-square"></i> แก้ไขเอกสาร (Edit File)';
            });
        });

        fileCancelBtn.addEventListener('click', function() {
            // Reset to Add Mode
            fileForm.action = defaultFileAction;
            fileFormMethod.value = "POST";
            fileForm.reset();
            
            // Hide current filename
            currentFileDisplay.classList.add('d-none');
            currentFileDisplay.innerHTML = '';
            fileInput.required = true; // Required on add

            fileSubmitBtn.innerHTML = '<i class="bi bi-plus-circle"></i> เพิ่ม';
            fileSubmitBtn.classList.add('btn-primary');
            fileSubmitBtn.classList.remove('btn-warning');
            
            fileCancelBtn.classList.add('d-none');
            fileFormTitle.innerHTML = '<i class="bi bi-file-earmark-plus"></i> เพิ่มเอกสารใหม่ (Add New File)';
        });


        // --- File Visibility Toggles ---
        // Toggle Report
        document.querySelector('.toggle-report-status')?.addEventListener('change', function() {
            const id = this.dataset.id;
            const checked = this.checked;
            
            fetch(`{{ url('backend/rdb_project') }}/${id}/toggle-report-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    console.log('Report status toggled:', data.status);
                } else {
                    alert('เกิดข้อผิดพลาดในการเปลี่ยนสถานะ');
                    this.checked = !checked;
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                this.checked = !checked;
            });
        });

        // Toggle Additional Files
        document.querySelectorAll('.toggle-file-status').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const pid = this.dataset.pid;
                const fid = this.dataset.fid;
                const checked = this.checked;

                fetch(`{{ url('backend/rdb_project') }}/${pid}/file/${fid}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        console.log('File status toggled:', data.status);
                    } else {
                        alert('เกิดข้อผิดพลาดในการเปลี่ยนสถานะ');
                        this.checked = !checked;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                    this.checked = !checked;
                });
            });
        });
    });
</script>
<!-- TomSelect JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
