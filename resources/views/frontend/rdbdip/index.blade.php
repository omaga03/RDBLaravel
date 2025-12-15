@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="bi bi-award"></i> ทรัพย์สินทางปัญญา (Intellectual Property)</h2>
            
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            @if(isset($project))
            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                <div>
                    กำลังแสดงรายการเฉพาะโครงการ: <strong>{!! $project->pro_nameTH !!}</strong>
                    <a href="{{ route('frontend.rdbdip.index') }}" class="btn btn-sm btn-outline-info ms-3 bg-white text-info text-decoration-none border-0 shadow-sm">แสดงทั้งหมด</a>
                </div>
            </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('frontend.rdbdip.index') }}" method="GET" class="row g-3">
                        @if(request('pro_id'))
                        <input type="hidden" name="pro_id" value="{{ request('pro_id') }}">
                        @endif
                        <div class="col-md-6">
                            <label for="search" class="form-label">คำค้นหา</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="ระบุเลขที่คำขอ, เลขที่ทะเบียน, ชื่อ, หรือชื่อโครงการ...">
                        </div>
                        <div class="col-md-2">
                            <label for="date_start" class="form-label">วันที่เริ่ม <span class="text-danger" id="date_required_indicator" style="display:none;">*</span></label>
                            <input type="date" class="form-control" id="date_start" name="date_start" value="{{ request('date_start') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_end" class="form-label">วันที่สิ้นสุด <span class="text-danger" id="date_end_required_indicator" style="display:none;">*</span></label>
                            <input type="date" class="form-control" id="date_end" name="date_end" value="{{ request('date_end') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้นหา</button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            @if(request('pro_id'))
                                <a href="{{ route('frontend.rdbdip.index', ['pro_id' => request('pro_id')]) }}" class="btn btn-secondary w-100">ล้างค่าค้นหา</a>
                            @else
                                <a href="{{ route('frontend.rdbdip.index') }}" class="btn btn-secondary w-100">ล้างค่า</a>
                            @endif
                        </div>
                        <div class="col-md-12 d-flex justify-content-end">
                            <button type="submit" formaction="{{ route('frontend.rdbdip.export') }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Export CSV</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-top">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 20%;">เลขที่คำขอ/ทะเบียน</th>
                                    <th style="width: 70%;">ชื่อผลงาน / ผู้ทรงสิทธิ</th>
                                    <th style="width: 10%;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td>
                                        <div><span class="badge bg-secondary">คำขอ</span> {!! $item->dip_request_number ?? '-' !!}</div>
                                        @if($item->dip_patent_number)
                                            <div class="mt-1"><span class="badge bg-success">ทะเบียน</span> {!! $item->dip_patent_number !!}</div>
                                        @endif
                                        @if($item->dip_request_date)
                                            <small class="text-muted d-block mt-1">
                                                <i class="bi bi-calendar"></i> {{ \App\Helpers\ThaiDateHelper::format($item->dip_request_date, false, true) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold mb-1">
                                            {!! $item->dip_data2_name ?? ($item->dip_name ?? 'ไม่มีชื่อผลงาน') !!}
                                        </div>
                                        <div class="small text-muted">
                                            <span class="me-2"><i class="bi bi-tag-fill me-1"></i> {!! $item->dipType->dipt_name ?? 'ไม่ระบุประเภท' !!}</span>
                                            @if($item->researcher)
                                                <span class="text-secondary">
                                                    <i class="bi bi-person-circle mx-1"></i>
                                                    {{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('frontend.rdbdip.show', $item->dip_id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> ดูข้อมูล
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">ไม่พบข้อมูลทรัพย์สินทางปัญญา</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="{{ route('frontend.rdbproject.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> กลับหน้ารายการโครงการ</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateStart = document.getElementById('date_start');
    const dateEnd = document.getElementById('date_end');
    const searchForm = dateStart.closest('form');
    
    // Update required indicators
    function updateRequiredIndicators() {
        const startIndicator = document.getElementById('date_required_indicator');
        const endIndicator = document.getElementById('date_end_required_indicator');
        
        if (dateEnd.value) {
            startIndicator.style.display = 'inline';
            dateStart.required = true;
        } else {
            startIndicator.style.display = 'none';
            dateStart.required = false;
        }
        
        if (dateStart.value) {
            endIndicator.style.display = 'inline';
            dateEnd.required = true;
        } else {
            endIndicator.style.display = 'none';
            dateEnd.required = false;
        }
    }
    
    // Set min/max constraints
    dateStart.addEventListener('change', function() {
        if (this.value) {
            dateEnd.min = this.value;
        } else {
            dateEnd.removeAttribute('min');
        }
        updateRequiredIndicators();
    });
    
    dateEnd.addEventListener('change', function() {
        if (this.value) {
            dateStart.max = this.value;
        } else {
            dateStart.removeAttribute('max');
        }
        updateRequiredIndicators();
    });
    
    // Form submission validation
    searchForm.addEventListener('submit', function(e) {
        const startVal = dateStart.value;
        const endVal = dateEnd.value;
        
        // Check if only one date is filled
        if ((startVal && !endVal) || (!startVal && endVal)) {
            e.preventDefault();
            alert('กรุณาระบุทั้งวันที่เริ่มและวันที่สิ้นสุด');
            return false;
        }
        
        // Check if end date is before start date
        if (startVal && endVal && new Date(endVal) < new Date(startVal)) {
            e.preventDefault();
            alert('วันที่สิ้นสุดต้องมากกว่าหรือเท่ากับวันที่เริ่ม');
            return false;
        }
    });
    
    // Initialize on page load
    updateRequiredIndicators();
    if (dateStart.value) {
        dateEnd.min = dateStart.value;
    }
    if (dateEnd.value) {
        dateStart.max = dateEnd.value;
    }
});
</script>
@endpush
@endsection