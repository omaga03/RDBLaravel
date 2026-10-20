@extends('layouts.app')

@section('content')
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active" aria-current="page">โครงการวิจัย</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-folder2-open me-2 text-success"></i>โครงการวิจัย (Research Projects)
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($projects->total()) }} โครงการ
            </span>
        </div>
    </div>

    <!-- Search Component -->
    <x-search-bar :searchRoute="route('frontend.rdbproject.index')" 
        simplePlaceholder="ค้นหาจาก: ชื่อโครงการ, รหัส, คำสำคัญ, นักวิจัย, ประเภททุน, ปีงบประมาณ, หน่วยงาน, บทคัดย่อ">
        
        <div class="row g-3">
            {{-- Row 1: Year, Type, SubType --}}
            <div class="col-md-2">
                <label class="form-label">ปีงบประมาณ</label>
                <select class="form-select" id="search_year_id" name="year_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($years as $year)
                        <option value="{{ $year->year_id }}" {{ request('year_id') == $year->year_id ? 'selected' : '' }}>
                            {{ $year->year_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">ประเภททุนอุดหนุนการวิจัย</label>
                <select class="form-select" id="search_pt_id" name="pt_id">
                    <option value="">-- เลือกปีงบประมาณก่อน --</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">ประเภทโครงการย่อย</label>
                <select class="form-select" id="search_pts_id" name="pts_id">
                    <option value="">-- เลือกประเภททุนก่อน --</option>
                </select>
            </div>

            {{-- Row 2: Code, Group, Budget --}}
            <div class="col-md-3">
                <label class="form-label">รหัสโครงการ</label>
                <input type="text" class="form-control" name="pro_code" value="{{ request('pro_code') }}" placeholder="ระบุรหัสโครงการ...">
            </div>
            <div class="col-md-3">
                <label class="form-label">กลุ่มโครงการ</label>
                <select class="form-select" name="pgroup_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->pgroup_id }}" {{ request('pgroup_id') == $group->pgroup_id ? 'selected' : '' }}>
                            {{ $group->pgroup_nameTH }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">งบประมาณ (เริ่มต้น)</label>
                <input type="number" class="form-control" name="budget_min" value="{{ request('budget_min') }}" placeholder="ต่ำสุด" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label">งบประมาณ (สิ้นสุด)</label>
                <input type="number" class="form-control" name="budget_max" value="{{ request('budget_max') }}" placeholder="สูงสุด" min="0">
            </div>

            {{-- Row 3: Name (Searchable TomSelect Autocomplete like Researcher) --}}
            <div class="col-md-12">
                <label class="form-label d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-folder2 text-primary me-1"></i> ชื่อโครงการวิจัย (ภาษาไทย-อังกฤษ)</span>
                    <span class="badge bg-secondary-subtle text-body-secondary" style="font-size: 0.7rem;">พิมพ์เพื่อค้นหาชื่อโครงการ, ประเภททุน หรือนักวิจัย</span>
                </label>
                <select class="form-select" id="search_pro_id" name="pro_id"></select>
                <input type="hidden" name="pro_nameTH" id="search_pro_nameTH" value="{{ request('pro_nameTH') }}">
            </div>

            {{-- Row 3.5: Multi-Keyword Title Search (กลุ่มคำคั่นด้วยช่องว่าง + เจอทั้งหมด / เจอบางคำ) --}}
            <div class="col-md-12">
                <div class="p-3 rounded-3 border bg-body-tertiary">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1.5 gap-2">
                        <label class="form-label mb-0 fw-semibold text-body" style="font-size: 0.88rem;">
                            <i class="bi bi-fonts text-primary me-1"></i> ค้นหาชื่อเรื่องแบบกลุ่มคำ (คั่นด้วยช่องว่าง)
                        </label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="title_keywords_mode" id="pro_mode_all" value="all" 
                                    {{ request('title_keywords_mode', 'all') === 'all' ? 'checked' : '' }}>
                                <label class="form-check-label small fw-medium" for="pro_mode_all">
                                    <i class="bi bi-check-all text-success me-1"></i>เจอทั้งหมด (ตรงทุกคำ - AND)
                                </label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="title_keywords_mode" id="pro_mode_any" value="any" 
                                    {{ request('title_keywords_mode') === 'any' ? 'checked' : '' }}>
                                <label class="form-check-label small fw-medium" for="pro_mode_any">
                                    <i class="bi bi-intersect text-primary me-1"></i>เจอบางคำ (ตรงคำใดคำหนึ่ง - OR)
                                </label>
                            </div>
                        </div>
                    </div>
                    <input type="text" class="form-control" name="title_keywords" value="{{ request('title_keywords') }}" 
                        placeholder="เช่น เกษตร ชุมชน พลังงาน (พิมพ์หลายคำคั่นด้วยช่องว่าง)...">
                    <div class="form-text small text-body-secondary mt-1">
                        <i class="bi bi-info-circle me-1"></i>ระบบจะค้นหาชื่อโครงการวิจัยที่มีกลุ่มคำดังกล่าวตามเงื่อนไขที่เลือก (เจอทั้งหมด หรือ เจอบางคำ)
                    </div>
                </div>
            </div>

            {{-- Row 4: Researcher, Dept --}}
            <div class="col-md-6">
                <label class="form-label">นักวิจัย</label>
                <select class="form-select" id="search_researcher_id" name="researcher_id"></select>
                <input type="hidden" name="researcher_name" id="search_researcher_name" value="{{ request('researcher_name') }}">
            </div>
            <div class="col-md-6">
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

            {{-- Row 5: Abstract --}}
            <div class="col-md-12">
                <label class="form-label">บทคัดย่อ (ภาษาไทย-อังกฤษ)</label>
                <input type="text" class="form-control" name="pro_abstract" value="{{ request('pro_abstract') }}" placeholder="ระบุคำค้นหาในบทคัดย่อ...">
            </div>

            {{-- Row 6: Keyword, Date --}}
            <div class="col-md-4">
                <label class="form-label">คำสำคัญ</label>
                <input type="text" class="form-control" name="pro_keyword" value="{{ request('pro_keyword') }}" placeholder="ระบุคำสำคัญ...">
            </div>
            <div class="col-md-4">
                <label class="form-label">วันที่เริ่มต้น</label>
                <input type="text" class="form-control datepicker" id="search_date_start" name="date_start" value="{{ request('date_start') }}" placeholder="วว/ดด/ปปปป">
            </div>
            <div class="col-md-4">
                <label class="form-label">วันที่สิ้นสุด</label>
                <input type="text" class="form-control datepicker" id="search_date_end" name="date_end" value="{{ request('date_end') }}" placeholder="วว/ดด/ปปปป">
            </div>

            {{-- Row 7: Note, Status --}}
            <div class="col-md-6">
                <label class="form-label">หมายเหตุ</label>
                <input type="text" class="form-control" name="pro_note" value="{{ request('pro_note') }}" placeholder="ระบุหมายเหตุ...">
            </div>
            <div class="col-md-6">
                <label class="form-label">สถานะโครงการ</label>
                <select class="form-select" name="ps_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->ps_id }}" {{ request('ps_id') == $status->ps_id ? 'selected' : '' }}>
                            {{ $status->ps_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-search-bar>

    <!-- Results Table Card -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-top mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 65%;">ชื่อโครงการ</th>
                            <th style="width: 25%;">นักวิจัย / สังกัด</th>
                            <th style="width: 10%;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <td>
                                <div class="fw-bold">
                                    @if($project->pro_id)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2 py-0.5 me-1">#{{ $project->pro_id }}</span>
                                    @endif
                                    <a href="{{ route('frontend.rdbproject.show', $project->pro_id) }}" class="text-decoration-none text-body fw-bold hover-primary">
                                        {!! $project->pro_nameTH !!}
                                    </a>
                                </div>
                                <div class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                    @if($project->year)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5">ปี {{ $project->year->year_name }}</span>
                                    @endif
                                    @if($project->type)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2 py-0.5">{{ $project->type->pt_name }}</span>
                                    @endif
                                    @if($project->pro_budget)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5">฿{{ number_format($project->pro_budget, 0) }}</span>
                                    @endif
                                    @if($project->status)
                                        <span class="badge" style="background-color: {{ $project->status->ps_color ?? '#6c757d' }}; color: #fff; font-size: 0.75rem;">{{ $project->status->ps_name }}</span>
                                    @endif
                                    @if(request('search') && !empty($project->match_sources))
                                        <span class="badge bg-warning text-dark" 
                                              data-bs-toggle="tooltip" 
                                              data-bs-placement="top" 
                                              data-bs-html="true"
                                              title="<strong>พบคำค้นหาใน:</strong><br>• {{ implode('<br>• ', $project->match_sources) }}">
                                            <i class="bi bi-search"></i> {{ count($project->match_sources) }}
                                        </span>
                                    @endif
                                </small>
                            </td>
                            <td>
                                @if($project->rdbProjectWorks->isNotEmpty())
                                    @php
                                        $leaders = $project->rdbProjectWorks->whereIn('position_id', [1, 2]);
                                        $firstLeader = $leaders->first();
                                        $remainingWorks = $project->rdbProjectWorks->slice(1);
                                        $remainingCount = $remainingWorks->count();
                                        $remainingNames = $remainingWorks->map(function($work) {
                                            return $work->researcher ? $work->researcher->researcher_fname . ' ' . $work->researcher->researcher_lname : '';
                                        })->filter()->implode('<br>• ');
                                    @endphp
                                    @if($firstLeader && $firstLeader->researcher)
                                    <div>
                                        <a href="{{ route('frontend.rdbresearcher.show', $firstLeader->researcher->researcher_id) }}" 
                                           class="text-decoration-none text-body fw-semibold hover-primary">
                                            <i class="bi bi-person-circle text-primary me-1"></i>{{ $firstLeader->researcher->researcher_fname }} {{ $firstLeader->researcher->researcher_lname }}
                                        </a>
                                        @if($remainingCount > 0)
                                            <span class="text-muted small" 
                                                  data-bs-toggle="tooltip" 
                                                  data-bs-placement="top" 
                                                  data-bs-html="true"
                                                  title="<strong>นักวิจัยร่วม:</strong><br>• {!! $remainingNames !!}"
                                                  style="cursor: pointer;">(+{{ $remainingCount }})</span>
                                        @endif
                                    </div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                                @if($project->department)
                                    <div class="text-muted mt-1 small">
                                        <i class="bi bi-geo-alt-fill"></i> {{ $project->department->department_nameTH }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('frontend.rdbproject.show', $project->pro_id) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                ไม่พบข้อมูลโครงการวิจัย
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $projects->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- TomSelect CDN -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Cascading Dropdowns
    const yearSelect = document.getElementById('search_year_id');
    const typeSelect = document.getElementById('search_pt_id');
    const subTypeSelect = document.getElementById('search_pts_id');
    
    const preSelectedYearId = "{{ request('year_id') }}";
    const preSelectedTypeId = "{{ request('pt_id') }}";
    const preSelectedSubTypeId = "{{ request('pts_id') }}";

    function loadProjectTypes(yearId, preSelectTypeId = '', preSelectSubTypeId = '') {
        if (!typeSelect) return;
        typeSelect.innerHTML = '<option value="">กำลังโหลด...</option>';
        subTypeSelect.innerHTML = '<option value="">-- เลือกประเภททุนก่อน --</option>';

        if (!yearId) {
            typeSelect.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            return;
        }

        fetch('{{ route("frontend.rdbproject.typesByYear") }}?year_id=' + yearId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">-- ทั้งหมด --</option>';
                (data.results || []).forEach(item => {
                    const isSelected = String(item.id) === String(preSelectTypeId) ? 'selected' : '';
                    options += `<option value="${item.id}" ${isSelected}>${item.text}</option>`;
                });
                typeSelect.innerHTML = options;
                if (preSelectTypeId) loadProjectTypeSubs(preSelectTypeId, preSelectSubTypeId);
            })
            .catch(err => { console.error(err); typeSelect.innerHTML = '<option value="">เกิดข้อผิดพลาด</option>'; });
    }

    function loadProjectTypeSubs(ptId, preSelectId = '') {
        if (!subTypeSelect) return;
        subTypeSelect.innerHTML = '<option value="">กำลังโหลด...</option>';
        if (!ptId) {
            subTypeSelect.innerHTML = '<option value="">-- ทั้งหมด --</option>';
            return;
        }
        fetch('{{ route("frontend.rdbproject.subTypesByType") }}?pt_id=' + ptId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">-- ทั้งหมด --</option>';
                (data.results || []).forEach(item => {
                    const isSelected = String(item.id) === String(preSelectId) ? 'selected' : '';
                    options += `<option value="${item.id}" ${isSelected}>${item.text}</option>`;
                });
                subTypeSelect.innerHTML = options;
            })
            .catch(err => { console.error(err); subTypeSelect.innerHTML = '<option value="">เกิดข้อผิดพลาด</option>'; });
    }

    if (yearSelect) {
        yearSelect.addEventListener('change', function() { loadProjectTypes(this.value); });
        if (preSelectedYearId) loadProjectTypes(preSelectedYearId, preSelectedTypeId, preSelectedSubTypeId);
    }
    if (typeSelect) {
        typeSelect.addEventListener('change', function() { loadProjectTypeSubs(this.value); });
    }

    // TomSelect: Project Title Autocomplete
    const projectSelect = document.getElementById('search_pro_id');
    const projectNameHidden = document.getElementById('search_pro_nameTH');

    if (projectSelect && typeof TomSelect !== 'undefined') {
        const tsProj = new TomSelect(projectSelect, {
            create: false,
            openOnFocus: true,
            persist: false,
            maxOptions: 20,
            valueField: 'value',
            labelField: 'text',
            searchField: ['text', 'pro_nameTH', 'pt_name', 'researcher_name'],
            placeholder: '-- พิมพ์เพื่อค้นหาชื่อโครงการ, ประเภททุน หรือนักวิจัย --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("frontend.rdbproject.search_projects") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => callback(json.results || json))
                    .catch(() => callback());
            },
            onChange: function(value) {
                const item = this.options[value];
                if (item && projectNameHidden) {
                    projectNameHidden.value = item.pro_nameTH || item.text.split(' [')[0];
                } else if (projectNameHidden) {
                    projectNameHidden.value = '';
                }
            },
            render: {
                option: function(data, escape) { return '<div>' + (data._highlight || escape(data.text)) + '</div>'; },
                item: function(data, escape) { return '<div>' + escape(data.text) + '</div>'; },
                no_results: function() { return '<div class="no-results p-2 text-muted">ไม่พบข้อมูลโครงการ (พิมพ์อย่างน้อย 2 ตัวอักษร)</div>'; }
            }
        });

        const preProId = "{{ request('pro_id') }}";
        const preProName = @json(request('pro_nameTH') ?: '');
        if (preProId || preProName) {
            const val = preProId || 'pre';
            const label = preProName || ('โครงการ #' + preProId);
            tsProj.addOption({ value: val, text: label, pro_nameTH: preProName });
            tsProj.setValue(val);
        }
    }

    // TomSelect: Researcher Autocomplete
    const researcherSelect = document.getElementById('search_researcher_id');
    const researcherNameHidden = document.getElementById('search_researcher_name');
    
    if (researcherSelect && typeof TomSelect !== 'undefined') {
        const ts = new TomSelect(researcherSelect, {
            create: false,
            openOnFocus: true,
            persist: false,
            maxOptions: 15,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            placeholder: '-- พิมพ์เพื่อค้นหานักวิจัย --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("frontend.rdbproject.search_researchers") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => callback(json.results || json))
                    .catch(() => callback());
            },
            onChange: function(value) {
                const item = this.options[value];
                if (item && researcherNameHidden) {
                    researcherNameHidden.value = item.text.split(' [')[0];
                } else if (researcherNameHidden) {
                    researcherNameHidden.value = '';
                }
            },
            render: {
                option: function(data, escape) { return '<div>' + (data._highlight || escape(data.text)) + '</div>'; },
                item: function(data, escape) { return '<div>' + escape(data.text) + '</div>'; },
                no_results: function() { return '<div class="no-results p-2 text-muted">ไม่พบข้อมูล (พิมพ์อย่างน้อย 2 ตัวอักษร)</div>'; }
            }
        });
        const preResearcherId = "{{ request('researcher_id') }}";
        const preResearcherName = @json(request('researcher_name') ?: '');
        if (preResearcherId || preResearcherName) {
            const val = preResearcherId || 'pre';
            ts.addOption({ value: val, text: preResearcherName || ('นักวิจัย #' + preResearcherId) });
            ts.setValue(val);
        }
    }

    // Flatpickr
    if (typeof initThaiFlatpickr !== 'undefined') {
        let searchEndDatePicker = null;
        const searchStartDatePicker = initThaiFlatpickr("#search_date_start", {
            onChange: function(selectedDates, dateStr, instance) {
                if (searchEndDatePicker) {
                    searchEndDatePicker.set('minDate', dateStr || null);
                    const currentEndDate = searchEndDatePicker.selectedDates[0];
                    if (currentEndDate && selectedDates.length > 0 && currentEndDate < selectedDates[0]) {
                        searchEndDatePicker.clear();
                    }
                }
            }
        });
        searchEndDatePicker = initThaiFlatpickr("#search_date_end", {
            onReady: function(selectedDates, dateStr, instance) {
                const startVal = document.getElementById('search_date_start').value;
                if (startVal) instance.set('minDate', startVal);
            }
        });
    }
});
</script>
@endpush