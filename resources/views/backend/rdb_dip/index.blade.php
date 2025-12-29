@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-award"></i> จัดการทรัพย์สินทางปัญญา (Intellectual Property)</h1>
         <a href="{{ route('backend.rdb_dip.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> เพิ่มข้อมูลใหม่
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Search Box -->
    <div class="card shadow-sm mb-4 search-box">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <h5 class="mb-0 text-primary"><i class="bi bi-funnel"></i> ค้นหาข้อมูล</h5>
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-expanded="{{ request()->anyFilled(['dip_request_number', 'dip_name', 'dipt_id', 'researcher_id', 'year_id', 'start_date', 'end_date']) ? 'true' : 'false' }}">
                <i class="bi bi-chevron-down"></i> แสดง/ซ่อน คัวกรอง
            </button>
        </div>
        <div class="collapse {{ request()->anyFilled(['dip_request_number', 'dip_name', 'dipt_id', 'researcher_id', 'year_id', 'start_date', 'end_date']) ? 'show' : '' }}" id="searchCollapse">
            <div class="card-body border-top">
                <form action="{{ route('backend.rdb_dip.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">เลขที่คำขอ</label>
                            <input type="text" class="form-control" name="dip_request_number" value="{{ request('dip_request_number') }}" placeholder="ค้นหาเลขที่คำขอ...">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">ชื่อผลงาน / ชื่อการประดิษฐ์</label>
                            <input type="text" class="form-control" name="dip_name" value="{{ request('dip_name') }}" placeholder="ค้นหาชื่อผลงาน...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">ประเภททรัพย์สินฯ</label>
                            <select class="form-select" name="dipt_id">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach($dipTypes as $type)
                                    <option value="{{ $type->dipt_id }}" {{ request('dipt_id') == $type->dipt_id ? 'selected' : '' }}>
                                        {{ $type->dipt_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Date Range --}}
                        <div class="col-md-3">
                            <label class="form-label fw-bold">วันที่ยื่นคำขอ (เริ่ม)</label>
                            <input type="text" class="form-control datepicker" name="start_date" value="{{ request('start_date') }}" placeholder="วว/ดด/ปปปป">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">วันที่ยื่นคำขอ (สิ้นสุด)</label>
                            <input type="text" class="form-control datepicker" name="end_date" value="{{ request('end_date') }}" placeholder="วว/ดด/ปปปป">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">ผู้ประดิษฐ์ (ค้นหาจากชื่อ)</label>
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
                            <label class="form-label fw-bold">ปีงบประมาณ</label>
                            <select class="form-select" id="year_id" name="year_id">
                                <option value="">-- ทั้งหมด --</option>
                                @if($selectedYear)
                                    <option value="{{ $selectedYear->year_id }}" selected>พ.ศ. {{ $selectedYear->year_name }}</option>
                                @endif
                            </select>
                        </div>
                        
                        <div class="col-md-12 d-flex justify-content-end gap-2 border-top pt-3">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="bi bi-search"></i> ค้นหาข้อมูล</button>
                            <a href="{{ route('backend.rdb_dip.index') }}" class="btn btn-outline-secondary px-3"><i class="bi bi-arrow-clockwise"></i> ล้างค่า</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-top mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 55%;">ชื่อผลงาน/วันที่/ประเภท</th>
                            <th style="width: 35%;">นักวิจัย/โครงการ</th>
                            <th style="width: 10%;" class="text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $item->dip_data2_name ?? '-' }}</div>
                                <div class="mt-1 small">
                                    <span class="text-muted"><i class="bi bi-calendar3"></i> {{ \App\Helpers\ThaiDateHelper::format($item->dip_request_date, false, true) }}</span>
                                    <span class="ms-2 badge bg-light text-dark border">
                                        {{ $item->dipType->dipt_name ?? ($item->dipType->diptype_name ?? '-') }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <i class="bi bi-person"></i> {{ $item->researcher->researcher_fname ?? '' }} {{ $item->researcher->researcher_lname ?? '' }}
                                </div>
                                @if($item->project)
                                <div class="mt-1">
                                    <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" class="text-decoration-none text-muted x-small" title="{{ $item->project->pro_nameTH }}">
                                        <i class="bi bi-link-45deg"></i> {{ Str::limit($item->project->pro_nameTH, 50) }}
                                    </a>
                                </div>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('backend.rdb_dip.show', $item->dip_id) }}" class="btn btn-sm btn-outline-primary" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                ไม่พบข้อมูล
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div class="card-footer py-3 d-flex justify-content-center">
                {{ $items->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
/* Fix TomSelect text color in dark mode/theme */
.ts-control input {
    color: inherit !important;
}
.search-box .ts-wrapper.multi .ts-control > div, 
.search-box .ts-wrapper.single .ts-control {
    background-color: transparent !important;
    color: var(--bs-body-color) !important;
    border-color: var(--bs-border-color) !important;
}
.ts-dropdown {
    background: var(--bs-body-bg) !important;
    color: var(--bs-body-color) !important;
    border-color: var(--bs-border-color) !important;
}
.ts-dropdown .active {
    background-color: var(--bs-primary) !important;
    color: white !important;
}
.ts-dropdown .option {
    color: var(--bs-body-color) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
        }
    });

    // Datepickers with global Buddhist Era and Range Logic
    if (typeof initThaiFlatpickr !== 'undefined') {
        const startDatePicker = initThaiFlatpickr("input[name='start_date']", {
            onChange: function(selectedDates, dateStr, instance) {
                if (endDatePicker) endDatePicker.set('minDate', dateStr);
            }
        });

        const endDatePicker = initThaiFlatpickr("input[name='end_date']", {
            onChange: function(selectedDates, dateStr, instance) {
                if (startDatePicker) startDatePicker.set('maxDate', dateStr);
            }
        });
    }
});
</script>
@endpush
