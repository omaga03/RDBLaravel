@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Breadcrumb & Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('frontend.rdbprojectutilize.index') }}" class="text-decoration-none">การนำไปใช้ประโยชน์</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายละเอียด</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-gear-wide-connected me-2 text-primary"></i>รายละเอียดการนำไปใช้ประโยชน์
            </h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-printer me-1"></i> พิมพ์
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content (Left Column: col-lg-8) -->
        <div class="col-lg-8">
            <!-- Title & Metadata Card -->
            <div class="card shadow-sm border mb-4">
                <div class="card-header text-white p-3 p-md-4" style="background: var(--rdb-gradient-primary);">
                    <span class="small text-white-50"><i class="bi bi-gear me-1"></i> ข้อมูลการนำไปใช้ประโยชน์</span>
                    <h4 class="card-title mb-0 mt-1 fw-semibold text-white text-break" style="font-size: 1.15rem; line-height: 1.55;">
                        {{ $item->project->pro_nameTH ?? 'การนำงานวิจัยไปใช้ประโยชน์' }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Badges Row -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if($item->utilizeType)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                                <i class="bi bi-tag-fill me-1"></i> {{ $item->utilizeType->ut_name }}
                            </span>
                        @endif
                        @if($item->utz_date)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                <i class="bi bi-calendar-check me-1"></i> วันที่นำไปใช้: {{ \App\Helpers\ThaiDateHelper::format($item->utz_date, false, true) }}
                            </span>
                        @endif
                        @if($item->utz_budget)
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1">
                                <i class="bi bi-cash-stack me-1"></i> งบประมาณ: {{ number_format($item->utz_budget, 2) }} บาท
                            </span>
                        @endif
                    </div>

                    <!-- Additional Details Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-body-secondary fw-semibold" style="width: 28%;">หน่วยงานที่นำไปใช้:</th>
                                    <td class="text-body fw-bold">{{ $item->utz_department_name ?? '-' }}</td>
                                </tr>
                                @if($item->utz_date)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">วันที่นำไปใช้ประโยชน์:</th>
                                    <td class="text-body">{{ \App\Helpers\ThaiDateHelper::format($item->utz_date, false, true) }}</td>
                                </tr>
                                @endif
                                @if($item->utz_department_address || $item->changwat)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">สถานที่ / ที่อยู่:</th>
                                    <td class="text-body">
                                        @if($item->utz_department_address)
                                            <div>{{ strip_tags((string) $item->utz_department_address) }}</div>
                                        @endif
                                        @if($item->changwat)
                                            <div class="text-body-secondary small mt-0.5">
                                                <i class="bi bi-geo-alt me-1 text-danger"></i>
                                                {{ $item->changwat->tambon_t ? 'ต.' . $item->changwat->tambon_t : '' }}
                                                {{ $item->changwat->amphoe_t ? 'อ.' . $item->changwat->amphoe_t : '' }}
                                                {{ $item->changwat->changwat_t ? 'จ.' . $item->changwat->changwat_t : '' }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if($item->utz_leading)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">ผู้แทน / ผู้ลงนาม:</th>
                                    <td class="text-body">
                                        <span class="fw-semibold">{{ strip_tags((string) $item->utz_leading) }}</span>
                                        @if($item->utz_leading_position)
                                            <span class="text-body-secondary ms-1">({{ $item->utz_leading_position }})</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if($item->utz_budget)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">งบประมาณ:</th>
                                    <td>
                                        <span class="text-success fw-bold">{{ number_format($item->utz_budget, 2) }}</span>
                                        <span class="text-body-secondary small ms-1">บาท</span>
                                    </td>
                                </tr>
                                @endif
                                @if($item->project && $item->project->department)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">สังกัดหน่วยงานเจ้าของงาน:</th>
                                    <td class="text-body">{{ $item->project->department->department_nameTH }}</td>
                                </tr>
                                @endif
                                @if(isset($item->utz_count))
                                <tr>
                                    <th class="text-body-secondary fw-semibold">จำนวนการเข้าดู:</th>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1" style="font-size: 0.76rem;">
                                            <i class="bi bi-eye me-1"></i> {{ number_format($item->utz_count ?? 0) }} ครั้ง
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detail Card -->
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-file-text-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">รายละเอียดการนำไปใช้ประโยชน์ (Utilization Details)</h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if($item->utz_detail)
                        <div class="abstract-box p-3 p-md-4 border border-start border-4 border-primary rounded-3 bg-body-tertiary shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-info-circle me-1"></i> ข้อมูลรายละเอียด
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText(this, 'utz-detail-text')">
                                    <i class="bi bi-clipboard me-1"></i> คัดลอกรายละเอียด
                                </button>
                            </div>
                            <div id="utz-detail-text" class="abstract-content abstract-indent">
                                {!! strip_tags((string) $item->utz_detail, '<p><br><b><strong><i><em><u><ul><ol><li><span><sub><sup>') !!}
                            </div>
                        </div>
                    @else
                        <div class="abstract-box p-4 border rounded-3 bg-body-tertiary text-center text-body-secondary">
                            <i class="bi bi-file-earmark-text text-body-secondary fs-3 d-block mb-2"></i>
                            <span class="fst-italic">ไม่มีข้อมูลรายละเอียดการนำไปใช้ประโยชน์</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar / Right Column (col-lg-4) -->
        <div class="col-lg-4">
            {{-- 1. Related Project Card --}}
            @if($item->project)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-folder-symlink-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">โครงการวิจัยที่เกี่ยวข้อง</h6>
                </div>
                <div class="card-body">
                    @if($item->project->pro_code)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 mb-1.5" style="font-size: 0.72rem;">
                            {{ $item->project->pro_code }}
                        </span>
                    @endif
                    <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" class="text-decoration-none fw-bold text-body hover-primary d-block mb-1.5" style="font-size: 0.92rem; line-height: 1.5;">
                        {{ strip_tags((string) $item->project->pro_nameTH) }}
                    </a>

                    @if($item->project->type || $item->project->typeSub)
                    <div class="text-body-secondary small mb-1" style="font-size: 0.78rem;">
                        {{ $item->project->type->pt_name ?? '' }}
                        @if($item->project->type && $item->project->typeSub) • @endif
                        {{ $item->project->typeSub->pts_name ?? '' }}
                    </div>
                    @endif

                    @if($item->project->department)
                    <div class="text-body-secondary small mb-2" style="font-size: 0.78rem;">
                        <i class="bi bi-building me-1"></i>{{ $item->project->department->department_nameTH }}
                    </div>
                    @endif

                    @php
                        $allWorks = $item->project->rdbProjectWorks ? $item->project->rdbProjectWorks->filter(fn($w) => !empty($w->researcher)) : collect();
                        $headWork = $allWorks->firstWhere('position_id', 2) 
                                 ?? $allWorks->firstWhere('position_id', 1) 
                                 ?? $allWorks->first();
                        $headResearcher = $headWork?->researcher;
                        $remainingWorks = $headResearcher ? $allWorks->reject(fn($w) => $w->researcher_id === $headResearcher->researcher_id)->unique('researcher_id') : collect();
                        $remainingCount = $remainingWorks->count();
                        $remainingNames = $remainingWorks->map(function($work) {
                            $prefix = $work->researcher->prefix->prefix_nameTH ?? '';
                            return e($prefix . $work->researcher->researcher_fname . ' ' . $work->researcher->researcher_lname);
                        })->filter()->implode('<br>• ');
                    @endphp
                    @if($headResearcher)
                    <hr class="my-2 border-secondary-subtle">
                    <div class="small fw-semibold text-body mb-1" style="font-size: 0.78rem;">หัวหน้าโครงการ:</div>
                    <div class="d-flex align-items-center flex-wrap">
                        <i class="bi bi-person-circle text-primary me-2 fs-6"></i>
                        <a href="{{ route('frontend.rdbresearcher.show', $headResearcher->researcher_id) }}" class="text-decoration-none text-body hover-primary fw-medium small">
                            {{ $headResearcher->prefix->prefix_nameTH ?? '' }}{{ $headResearcher->researcher_fname }} {{ $headResearcher->researcher_lname }}
                        </a>
                        @if($remainingCount > 0)
                            <span class="text-body-secondary small ms-1" 
                                  data-bs-toggle="tooltip" 
                                  data-bs-placement="top" 
                                  data-bs-html="true"
                                  title="<strong>นักวิจัยร่วม:</strong><br>• {!! strip_tags($remainingNames, '<br>') !!}"
                                  style="cursor: pointer;">(+{{ $remainingCount }})</span>
                        @endif
                    </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> ดูรายละเอียดโครงการ
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- 2. Utilization Focus / Types Card (ประเด็นการนำไปใช้ประโยชน์) --}}
            @php
                $utzGroupIds = !empty($item->utz_group) ? array_filter(array_map('trim', explode(',', $item->utz_group))) : [];
                $utzTypes = !empty($utzGroupIds) ? \App\Models\RdbProjectUtilizeType::whereIn('utz_type_id', $utzGroupIds)->get() : collect();
            @endphp
            @if($utzTypes->isNotEmpty() || $item->utz_group_qa)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2.5 px-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-bookmark-check-fill text-primary me-2"></i>
                        <h6 class="mb-0 fw-semibold text-body">ประเด็นการนำไปใช้ประโยชน์</h6>
                    </div>
                    @if($utzTypes->count() > 0)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">{{ $utzTypes->count() }} ด้าน</span>
                    @endif
                </div>
                <div class="card-body p-3">
                    @if($utzTypes->isNotEmpty())
                        <div class="d-flex flex-column gap-2 {{ $item->utz_group_qa ? 'mb-2.5' : '' }}">
                            @foreach($utzTypes as $type)
                                <div class="d-flex align-items-center p-2 rounded-2 bg-body-tertiary border">
                                    <i class="bi bi-check-circle-fill text-success me-2 fs-6 flex-shrink-0"></i>
                                    <span class="text-body fw-medium" style="font-size: 0.82rem; line-height: 1.4;">{{ $type->utz_typr_name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($item->utz_group_qa)
                        <div class="d-flex align-items-center p-2 rounded-2 bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size: 0.78rem;">
                            <i class="bi bi-shield-check text-success fs-5 me-2 flex-shrink-0"></i>
                            <span>เป็นไปตามเกณฑ์การประกันคุณภาพการศึกษา (QA)</span>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- 3. Files Card (High-Impact CTA Card) --}}
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-file-earmark-arrow-down-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">ไฟล์เอกสาร (Files)</h6>
                </div>
                <div class="card-body p-3">
                    @if($item->utz_files || $item->uf_path)
                        <a href="{{ route('frontend.rdbprojectutilize.view_file', $item->utz_id) }}" 
                           target="_blank" 
                           class="file-cta-card card border border-success-subtle text-decoration-none shadow-sm"
                           style="background: rgba(16, 185, 129, 0.04);">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2.5">
                                    <div class="d-flex align-items-center min-w-0 me-2">
                                        <div class="file-icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                                            <i class="bi bi-file-earmark-pdf-fill fs-2"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <h6 class="fw-bold text-success mb-0 text-truncate" style="font-size: 0.98rem;">เอกสารการนำไปใช้ประโยชน์</h6>
                                            <small class="text-body-secondary d-block" style="font-size: 0.78rem;">Utilization Document (PDF)</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 flex-shrink-0" title="ดาวน์โหลด: {{ number_format($item->utz_countfile ?? 0) }} ครั้ง" style="font-size: 0.76rem;">
                                        <i class="bi bi-download me-1"></i>{{ number_format($item->utz_countfile ?? 0) }}
                                    </span>
                                </div>
                                <div class="btn btn-sm btn-success w-100 rounded-2 fw-medium d-flex align-items-center justify-content-center gap-1.5 py-1.5 text-white shadow-sm">
                                    <i class="bi bi-cloud-arrow-down-fill"></i> ดาวน์โหลดเอกสารแนบ
                                </div>
                            </div>
                        </a>
                    @else
                        <div class="p-2.5 border border-dashed rounded bg-body-tertiary mb-0 text-body-secondary small d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-earmark text-muted fs-5 me-2"></i>
                            <span>ไม่มีไฟล์เอกสารแนบ</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 4. Location & Map Card (พิกัดสถานที่นำไปใช้ประโยชน์) --}}
            @php
                $hasCoords = $item->changwat && !empty($item->changwat->lat) && !empty($item->changwat->long);
                $locationName = $item->changwat 
                    ? trim(($item->changwat->tambon_t ? $item->changwat->tambon_t . ' ' : '') . 
                           ($item->changwat->amphoe_t ? $item->changwat->amphoe_t . ' ' : '') . 
                           ($item->changwat->changwat_t ?? ''))
                    : '';
                $mapQuery = $hasCoords 
                    ? ($item->changwat->lat . ',' . $item->changwat->long) 
                    : ($locationName ?: ($item->utz_department_name ?? ''));
            @endphp
            @if($hasCoords || !empty($locationName))
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2.5 px-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                        <h6 class="mb-0 fw-semibold text-body">พิกัดสถานที่นำไปใช้ประโยชน์</h6>
                    </div>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.7rem;">Map</span>
                </div>
                <div class="card-body p-0">
                    <div class="ratio ratio-16x9">
                        <iframe style="border:0;" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"
                            src="https://maps.google.com/maps?q={{ urlencode($mapQuery) }}&z=13&output=embed">
                        </iframe>
                    </div>
                    <div class="p-3 bg-body-tertiary border-top">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="min-w-0">
                                @if(!empty($locationName))
                                    <span class="fw-semibold text-body d-block small">
                                        <i class="bi bi-pin-map text-danger me-1"></i>{{ $locationName }}
                                    </span>
                                @endif
                                @if($hasCoords)
                                    <small class="text-body-secondary font-monospace d-block" style="font-size: 0.74rem;">
                                        พิกัด: {{ $item->changwat->lat }}, {{ $item->changwat->long }}
                                    </small>
                                @endif
                            </div>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($mapQuery) }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-danger py-1 px-2.5 rounded-2 d-inline-flex align-items-center flex-shrink-0"
                               style="font-size: 0.75rem;">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Google Maps
                            </a>
                        </div>
                        <div class="mt-2 pt-2 border-top text-body-secondary fst-italic d-flex align-items-start gap-1.5" style="font-size: 0.72rem; line-height: 1.45;">
                            <i class="bi bi-info-circle text-muted mt-0.5 flex-shrink-0"></i>
                            <span>หมายเหตุ: พิกัดบนแผนที่เป็นตำแหน่งโดยประมาณระดับพื้นที่/ตำบล อาจไม่ใช่พิกัดของสถานที่ที่นำไปใช้ประโยชน์จริง</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
    .hover-underline:hover {
        text-decoration: underline !important;
    }

    /* High-Impact File CTA Card Styling */
    .file-cta-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        border-radius: 10px;
    }
    .file-cta-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.12) !important;
    }
    .file-cta-card:active {
        transform: translateY(0);
    }

    /* Longform Typography & Box Styling */
    .abstract-box {
        border-radius: 12px;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .abstract-box:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }
    .abstract-content {
        font-size: 0.96rem;
        line-height: 1.88;
        letter-spacing: 0.012em;
        text-align: justify;
        text-justify: inter-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .abstract-indent {
        text-indent: 2.25rem;
    }
    .abstract-content p {
        margin-bottom: 0.85rem;
        text-indent: 2.25rem;
    }
    [data-bs-theme="dark"] .abstract-content {
        color: #d1d5db; /* Softer, comfortable reading tone in dark mode */
    }
    [data-bs-theme="light"] .abstract-content {
        color: #334155; /* Slate-700 for optimal contrast without eye strain */
    }
    .copy-abstract-btn {
        transition: all 0.15s ease;
    }
    .copy-abstract-btn:hover {
        background-color: var(--bs-secondary-bg);
        color: var(--bs-body-color);
    }
</style>

<script>
    function copyTextToClipboard(text, onSuccess, onError) {
        if (!text) return;
        text = text.trim();

        // 1. Modern Clipboard API (available in HTTPS or localhost)
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                if (typeof onSuccess === 'function') onSuccess();
            }).catch(() => {
                execFallback(text, onSuccess, onError);
            });
        } else {
            // 2. Fallback for non-secure contexts (e.g. http:// domain in Laragon / local dev)
            execFallback(text, onSuccess, onError);
        }

        function execFallback(str, successCb, errorCb) {
            let textarea = document.createElement('textarea');
            textarea.value = str;
            textarea.style.position = 'fixed';
            textarea.style.top = '0';
            textarea.style.left = '0';
            textarea.style.width = '2em';
            textarea.style.height = '2em';
            textarea.style.padding = '0';
            textarea.style.border = 'none';
            textarea.style.outline = 'none';
            textarea.style.boxShadow = 'none';
            textarea.style.background = 'transparent';
            textarea.style.opacity = '0';
            textarea.setAttribute('readonly', '');
            document.body.appendChild(textarea);

            textarea.focus();
            textarea.select();

            let successful = false;
            try {
                successful = document.execCommand('copy');
            } catch (err) {
                console.error('execCommand copy failed:', err);
            }
            document.body.removeChild(textarea);

            if (successful) {
                if (typeof successCb === 'function') successCb();
            } else {
                if (typeof errorCb === 'function') errorCb();
            }
        }
    }

    function copyAbstractText(target, triggerBtn) {
        let btn = (triggerBtn && triggerBtn.nodeType) ? triggerBtn : (target && target.nodeType ? target : null);
        let elId = (typeof target === 'string') ? target : (typeof triggerBtn === 'string' ? triggerBtn : null);
        if (!elId) return;
        const el = document.getElementById(elId);
        if (!el) return;
        const text = el.innerText || el.textContent || '';

        copyTextToClipboard(text, () => {
            if (!btn) return;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 text-success me-1"></i> คัดลอกแล้ว';
            btn.classList.add('border-success');
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('border-success');
            }, 2000);
        }, () => {
            window.prompt("กด Ctrl+C เพื่อคัดลอกข้อความ:", text);
        });
    }
</script>
@endsection