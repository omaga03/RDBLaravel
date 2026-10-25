@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item active" aria-current="page">จัดการโครงการวิจัย</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-folder2-open me-2 text-primary"></i>จัดการโครงการวิจัย <span class="text-body-secondary small fw-normal">(Research Projects)</span>
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2 shadow-sm">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($projects->total()) }} โครงการ
            </span>
            @if(request('trash'))
                <a href="{{ route('backend.rdb_project.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                    <i class="bi bi-arrow-left me-1.5"></i> รายการปกติ
                </a>
            @else
                <a href="{{ route('backend.rdb_project.index', ['trash' => 1]) }}" class="btn btn-outline-danger d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                    <i class="bi bi-trash3 me-1.5"></i> ถังขยะ
                </a>
            @endif
            @can('create', \App\Models\RdbProject::class)
            <a href="{{ route('backend.rdb_project.create') }}" class="btn btn-primary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                <i class="bi bi-plus-circle me-1.5"></i> เพิ่มโครงการใหม่
            </a>
            @endcan
        </div>
    </div>

    <x-search-bar :searchRoute="route('backend.rdb_project.index')" :exportRoute="route('backend.rdb_project.export')" :collapsed="true" 
        :simplePlaceholder="'ค้นหาจาก: ชื่อโครงการ, รหัส, คำสำคัญ, นักวิจัย, ประเภททุน, ปีงบประมาณ, หน่วยงาน, บทคัดย่อ'">
        <div class="row g-3">
            {{-- Row 1: ปีงบประมาณ, ประเภททุน, ประเภทย่อย (Cascading) --}}
            <div class="col-md-2">
                <label class="form-label">ปีงบประมาณ</label>
                <select class="form-select" id="search_year_id" name="year_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($years as $year)
                        <option value="{{ $year->year_id }}" {{ request('year_id') == $year->year_id ? 'selected' : '' }}>
                            {{ $year->year_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">ประเภททุนอุดหนุนการวิจัย</label>
                <select class="form-select" id="search_pt_id" name="pt_id">
                    <option value="">-- เลือกปีงบประมาณก่อน --</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">ประเภทโครงการย่อย</label>
                <select class="form-select" id="search_pts_id" name="pts_id">
                    <option value="">-- เลือกประเภททุนก่อน --</option>
                </select>
            </div>

            {{-- Row 2: รหัสโครงการ, กลุ่มโครงการ, งบประมาณ --}}
            <div class="col-md-3">
                <label class="form-label">รหัสโครงการ</label>
                <input type="text" class="form-control" name="pro_code" value="{{ request('pro_code') }}" placeholder="ระบุรหัสโครงการ...">
            </div>
            <div class="col-md-3">
                <label class="form-label">กลุ่มโครงการ</label>
                <select class="form-select" name="pgroup_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->pgroup_id }}" {{ request('pgroup_id') == $group->pgroup_id ? 'selected' : '' }}>
                            {{ $group->pgroup_nameTH }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">งบประมาณ (เริ่มต้น)</label>
                <input type="number" class="form-control" name="budget_min" value="{{ request('budget_min') }}" placeholder="ต่ำสุด" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label">งบประมาณ (สิ้นสุด)</label>
                <input type="number" class="form-control" name="budget_max" value="{{ request('budget_max') }}" placeholder="สูงสุด" min="0">
            </div>

            {{-- Row 3: ชื่อโครงการ (Searchable TomSelect Autocomplete like Researcher) --}}
            <div class="col-md-12">
                <label class="form-label d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-folder2 text-primary me-1"></i> ชื่อโครงการวิจัย (ภาษาไทย-อังกฤษ)</span>
                    <span class="badge bg-secondary-subtle text-body-secondary" style="font-size: 0.7rem;">พิมพ์เพื่อค้นหาชื่อโครงการ</span>
                </label>
                <select class="form-select" id="search_pro_id" name="pro_id"></select>
                <input type="hidden" name="pro_nameTH" id="search_pro_nameTH" value="{{ request('pro_nameTH') }}">
            </div>

            {{-- Row 3.5: Multi-Keyword Title Search (กลุ่มคำคั่นด้วยช่องว่าง + เจอทั้งหมด / เจอบางคำ) --}}
            <div class="col-md-12">
                <div class="p-3 rounded-3 border bg-body-tertiary">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1.5 gap-2">
                        <label class="form-label mb-0 fw-semibold text-body" style="font-size: 0.88rem;">
                            <i class="bi bi-fonts text-primary me-1"></i> ค้นหาชื่อเรื่องแบบกลุ่มคำ (คั่นด้วยช่องว่าง)
                        </label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="title_keywords_mode" id="b_pro_mode_all" value="all" 
                                    {{ request('title_keywords_mode', 'all') === 'all' ? 'checked' : '' }}>
                                <label class="form-check-label small fw-medium" for="b_pro_mode_all">
                                    <i class="bi bi-check-all text-success me-1"></i>เจอทั้งหมด (ตรงทุกคำ - AND)
                                </label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="title_keywords_mode" id="b_pro_mode_any" value="any" 
                                    {{ request('title_keywords_mode') === 'any' ? 'checked' : '' }}>
                                <label class="form-check-label small fw-medium" for="b_pro_mode_any">
                                    <i class="bi bi-intersect text-primary me-1"></i>เจอบางคำ (ตรงคำใดคำหนึ่ง - OR)
                                </label>
                            </div>
                        </div>
                    </div>
                    <input type="text" class="form-control" name="title_keywords" value="{{ request('title_keywords') }}" 
                        placeholder="เช่น เกษตร ชุมชน พลังงาน (พิมพ์หลายคำคั่นด้วยช่องว่าง)...">
                    <div class="form-text small text-body-secondary mt-1">
                        <i class="bi bi-info-circle me-1"></i>ระบบจะค้นหาชื่อโครงการวิจัยที่มีกลุ่มคำดังกล่าวตามเงื่อนไขที่เลือก (เจอทั้งหมด หรือ เจอบางคำ)
                    </div>
                </div>
            </div>

            {{-- Row 4: นักวิจัย (TomSelect), หน่วยงาน --}}
            <div class="col-md-6">
                <label class="form-label">นักวิจัย</label>
                <select class="form-select" id="search_researcher_id" name="researcher_id"></select>
                <input type="hidden" name="researcher_name" id="search_researcher_name" value="{{ request('researcher_name') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">หน่วยงาน/คณะ</label>
                <select class="form-select" name="department_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                            {{ $dept->department_nameTH ?? $dept->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Row 5: บทคัดย่อ --}}
            <div class="col-md-12">
                <label class="form-label">บทคัดย่อ (ภาษาไทย-อังกฤษ)</label>
                <input type="text" class="form-control" name="pro_abstract" value="{{ request('pro_abstract') }}" placeholder="ระบุคำค้นหาในบทคัดย่อ...">
            </div>

            {{-- Row 6: คำสำคัญ, วันที่ --}}
            <div class="col-md-4">
                <label class="form-label">คำสำคัญ</label>
                <input type="text" class="form-control" name="pro_keyword" value="{{ request('pro_keyword') }}" placeholder="ระบุคำสำคัญ...">
            </div>
            <div class="col-md-4">
                <label class="form-label">วันที่เริ่มต้น</label>
                <input type="text" class="form-control datepicker" id="search_date_start" name="date_start" value="{{ request('date_start') }}" placeholder="วว/ดด/ปปปป">
            </div>
            <div class="col-md-4">
                <label class="form-label">วันที่สิ้นสุด</label>
                <input type="text" class="form-control datepicker" id="search_date_end" name="date_end" value="{{ request('date_end') }}" placeholder="วว/ดด/ปปปป">
            </div>

            {{-- Row 7: หมายเหตุ, สถานะ --}}
            <div class="col-md-6">
                <label class="form-label">หมายเหตุ</label>
                <input type="text" class="form-control" name="pro_note" value="{{ request('pro_note') }}" placeholder="ระบุหมายเหตุ...">
            </div>
            <div class="col-md-6">
                <label class="form-label">สถานะโครงการ</label>
                <select class="form-select" name="ps_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->ps_id }}" {{ request('ps_id') == $status->ps_id ? 'selected' : '' }}>
                            {{ $status->ps_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการโครงการวิจัย
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2">{{ number_format($projects->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-top mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60%;">ชื่อโครงการ</th>
                            <th style="width: 25%;">นักวิจัย / สังกัด</th>
                            <th style="width: 15%;" class="text-center text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <td class="p-3">
                                <div class="fw-bold mb-1">
                                    @if($project->pro_id)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border me-1">#{{ $project->pro_id }}</span>
                                    @endif
                                    <a href="{{ route('backend.rdb_project.show', $project->pro_id) }}" class="text-decoration-none text-body fw-bold hover-primary text-break">
                                        {{ strip_tags($project->pro_nameTH) }}
                                    </a>
                                </div>
                                    @if($project->workflow_status === 'published')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" title="เผยแพร่แล้ว (ล็อกการแก้ไข)"><i class="bi bi-shield-check"></i> เผยแพร่แล้ว</span>
                                    @elseif($project->workflow_status === 'unlock_requested')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" title="ยื่นขอเปิดสิทธิ์แก้ไขแล้ว"><i class="bi bi-unlock"></i> ยื่นขอแก้ไข</span>
                                    @elseif($project->workflow_status === 'pending_review')
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" title="รอการตรวจสอบ"><i class="bi bi-clock-history"></i> รอตรวจรับ</span>
                                    @elseif($project->workflow_status === 'revision_required')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="ส่งกลับแก้ไข"><i class="bi bi-exclamation-triangle-fill"></i> ส่งกลับแก้ไข</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" title="ฉบับร่าง"><i class="bi bi-pencil"></i> ฉบับร่าง</span>
                                    @endif
                                    @if(isset($project->data_show))
                                        @if($project->data_show)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" title="เผยแพร่สู่สาธารณะ">
                                                <i class="bi bi-eye"></i> สาธารณะ
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" title="ซ่อนไว้ (ไม่แสดงหน้าบ้าน)">
                                                <i class="bi bi-eye-slash"></i> ซ่อน
                                            </span>
                                        @endif
                                    @endif
                                    @if($project->year)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">ปี {{ $project->year->year_name }}</span>
                                    @endif
                                    @if($project->type)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">{{ $project->type->pt_name }}</span>
                                    @endif
                                    @if($project->pro_budget)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">฿{{ number_format($project->pro_budget, 0) }}</span>
                                    @endif
                                    @if($project->status)
                                        <span class="badge" style="background-color: {{ $project->status->ps_color ?? '#6c757d' }}; color: #fff;">{{ $project->status->ps_name }}</span>
                                    @endif
                                    @if(request('search') && !empty($project->match_sources))
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" 
                                              data-bs-toggle="tooltip" 
                                              data-bs-placement="top" 
                                              data-bs-html="true"
                                              title="<strong>พบคำค้นหาใน:</strong><br>• {{ implode('<br>• ', $project->match_sources) }}">
                                            <i class="bi bi-search"></i> {{ count($project->match_sources) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3">
                                @if($project->rdbProjectWorks->isNotEmpty())
                                    @php
                                        $leaders = $project->rdbProjectWorks->whereIn('position_id', [1, 2]);
                                        $firstLeader = $leaders->first();
                                        $remainingWorks = $project->rdbProjectWorks->slice(1);
                                        $remainingCount = $remainingWorks->count();
                                        $remainingNames = $remainingWorks->map(function($work) {
                                            return $work->researcher ? $work->researcher->researcher_fname . ' ' . $work->researcher->researcher_lname : '';
                                        })->filter()->implode('<br>• ');
                                    @endphp
                                    @if($firstLeader && $firstLeader->researcher)
                                    <div>
                                        <i class="bi bi-person-circle text-primary me-1"></i>
                                        @php
                                            $canManageLeader = auth()->user()->hasRole(['Admin', 'The Creator', 'Admin Research', 'Admin Project']) || auth()->user()->id === 129;
                                            $isSelfLeader = (int)auth()->user()->researcher_id === (int)$firstLeader->researcher->researcher_id;
                                        @endphp
                                        @if($canManageLeader || $isSelfLeader)
                                            <a href="{{ route('backend.rdb_researcher.show', $firstLeader->researcher->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body fw-bold hover-primary">
                                                {{ $firstLeader->researcher->researcher_fname }} {{ $firstLeader->researcher->researcher_lname }}
                                                @if($isSelfLeader)
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.7rem;">(คุณ)</span>
                                                @endif
                                            </a>
                                        @else
                                            <span class="text-body fw-bold">
                                                {{ $firstLeader->researcher->researcher_fname }} {{ $firstLeader->researcher->researcher_lname }}
                                            </span>
                                        @endif
                                        @if($remainingCount > 0)
                                            <span class="text-body-secondary small ms-1" 
                                                  data-bs-toggle="tooltip" 
                                                  data-bs-placement="top" 
                                                  data-bs-html="true"
                                                  title="<strong>นักวิจัยร่วม:</strong><br>• {{ $remainingNames }}"
                                                  style="cursor: pointer;">(+{{ $remainingCount }})</span>
                                        @endif
                                    </div>
                                    @endif
                                @else
                                    <span class="text-body-secondary">-</span>
                                @endif

                                @if($project->department)
                                    <div class="text-body-secondary mt-1 small">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $project->department->department_nameTH ?? $project->department->department_name }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center p-3 text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('backend.rdb_project.show', $project->pro_id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('update', $project)
                                    <a href="{{ route('backend.rdb_project.edit', $project->pro_id) }}" class="btn btn-outline-secondary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @else
                                        @if($project->workflow_status === 'published' && auth()->user()->can('requestUnlock', $project))
                                            <button type="button" class="btn btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#requestUnlockModal" data-id="{{ $project->pro_id }}" data-type="project" data-title="{{ $project->pro_nameTH }}" title="ผลงานนี้เผยแพร่แล้ว ยื่นขอเปิดสิทธิ์แก้ไขจาก Admin">
                                                <i class="bi bi-unlock"></i> ขอแก้ไข
                                            </button>
                                        @elseif($project->workflow_status === 'unlock_requested')
                                            <span class="btn btn-sm btn-outline-warning disabled" title="ยื่นขอเปิดสิทธิ์แก้ไขแล้ว รอ Admin อนุมัติ">
                                                <i class="bi bi-hourglass-split"></i> รออนุมัติ
                                            </span>
                                        @endif
                                    @endcan

                                    @if(auth()->user()->can('approveUnlock', $project) && $project->workflow_status === 'unlock_requested')
                                        <form action="{{ route('backend.workflow.approve_unlock', ['type' => 'project', 'id' => $project->pro_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('อนุมัติให้แก้ไข และย้ายโครงการนี้ไปฉบับร่างหรือไม่?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="อนุมัติให้แก้ไข (ย้ายไปฉบับร่าง)"><i class="bi bi-unlock"></i> ปลดล็อก</button>
                                        </form>
                                    @endif
                                    @if((int)$project->data_show === 0)
                                        @can('delete', $project)
                                        <form action="{{ route('backend.rdb_project.restore', $project->pro_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันกู้คืนโครงการวิจัยนี้จากถังขยะ?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="กู้คืนข้อมูล"><i class="bi bi-arrow-counterclockwise"></i> กู้คืน</button>
                                        </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-folder-x fs-1 d-block mb-2 opacity-50"></i>
                                ไม่พบข้อมูลโครงการวิจัย
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($projects->hasPages())
            <div class="d-flex justify-content-center p-4">
                {{ $projects->withQueryString()->links() }}
            </div>
            @endif
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
                        <textarea class="form-control" id="unlockReason" name="reason" rows="4" placeholder="ระบุเหตุผล เช่น ต้องการปรับปรุงงบประมาณตามสัญญาเพิ่มเติม, เพิ่มเติมรายชื่อผู้ร่วมวิจัย..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-sm btn-warning text-dark px-3 fw-bold">
                        <i class="bi bi-send me-1"></i> ส่งคำขอเปิดสิทธิ์แก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- TomSelect CDN -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Request Unlock Modal Handler
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

    // =========================================================================
    // TOOLTIP INITIALIZATION (for search results)
    // =========================================================================
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // =========================================================================
    // CASCADING DROPDOWNS: Year -> Type -> SubType
    // =========================================================================
    const yearSelect = document.getElementById('search_year_id');
    const typeSelect = document.getElementById('search_pt_id');
    const subTypeSelect = document.getElementById('search_pts_id');
    
    const preSelectedYearId = "{{ request('year_id') }}";
    const preSelectedTypeId = "{{ request('pt_id') }}";
    const preSelectedSubTypeId = "{{ request('pts_id') }}";

    function loadProjectTypes(yearId, preSelectTypeId = '', preSelectSubTypeId = '') {
        if (!typeSelect) return;
        
        typeSelect.innerHTML = '<option value="">กำลังโหลด...</option>';
        subTypeSelect.innerHTML = '<option value="">-- เลือกประเภททุนก่อน --</option>';

        if (!yearId) {
            typeSelect.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            return;
        }

        fetch('{{ route("backend.rdb_project.search_project_type") }}?year_id=' + yearId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">-- ทั้งหมด --</option>';
                (data.results || []).forEach(item => {
                    const isSelected = String(item.id) === String(preSelectTypeId) ? 'selected' : '';
                    options += `<option value="${item.id}" ${isSelected}>${item.text}</option>`;
                });
                typeSelect.innerHTML = options;

                if (preSelectTypeId) {
                    loadProjectTypeSubs(preSelectTypeId, preSelectSubTypeId);
                }
            })
            .catch(err => {
                console.error(err);
                typeSelect.innerHTML = '<option value="">เกิดข้อผิดพลาด</option>';
            });
    }

    function loadProjectTypeSubs(ptId, preSelectId = '') {
        if (!subTypeSelect) return;
        
        subTypeSelect.innerHTML = '<option value="">กำลังโหลด...</option>';

        if (!ptId) {
            subTypeSelect.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            return;
        }

        fetch('{{ route("backend.rdb_project.search_project_type_sub") }}?pt_id=' + ptId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">-- ทั้งหมด --</option>';
                (data.results || []).forEach(item => {
                    const isSelected = String(item.id) === String(preSelectId) ? 'selected' : '';
                    options += `<option value="${item.id}" ${isSelected}>${item.text}</option>`;
                });
                subTypeSelect.innerHTML = options;
            })
            .catch(err => {
                console.error(err);
                subTypeSelect.innerHTML = '<option value="">เกิดข้อผิดพลาด</option>';
            });
    }

    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            loadProjectTypes(this.value);
        });
        // Initialize with pre-selected values
        if (preSelectedYearId) {
            loadProjectTypes(preSelectedYearId, preSelectedTypeId, preSelectedSubTypeId);
        }
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            loadProjectTypeSubs(this.value);
        });
    }

    // =========================================================================
    // TOMSELECT FOR PROJECT TITLE SEARCH
    // =========================================================================
    const projectSelect = document.getElementById('search_pro_id');
    const projectNameHidden = document.getElementById('search_pro_nameTH');

    if (projectSelect && typeof TomSelect !== 'undefined') {
        const tsProj = new TomSelect(projectSelect, {
            create: false,
            openOnFocus: true,
            persist: false,
            maxOptions: 20,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            placeholder: '-- พิมพ์เพื่อค้นหาชื่อโครงการวิจัย --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("backend.rdb_project.search") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => callback(json.results || json))
                    .catch(() => callback());
            },
            onChange: function(value) {
                const item = this.options[value];
                if (item && projectNameHidden) {
                    projectNameHidden.value = item.text.split(' [')[0];
                } else if (projectNameHidden) {
                    projectNameHidden.value = '';
                }
            },
            render: {
                option: function(data, escape) { return '<div>' + (data._highlight || escape(data.text)) + '</div>'; },
                item: function(data, escape) { return '<div>' + escape(data.text) + '</div>'; },
                no_results: function() { return '<div class="no-results p-2 text-muted">ไม่พบข้อมูลโครงการ (พิมพ์อย่างน้อย 2 ตัวอักษร)</div>'; }
            }
        });

        const preProId = "{{ request('pro_id') }}";
        const preProName = @json(request('pro_nameTH') ?: '');
        if (preProId || preProName) {
            const val = preProId || 'pre';
            const label = preProName || ('โครงการ #' + preProId);
            tsProj.addOption({ value: val, text: label });
            tsProj.setValue(val);
        }
    }

    // =========================================================================
    // TOMSELECT FOR RESEARCHER SEARCH
    // =========================================================================
    const researcherSelect = document.getElementById('search_researcher_id');
    const researcherNameHidden = document.getElementById('search_researcher_name');
    
    if (researcherSelect && typeof TomSelect !== 'undefined') {
        const ts = new TomSelect(researcherSelect, {
            create: false,
            openOnFocus: true,
            persist: false,
            maxOptions: 15,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            placeholder: '-- พิมพ์เพื่อค้นหานักวิจัย --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                
                fetch('{{ route("backend.rdb_project.search_researchers") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => callback(json.results || json))
                    .catch(() => callback());
            },
            onChange: function(value) {
                // Update hidden field with researcher name for search
                const item = this.options[value];
                if (item && researcherNameHidden) {
                    researcherNameHidden.value = item.text.split(' [')[0]; // Get name without department
                } else if (researcherNameHidden) {
                    researcherNameHidden.value = '';
                }
            },
            render: {
                option: function(data, escape) {
                    return '<div>' + (data._highlight || escape(data.text)) + '</div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                },
                no_results: function() {
                    return '<div class="no-results p-2 text-muted">ไม่พบข้อมูล (พิมพ์อย่างน้อย 2 ตัวอักษร)</div>';
                }
            }
        });

        // Pre-populate if there's a researcher_name value
        const preResearcherName = "{{ request('researcher_name') }}";
        if (preResearcherName) {
            // Add a temporary option for the pre-selected value
            ts.addOption({ value: 'pre', text: preResearcherName });
            ts.setValue('pre');
        }
    }

    // =========================================================================
    // FLATPICKR DATE PICKERS WITH VALIDATION
    // =========================================================================
    if (typeof initThaiFlatpickr !== 'undefined') {
        let searchEndDatePicker = null;
        
        const searchStartDatePicker = initThaiFlatpickr("#search_date_start", {
            onChange: function(selectedDates, dateStr, instance) {
                if (searchEndDatePicker) {
                    searchEndDatePicker.set('minDate', dateStr || null);
                    // Clear end date if it's now before start date
                    const currentEndDate = searchEndDatePicker.selectedDates[0];
                    if (currentEndDate && selectedDates.length > 0 && currentEndDate < selectedDates[0]) {
                        searchEndDatePicker.clear();
                    }
                }
            }
        });

        searchEndDatePicker = initThaiFlatpickr("#search_date_end", {
            onReady: function(selectedDates, dateStr, instance) {
                // Set min date to start date value if exists
                const startVal = document.getElementById('search_date_start').value;
                if (startVal) {
                    instance.set('minDate', startVal);
                }
            }
        });
    }
});
</script>
@endpush
