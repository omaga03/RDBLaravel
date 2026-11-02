@extends('layouts.app')

@section('content')
@include('layouts.partials.scholar-meta', ['type' => 'dip', 'item' => $item])
<div class="container-fluid py-4">

    <!-- Breadcrumb & Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('frontend.rdbdip.index') }}" class="text-decoration-none">ทรัพย์สินทางปัญญา</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายละเอียด</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-award-fill me-2 text-primary"></i>รายละเอียดทรัพย์สินทางปัญญา
            </h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontend.rdbdip.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
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
                    <span class="small text-white-50"><i class="bi bi-award me-1"></i> ข้อมูลทรัพย์สินทางปัญญา</span>
                    <h4 class="card-title mb-0 mt-1 fw-semibold text-white text-break" style="font-size: 1.15rem; line-height: 1.55;">
                        {{ $item->dip_name ?: ($item->dip_data2_name ?? 'ทรัพย์สินทางปัญญา') }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Badges Row -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if($item->dipType)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                                <i class="bi bi-bookmark-star me-1"></i> {{ $item->dipType->dipt_name }}
                            </span>
                        @endif
                        @if($item->dip_data2_status)
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1">
                                <i class="bi bi-info-circle me-1"></i> สถานะ: {{ $item->dip_data2_status }}
                            </span>
                        @endif
                        @if($item->dip_patent_number || $item->dip_department_no)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                <i class="bi bi-patch-check me-1"></i> ทะเบียน: {{ $item->dip_patent_number ?? $item->dip_department_no }}
                            </span>
                        @endif
                    </div>

                    <!-- Additional Details Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                @if($item->dip_name && $item->dip_data2_name && $item->dip_name !== $item->dip_data2_name)
                                <tr>
                                    <th class="text-body-secondary fw-semibold" style="width: 28%;">ชื่อผลงานเดิม:</th>
                                    <td class="text-body">{{ $item->dip_data2_name }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th class="text-body-secondary fw-semibold" style="width: 28%;">ประเภททรัพย์สินทางปัญญา:</th>
                                    <td class="text-body">{{ $item->dipType->dipt_name ?? '-' }}</td>
                                </tr>
                                @if($item->dip_request_number || $item->dip_request_date)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">การยื่นคำขอ:</th>
                                    <td class="text-body">
                                        @if($item->dip_request_number)
                                            <span>เลขที่คำขอ: <strong>{{ $item->dip_request_number }}</strong></span>
                                        @endif
                                        @if($item->dip_request_date)
                                            <span class="text-body-secondary ms-2">(ยื่นวันที่ {{ \App\Helpers\ThaiDateHelper::format($item->dip_request_date, false, true) }})</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if($item->dip_publication_no || $item->dip_publication_date)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">ประกาศโฆษณา:</th>
                                    <td class="text-body">
                                        @if($item->dip_publication_no)
                                            <span>เลขที่: <strong>{{ $item->dip_publication_no }}</strong></span>
                                        @endif
                                        @if($item->dip_publication_date)
                                            <span class="text-body-secondary ms-2">(วันที่ {{ \App\Helpers\ThaiDateHelper::format($item->dip_publication_date, false, true) }})</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if($item->dip_patent_number || $item->dip_department_no)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">เลขที่สิทธิบัตร/ทะเบียน:</th>
                                    <td class="text-success fw-bold font-monospace">{{ $item->dip_patent_number ?? $item->dip_department_no }}</td>
                                </tr>
                                @endif
                                @if($item->dip_startdate || $item->dip_enddate)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">ระยะเวลาการคุ้มครอง:</th>
                                    <td class="text-body">
                                        @if($item->dip_startdate)
                                            {{ \App\Helpers\ThaiDateHelper::format($item->dip_startdate, false, true) }}
                                        @endif
                                        <span class="text-body-secondary mx-1">ถึง</span>
                                        @if($item->dip_enddate)
                                            {{ \App\Helpers\ThaiDateHelper::format($item->dip_enddate, false, true) }}
                                        @else
                                            <span class="text-body-secondary">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if($item->researcher && $item->researcher->department)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">สังกัดหน่วยงาน:</th>
                                    <td class="text-body">{{ $item->researcher->department->department_nameTH }}</td>
                                </tr>
                                @elseif($item->project && $item->project->department)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">สังกัดหน่วยงาน:</th>
                                    <td class="text-body">{{ $item->project->department->department_nameTH }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detail & Conclusion Card -->
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-file-text-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">บทสรุปการประดิษฐ์และรายละเอียด (Invention Summary & Details)</h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if($item->dip_data2_conclusion)
                        <div class="abstract-box p-3 p-md-4 border border-start border-4 border-primary rounded-3 mb-3 bg-body-tertiary shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-lightbulb me-1"></i> บทสรุปการประดิษฐ์
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText(this, 'dip-conclusion-text')">
                                    <i class="bi bi-clipboard me-1"></i> คัดลอกบทสรุป
                                </button>
                            </div>
                            <div id="dip-conclusion-text" class="abstract-content abstract-indent">
                                {!! nl2br(e($item->dip_data2_conclusion)) !!}
                            </div>
                        </div>
                    @endif

                    @if($item->dip_note)
                        <div class="abstract-box p-3 p-md-4 border border-start border-4 border-info rounded-3 bg-body-tertiary shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-chat-left-text me-1"></i> รายละเอียด / หมายเหตุ
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText(this, 'dip-note-text')">
                                    <i class="bi bi-clipboard me-1"></i> คัดลอกหมายเหตุ
                                </button>
                            </div>
                            <div id="dip-note-text" class="abstract-content abstract-indent">
                                {!! nl2br(e($item->dip_note)) !!}
                            </div>
                        </div>
                    @endif

                    @if(!$item->dip_data2_conclusion && !$item->dip_note)
                        <div class="abstract-box p-4 border rounded-3 bg-body-tertiary text-center text-body-secondary">
                            <i class="bi bi-file-earmark-text text-muted fs-3 d-block mb-2"></i>
                            <span class="fst-italic">ไม่มีข้อมูลบทสรุปการประดิษฐ์หรือรายละเอียดเพิ่มเติม</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar / Right Column (col-lg-4) -->
        <div class="col-lg-4">
            {{-- 1. Related Project Card (If exists, shown at the top) --}}
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
                    <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-bold text-body hover-primary d-block mb-1.5" style="font-size: 0.92rem; line-height: 1.5;">
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
                        <a href="{{ route('frontend.rdbresearcher.show', $headResearcher->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body hover-primary fw-medium small">
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
                        <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> ดูรายละเอียดโครงการ
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- 2. Inventors / Researchers Card --}}
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-people-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">ผู้ทรงสิทธิ / ผู้ประดิษฐ์</h6>
                    @if($item->researcher)
                        <span class="badge bg-primary-subtle text-primary ms-auto">1</span>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($item->researcher)
                        <div class="list-group list-group-flush">
                            <div class="list-group-item bg-transparent py-2.5 px-3">
                                <div class="d-flex align-items-start">
                                    <div class="me-2 text-primary fs-5 mt-0.5">
                                        <i class="bi bi-person-circle"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex flex-wrap align-items-center gap-1.5 mb-0.5">
                                            <a href="{{ route('frontend.rdbresearcher.show', $item->researcher->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-bold text-body hover-primary">
                                                {{ $item->researcher->prefix->prefix_nameTH ?? '' }}{{ $item->researcher->researcher_fname }} {{ $item->researcher->researcher_lname }}
                                            </a>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem; font-weight: 500; padding: 0.16rem 0.45rem;">
                                                ผู้ประดิษฐ์
                                            </span>
                                        </div>
                                        @if($item->researcher->department)
                                            <small class="text-body-secondary d-block" style="font-size: 0.78rem;">
                                                <i class="bi bi-building me-1"></i>{{ $item->researcher->department->department_nameTH }}
                                            </small>
                                        @elseif($item->researcher->researcher_note)
                                            <small class="text-body-secondary d-block" style="font-size: 0.78rem;">
                                                {{ $item->researcher->researcher_note }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-3 text-body-secondary text-center">ไม่มีข้อมูลผู้ประดิษฐ์</div>
                    @endif
                </div>
            </div>

            {{-- 2. Files Card (High-Impact CTA Card) --}}
            @php
                $additionalFiles = [];
                if($item->dip_data1_files) {
                    $additionalFiles[] = ['field' => 'dip_data1_files', 'name' => 'ไฟล์ข้อมูลชุดที่ 1 (Date Start)', 'sub' => 'Filing Document', 'icon' => 'bi-file-earmark-text'];
                }
                if($item->dip_data2_files_con) {
                    $additionalFiles[] = ['field' => 'dip_data2_files_con', 'name' => 'ไฟล์สัญญาโอนสิทธิ / สรุปผล', 'sub' => 'Invention Summary / Assignment', 'icon' => 'bi-file-earmark-text'];
                }
                if($item->dip_data3_filesass1) {
                    $additionalFiles[] = ['field' => 'dip_data3_filesass1', 'name' => 'ไฟล์เอกสารข้อถือสิทธิ 1', 'sub' => 'Claim Attachment 1', 'icon' => 'bi-file-earmark-check'];
                }
                if($item->dip_data3_filesass2) {
                    $additionalFiles[] = ['field' => 'dip_data3_filesass2', 'name' => 'ไฟล์เอกสารข้อถือสิทธิ 2', 'sub' => 'Claim Attachment 2', 'icon' => 'bi-file-earmark-check'];
                }
                if($item->dip_data3_filesass3) {
                    $additionalFiles[] = ['field' => 'dip_data3_filesass3', 'name' => 'ไฟล์เอกสารข้อถือสิทธิ 3', 'sub' => 'Claim Attachment 3', 'icon' => 'bi-file-earmark-check'];
                }
                if($item->dip_data3_drawing_picture) {
                    $additionalFiles[] = ['field' => 'dip_data3_drawing_picture', 'name' => 'ไฟล์แบบแสดง / รูปเขียน (Drawing)', 'sub' => 'Technical Drawing', 'icon' => 'bi-file-earmark-image'];
                }
                if($item->dip_data_forms_request) {
                    $additionalFiles[] = ['field' => 'dip_data_forms_request', 'name' => 'ไฟล์แบบฟอร์มคำขอ', 'sub' => 'Application Form', 'icon' => 'bi-file-earmark-text'];
                }
            @endphp
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-file-earmark-arrow-down-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">ไฟล์เอกสาร (Files)</h6>
                </div>
                <div class="card-body p-3">
                    @if($item->dip_files || $item->dip_path)
                        <a href="{{ route('frontend.rdbdip.view_file', $item->dip_id) }}" 
                           target="_blank" 
                           class="file-cta-card card border border-success-subtle text-decoration-none shadow-sm mb-3"
                           style="background: rgba(16, 185, 129, 0.04);">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2.5">
                                    <div class="d-flex align-items-center min-w-0 me-2">
                                        <div class="file-icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                                            <i class="bi bi-file-earmark-pdf-fill fs-2"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <h6 class="fw-bold text-success mb-0 text-truncate" style="font-size: 0.98rem;">เอกสารสิทธิบัตรหลัก</h6>
                                            <small class="text-body-secondary d-block" style="font-size: 0.78rem;">Patent Document (PDF)</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 flex-shrink-0" title="เอกสารหลัก" style="font-size: 0.76rem;">
                                        <i class="bi bi-check-circle-fill me-1"></i>ไฟล์หลัก
                                    </span>
                                </div>
                                <div class="btn btn-sm btn-success w-100 rounded-2 fw-medium d-flex align-items-center justify-content-center gap-1.5 py-1.5 text-white shadow-sm">
                                    <i class="bi bi-cloud-arrow-down-fill me-1.5"></i> ดาวน์โหลดเอกสารหลัก
                                </div>
                            </div>
                        </a>
                    @endif

                    @if(count($additionalFiles) > 0)
                        <div class="card border rounded-2 bg-body-tertiary">
                            <div class="card-header bg-transparent py-2 px-3 border-bottom d-flex align-items-center">
                                <i class="bi bi-paperclip text-primary me-2"></i>
                                <span class="fw-semibold text-body small">เอกสารแนบเพิ่มเติม</span>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis ms-auto" style="font-size: 0.7rem;">{{ count($additionalFiles) }}</span>
                            </div>
                            <div class="list-group list-group-flush">
                                @foreach($additionalFiles as $f)
                                    <div class="list-group-item bg-transparent px-3 py-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center min-w-0 me-2">
                                                <i class="bi {{ $f['icon'] }} text-primary fs-5 me-2 flex-shrink-0"></i>
                                                <div class="min-w-0">
                                                    <a href="{{ route('frontend.rdbdip.view_file', ['id' => $item->dip_id, 'field' => $f['field']]) }}" target="_blank" class="text-decoration-none fw-medium text-body hover-primary text-truncate d-block" style="font-size: 0.82rem;">
                                                        {{ $f['name'] }}
                                                    </a>
                                                    <small class="text-body-secondary d-block" style="font-size: 0.72rem;">{{ $f['sub'] }}</small>
                                                </div>
                                            </div>
                                            <a href="{{ route('frontend.rdbdip.view_file', ['id' => $item->dip_id, 'field' => $f['field']]) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 rounded-2 flex-shrink-0" style="font-size: 0.74rem;">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> เปิดดู
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!$item->dip_files && !$item->dip_path && count($additionalFiles) === 0)
                        <div class="p-2.5 border border-dashed rounded bg-body-tertiary mb-0 text-body-secondary small d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-earmark text-muted fs-5 me-2"></i>
                            <span>ไม่มีไฟล์เอกสารแนบ</span>
                        </div>
                    @endif
                </div>
            </div>

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