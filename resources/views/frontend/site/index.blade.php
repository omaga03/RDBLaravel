@extends('layouts.app')

@section('title', 'หน้าแรก - ระบบฐานข้อมูลงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์')

@section('content')
<div class="container-fluid py-4 px-md-4">
    <!-- Academic Hero Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden" 
                 style="background: radial-gradient(circle at 80% 20%, rgba(37, 99, 235, 0.15) 0%, rgba(30, 58, 95, 0.08) 60%, transparent 100%), var(--bs-card-bg); border-radius: var(--rdb-radius-lg) !important;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center">
                        <div class="col-lg-8 text-center text-lg-start mb-4 mb-lg-0">
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 border bg-body-tertiary shadow-xs">
                                <span class="badge rounded-pill" style="background: var(--rdb-gradient-primary); font-size: 0.72rem;">PCRU RDB</span>
                                <small class="text-body-secondary fw-semibold">สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์</small>
                            </div>
                            <h1 class="display-6 fw-bold mb-2 text-body">
                                ระบบฐานข้อมูลงานวิจัย
                            </h1>
                            <p class="lead text-body-secondary mb-0" style="font-size: 1.05rem;">
                                แหล่งรวบรวมและเผยแพร่ผลงานวิจัย นวัตกรรม ทรัพย์สินทางปัญญา และการนำไปใช้ประโยชน์เพื่อการพัฒนาท้องถิ่น
                            </p>
                        </div>
                        <div class="col-lg-4 text-center text-lg-end">
                            <img src="https://research.pcru.ac.th/rdb/images/pcru.png" alt="PCRU Logo" style="height: 110px; max-width: 100%;" class="drop-shadow-md">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Year Filter Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 p-3 bg-body rounded-3 border shadow-sm" style="border-radius: var(--rdb-radius-md) !important;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-primary"></i>
                    <span class="fw-bold text-body">ตัวกรองปีงบประมาณ:</span>
                    @if(isset($max_year))
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            ปีงบประมาณ {{ $max_year->year_name }}
                        </span>
                    @endif
                </div>
                <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center gap-2 m-0">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-body-tertiary border-end-0">
                            <i class="bi bi-calendar3 text-primary"></i>
                        </span>
                        <select name="year_id" id="year_id" class="form-select form-select-sm border-start-0" onchange="this.form.submit()" style="min-width: 180px;">
                            @foreach($years_list as $y)
                                <option value="{{ $y->year_id }}" {{ (isset($max_year) && $max_year->year_id == $y->year_id) ? 'selected' : '' }}>
                                    ปีงบประมาณ {{ $y->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- UI/UX Pro Max Bento Stat Cards -->
    <style>
        .stats-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
            border: none !important;
            border-radius: var(--rdb-radius-md) !important;
        }
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.15) !important;
        }
        .stats-icon {
            position: absolute;
            right: 15px;
            bottom: -8px;
            font-size: 5.5rem;
            opacity: 0.18;
            transform: rotate(-12deg);
            z-index: 0;
            pointer-events: none;
        }
    </style>

    <div class="row g-3 mb-4">
        <!-- Project Card -->
        <div class="col-md-4">
            <div class="card stats-card h-100 text-white shadow-sm" 
                 style="background: var(--rdb-gradient-primary);">
                <div class="card-body position-relative p-4">
                    <i class="bi bi-folder2-open stats-icon"></i>
                    <div class="position-relative" style="z-index: 1;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-light-subtle bg-opacity-25 text-white border-0">
                                <i class="bi bi-calendar-check me-1"></i>ปี {{ $max_year->year_name }}
                            </span>
                        </div>
                        <h6 class="text-white-50 text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.04em;">จำนวนโครงการวิจัย</h6>
                        <div class="display-5 fw-bold my-1">{{ number_format($sumProject) }}</div>
                        <div class="small text-white-50 mt-1 d-flex align-items-center">
                            <i class="bi bi-check2-circle me-1"></i> โครงการที่เปิดเผยข้อมูล
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Card -->
        <div class="col-md-4">
            <div class="card stats-card h-100 text-white shadow-sm" 
                 style="background: linear-gradient(135deg, #92400E 0%, #B45309 45%, #D97706 100%);">
                <div class="card-body position-relative p-4">
                    <i class="bi bi-cash-stack stats-icon"></i>
                    <div class="position-relative" style="z-index: 1;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-light-subtle bg-opacity-25 text-white border-0">
                                <i class="bi bi-award me-1"></i>งบประมาณสนับสนุน
                            </span>
                        </div>
                        <h6 class="text-white-50 text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.04em;">รวมเป็นเงินทั้งสิ้น</h6>
                        <div class="display-5 fw-bold my-1">{{ number_format($sumBudget) }}</div>
                        <div class="small text-white-50 mt-1 d-flex align-items-center">
                            <i class="bi bi-currency-exchange me-1"></i> บาท (ทุกแหล่งทุนรวมกัน)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Researcher Card -->
        <div class="col-md-4">
            <div class="card stats-card h-100 text-white shadow-sm" 
                 style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 55%, #3B82F6 100%);">
                <div class="card-body position-relative p-4">
                    <i class="bi bi-people-fill stats-icon"></i>
                    <div class="position-relative" style="z-index: 1;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-light-subtle bg-opacity-25 text-white border-0">
                                <i class="bi bi-person-badge me-1"></i>กำลังคนด้านวิจัย
                            </span>
                        </div>
                        <h6 class="text-white-50 text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.04em;">นักวิจัยในโครงการ (ทุกสถานะ)</h6>
                        <div class="display-5 fw-bold my-1">{{ number_format($countResearcher) }}</div>
                        <div class="small text-white-50 mt-1 d-flex align-items-center">
                            <i class="bi bi-mortarboard me-1"></i> คณาจารย์และบุคลากรวิจัยในปี {{ $max_year->year_name }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border">
                <div class="card-header bg-body-tertiary py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i> สถิติโครงการวิจัยและงบประมาณ แยกตามหน่วยงาน
                    </h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        <i class="bi bi-graph-up me-1"></i> กราฟแท่งและเส้นงบประมาณ
                    </span>
                </div>
                <div class="card-body p-4">
                    <div style="height: 380px; width: 100%;">
                        <canvas id="projectChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- News & Conference Styles -->
    <style>
        .premium-card {
            border-radius: var(--rdb-radius-md) !important;
            border: 1px solid var(--rdb-card-border) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--bs-card-bg);
            overflow: hidden;
            position: relative;
        }
        .premium-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--rdb-gradient-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .premium-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--rdb-shadow-lg) !important;
        }
        .premium-card:hover::after {
            opacity: 1;
        }
        .premium-card .card-img-wrapper {
            overflow: hidden;
            border-top-left-radius: calc(var(--rdb-radius-md) - 1px);
            border-top-right-radius: calc(var(--rdb-radius-md) - 1px);
            position: relative;
        }
        .premium-card .card-img-top {
            transition: transform 0.5s ease;
        }
        .premium-card:hover .card-img-top {
            transform: scale(1.08);
        }
        .premium-card-title {
            font-size: 0.88rem;
            font-weight: 600;
            line-height: 1.45;
            transition: color 0.2s ease;
        }
        .premium-card:hover .premium-card-title {
            color: var(--rdb-secondary);
        }
        .premium-card.conf-card::after {
            background: var(--rdb-gradient-gold);
        }
        .premium-card.conf-card:hover .premium-card-title {
            color: var(--rdb-accent);
        }
    </style>

    <!-- Latest News Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0 text-body">
                    <i class="bi bi-newspaper text-primary me-2"></i> ข่าวกิจกรรมงานวิจัยล่าสุด
                </h4>
                <a href="{{ route('frontend.researchnews.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    ดูทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="row g-3">
                @foreach($latestNews as $news)
                <div class="col-xl-2 col-md-3 col-sm-6">
                    <div class="card h-100 shadow-sm premium-card">
                        <a href="{{ route('frontend.researchnews.show', $news->id) }}" target="_blank" class="text-decoration-none text-body" title="{{ $news->news_name }}">
                            <div class="ratio ratio-4x3 card-img-wrapper">
                                @if($news->image_url)
                                    <img src="{{ $news->image_url }}" class="card-img-top object-fit-cover" alt="{{ $news->news_name }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-body-tertiary text-body-secondary h-100">
                                        <i class="bi bi-newspaper fs-1 opacity-25"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body p-3 d-flex align-items-center justify-content-center text-center">
                                <h6 class="premium-card-title mb-0" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    <i class="bi bi-dot text-primary"></i>{{ $news->news_name }}
                                </h6>
                            </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Latest Conference Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0 text-body">
                    <i class="bi bi-megaphone-fill text-warning me-2"></i> การประชุมวิชาการล่าสุด
                </h4>
                <a href="{{ route('frontend.researchcoferenceinthai.index') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                    ดูทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="row g-3">
                @foreach($latestConference as $conf)
                <div class="col-xl-2 col-md-3 col-sm-6">
                    <div class="card h-100 shadow-sm premium-card conf-card">
                        <a href="{{ route('frontend.researchcoferenceinthai.show', $conf->id) }}" target="_blank" class="text-decoration-none text-body" title="{{ $conf->con_name }}">
                            <div class="ratio ratio-4x3 card-img-wrapper">
                                @if($conf->image_url)
                                    <img src="{{ $conf->image_url }}" class="card-img-top object-fit-cover" alt="{{ $conf->con_name }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-body-tertiary text-body-secondary h-100">
                                        <i class="bi bi-megaphone-fill fs-1 opacity-25"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body p-3 d-flex align-items-center justify-content-center text-center">
                                <h6 class="premium-card-title mb-0" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    <i class="bi bi-dot text-warning"></i>{{ $conf->con_name }}
                                </h6>
                            </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Latest Data Bento Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border">
                <div class="card-header bg-body-tertiary border-bottom p-0">
                    <ul class="nav nav-tabs card-header-tabs m-0 border-0" id="dashboardTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active px-4 py-3 fw-bold border-0 d-flex align-items-center" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab" aria-controls="projects" aria-selected="true">
                                <i class="bi bi-folder2-open me-2 text-primary"></i> โครงการวิจัยล่าสุด
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-4 py-3 fw-bold border-0 d-flex align-items-center" id="publications-tab" data-bs-toggle="tab" data-bs-target="#publications" type="button" role="tab" aria-controls="publications" aria-selected="false">
                                <i class="bi bi-journal-text me-2 text-info"></i> ผลงานตีพิมพ์ล่าสุด
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-4 py-3 fw-bold border-0 d-flex align-items-center" id="ip-tab" data-bs-toggle="tab" data-bs-target="#ip" type="button" role="tab" aria-controls="ip" aria-selected="false">
                                <i class="bi bi-lightbulb-fill me-2 text-warning"></i> ทรัพย์สินทางปัญญาล่าสุด
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-4 py-3 fw-bold border-0 d-flex align-items-center" id="utilization-tab" data-bs-toggle="tab" data-bs-target="#utilization" type="button" role="tab" aria-controls="utilization" aria-selected="false">
                                <i class="bi bi-rocket-takeoff-fill me-2 text-danger"></i> การนำไปใช้ประโยชน์ล่าสุด
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="myTabContent">
                        <!-- Projects Tab -->
                        <div class="tab-pane fade show active p-3" id="projects" role="tabpanel" aria-labelledby="projects-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ชื่อโครงการวิจัย</th>
                                            <th style="width: 32%;">นักวิจัย</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latestProjects as $project)
                                        <tr>
                                            <td>
                                                <div class="mb-1">
                                                    <a href="{{ route('frontend.rdbproject.show', $project->pro_id) }}" class="text-body text-decoration-none fw-bold hover-primary" target="_blank">
                                                        <i class="bi bi-journal-bookmark me-1 text-primary"></i> {{ $project->pro_nameTH }}
                                                    </a>
                                                </div>
                                                <div class="small text-body-secondary d-flex align-items-center flex-wrap gap-2">
                                                    <span><i class="bi bi-calendar3 me-1"></i>ปี {{ $project->year->year_name ?? '-' }}</span>
                                                    @if($project->type)
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                            <i class="bi bi-tag-fill me-1"></i>{{ $project->type->pt_name }}
                                                            @if(str_contains($project->type->pt_name, 'ภายนอก') && $project->typeSub)
                                                                • {{ $project->typeSub->pts_name }}
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $sortedResearchers = $project->rdbProjectWorks->sortBy(function($work) {
                                                        return $work->position_id;
                                                    });
                                                    $firstResearcher = $sortedResearchers->first();
                                                    $countOthers = $sortedResearchers->count() - 1;
                                                @endphp

                                                @if($firstResearcher && $firstResearcher->researcher)
                                                    <a href="{{ route('frontend.rdbresearcher.show', $firstResearcher->researcher->researcher_id) }}" class="text-body text-decoration-none fw-semibold" target="_blank">
                                                        <i class="bi bi-person-fill text-primary me-1"></i>{{ $firstResearcher->researcher->researcher_fname }} {{ $firstResearcher->researcher->researcher_lname }}
                                                    </a>
                                                    @if($countOthers > 0)
                                                        <span class="badge bg-body-tertiary text-body-secondary ms-1">+{{ $countOthers }} คน</span>
                                                    @endif
                                                @else
                                                    <span class="text-body-secondary">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Publications Tab -->
                        <div class="tab-pane fade p-3" id="publications" role="tabpanel" aria-labelledby="publications-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>รายละเอียดผลงานตีพิมพ์</th>
                                            <th style="width: 32%;">ผู้แต่ง</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latestPublications as $pub)
                                        <tr>
                                            <td>
                                                <div class="mb-1">
                                                    <a href="{{ route('frontend.rdbpublished.show', $pub->id) }}" class="text-body text-decoration-none fw-bold hover-primary" target="_blank">
                                                        <i class="bi bi-file-earmark-text text-info me-1"></i> {{ $pub->pub_name }}
                                                    </a>
                                                </div>
                                                <div class="small text-body-secondary d-flex align-items-center flex-wrap gap-2">
                                                    @php
                                                        $type = $pub->pubtype->pubtype_subgroup ?? 'ไม่ระบุประเภท';
                                                    @endphp
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $type }}</span>
                                                    @if($pub->pub_date)
                                                        <span><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($pub->pub_date)->locale('th')->addYears(543)->translatedFormat('d M y') }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="align-top">
                                                @php
                                                    $mainAuthor = $pub->authors->sortBy('pivot.pubw_main')->first();
                                                @endphp
                                                @if($mainAuthor)
                                                    <a href="{{ route('frontend.rdbresearcher.show', $mainAuthor->researcher_id) }}" class="text-body text-decoration-none fw-semibold" target="_blank">
                                                        <i class="bi bi-pen me-1 text-primary"></i>{{ $mainAuthor->researcher_fname }} {{ $mainAuthor->researcher_lname }}
                                                    </a>
                                                @else
                                                    <span class="text-body-secondary">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- IP Tab -->
                        <div class="tab-pane fade p-3" id="ip" role="tabpanel" aria-labelledby="ip-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ชื่อผลงานทรัพย์สินทางปัญญา</th>
                                            <th style="width: 32%;">ผู้ประดิษฐ์</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latestIP as $ip)
                                        <tr>
                                            <td>
                                                <div class="mb-1">
                                                    <a href="{{ route('frontend.rdbdip.show', $ip->dip_id) }}" class="text-body text-decoration-none fw-bold hover-primary" target="_blank">
                                                        <i class="bi bi-lightbulb text-warning me-1"></i> {{ $ip->dip_name ?: ($ip->dip_data2_name ?? '-') }}
                                                    </a>
                                                </div>
                                                <div class="small text-body-secondary d-flex align-items-center flex-wrap gap-2">
                                                    @if($ip->dipType)
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $ip->dipType->dipt_name }}</span>
                                                    @endif
                                                    @if($ip->dip_request_date)
                                                        <span><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($ip->dip_request_date)->locale('th')->addYears(543)->translatedFormat('d M y') }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($ip->researcher)
                                                    <a href="{{ route('frontend.rdbresearcher.show', $ip->researcher->researcher_id) }}" class="text-body text-decoration-none fw-semibold" target="_blank">
                                                        <i class="bi bi-person-fill text-warning me-1"></i>{{ $ip->researcher->researcher_fname }} {{ $ip->researcher->researcher_lname }}
                                                    </a>
                                                @else
                                                    <span class="text-body-secondary">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Utilization Tab -->
                        <div class="tab-pane fade p-3" id="utilization" role="tabpanel" aria-labelledby="utilization-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>โครงการวิจัย</th>
                                            <th style="width: 42%;">ข้อมูลการนำไปใช้ประโยชน์</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latestUtilization as $util)
                                        <tr>
                                            <td>
                                                @if(isset($util->project))
                                                <a href="{{ route('frontend.rdbprojectutilize.show', $util->utz_id) }}" class="text-body text-decoration-none fw-bold hover-primary" target="_blank">
                                                    <i class="bi bi-rocket-takeoff text-danger me-1"></i> {{ $util->project->pro_nameTH }}
                                                </a>
                                                @else
                                                    <span class="text-body-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                        <i class="bi bi-building me-1"></i>{{ $util->utz_department_name }}
                                                    </span>
                                                    @if($util->utz_date)
                                                        <span class="small text-body-secondary">
                                                            <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($util->utz_date)->locale('th')->addYears(543)->translatedFormat('d M y') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if(isset($util->changwat))
                                                <div class="small text-body-secondary">
                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                    {{ $util->changwat->tambon_t }} {{ $util->changwat->amphoe_t }} {{ $util->changwat->changwat_t }}
                                                </div>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js with Theme Detection -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('projectChart').getContext('2d');
        const chartData = @json($chartData);

        // UI/UX Pro Max Scholarly Theme Colors for Datasets
        const scholarlyColors = ['#1E3A5F', '#2563EB', '#0D9488', '#D97706', '#059669'];
        if (chartData.datasets) {
            chartData.datasets.forEach((ds, idx) => {
                if (ds.type === 'bar') {
                    ds.backgroundColor = scholarlyColors[idx % scholarlyColors.length];
                    ds.borderRadius = 6;
                } else if (ds.type === 'line') {
                    ds.borderColor = '#D97706';
                    ds.backgroundColor = 'rgba(217, 119, 6, 0.15)';
                    ds.borderWidth = 3;
                    ds.pointBackgroundColor = '#D97706';
                    ds.pointRadius = 4;
                }
            });
        }

        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const textColor = isDark ? '#94A3B8' : '#475569';
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

        const chart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        ticks: { color: textColor, font: { family: 'Plus Jakarta Sans, Prompt', size: 11 } },
                        grid: { color: gridColor }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'จำนวนโครงการ',
                            color: textColor,
                            font: { family: 'Plus Jakarta Sans, Prompt', weight: '600' }
                        },
                        ticks: { color: textColor },
                        grid: { color: gridColor }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'งบประมาณ (บาท)',
                            color: textColor,
                            font: { family: 'Plus Jakarta Sans, Prompt', weight: '600' }
                        },
                        ticks: {
                            color: textColor,
                            callback: function(v) {
                                return v >= 1000000 ? (v / 1000000).toFixed(1) + 'M' : v.toLocaleString();
                            }
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                },
                plugins: {
                    legend: {
                        labels: {
                            color: textColor,
                            font: { family: 'Plus Jakarta Sans, Prompt', size: 12 }
                        }
                    }
                }
            }
        });

        // Listen for Theme Change Event
        window.addEventListener('themeChanged', (e) => {
            const isDarkMode = e.detail.theme === 'dark';
            const newTextColor = isDarkMode ? '#94A3B8' : '#475569';
            const newGridColor = isDarkMode ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

            chart.options.scales.x.ticks.color = newTextColor;
            chart.options.scales.x.grid.color = newGridColor;
            chart.options.scales.y.ticks.color = newTextColor;
            chart.options.scales.y.title.color = newTextColor;
            chart.options.scales.y.grid.color = newGridColor;
            chart.options.scales.y1.ticks.color = newTextColor;
            chart.options.scales.y1.title.color = newTextColor;
            chart.options.plugins.legend.labels.color = newTextColor;
            chart.update();
        });
    });
</script>
@endsection