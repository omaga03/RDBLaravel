@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Page Title & Actions -->
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="mb-0"><i class="bi bi-award me-2"></i> ทรัพย์สินทางปัญญา (Intellectual Property)</h2>
            <div class="text-muted small mt-1">สิทธิบัตร อนุสิทธิบัตร ลิขสิทธิ์ และเครื่องหมายการค้า</div>
        </div>
        <div>
            <a href="{{ route('frontend.rdbdip.export', request()->query()) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i> ส่งออกข้อมูล (CSV)
            </a>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(isset($project))
    <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
        <div>
            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
            กำลังแสดงรายการทรัพย์สินทางปัญญาของโครงการ: <strong>{!! $project->pro_nameTH !!}</strong>
        </div>
        <a href="{{ route('frontend.rdbdip.index') }}" class="btn btn-sm btn-outline-primary bg-white">แสดงทั้งหมด</a>
    </div>
    @endif

    <!-- Search Component (Styled like Research Projects) -->
    <x-search-bar :searchRoute="route('frontend.rdbdip.index')" 
        simplePlaceholder="ค้นหาจาก: เลขที่คำขอ, เลขที่ทะเบียน/สิทธิบัตร, ชื่อผลงาน, ผู้ประดิษฐ์, โครงการวิจัย...">
        
        <div class="row g-3">
            @if(request('pro_id'))
                <input type="hidden" name="pro_id" value="{{ request('pro_id') }}">
            @endif

            {{-- Row 1: Search, Type, Department --}}
            <div class="col-md-6">
                <label class="form-label">คำค้นหา</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                    placeholder="ระบุเลขที่คำขอ, เลขที่ทะเบียน, ชื่อผลงาน, หรือผู้ประดิษฐ์...">
            </div>
            <div class="col-md-3">
                <label class="form-label">ประเภททรัพย์สินทางปัญญา</label>
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

            {{-- Row 2: Date Range --}}
            <div class="col-md-6">
                <label class="form-label">วันที่ยื่นคำขอ (เริ่มต้น)</label>
                <input type="date" class="form-control" id="date_start" name="date_start" value="{{ request('date_start') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">วันที่ยื่นคำขอ (สิ้นสุด)</label>
                <input type="date" class="form-control" id="date_end" name="date_end" value="{{ request('date_end') }}">
            </div>
        </div>
    </x-search-bar>

    <!-- Results Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการทรัพย์สินทางปัญญา
                <span class="badge bg-primary rounded-pill ms-2">{{ number_format($items->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50%;">ชื่อผลงาน / ข้อมูลทรัพย์สินทางปัญญา</th>
                            <th style="width: 35%;">ผู้ทรงสิทธิ / ผู้ประดิษฐ์</th>
                            <th style="width: 15%;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="fw-bold mb-1">
                                    <span class="badge bg-secondary me-1">#{{ $item->dip_id }}</span>
                                    <a href="{{ route('frontend.rdbdip.show', $item->dip_id) }}" class="text-decoration-none text-body hover-primary">
                                        {!! $item->dip_data2_name ?: ($item->dip_name ?: 'ไม่มีชื่อผลงาน') !!}
                                    </a>
                                </div>
                                <div class="small d-flex flex-wrap gap-1 align-items-center mt-1">
                                    @if($item->dipType)
                                        <span class="badge bg-info text-dark">{{ $item->dipType->dipt_name }}</span>
                                    @endif

                                    @if($item->dip_request_number)
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-file-earmark-text me-1"></i>คำขอ: {{ $item->dip_request_number }}
                                        </span>
                                    @endif

                                    @if($item->dip_patent_number)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                            <i class="bi bi-patch-check-fill me-1"></i>ทะเบียน: {{ $item->dip_patent_number }}
                                        </span>
                                    @endif

                                    @if($item->dip_request_date)
                                        <span class="badge bg-light text-muted border">
                                            <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($item->dip_request_date, false, true) }}
                                        </span>
                                    @endif

                                    @if(request('search') && !empty($item->match_sources))
                                        <span class="badge bg-warning text-dark" 
                                              data-bs-toggle="tooltip" 
                                              data-bs-placement="top" 
                                              data-bs-html="true"
                                              title="<strong>พบคำค้นหาใน:</strong><br>• {{ implode('<br>• ', $item->match_sources) }}">
                                            <i class="bi bi-search"></i> {{ count($item->match_sources) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($item->researcher)
                                    <div>
                                        <i class="bi bi-person-circle text-primary me-1"></i>
                                        <a href="{{ route('frontend.rdbresearcher.show', $item->researcher->researcher_id) }}" class="text-decoration-none text-body fw-bold">
                                            {{ $item->researcher->prefix->prefix_nameTH ?? '' }}{{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                                        </a>
                                    </div>
                                    @if($item->researcher->department)
                                        <div class="text-muted mt-1 small">
                                            <i class="bi bi-geo-alt-fill text-secondary me-1"></i>{{ $item->researcher->department->department_nameTH }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif

                                @if($item->project)
                                    <div class="small mt-1 text-truncate" style="max-width: 350px;">
                                        <i class="bi bi-folder2 text-success me-1"></i>
                                        <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" 
                                           class="text-decoration-none text-muted" 
                                           title="{{ $item->project->pro_nameTH }}">
                                            {{ $item->project->pro_nameTH }}
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('frontend.rdbdip.show', $item->dip_id) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i> ดูข้อมูล
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                ไม่พบข้อมูลทรัพย์สินทางปัญญา
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('frontend.rdbproject.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> ไปหน้ารายการโครงการวิจัย
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Date range validation
    const dateStart = document.getElementById('date_start');
    const dateEnd = document.getElementById('date_end');
    
    if (dateStart && dateEnd) {
        dateStart.addEventListener('change', function() {
            if (this.value) {
                dateEnd.min = this.value;
            } else {
                dateEnd.removeAttribute('min');
            }
        });
        
        dateEnd.addEventListener('change', function() {
            if (this.value) {
                dateStart.max = this.value;
            } else {
                dateStart.removeAttribute('max');
            }
        });
    }
});
</script>
<style>
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
</style>
@endpush