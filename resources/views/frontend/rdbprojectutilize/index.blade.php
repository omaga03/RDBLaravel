@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="bi bi-graph-up-arrow"></i> การนำผลงานวิจัยไปใช้ประโยชน์ (Utilization)</h2>
            
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
                    กำลังแสดงรายการเฉพาะโครงการ: <strong>{{ $project->pro_nameTH }}</strong>
                    <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="btn btn-sm btn-outline-info ms-3 bg-white text-info text-decoration-none border-0 shadow-sm">แสดงทั้งหมด</a>
                </div>
            </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('frontend.rdbprojectutilize.index') }}" method="GET" class="row g-3">
                        @if(request('pro_id'))
                        <input type="hidden" name="pro_id" value="{{ request('pro_id') }}">
                        @endif
                        <div class="col-md-6">
                            <label for="search" class="form-label">คำค้นหา</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="ระบุรายละเอียด, ชื่อหน่วยงาน, หรือชื่อโครงการ...">
                        </div>
                        <div class="col-md-2">
                            <label for="date_start" class="form-label">วันที่เริ่ม <span class="text-danger" id="date_required_indicator" style="display:none;">*</span></label>
                            <input type="date" class="form-control" id="date_start" name="date_start" value="{{ request('date_start') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_end" class="form-label">วันที่สิ้นสุด <span class="text-danger" id="date_end_required_indicator" style="display:none;">*</span></label>
                            <input type="date" class="form-control" id="date_end" name="date_end" value="{{ request('date_end') }}">
                        </div>
                        <div class="col-md-12 d-flex justify-content-end align-items-end">
                            <button type="submit" class="btn btn-primary me-2"><i class="bi bi-search"></i> ค้นหา</button>
                            @if(request('pro_id'))
                                <a href="{{ route('frontend.rdbprojectutilize.index', ['pro_id' => request('pro_id')]) }}" class="btn btn-secondary me-2">ล้างค่าค้นหา</a>
                            @else
                                <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="btn btn-secondary me-2">ล้างค่า</a>
                            @endif
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
                                    <th style="width: 35%;">🏢 หน่วยงานที่นำไปใช้ประโยชน์</th>
                                    <th style="width: 40%;">🔬 โครงการวิจัย</th>
                                    <th style="width: 10%;">📅 วันที่นำไปใช้</th>
                                    <th style="width: 15%;">⚙️ จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold"><i class="bi bi-building"></i> {!! $item->utz_department_name ?? '-' !!}</div>
                                        <div>
                                            👥 {!! $item->utz_leading ?? '-' !!}
                                            @if($item->utz_leading_position)
                                                <small class="text-muted fw-normal">({!! $item->utz_leading_position !!})</small>
                                            @endif
                                        </div>
                                        @if($item->changwat)
                                        <small class="text-muted d-block mt-1">
                                            📍 {!! $item->changwat->tambon_t !!} 
                                            {!! $item->changwat->amphoe_t !!} 
                                            {!! $item->changwat->changwat_t !!}
                                        </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold"><i class="bi bi-journal-text"></i> {!! $item->project->pro_nameTH ?? '-' !!}</div>
                                        @if($item->project && $item->project->rdbProjectWorks->isNotEmpty())
                                            @php
                                                $head = $item->project->rdbProjectWorks->first();
                                            @endphp
                                            @if($head && $head->researcher)
                                                <small class="text-muted d-block mt-1">
                                                    <i class="bi bi-person"></i> {!! $head->researcher->researcher_fname !!} {!! $head->researcher->researcher_lname !!}
                                                    @if($item->project && $item->project->department)
                                                        • <i class="bi bi-building"></i> {!! $item->project->department->department_nameTH !!}
                                                    @endif
                                                </small>
                                            @endif
                                        @elseif($item->project && $item->project->department)
                                            <small class="text-muted d-block mt-1">
                                                <i class="bi bi-building"></i> {!! $item->project->department->department_nameTH !!}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->utz_date)
                                            @php
                                                $date = \Carbon\Carbon::parse($item->utz_date);
                                                $months = [
                                                    1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
                                                    7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
                                                ];
                                            @endphp
                                            {{ $date->day }} {{ $months[$date->month] }} {{ $date->year + 543 }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('frontend.rdbprojectutilize.show', $item->utz_id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> ดูข้อมูล
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูลการนำไปใช้ประโยชน์</td>
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