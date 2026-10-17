@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <img src="https://research.pcru.ac.th/rdb/images/pcru.png" alt="PCRU Logo" style="height: 100px;" class="mb-3">
            <h1 class="display-5 fw-bold">ระบบฐานข้อมูลงานวิจัย</h1>
            <p class="lead">สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์</p>
        </div>
    </div>

    <!-- Year Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <form action="{{ route('frontend.site.index') }}" method="GET" class="d-flex justify-content-end align-items-center">
                <label for="year_id" class="me-2 fw-bold">เลือกปีงบประมาณ:</label>
                <select name="year_id" id="year_id" class="form-select w-auto" onchange="this.form.submit()">
                    @foreach($years_list as $y)
                        <option value="{{ $y->year_id }}" {{ (isset($max_year) && $max_year->year_id == $y->year_id) ? 'selected' : '' }}>
                            {{ $y->year_name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <!-- Stats Cards -->
    <style>
        .stats-card {
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            border: none;
            border-radius: 15px;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important;
        }
        .stats-icon {
            position: absolute;
            right: 10px;
            bottom: -10px;
            font-size: 5rem;
            opacity: 0.2;
            transform: rotate(-15deg);
            z-index: 0;
        }
    </style>

    <div class="row mb-4">
        <!-- Project Card -->
        <div class="col-md-4 mb-4">
            <div class="card stats-card h-100 text-white shadow" 
                 style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body position-relative p-4">
                    <i class="bi bi-file-earmark-text-fill stats-icon"></i>
                    <div class="d-flex flex-column position-relative z-1" style="z-index: 1;">
                        <h6 class="text-white-50 text-uppercase fw-bold mb-2">📅 ปีงบประมาณ {{ $max_year->year_name }}</h6>
                        <h5 class="fw-bold mb-1">จำนวนโครงการวิจัย</h5>
                        <div class="display-5 fw-bold">{{ number_format($sumProject) }}</div>
                        <small class="text-white-50 mt-1">โครงการ</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Card -->
        <div class="col-md-4 mb-4">
            <div class="card stats-card h-100 text-white shadow" 
                 style="background: linear-gradient(135deg, #FF8008 0%, #FFC837 100%);">
                <div class="card-body position-relative p-4">
                    <i class="bi bi-cash-coin stats-icon"></i>
                    <div class="d-flex flex-column position-relative z-1" style="z-index: 1;">
                        <h6 class="text-white-50 text-uppercase fw-bold mb-2">💰 งบประมาณสนับสนุน</h6>
                        <h5 class="fw-bold mb-1">รวมเป็นเงินทั้งสิ้น</h5>
                        <div class="display-5 fw-bold">{{ number_format($sumBudget) }}</div>
                        <small class="text-white-50 mt-1">บาท</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Researcher Card -->
        <div class="col-md-4 mb-4">
            <div class="card stats-card h-100 text-white shadow" 
                 style="background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);">
                <div class="card-body position-relative p-4">
                    <i class="bi bi-people-fill stats-icon"></i>
                    <div class="d-flex flex-column position-relative z-1" style="z-index: 1;">
                        <h6 class="text-white-50 text-uppercase fw-bold mb-2">👥 นักวิจัย</h6>
                        <h5 class="fw-bold mb-1">นักวิจัยทั้งหมด</h5>
                        <div class="display-5 fw-bold">{{ number_format($countResearcher) }}</div>
                        <small class="text-white-50 mt-1">คน</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-fill text-primary"></i> สถิติโครงการวิจัยและงบประมาณ แยกตามหน่วยงาน</h5>
                </div>
                <div class="card-body">
                    <canvas id="projectChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <style>
        .premium-card {
            border-radius: 20px;
            border: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Bouncy effect */
            background: var(--bs-body-bg);
            overflow: hidden;
            position: relative;
        }
        .premium-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #00c6ff); /* Default Blue Gradient */
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .premium-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
        }
        .premium-card:hover::after {
            opacity: 1;
        }
        .premium-card .card-img-wrapper {
            overflow: hidden;
            border-radius: 20px 20px 0 0;
            position: relative;
        }
        .premium-card .card-img-top {
            transition: transform 0.6s ease;
        }
        .premium-card:hover .card-img-top {
            transform: scale(1.1);
        }
        .premium-card-title {
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.4;
            transition: color 0.3s ease;
        }
        .premium-card:hover .premium-card-title {
            color: var(--bs-primary);
        }
        
        /* Specific coloring for Conference */
        .premium-card.conf-card::after {
            background: linear-gradient(90deg, #FF8008, #FFC837); /* Orange Gradient */
        }
        .premium-card.conf-card:hover .premium-card-title {
            color: #FF8008;
        }
    </style>

    <!-- Latest News Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold"><i class="bi bi-newspaper text-primary"></i> ข่าวกิจกรรมงานวิจัยล่าสุด</h4>
                <a href="{{ route('frontend.researchnews.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="row g-3"> <!-- g-3 for better spacing -->
                @foreach($latestNews as $news)
                <div class="col-md-2 col-sm-6">
                    <div class="card h-100 shadow-sm premium-card">
                        <a href="{{ route('frontend.researchnews.show', $news->id) }}" target="_blank" class="text-decoration-none text-body" title="{{ $news->news_name }}">
                            <div class="ratio ratio-4x3 card-img-wrapper">
                                @if($news->news_img)
                                    <img src="{{ Storage::url($news->news_img) }}" class="card-img-top object-fit-cover" alt="{{ $news->news_name }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary h-100">
                                        <i class="bi bi-image fs-1 opacity-50"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body p-3 d-flex align-items-center justify-content-center text-center">
                                <h6 class="premium-card-title mb-0 text-truncate-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    📰 {{ $news->news_name }}
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
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold"><i class="bi bi-megaphone-fill text-primary"></i> การประชุมวิชาการล่าสุด</h4>
                <a href="{{ route('frontend.researchcoferenceinthai.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="row g-3">
                @foreach($latestConference as $conf)
                <div class="col-md-2 col-sm-6">
                    <div class="card h-100 shadow-sm premium-card conf-card">
                        <a href="{{ route('frontend.researchcoferenceinthai.show', $conf->id) }}" target="_blank" class="text-decoration-none text-body" title="{{ $conf->con_name }}">
                            <div class="ratio ratio-4x3 card-img-wrapper">
                                @if($conf->con_img)
                                    <img src="{{ Storage::url($conf->con_img) }}" class="card-img-top object-fit-cover" alt="{{ $conf->con_name }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary h-100">
                                        <i class="bi bi-people-fill fs-1 opacity-50"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body p-3 d-flex align-items-center justify-content-center text-center">
                                <h6 class="premium-card-title mb-0 text-truncate-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    📢 {{ $conf->con_name }}
                                </h6>
                            </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Latest Data Tabs -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs" id="dashboardTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab" aria-controls="projects" aria-selected="true">
                            📊 โครงการวิจัยล่าสุด
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="publications-tab" data-bs-toggle="tab" data-bs-target="#publications" type="button" role="tab" aria-controls="publications" aria-selected="false">
                            📑 ผลงานตีพิมพ์ล่าสุด
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ip-tab" data-bs-toggle="tab" data-bs-target="#ip" type="button" role="tab" aria-controls="ip" aria-selected="false">
                            💡 ทรัพย์สินทางปัญญาล่าสุด
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="utilization-tab" data-bs-toggle="tab" data-bs-target="#utilization" type="button" role="tab" aria-controls="utilization" aria-selected="false">
                            🚀 การนำไปใช้ประโยชน์ล่าสุด
                        </button>
                    </li>


                </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="projects" role="tabpanel" aria-labelledby="projects-tab">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ชื่อโครงการ</th>
                                            <th style="width: 30%;">นักวิจัย</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latestProjects as $project)
                                        <tr>
                                            <td>
                                                <!-- Row 1: Project Name -->
                                                <div class="mb-1">
                                                    <a href="{{ route('frontend.rdbproject.show', $project->pro_id) }}" class="text-body text-decoration-none fw-bold" target="_blank">
                                                        📖 {{ $project->pro_nameTH }}
                                                    </a>
                                                </div>

                                                <!-- Row 2: Year & Funding Source -->
                                                <div class="small text-muted">
                                                    <span class="me-2">📅 ปีงบประมาณ {{ $project->year->year_name ?? '-' }}</span>
                                                    @if($project->type)
                                                        <span>💰 {{ $project->type->pt_name }}</span>
                                                        @if(str_contains($project->type->pt_name, 'ภายนอก') && $project->typeSub)
                                                            <span> • {{ $project->typeSub->pts_name }}</span>
                                                        @endif
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
                                                    <a href="{{ route('frontend.rdbresearcher.show', $firstResearcher->researcher->researcher_id) }}" class="text-body text-decoration-none" target="_blank">
                                                        🧑‍🔬 {{ $firstResearcher->researcher->researcher_fname }} {{ $firstResearcher->researcher->researcher_lname }}
                                                    </a>
                                                    @if($countOthers > 0)
                                                        <span class="text-muted">และอีก {{ $countOthers }} คน</span>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Publications Tab -->
                        <div class="tab-pane fade" id="publications" role="tabpanel" aria-labelledby="publications-tab">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>รายละเอียดผลงาน</th>
                                            <th style="width: 30%;">ผู้แต่ง</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latestPublications as $pub)
                                        <tr>
                                            <td>
                                                <!-- Row 1: Name -->
                                                <div class="mb-1">
                                                    <a href="{{ route('frontend.rdbpublished.show', $pub->id) }}" class="text-body text-decoration-none fw-bold" target="_blank">
                                                        📖 {{ $pub->pub_name }}
                                                    </a>
                                                </div>

                                                <!-- Row 2: Type & Date -->
                                                <div>
                                                    @php
                                                        $type = $pub->pubtype->pubtype_subgroup ?? 'ไม่ระบุประเภท';
                                                        $emoji = '📄';
                                                        if (str_contains($type, 'วิจัย')) $emoji = '🔬';
                                                        elseif (str_contains($type, 'วิชาการ')) $emoji = '📚';
                                                        elseif (str_contains($type, 'ประชุม')) $emoji = '📑';
                                                        elseif (str_contains($type, 'TCI')) $emoji = '🏅';
                                                    @endphp
                                                    <span class="text-muted">{{ $emoji }} {{ $type }}</span>

                                                    <span class="small text-muted ms-2">
                                                        📅 
                                                        @if($pub->pub_date)
                                                            {{ \Carbon\Carbon::parse($pub->pub_date)->locale('th')->addYears(543)->translatedFormat('d M y') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="align-top">
                                                @php
                                                    // Find author with lowest pubw_main (likely 1 is main) or fallback to first
                                                    $mainAuthor = $pub->authors->sortBy('pivot.pubw_main')->first();
                                                @endphp
                                                @if($mainAuthor)
                                                    <a href="{{ route('frontend.rdbresearcher.show', $mainAuthor->researcher_id) }}" class="text-body text-decoration-none hover-primary" target="_blank">
                                                        ✍️ {{ $mainAuthor->researcher_fname }} {{ $mainAuthor->researcher_lname }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- IP Tab -->
                        <div class="tab-pane fade" id="ip" role="tabpanel" aria-labelledby="ip-tab">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ชื่อผลงาน</th>
                                            <th style="width: 30%;">ผู้ประดิษฐ์</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latestIP as $ip)
                                        <tr>
                                            <td>
                                                <!-- Row 1: Title -->
                                                <div class="mb-1">
                                                    <a href="{{ route('frontend.rdbdip.show', $ip->dip_id) }}" class="text-body text-decoration-none fw-bold" target="_blank">
                                                        📖 {{ $ip->dip_name ?: ($ip->dip_data2_name ?? '-') }}
                                                    </a>
                                                </div>

                                                <!-- Row 2: Type & Date -->
                                                <div class="small text-muted">
                                                    @if($ip->dipType)
                                                        <span class="me-2">🏷️ {{ $ip->dipType->dipt_name }}</span>
                                                    @endif

                                                    <span>
                                                        📅  
                                                        @if($ip->dip_request_date)
                                                            {{ \Carbon\Carbon::parse($ip->dip_request_date)->locale('th')->addYears(543)->translatedFormat('d M y') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($ip->researcher)
                                                    <a href="{{ route('frontend.rdbresearcher.show', $ip->researcher->researcher_id) }}" class="text-body text-decoration-none" target="_blank">
                                                        🧑‍🔬 {{ $ip->researcher->researcher_fname }} {{ $ip->researcher->researcher_lname }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Utilization Tab -->
                        <div class="tab-pane fade" id="utilization" role="tabpanel" aria-labelledby="utilization-tab">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>โครงการ</th>
                                            <th style="width: 40%;">ข้อมูลการนำไปใช้</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tbody>
                                        @foreach($latestUtilization as $util)
                                        <tr>
                                            <td>
                                                @if(isset($util->project))
                                                <a href="{{ route('frontend.rdbprojectutilize.show', $util->utz_id) }}" class="text-body text-decoration-none fw-bold" target="_blank">
                                                    📖 {{ $util->project->pro_nameTH }}
                                                </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <!-- Row 1: Date -->
                                                <div class="small text-muted mb-1">
                                                    📅 
                                                    @if($util->utz_date)
                                                        {{ \Carbon\Carbon::parse($util->utz_date)->locale('th')->addYears(543)->translatedFormat('d M y') }}
                                                    @else
                                                        -
                                                    @endif
                                                </div>

                                                <!-- Row 2: Agency -->
                                                <div class="mb-1">
                                                    🏢 {{ $util->utz_department_name }}
                                                </div>

                                                <!-- Row 3: Address -->
                                                @if(isset($util->changwat))
                                                <div class="small text-muted">
                                                    📍 
                                                    @if($util->changwat->tambon_t)
                                                        {{ $util->changwat->tambon_t }}
                                                    @endif
                                                    @if($util->changwat->amphoe_t)
                                                        {{ $util->changwat->amphoe_t }}
                                                    @endif
                                                    @if($util->changwat->changwat_t)
                                                        {{ $util->changwat->changwat_t }}
                                                    @endif
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('projectChart').getContext('2d');
        const chartData = @json($chartData);

        new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'จำนวนโครงการ'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'งบประมาณ (บาท)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                }
            }
        });
    });
</script>
@endsection