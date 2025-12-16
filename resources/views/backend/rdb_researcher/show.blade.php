@extends('layouts.app')

@section('content')
@php 
    $item = $researcher; 
@endphp
<div class="container py-4">
    <div class="row">
        <!-- Sidebar: Profile Image & Contact -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body text-center">
                    <div class="mb-3 position-relative d-inline-block">
                        @if($item->researcher_picture)
                            <img src="{{ asset('storage/uploads/researchers/' . $item->researcher_picture) }}" 
                                 alt="{{ $item->researcher_fname }}" 
                                 class="rounded-circle object-fit-cover"
                                 style="width: 150px; height: 150px; border: 4px solid #f8f9fa;">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto text-white" 
                                 style="width: 150px; height: 150px; font-size: 4rem;">
                                {{ strtoupper(substr($item->researcher_fnameEN ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                        
                        <!-- Upload Trigger Button -->
                        <a href="#" data-bs-toggle="modal" data-bs-target="#editImageModal" 
                           class="position-absolute bottom-0 end-0 bg-white border rounded-circle d-flex align-items-center justify-content-center shadow-sm text-secondary"
                           style="width: 40px; height: 40px; text-decoration: none; border-width: 2px !important; right: 10px !important;">
                            <i class="bi bi-camera-fill fa-lg"></i>
                        </a>
                    </div>
                    <h4 class="card-title mb-1">
                        {{ $item->prefix->prefix_nameTH ?? '' }}{{ $item->researcher_fname }} {{ $item->researcher_lname }}
                    </h4>
                    <p class="text-muted mb-3">
                        {{ $item->researcher_fnameEN }} {{ $item->researcher_lnameEN }}
                    </p>
                    <span class="badge bg-primary mb-3">{{ $item->department->department_nameTH ?? '-' }}</span>

                    <div class="mb-3 d-flex justify-content-center align-items-center">
                        <span class="text-secondary small font-monospace me-2">{{ $item->researcher_codeid ?? '-' }}</span>
                         <a href="#" data-bs-toggle="modal" data-bs-target="#editCodeIdModal" class="text-secondary text-decoration-none" title="แก้ไข Code ID">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    </div>
                    
                    <hr>
                    
                    <div class="text-start px-3">
                        <p class="mb-2"><i class="bi bi-envelope-fill text-secondary me-2"></i> {{ $item->researcher_email ?? '-' }}</p>
                        <p class="mb-2"><i class="bi bi-telephone-fill text-secondary me-2"></i> {{ $item->researcher_tel ?? '-' }}</p>
                        <p class="mb-2"><i class="bi bi-phone-fill text-secondary me-2"></i> {{ $item->researcher_mobile ?? '-' }}</p>
                        @if($item->scopus_authorId)
                            <p class="mb-2"><i class="bi bi-journal-text text-secondary me-2"></i> Scopus ID: {{ $item->scopus_authorId }}</p>
                        @endif
                        @if($item->orcid)
                            <p class="mb-2"><i class="bi bi-person-badge text-secondary me-2"></i> ORCID: {{ $item->orcid }}</p>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('backend.rdb_researcher.edit', $item->researcher_id) }}" class="btn btn-warning text-dark">
                            <i class="bi bi-pencil-square"></i> แก้ไขข้อมูลนักวิจัย
                        </a>
                        <a href="{{ route('backend.rdb_researcher.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> กลับหน้ารายการ
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Tabs -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="researcherTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab">
                                <i class="bi bi-folder-fill"></i> โครงการวิจัย <span class="badge bg-primary">{{ $item->rdbProjects->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="publications-tab" data-bs-toggle="tab" data-bs-target="#publications" type="button" role="tab">
                                <i class="bi bi-journal-text"></i> ผลงานตีพิมพ์ <span class="badge bg-primary">{{ $item->rdbPublisheds->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="utilizations-tab" data-bs-toggle="tab" data-bs-target="#utilizations" type="button" role="tab">
                                <i class="bi bi-box-seam"></i> การนำไปใช้ <span class="badge bg-primary">{{ $utilizations->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ip-tab" data-bs-toggle="tab" data-bs-target="#ip" type="button" role="tab">
                                <i class="bi bi-shield-check"></i> ทรัพย์สินทางปัญญา <span class="badge bg-primary">{{ $item->rdbDips->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="researcherTabsContent">
                        <!-- Projects Tab -->
                        <div class="tab-pane fade show active" id="projects" role="tabpanel">
                            @if($item->rdbProjects && $item->rdbProjects->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-top">
                                        <thead>
                                            <tr>
                                                <th style="width: 15%;">ปีงบฯ</th>
                                                <th style="width: 85%;">ชื่อโครงการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->rdbProjects as $project)
                                                <tr>
                                                    <td>{{ $project->year->year_name ?? '-' }}</td>
                                                    <td>
                                                        <div class="fw-bold">
                                                            @if($project->pro_code)
                                                                <span class="text-primary">{!! $project->pro_code !!}</span>
                                                            @endif
                                                            <a href="{{ route('backend.rdb_project.show', $project->pro_id) }}" target="_blank" class="text-body text-decoration-none">
                                                                {!! $project->pro_nameTH !!}
                                                            </a>
                                                        </div>
                                                        <div class="mt-1">
                                                            @if($project->status)
                                                                <span class="badge" 
                                                                      style="background-color: {{ $project->status->ps_color ?? '#6c757d' }}; color: #fff;">
                                                                    {{ $project->status->ps_name }}
                                                                </span>
                                                            @endif

                                                            @if(isset($projectPositions) && $project->pivot && $project->pivot->position_id && isset($projectPositions[$project->pivot->position_id]))
                                                                @php
                                                                    $roleName = $projectPositions[$project->pivot->position_id]->position_nameTH;
                                                                    $roleClass = $project->pivot->position_id == 1 ? 'border-primary text-primary' : 'border-secondary text-secondary';
                                                                @endphp
                                                                <span class="badge border {{ $roleClass }} bg-transparent">{{ $roleName }}</span>
                                                            @endif
                                                        </div>
                                                        <small class="text-muted d-block mt-1">
                                                            @if($project->type)
                                                                {{ $project->type->pt_name }}
                                                                @if($project->typeSub)
                                                                    • {{ $project->typeSub->pts_name }}
                                                                @endif
                                                            @endif
                                                            @if($project->type && $project->pro_budget)
                                                                •
                                                            @endif
                                                            @if($project->pro_budget)
                                                                งบฯ {{ number_format($project->pro_budget, 0) }} บาท
                                                            @endif
                                                        </small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-4">ไม่พบข้อมูลโครงการวิจัย</p>
                            @endif
                        </div>

                        <!-- Publications Tab -->
                        <div class="tab-pane fade" id="publications" role="tabpanel">
                            @if($item->rdbPublisheds && $item->rdbPublisheds->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-top">
                                        <thead>
                                            <tr>
                                                <th style="width: 50%;">ชื่อผลงาน</th>
                                                <th style="width: 50%;">วันที่ตีพิมพ์</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->rdbPublisheds as $pub)
                                                <tr>
                                                    <td>
                                                        @if(isset($pub->authorType))
                                                            <span class="badge bg-info text-dark mb-1">{{ $pub->authorType->pubta_nameTH }}</span>
                                                        @endif
                                                        <div class="fw-bold">
                                                            <!-- Admin has no show route for publication yet, show text only -->
                                                            {!! $pub->pub_name !!}
                                                        </div>
                                                        @if($pub->project)
                                                            <small class="text-muted">
                                                                โครงการ: 
                                                                <a href="{{ route('backend.rdb_project.show', $pub->project->pro_id) }}" target="_blank" class="text-body text-decoration-none">
                                                                    {!! $pub->project->pro_nameTH !!}
                                                                </a>
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($pub->pub_date)
                                                            <div class="fw-bold">
                                                                {{ \App\Helpers\ThaiDateHelper::format($pub->pub_date, false, true) }}
                                                            </div>
                                                        @endif
                                                        @if($pub->pubtype && $pub->pubtype->pubtype_subgroup)
                                                            <div><small class="text-muted">{{ $pub->pubtype->pubtype_subgroup }}</small></div>
                                                        @endif
                                                        @if($pub->pub_name_journal)
                                                            <small class="text-muted">{!! $pub->pub_name_journal !!}</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-4">ไม่พบข้อมูลผลงานตีพิมพ์</p>
                            @endif
                        </div>

                        <!-- Utilizations Tab -->
                        <div class="tab-pane fade" id="utilizations" role="tabpanel">
                            @if($utilizations && $utilizations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-top">
                                        <thead>
                                            <tr>
                                                <th style="width: 100%;">รายละเอียดการนำไปใช้ประโยชน์</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($utilizations as $util)
                                                <tr>
                                                    <td>
                                                        @if($util->utz_date)
                                                            <div class="fw-bold mb-1">
                                                                วันที่: {{ \App\Helpers\ThaiDateHelper::format($util->utz_date, false, true) }}
                                                            </div>
                                                        @endif
                                                        
                                                        @if($util->utz_department_name)
                                                            <div class="mb-1">
                                                                <strong>หน่วยงาน:</strong> 
                                                                <!-- Admin has no show route for utilize yet -->
                                                                {!! $util->utz_department_name !!}
                                                                @if($util->changwat)
                                                                    <small class="text-muted">
                                                                        @if($util->changwat->tambon_t)
                                                                            {{ $util->changwat->tambon_t }}
                                                                        @endif
                                                                        @if($util->changwat->amphoe_t)
                                                                            {{ $util->changwat->amphoe_t }}
                                                                        @endif
                                                                        @if($util->changwat->changwat_t)
                                                                            {{ $util->changwat->changwat_t }}
                                                                        @endif
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        
                                                        @if($util->utz_department_address)
                                                            <div class="mb-1">
                                                                <strong>ที่อยู่:</strong> <small class="text-muted">{!! $util->utz_department_address !!}</small>
                                                            </div>
                                                        @endif
                                                        
                                                        @if($util->utz_leading)
                                                            <div class="mb-1">
                                                                <strong>ผู้ลงนาม:</strong> 
                                                                <small class="text-muted">
                                                                    {!! $util->utz_leading !!}
                                                                    @if($util->utz_leading_position)
                                                                        ({{ $util->utz_leading_position }})
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        @endif
                                                        
                                                        @if($util->project)
                                                            <div>
                                                                <strong>โครงการวิจัย:</strong> 
                                                                <a href="{{ route('backend.rdb_project.show', $util->project->pro_id) }}" target="_blank" class="text-body text-decoration-none">
                                                                    <small>{!! $util->project->pro_nameTH !!}</small>
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-4">ไม่พบข้อมูลการนำไปใช้ประโยชน์</p>
                            @endif
                        </div>

                        <!-- Intellectual Property Tab -->
                        <div class="tab-pane fade" id="ip" role="tabpanel">
                            @if($item->rdbDips && $item->rdbDips->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-top">
                                        <thead>
                                            <tr>
                                                <th style="width: 30%;">เลขที่คำขอ</th>
                                                <th style="width: 50%;">ชื่อ / ประเภท</th>
                                                <th style="width: 20%;">วันที่คำขอ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->rdbDips as $dip)
                                                <tr>
                                                    <td>
                                                        <div>{!! $dip->dip_request_number !!}</div>
                                                        @if($dip->dip_number)
                                                            <small class="text-muted">ทะเบียน: {!! $dip->dip_number !!}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold">{!! $dip->dip_name !!}</div>
                                                        @if($dip->dipType)
                                                            <small class="text-muted">{{ $dip->dipType->diptype_name }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($dip->dip_request_date)
                                                            {{ \App\Helpers\ThaiDateHelper::format($dip->dip_request_date, false, true) }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-4">ไม่พบข้อมูลทรัพย์สินทางปัญญา</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Code ID Modal -->
<div class="modal fade" id="editCodeIdModal" tabindex="-1" aria-labelledby="editCodeIdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('backend.rdb_researcher.update_codeid', $item->researcher_id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editCodeIdModalLabel">จัดการ Researcher Code ID</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="citizen_id_modal" class="form-label">เลขบัตรประจำตัวประชาชน</label>
                        <input type="text" class="form-control" id="citizen_id_modal" name="citizen_id" placeholder="0-0000-00000-00-0" autocomplete="off" required>
                        <div class="form-text">ระบุเลขบัตรประชาชนเพื่อคำนวณและสร้าง Code ID ใหม่ (ระบบจะทำการตรวจสอบความถูกต้องของเลขบัตร before บันทึก)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึกและสร้าง Code ID</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Edit Image Modal -->
<div class="modal fade" id="editImageModal" tabindex="-1" aria-labelledby="editImageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('backend.rdb_researcher.update_image', $item->researcher_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editImageModalLabel">เปลี่ยนรูปโปรไฟล์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <label for="fileimg_modal" class="form-label">เลือกรูปภาพใหม่ (JPG, PNG, GIF ไม่เกิน 2MB)</label>
                        <input type="file" class="form-control" id="fileimg_modal" name="fileimg" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <div class="mt-3">
                        <img id="imagePreview" src="#" alt="ตัวอย่างรูปภาพ" class="rounded-circle object-fit-cover d-none" style="width: 150px; height: 150px; border: 4px solid #f8f9fa;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึกรูปภาพ</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mask for Modal Input
        const citizenIdInput = document.getElementById('citizen_id_modal');
        if (citizenIdInput) {
            IMask(citizenIdInput, {
                mask: '0-0000-00000-00-0'
            });
        }
    });

    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '#';
            preview.classList.add('d-none');
        }
    }
</script>
@endpush
