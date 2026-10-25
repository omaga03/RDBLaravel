@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Breadcrumb & Back -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('frontend.rdbresearcher.index') }}" class="text-decoration-none">นักวิจัย</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $item->prefix->prefix_nameTH ?? '' }}{{ $item->researcher_fname }} {{ $item->researcher_lname }}
                    </li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-person-badge me-2 text-primary"></i>ข้อมูลประวัตินักวิจัย
            </h3>
        </div>
        <a href="{{ route('frontend.rdbresearcher.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
        </a>
    </div>

    <div class="row g-4">
        <!-- Sidebar: Profile Card (PDPA Compliant - no personal phone/email/address/ID card) -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm sticky-top border" style="top: 20px;">
                <div class="card-body text-center p-4">
                    <!-- Profile Image -->
                    <div class="mb-3 position-relative d-inline-block">
                        @php
                            $profileImg = $item->profile_image_url;
                        @endphp
                        @if($profileImg)
                            <img src="{{ $profileImg }}"
                                 alt="{{ $item->researcher_fname }}"
                                 class="rounded-circle object-fit-cover shadow-sm"
                                 style="width: 140px; height: 140px; border: 4px solid var(--bs-border-color); box-shadow: 0 0 10px rgba(0,0,0,0.15);">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto text-white fw-bold shadow-sm"
                                 style="width: 140px; height: 140px; font-size: 3.5rem; border: 4px solid var(--bs-border-color);">
                                {{ strtoupper(mb_substr($item->researcher_fnameEN ?? $item->researcher_fname ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <!-- Name (visible in both light & dark themes) -->
                    <h5 class="card-title mb-1 fw-semibold text-body text-break" style="font-size: 1.1rem;">
                        {{ $item->prefix->prefix_nameTH ?? '' }}{{ $item->researcher_fname }} {{ $item->researcher_lname }}
                    </h5>
                    @if($item->researcher_fnameEN || $item->researcher_lnameEN)
                        <p class="text-body-secondary small mb-2 text-break">
                            {{ $item->researcher_fnameEN }} {{ $item->researcher_lnameEN }}
                        </p>
                    @endif

                    <!-- Department (wrapped to prevent overflow) -->
                    @if($item->department)
                        <div class="mb-2">
                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle border px-2.5 py-1.5 text-wrap text-break"
                                  style="max-width: 100%; white-space: normal; line-height: 1.4;">
                                <i class="bi bi-building me-1"></i>{{ $item->department->department_nameTH }}
                            </span>
                        </div>
                    @endif

                    <!-- Status -->
                    @if($item->status)
                        <div class="mb-3">
                            <span class="badge bg-{{ $item->status->restatus_id == 1 ? 'success' : 'secondary' }} rounded-pill px-3 py-1">
                                {{ $item->status->rst_name }}
                            </span>
                        </div>
                    @endif

                    <!-- Academic Metrics (H-index, Scopus, ORCID) -->
                    @if($item->researcher_hindex || $item->scopus_authorId || $item->orcid)
                        <hr class="my-3">
                        <div class="text-start">
                            <h6 class="text-body-secondary fw-bold small text-uppercase mb-2">
                                <i class="bi bi-award me-1"></i>ข้อมูลทางวิชาการ
                            </h6>
                            @if($item->researcher_hindex)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-body-tertiary border rounded">
                                    <span class="small text-body"><i class="bi bi-graph-up text-primary me-1"></i>H-Index</span>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle fs-6">{{ $item->researcher_hindex }}</span>
                                </div>
                            @endif
                            @if($item->scopus_authorId)
                                <div class="mb-2 p-2 bg-body-tertiary border rounded small">
                                    <div class="text-body-secondary mb-1"><i class="bi bi-journal-text text-primary me-1"></i>Scopus Author ID</div>
                                    <a href="https://www.scopus.com/authid/detail.uri?authorId={{ $item->scopus_authorId }}"
                                       target="_blank" rel="noopener" class="text-decoration-none fw-bold text-primary hover-underline">
                                        {{ $item->scopus_authorId }} <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                </div>
                            @endif
                            @if($item->orcid)
                                <div class="p-2 bg-body-tertiary border rounded small">
                                    <div class="text-body-secondary mb-1"><i class="bi bi-person-badge text-success me-1"></i>ORCID ID</div>
                                    <a href="https://orcid.org/{{ $item->orcid }}"
                                       target="_blank" rel="noopener" class="text-decoration-none fw-bold text-success hover-underline">
                                        {{ $item->orcid }} <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <hr class="my-3">
                    <div class="d-grid">
                        <a href="{{ route('frontend.rdbresearcher.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Connected Work Tabs -->
        <div class="col-lg-9 col-md-8">
            <div class="card shadow-sm border">
                <!-- Tab Header: Transparent bg, scrollable tabs to prevent overflow -->
                <div class="card-header border-bottom p-0 bg-transparent">
                    <ul class="nav nav-tabs card-header-tabs m-0 px-2 pt-2 flex-nowrap overflow-x-auto" id="researcherTabs" role="tablist" style="scrollbar-width: thin;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold text-nowrap px-3 py-2" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab">
                                <i class="bi bi-folder2-open me-1 text-primary"></i> โครงการวิจัย 
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-1">{{ $item->rdbProjects->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold text-nowrap px-3 py-2" id="publications-tab" data-bs-toggle="tab" data-bs-target="#publications" type="button" role="tab">
                                <i class="bi bi-journal-richtext me-1 text-success"></i> ผลงานตีพิมพ์ 
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill ms-1">{{ $item->rdbPublisheds->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold text-nowrap px-3 py-2" id="utilizations-tab" data-bs-toggle="tab" data-bs-target="#utilizations" type="button" role="tab">
                                <i class="bi bi-boxes me-1 text-info"></i> การนำไปใช้ 
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill ms-1">{{ $utilizations->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold text-nowrap px-3 py-2" id="ip-tab" data-bs-toggle="tab" data-bs-target="#ip" type="button" role="tab">
                                <i class="bi bi-shield-check me-1 text-warning"></i> ทรัพย์สินทางปัญญา 
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill ms-1">{{ $item->rdbDips->count() }}</span>
                            </button>
                        </li>
                        @if($item->rdbResearcherEducations && $item->rdbResearcherEducations->count() > 0)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold text-nowrap px-3 py-2" id="education-tab" data-bs-toggle="tab" data-bs-target="#education" type="button" role="tab">
                                <i class="bi bi-mortarboard me-1 text-secondary"></i> ประวัติการศึกษา 
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill ms-1">{{ $item->rdbResearcherEducations->count() }}</span>
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="tab-content" id="researcherTabsContent">

                        <!-- ================= 1. Projects Tab ================= -->
                        <div class="tab-pane fade show active" id="projects" role="tabpanel">
                            @if($item->rdbProjects && $item->rdbProjects->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 table-paginated" id="projects-table" data-page-size="10">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 90px;" class="text-center text-nowrap">ปีงบฯ</th>
                                                <th>โครงการวิจัย</th>
                                                <th style="width: 160px;" class="text-center text-nowrap">บทบาทในโครงการ</th>
                                                <th style="width: 120px;" class="text-center text-nowrap">สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->rdbProjects as $project)
                                                <tr>
                                                    <td class="text-center fw-bold text-body-secondary text-nowrap">
                                                        {{ $project->year->year_name ?? '-' }}
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold mb-1">
                                                            @if($project->pro_code)
                                                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle me-1">{{ $project->pro_code }}</span>
                                                            @endif
                                                            <a href="{{ route('frontend.rdbproject.show', $project->pro_id) }}" target="_blank" class="text-body fw-bold text-decoration-none hover-primary text-break">
                                                                {{ strip_tags((string) $project->pro_nameTH) }}
                                                            </a>
                                                        </div>
                                                        <div class="small text-body-secondary d-flex flex-wrap gap-2 align-items-center mt-1">
                                                            @if($project->type)
                                                                <span class="badge bg-body-secondary text-body border"><i class="bi bi-tag me-1"></i>{{ $project->type->pt_name }}</span>
                                                            @endif
                                                            @if($project->typeSub)
                                                                <span>• {{ $project->typeSub->pts_name }}</span>
                                                            @endif
                                                            @if($project->pro_budget)
                                                                <span class="text-success fw-semibold">
                                                                    <i class="bi bi-cash me-1"></i>งบประมาณ {{ number_format($project->pro_budget, 0) }} บาท
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        @if(isset($projectPositions) && $project->pivot && $project->pivot->position_id && isset($projectPositions[$project->pivot->position_id]))
                                                            @php
                                                                $posId = $project->pivot->position_id;
                                                                $roleName = $projectPositions[$posId]->position_nameTH;
                                                                $badgeClass = match($posId) {
                                                                    1 => 'bg-danger text-white',
                                                                    2 => 'bg-primary text-white',
                                                                    3 => 'bg-warning text-dark',
                                                                    4 => 'bg-info text-dark',
                                                                    default => 'bg-secondary text-white'
                                                                };
                                                            @endphp
                                                            <span class="badge {{ $badgeClass }} px-2 py-1 text-nowrap">
                                                                {{ $roleName }}
                                                            </span>
                                                        @else
                                                            <span class="text-body-secondary">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center text-nowrap">
                                                        @if($project->status)
                                                            <span class="badge px-2.5 py-1" style="background-color: {{ $project->status->ps_color ?? '#6c757d' }}; color: #fff;">
                                                                {{ $project->status->ps_name }}
                                                            </span>
                                                        @else
                                                            <span class="text-body-secondary">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-body-secondary">
                                    <i class="bi bi-folder-x display-6 d-block mb-2"></i>
                                    ไม่พบข้อมูลโครงการวิจัย
                                </div>
                            @endif
                        </div>

                        <!-- ================= 2. Publications Tab ================= -->
                        <div class="tab-pane fade" id="publications" role="tabpanel">
                            @if($item->rdbPublisheds && $item->rdbPublisheds->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 table-paginated" id="publications-table" data-page-size="10">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ชื่อผลงานตีพิมพ์</th>
                                                <th style="width: 160px;" class="text-center text-nowrap">บทบาทผู้เขียน</th>
                                                <th style="width: 140px;" class="text-center text-nowrap">วันที่ตีพิมพ์</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->rdbPublisheds as $pub)
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold mb-1">
                                                            <a href="{{ route('frontend.rdbpublished.show', $pub->id) }}" target="_blank" class="text-body fw-bold text-decoration-none hover-primary text-break">
                                                                {{ strip_tags((string) $pub->pub_name) }}
                                                            </a>
                                                        </div>
                                                        @if($pub->pub_name_journal)
                                                            <small class="text-body-secondary d-block mb-1 text-break">
                                                                <i class="bi bi-book me-1"></i>{{ strip_tags((string) $pub->pub_name_journal) }}
                                                            </small>
                                                        @endif
                                                        <div class="small d-flex flex-wrap gap-2 align-items-center mt-1">
                                                            @if($pub->pubtype)
                                                                <span class="badge bg-body-secondary text-body border">
                                                                    {{ $pub->pubtype->pubtype_name ?? $pub->pubtype->pubtype_subgroup ?? '-' }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-center text-nowrap">
                                                        @if(isset($pub->authorType))
                                                            @php
                                                                $authColor = match($pub->authorType->pubta_id) {
                                                                    1 => 'bg-primary text-white',
                                                                    2 => 'bg-info text-dark',
                                                                    3 => 'bg-secondary text-white',
                                                                    4 => 'bg-success text-white',
                                                                    default => 'bg-body-secondary text-body border'
                                                                };
                                                            @endphp
                                                            <span class="badge {{ $authColor }} px-2 py-1">
                                                                {{ $pub->authorType->pubta_nameTH }}
                                                            </span>
                                                        @else
                                                            <span class="text-body-secondary">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center text-body-secondary small text-nowrap">
                                                        @if($pub->pub_date)
                                                            <i class="bi bi-calendar3 me-1"></i>
                                                            {{ \App\Helpers\ThaiDateHelper::format($pub->pub_date, false, true) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-body-secondary">
                                    <i class="bi bi-journal-x display-6 d-block mb-2"></i>
                                    ไม่พบข้อมูลผลงานตีพิมพ์
                                </div>
                            @endif
                        </div>

                        <!-- ================= 3. Utilizations Tab ================= -->
                        <div class="tab-pane fade" id="utilizations" role="tabpanel">
                            @if($utilizations && $utilizations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 table-paginated" id="utilizations-table" data-page-size="10">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 140px;" class="text-center text-nowrap">วันที่นำไปใช้</th>
                                                <th>หน่วยงาน / รายละเอียดการนำไปใช้</th>
                                                <th>โครงการวิจัยที่เกี่ยวข้อง</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($utilizations as $util)
                                                <tr>
                                                    <td class="text-center text-body-secondary small text-nowrap">
                                                        @if($util->utz_date)
                                                            <i class="bi bi-calendar3 me-1"></i>
                                                            {{ \App\Helpers\ThaiDateHelper::format($util->utz_date, false, true) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($util->utz_department_name)
                                                            <div class="fw-bold mb-1">
                                                                <a href="{{ route('frontend.rdbprojectutilize.show', $util->utz_id) }}" target="_blank" class="text-body fw-bold text-decoration-none hover-primary text-break">
                                                                    <i class="bi bi-building me-1 text-primary"></i>{{ strip_tags((string) $util->utz_department_name) }}
                                                                </a>
                                                            </div>
                                                        @endif

                                                        @if($util->changwat)
                                                            <small class="text-body-secondary d-block mb-1 text-break">
                                                                <i class="bi bi-geo-alt me-1"></i>
                                                                @if($util->changwat->tambon_t) ต.{{ $util->changwat->tambon_t }} @endif
                                                                @if($util->changwat->amphoe_t) อ.{{ $util->changwat->amphoe_t }} @endif
                                                                @if($util->changwat->changwat_t) จ.{{ $util->changwat->changwat_t }} @endif
                                                            </small>
                                                        @endif

                                                        @if($util->utz_leading)
                                                            <small class="text-body-secondary d-block text-break">
                                                                <i class="bi bi-person me-1"></i>ผู้ลงนาม: {{ strip_tags((string) $util->utz_leading) }}
                                                                @if($util->utz_leading_position) ({{ $util->utz_leading_position }}) @endif
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($util->project)
                                                            <div class="small">
                                                                <a href="{{ route('frontend.rdbproject.show', $util->project->pro_id) }}" target="_blank" class="text-primary text-decoration-none hover-underline text-break">
                                                                    <i class="bi bi-folder2 me-1"></i>{{ strip_tags((string) $util->project->pro_nameTH) }}
                                                                </a>
                                                            </div>
                                                        @else
                                                            <span class="text-body-secondary">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-body-secondary">
                                    <i class="bi bi-box-seam display-6 d-block mb-2"></i>
                                    ไม่พบข้อมูลการนำไปใช้ประโยชน์
                                </div>
                            @endif
                        </div>

                        <!-- ================= 4. Intellectual Property (DIP) Tab ================= -->
                        <div class="tab-pane fade" id="ip" role="tabpanel">
                            @if($item->rdbDips && $item->rdbDips->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 table-paginated" id="ip-table" data-page-size="10">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 180px;" class="text-nowrap">เลขที่คำขอ / ทะเบียน</th>
                                                <th>ชื่อผลงาน / ข้อมูลทรัพย์สินทางปัญญา</th>
                                                <th style="width: 140px;" class="text-center text-nowrap">วันที่คำขอ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->rdbDips as $dip)
                                                <tr>
                                                    <td>
                                                        @if($dip->dip_request_number)
                                                            <div class="fw-bold">
                                                                <span class="badge bg-body-secondary text-body border">
                                                                    คำขอ: {{ $dip->dip_request_number }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                        @if($dip->dip_patent_number ?? $dip->dip_number)
                                                            <div class="mt-1">
                                                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                                                    ทะเบียน: {{ $dip->dip_patent_number ?? $dip->dip_number }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold mb-1">
                                                            <a href="{{ route('frontend.rdbdip.show', $dip->dip_id) }}" target="_blank" class="text-body fw-bold text-decoration-none hover-primary text-break">
                                                                {{ strip_tags((string) ($dip->dip_data2_name ?: ($dip->dip_name ?: 'ไม่มีชื่อผลงาน'))) }}
                                                            </a>
                                                        </div>
                                                        <div class="small d-flex flex-wrap gap-2 align-items-center mt-1">
                                                            @if($dip->dipType)
                                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $dip->dipType->dipt_name }}</span>
                                                            @endif
                                                            @if($dip->project)
                                                                <span class="text-body-secondary">
                                                                    <i class="bi bi-link-45deg"></i> โครงการ:
                                                                    <a href="{{ route('frontend.rdbproject.show', $dip->project->pro_id) }}" target="_blank" class="text-primary text-decoration-none hover-underline text-break">
                                                                        {{ strip_tags((string) $dip->project->pro_nameTH) }}
                                                                    </a>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-center text-body-secondary small text-nowrap">
                                                        @if($dip->dip_request_date)
                                                            <i class="bi bi-calendar3 me-1"></i>
                                                            {{ \App\Helpers\ThaiDateHelper::format($dip->dip_request_date, false, true) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-body-secondary">
                                    <i class="bi bi-shield-x display-6 d-block mb-2"></i>
                                    ไม่พบข้อมูลทรัพย์สินทางปัญญา
                                </div>
                            @endif
                        </div>

                        <!-- ================= 5. Education Tab ================= -->
                        @if($item->rdbResearcherEducations && $item->rdbResearcherEducations->count() > 0)
                        <div class="tab-pane fade" id="education" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 table-paginated" id="education-table" data-page-size="10">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 90px;" class="text-center text-nowrap">ปีที่จบ</th>
                                            <th style="width: 140px;" class="text-nowrap">ระดับการศึกษา</th>
                                            <th>วุฒิการศึกษา</th>
                                            <th>สถาบันการศึกษา</th>
                                            <th style="width: 130px;" class="text-center text-nowrap">สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($item->rdbResearcherEducations as $edu)
                                            <tr>
                                                <td class="text-center fw-bold text-body-secondary text-nowrap">
                                                    {{ $edu->reedu_year ?: '-' }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-body-secondary text-body border">
                                                        {{ $edu->reedu_type ?: '-' }}
                                                    </span>
                                                </td>
                                                <td class="fw-semibold text-body text-break">
                                                    {{ $edu->reedu_cational ?: '-' }}
                                                </td>
                                                <td class="text-body-secondary text-break">
                                                    {{ $edu->reedu_name ?: '-' }}
                                                </td>
                                                <td class="text-center small text-nowrap">
                                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle border">
                                                        {{ $edu->reedu_status ?: 'จบการศึกษาแล้ว' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
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
.table-paginated-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding-top: 0.85rem;
    margin-top: 0.5rem;
    border-top: 1px solid var(--bs-border-color);
}
.table-paginated-footer .pagination .page-link {
    cursor: pointer;
    font-size: 0.82rem;
    padding: 0.28rem 0.65rem;
}
.table-paginated-footer .form-select-sm {
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialize pagination for all paginated tables
    document.querySelectorAll('table.table-paginated').forEach(table => {
        initTablePagination(table, 10);
    });

    // 2. Tab URL hash synchronization
    const hash = window.location.hash;
    if (hash) {
        const targetBtn = document.querySelector(`#researcherTabs button[data-bs-target="${hash}"]`);
        if (targetBtn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(targetBtn).show();
        }
    }

    document.querySelectorAll('#researcherTabs button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target && history.replaceState) {
                history.replaceState(null, null, target);
            }
        });
    });

    function initTablePagination(table, initialPageSize = 10) {
        if (!table) return;
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const totalRows = rows.length;
        if (totalRows === 0) return;

        let pageSize = parseInt(table.getAttribute('data-page-size')) || initialPageSize;
        let currentPage = 1;

        const wrapper = table.closest('.table-responsive') || table;
        const footer = document.createElement('div');
        footer.className = 'table-paginated-footer';

        const leftContainer = document.createElement('div');
        leftContainer.className = 'd-flex align-items-center flex-wrap gap-2 gap-sm-3';

        const infoEl = document.createElement('span');
        infoEl.className = 'small text-body-secondary';
        leftContainer.appendChild(infoEl);

        const sizeSelectContainer = document.createElement('div');
        sizeSelectContainer.className = 'd-flex align-items-center gap-1.5';
        sizeSelectContainer.innerHTML = `
            <label class="small text-body-secondary text-nowrap me-1">แสดง</label>
            <select class="form-select form-select-sm py-0 px-2 text-center" style="width: 75px; height: 28px; font-size: 0.82rem;">
                <option value="10"${pageSize === 10 ? ' selected' : ''}>10</option>
                <option value="25"${pageSize === 25 ? ' selected' : ''}>25</option>
                <option value="50"${pageSize === 50 ? ' selected' : ''}>50</option>
                <option value="-1"${pageSize === -1 ? ' selected' : ''}>ทั้งหมด</option>
            </select>
            <label class="small text-body-secondary text-nowrap ms-1">รายการ/หน้า</label>
        `;
        const sizeSelect = sizeSelectContainer.querySelector('select');
        sizeSelect.addEventListener('change', function (e) {
            pageSize = parseInt(e.target.value);
            currentPage = 1;
            render();
        });
        leftContainer.appendChild(sizeSelectContainer);
        footer.appendChild(leftContainer);

        const navEl = document.createElement('nav');
        navEl.setAttribute('aria-label', 'การแบ่งหน้าข้อมูล');
        const ulEl = document.createElement('ul');
        ulEl.className = 'pagination pagination-sm mb-0';
        navEl.appendChild(ulEl);
        footer.appendChild(navEl);

        wrapper.parentNode.insertBefore(footer, wrapper.nextSibling);

        function render(shouldScroll = false) {
            let totalPages = pageSize === -1 ? 1 : Math.ceil(totalRows / pageSize);
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIndex = pageSize === -1 ? 0 : (currentPage - 1) * pageSize;
            const endIndex = pageSize === -1 ? totalRows : Math.min(startIndex + pageSize, totalRows);

            rows.forEach((row, idx) => {
                row.style.display = (idx >= startIndex && idx < endIndex) ? '' : 'none';
            });

            if (totalPages <= 1) {
                infoEl.innerHTML = `<i class="bi bi-list-check me-1 text-primary"></i> ทั้งหมด <strong>${totalRows}</strong> รายการ`;
            } else {
                infoEl.innerHTML = `<i class="bi bi-list-check me-1 text-primary"></i> แสดง <strong>${startIndex + 1} - ${endIndex}</strong> จาก <strong>${totalRows}</strong> รายการ`;
            }

            if (totalRows <= 10) {
                sizeSelectContainer.style.display = 'none';
                navEl.style.display = 'none';
            } else {
                sizeSelectContainer.style.display = 'flex';
                navEl.style.display = totalPages <= 1 ? 'none' : '';
            }

            if (totalPages > 1) {
                ulEl.innerHTML = '';

                // Prev
                const prevLi = document.createElement('li');
                prevLi.className = `page-item${currentPage === 1 ? ' disabled' : ''}`;
                prevLi.innerHTML = `<a class="page-link" href="javascript:void(0)" aria-label="Previous"><i class="bi bi-chevron-left me-1"></i>ก่อนหน้า</a>`;
                if (currentPage > 1) {
                    prevLi.querySelector('a').addEventListener('click', function (e) {
                        e.preventDefault();
                        currentPage--;
                        render(true);
                    });
                }
                ulEl.appendChild(prevLi);

                // Page numbers
                const maxVisibleButtons = 5;
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + maxVisibleButtons - 1);
                if (endPage - startPage < maxVisibleButtons - 1) {
                    startPage = Math.max(1, endPage - maxVisibleButtons + 1);
                }

                if (startPage > 1) {
                    const li = document.createElement('li');
                    li.className = 'page-item';
                    li.innerHTML = `<a class="page-link" href="javascript:void(0)">1</a>`;
                    li.querySelector('a').addEventListener('click', function (e) {
                        e.preventDefault();
                        currentPage = 1;
                        render(true);
                    });
                    ulEl.appendChild(li);

                    if (startPage > 2) {
                        const dotsLi = document.createElement('li');
                        dotsLi.className = 'page-item disabled';
                        dotsLi.innerHTML = `<span class="page-link">…</span>`;
                        ulEl.appendChild(dotsLi);
                    }
                }

                for (let p = startPage; p <= endPage; p++) {
                    const li = document.createElement('li');
                    li.className = `page-item${p === currentPage ? ' active' : ''}`;
                    li.innerHTML = `<a class="page-link" href="javascript:void(0)">${p}</a>`;
                    if (p !== currentPage) {
                        const pageNum = p;
                        li.querySelector('a').addEventListener('click', function (e) {
                            e.preventDefault();
                            currentPage = pageNum;
                            render(true);
                        });
                    }
                    ulEl.appendChild(li);
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        const dotsLi = document.createElement('li');
                        dotsLi.className = 'page-item disabled';
                        dotsLi.innerHTML = `<span class="page-link">…</span>`;
                        ulEl.appendChild(dotsLi);
                    }

                    const li = document.createElement('li');
                    li.className = 'page-item';
                    li.innerHTML = `<a class="page-link" href="javascript:void(0)">${totalPages}</a>`;
                    li.querySelector('a').addEventListener('click', function (e) {
                        e.preventDefault();
                        currentPage = totalPages;
                        render(true);
                    });
                    ulEl.appendChild(li);
                }

                // Next
                const nextLi = document.createElement('li');
                nextLi.className = `page-item${currentPage === totalPages ? ' disabled' : ''}`;
                nextLi.innerHTML = `<a class="page-link" href="javascript:void(0)" aria-label="Next">ถัดไป<i class="bi bi-chevron-right ms-1"></i></a>`;
                if (currentPage < totalPages) {
                    nextLi.querySelector('a').addEventListener('click', function (e) {
                        e.preventDefault();
                        currentPage++;
                        render(true);
                    });
                }
                ulEl.appendChild(nextLi);
            }

            if (shouldScroll) {
                const navTabs = document.getElementById('researcherTabs');
                if (navTabs) {
                    navTabs.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        }

        render();
    }
});
</script>
@endsection