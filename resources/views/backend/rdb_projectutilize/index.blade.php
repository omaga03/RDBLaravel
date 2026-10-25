@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item active" aria-current="page">จัดการการใช้ประโยชน์</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-rocket-takeoff me-2 text-primary"></i>จัดการการใช้ประโยชน์ <span class="text-body-secondary small fw-normal">(Research Utilization)</span>
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2 shadow-sm">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($items->total()) }} รายการ
            </span>
            @if(request('trash'))
                <a href="{{ route('backend.rdbprojectutilize.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                    <i class="bi bi-arrow-left me-1.5"></i> รายการปกติ
                </a>
            @else
                <a href="{{ route('backend.rdbprojectutilize.index', ['trash' => 1]) }}" class="btn btn-outline-danger d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                    <i class="bi bi-trash3 me-1.5"></i> ถังขยะ
                </a>
            @endif
            @can('create', \App\Models\RdbProjectUtilize::class)
            <a href="{{ route('backend.rdbprojectutilize.create') }}" class="btn btn-primary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                <i class="bi bi-plus-circle me-1.5"></i> เพิ่มข้อมูลใหม่
            </a>
            @endcan
        </div>
    </div>

    {{-- Search Bar --}}
    <x-search-bar :searchRoute="route('backend.rdbprojectutilize.index')" :exportRoute="route('backend.rdbprojectutilize.export')" :collapsed="true">
        <!-- Search Keyword -->
        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <label class="form-label">คำค้นหา (Keyword)</label>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="พิมพ์ชื่อหน่วยงาน, ผู้นำไปใช้, หรือชื่อโครงการ...">
            </div>
        </div>

        <hr class="my-3">

        <!-- Advanced Filters -->
        <div class="row g-3">
             <div class="col-md-4">
                <label class="form-label">หน่วยงานที่นำไปใช้</label>
                <input type="text" class="form-control" name="utz_department_name" value="{{ request('utz_department_name') }}" placeholder="ชื่อหน่วยงาน...">
            </div>
            <div class="col-md-4">
                <label class="form-label">ผู้นำไปใช้ประโยชน์</label>
                <input type="text" class="form-control" name="utz_leading" value="{{ request('utz_leading') }}" placeholder="ชื่อผู้นำไปใช้...">
            </div>
            <div class="col-md-4">
                <label class="form-label">ประเภทการใช้ประโยชน์</label>
                <select class="form-select" id="s_utz_group" name="utz_group">
                    <option value="">-- คลิกเพื่อเลือก --</option>
                </select>
            </div>
            
            <!-- Location -->
            <div class="col-md-4">
                <label class="form-label">จังหวัด</label>
                <select class="form-select" id="s_changwat" name="changwat">
                    <option value="">-- คลิกเพื่อเลือก --</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">อำเภอ</label>
                <select class="form-select" id="s_amphoe" name="amphoe" {{ !request('changwat') ? 'disabled' : '' }}>
                    <option value="">-- ทั้งหมด --</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">ตำบล</label>
                <select class="form-select" id="s_tambon" name="tambon" {{ !request('amphoe') ? 'disabled' : '' }}>
                    <option value="">-- ทั้งหมด --</option>
                </select>
            </div>
            
            <!-- Date/Year -->
            <div class="col-md-4">
                <label class="form-label">ปี พ.ศ.</label>
                <select class="form-select" id="s_year" name="year_id">
                    <option value="">-- คลิกเพื่อเลือก --</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">ตั้งแต่วันที่</label>
                <input type="text" class="form-control datepicker" id="s_date_start" name="date_start" value="{{ request('date_start') }}" placeholder="เลือกวันที่...">
            </div>
            <div class="col-md-4">
                <label class="form-label">ถึงวันที่</label>
                <input type="text" class="form-control datepicker" id="s_date_end" name="date_end" value="{{ request('date_end') }}" placeholder="เลือกวันที่...">
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการการใช้ประโยชน์งานวิจัย
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2">{{ number_format($items->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-top mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60%;">📅 วันที่ / หน่วยงาน / โครงการอ้างอิง</th>
                            <th style="width: 30%;">ผู้ใช้ประโยชน์</th>
                            <th style="width: 10%;" class="text-center text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="p-3">
                                <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                                    @if($item->workflow_status === 'published')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" title="เผยแพร่แล้ว (ล็อกการแก้ไข)"><i class="bi bi-shield-check"></i> เผยแพร่แล้ว</span>
                                    @elseif($item->workflow_status === 'unlock_requested')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" title="ยื่นขอเปิดสิทธิ์แก้ไขแล้ว"><i class="bi bi-unlock"></i> ยื่นขอแก้ไข</span>
                                    @elseif($item->workflow_status === 'pending_review')
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" title="รอการตรวจสอบ"><i class="bi bi-clock-history"></i> รอตรวจรับ</span>
                                    @elseif($item->workflow_status === 'revision_required')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="ส่งกลับแก้ไข"><i class="bi bi-exclamation-triangle-fill"></i> ส่งกลับแก้ไข</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" title="ฉบับร่าง"><i class="bi bi-pencil"></i> ฉบับร่าง</span>
                                    @endif
                                    @if(isset($item->data_show))
                                        @if($item->data_show)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" title="เผยแพร่สู่สาธารณะ">
                                                <i class="bi bi-eye"></i> สาธารณะ
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" title="ซ่อนไว้ (ไม่แสดงหน้าบ้าน)">
                                                <i class="bi bi-eye-slash"></i> ซ่อน
                                            </span>
                                        @endif
                                    @endif
                                    @if($item->utz_date)
                                        <small class="text-body-secondary">
                                            <i class="bi bi-calendar-event me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($item->utz_date, false, true) }}
                                        </small>
                                    @endif
                                </div>

                                <!-- Department Name -->
                                <div class="fw-bold fs-6 mt-1">
                                    <a href="{{ route('backend.rdbprojectutilize.show', $item->utz_id) }}" class="text-decoration-none text-body fw-bold hover-primary text-break">
                                        {{ html_entity_decode($item->utz_department_name ?? '-') }}
                                    </a>
                                </div>

                                @if($item->changwat)
                                    <small class="text-body-secondary d-block mt-1">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        @if($item->changwat->tambon_t)
                                            ต.{{ preg_replace('/^ต\./', '', $item->changwat->tambon_t) }}
                                        @endif
                                        @if($item->changwat->amphoe_t)
                                            อ.{{ preg_replace('/^อ\./', '', $item->changwat->amphoe_t) }}
                                        @endif
                                        @if($item->changwat->changwat_t)
                                            จ.{{ preg_replace('/^จ\./', '', $item->changwat->changwat_t) }}
                                        @endif
                                    </small>
                                @endif

                                <!-- Project Reference -->
                                @if($item->project)
                                    <div class="mt-2 p-2 bg-body-tertiary rounded border-start border-3 border-info">
                                        <div class="small fw-bold text-body-secondary mb-1">โครงการอ้างอิง:</div>
                                        <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body fw-bold hover-primary small text-break d-block">
                                            <i class="bi bi-folder-symlink me-1 text-primary"></i> {{ Str::limit($item->project->pro_nameTH, 80) }}
                                        </a>
                                        @php
                                            $leader = $item->project->rdbProjectWorks->where('position_id', 1)->first();
                                        @endphp
                                        @if($leader && $leader->researcher)
                                            @php
                                                $canManageUtzLeader = auth()->user()->hasRole(['Admin', 'The Creator', 'Admin Research', 'Admin Utilize', 'Utilize']) || auth()->user()->id === 129;
                                                $isSelfUtzLeader = (int)auth()->user()->researcher_id === (int)$leader->researcher->researcher_id;
                                            @endphp
                                            <div class="small text-body-secondary mt-1">
                                                <i class="bi bi-person-circle text-primary me-1"></i>
                                                @if($canManageUtzLeader || $isSelfUtzLeader)
                                                    <a href="{{ route('backend.rdb_researcher.show', $leader->researcher->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body hover-primary">
                                                        {{ $leader->researcher->researcher_fname }} {{ $leader->researcher->researcher_lname }}
                                                        @if($isSelfUtzLeader)
                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.7rem;">(คุณ)</span>
                                                        @endif
                                                    </a>
                                                @else
                                                    <span class="text-body fw-bold">
                                                        {{ $leader->researcher->researcher_fname }} {{ $leader->researcher->researcher_lname }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Utilize Types Badges -->
                                @if($item->utz_group)
                                    <div class="mt-2 d-flex flex-wrap gap-1">
                                        @php
                                            $groupIds = array_map('trim', explode(',', $item->utz_group));
                                            $types = \App\Models\RdbProjectUtilizeType::whereIn('utz_type_id', $groupIds)->get();
                                        @endphp
                                        @foreach($types as $type)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.75rem;">
                                                {{ $type->utz_typr_name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="p-3">
                                <!-- Leading Person -->
                                @if($item->utz_leading)
                                    <div class="fw-semibold text-body">
                                        <i class="bi bi-person me-1"></i>{{ $item->utz_leading }}
                                    </div>
                                @else
                                    <span class="text-body-secondary">-</span>
                                @endif

                                <!-- Position -->
                                @if($item->utz_leading_position)
                                    <small class="text-body-secondary d-block mt-1">
                                        <i class="bi bi-briefcase me-1"></i>{{ $item->utz_leading_position }}
                                    </small>
                                @endif

                                @if($item->utz_budget && floatval($item->utz_budget) > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle mt-2">
                                        ฿{{ number_format($item->utz_budget, 2) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center p-3 text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('backend.rdbprojectutilize.show', $item->utz_id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('update', $item)
                                    <a href="{{ route('backend.rdbprojectutilize.edit', $item->utz_id) }}" class="btn btn-outline-secondary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @else
                                        @if($item->workflow_status === 'published' && auth()->user()->can('requestUnlock', $item))
                                            <button type="button" class="btn btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#requestUnlockModal" data-id="{{ $item->utz_id }}" data-type="utilize" data-title="การนำไปใช้ประโยชน์: {{ $item->utz_department_name }}" title="ผลงานนี้เผยแพร่แล้ว ยื่นขอเปิดสิทธิ์แก้ไขจาก Admin">
                                                <i class="bi bi-unlock"></i> ขอแก้ไข
                                            </button>
                                        @elseif($item->workflow_status === 'unlock_requested')
                                            <span class="btn btn-sm btn-outline-warning disabled" title="ยื่นขอเปิดสิทธิ์แก้ไขแล้ว รอ Admin อนุมัติ">
                                                <i class="bi bi-hourglass-split"></i> รออนุมัติ
                                            </span>
                                        @endif
                                    @endcan

                                    @if(auth()->user()->can('approveUnlock', $item) && $item->workflow_status === 'unlock_requested')
                                        <form action="{{ route('backend.workflow.approve_unlock', ['type' => 'utilize', 'id' => $item->utz_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('อนุมัติให้แก้ไข และย้ายผลงานนี้ไปฉบับร่างหรือไม่?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="อนุมัติให้แก้ไข (ย้ายไปฉบับร่าง)"><i class="bi bi-unlock"></i> ปลดล็อก</button>
                                        </form>
                                    @endif
                                    @if((int)$item->data_show === 0)
                                        @can('delete', $item)
                                        <form action="{{ route('backend.rdbprojectutilize.restore', $item->utz_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันกู้คืนข้อมูลการนำไปใช้ประโยชน์นี้จากถังขยะ?');">
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
                                <i class="bi bi-inbox fs-2 d-block mb-3 opacity-50"></i>
                                ไม่พบข้อมูลการใช้ประโยชน์
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($items->hasPages())
                <div class="d-flex justify-content-center p-4">
                    {{ $items->withQueryString()->links() }}
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
                        <textarea class="form-control" id="unlockReason" name="reason" rows="4" placeholder="ระบุเหตุผลในการขอแก้ไขข้อมูล..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-sm btn-warning px-3 fw-bold">
                        <i class="bi bi-send me-1"></i> ส่งคำขอเปิดสิทธิ์แก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
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

    const sChangwat = document.getElementById('s_changwat');
    const sAmphoe = document.getElementById('s_amphoe');
    const sTambon = document.getElementById('s_tambon');
    
    // Old values for restore
    const oldChangwat = "{{ request('changwat') }}";
    const oldAmphoe = "{{ request('amphoe') }}";
    const oldTambon = "{{ request('tambon') }}";

    // Utilize Type Dropdown (simple single select)
    const sUtzGroup = document.getElementById('s_utz_group');
    const oldUtzGroup = "{{ request('utz_group') }}";
    let utzTypesLoaded = false;

    // AJAX: Fetch Utilize Types (on first click)
    async function fetchUtzTypes(selectedVal = '') {
        if (utzTypesLoaded) return;
        
        sUtzGroup.innerHTML = '<option value="">กำลังโหลด...</option>';
        
        try {
            const response = await fetch(`{{ route('backend.rdbprojectutilize.search_utilize_types') }}`);
            const data = await response.json();
            
            sUtzGroup.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            data.forEach(t => {
                const option = new Option(t.text, t.value, false, t.value == selectedVal);
                sUtzGroup.add(option);
            });
            utzTypesLoaded = true;
        } catch (e) {
            sUtzGroup.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        }
    }

    // Load utz types on click/focus
    sUtzGroup.addEventListener('focus', () => fetchUtzTypes(oldUtzGroup));
    sUtzGroup.addEventListener('click', () => fetchUtzTypes(oldUtzGroup));

    // Init utz types if old value exists
    if (oldUtzGroup) {
        fetchUtzTypes(oldUtzGroup);
    }
    
    let provincesLoaded = false;

    // AJAX: Fetch Provinces (on first click)
    async function fetchProvinces(selectedVal = '') {
        if (provincesLoaded) return;
        
        sChangwat.innerHTML = '<option value="">กำลังโหลด...</option>';
        
        try {
            const response = await fetch(`{{ route('backend.rdbprojectutilize.search_provinces') }}`);
            const data = await response.json();
            
            sChangwat.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            data.forEach(p => {
                const option = new Option(p.text, p.value, false, p.value === selectedVal);
                sChangwat.add(option);
            });
            provincesLoaded = true;
        } catch (e) {
            sChangwat.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        }
    }

    // Load provinces on click/focus
    sChangwat.addEventListener('focus', () => fetchProvinces(oldChangwat));
    sChangwat.addEventListener('click', () => fetchProvinces(oldChangwat));

    // Year dropdown
    const sYear = document.getElementById('s_year');
    const oldYear = "{{ request('year_id') }}";
    let yearsLoaded = false;

    // AJAX: Fetch Years (on first click)
    async function fetchYears(selectedVal = '') {
        if (yearsLoaded) return;
        
        sYear.innerHTML = '<option value="">กำลังโหลด...</option>';
        
        try {
            const response = await fetch(`{{ route('backend.rdbprojectutilize.search_years') }}`);
            const data = await response.json();
            
            sYear.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            data.forEach(y => {
                const option = new Option(y.text, y.value, false, y.value == selectedVal);
                sYear.add(option);
            });
            yearsLoaded = true;
        } catch (e) {
            sYear.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        }
    }

    // Load years on click/focus
    sYear.addEventListener('focus', () => fetchYears(oldYear));
    sYear.addEventListener('click', () => fetchYears(oldYear));

    // Init years if old value exists
    if (oldYear) {
        fetchYears(oldYear);
    }

    // AJAX: Fetch Amphoes
    async function fetchAmphoes(changwat, selectedVal = '') {
        sAmphoe.innerHTML = '<option value="">กำลังโหลด...</option>';
        sAmphoe.disabled = true;
        
        if (!changwat) {
            sAmphoe.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            return;
        }

        try {
            const response = await fetch(`{{ route('backend.rdbprojectutilize.search_amphoes') }}?changwat=${encodeURIComponent(changwat)}`);
            const data = await response.json();
            
            sAmphoe.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            data.forEach(a => {
                const option = new Option(a.text, a.value, false, a.value === selectedVal);
                sAmphoe.add(option);
            });
            sAmphoe.disabled = false;
        } catch (e) {
            sAmphoe.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        }
    }

    // AJAX: Fetch Tambons
    async function fetchTambons(changwat, amphoe, selectedVal = '') {
        sTambon.innerHTML = '<option value="">กำลังโหลด...</option>';
        sTambon.disabled = true;
        
        if (!changwat || !amphoe) {
            sTambon.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            return;
        }

        try {
            const response = await fetch(`{{ route('backend.rdbprojectutilize.search_tambons') }}?changwat=${encodeURIComponent(changwat)}&amphoe=${encodeURIComponent(amphoe)}`);
            const data = await response.json();
            
            sTambon.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            data.forEach(t => {
                const option = new Option(t.text, t.value, false, t.value === selectedVal);
                sTambon.add(option);
            });
            sTambon.disabled = false;
        } catch (e) {
            sTambon.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        }
    }

    // Event: Changwat change
    sChangwat.addEventListener('change', function() {
        fetchAmphoes(this.value);
        sTambon.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        sTambon.disabled = true;
    });

    // Event: Amphoe change
    sAmphoe.addEventListener('change', function() {
        fetchTambons(sChangwat.value, this.value);
    });

    // Init old values (restore from URL params)
    if (oldChangwat) {
        // First load provinces, then select the old value
        fetchProvinces(oldChangwat).then(() => {
            fetchAmphoes(oldChangwat, oldAmphoe).then(() => {
                if (oldAmphoe) {
                    fetchTambons(oldChangwat, oldAmphoe, oldTambon);
                }
            });
        });
    }

    // Thai Datepicker
    if (typeof initThaiFlatpickr === 'function') {
        initThaiFlatpickr('#s_date_start', {
            onChange: function(selectedDates, dateStr, instance) {
                const endInput = document.getElementById('s_date_end');
                if (endInput && endInput._flatpickr) {
                    endInput._flatpickr.set('minDate', selectedDates[0] || null);
                    if (selectedDates.length > 0 && !endInput.value) {
                        endInput._flatpickr.setDate(selectedDates[0]);
                    }
                }
            }
        });
        initThaiFlatpickr('#s_date_end');
    }
});
</script>
@endpush
@endsection
