@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2>{{ strip_tags(html_entity_decode($project->pro_nameTH, ENT_QUOTES, 'UTF-8')) }}</h2>
        </div>
    </div>

    <!-- TomSelect CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <div class="py-4">
        {{-- Page Header with Actions --}}
        <x-page-header 
            title="รายละเอียดโครงการวิจัย"
            icon="bi-folder2-open"
            :backRoute="route('backend.rdb_project.index')"
            :editRoute="route('backend.rdb_project.edit', $project->pro_id)"
            :deleteRoute="route('backend.rdb_project.destroy', $project->pro_id)"
            :canDelete="true"
            :showPrint="true"
        />

        <div class="row g-4">
            <!-- Left Column: Project Summary & Quick Info -->
            <div class="col-lg-4">
                <div class="card shadow-sm border mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-primary fw-bold font-monospace fs-6">
                                {{ $project->pro_code ?? 'ID: ' . $project->pro_id }}
                            </span>
                            <div class="d-flex align-items-center gap-1">
                                @if($project->status)
                                    <span class="badge" style="background-color: {{ $project->status->ps_color ?? '#6c757d' }}; color: #fff;">
                                        {{ $project->status->ps_name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">ไม่ระบุสถานะ</span>
                                @endif
                                @can('update', $project)
                                <button type="button" class="btn btn-sm btn-light py-0 px-2 text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#projectStatusModal" title="ปรับปรุงสถานะโครงการ">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Budget Highlight -->
                        <div class="text-center p-3 mb-3 bg-body-secondary rounded border border-secondary-subtle">
                            <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">งบประมาณโครงการ</span>
                            <h3 class="fw-bold mb-0 text-success">฿{{ number_format($project->pro_budget, 2) }}</h3>
                        </div>

                        <!-- Key Details List -->
                        <ul class="list-group list-group-flush mb-3 small">
                            <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                <span class="text-body-secondary">ปีงบประมาณ:</span>
                                <span class="fw-bold text-body">{{ $project->year->year_name ?? '-' }}</span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                <span class="text-body-secondary">หน่วยงาน/คณะ:</span>
                                <span class="fw-bold text-body text-end ms-2">{{ $project->department->department_nameTH ?? '-' }}</span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                <span class="text-body-secondary">ประเภททุน:</span>
                                <span class="fw-bold text-body text-end ms-2">{{ $project->type->pt_name ?? '-' }}</span>
                            </li>
                            @if($project->typeSub)
                            <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                <span class="text-body-secondary">ทุนย่อย:</span>
                                <span class="fw-bold text-body text-end ms-2">{{ $project->typeSub->pts_name }}</span>
                            </li>
                            @endif
                            <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                <span class="text-body-secondary">ระยะเวลา:</span>
                                <span class="fw-bold text-body text-end ms-2">
                                    {{ \App\Helpers\ThaiDateHelper::format($project->pro_date_start) }} ถึง {{ \App\Helpers\ThaiDateHelper::format($project->pro_date_end) }}
                                </span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                <span class="text-body-secondary">การเผยแพร่ข้อมูล:</span>
                                @if($project->data_show)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-globe me-1"></i> สาธารณะ (Public)
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                        <i class="bi bi-eye-slash-fill me-1"></i> ซ่อนเฉพาะผู้ดูแล (Private)
                                    </span>
                                @endif
                            </li>
                        </ul>

                        <!-- Statistics Counter -->
                        <div class="row g-2 text-center mb-3">
                            <div class="col-4">
                                <div class="p-2 border rounded bg-body-tertiary">
                                    <div class="fw-bold fs-6 text-primary">{{ number_format($project->pro_count_page ?? 0) }}</div>
                                    <small class="text-body-secondary" style="font-size: 0.75rem;">เข้าชม</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-body-tertiary">
                                    <div class="fw-bold fs-6 text-info">{{ number_format($project->pro_count_abs ?? 0) }}</div>
                                    <small class="text-body-secondary" style="font-size: 0.75rem;">บทคัดย่อ</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-body-tertiary">
                                    <div class="fw-bold fs-6 text-success">{{ number_format($project->pro_count_full ?? 0) }}</div>
                                    <small class="text-body-secondary" style="font-size: 0.75rem;">รายงาน</small>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Shortcuts -->
                        <div class="d-grid gap-2">
                            @can('update', $project)
                            <button type="button" class="btn btn-outline-warning btn-sm d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#projectStatusModal">
                                <span><i class="bi bi-flag-fill me-2"></i> ปรับปรุงสถานะโครงการ</span>
                                @if($project->status)
                                    <span class="badge" style="background-color: {{ $project->status->ps_color ?? '#6c757d' }}; color: #fff;">
                                        {{ $project->status->ps_name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">ยังไม่ระบุ</span>
                                @endif
                            </button>
                            @endcan
                            <button type="button" class="btn btn-outline-success btn-sm d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#researcherModal">
                                <span><i class="bi bi-people-fill me-2"></i> จัดการคณะผู้วิจัย</span>
                                <span class="badge bg-success">{{ $project->rdbProjectWorks->count() }} ท่าน</span>
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#projectFilesModal">
                                <span><i class="bi bi-file-earmark-arrow-up-fill me-2"></i> จัดการไฟล์โครงการ</span>
                                <span class="badge bg-primary">{{ ($project->pro_abstract_file ? 1 : 0) + ($project->pro_file ? 1 : 0) + $project->files->count() }} ไฟล์</span>
                            </button>
                        </div>
                    </div>

                    <!-- System Info Footer -->
                    <div class="card-footer bg-body-tertiary py-2 px-3 small text-body-secondary">
                        @php
                            $createdBy = $project->user_created ? \App\Models\User::find($project->user_created) : null;
                            $updatedBy = $project->user_updated ? \App\Models\User::find($project->user_updated) : null;
                            $createdByName = $createdBy?->researcher ? ($createdBy->researcher->researcher_fname . ' ' . $createdBy->researcher->researcher_lname) : ($createdBy?->username ?? '-');
                            $updatedByName = $updatedBy?->researcher ? ($updatedBy->researcher->researcher_fname . ' ' . $updatedBy->researcher->researcher_lname) : ($updatedBy?->username ?? '-');
                        @endphp
                        <div><i class="bi bi-clock me-1"></i> สร้าง: {{ \App\Helpers\ThaiDateHelper::formatDateTime($project->created_at) }} ({{ $createdByName }})</div>
                        @if($project->updated_at)
                        <div><i class="bi bi-pencil me-1"></i> ปรับปรุง: {{ \App\Helpers\ThaiDateHelper::formatDateTime($project->updated_at) }} ({{ $updatedByName }})</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Tabs (Details, Researchers, Files, Related Outputs) -->
            <div class="col-lg-8">
                <div class="card shadow-sm border mb-4">
                    <div class="card-header bg-body-tertiary border-bottom pb-0 pt-3">
                        <ul class="nav nav-tabs card-header-tabs" id="projectTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold" id="details-tab" data-bs-toggle="tab" data-bs-target="#tab-details" type="button" role="tab" aria-selected="true">
                                    <i class="bi bi-info-circle me-1"></i> ข้อมูล & บทคัดย่อ
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold" id="researchers-tab" data-bs-toggle="tab" data-bs-target="#tab-researchers" type="button" role="tab" aria-selected="false">
                                    <i class="bi bi-people me-1"></i> คณะผู้วิจัย
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">{{ $project->rdbProjectWorks->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold" id="files-tab" data-bs-toggle="tab" data-bs-target="#tab-files" type="button" role="tab" aria-selected="false">
                                    <i class="bi bi-folder-check me-1"></i> เอกสารโครงการ
                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">{{ ($project->pro_abstract_file ? 1 : 0) + ($project->pro_file ? 1 : 0) + $project->files->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold" id="related-tab" data-bs-toggle="tab" data-bs-target="#tab-related" type="button" role="tab" aria-selected="false">
                                    <i class="bi bi-diagram-3 me-1"></i> ผลงานที่เกี่ยวข้อง
                                    <span class="badge bg-info-subtle text-info border border-info-subtle ms-1">{{ $project->publisheds->count() + $project->dips->count() + $project->utilizations->count() }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content" id="projectTabContent">
                            <!-- Tab 1: รายละเอียด & บทคัดย่อ -->
                            <div class="tab-pane fade show active" id="tab-details" role="tabpanel" aria-labelledby="details-tab">
                                <h4 class="text-primary fw-bold mb-2">{{ strip_tags($project->pro_nameTH) }}</h4>
                                @if($project->pro_nameEN)
                                    <h5 class="text-body-secondary mb-4">{{ strip_tags($project->pro_nameEN) }}</h5>
                                @endif

                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-sm align-middle">
                                        <tbody>
                                            <tr>
                                                <th style="width: 25%;" class="bg-body-tertiary">สถานะโครงการ:</th>
                                                <td colspan="3">
                                                    <div class="d-flex align-items-center gap-2">
                                                        @if($project->status)
                                                            <span class="badge px-3 py-1" style="background-color: {{ $project->status->ps_color ?? '#6c757d' }}; color: #fff;">
                                                                <i class="bi bi-flag-fill me-1"></i> {{ $project->status->ps_name }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary px-3 py-1">ยังไม่ระบุสถานะ</span>
                                                        @endif
                                                        @can('update', $project)
                                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#projectStatusModal">
                                                            <i class="bi bi-pencil-square me-1"></i> ปรับปรุงสถานะ
                                                        </button>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 25%;" class="bg-body-tertiary">ประเภททุน:</th>
                                                <td>
                                                    {{ $project->type->pt_name ?? '-' }}
                                                    @if($project->typeSub)
                                                        <small class="text-body-secondary">({{ $project->typeSub->pts_name }})</small>
                                                    @endif
                                                </td>
                                                <th style="width: 25%;" class="bg-body-tertiary">กลุ่มโครงการ:</th>
                                                <td>{{ $project->group->group_name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-body-tertiary">ยุทธศาสตร์:</th>
                                                <td colspan="3">{{ $project->strategic->strategic_name ?? '-' }}</td>
                                            </tr>
                                            @if($project->pro_keyword)
                                            <tr>
                                                <th class="bg-body-tertiary">คำสำคัญ:</th>
                                                <td colspan="3">
                                                    @foreach(explode(',', $project->pro_keyword) as $keyword)
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1 mb-1">{{ trim($keyword) }}</span>
                                                    @endforeach
                                                </td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                @if($project->pro_abstract)
                                <div class="mt-4">
                                    <h6 class="fw-bold border-bottom pb-2 mb-3">
                                        <i class="bi bi-file-text me-1 text-primary"></i> บทคัดย่อ (Abstract)
                                    </h6>
                                    @php
                                        $separator = '<br><br><br><br>';
                                        if (!str_contains($project->pro_abstract, $separator) && str_contains($project->pro_abstract, '<br><br><br>')) {
                                            $separator = '<br><br><br>';
                                        }
                                        $abstractParts = explode($separator, $project->pro_abstract);
                                    @endphp

                                    @if(!empty($abstractParts[0]))
                                    <div class="mb-3">
                                        <span class="badge bg-primary mb-2">บทคัดย่อภาษาไทย</span>
                                        <div class="p-3 rounded border bg-body-tertiary" style="line-height: 1.8; overflow-wrap: break-word;">
                                            {!! strip_tags($abstractParts[0], '<p><br><b><strong><i><em><u>') !!}
                                        </div>
                                    </div>
                                    @endif

                                    @if(!empty($abstractParts[1]))
                                    <div>
                                        <span class="badge bg-info text-dark mb-2">บทคัดย่อภาษาอังกฤษ</span>
                                        <div class="p-3 rounded border bg-body-tertiary" style="line-height: 1.8; overflow-wrap: break-word;">
                                            {!! strip_tags($abstractParts[1], '<p><br><b><strong><i><em><u>') !!}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>

                            <!-- Tab 2: คณะผู้วิจัย -->
                            <div class="tab-pane fade" id="tab-researchers" role="tabpanel" aria-labelledby="researchers-tab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0 text-body">
                                        <i class="bi bi-people-fill text-success me-2"></i>รายชื่อผู้วิจัยและสัดส่วนงานวิจัย
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#researcherModal">
                                        <i class="bi bi-gear-fill me-1"></i> จัดการคณะผู้วิจัย
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 50%;">ชื่อ-นามสกุล</th>
                                                <th style="width: 30%;">ตำแหน่งในโครงการ</th>
                                                <th style="width: 20%;" class="text-center">สัดส่วน (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($project->rdbProjectWorks as $work)
                                            <tr>
                                                <td>
                                                    @if($work->researcher)
                                                        <a href="{{ route('backend.rdb_researcher.show', $work->researcher->researcher_id) }}" class="text-decoration-none text-body fw-bold">
                                                            {{ $work->researcher->prefix->prefix_nameTH ?? '' }}{{ $work->researcher->researcher_fname }} {{ $work->researcher->researcher_lname }}
                                                        </a>
                                                        <small class="text-body-secondary d-block">
                                                            {{ $work->researcher->department->department_nameTH ?? $work->researcher->department->department_nameEN ?? '-' }}
                                                        </small>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($work->position_id == 1)
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $work->position->position_nameTH ?? '-' }}</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $work->position->position_nameTH ?? '-' }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center fw-bold">{{ $work->ratio }}%</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-body-secondary">ยังไม่มีข้อมูลผู้วิจัยในโครงการ</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        @if($project->rdbProjectWorks->count() > 0)
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="2" class="text-end">รวมสัดส่วนทั้งหมด:</td>
                                                <td class="text-center">
                                                    @php $totalRatio = $project->rdbProjectWorks->sum('ratio'); @endphp
                                                    <span class="badge {{ $totalRatio == 100 ? 'bg-success' : 'bg-warning text-dark' }} fs-6">
                                                        {{ $totalRatio }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 3: เอกสารโครงการ -->
                            <div class="tab-pane fade" id="tab-files" role="tabpanel" aria-labelledby="files-tab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0 text-body">
                                        <i class="bi bi-folder-check text-primary me-2"></i>เอกสารและไฟล์แนบโครงการ
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#projectFilesModal">
                                        <i class="bi bi-upload me-1"></i> อัปโหลด / จัดการไฟล์
                                    </button>
                                </div>

                                <div class="row g-3 mb-4">
                                    <!-- Abstract File Card -->
                                    <div class="col-md-6">
                                        <div class="card h-100 border">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="fw-bold"><i class="bi bi-file-earmark-pdf text-danger me-2 fs-5"></i>ไฟล์บทคัดย่อ (Abstract)</span>
                                                    @if($project->pro_abstract_file)
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">มีไฟล์</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary">ไม่มีไฟล์</span>
                                                    @endif
                                                </div>
                                                @if($project->pro_abstract_file)
                                                    <div class="small text-body-secondary text-truncate mb-3">{{ $project->pro_abstract_file }}</div>
                                                    <a href="{{ route('backend.rdb_project.view_abstract', $project->pro_id) }}" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                                                        <i class="bi bi-eye me-1"></i> เปิดดูไฟล์บทคัดย่อ
                                                    </a>
                                                @else
                                                    <p class="small text-body-secondary mb-0">ยังไม่มีการอัปโหลดไฟล์บทคัดย่อ</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Report File Card -->
                                    <div class="col-md-6">
                                        <div class="card h-100 border">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="fw-bold"><i class="bi bi-file-earmark-richtext text-success me-2 fs-5"></i>รายงานฉบับสมบูรณ์</span>
                                                    <div class="d-flex align-items-center gap-1">
                                                        @if($project->pro_file)
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle">มีไฟล์</span>
                                                            @if($project->pro_file_show)
                                                                <span class="badge bg-success text-white" title="เปิดเผยต่อสาธารณะ"><i class="bi bi-globe me-1"></i>สาธารณะ (Public)</span>
                                                            @else
                                                                <span class="badge bg-secondary text-white" title="ซ่อนเฉพาะผู้ดูแล"><i class="bi bi-lock-fill me-1"></i>ซ่อน (Private)</span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary">ไม่มีไฟล์</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($project->pro_file)
                                                    <div class="small text-body-secondary text-truncate mb-2">{{ $project->pro_file }}</div>
                                                    <div class="d-flex align-items-center justify-content-between small text-body-secondary mb-3 p-2 bg-body-tertiary rounded">
                                                        <span><i class="bi bi-shield-check me-1 text-primary"></i>การแสดงผลสาธารณะ:</span>
                                                        @if($project->pro_file_show)
                                                            <strong class="text-success"><i class="bi bi-eye me-1"></i>เปิดเผยต่อบุคคลทั่วไป (Public)</strong>
                                                        @else
                                                            <strong class="text-secondary"><i class="bi bi-eye-slash me-1"></i>ซ่อนเฉพาะผู้ดูแลระบบ (Private)</strong>
                                                        @endif
                                                    </div>
                                                    <a href="{{ route('backend.rdb_project.view_report', $project->pro_id) }}" target="_blank" class="btn btn-outline-success btn-sm w-100">
                                                        <i class="bi bi-eye me-1"></i> เปิดดูรายงานฉบับสมบูรณ์
                                                    </a>
                                                @else
                                                    <p class="small text-body-secondary mb-0">ยังไม่มีการอัปโหลดรายงานฉบับสมบูรณ์</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Files List -->
                                <h6 class="fw-bold mb-2">เอกสารเพิ่มเติม ({{ $project->files->count() }} ไฟล์)</h6>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ชื่อเอกสาร</th>
                                                <th>หมายเหตุ</th>
                                                <th class="text-center" style="width: 170px;">การแสดงผล (Visibility)</th>
                                                <th class="text-center" style="width: 120px;">ดาวน์โหลด</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($project->files as $file)
                                            <tr>
                                                <td>
                                                    <i class="bi bi-paperclip text-primary me-2"></i>
                                                    <span class="fw-semibold">{{ $file->rf_filesname }}</span>
                                                </td>
                                                <td><small class="text-body-secondary">{{ $file->rf_note ?? '-' }}</small></td>
                                                <td class="text-center">
                                                    @if($file->rf_files_show)
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                            <i class="bi bi-globe me-1"></i> สาธารณะ (Public)
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                                            <i class="bi bi-lock-fill me-1"></i> ซ่อน (Private)
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('backend.rdb_project.file.download', [$project->pro_id, $file->id]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-download"></i> ดาวน์โหลด
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-3 text-body-secondary">ไม่มีเอกสารเพิ่มเติม</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 4: ผลงานที่เกี่ยวข้อง -->
                            <div class="tab-pane fade" id="tab-related" role="tabpanel" aria-labelledby="related-tab">
                                <!-- 1. Publications -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3 text-info">
                                        <i class="bi bi-journal-text me-2"></i>ผลงานตีพิมพ์ที่เชื่อมโยง ({{ $project->publisheds->count() }})
                                    </h6>
                                    @if($project->publisheds->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>ชื่อผลงานตีพิมพ์</th>
                                                    <th>วารสาร / แหล่งตีพิมพ์</th>
                                                    <th class="text-center" style="width: 100px;">ดูข้อมูล</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($project->publisheds as $pub)
                                                <tr>
                                                    <td><span class="fw-semibold">{{ $pub->pub_name }}</span></td>
                                                    <td><small class="text-body-secondary">{{ $pub->pub_name_journal ?? '-' }}</small></td>
                                                    <td class="text-center">
                                                        <a href="{{ route('backend.rdb_published.show', $pub->getKey()) }}" class="btn btn-sm btn-outline-info" target="_blank">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-body-secondary small mb-0"><i class="bi bi-dash-circle me-1"></i> ยังไม่มีผลงานตีพิมพ์ที่เชื่อมโยงกับโครงการนี้</p>
                                    @endif
                                </div>

                                <hr>

                                <!-- 2. Intellectual Property -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3 text-warning">
                                        <i class="bi bi-award me-2"></i>ทรัพย์สินทางปัญญาที่เชื่อมโยง ({{ $project->dips->count() }})
                                    </h6>
                                    @if($project->dips->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>ชื่อผลงานทรัพย์สินฯ</th>
                                                    <th>เลขที่คำขอ</th>
                                                    <th class="text-center" style="width: 100px;">ดูข้อมูล</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($project->dips as $dip)
                                                <tr>
                                                    <td><span class="fw-semibold">{{ $dip->dip_data2_name }}</span></td>
                                                    <td><code>{{ $dip->dip_request_number ?? '-' }}</code></td>
                                                    <td class="text-center">
                                                        <a href="{{ route('backend.rdb_dip.show', $dip->dip_id) }}" class="btn btn-sm btn-outline-warning" target="_blank">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-body-secondary small mb-0"><i class="bi bi-dash-circle me-1"></i> ยังไม่มีทรัพย์สินทางปัญญาที่เชื่อมโยงกับโครงการนี้</p>
                                    @endif
                                </div>

                                <hr>

                                <!-- 3. Utilization -->
                                <div>
                                    <h6 class="fw-bold mb-3 text-danger">
                                        <i class="bi bi-rocket-takeoff me-2"></i>การนำไปใช้ประโยชน์ที่เชื่อมโยง ({{ $project->utilizations->count() }})
                                    </h6>
                                    @if($project->utilizations->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>รายละเอียดการนำไปใช้</th>
                                                    <th>ประเภทการนำไปใช้</th>
                                                    <th class="text-center" style="width: 100px;">ดูข้อมูล</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($project->utilizations as $utz)
                                                <tr>
                                                    <td><span class="fw-semibold">{{ $utz->utz_agency_name ?? $utz->utz_location_address ?? '-' }}</span></td>
                                                    <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $utz->utilizeType->utz_type_name ?? '-' }}</span></td>
                                                    <td class="text-center">
                                                        <a href="{{ route('backend.rdbprojectutilize.show', $utz->utz_id) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-body-secondary small mb-0"><i class="bi bi-dash-circle me-1"></i> ยังไม่มีข้อมูลการนำไปใช้ประโยชน์ที่เชื่อมโยงกับโครงการนี้</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Status Modal -->
        <div class="modal fade" id="projectStatusModal" tabindex="-1" aria-labelledby="projectStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow border-0">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="projectStatusModalLabel">
                            <i class="bi bi-flag-fill me-2"></i> ปรับปรุงสถานะโครงการวิจัย
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('backend.rdb_project.update_status', $project->pro_id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body p-4">
                            <div class="mb-3 text-center p-3 bg-body-tertiary rounded border">
                                <div class="small text-muted mb-1">สถานะโครงการปัจจุบัน:</div>
                                @if($project->status)
                                    <span class="badge px-3 py-2 fs-6" style="background-color: {{ $project->status->ps_color ?? '#6c757d' }}; color: #fff;">
                                        <i class="bi bi-flag-fill me-1"></i> {{ $project->status->ps_name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2 fs-6">ยังไม่ระบุสถานะ</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="project_status_select" class="form-label fw-bold">เลือกสถานะใหม่ <span class="text-danger">*</span></label>
                                <select name="ps_id" id="project_status_select" class="form-select form-select-lg" required>
                                    <option value="">-- เลือกสถานะโครงการ --</option>
                                    @foreach($statuses as $st)
                                        <option value="{{ $st->ps_id }}" 
                                                {{ (string)$project->ps_id === (string)$st->ps_id ? 'selected' : '' }}>
                                            {{ $st->ps_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="alert alert-info py-2 px-3 small mb-0 d-flex align-items-center">
                                <i class="bi bi-info-circle-fill fs-5 me-2 text-info"></i>
                                <div>การปรับปรุงสถานะโครงการจะอัปเดตข้อมูลบนแดชบอร์ด รายงานวิจัย และหน้าแสดงผลทันที</div>
                            </div>
                        </div>
                        <div class="modal-footer bg-body-tertiary">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg me-1"></i> ยกเลิก
                            </button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> บันทึกสถานะใหม่
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
                                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded border bg-body-tertiary">
                                                <div class="text-truncate me-2" style="max-width: 55%;">
                                                    <i class="bi bi-file-earmark-pdf text-danger fs-4 me-2 align-middle"></i>
                                                    <strong class="text-body">{{ $project->pro_file }}</strong>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="form-check form-switch m-0 d-flex align-items-center px-2 py-1 rounded border bg-white" title="ปรับการแสดงผลไฟล์นี้">
                                                        <input class="form-check-input toggle-report-status me-2" type="checkbox" id="toggleReportStatus" data-id="{{ $project->pro_id }}" role="button" {{ $project->pro_file_show ? 'checked' : '' }}>
                                                        <label class="form-check-label small fw-bold mb-0 cursor-pointer" for="toggleReportStatus" id="reportStatusLabel">
                                                            @if($project->pro_file_show)
                                                                <span class="text-success"><i class="bi bi-globe me-1"></i>สาธารณะ (Public)</span>
                                                            @else
                                                                <span class="text-secondary"><i class="bi bi-lock-fill me-1"></i>ซ่อน (Private)</span>
                                                            @endif
                                                        </label>
                                                    </div>
                                                    <a href="{{ asset('storage/uploads/projects/' . $project->pro_file) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center" style="min-width: 70px; height: 32px;">
                                                        <i class="bi bi-eye me-1"></i> ดู
                                                    </a>
                                                    <form action="{{ route('backend.rdb_project.delete_report', $project->pro_id) }}" method="POST" class="d-inline-flex m-0 p-0" onsubmit="return confirm('ยืนยันลบไฟล์รายงานฉบับสมบูรณ์?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center" style="min-width: 70px; height: 32px;">
                                                            <i class="bi bi-trash me-1"></i> ลบ
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                        <form action="{{ route('backend.rdb_project.upload_report', $project->pro_id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="input-group mb-2">
                                                <input type="file" name="pro_file" class="form-control form-control-sm" accept=".pdf" required>
                                                <button type="submit" class="btn btn-success btn-sm px-3">
                                                    <i class="bi bi-upload me-1"></i> {{ $project->pro_file ? 'เปลี่ยนไฟล์รายงาน' : 'อัปโหลดรายงาน' }}
                                                </button>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-2 p-2 bg-light rounded border flex-wrap gap-2">
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" name="pro_file_show" value="1" id="proFileShow" checked>
                                                    <label class="form-check-label small fw-bold text-dark" for="proFileShow">
                                                        <i class="bi bi-globe me-1 text-primary"></i> เปิดให้บุคคลทั่วไปเข้าถึงได้ (Public Visible)
                                                    </label>
                                                </div>
                                                <small class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>อัปโหลดแล้วระบบจะปรับสถานะเป็น "ดำเนินการเสร็จสิ้น" ทันที</small>
                                            </div>
                                            <small class="text-muted d-block mt-1">รองรับเฉพาะไฟล์ PDF ขนาดไม่เกิน 50MB</small>
                                        </form>
                                    </div>
                                </div>

                                <!-- Additional Files -->
                                <div class="card mt-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-paperclip"></i> ไฟล์เพิ่มเติม (Additional Files)</span>
                                        <span class="badge bg-info">{{ $project->files->count() }} ไฟล์</span>
                                    </div>
                                    <div class="card-body">
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
                                                    <div class="row g-2 mt-2">
                                                        <div class="col-12">
                                                            <div class="form-check form-switch p-2 bg-light rounded border">
                                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="rf_files_show" value="1" id="rfFilesShow" checked>
                                                                <label class="form-check-label small fw-bold text-dark" for="rfFilesShow">
                                                                    <i class="bi bi-globe me-1 text-primary"></i> เปิดให้บุคคลทั่วไปดาวน์โหลดเอกสารนี้ได้ (Public Visible)
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- File List Table -->
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover align-middle table-sm">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>ชื่อเอกสาร</th>
                                                        <th>หมายเหตุ</th>
                                                        <th class="text-center" style="width: 150px;">สถานะการแสดงผล</th>
                                                        <th class="text-center" style="width: 100px;">จัดการ</th>
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
                                                                        if(strlen($fname) > 40) {
                                                                            $ext = pathinfo($fname, PATHINFO_EXTENSION);
                                                                            $nameOnly = pathinfo($fname, PATHINFO_FILENAME);
                                                                            $shortName = substr($nameOnly, 0, 30) . '...' . substr($nameOnly, -5) . '.' . $ext;
                                                                        } else {
                                                                            $shortName = $fname;
                                                                        }
                                                                    @endphp
                                                                    <a href="{{ route('backend.rdb_project.file.download', [$project->pro_id, $file->id]) }}" target="_blank" class="text-reset text-decoration-none">
                                                                        <i class="bi bi-paperclip"></i> {{ $shortName }}
                                                                    </a>
                                                                </small>
                                                            </td>
                                                            <td>{{ $file->rf_note ?? '-' }}</td>
                                                            <td class="text-center">
                                                                <div class="form-check form-switch d-inline-flex align-items-center gap-1 m-0">
                                                                    <input class="form-check-input toggle-file-status" type="checkbox" data-pid="{{ $project->pro_id }}" data-fid="{{ $file->id }}" id="toggle_file_{{ $file->id }}" role="button" {{ $file->rf_files_show ? 'checked' : '' }}>
                                                                    <label class="form-check-label small mb-0 fw-semibold file-status-label-{{ $file->id }}" for="toggle_file_{{ $file->id }}">
                                                                        @if($file->rf_files_show)
                                                                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-globe me-1"></i>สาธารณะ</span>
                                                                        @else
                                                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><i class="bi bi-lock-fill me-1"></i>ซ่อน</span>
                                                                        @endif
                                                                    </label>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="btn-group btn-group-sm">
                                                                    <button class="btn btn-outline-warning edit-file-btn"
                                                                        data-fid="{{ $file->id }}"
                                                                        data-name="{{ $file->rf_filesname }}"
                                                                        data-note="{{ $file->rf_note }}"
                                                                        data-show="{{ $file->rf_files_show ? 1 : 0 }}"
                                                                        data-filename="{{ $file->rf_files }}"
                                                                        data-action="{{ route('backend.rdb_project.file.update', [$project->pro_id, $file->id]) }}"
                                                                        title="แก้ไข">
                                                                        <i class="bi bi-pencil"></i>
                                                                    </button>
                                                                    <form action="{{ route('backend.rdb_project.file.destroy', [$project->pro_id, $file->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันลบไฟล์นี้?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-outline-danger" title="ลบ">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="4" class="text-center text-muted">ไม่มีไฟล์เพิ่มเติม</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-lg me-1"></i> ปิดหน้าต่าง
                                </button>
                            </div>
                        </div>
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
                        return '<div class="py-1">' + 
                            '<div>' + (data._highlight || escape(data.text)) + '</div>' +
                            '</div>';
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
        const rfFilesShow = document.getElementById('rfFilesShow');
        const currentFileDisplay = document.getElementById('currentFileDisplay');
        const defaultFileAction = "{{ route('backend.rdb_project.file.store', $project->pro_id) }}";

        document.querySelectorAll('.edit-file-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const fid = this.dataset.fid;
                const name = this.dataset.name;
                const note = this.dataset.note;
                const filename = this.dataset.filename;
                const action = this.dataset.action;
                const isShow = this.dataset.show === '1';

                // Set Form to Edit Mode
                fileForm.action = action;
                fileFormMethod.value = "PUT";
                
                fileNameInput.value = name;
                fileNoteInput.value = (note && note !== 'null' && note !== 'undefined') ? note : '';
                if(rfFilesShow) rfFilesShow.checked = isShow;
                
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
            if(rfFilesShow) rfFilesShow.checked = true;

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
            const label = document.getElementById('reportStatusLabel');
            
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
                    if (label) {
                        label.innerHTML = data.status == 1 
                            ? '<span class="text-success"><i class="bi bi-globe me-1"></i>สาธารณะ (Public)</span>'
                            : '<span class="text-secondary"><i class="bi bi-lock-fill me-1"></i>ซ่อน (Private)</span>';
                    }
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
                const label = document.querySelector(`.file-status-label-${fid}`);

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
                        if (label) {
                            label.innerHTML = data.status == 1
                                ? '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-globe me-1"></i>สาธารณะ</span>'
                                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><i class="bi bi-lock-fill me-1"></i>ซ่อน</span>';
                        }
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
