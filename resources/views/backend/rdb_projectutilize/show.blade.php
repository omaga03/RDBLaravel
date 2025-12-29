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
                <h2 class="mb-0 text-gray-800"><i class="bi bi-rocket-takeoff"></i> รายละเอียดการใช้ประโยชน์ (Utilization Details)</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('backend.rdbprojectutilize.index') }}" class="btn btn-secondary d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                    </a>
                    <a href="{{ route('backend.rdbprojectutilize.edit', $item->utz_id) }}" class="btn btn-warning d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-pencil me-2"></i> แก้ไขข้อมูล
                    </a>
                    <button onclick="window.print()" class="btn btn-primary d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-printer me-2"></i> พิมพ์
                    </button>
                    <form action="{{ route('backend.rdbprojectutilize.destroy', $item->utz_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบข้อมูลนี้?');">
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
            <!-- General Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-print-none border-0">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> ข้อมูลทั่วไป (General Information)</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-primary fw-bold mb-3">{{ html_entity_decode($item->utz_department_name) }}</h4>
                    
                    @if($item->utz_group)
                        <div class="mb-4">
                            @php
                                $groupIds = array_map('trim', explode(',', $item->utz_group));
                                $types = \App\Models\RdbProjectUtilizeType::whereIn('utz_type_id', $groupIds)->get();
                            @endphp
                            @foreach($types as $type)
                                <span class="badge bg-info text-dark me-1 border shadow-sm">{{ $type->utz_typr_name }}</span>
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
                                    @if($item->utz_year_bud) <span class="badge bg-secondary">งบประมาณ: {{ $item->utz_year_bud }}</span> @endif
                                    @if($item->utz_year_edu) <span class="badge bg-dark">การศึกษา: {{ $item->utz_year_edu }}</span> @endif
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
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white border-0">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> รายละเอียดการดำเนินงาน (Utilization Details)</h5>
                </div>
                <div class="card-body">
                    <div class="bg-light-styled p-3 rounded border">
                        {!! $item->utz_detail !!}
                    </div>
                </div>
            </div>
            @endif

            <!-- Note Card -->
            @if($item->utz_note)
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-warning text-dark border-0">
                    <h5 class="mb-0"><i class="bi bi-sticky"></i> หมายเหตุ (Note)</h5>
                </div>
                <div class="card-body">
                    <div class="text-muted small">
                        {!! $item->utz_note !!}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- File Attachment -->
            <div class="card shadow-sm mb-4 d-print-none border">
                <div class="card-header bg-warning text-dark border-0">
                    <h5 class="mb-0"><i class="bi bi-paperclip"></i> ไฟล์แนบ (File Attachment)</h5>
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
                                    <i class="bi bi-eye"></i> {{ $displayName }}
                                </a>
                            @endif
                        @endforeach
                        </div>
                        @if($item->utz_countfile)
                            <p class="text-muted small mb-0"><i class="bi bi-download"></i> ดาวน์โหลดรวม {{ number_format($item->utz_countfile) }} ครั้ง</p>
                        @endif
                    @else
                        <div class="text-muted py-3">
                             <i class="bi bi-file-earmark-x" style="font-size: 2rem;"></i><br>
                             ไม่มีไฟล์แนบ
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Project -->
            @if($item->project)
            <div class="card shadow-sm mb-4 border">
                <div class="card-header bg-success text-white border-0">
                    <h5 class="mb-0"><i class="bi bi-folder-symlink"></i> โครงการวิจัยต้นแบบ</h5>
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
                <div class="card-header bg-info text-white border-0">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> พิกัดสถานที่</h5>
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
            <div class="card shadow-sm d-print-none border">
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
        <a href="{{ route('backend.rdbprojectutilize.index') }}" class="btn btn-secondary d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
            <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
        </a>
        <a href="{{ route('backend.rdbprojectutilize.edit', $item->utz_id) }}" class="btn btn-warning d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
            <i class="bi bi-pencil me-2"></i> แก้ไขข้อมูล
        </a>
        <button onclick="window.print()" class="btn btn-primary d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
            <i class="bi bi-printer me-2"></i> พิมพ์
        </button>
        <form action="{{ route('backend.rdbprojectutilize.destroy', $item->utz_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันคุณจะลบรายการนี้?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                <i class="bi bi-trash me-2"></i> ลบ
            </button>
        </form>
    </div>
</div>
@endsection
