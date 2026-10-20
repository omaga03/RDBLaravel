@extends('layouts.app')

@section('content')
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ผลงานตีพิมพ์</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-journal-text me-2 text-info"></i>ผลงานตีพิมพ์ (Published Works)
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($items->total()) }} รายการ
            </span>
            <a href="{{ route('frontend.rdbpublished.export', request()->query()) }}" class="btn btn-outline-success btn-sm d-inline-flex align-items-center" style="height: 38px;">
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
    <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 border" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
            <div>
                กำลังแสดงรายการเฉพาะโครงการ: <strong>{!! $project->pro_nameTH !!}</strong>
            </div>
        </div>
        <a href="{{ route('frontend.rdbpublished.index') }}" class="btn btn-sm btn-outline-info bg-body text-info text-decoration-none shadow-sm">
            แสดงทั้งหมด
        </a>
    </div>
    @endif

    <!-- Search Component -->
    <x-search-bar :searchRoute="route('frontend.rdbpublished.index')"
        simplePlaceholder="ค้นหาจาก: ชื่อผลงาน, วารสาร, นักวิจัย, หรือชื่อโครงการ...">

        @if(request('pro_id'))
            <input type="hidden" name="pro_id" value="{{ request('pro_id') }}">
        @endif

        <div class="row g-3">
            {{-- Row 1: Publication Title Autocomplete Search --}}
            <div class="col-md-12">
                <label class="form-label d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-journal-text text-info me-1"></i> ชื่อผลงานตีพิมพ์ / วารสาร</span>
                    <span class="badge bg-secondary-subtle text-body-secondary" style="font-size: 0.7rem;">พิมพ์เพื่อค้นหาชื่อผลงาน</span>
                </label>
                <select class="form-select" id="search_pub_id" name="pub_id"></select>
                <input type="hidden" name="pub_name" id="search_pub_name" value="{{ request('pub_name') }}">
            </div>

            {{-- Row 2: Multi-Keyword Title Search (กลุ่มคำคั่นด้วยช่องว่าง + เจอทั้งหมด / เจอบางคำ) --}}
            <div class="col-md-12">
                <div class="p-3 rounded-3 border bg-body-tertiary">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1.5 gap-2">
                        <label class="form-label mb-0 fw-semibold text-body" style="font-size: 0.88rem;">
                            <i class="bi bi-fonts text-info me-1"></i> ค้นหาชื่อเรื่องแบบกลุ่มคำ (คั่นด้วยช่องว่าง)
                        </label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="title_keywords_mode" id="pub_mode_all" value="all" 
                                    {{ request('title_keywords_mode', 'all') === 'all' ? 'checked' : '' }}>
                                <label class="form-check-label small fw-medium" for="pub_mode_all">
                                    <i class="bi bi-check-all text-success me-1"></i>เจอทั้งหมด (ตรงทุกคำ - AND)
                                </label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="title_keywords_mode" id="pub_mode_any" value="any" 
                                    {{ request('title_keywords_mode') === 'any' ? 'checked' : '' }}>
                                <label class="form-check-label small fw-medium" for="pub_mode_any">
                                    <i class="bi bi-intersect text-primary me-1"></i>เจอบางคำ (ตรงคำใดคำหนึ่ง - OR)
                                </label>
                            </div>
                        </div>
                    </div>
                    <input type="text" class="form-control" name="title_keywords" value="{{ request('title_keywords') }}" 
                        placeholder="เช่น เทคโนโลยี การศึกษา ชุมชน (พิมพ์หลายคำคั่นด้วยช่องว่าง)...">
                    <div class="form-text small text-body-secondary mt-1">
                        <i class="bi bi-info-circle me-1"></i>ระบบจะค้นหาชื่อผลงานหรือวารสารที่มีกลุ่มคำดังกล่าวตามเงื่อนไขที่เลือก (เจอทั้งหมด หรือ เจอบางคำ)
                    </div>
                </div>
            </div>

            {{-- Row 3: Researcher & Department --}}
            <div class="col-md-6">
                <label class="form-label"><i class="bi bi-person text-primary me-1"></i>นักวิจัย / ผู้ประพันธ์</label>
                <select class="form-select" id="search_researcher_id" name="researcher_id"></select>
                <input type="hidden" name="researcher_name" id="search_researcher_name" value="{{ request('researcher_name') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label"><i class="bi bi-building text-primary me-1"></i>หน่วยงาน/คณะ</label>
                <select class="form-select" name="department_id">
                    <option value="">-- ทั้งหมด --</option>
                    @if(isset($departments))
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->department_nameTH ?? $dept->department_name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- Row 4: Type & Year --}}
            <div class="col-md-6">
                <label class="form-label">ประเภทผลงาน</label>
                <select class="form-select" name="pubtype_id">
                    <option value="">-- ทั้งหมด --</option>
                    @if(isset($pubTypes))
                        @foreach($pubTypes as $type)
                            <option value="{{ $type->pubtype_id }}" {{ request('pubtype_id') == $type->pubtype_id ? 'selected' : '' }}>
                                {{ $type->pubtype_group }}{{ $type->pubtype_subgroup ? ' (' . $type->pubtype_subgroup . ')' : '' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">ปีงบประมาณ</label>
                <select class="form-select" name="year_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($years as $year)
                        <option value="{{ $year->year_id }}" {{ request('year_id') == $year->year_id ? 'selected' : '' }}>
                            {{ $year->year_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Row 5: Date Range --}}
            <div class="col-md-6">
                <label class="form-label">วันที่ตีพิมพ์ (เริ่มต้น)</label>
                <input type="date" class="form-control" name="date_start" value="{{ request('date_start') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">วันที่ตีพิมพ์ (สิ้นสุด)</label>
                <input type="date" class="form-control" name="date_end" value="{{ request('date_end') }}">
            </div>
        </div>
    </x-search-bar>

    <!-- Results Table Card -->
    <div class="card shadow-sm border">
        <div class="card-header bg-body-tertiary border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการผลงานตีพิมพ์
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50%;">รายละเอียดผลงาน</th>
                            <th style="width: 25%;">ผู้ประพันธ์ / สังกัด</th>
                            <th style="width: 15%;" class="text-center text-nowrap">วันที่ตีพิมพ์</th>
                            <th style="width: 10%;" class="text-center text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="fw-bold mb-1">
                                    <a href="{{ route('frontend.rdbpublished.show', $item->id) }}" class="text-body fw-bold text-decoration-none hover-primary text-break">
                                        {!! $item->pub_name !!}
                                    </a>
                                </div>
                                <div class="small d-flex flex-wrap gap-2 align-items-center mt-1">
                                    @if($item->pubtype)
                                        <span class="badge bg-body-secondary text-body border">
                                            {{ $item->pubtype->pubtype_name ?? $item->pubtype->pubtype_subgroup ?? '-' }}
                                        </span>
                                    @endif
                                    @if($item->year)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                            ปี {{ $item->year->year_name }}
                                        </span>
                                    @endif
                                </div>
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
                                        <a href="{{ route('frontend.rdbresearcher.show', $mainAuthor->researcher_id) }}" 
                                           class="text-decoration-none text-body fw-bold hover-primary">
                                            <i class="bi bi-person-circle text-primary me-1"></i>{!! $mainAuthor->researcher_fname !!} {!! $mainAuthor->researcher_lname !!}
                                        </a>
                                        @if($otherCount > 0)
                                            <span class="text-body-secondary fw-normal">และ {{ $otherCount }} คน</span>
                                        @endif
                                    </div>
                                    @if($mainAuthor->department)
                                    <small class="text-body-secondary d-block mt-1">
                                        <i class="bi bi-building me-1"></i>{!! $mainAuthor->department->department_nameTH !!}
                                    </small>
                                    @endif
                                @else
                                    <span class="text-body-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-center text-body-secondary small text-nowrap">
                                @if($item->pub_date)
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ \App\Helpers\ThaiDateHelper::format($item->pub_date, false, true) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('frontend.rdbpublished.show', $item->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i> ดูข้อมูล
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-journal-x display-6 d-block mb-2"></i>
                                ไม่พบข้อมูลผลงานตีพิมพ์
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

<style>
.hover-primary {
    transition: color 0.15s ease-in-out;
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
    text-decoration: underline !important;
}
.hover-underline:hover {
    text-decoration: underline !important;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // TomSelect: Publication Title Autocomplete
    const pubSelect = document.getElementById('search_pub_id');
    const pubNameHidden = document.getElementById('search_pub_name');

    if (pubSelect && typeof TomSelect !== 'undefined') {
        const tsPub = new TomSelect(pubSelect, {
            create: false,
            openOnFocus: true,
            persist: false,
            maxOptions: 20,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            placeholder: '-- พิมพ์เพื่อค้นหาชื่อผลงานตีพิมพ์ --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("frontend.rdbpublished.search_publications") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => callback(json.results || json))
                    .catch(() => callback());
            },
            onChange: function(value) {
                const item = this.options[value];
                if (item && pubNameHidden) {
                    pubNameHidden.value = item.pub_name || item.text.split(' [')[0];
                } else if (pubNameHidden) {
                    pubNameHidden.value = '';
                }
            },
            render: {
                option: function(data, escape) { return '<div>' + (data._highlight || escape(data.text)) + '</div>'; },
                item: function(data, escape) { return '<div>' + escape(data.text) + '</div>'; },
                no_results: function() { return '<div class="no-results p-2 text-muted">ไม่พบข้อมูลผลงาน (พิมพ์อย่างน้อย 2 ตัวอักษร)</div>'; }
            }
        });

        const prePubId = "{{ request('pub_id') }}";
        const prePubName = @json(request('pub_name') ?: '');
        if (prePubId || prePubName) {
            const val = prePubId || 'pre';
            const label = prePubName || ('ผลงาน #' + prePubId);
            tsPub.addOption({ value: val, text: label, pub_name: prePubName });
            tsPub.setValue(val);
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
            placeholder: '-- พิมพ์เพื่อค้นหานักวิจัย/ผู้ประพันธ์ --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("frontend.rdbpublished.search_researchers") }}?q=' + encodeURIComponent(query))
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
});
</script>
@endpush