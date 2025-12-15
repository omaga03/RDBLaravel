@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Sidebar: Profile Image & Contact -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body text-center">
                    <div class="mb-3">
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
                    </div>
                    <h4 class="card-title mb-1">
                        {{ $item->prefix->prefix_nameTH ?? '' }}{{ $item->researcher_fname }} {{ $item->researcher_lname }}
                    </h4>
                    <p class="text-muted mb-3">
                        {{ $item->researcher_fnameEN }} {{ $item->researcher_lnameEN }}
                    </p>
                    <span class="badge bg-primary mb-3">{{ $item->department->department_nameTH ?? '-' }}</span>
                    
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
                    
                    <div class="d-grid">
                        <a href="{{ route('frontend.rdbresearcher.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> กลับหน้ารายการ
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Tabs -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
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
                                    <table class="table table-hover align-top">
                                        <thead class="table-light">
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
                                                            <a href="{{ route('frontend.rdbproject.show', $project->pro_id) }}" target="_blank" class="text-body text-decoration-none">
                                                                {!! $project->pro_nameTH !!}
                                                            </a>
                                                        </div>
                                                        <div class="mt-1">
                                                            @if($project->status)
                                                                @php
                                                                    $statusColor = 'secondary';
                                                                    if($project->status->ps_id == 1) $statusColor = 'success'; // ดำเนินการ
                                                                    elseif($project->status->ps_id == 2) $statusColor = 'warning text-dark'; // ระหว่างดำเนินการ
                                                                    elseif($project->status->ps_id == 3) $statusColor = 'info text-dark'; // ขยายเวลา
                                                                    elseif($project->status->ps_id == 4) $statusColor = 'primary'; // เสร็จสมบูรณ์
                                                                    elseif($project->status->ps_id == 5) $statusColor = 'success'; // ปิดโครงการ
                                                                    elseif($project->status->ps_id == 6) $statusColor = 'danger'; // ยกเลิก
                                                                @endphp
                                                                <span class="badge bg-{{ $statusColor }} me-1">{{ $project->status->ps_name }}</span>
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
                                    <table class="table table-hover align-top">
                                        <thead class="table-light">
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
                                                            <a href="{{ route('frontend.rdbpublished.show', $pub->id) }}" target="_blank" class="text-body text-decoration-none">
                                                                {!! $pub->pub_name !!}
                                                            </a>
                                                        </div>
                                                        @if($pub->project)
                                                            <small class="text-muted">
                                                                โครงการ: 
                                                                <a href="{{ route('frontend.rdbproject.show', $pub->project->pro_id) }}" target="_blank" class="text-body text-decoration-none">
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
                                    <table class="table table-hover align-top">
                                        <thead class="table-light">
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
                                                                <a href="{{ route('frontend.rdbprojectutilize.show', $util->utz_id) }}" target="_blank" class="text-body text-decoration-none">
                                                                    {!! $util->utz_department_name !!}
                                                                </a>
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
                                                                <a href="{{ route('frontend.rdbproject.show', $util->project->pro_id) }}" target="_blank" class="text-body text-decoration-none">
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
                                    <table class="table table-hover align-top">
                                        <thead class="table-light">
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
@endsection