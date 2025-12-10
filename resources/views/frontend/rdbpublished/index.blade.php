@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="bi bi-journal-text"></i> ผลงานตีพิมพ์ (Published Works)</h2>
            
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
                    <a href="{{ route('frontend.rdbpublished.index') }}" class="btn btn-sm btn-outline-info ms-3 bg-white text-info text-decoration-none border-0 shadow-sm">แสดงทั้งหมด</a>
                </div>
            </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('frontend.rdbpublished.index') }}" method="GET" class="row g-3">
                        @if(request('pro_id'))
                        <input type="hidden" name="pro_id" value="{{ request('pro_id') }}">
                        @endif
                        <div class="col-md-6">
                            <label for="search" class="form-label">คำค้นหา</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="ระบุชื่อผลงาน, วารสาร, นักวิจัย, หรือชื่อโครงการ...">
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
                                <a href="{{ route('frontend.rdbpublished.index', ['pro_id' => request('pro_id')]) }}" class="btn btn-secondary me-2">ล้างค่าค้นหา</a>
                            @else
                                <a href="{{ route('frontend.rdbpublished.index') }}" class="btn btn-secondary me-2">ล้างค่า</a>
                            @endif
                            <button type="submit" formaction="{{ route('frontend.rdbpublished.export') }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Export CSV</button>
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
                                    <th style="width: 40%;">ชื่อผลงาน / วารสาร</th>
                                    <th style="width: 25%;">ผู้เขียนบทความ</th>
                                    <th style="width: 20%;">วันที่ตีพิมพ์</th>
                                    <th style="width: 15%;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold"><i class="bi bi-file-earmark-text-fill text-primary"></i> {!! $item->pub_name !!}</div>
                                        @if($item->pub_name_journal)
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-journal-bookmark"></i> {!! $item->pub_name_journal !!}
                                        </small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $mainAuthor = null;
                                            $otherCount = 0;
                                            
                                            if ($item->authors && $item->authors->count() > 0) {
                                                $mainAuthor = $item->authors->first();
                                                $otherCount = $item->authors->count() - 1;
                                            } elseif ($item->researcher) {
                                                $mainAuthor = $item->researcher;
                                            }
                                        @endphp

                                        @if($mainAuthor)
                                            <div class="fw-bold">
                                                {!! $mainAuthor->researcher_fname !!} {!! $mainAuthor->researcher_lname !!}
                                                @if($otherCount > 0)
                                                    <span class="text-muted fw-normal">และ {{ $otherCount }} คน</span>
                                                @endif
                                            </div>
                                            @if($mainAuthor->department)
                                            <small class="text-muted d-block">
                                                <i class="bi bi-building"></i> {!! $mainAuthor->department->department_nameTH !!}
                                            </small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->pub_date)
                                            @php
                                                $date = \Carbon\Carbon::parse($item->pub_date);
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
                                        <a href="{{ route('frontend.rdbpublished.show', $item->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> ดูข้อมูล
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">ไม่พบข้อมูลผลงานตีพิมพ์</td>
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
        // Skip validation for export button
        if (e.submitter && e.submitter.formAction && e.submitter.formAction.includes('export')) {
            return true;
        }
        
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