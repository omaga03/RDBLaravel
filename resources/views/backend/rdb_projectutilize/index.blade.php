@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-rocket-takeoff"></i> จัดการการใช้ประโยชน์ (Utilization)</h1>
        <a href="{{ route('backend.rdbprojectutilize.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> เพิ่มข้อมูลใหม่
        </a>
    </div>

    <!-- Search Box -->
    <div class="card shadow-sm mb-4 search-box">
        <div class="card-header py-3">
            <ul class="nav nav-tabs card-header-tabs" id="searchTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link {{ request('search_mode') != 'advanced' ? 'active' : '' }}" id="simple-tab" data-bs-toggle="tab" data-bs-target="#simple-search" type="button" role="tab"><i class="bi bi-search"></i> ค้นหาทั่วไป</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ request('search_mode') == 'advanced' ? 'active' : '' }}" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced-search" type="button" role="tab"><i class="bi bi-sliders"></i> ค้นหาละเอียด</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="searchTabsContent">
                <!-- Simple Search -->
                <div class="tab-pane fade {{ request('search_mode') != 'advanced' ? 'show active' : '' }}" id="simple-search" role="tabpanel">
                    <form action="{{ route('backend.rdbprojectutilize.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" name="q" value="{{ request('q') }}" placeholder="พิมพ์ชื่อหน่วยงาน, ผู้นำไปใช้, หรือชื่อโครงการ...">
                            <button class="btn btn-primary px-4" type="submit"><i class="bi bi-search"></i> ค้นหา</button>
                            @if(request('q'))
                                <a href="{{ route('backend.rdbprojectutilize.index') }}" class="btn btn-secondary px-3" title="ล้างค่า"><i class="bi bi-x-lg"></i></a>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Advanced Search -->
                <div class="tab-pane fade {{ request('search_mode') == 'advanced' ? 'show active' : '' }}" id="advanced-search" role="tabpanel">
                    <form action="{{ route('backend.rdbprojectutilize.index') }}" method="GET" id="advancedForm">
                        <input type="hidden" name="search_mode" value="advanced">
                        
                        <!-- Row 1: Department, Leader, Utilize Type -->
                        <div class="row g-3 mb-3">
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
                        </div>

                        <!-- Row 2: Location (Cascading) -->
                        <div class="row g-3 mb-3">
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
                        </div>

                        <!-- Row 3: Year & Date Range -->
                        <div class="row g-3 mb-3">
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

                        <div class="d-flex justify-content-end mt-3 gap-2">
                            <a href="{{ route('backend.rdbprojectutilize.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> ล้างค่า</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> ค้นหา</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-top mb-0">
                    <thead>
                        <tr>
                            <th style="width: 65%;">📅 วันที่ / หน่วยงาน / โครงการอ้างอิง</th>
                            <th style="width: 30%;">ผู้ใช้ประโยชน์</th>
                            <th style="width: 5%;" class="text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                @if($item->utz_date)
                                    <small class="text-muted">
                                        {{ \App\Helpers\ThaiDateHelper::format($item->utz_date, false, true) }}
                                    </small>
                                    <span class="text-muted mx-1">|</span>
                                @endif

                                <!-- Department Name -->
                                <span class="fw-bold">
                                    {{ html_entity_decode($item->utz_department_name ?? '-') }}
                                </span>

                                @if($item->changwat)
                                    <small class="text-muted d-block mt-1">
                                        📍 
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
                                    <div class="mt-2">
                                        <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" class="text-decoration-none text-secondary small" target="_blank">
                                            <i class="bi bi-folder-symlink"></i> {{ Str::limit($item->project->pro_nameTH, 80) }}
                                        </a>
                                        @php
                                            $leader = $item->project->rdbProjectWorks->where('position_id', 1)->first();
                                        @endphp
                                        @if($leader && $leader->researcher)
                                            <small class="text-muted d-block">
                                                👨‍🔬 {{ $leader->researcher->researcher_fname }} {{ $leader->researcher->researcher_lname }}
                                            </small>
                                        @endif
                                    </div>
                                @endif

                                <!-- Utilize Types Badges -->
                                @if($item->utz_group)
                                    <div class="mt-2">
                                        @php
                                            $groupIds = array_map('trim', explode(',', $item->utz_group));
                                            $types = \App\Models\RdbProjectUtilizeType::whereIn('utz_type_id', $groupIds)->get();
                                        @endphp
                                        @foreach($types as $type)
                                            <span class="badge bg-light text-primary border border-primary-subtle" style="font-size: 0.75rem;">
                                                {{ $type->utz_typr_name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                <!-- Leading Person -->
                                @if($item->utz_leading)
                                    <div class="fw-semibold">
                                        👤 {{ $item->utz_leading }}
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif

                                <!-- Position -->
                                @if($item->utz_leading_position)
                                    <small class="text-muted d-block">
                                        💼 {{ $item->utz_leading_position }}
                                    </small>
                                @endif

                                @if($item->utz_budget && floatval($item->utz_budget) > 0)
                                    <small class="text-success d-block mt-1">
                                        💰 {{ number_format($item->utz_budget, 2) }} บาท
                                    </small>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('backend.rdbprojectutilize.show', $item->utz_id) }}" class="btn btn-sm btn-outline-primary" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                ไม่พบข้อมูลการใช้ประโยชน์
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
