@extends('layouts.app')

@section('content')
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ทรัพย์สินทางปัญญา</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-lightbulb-fill me-2 text-warning"></i>ทรัพย์สินทางปัญญา (Intellectual Property)
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($items->total()) }} รายการ
            </span>
            <a href="{{ route('frontend.rdbdip.export', request()->query()) }}" class="btn btn-outline-success btn-sm d-inline-flex align-items-center" style="height: 38px;">
                <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
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
            กำลังแสดงรายการทรัพย์สินทางปัญญาของโครงการ: <strong>{{ strip_tags((string) $project->pro_nameTH) }}</strong>
        </div>
        <a href="{{ route('frontend.rdbdip.index') }}" class="btn btn-sm btn-outline-primary bg-body">แสดงทั้งหมด</a>
    </div>
    @endif

    <!-- Search Component (Styled like Research Projects) -->
    <x-search-bar :searchRoute="route('frontend.rdbdip.index')" 
        simplePlaceholder="ค้นหาจาก: เลขที่คำขอ, เลขที่ทะเบียน/สิทธิบัตร, ชื่อผลงาน, ผู้ประดิษฐ์, โครงการวิจัย...">
        
        @if(request('pro_id'))
            <input type="hidden" name="pro_id" value="{{ request('pro_id') }}">
        @endif

        <div class="row g-3">
            {{-- Row 1: DIP Title Autocomplete Search --}}
                <div class="col-md-12">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-award text-primary me-1"></i> ชื่อผลงาน / ทรัพย์สินทางปัญญา</span>
                        <span class="badge bg-secondary-subtle text-body-secondary" style="font-size: 0.7rem;">พิมพ์เพื่อค้นหาชื่อผลงาน/เลขที่คำขอ</span>
                    </label>
                    <select class="form-select" id="search_dip_id" name="dip_id"></select>
                    <input type="hidden" name="dip_name" id="search_dip_name" value="{{ request('dip_name') }}">
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
                                    <input class="form-check-input" type="radio" name="title_keywords_mode" id="dip_mode_all" value="all" 
                                        {{ request('title_keywords_mode', 'all') === 'all' ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-medium" for="dip_mode_all">
                                        <i class="bi bi-check-all text-success me-1"></i>เจอทั้งหมด (ตรงทุกคำ - AND)
                                    </label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" name="title_keywords_mode" id="dip_mode_any" value="any" 
                                        {{ request('title_keywords_mode') === 'any' ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-medium" for="dip_mode_any">
                                        <i class="bi bi-intersect text-primary me-1"></i>เจอบางคำ (ตรงคำใดคำหนึ่ง - OR)
                                    </label>
                                </div>
                            </div>
                        </div>
                        <input type="text" class="form-control" name="title_keywords" value="{{ request('title_keywords') }}" 
                            placeholder="เช่น อุปกรณ์ เครื่องมือ ระบบ (พิมพ์หลายคำคั่นด้วยช่องว่าง)...">
                        <div class="form-text small text-body-secondary mt-1">
                            <i class="bi bi-info-circle me-1"></i>ระบบจะค้นหาชื่อผลงานทรัพย์สินทางปัญญาที่มีกลุ่มคำดังกล่าวตามเงื่อนไขที่เลือก (เจอทั้งหมด หรือ เจอบางคำ)
                        </div>
                    </div>
                </div>

                {{-- Row 3: Researcher & Department --}}
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-person text-primary me-1"></i>ผู้ทรงสิทธิ / ผู้ประดิษฐ์</label>
                    <select class="form-select" id="search_researcher_id" name="researcher_id"></select>
                    <input type="hidden" name="researcher_name" id="search_researcher_name" value="{{ request('researcher_name') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-building text-primary me-1"></i>หน่วยงาน/คณะ</label>
                    <select class="form-select" name="department_id">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->department_nameTH ?? $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Row 4: Type --}}
                <div class="col-md-12">
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

                {{-- Row 5: Date Range --}}
                <div class="col-md-6">
                    <label class="form-label">วันที่ยื่นคำขอ (เริ่มต้น)</label>
                    <input type="text" class="form-control datepicker" id="date_start" name="date_start" value="{{ request('date_start') }}" placeholder="วว/ดด/ปปปป" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="form-label">วันที่ยื่นคำขอ (สิ้นสุด)</label>
                    <input type="text" class="form-control datepicker" id="date_end" name="date_end" value="{{ request('date_end') }}" placeholder="วว/ดด/ปปปป" autocomplete="off">
                </div>
            </div>
        </x-search-bar>

    <!-- Results Table Card -->
    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการทรัพย์สินทางปัญญา
            </h6>
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
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle me-1">#{{ $item->dip_id }}</span>
                                    <a href="{{ route('frontend.rdbdip.show', $item->dip_id) }}" class="text-decoration-none text-body hover-primary">
                                        {{ strip_tags((string) ($item->dip_data2_name ?: ($item->dip_name ?: 'ไม่มีชื่อผลงาน'))) }}
                                    </a>
                                </div>
                                <div class="small d-flex flex-wrap gap-1 align-items-center mt-1">
                                    @if($item->dipType)
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $item->dipType->dipt_name }}</span>
                                    @endif

                                    @if($item->dip_request_number)
                                        <span class="badge bg-body-secondary text-body-secondary border">
                                            <i class="bi bi-file-earmark-text me-1"></i>คำขอ: {{ $item->dip_request_number }}
                                        </span>
                                    @endif

                                    @if($item->dip_patent_number)
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                            <i class="bi bi-patch-check-fill me-1"></i>ทะเบียน: {{ $item->dip_patent_number }}
                                        </span>
                                    @endif

                                    @if($item->dip_request_date)
                                        <span class="badge bg-body-secondary text-body-secondary border">
                                            <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($item->dip_request_date, false, true) }}
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
                                @if($item->researcher)
                                    <div>
                                        <i class="bi bi-person-circle text-primary me-1"></i>
                                        <a href="{{ route('frontend.rdbresearcher.show', $item->researcher->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body fw-bold">
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
                                           target="_blank" rel="noopener noreferrer"
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
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
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

    // TomSelect: DIP Title Autocomplete
    const dipSelect = document.getElementById('search_dip_id');
    const dipNameHidden = document.getElementById('search_dip_name');

    if (dipSelect && typeof TomSelect !== 'undefined') {
        const tsDip = new TomSelect(dipSelect, {
            create: false,
            openOnFocus: true,
            persist: false,
            maxOptions: 20,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            placeholder: '-- พิมพ์เพื่อค้นหาชื่อผลงาน/เลขที่คำขอ --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("frontend.rdbdip.search_dips") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => callback(json.results || json))
                    .catch(() => callback());
            },
            onChange: function(value) {
                const item = this.options[value];
                if (item && dipNameHidden) {
                    dipNameHidden.value = item.dip_name || item.text.split(' [')[0];
                } else if (dipNameHidden) {
                    dipNameHidden.value = '';
                }
            },
            render: {
                option: function(data, escape) { return '<div>' + (data._highlight || escape(data.text)) + '</div>'; },
                item: function(data, escape) { return '<div>' + escape(data.text) + '</div>'; },
                no_results: function() { return '<div class="no-results p-2 text-muted">ไม่พบข้อมูลทรัพย์สินทางปัญญา (พิมพ์อย่างน้อย 2 ตัวอักษร)</div>'; }
            }
        });

        const preDipId = "{{ request('dip_id') }}";
        const preDipName = @json(request('dip_name') ?: '');
        if (preDipId || preDipName) {
            const val = preDipId || 'pre';
            const label = preDipName || ('ทรัพย์สินทางปัญญา #' + preDipId);
            tsDip.addOption({ value: val, text: label, dip_name: preDipName });
            tsDip.setValue(val);
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
            placeholder: '-- พิมพ์เพื่อค้นหาผู้ทรงสิทธิ/ผู้ประดิษฐ์ --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("frontend.rdbdip.search_researchers") }}?q=' + encodeURIComponent(query))
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

    // Flatpickr Thai Buddhist Date Pickers
    if (typeof initThaiFlatpickr === 'function') {
        const endPicker = initThaiFlatpickr('#date_end');
        initThaiFlatpickr('#date_start', {
            onChange: function(selectedDates, dateStr) {
                if (endPicker) {
                    endPicker.set('minDate', dateStr || null);
                    const currentEnd = endPicker.selectedDates[0];
                    if (currentEnd && selectedDates.length > 0 && currentEnd < selectedDates[0]) {
                        endPicker.clear();
                    }
                }
            }
        });
        const startVal = document.getElementById('date_start').value;
        if (startVal && endPicker) {
            endPicker.set('minDate', startVal);
        }
    }
});
</script>
<style>
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
</style>
@endpush