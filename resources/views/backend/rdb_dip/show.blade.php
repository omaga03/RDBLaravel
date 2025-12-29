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
    
    /* Dark Mode Support */
    [data-bs-theme="dark"] .text-gray-800 { color: #e9ecef !important; }
    [data-bs-theme="dark"] .bg-light-styled { background-color: #2b3035 !important; color: #dee2e6; }
    [data-bs-theme="light"] .bg-light-styled { background-color: #f8f9fa !important; color: #212529; }
</style>

<div class="py-4">
    <div class="row">
        <!-- Header & Action Buttons -->
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="mb-0 text-gray-800"><i class="bi bi-patch-check"></i> รายละเอียดทรัพย์สินทางปัญญา (IP Details)</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('backend.rdb_dip.index') }}" class="btn btn-secondary d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                    </a>
                    <a href="{{ route('backend.rdb_dip.edit', $item->dip_id) }}" class="btn btn-warning d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-pencil me-2"></i> แก้ไขข้อมูล
                    </a>
                    <button onclick="window.print()" class="btn btn-primary d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-printer me-2"></i> พิมพ์
                    </button>
                    <form action="{{ route('backend.rdb_dip.destroy', $item->dip_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบข้อมูลนี้?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                            <i class="bi bi-trash me-2"></i> ลบ
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- General Information -->
            <div class="card shadow-sm mb-4 border">
                <div class="card-header bg-primary text-white d-print-none border-0">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> ข้อมูลทั่วไป (General Information)</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-primary fw-bold mb-3">{{ $item->dip_data2_name ?? '-' }}</h4>
                    @if($item->dip_nameEN)
                        <h5 class="text-muted mb-4 fs-6">{{ $item->dip_nameEN }}</h5>
                    @endif

                    <h6 class="fw-bold border-bottom pb-2 mb-3">รายละเอียดเพิ่มเติม</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless">
                            @if($item->dip_request_date)
                            <tr>
                                <th style="width: 30%;">ปีที่ขอรับ:</th>
                                <td><span class="badge bg-secondary">{{ $item->dip_request_date->year + 543 }}</span></td>
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
                                    <a href="{{ route('backend.rdb_researcher.show', $item->researcher_id) }}" target="_blank" class="fw-bold text-decoration-none">
                                        {{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                                    </a>
                                    @if($item->researcher->department)
                                        <span class="text-muted ms-1">({{ $item->researcher->department->department_nameTH }})</span>
                                    @endif
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
                                <td><span class="badge bg-success">{{ $item->dip_data2_agent }}</span></td>
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
                        <h6 class="fw-bold border-bottom pb-2">บทคัดย่อ / สรุปผล (Abstract / Conclusion)</h6>
                        <div class="bg-light-styled p-3 rounded border">
                            @if($item->dip_note)
                                <div class="mb-2"><strong>บันทึก:</strong> {!! nl2br(e($item->dip_note)) !!}</div>
                            @endif
                            @if($item->dip_data2_conclusion)
                                <div class="mb-2"><strong>สรุปผล:</strong> {!! nl2br(e($item->dip_data2_conclusion)) !!}</div>
                            @endif
                            @if($item->dip_data2_assertion)
                                <div><strong>ข้อถือสิทธิ:</strong> {!! nl2br(e($item->dip_data2_assertion)) !!}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- File Attachment -->
            <div class="card shadow-sm mb-4 d-print-none border">
                <div class="card-header bg-warning text-dark border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-paperclip"></i> ไฟล์แนบ</h5>
                    <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#fileModal">
                        <i class="bi bi-gear"></i> จัดการ
                    </button>
                </div>
                <div class="card-body text-center p-4 border-top">
                    <div class="mb-3">
                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <div class="text-muted py-2">
                        <i class="bi bi-file-earmark-check"></i> เอกสารทรัพย์สินทางปัญญาหลัก
                    </div>
                    <div class="d-grid gap-2 mt-3">
                        @if($item->dip_files)
                            <a href="{{ asset('storage/uploads/dips/' . $item->dip_files) }}" target="_blank" class="btn btn-primary d-flex align-items-center justify-content-center">
                                <i class="bi bi-eye me-2"></i> เปิดดูไฟล์
                            </a>
                        @else
                            <button class="btn btn-outline-secondary" disabled>
                                <i class="bi bi-file-earmark-x"></i> ยังไม่มีไฟล์แนบ
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Modal Manage File -->
            <div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content border-primary">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="fileModalLabel"><i class="bi bi-folder-fill"></i> จัดการไฟล์แนบหลัก</h5>
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
                                                <i class="bi bi-trash"></i> ลบไฟล์
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
                                    <div class="form-text mt-2 small text-muted"><i class="bi bi-info-circle"></i> ขนาดไฟล์ที่อนุญาตไม่เกิน 20MB</div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary shadow-sm">
                                        <i class="bi bi-upload"></i> {{ $item->dip_files ? 'อัปโหลดเปลี่ยนไฟล์ใหม่' : 'เริ่มอัปโหลดไฟล์' }}
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

            <!-- Related Project -->
            @if($item->project)
            <div class="card shadow-sm mb-4 border">
                <div class="card-header bg-info text-white border-0">
                    <h5 class="mb-0"><i class="bi bi-folder-symlink"></i> โครงการที่เกี่ยวข้อง</h5>
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
                                    <i class="bi bi-box-arrow-up-right"></i> ดูรายละเอียดโครงการ
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            <!-- Metadata / System Info -->
            <div class="card shadow-sm d-print-none border mb-4">
                <div class="card-header bg-dark text-white border-0">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> ข้อมูลระบบ (System Info)</h5>
                </div>
                <div class="card-body p-3 border-top">
                    @php
                        function getUserName($user) {
                            if(!$user) return '-';
                            if($user->researcher) {
                                return $user->researcher->researcher_fname . ' ' . $user->researcher->researcher_lname;
                            }
                            return $user->username ?? $user->email ?? '-';
                        }
                    @endphp
                    <div class="small">
                        <div class="mb-2">
                            <span class="text-muted">สร้างเมื่อ:</span> 
                            <span class="fw-bold">{{ \App\Helpers\ThaiDateHelper::formatDateTime($item->created_at) }}</span>
                            <br>
                            <span class="text-muted">โดย:</span> {{ getUserName($item->createdBy) }}
                        </div>
                        <hr class="my-2">
                        <div class="mb-0">
                            <span class="text-muted">แก้ไขล่าสุด:</span> 
                            <span class="fw-bold">{{ \App\Helpers\ThaiDateHelper::formatDateTime($item->updated_at) }}</span>
                            <br>
                            <span class="text-muted">โดย:</span> {{ getUserName($item->updatedBy) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Actions -->
    <div class="d-flex justify-content-end flex-wrap gap-2 mt-4 mb-4 d-print-none">
        <a href="{{ route('backend.rdb_dip.index') }}" class="btn btn-secondary d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
            <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
        </a>
        <a href="{{ route('backend.rdb_dip.edit', $item->dip_id) }}" class="btn btn-warning d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
            <i class="bi bi-pencil me-2"></i> แก้ไขข้อมูล
        </a>
        <button onclick="window.print()" class="btn btn-primary d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
            <i class="bi bi-printer me-2"></i> พิมพ์
        </button>
        <form action="{{ route('backend.rdb_dip.destroy', $item->dip_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันคุณจะลบรายการนี้?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                <i class="bi bi-trash me-2"></i> ลบ
            </button>
        </form>
    </div>
</div>
@endsection
