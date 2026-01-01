@extends('layouts.app')

@push('styles')
<!-- IMask for Modal -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@section('content')
@php 
    $item = $researcher; 
@endphp
<div class="py-4">
    {{-- Page Header with Actions --}}
    <x-page-header 
        title="รายละเอียดข้อมูลนักวิจัย"
        icon="bi-person-badge"
        :backRoute="route('backend.rdb_researcher.index')"
        :editRoute="route('backend.rdb_researcher.edit', $item->researcher_id)"
        :deleteRoute="route('backend.rdb_researcher.destroy', $item->researcher_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />

    <div class="row">
        <!-- Sidebar: Profile Image & Contact -->
        <div class="col-md-4 mb-4">
            <x-card class="sticky-top" style="top: 20px;" color="info">
                <div class="text-center">
                    <div class="mb-3 position-relative d-inline-block">
                        @if($item->researcher_picture)
                            <img src="{{ asset('storage/uploads/researchers/' . $item->researcher_picture) }}" 
                                 alt="{{ $item->researcher_fname }}" 
                                 class="rounded-circle object-fit-cover shadow-sm bg-white"
                                 style="width: 150px; height: 150px; border: 4px solid #fff;">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto text-white shadow-sm border border-4 border-white" 
                                 style="width: 150px; height: 150px; font-size: 4rem;">
                                {{ strtoupper(substr($item->researcher_fnameEN ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                        
                        <!-- Upload Trigger Button -->
                        <a href="#" data-bs-toggle="modal" data-bs-target="#editImageModal" 
                           class="position-absolute bottom-0 end-0 bg-white border rounded-circle d-flex align-items-center justify-content-center shadow-sm text-primary"
                           style="width: 40px; height: 40px; text-decoration: none; right: 5px !important; bottom: 5px !important;">
                            <i class="bi bi-camera-fill"></i>
                        </a>
                    </div>
                    
                    <h4 class="card-title mb-1 fw-bold text-primary">
                        {{ $item->prefix->prefix_nameTH ?? '' }}{{ $item->researcher_fname }} {{ $item->researcher_lname }}
                    </h4>
                    <p class="text-muted mb-2">
                        {{ $item->researcher_fnameEN }} {{ $item->researcher_lnameEN }}
                    </p>
                    <div class="mb-3">
                        <span class="badge bg-primary text-wrap lh-base" style="max-width: 100%;">
                            {{ $item->department->department_nameTH ?? '-' }}
                        </span>
                    </div>

                    <div class="mb-3 d-flex justify-content-center align-items-center bg-light rounded p-2 mx-4 border">
                        <i class="bi bi-upc-scan me-2 text-muted"></i>
                        <span class="text-secondary small font-monospace me-2 text-break fw-bold">{{ $item->researcher_codeid ?? '-' }}</span>
                         <a href="#" data-bs-toggle="modal" data-bs-target="#editCodeIdModal" class="text-secondary text-decoration-none" title="แก้ไข Code ID">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    </div>
                    
                    <hr>
                    
                    <div class="text-start px-2 text-break">
                        <p class="mb-2"><i class="bi bi-envelope-fill text-primary me-2"></i> {{ $item->researcher_email ?? '-' }}</p>
                        <p class="mb-2"><i class="bi bi-telephone-fill text-primary me-2"></i> {{ $item->researcher_tel ?? '-' }}</p>
                        <p class="mb-2"><i class="bi bi-phone-fill text-primary me-2"></i> {{ $item->researcher_mobile ?? '-' }}</p>
                        @if($item->scopus_authorId)
                            <div class="mb-2 p-2 bg-light rounded border">
                                <div class="mb-1">
                                    <i class="bi bi-journal-bookmark-fill text-info me-2"></i> <strong>Scopus ID:</strong> 
                                    <a href="https://www.scopus.com/authid/detail.uri?authorId={{ $item->scopus_authorId }}" target="_blank" class="text-decoration-none">
                                        {{ $item->scopus_authorId }} <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>h-index: <span class="badge bg-info text-dark">{{ $item->researcher_hindex ?? 0 }}</span></span>
                                    <form action="{{ route('backend.rdb_researcher.sync_scopus', $item->researcher_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-info py-0 px-2" title="Sync h-index จาก Scopus">
                                            <i class="bi bi-arrow-repeat"></i> Sync
                                        </button>
                                    </form>
                                </div>
                                @if($item->scopus_synced_at)
                                    <div class="text-end mt-1">
                                        <small class="text-muted" style="font-size: 0.7em;">อัปเดต: {{ \App\Helpers\ThaiDateHelper::format($item->scopus_synced_at, false, true) }}</small>
                                    </div>
                                @endif
                            </div>
                        @endif
                        @if($item->orcid)
                            <p class="mb-2">
                                <i class="bi bi-link-45deg text-success me-2"></i> <strong>ORCID:</strong>
                                <a href="https://orcid.org/{{ $item->orcid }}" target="_blank" class="text-decoration-none">
                                    {{ $item->orcid }} <i class="bi bi-box-arrow-up-right small"></i>
                                </a>
                            </p>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Main Content: Tabs -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs" id="researcherTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab">
                                <i class="bi bi-folder-fill"></i> โครงการวิจัย <span class="badge bg-primary ms-1">{{ $item->rdbProjects->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="publications-tab" data-bs-toggle="tab" data-bs-target="#publications" type="button" role="tab">
                                <i class="bi bi-journal-text"></i> ผลงานตีพิมพ์ <span class="badge bg-primary ms-1">{{ $item->rdbPublisheds->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="utilizations-tab" data-bs-toggle="tab" data-bs-target="#utilizations" type="button" role="tab">
                                <i class="bi bi-box-seam"></i> การนำไปใช้ <span class="badge bg-primary ms-1">{{ $utilizations->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ip-tab" data-bs-toggle="tab" data-bs-target="#ip" type="button" role="tab">
                                <i class="bi bi-shield-check"></i> ทรัพย์สินทางปัญญา <span class="badge bg-primary ms-1">{{ $item->rdbDips->count() }}</span>
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
                                    <table class="table table-hover align-top">
                                        <thead>
                                            <tr>
                                                <th style="width: 15%;">ปีงบฯ</th>
                                                <th style="width: 85%;">ชื่อโครงการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->rdbProjects as $project)
                                                <tr>
                                                    <td><span class="badge bg-light text-dark border">{{ $project->year->year_name ?? '-' }}</span></td>
                                                    <td>
                                                        <div class="fw-bold mb-1">
                                                            @if($project->pro_code)
                                                                <span class="text-primary me-1">[{!! $project->pro_code !!}]</span>
                                                            @endif
                                                            <a href="{{ route('backend.rdb_project.show', $project->pro_id) }}" target="_blank" class="text-decoration-none text-body">
                                                                {!! $project->pro_nameTH !!}
                                                            </a>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
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
                                                        <small class="text-muted">
                                                            @if($project->type)
                                                                <i class="bi bi-tag"></i> {{ $project->type->pt_name }}
                                                                @if($project->typeSub)
                                                                    • {{ $project->typeSub->pts_name }}
                                                                @endif
                                                            @endif
                                                            @if($project->pro_budget)
                                                                <span class="ms-2"><i class="bi bi-cash"></i> งบฯ {{ number_format($project->pro_budget, 0) }} บาท</span>
                                                            @endif
                                                        </small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x fs-1 opacity-50"></i>
                                    <p class="mt-2">ไม่พบข้อมูลโครงการวิจัย</p>
                                </div>
                            @endif
                        </div>

                        <!-- Publications Tab -->
                        <div class="tab-pane fade" id="publications" role="tabpanel">
                            @if($item->rdbPublisheds && $item->rdbPublisheds->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-top">
                                        <thead>
                                            <tr>
                                                <th style="width: 70%;">ชื่อผลงาน</th>
                                                <th style="width: 30%;">วันที่ตีพิมพ์</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->rdbPublisheds as $pub)
                                                <tr>
                                                    <td>
                                                        @if(isset($pub->authorType))
                                                            <span class="badge bg-info text-dark mb-1">{{ $pub->authorType->pubta_nameTH }}</span>
                                                        @endif
                                                        <div class="fw-bold text-body">
                                                            {!! $pub->pub_name !!}
                                                        </div>
                                                        @if($pub->project)
                                                            <small class="text-muted d-block mt-1">
                                                                <i class="bi bi-link-45deg"></i> โครงการ: 
                                                                <a href="{{ route('backend.rdb_project.show', $pub->project->pro_id) }}" target="_blank" class="text-decoration-none text-muted">
                                                                    {!! $pub->project->pro_nameTH !!}
                                                                </a>
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($pub->pub_date)
                                                            <div class="fw-bold">
                                                                <i class="bi bi-calendar-event"></i> {{ \App\Helpers\ThaiDateHelper::format($pub->pub_date, false, true) }}
                                                            </div>
                                                        @endif
                                                        @if($pub->pubtype && $pub->pubtype->pubtype_subgroup)
                                                            <div class="small text-muted mt-1">{{ $pub->pubtype->pubtype_subgroup }}</div>
                                                        @endif
                                                        @if($pub->pub_name_journal)
                                                            <small class="text-muted d-block">in: {!! $pub->pub_name_journal !!}</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 opacity-50"></i>
                                    <p class="mt-2">ไม่พบข้อมูลผลงานตีพิมพ์</p>
                                </div>
                            @endif
                        </div>

                        <!-- Utilizations Tab -->
                        <div class="tab-pane fade" id="utilizations" role="tabpanel">
                            @if($utilizations && $utilizations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-top">
                                        <thead>
                                            <tr>
                                                <th style="width: 100%;">รายละเอียดการนำไปใช้ประโยชน์</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($utilizations as $util)
                                                <tr>
                                                    <td class="pb-3">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            @if($util->utz_department_name)
                                                                <div class="fw-bold text-primary">
                                                                    <i class="bi bi-building"></i> {!! $util->utz_department_name !!}
                                                                </div>
                                                            @endif
                                                            @if($util->utz_date)
                                                                <span class="badge bg-light text-dark border">
                                                                    {{ \App\Helpers\ThaiDateHelper::format($util->utz_date, false, true) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        
                                                        @if($util->changwat)
                                                            <div class="mb-2 small text-muted">
                                                                <i class="bi bi-geo-alt"></i>
                                                                @if($util->changwat->tambon_t) {{ $util->changwat->tambon_t }} @endif
                                                                @if($util->changwat->amphoe_t) » {{ $util->changwat->amphoe_t }} @endif
                                                                @if($util->changwat->changwat_t) » {{ $util->changwat->changwat_t }} @endif
                                                            </div>
                                                        @endif

                                                        @if($util->utz_department_address)
                                                            <div class="mb-2 small text-secondary">
                                                                {!! $util->utz_department_address !!}
                                                            </div>
                                                        @endif
                                                        
                                                        @if($util->project)
                                                            <div class="mt-2 pt-2 border-top">
                                                                <small class="text-muted">จากโครงการ:</small>
                                                                <a href="{{ route('backend.rdb_project.show', $util->project->pro_id) }}" target="_blank" class="d-block text-decoration-none text-body fw-semibold">
                                                                    {!! $util->project->pro_nameTH !!}
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
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam fs-1 opacity-50"></i>
                                    <p class="mt-2">ไม่พบข้อมูลการนำไปใช้ประโยชน์</p>
                                </div>
                            @endif
                        </div>

                        <!-- Intellectual Property Tab -->
                        <div class="tab-pane fade" id="ip" role="tabpanel">
                            @if($item->rdbDips && $item->rdbDips->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-top">
                                        <thead>
                                            <tr>
                                                <th style="width: 60%;">ชื่อผลงาน</th>
                                                <th style="width: 20%;">ประเภท</th>
                                                <th style="width: 20%;">วันที่</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->rdbDips as $dip)
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold">
                                                            <a href="{{ route('backend.rdb_dip.show', $dip->dip_id) }}" target="_blank" class="text-decoration-none text-body">
                                                                {!! $dip->dip_data2_name ?? 'ไม่มีชื่อผลงาน' !!}
                                                            </a>
                                                        </div>
                                                        @if($dip->dip_request_number)
                                                            <small class="text-muted"><i class="bi bi-upc"></i> เลขที่คำขอ: {!! $dip->dip_request_number !!}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border">{{ $dip->dipType->dipt_name ?? '-' }}</span>
                                                    </td>
                                                    <td>
                                                        @if($dip->dip_request_date)
                                                            {{ \App\Helpers\ThaiDateHelper::format($dip->dip_request_date, false, true) }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-shield-x fs-1 opacity-50"></i>
                                    <p class="mt-2">ไม่พบข้อมูลทรัพย์สินทางปัญญา</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                @php
                    $createdBy = $item->user_created ? \App\Models\User::find($item->user_created) : null;
                    $updatedBy = $item->user_updated ? \App\Models\User::find($item->user_updated) : null;
                    $createdByName = $createdBy?->researcher ? ($createdBy->researcher->researcher_fname . ' ' . $createdBy->researcher->researcher_lname) : ($createdBy?->username ?? '-');
                    $updatedByName = $updatedBy?->researcher ? ($updatedBy->researcher->researcher_fname . ' ' . $updatedBy->researcher->researcher_lname) : ($updatedBy?->username ?? '-');
                @endphp
                <x-system-info :created_at="$item->created_at" :created_by="$createdByName" :updated_at="$item->updated_at" :updated_by="$updatedByName" />
            </div>
        </div>
    </div>

    {{-- Bottom Action Buttons --}}
    <x-action-buttons 
        :backRoute="route('backend.rdb_researcher.index')"
        :editRoute="route('backend.rdb_researcher.edit', $item->researcher_id)"
        :deleteRoute="route('backend.rdb_researcher.destroy', $item->researcher_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />
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
                        <div class="form-text text-danger help-text">
                            <i class="bi bi-exclamation-circle"></i> ระบุเลขบัตรประชาชนเพื่อคำนวณและสร้าง Code ID ใหม่ ระบบจะทำการตรวจสอบความถูกต้องของเลขบัตรก่อนบันทึก
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> บันทึกและสร้าง Code ID</button>
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
                        <input type="file" class="form-control" id="fileimg_modal" name="fileimg" accept="image/*" onchange="previewImages(this)">
                    </div>
                    <div class="mt-3">
                        <img id="imagePreviews" src="#" alt="ตัวอย่างรูปภาพ" class="rounded-circle object-fit-cover d-none shadow-sm" style="width: 150px; height: 150px; border: 4px solid #f8f9fa;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> บันทึกรูปภาพ</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/imask"></script>
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

    function previewImages(input) {
        const preview = document.getElementById('imagePreviews');
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
