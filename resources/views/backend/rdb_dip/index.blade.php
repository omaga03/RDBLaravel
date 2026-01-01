@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-award me-2"></i>จัดการทรัพย์สินทางปัญญา (Intellectual Property)</h2>
        <a href="{{ route('backend.rdb_dip.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มข้อมูลใหม่
        </a>
    </div>

    {{-- Search Bar --}}
    <x-search-bar :searchRoute="route('backend.rdb_dip.index')" :collapsed="true">
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

    {{-- Data Table --}}
    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-top mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50%;">ชื่อผลงาน/วันที่/ประเภท</th>
                        <th style="width: 40%;">นักวิจัย/โครงการ</th>
                        <th style="width: 10%;" class="text-center">จัดการ</th>
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
                                <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" class="text-decoration-none text-muted small" title="{{ $item->project->pro_nameTH }}">
                                    <i class="bi bi-link-45deg"></i> {{ Str::limit($item->project->pro_nameTH, 60) }}
                                </a>
                            </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('backend.rdb_dip.show', $item->dip_id) }}" class="btn btn-sm btn-outline-primary" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-3"></i>
                            ไม่พบข้อมูล
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($items->hasPages())
        <div class="mt-3">
            {{ $items->withQueryString()->links() }}
        </div>
        @endif
    </x-card>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

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
