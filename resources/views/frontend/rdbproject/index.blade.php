@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="bi bi-folder2-open"></i> โครงการวิจัย (Research Projects)</h2>
            
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> ค้นหาโครงการวิจัย</h5>
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-expanded="false" aria-controls="searchCollapse">
                        <i class="bi bi-chevron-down"></i> แสดง/ซ่อน
                    </button>
                </div>
                <div class="collapse" id="searchCollapse">
                    <div class="card-body">
                        <form action="{{ route('frontend.rdbproject.index') }}" method="GET" id="searchForm">
                            <div class="row g-3">
                                <!-- Basic Search -->
                                <div class="col-md-12">
                                    <label for="search" class="form-label fw-bold">คำค้นหาทั่วไป</label>
                                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="ระบุชื่อโครงการ, รหัส, คำสำคัญ, หรือนักวิจัย...">
                                </div>

                                <div class="col-12"><hr></div>

                                <!-- Advanced Filters -->
                                <div class="col-md-12">
                                    <h6 class="text-primary"><i class="bi bi-sliders"></i> ตัวกรองขั้นสูง</h6>
                                </div>

                                <!-- Year, Type, Sub-Type -->
                                <div class="col-md-3">
                                    <label for="year_id" class="form-label">ปีงบประมาณ</label>
                                    <select class="form-select" id="year_id" name="year_id">
                                        <option value="">-- ทั้งหมด --</option>
                                        @foreach($years as $year)
                                            <option value="{{ $year->year_id }}" {{ request('year_id') == $year->year_id ? 'selected' : '' }}>
                                                {{ $year->year_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="pt_id" class="form-label">แหล่งทุน/ประเภททุน</label>
                                    <select class="form-select" id="pt_id" name="pt_id" disabled>
                                        <option value="">-- เลือกปีก่อน --</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="pts_id" class="form-label">ประเภทย่อย (Sub-Type)</label>
                                    <select class="form-select" id="pts_id" name="pts_id" disabled>
                                        <option value="">-- เลือก Type ก่อน --</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="department_id" class="form-label">หน่วยงาน/สังกัด</label>
                                    <select class="form-select" id="department_id" name="department_id">
                                        <option value="">-- ทั้งหมด --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                                {{ $dept->department_nameTH }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Budget Range -->
                                <div class="col-md-3">
                                    <label for="budget_min" class="form-label">งบประมาณขั้นต่ำ (บาท)</label>
                                    <input type="number" class="form-control" id="budget_min" name="budget_min" value="{{ request('budget_min') }}" placeholder="0" min="0" step="1000">
                                </div>

                                <div class="col-md-3">
                                    <label for="budget_max" class="form-label">งบประมาณสูงสุด (บาท)</label>
                                    <input type="number" class="form-control" id="budget_max" name="budget_max" value="{{ request('budget_max') }}" placeholder="ไม่จำกัด" min="0" step="1000">
                                </div>

                                <!-- Date Range -->
                                <div class="col-md-3">
                                    <label for="date_start" class="form-label">วันที่เริ่มโครงการ <span class="text-danger" id="date_required_indicator" style="display:none;">*</span></label>
                                    <input type="date" class="form-control" id="date_start" name="date_start" value="{{ request('date_start') }}">
                                </div>

                                <div class="col-md-3">
                                    <label for="date_end" class="form-label">วันที่สิ้นสุด <span class="text-danger" id="date_end_required_indicator" style="display:none;">*</span></label>
                                    <input type="date" class="form-control" id="date_end" name="date_end" value="{{ request('date_end') }}">
                                </div>

                                <!-- Action Buttons -->
                                <div class="col-md-12 d-flex justify-content-end align-items-end gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> ค้นหา</button>
                                    <a href="{{ route('frontend.rdbproject.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> ล้างค่า</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-top">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 55%;">
                                        <a href="{{ route('frontend.rdbproject.index', array_merge(request()->all(), ['sort' => 'pro_nameTH', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            ชื่อโครงการ <i class="bi bi-arrow-down-up"></i>
                                        </a>
                                    </th>
                                    <th style="width: 30%;">นักวิจัย / สังกัด</th>
                                    <th style="width: 15%;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                <tr>
                                    <td>
                                        <div class="fw-bold">
                                            @if($project->pro_code)
                                                <span class="text-primary">{!! $project->pro_code !!}</span>
                                            @endif
                                            {!! $project->pro_nameTH !!}
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            @if($project->year)
                                                ปีงบฯ {{ $project->year->year_name }}
                                            @endif
                                            @if($project->year && $project->type)
                                                •
                                            @endif
                                            @if($project->type)
                                                {{ $project->type->pt_name }}
                                                @if($project->typeSub)
                                                    • {{ $project->typeSub->pts_name }}
                                                @endif
                                            @endif
                                            @if(($project->year || $project->type) && $project->pro_budget)
                                                •
                                            @endif
                                            @if($project->pro_budget)
                                                งบฯ {{ number_format($project->pro_budget, 0) }} บาท
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        @if($project->rdbProjectWorks->isNotEmpty())
                                            @php
                                                // กรองเฉพาะหัวหน้าโครงการ (position_id 1-2)
                                                $leaders = $project->rdbProjectWorks->whereIn('position_id', [1, 2]);
                                                $firstLeader = $leaders->first();
                                                $remainingCount = $project->rdbProjectWorks->count() - 1;
                                            @endphp
                                            @if($firstLeader && $firstLeader->researcher)
                                            <div>
                                                <i class="bi bi-person"></i> 
                                                <a href="{{ route('frontend.rdbresearcher.show', $firstLeader->researcher->researcher_id) }}" target="_blank" class="text-decoration-none">
                                                    {!! $firstLeader->researcher->researcher_fname !!} {!! $firstLeader->researcher->researcher_lname !!}
                                                </a>
                                                @if($remainingCount > 0)
                                                    <span class="text-muted">และอีก {{ $remainingCount }} คน</span>
                                                @endif
                                            </div>
                                            @endif
                                        @endif
                                        @if($project->department)
                                        <div class="text-muted mt-1"><small><i class="bi bi-building"></i> {!! $project->department->department_nameTH !!}</small></div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('frontend.rdbproject.show', $project->pro_id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> ดูข้อมูล
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">ไม่พบข้อมูลโครงการวิจัย</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $projects->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const yearSelect = document.getElementById('year_id');
    const typeSelect = document.getElementById('pt_id');
    const subTypeSelect = document.getElementById('pts_id');
    const dateStart = document.getElementById('date_start');
    const dateEnd = document.getElementById('date_end');
    const searchForm = document.getElementById('searchForm');
    
    // Auto-expand if there are search parameters
    const hasSearchParams = {{ count(request()->except('page')) > 0 ? 'true' : 'false' }};
    if (hasSearchParams) {
        const collapseElement = document.getElementById('searchCollapse');
        const bsCollapse = new bootstrap.Collapse(collapseElement, { toggle: false });
        bsCollapse.show();
    }
    
    // Cascading dropdown: Year -> Type
    yearSelect.addEventListener('change', function() {
        const yearId = this.value;
        
        console.log('Year selected:', yearId);
        
        // Reset type and sub-type
        typeSelect.innerHTML = '<option value="">-- กำลังโหลด... --</option>';
        typeSelect.disabled = true;
        subTypeSelect.innerHTML = '<option value="">-- เลือก Type ก่อน --</option>';
        subTypeSelect.disabled = true;
        
        if (!yearId) {
            typeSelect.innerHTML = '<option value="">-- เลือกปีก่อน --</option>';
            return;
        }
        
        // Fetch types for selected year
        const url = `{{ route('frontend.rdbproject.typesByYear') }}?year_id=${yearId}`;
        console.log('Fetching URL:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Types data:', data);
                typeSelect.innerHTML = '<option value="">-- ทั้งหมด --</option>';
                data.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.pt_id;
                    option.textContent = type.pt_name;
                    option.selected = '{{ request("pt_id") }}' == type.pt_id;
                    typeSelect.appendChild(option);
                });
                typeSelect.disabled = false;
                
                // Trigger change if there's a pre-selected value
                if ('{{ request("pt_id") }}') {
                    typeSelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => {
                console.error('Error fetching types:', error);
                typeSelect.innerHTML = '<option value="">-- เกิดข้อผิดพลาด --</option>';
            });
    });
    
    // Cascading dropdown: Type -> Sub-Type
    typeSelect.addEventListener('change', function() {
        const ptId = this.value;
        
        // Reset sub-type
        subTypeSelect.innerHTML = '<option value="">-- กำลังโหลด... --</option>';
        subTypeSelect.disabled = true;
        
        if (!ptId) {
            subTypeSelect.innerHTML = '<option value="">-- เลือก Type ก่อน --</option>';
            return;
        }
        
        // Fetch sub-types for selected type
        fetch(`{{ route('frontend.rdbproject.subTypesByType') }}?pt_id=${ptId}`)
            .then(response => response.json())
            .then(data => {
                subTypeSelect.innerHTML = '<option value="">-- ทั้งหมด --</option>';
                data.forEach(subType => {
                    const option = document.createElement('option');
                    option.value = subType.pts_id;
                    option.textContent = subType.pts_name;
                    option.selected = '{{ request("pts_id") }}' == subType.pts_id;
                    subTypeSelect.appendChild(option);
                });
                subTypeSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching sub-types:', error);
                subTypeSelect.innerHTML = '<option value="">-- เกิดข้อผิดพลาด --</option>';
            });
    });
    
    // Initialize cascading on page load if year is selected
    if (yearSelect.value) {
        yearSelect.dispatchEvent(new Event('change'));
    }
    
    // Date range validation
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
    
    // Initialize
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