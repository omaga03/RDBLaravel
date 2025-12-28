@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="row">
        <!-- Header -->
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="mb-0"><i class="bi bi-rocket-takeoff"></i> รายละเอียดการใช้ประโยชน์ (Utilization Details)</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('backend.rdbprojectutilize.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                    </a>
                    <a href="{{ route('backend.rdbprojectutilize.edit', $item->utz_id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-2"></i> แก้ไข
                    </a>
                    <button type="button" class="btn btn-info" onclick="window.print();">
                        <i class="bi bi-printer me-2"></i> พิมพ์
                    </button>
                    <button type="button" class="btn btn-danger" onclick="if(confirm('ยืนยันการลบข้อมูล?')) document.getElementById('delete-form').submit();">
                        <i class="bi bi-trash me-2"></i> ลบ
                    </button>
                </div>
                <form id="delete-form" action="{{ route('backend.rdbprojectutilize.destroy', $item->utz_id) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-8 order-1 order-lg-1">
            <!-- General Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> ข้อมูลทั่วไป (General Information)</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-primary fw-bold mb-3">{{ html_entity_decode($item->utz_department_name) }}</h4>
                    
                    @if($item->utz_group)
                        <div class="mb-3">
                            @php
                                $groupIds = array_map('trim', explode(',', $item->utz_group));
                                $types = \App\Models\RdbProjectUtilizeType::whereIn('utz_type_id', $groupIds)->get();
                            @endphp
                            @foreach($types as $type)
                                <span class="badge bg-info text-dark me-1">{{ $type->utz_typr_name }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="col-md-12 mt-4">
                        <h6 class="fw-bold border-bottom pb-2">รายละเอียดเพิ่มเติม</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th style="width: 30%;">วันที่ใช้ประโยชน์:</th>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        📅 {{ \App\Helpers\ThaiDateHelper::format($item->utz_date, false, true) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>ผู้ใช้ประโยชน์:</th>
                                <td>
                                    <strong>{{ $item->utz_leading ?? '-' }}</strong>
                                    @if($item->utz_leading_position)
                                        <br><small class="text-muted">{{ $item->utz_leading_position }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>ที่อยู่:</th>
                                <td>
                                    @if($item->changwat)
                                        📍 ต.{{ preg_replace('/^ต\./', '', $item->changwat->tambon_t ?? '') }} 
                                        อ.{{ preg_replace('/^อ\./', '', $item->changwat->amphoe_t ?? '') }} 
                                        จ.{{ preg_replace('/^จ\./', '', $item->changwat->changwat_t ?? '') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @if($item->utz_budget && $item->utz_budget > 0)
                            <tr>
                                <th>งบประมาณ:</th>
                                <td><span class="badge bg-success">💰 {{ number_format($item->utz_budget, 2) }} บาท</span></td>
                            </tr>
                            @endif
                            <tr>
                            <th>เข้าชม:</th>
                            <td><span class="badge bg-info text-dark">{{ number_format($item->utz_count ?? 0) }} ครั้ง</span></td>
                        </tr>
                            @if($item->utz_files || $item->utz_countfile)
                            <tr>
                                <th>ไฟล์แนบ:</th>
                                <td>
                                    @if($item->utz_files)
                                        <div class="mb-2">
                                            @foreach(explode(',', $item->utz_files) as $file)
                                                @if(trim($file))
                                                    @php
                                                        $trimmedFile = trim($file);
                                                        $ext = pathinfo($trimmedFile, PATHINFO_EXTENSION);
                                                        $nameOnly = pathinfo($trimmedFile, PATHINFO_FILENAME);
                                                        $displayName = (mb_strlen($nameOnly) > 10) 
                                                            ? mb_substr($nameOnly, 0, 10) . '...' . ($ext ? '.' . $ext : '')
                                                            : $trimmedFile;
                                                    @endphp
                                                    <a href="{{ route('backend.rdbprojectutilize.download', ['id' => $item->utz_id, 'filename' => $trimmedFile]) }}" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-outline-primary me-1 mb-1"
                                                       title="{{ $trimmedFile }}">
                                                        <i class="bi bi-file-earmark"></i> {{ $displayName }}
                                                    </a>
                                                @endif
                                            @endforeach
                                            
                                            @if($item->utz_countfile)
                                                <span class="badge bg-secondary ms-1">📊 {{ number_format($item->utz_countfile) }} ครั้ง</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detail Card -->
            @if($item->utz_detail)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> รายละเอียดการใช้ประโยชน์</h5>
                </div>
                <div class="card-body">
                    <div class="content-area">
                        {!! $item->utz_detail !!}
                    </div>
                </div>
            </div>
            @endif

            <!-- Note Card -->
            @if($item->utz_note)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-sticky"></i> หมายเหตุ</h5>
                </div>
                <div class="card-body">
                    {!! $item->utz_note !!}
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 order-2 order-lg-2">
            <!-- Map Card -->
            @if($item->changwat && $item->changwat->lat && $item->changwat->long)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> แผนที่ตำแหน่ง</h5>
                </div>
                <div class="card-body p-0">
                    <iframe 
                        width="100%" 
                        height="200" 
                        style="border:0;" 
                        loading="lazy" 
                        allowfullscreen 
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://maps.google.com/maps?q={{ $item->changwat->lat }},{{ $item->changwat->long }}&z=12&output=embed">
                    </iframe>
                </div>
                <div class="card-footer text-muted small">
                    📍 {{ $item->changwat->tambon_t }}, {{ $item->changwat->amphoe_t }}, {{ $item->changwat->changwat_t }}
                </div>
            </div>
            @endif

            <!-- Related Project Card -->
            @if($item->project)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-link-45deg"></i> โครงการที่เกี่ยวข้อง</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold text-primary">
                        <a href="{{ route('backend.rdb_project.show', $item->pro_id) }}" target="_blank" class="text-decoration-none">
                            {{ $item->project->pro_nameTH }}
                        </a>
                    </h6>
                    @if($item->project->pro_code)
                        <p class="mb-2"><small class="text-muted">รหัสโครงการ: {{ $item->project->pro_code }}</small></p>
                    @endif
                    
                    <div class="mt-3">
                        <a href="{{ route('backend.rdb_project.show', $item->pro_id) }}" target="_blank" class="btn btn-sm btn-outline-success w-100">
                            <i class="bi bi-box-arrow-up-right"></i> ดูรายละเอียดโครงการ
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Metadata Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> ข้อมูลระบบ</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        @if($item->created_at)
                        <tr>
                            <th style="width: 35%;">สร้างเมื่อ:</th>
                            <td>
                                {{ \App\Helpers\ThaiDateHelper::format($item->created_at, true, true) }}
                                @if($item->createdBy)
                                    โดย {{ $item->createdBy->researcher->researcher_fname ?? '' }} {{ $item->createdBy->researcher->researcher_lname ?? $item->createdBy->username ?? '' }}
                                @endif
                            </td>
                        </tr>
                        @endif
                        @if($item->updated_at)
                        <tr>
                            <th>แก้ไขล่าสุด:</th>
                            <td>
                                {{ \App\Helpers\ThaiDateHelper::format($item->updated_at, true, true) }}
                                @if($item->updatedBy)
                                    โดย {{ $item->updatedBy->researcher->researcher_fname ?? '' }} {{ $item->updatedBy->researcher->researcher_lname ?? $item->updatedBy->username ?? '' }}
                                @endif
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end flex-wrap gap-2">
                <a href="{{ route('backend.rdbprojectutilize.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                </a>
                <a href="{{ route('backend.rdbprojectutilize.edit', $item->utz_id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-2"></i> แก้ไข
                </a>
                <button type="button" class="btn btn-info" onclick="window.print();">
                    <i class="bi bi-printer me-2"></i> พิมพ์
                </button>
                <button type="button" class="btn btn-danger" onclick="if(confirm('ยืนยันการลบข้อมูล?')) document.getElementById('delete-form').submit();">
                    <i class="bi bi-trash me-2"></i> ลบ
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
