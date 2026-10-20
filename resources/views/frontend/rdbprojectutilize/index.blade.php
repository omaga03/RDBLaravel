@extends('layouts.app')

@section('content')
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active" aria-current="page">การนำไปใช้ประโยชน์</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-graph-up-arrow me-2 text-primary"></i>การนำผลงานวิจัยไปใช้ประโยชน์ (Research Utilization)
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($items->total()) }} รายการ
            </span>
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
            กำลังแสดงรายการการนำไปใช้ประโยชน์ของโครงการ: <strong>{!! $project->pro_nameTH !!}</strong>
        </div>
        <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="btn btn-sm btn-outline-primary bg-white">แสดงทั้งหมด</a>
    </div>
    @endif

    <!-- Search Component (Styled like Research Projects) -->
    <x-search-bar :searchRoute="route('frontend.rdbprojectutilize.index')" 
        simplePlaceholder="ค้นหาจาก: ชื่อหน่วยงาน, รายละเอียด, ผู้แทน/ผู้ลงนาม, ชื่อโครงการวิจัย, นักวิจัย...">
        
        <div class="row g-3">
            @if(request('pro_id'))
                <input type="hidden" name="pro_id" value="{{ request('pro_id') }}">
            @endif

        <div class="row g-3">
            {{-- Row 1: Utilize / Project Title Autocomplete Search --}}
            <div class="col-md-12">
                <label class="form-label d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-diagram-3 text-primary me-1"></i> ชื่อโครงการวิจัย / การนำไปใช้ประโยชน์</span>
                    <span class="badge bg-secondary-subtle text-body-secondary" style="font-size: 0.7rem;">พิมพ์เพื่อค้นหาการใช้ประโยชน์/ชื่อโครงการ</span>
                </label>
                <select class="form-select" id="search_utz_id" name="utz_id"></select>
                <input type="hidden" name="utz_name" id="search_utz_name" value="{{ request('utz_name') }}">
            </div>

            {{-- Row 2: Multi-Keyword Title Search (กลุ่มคำคั่นด้วยช่องว่าง + เจอทั้งหมด / เจอบางคำ) --}}
            <div class="col-md-12">
                <div class="p-3 rounded-3 border bg-body-tertiary">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1.5 gap-2">
                        <label class="form-label mb-0 fw-semibold text-body" style="font-size: 0.88rem;">
                            <i class="bi bi-fonts text-primary me-1"></i> ค้นหาชื่อเรื่องแบบกลุ่มคำ (คั่นด้วยช่องว่าง)
                        </label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="title_keywords_mode" id="utz_mode_all" value="all" 
                                    {{ request('title_keywords_mode', 'all') === 'all' ? 'checked' : '' }}>
                                <label class="form-check-label small fw-medium" for="utz_mode_all">
                                    <i class="bi bi-check-all text-success me-1"></i>เจอทั้งหมด (ตรงทุกคำ - AND)
                                </label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="title_keywords_mode" id="utz_mode_any" value="any" 
                                    {{ request('title_keywords_mode') === 'any' ? 'checked' : '' }}>
                                <label class="form-check-label small fw-medium" for="utz_mode_any">
                                    <i class="bi bi-intersect text-primary me-1"></i>เจอบางคำ (ตรงคำใดคำหนึ่ง - OR)
                                </label>
                            </div>
                        </div>
                    </div>
                    <input type="text" class="form-control" name="title_keywords" value="{{ request('title_keywords') }}" 
                        placeholder="เช่น ชุมชน ท้องถิ่น ถ่ายทอด (พิมพ์หลายคำคั่นด้วยช่องว่าง)...">
                    <div class="form-text small text-body-secondary mt-1">
                        <i class="bi bi-info-circle me-1"></i>ระบบจะค้นหาข้อมูลการใช้ประโยชน์หรือชื่อโครงการที่มีกลุ่มคำดังกล่าวตามเงื่อนไขที่เลือก (เจอทั้งหมด หรือ เจอบางคำ)
                    </div>
                </div>
            </div>

            {{-- Row 3: Researcher & Department --}}
            <div class="col-md-6">
                <label class="form-label"><i class="bi bi-person text-primary me-1"></i>นักวิจัย / ผู้แทนลงนาม</label>
                <select class="form-select" id="search_researcher_id" name="researcher_id"></select>
                <input type="hidden" name="researcher_name" id="search_researcher_name" value="{{ request('researcher_name') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label"><i class="bi bi-building text-primary me-1"></i>หน่วยงาน/คณะ (โครงการวิจัย)</label>
                <select class="form-select" name="department_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                            {{ $dept->department_nameTH ?? $dept->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Row 4: Utilize Type --}}
            <div class="col-md-12">
                <label class="form-label">ประเภทการนำไปใช้ประโยชน์</label>
                <select class="form-select" name="utz_group">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($utilizeTypes as $uType)
                        <option value="{{ $uType->utz_type_id }}" {{ request('utz_group') == $uType->utz_type_id ? 'selected' : '' }}>
                            {{ $uType->utz_typr_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Row 5: Date Range --}}
            <div class="col-md-6">
                <label class="form-label">วันที่นำไปใช้ประโยชน์ (เริ่มต้น)</label>
                <input type="date" class="form-control" id="date_start" name="date_start" value="{{ request('date_start') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">วันที่นำไปใช้ประโยชน์ (สิ้นสุด)</label>
                <input type="date" class="form-control" id="date_end" name="date_end" value="{{ request('date_end') }}">
            </div>
        </div>
    </x-search-bar>

    <!-- Results Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-body-tertiary border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการการนำผลงานวิจัยไปใช้ประโยชน์
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2">{{ number_format($items->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45%;">หน่วยงานที่นำไปใช้ประโยชน์ / รายละเอียด</th>
                            <th style="width: 40%;">โครงการวิจัย / นักวิจัย</th>
                            <th style="width: 15%;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="fw-bold mb-1">
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle me-1">#{{ $item->utz_id }}</span>
                                    <a href="{{ route('frontend.rdbprojectutilize.show', $item->utz_id) }}" class="text-decoration-none text-body hover-primary">
                                        <i class="bi bi-building text-primary me-1"></i>{!! $item->utz_department_name ?: 'ไม่ระบุหน่วยงาน' !!}
                                    </a>
                                </div>
                                
                                @if($item->utz_leading)
                                <div class="small text-muted mb-1">
                                    <i class="bi bi-person-badge me-1"></i>ผู้แทน/ผู้ลงนาม: <strong>{!! $item->utz_leading !!}</strong>
                                    @if($item->utz_leading_position)
                                        <span class="text-secondary fw-normal">({!! $item->utz_leading_position !!})</span>
                                    @endif
                                </div>
                                @endif

                                <div class="small d-flex flex-wrap gap-1 align-items-center mt-1">
                                    @if($item->utilizeType)
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                            <i class="bi bi-tag-fill me-1"></i>{{ $item->utilizeType->utz_typr_name }}
                                        </span>
                                    @endif

                                    @if($item->utz_date)
                                        <span class="badge bg-body-secondary text-body-secondary border">
                                            <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($item->utz_date, false, true) }}
                                        </span>
                                    @endif

                                    @if($item->changwat)
                                        <span class="badge bg-body-secondary text-body-secondary border">
                                            <i class="bi bi-geo-alt-fill me-1"></i>{!! $item->changwat->changwat_t ?? '' !!}
                                        </span>
                                    @endif

                                    @if(request('search') && !empty($item->match_sources))
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" 
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
                                @if($item->project)
                                    <div class="fw-bold mb-1">
                                        <i class="bi bi-journal-text text-success me-1"></i>
                                        <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" 
                                           class="text-decoration-none text-body hover-primary">
                                            @if($item->project->pro_code)
                                                <span class="text-primary me-1">[{{ $item->project->pro_code }}]</span>
                                            @endif
                                            {!! $item->project->pro_nameTH !!}
                                        </a>
                                    </div>

                                    @if($item->project->rdbProjectWorks && $item->project->rdbProjectWorks->isNotEmpty())
                                        @php
                                            $head = $item->project->rdbProjectWorks->first();
                                        @endphp
                                        @if($head && $head->researcher)
                                            <div class="small">
                                                <a href="{{ route('frontend.rdbresearcher.show', $head->researcher->researcher_id) }}" class="text-decoration-none text-body hover-primary">
                                                    <i class="bi bi-person-circle text-primary me-1"></i>{{ $head->researcher->prefix->prefix_nameTH ?? '' }}{{ $head->researcher->researcher_fname }} {{ $head->researcher->researcher_lname }}
                                                </a>
                                            </div>
                                        @endif
                                    @endif

                                    @if($item->project->department)
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-building me-1"></i>{{ $item->project->department->department_nameTH }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('frontend.rdbprojectutilize.show', $item->utz_id) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i> ดูข้อมูล
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                ไม่พบข้อมูลการนำผลงานวิจัยไปใช้ประโยชน์
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // TomSelect: Utilize / Project Autocomplete
    const utzSelect = document.getElementById('search_utz_id');
    const utzNameHidden = document.getElementById('search_utz_name');

    if (utzSelect && typeof TomSelect !== 'undefined') {
        const tsUtz = new TomSelect(utzSelect, {
            create: false,
            openOnFocus: true,
            persist: false,
            maxOptions: 20,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            placeholder: '-- พิมพ์เพื่อค้นหาการใช้ประโยชน์/ชื่อโครงการ --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("frontend.rdbprojectutilize.search_utilizes") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => callback(json.results || json))
                    .catch(() => callback());
            },
            onChange: function(value) {
                const item = this.options[value];
                if (item && utzNameHidden) {
                    utzNameHidden.value = item.utz_name || item.text.split(' [')[0];
                } else if (utzNameHidden) {
                    utzNameHidden.value = '';
                }
            },
            render: {
                option: function(data, escape) { return '<div>' + (data._highlight || escape(data.text)) + '</div>'; },
                item: function(data, escape) { return '<div>' + escape(data.text) + '</div>'; },
                no_results: function() { return '<div class="no-results p-2 text-muted">ไม่พบข้อมูลการใช้ประโยชน์ (พิมพ์อย่างน้อย 2 ตัวอักษร)</div>'; }
            }
        });

        const preUtzId = "{{ request('utz_id') }}";
        const preUtzName = @json(request('utz_name') ?: '');
        if (preUtzId || preUtzName) {
            const val = preUtzId || 'pre';
            const label = preUtzName || ('การใช้ประโยชน์ #' + preUtzId);
            tsUtz.addOption({ value: val, text: label, utz_name: preUtzName });
            tsUtz.setValue(val);
        }
    }

    // TomSelect: Researcher Autocomplete
    const researcherSelect = document.getElementById('search_researcher_id');
    const researcherNameHidden = document.getElementById('search_researcher_name');

    if (researcherSelect && typeof TomSelect !== 'undefined') {
        const tsRes = new TomSelect(researcherSelect, {
            create: false,
            openOnFocus: true,
            persist: false,
            maxOptions: 15,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            placeholder: '-- พิมพ์เพื่อค้นหานักวิจัย/ผู้แทนลงนาม --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("frontend.rdbprojectutilize.search_researchers") }}?q=' + encodeURIComponent(query))
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
                no_results: function() { return '<div class="no-results p-2 text-muted">ไม่พบข้อมูลนักวิจัย (พิมพ์อย่างน้อย 2 ตัวอักษร)</div>'; }
            }
        });

        const preResearcherId = "{{ request('researcher_id') }}";
        const preResearcherName = @json(request('researcher_name') ?: '');
        if (preResearcherId || preResearcherName) {
            const val = preResearcherId || 'pre';
            tsRes.addOption({ value: val, text: preResearcherName || ('นักวิจัย #' + preResearcherId) });
            tsRes.setValue(val);
        }
    }

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