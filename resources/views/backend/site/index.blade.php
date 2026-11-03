@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold text-body">
                @if($userRoleType === 'faculty')
                    <i class="bi bi-building me-2 text-primary"></i>แดชบอร์ดภาพรวมงานวิจัยระดับคณะ {{ $facultyDeptName ? "($facultyDeptName)" : "" }}
                @elseif($userRoleType === 'researcher')
                    <i class="bi bi-person-workspace me-2 text-primary"></i>แดชบอร์ดผลงานวิจัยของฉัน (Researcher Dashboard)
                @else
                    <i class="bi bi-speedometer2 me-2 text-primary"></i>แดชบอร์ดภาพรวมระบบ (Admin Dashboard)
                @endif
            </h2>
            <p class="text-body-secondary mb-0 d-flex align-items-center flex-wrap gap-2">
                @if($userRoleType === 'faculty')
                    <span>สรุปข้อมูลสถิติและการดำเนินงานด้านการวิจัย สังกัดคณะ/หน่วยงานของคุณ</span>
                @elseif($userRoleType === 'researcher')
                    <span>สรุปข้อมูลสถิติผลงานวิจัย งานตีพิมพ์ ทรัพย์สินทางปัญญา และการนำไปใช้ประโยชน์ของคุณ</span>
                @else
                    <span>สรุปข้อมูลสถิติและการดำเนินงานด้านการวิจัย สถาบันวิจัยและพัฒนา</span>
                @endif
                @if($selectedYear)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        <i class="bi bi-funnel-fill me-1"></i>ปีงบประมาณ {{ $selectedYear->year_name }}
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                        <i class="bi bi-collection-fill me-1"></i>ข้อมูลสะสมทุกปี
                    </span>
                @endif
            </p>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <!-- Year Filter Dropdown -->
            <form method="GET" action="{{ route('backend.site.index') }}" class="d-flex align-items-center gap-2 m-0" id="dashboardYearForm">
                <div class="input-group input-group-sm shadow-sm" style="min-width: 250px;">
                    <span class="input-group-text bg-body-tertiary border-end-0">
                        <i class="bi bi-calendar3 text-primary"></i>
                    </span>
                    <select name="year_id" id="year_id" class="form-select border-start-0" onchange="this.form.submit()">
                        <option value="">-- ทุกปีงบประมาณ (All Years) --</option>
                        @foreach($availableYears as $year)
                            <option value="{{ $year->year_id }}" {{ (string)$selectedYearId === (string)$year->year_id ? 'selected' : '' }}>
                                ปีงบประมาณ {{ $year->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedYearId)
                    <a href="{{ route('backend.site.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm text-nowrap" title="ล้างตัวกรองปี">
                        <i class="bi bi-x-circle me-1"></i>ดูทั้งหมด
                    </a>
                @endif
            </form>

            <a href="{{ route('frontend.site.index') }}" target="_blank" class="btn btn-sm btn-outline-primary shadow-sm text-nowrap">
                <i class="bi bi-box-arrow-up-right me-1"></i> ดูหน้าบ้าน
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards (6 Key Modules) -->
    <div class="row g-3 mb-4">
        <!-- 1. Projects -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card rdb-card-tint-blue">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-body-secondary small fw-bold text-uppercase">โครงการวิจัย</span>
                            <div class="p-2 rounded-circle bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-folder2-open fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-primary" style="font-variant-numeric: tabular-nums;">{{ number_format($totalProjects) }}</h3>
                        <small class="text-success fw-semibold text-truncate d-block mt-1" style="font-variant-numeric: tabular-nums;" title="งบประมาณรวม ฿{{ number_format($totalProjectBudget) }}">
                            @if($totalProjectBudget >= 1000000)
                                ฿{{ number_format($totalProjectBudget / 1000000, 2) }}M บาท
                            @else
                                ฿{{ number_format($totalProjectBudget) }} บาท
                            @endif
                        </small>
                    </div>

                    @if(!empty($yoyDelta['prev_year_name']))
                        @if($yoyDelta['project_prev'] >= 5 && $yoyDelta['project_pct'] !== null)
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">เทียบปี {{ $yoyDelta['prev_year_name'] }}</span>
                                <span class="badge {{ $yoyDelta['project_diff'] >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}" style="font-variant-numeric: tabular-nums; font-size: 0.7rem;">
                                    <i class="bi {{ $yoyDelta['project_diff'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    {{ $yoyDelta['project_diff'] >= 0 ? '+' : '' }}{{ $yoyDelta['project_pct'] }}%
                                </span>
                            </div>
                        @else
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">ปีก่อน ({{ $yoyDelta['prev_year_name'] }})</span>
                                <span class="text-body-secondary fw-semibold" style="font-variant-numeric: tabular-nums; font-size: 0.72rem;">{{ number_format($yoyDelta['project_prev']) }} โครงการ</span>
                            </div>
                        @endif
                    @endif
                </div>
                <a href="{{ route('backend.rdb_project.index', $selectedYearId ? ['pro_year' => $selectedYearId] : []) }}" class="card-footer bg-transparent border-top py-2 px-3 text-decoration-none small text-primary d-flex justify-content-between align-items-center">
                    <span>จัดการโครงการ</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 2. Researchers -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card rdb-card-tint-emerald">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-body-secondary small fw-bold text-uppercase">นักวิจัย</span>
                            <div class="p-2 rounded-circle bg-success bg-opacity-10 text-success">
                                <i class="bi bi-people-fill fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-success" style="font-variant-numeric: tabular-nums;">{{ number_format($totalResearchers) }}</h3>
                        <small class="text-body-secondary d-block mt-1">
                            {{ $selectedYearId ? 'ร่วมโครงการในปีนี้' : 'นักวิจัยทั้งหมด' }}
                        </small>
                    </div>

                    @if(!empty($yoyDelta['prev_year_name']))
                        @if($yoyDelta['researcher_prev'] >= 5 && $yoyDelta['researcher_pct'] !== null)
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">เทียบปี {{ $yoyDelta['prev_year_name'] }}</span>
                                <span class="badge {{ $yoyDelta['researcher_diff'] >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}" style="font-variant-numeric: tabular-nums; font-size: 0.7rem;">
                                    <i class="bi {{ $yoyDelta['researcher_diff'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    {{ $yoyDelta['researcher_diff'] >= 0 ? '+' : '' }}{{ $yoyDelta['researcher_pct'] }}%
                                </span>
                            </div>
                        @else
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">ปีก่อน ({{ $yoyDelta['prev_year_name'] }})</span>
                                <span class="text-body-secondary fw-semibold" style="font-variant-numeric: tabular-nums; font-size: 0.72rem;">{{ number_format($yoyDelta['researcher_prev']) }} ท่าน</span>
                            </div>
                        @endif
                    @endif
                </div>
                <a href="{{ route('backend.rdb_researcher.index') }}" class="card-footer bg-transparent border-top py-2 px-3 text-decoration-none small text-success d-flex justify-content-between align-items-center">
                    <span>จัดการนักวิจัย</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 3. Publications -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card rdb-card-tint-teal">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-body-secondary small fw-bold text-uppercase">ผลงานตีพิมพ์</span>
                            <div class="p-2 rounded-circle bg-info bg-opacity-10 text-info">
                                <i class="bi bi-file-earmark-text-fill fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-info" style="font-variant-numeric: tabular-nums;">{{ number_format($totalPublished) }}</h3>
                        <small class="text-body-secondary d-block mt-1">
                            {{ $selectedYearId ? 'ตีพิมพ์ปีนี้' : 'ผลงานทั้งหมด' }}
                        </small>
                    </div>

                    @if(!empty($yoyDelta['prev_year_name']))
                        @if($yoyDelta['published_prev'] >= 5 && $yoyDelta['published_pct'] !== null)
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">เทียบปี {{ $yoyDelta['prev_year_name'] }}</span>
                                <span class="badge {{ $yoyDelta['published_diff'] >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}" style="font-variant-numeric: tabular-nums; font-size: 0.7rem;">
                                    <i class="bi {{ $yoyDelta['published_diff'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    {{ $yoyDelta['published_diff'] >= 0 ? '+' : '' }}{{ $yoyDelta['published_pct'] }}%
                                </span>
                            </div>
                        @else
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">ปีก่อน ({{ $yoyDelta['prev_year_name'] }})</span>
                                <span class="text-body-secondary fw-semibold" style="font-variant-numeric: tabular-nums; font-size: 0.72rem;">{{ number_format($yoyDelta['published_prev']) }} เรื่อง</span>
                            </div>
                        @endif
                    @endif
                </div>
                <a href="{{ route('backend.rdb_published.index', $selectedYearId ? ['year_id' => $selectedYearId] : []) }}" class="card-footer bg-transparent border-top py-2 px-3 text-decoration-none small text-info d-flex justify-content-between align-items-center">
                    <span>จัดการงานตีพิมพ์</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 4. Intellectual Property -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card rdb-card-tint-amber">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-body-secondary small fw-bold text-uppercase">ทรัพย์สินทางปัญญา</span>
                            <div class="p-2 rounded-circle bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-award-fill fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-warning" style="font-variant-numeric: tabular-nums;">{{ number_format($totalDip) }}</h3>
                        <small class="text-body-secondary d-block mt-1">
                            {{ $selectedYearId ? 'ยื่นคำขอปีนี้' : 'ทรัพย์สินฯ ทั้งหมด' }}
                        </small>
                    </div>

                    @if(!empty($yoyDelta['prev_year_name']))
                        @if($yoyDelta['dip_prev'] >= 5 && $yoyDelta['dip_pct'] !== null)
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">เทียบปี {{ $yoyDelta['prev_year_name'] }}</span>
                                <span class="badge {{ $yoyDelta['dip_diff'] >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}" style="font-variant-numeric: tabular-nums; font-size: 0.7rem;">
                                    <i class="bi {{ $yoyDelta['dip_diff'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    {{ $yoyDelta['dip_diff'] >= 0 ? '+' : '' }}{{ $yoyDelta['dip_pct'] }}%
                                </span>
                            </div>
                        @else
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">ปีก่อน ({{ $yoyDelta['prev_year_name'] }})</span>
                                <span class="text-body-secondary fw-semibold" style="font-variant-numeric: tabular-nums; font-size: 0.72rem;">{{ number_format($yoyDelta['dip_prev']) }} รายการ</span>
                            </div>
                        @endif
                    @endif
                </div>
                <a href="{{ route('backend.rdb_dip.index', $selectedYearId ? ['year_id' => $selectedYearId] : []) }}" class="card-footer bg-transparent border-top py-2 px-3 text-decoration-none small text-warning d-flex justify-content-between align-items-center">
                    <span>จัดการทรัพย์สินฯ</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 5. Utilization -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card rdb-card-tint-red">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-body-secondary small fw-bold text-uppercase">การใช้ประโยชน์</span>
                            <div class="p-2 rounded-circle bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-rocket-takeoff-fill fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-danger" style="font-variant-numeric: tabular-nums;">{{ number_format($totalUtilize) }}</h3>
                        <small class="text-body-secondary d-block mt-1">
                            {{ $selectedYearId ? 'นำไปใช้ปีนี้' : 'ผลงานทั้งหมด' }}
                        </small>
                    </div>

                    @if(!empty($yoyDelta['prev_year_name']))
                        @if($yoyDelta['utilize_prev'] >= 5 && $yoyDelta['utilize_pct'] !== null)
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">เทียบปี {{ $yoyDelta['prev_year_name'] }}</span>
                                <span class="badge {{ $yoyDelta['utilize_diff'] >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}" style="font-variant-numeric: tabular-nums; font-size: 0.7rem;">
                                    <i class="bi {{ $yoyDelta['utilize_diff'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    {{ $yoyDelta['utilize_diff'] >= 0 ? '+' : '' }}{{ $yoyDelta['utilize_pct'] }}%
                                </span>
                            </div>
                        @else
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">ปีก่อน ({{ $yoyDelta['prev_year_name'] }})</span>
                                <span class="text-body-secondary fw-semibold" style="font-variant-numeric: tabular-nums; font-size: 0.72rem;">{{ number_format($yoyDelta['utilize_prev']) }} ผลงาน</span>
                            </div>
                        @endif
                    @endif
                </div>
                <a href="{{ route('backend.rdbprojectutilize.index', $selectedYearId ? ['year_id' => $selectedYearId] : []) }}" class="card-footer bg-transparent border-top py-2 px-3 text-decoration-none small text-danger d-flex justify-content-between align-items-center">
                    <span>จัดการการนำไปใช้</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 6. News & Conferences -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card rdb-card-tint-slate">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-body-secondary small fw-bold text-uppercase">ข่าว & ประชุม</span>
                            <div class="p-2 rounded-circle bg-secondary bg-opacity-10 text-secondary">
                                <i class="bi bi-newspaper fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-secondary" style="font-variant-numeric: tabular-nums;">{{ number_format($totalNews + $totalConference) }}</h3>
                        <small class="text-body-secondary d-block mt-1">
                            {{ $selectedYearId ? 'กิจกรรมปีนี้' : 'รายการทั้งหมด' }}
                        </small>
                    </div>
                </div>
                <a href="{{ route('backend.research_news.index') }}" class="card-footer bg-transparent border-top py-2 px-3 text-decoration-none small text-secondary d-flex justify-content-between align-items-center">
                    <span>จัดการข่าวสาร</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        <!-- Status Chart & Ranked Breakdown -->
        <div class="col-lg-5">
            <div class="card shadow-sm border h-100">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-pie-chart-fill text-primary me-2"></i>
                        <h6 class="mb-0 fw-bold text-body">สัดส่วนโครงการตามสถานะ</h6>
                    </div>
                    @if($selectedYear)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                            ปี {{ $selectedYear->year_name }}
                        </span>
                    @endif
                </div>
                <div class="card-body p-3">
                    <div style="height: 180px; width: 100%;" class="mb-3">
                        <canvas id="statusChart"></canvas>
                    </div>

                    @if(!empty($statusRankings))
                        <div class="border-top border-subtle pt-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-body-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">สรุปสถานะการดำเนินงาน</span>
                                <span class="text-body-secondary small" style="font-size: 0.72rem; font-variant-numeric: tabular-nums;">รวม {{ number_format($totalProjects) }} โครงการ</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                @foreach($statusRankings as $stItem)
                                    @php
                                        $stPct = $totalProjects > 0 ? round(($stItem['count'] / $totalProjects) * 100, 1) : 0;
                                    @endphp
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center small mb-1">
                                            <span class="text-truncate text-body-secondary" style="max-width: 65%; font-size: 0.8rem;" title="{{ $stItem['name'] }}">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; color: {{ $stItem['color'] }};"></i>
                                                {{ $stItem['name'] }}
                                            </span>
                                            <span class="text-body fw-semibold" style="font-size: 0.8rem; font-variant-numeric: tabular-nums;">
                                                {{ number_format($stItem['count']) }} <span class="text-body-secondary fw-normal">({{ $stPct }}%)</span>
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 3px; background-color: var(--bs-border-color-subtle, rgba(0,0,0,0.06));">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $stPct }}%; background-color: {{ $stItem['color'] }};" aria-valuenow="{{ $stPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 5-Year Projects & Budget Combo Chart -->
        <div class="col-lg-7">
            <div class="card shadow-sm border h-100">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>
                        <h6 class="mb-0 fw-bold text-body">แนวโน้มโครงการและงบประมาณวิจัยย้อนหลัง 5 ปีงบประมาณ</h6>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small">
                        5 ปีล่าสุด
                    </span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="yearChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Projects & System Quick Links -->
    <div class="row g-4">
        <!-- Recent Projects Table -->
        <div class="col-lg-8">
            <div class="card shadow-sm border">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold text-body">
                        <i class="bi bi-clock-history text-primary me-2"></i>โครงการวิจัยล่าสุด 5 รายการ
                        @if($selectedYear)
                            <span class="text-primary">(ปี {{ $selectedYear->year_name }})</span>
                        @endif
                    </h6>
                    <a href="{{ route('backend.rdb_project.index', $selectedYearId ? ['pro_year' => $selectedYearId] : []) }}" class="btn btn-sm btn-outline-primary">
                        ดูทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 55%;">ชื่อโครงการวิจัย</th>
                                    <th style="width: 25%;">ปี / หน่วยงาน</th>
                                    <th style="width: 20%;" class="text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProjects as $proj)
                                <tr>
                                    <td class="p-3">
                                        <a href="{{ route('backend.rdb_project.show', $proj->pro_id) }}" class="fw-bold text-body hover-primary text-decoration-none text-break d-block">
                                            {{ Str::limit(strip_tags($proj->pro_nameTH), 80) }}
                                        </a>
                                        @if($proj->pro_budget)
                                            <span class="text-success small fw-semibold">฿{{ number_format($proj->pro_budget) }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        @if($proj->year)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                ปี {{ $proj->year->year_name }}
                                            </span>
                                        @endif
                                        <div class="small text-body-secondary text-truncate mt-1" style="max-width: 180px;">
                                            {{ $proj->department->department_nameTH ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="text-center p-3">
                                        @if($proj->status)
                                            <span class="badge" style="background-color: {{ $proj->status->ps_color ?? '#6c757d' }}; color: #fff;">
                                                {{ $proj->status->ps_name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-body-secondary">
                                        {{ $selectedYear ? "ไม่พบข้อมูลโครงการวิจัยสำหรับปี {$selectedYear->year_name}" : "ยังไม่มีข้อมูลโครงการวิจัย" }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Quick Links -->
        <div class="col-lg-4">
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary">
                    @if($userRoleType === 'admin')
                        <h6 class="mb-0 fw-bold text-body"><i class="bi bi-grid-fill text-primary me-2"></i>เมนูลัดผู้ดูแลระบบ</h6>
                    @elseif($userRoleType === 'faculty')
                        <h6 class="mb-0 fw-bold text-body"><i class="bi bi-grid-fill text-primary me-2"></i>เมนูลัดผู้ประสานงานระดับคณะ</h6>
                    @else
                        <h6 class="mb-0 fw-bold text-body"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>เมนูลัดผลงานวิจัยของฉัน</h6>
                    @endif
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('backend.rdb_project.create') }}" class="list-group-item list-group-item-action bg-transparent d-flex align-items-center py-3">
                        <i class="bi bi-plus-circle-fill text-primary fs-5 me-3"></i>
                        <div>
                            <div class="fw-bold text-body">เพิ่มโครงการวิจัยใหม่</div>
                            <small class="text-body-secondary">บันทึกข้อมูลโครงการวิจัยและผู้ร่วมวิจัย</small>
                        </div>
                    </a>
                    <a href="{{ route('backend.rdb_published.create') }}" class="list-group-item list-group-item-action bg-transparent d-flex align-items-center py-3">
                        <i class="bi bi-file-earmark-plus-fill text-info fs-5 me-3"></i>
                        <div>
                            <div class="fw-bold text-body">เพิ่มผลงานตีพิมพ์</div>
                            <small class="text-body-secondary">บันทึกบทความวิจัย วารสาร และการอ้างอิง</small>
                        </div>
                    </a>
                    @if($userRoleType === 'admin')
                        <a href="{{ route('backend.user.index') }}" class="list-group-item list-group-item-action bg-transparent d-flex align-items-center py-3">
                            <i class="bi bi-person-gear text-primary fs-5 me-3"></i>
                            <div>
                                <div class="fw-bold text-body">จัดการผู้ใช้งาน (Users)</div>
                                <small class="text-body-secondary">จัดการบัญชีผู้ใช้งาน สิทธิ์การเข้าถึง และรีเซ็ตรหัสผ่าน</small>
                            </div>
                        </a>
                        <a href="{{ route('backend.rdb_researcher.create') }}" class="list-group-item list-group-item-action bg-transparent d-flex align-items-center py-3">
                            <i class="bi bi-person-plus-fill text-success fs-5 me-3"></i>
                            <div>
                                <div class="fw-bold text-body">เพิ่มข้อมูลนักวิจัย</div>
                                <small class="text-body-secondary">บันทึกประวัติ สังกัด และวุฒิการศึกษา</small>
                            </div>
                        </a>
                        <a href="{{ route('backend.research_news.create') }}" class="list-group-item list-group-item-action bg-transparent d-flex align-items-center py-3">
                            <i class="bi bi-megaphone-fill text-warning fs-5 me-3"></i>
                            <div>
                                <div class="fw-bold text-body">สร้างข่าวสารประชาสัมพันธ์</div>
                                <small class="text-body-secondary">เผยแพร่ข่าวสารและกิจกรรมงานวิจัย</small>
                            </div>
                        </a>
                    @else
                        <a href="{{ route('backend.rdb_dip.create') }}" class="list-group-item list-group-item-action bg-transparent d-flex align-items-center py-3">
                            <i class="bi bi-award-fill text-success fs-5 me-3"></i>
                            <div>
                                <div class="fw-bold text-body">เพิ่มทรัพย์สินทางปัญญา</div>
                                <small class="text-body-secondary">บันทึกข้อมูลสิทธิบัตร/อนุสิทธิบัตร/ลิขสิทธิ์</small>
                            </div>
                        </a>
                        <a href="{{ route('backend.rdbprojectutilize.create') }}" class="list-group-item list-group-item-action bg-transparent d-flex align-items-center py-3">
                            <i class="bi bi-rocket-takeoff-fill text-danger fs-5 me-3"></i>
                            <div>
                                <div class="fw-bold text-body">เพิ่มการนำไปใช้ประโยชน์</div>
                                <small class="text-body-secondary">บันทึกการนำผลงานวิจัยไปใช้ในพื้นที่</small>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12) !important;
    }
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#475569';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

    // 1. Status Pie / Doughnut Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusLabels = {!! json_encode($projectsByStatus->pluck('status.ps_name')->map(fn($n) => $n ?? 'ไม่ระบุ')) !!};
    const statusData = {!! json_encode($projectsByStatus->pluck('total')) !!};

    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels.length ? statusLabels : ['ไม่มีข้อมูล'],
            datasets: [{
                data: statusData.length ? statusData : [0],
                backgroundColor: [
                    '#2563EB', '#059669', '#D97706', '#DC2626', '#6366F1', '#0D9488', '#64748B'
                ],
                borderColor: isDark ? '#111D38' : '#FFFFFF',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // 2. 5-Year Projects & Budget Combo Chart (Bar + Line)
    const yearCtx = document.getElementById('yearChart').getContext('2d');
    const yearLabels = {!! json_encode($projectsByYear->pluck('year.year_name')->map(fn($y) => 'ปี ' . $y)) !!};
    const projectCountData = {!! json_encode($projectsByYear->pluck('total')) !!};
    const projectBudgetData = {!! json_encode($projectsByYear->pluck('total_budget')) !!};

    const yearChart = new Chart(yearCtx, {
        data: {
            labels: yearLabels,
            datasets: [
                {
                    type: 'bar',
                    label: 'จำนวนโครงการ (โครงการ)',
                    data: projectCountData,
                    backgroundColor: 'rgba(37, 99, 235, 0.75)',
                    borderColor: '#1E3A5F',
                    borderWidth: 1,
                    borderRadius: 6,
                    yAxisID: 'y',
                    order: 2
                },
                {
                    type: 'line',
                    label: 'งบประมาณรวม (บาท)',
                    data: projectBudgetData,
                    borderColor: '#D97706',
                    backgroundColor: 'rgba(217, 119, 6, 0.15)',
                    borderWidth: 3,
                    pointBackgroundColor: '#D97706',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.3,
                    fill: false,
                    yAxisID: 'y1',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            scales: {
                x: {
                    ticks: { color: textColor, font: { family: 'Plus Jakarta Sans, Prompt', size: 11 } },
                    grid: { display: false }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวนโครงการ',
                        color: textColor,
                        font: { family: 'Plus Jakarta Sans, Prompt', size: 11, weight: 'bold' }
                    },
                    ticks: {
                        color: textColor,
                        precision: 0,
                        maxTicksLimit: 5
                    },
                    grid: { color: gridColor }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'งบประมาณ (บาท)',
                        color: textColor,
                        font: { family: 'Plus Jakarta Sans, Prompt', size: 11, weight: 'bold' }
                    },
                    ticks: {
                        color: textColor,
                        maxTicksLimit: 5,
                        callback: function (value) {
                            if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'M ฿';
                            }
                            return value.toLocaleString() + ' ฿';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        boxWidth: 14,
                        usePointStyle: true,
                        color: textColor,
                        font: { family: 'Plus Jakarta Sans, Prompt', size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.yAxisID === 'y1') {
                                label += Number(context.raw).toLocaleString() + ' บาท';
                            } else {
                                label += Number(context.raw).toLocaleString() + ' โครงการ';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Theme Switch Listener
    window.addEventListener('themeChanged', (e) => {
        const isDarkMode = e.detail.theme === 'dark';
        const newTextColor = isDarkMode ? '#94A3B8' : '#475569';
        const newGridColor = isDarkMode ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

        // Update status chart
        statusChart.options.plugins.legend.labels.color = newTextColor;
        statusChart.data.datasets[0].borderColor = isDarkMode ? '#111D38' : '#FFFFFF';
        statusChart.update();

        // Update year chart
        yearChart.options.scales.x.ticks.color = newTextColor;
        yearChart.options.scales.y.ticks.color = newTextColor;
        yearChart.options.scales.y.title.color = newTextColor;
        yearChart.options.scales.y.grid.color = newGridColor;
        yearChart.options.scales.y1.ticks.color = newTextColor;
        yearChart.options.scales.y1.title.color = newTextColor;
        yearChart.options.plugins.legend.labels.color = newTextColor;
        yearChart.update();
    });
});
</script>
@endsection
