@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item active" aria-current="page">จัดการทรัพย์สินทางปัญญา</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-award me-2 text-primary"></i>จัดการทรัพย์สินทางปัญญา <span class="text-body-secondary small fw-normal">(Intellectual Property)</span>
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2 shadow-sm">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($items->total()) }} รายการ
            </span>
            @can('create', \App\Models\RdbDip::class)
            <a href="{{ route('backend.rdb_dip.create') }}" class="btn btn-primary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                <i class="bi bi-plus-circle me-1.5"></i> เพิ่มข้อมูลใหม่
            </a>
            @endcan
        </div>
    </div>

    {{-- Search Bar --}}
    <x-search-bar :searchRoute="route('backend.rdb_dip.index')" :exportRoute="route('backend.rdb_dip.export')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">เลขที่คำขอ</label>
                <input type="text" class="form-control" name="dip_request_number" value="{{ request('dip_request_number') }}" placeholder="ค้นหาเลขที่คำขอ...">
            </div>
            <div class="col-md-5">
                <label class="form-label">ชื่อผลงาน / ชื่อการประดิษฐ์</label>
                <input type="text" class="form-control" name="dip_name" value="{{ request('dip_name') }}" placeholder="ค้นหาชื่อผลงาน...">
            </div>
            <div class="col-md-4">
                <label class="form-label">ประเภททรัพย์สินฯ</label>
                <select class="form-select" name="dipt_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($dipTypes as $type)
                        <option value="{{ $type->dipt_id }}" {{ request('dipt_id') == $type->dipt_id ? 'selected' : '' }}>
                            {{ $type->dipt_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">วันที่ยื่นคำขอ (เริ่ม)</label>
                <input type="date" class="form-control datepicker" name="start_date" value="{{ request('start_date') }}" placeholder="วว/ดด/ปปปป">
            </div>
            <div class="col-md-3">
                <label class="form-label">วันที่ยื่นคำขอ (สิ้นสุด)</label>
                <input type="date" class="form-control datepicker" name="end_date" value="{{ request('end_date') }}" placeholder="วว/ดด/ปปปป">
            </div>

            <div class="col-md-4">
                <label class="form-label">ผู้ประดิษฐ์ (ค้นหาจากชื่อ)</label>
                <select class="form-select" id="researcher_id" name="researcher_id">
                    <option value="">-- ทั้งหมด --</option>
                    @if($selectedResearcher)
                        <option value="{{ $selectedResearcher->researcher_id }}" selected>
                            {{ $selectedResearcher->researcher_fname }} {{ $selectedResearcher->researcher_lname }}
                        </option>
                    @endif
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">ปีงบประมาณ</label>
                <select class="form-select" id="year_id" name="year_id">
                    <option value="">-- ทั้งหมด --</option>
                    @if($selectedYear)
                        <option value="{{ $selectedYear->year_id }}" selected>พ.ศ. {{ $selectedYear->year_name }}</option>
                    @endif
                </select>
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการทรัพย์สินทางปัญญา
                <span class="badge bg-primary rounded-pill ms-2">{{ number_format($items->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-top mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50%;">ชื่อผลงาน/วันที่/ประเภท</th>
                            <th style="width: 40%;">นักวิจัย/โครงการ</th>
                            <th style="width: 10%;" class="text-center text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="p-3">
                                <div class="fw-bold mb-1">
                                    <a href="{{ route('backend.rdb_dip.show', $item->dip_id) }}" class="text-decoration-none text-body fw-bold hover-primary text-break">
                                        {{ $item->dip_name ?: ($item->dip_data2_name ?? '-') }}
                                    </a>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 small">
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
                                    @if($item->dip_request_date)
                                        <span class="text-body-secondary">
                                            <i class="bi bi-calendar3 me-1"></i> {{ \App\Helpers\ThaiDateHelper::format($item->dip_request_date, false, true) }}
                                        </span>
                                    @endif
                                    @if($item->dipType)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                            {{ $item->dipType->dipt_name ?? ($item->dipType->diptype_name ?? '-') }}
                                        </span>
                                    @endif
                                    @if($item->dip_request_number)
                                        <span class="text-body-secondary small">เลขที่คำขอ: {{ $item->dip_request_number }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3">
                                @if($item->researcher)
                                <div class="small">
                                        @php
                                            $canManageDipRes = auth()->user()->hasRole(['Admin', 'The Creator', 'Admin Research', 'Admin Dip', 'Dip']) || auth()->user()->id === 129;
                                            $isSelfDipRes = (int)auth()->user()->researcher_id === (int)$item->researcher->researcher_id;
                                        @endphp
                                        @if($canManageDipRes || $isSelfDipRes)
                                            <a href="{{ route('backend.rdb_researcher.show', $item->researcher->researcher_id) }}" class="text-decoration-none text-body fw-bold hover-primary">
                                                {{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                                                @if($isSelfDipRes)
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.7rem;">(คุณ)</span>
                                                @endif
                                            </a>
                                        @else
                                            <span class="text-body fw-bold">
                                                {{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                                            </span>
                                        @endif
                                </div>
                                @endif
                                @if($item->project)
                                <div class="mt-1">
                                    <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" class="text-decoration-none text-body-secondary small hover-primary" title="{{ $item->project->pro_nameTH }}">
                                        <i class="bi bi-link-45deg me-1"></i>{{ Str::limit($item->project->pro_nameTH, 60) }}
                                    </a>
                                </div>
                                @endif
                            </td>
                            <td class="text-center p-3 text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('backend.rdb_dip.show', $item->dip_id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('update', $item)
                                    <a href="{{ route('backend.rdb_dip.edit', $item->dip_id) }}" class="btn btn-outline-secondary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @else
                                        @if($item->workflow_status === 'published' && auth()->user()->can('requestUnlock', $item))
                                            <button type="button" class="btn btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#requestUnlockModal" data-id="{{ $item->dip_id }}" data-type="dip" data-title="{{ $item->dip_name ?: ($item->dip_data2_name ?? 'ทรัพย์สินทางปัญญา') }}" title="ผลงานนี้เผยแพร่แล้ว ยื่นขอเปิดสิทธิ์แก้ไขจาก Admin">
                                                <i class="bi bi-unlock"></i> ขอแก้ไข
                                            </button>
                                        @elseif($item->workflow_status === 'unlock_requested')
                                            <span class="btn btn-sm btn-outline-warning disabled" title="ยื่นขอเปิดสิทธิ์แก้ไขแล้ว รอ Admin อนุมัติ">
                                                <i class="bi bi-hourglass-split"></i> รออนุมัติ
                                            </span>
                                        @endif
                                    @endcan

                                    @if(auth()->user()->can('approveUnlock', $item) && $item->workflow_status === 'unlock_requested')
                                        <form action="{{ route('backend.workflow.approve_unlock', ['type' => 'dip', 'id' => $item->dip_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('อนุมัติให้แก้ไข และย้ายผลงานนี้ไปฉบับร่างหรือไม่?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="อนุมัติให้แก้ไข (ย้ายไปฉบับร่าง)"><i class="bi bi-unlock"></i> ปลดล็อก</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-inbox fs-2 d-block mb-3 opacity-50"></i>
                                ไม่พบข้อมูลทรัพย์สินทางปัญญา
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

    // Researcher Search (Inventor)
    new TomSelect('#researcher_id', {
        valueField: 'value',
        labelField: 'text',
        searchField: 'text',
        placeholder: '-- พิมพ์ชื่อผู้ประดิษฐ์เพื่อค้นหา --',
        load: function(query, callback) {
            if (!query.length || query.length < 2) return callback();
            fetch('{{ route("backend.rdb_project.search_researchers") }}?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(json => callback(json))
                .catch(() => callback());
        },
        render: {
            option: function(data, escape) {
                return '<div>' + (data._highlight || escape(data.text)) + '</div>';
            },
            item: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            }
        }
    });

    // Year Search (Show on click)
    new TomSelect('#year_id', {
        valueField: 'value',
        labelField: 'text',
        searchField: 'text',
        placeholder: '-- เลือกปีงบประมาณ --',
        preload: true,
        load: function(query, callback) {
            fetch('{{ route("backend.rdb_dip.search_years") }}')
                .then(response => response.json())
                .then(json => callback(json))
                .catch(() => callback());
        },
        render: {
            option: function(data, escape) {
                return '<div>' + (data._highlight || escape(data.text)) + '</div>';
            },
            item: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            }
        }
    });

    // Datepickers
    if (typeof initThaiFlatpickr === 'function') {
        const startDatePicker = initThaiFlatpickr("input[name='start_date']", {
            onChange: function(selectedDates, dateStr, instance) {
                const endDateInput = document.querySelector("input[name='end_date']");
                if (endDateInput && endDateInput._flatpickr) {
                    endDateInput._flatpickr.set('minDate', dateStr);
                }
            }
        });

        const endDatePicker = initThaiFlatpickr("input[name='end_date']", {
            onReady: function(selectedDates, dateStr, instance) {
                const startDateInput = document.querySelector("input[name='start_date']");
                if (startDateInput && startDateInput._flatpickr && startDateInput._flatpickr.selectedDates.length > 0) {
                     instance.set('minDate', startDateInput._flatpickr.selectedDates[0]);
                }
            }
        });
    }
});
</script>
@endpush
@endsection
