@extends('layouts.app')

@section('title', 'แดชบอร์ดภาพรวมยุทธศาสตร์งานวิจัย - มหาวิทยาลัยราชภัฏเพชรบูรณ์')

@section('content')
<div class="container-fluid py-4 px-md-4">
    @include('frontend.dashboard._subnav', [
        'pageTitle' => 'ภาพรวมยุทธศาสตร์งานวิจัย (Executive Overview)',
        'pageIcon' => 'bi bi-speedometer2'
    ])

    <!-- 6 Executive KPI Bento Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Budget -->
        <div class="col-xxl-2 col-xl-4 col-md-6 col-sm-12">
            <div class="card h-100 border shadow-sm position-relative overflow-hidden hover-card rdb-card-tint-green" style="border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative d-flex flex-column justify-content-between" style="z-index: 1;">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-cash-stack me-1"></i> งบประมาณวิจัย
                            </span>
                            <div class="p-1.5 rounded-circle bg-success bg-opacity-10 text-success">
                                <i class="bi bi-graph-up fs-6"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-body text-nowrap" style="font-size: 1.45rem; font-variant-numeric: tabular-nums;">
                            @if($totalBudget >= 1000000)
                                ฿{{ number_format($totalBudget / 1000000, 2) }}M
                            @else
                                ฿{{ number_format($totalBudget, 0) }}
                            @endif
                        </h3>
                        <small class="text-body-secondary d-block mt-1 text-truncate" title="งบประมาณรวม ฿{{ number_format($totalBudget, 2) }} บาท">
                            งบจัดสรรรวม {{ number_format($totalBudget, 0) }} ฿
                        </small>
                    </div>

                    @if(!empty($yoyDelta['prev_year_name']))
                        @if($yoyDelta['budget_prev'] >= 10000 && $yoyDelta['budget_pct'] !== null)
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">เทียบปี {{ $yoyDelta['prev_year_name'] }}</span>
                                <span class="badge {{ $yoyDelta['budget_diff'] >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}" style="font-variant-numeric: tabular-nums; font-size: 0.7rem;">
                                    <i class="bi {{ $yoyDelta['budget_diff'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    {{ $yoyDelta['budget_diff'] >= 0 ? '+' : '' }}{{ $yoyDelta['budget_pct'] }}%
                                </span>
                            </div>
                        @else
                            <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                                <span class="text-body-secondary" style="font-size: 0.72rem;">ปีก่อน ({{ $yoyDelta['prev_year_name'] }})</span>
                                <span class="text-body-secondary fw-semibold" style="font-variant-numeric: tabular-nums; font-size: 0.72rem;">฿{{ number_format($yoyDelta['budget_prev'] / 1000000, 1) }}M</span>
                            </div>
                        @endif
                    @endif
                </div>
                <i class="bi bi-cash-coin position-absolute end-0 bottom-0 text-success" style="font-size: 3.5rem; opacity: 0.07; transform: translate(6px, 6px); pointer-events: none;"></i>
            </div>
        </div>

        <!-- 2. Projects -->
        <div class="col-xxl-2 col-xl-4 col-md-6 col-sm-6">
            <div class="card h-100 border shadow-sm position-relative overflow-hidden hover-card rdb-card-tint-blue" style="border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative d-flex flex-column justify-content-between" style="z-index: 1;">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-folder2-open me-1"></i> โครงการวิจัย
                            </span>
                            <div class="p-1.5 rounded-circle bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-folder-check fs-6"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-primary" style="font-size: 1.45rem; font-variant-numeric: tabular-nums;">
                            {{ number_format($totalProjects) }}
                        </h3>
                        <small class="text-body-secondary d-block mt-1">โครงการทั้งหมด</small>
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
                <i class="bi bi-folder position-absolute end-0 bottom-0 text-primary" style="font-size: 3.5rem; opacity: 0.07; transform: translate(6px, 6px); pointer-events: none;"></i>
            </div>
        </div>

        <!-- 3. Publications -->
        <div class="col-xxl-2 col-xl-4 col-md-6 col-sm-6">
            <div class="card h-100 border shadow-sm position-relative overflow-hidden hover-card rdb-card-tint-teal" style="border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative d-flex flex-column justify-content-between" style="z-index: 1;">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                <i class="bi bi-journal-richtext me-1"></i> ผลงานตีพิมพ์
                            </span>
                            <div class="p-1.5 rounded-circle bg-info bg-opacity-10 text-info">
                                <i class="bi bi-award fs-6"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-info" style="font-size: 1.45rem; font-variant-numeric: tabular-nums;">
                            {{ number_format($totalPublished) }}
                        </h3>
                        <div class="mt-1">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-1.5 py-0.5 rounded-pill" style="font-size: 0.7rem; font-variant-numeric: tabular-nums;">
                                <i class="bi bi-globe me-0.5"></i> Scopus/WoS: {{ number_format($internationalPubsCount) }}
                            </span>
                        </div>
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
                <i class="bi bi-journal-text position-absolute end-0 bottom-0 text-info" style="font-size: 3.5rem; opacity: 0.07; transform: translate(6px, 6px); pointer-events: none;"></i>
            </div>
        </div>

        <!-- 4. Utilizations -->
        <div class="col-xxl-2 col-xl-4 col-md-6 col-sm-6">
            <div class="card h-100 border shadow-sm position-relative overflow-hidden hover-card rdb-card-tint-red" style="border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative d-flex flex-column justify-content-between" style="z-index: 1;">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                <i class="bi bi-geo-alt me-1"></i> การใช้ประโยชน์
                            </span>
                            <div class="p-1.5 rounded-circle bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-pin-map fs-6"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-danger" style="font-size: 1.45rem; font-variant-numeric: tabular-nums;">
                            {{ number_format($totalUtilizations) }}
                        </h3>
                        <small class="text-body-secondary d-block mt-1">ชุมชน/พื้นที่รับประโยชน์</small>
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
                <i class="bi bi-pin-map-fill position-absolute end-0 bottom-0 text-danger" style="font-size: 3.5rem; opacity: 0.07; transform: translate(6px, 6px); pointer-events: none;"></i>
            </div>
        </div>

        <!-- 5. Intellectual Property (IP) -->
        <div class="col-xxl-2 col-xl-4 col-md-6 col-sm-6">
            <div class="card h-100 border shadow-sm position-relative overflow-hidden hover-card rdb-card-tint-amber" style="border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative d-flex flex-column justify-content-between" style="z-index: 1;">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                <i class="bi bi-lightbulb me-1"></i> ทรัพย์สินทางปัญญา
                            </span>
                            <div class="p-1.5 rounded-circle bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-patch-check fs-6"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-warning" style="font-size: 1.45rem; font-variant-numeric: tabular-nums;">
                            {{ number_format($totalDips) }}
                        </h3>
                        <small class="text-body-secondary d-block mt-1">สิทธิบัตร/อนุฯ/ลิขสิทธิ์</small>
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
                <i class="bi bi-award position-absolute end-0 bottom-0 text-warning" style="font-size: 3.5rem; opacity: 0.07; transform: translate(6px, 6px); pointer-events: none;"></i>
            </div>
        </div>

        <!-- 6. Researchers -->
        <div class="col-xxl-2 col-xl-4 col-md-6 col-sm-6">
            <div class="card h-100 border shadow-sm position-relative overflow-hidden hover-card rdb-card-tint-indigo" style="border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative d-flex flex-column justify-content-between" style="z-index: 1;">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-people-fill me-1"></i> นักวิจัย
                            </span>
                            <div class="p-1.5 rounded-circle bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-people fs-6"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-primary" style="font-size: 1.45rem; font-variant-numeric: tabular-nums;">
                            {{ number_format($totalResearchers) }}
                        </h3>
                        <small class="text-body-secondary d-block mt-1">นักวิจัยทั้งหมด (ไม่นับซ้ำ)</small>
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
                <i class="bi bi-person-lines-fill position-absolute end-0 bottom-0 text-primary" style="font-size: 3.5rem; opacity: 0.07; transform: translate(6px, 6px); pointer-events: none;"></i>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Multi-Year Trend & Status Distribution -->
    <div class="row g-4 mb-4">
        <!-- 6-Year Trend: Budget and Projects -->
        <div class="col-lg-8">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-graph-up-arrow text-primary me-2"></i> แนวโน้มงบประมาณและจำนวนโครงการวิจัยย้อนหลัง
                    </h5>
                    <span class="badge bg-body-secondary text-body border">
                        <i class="bi bi-calendar-range me-1"></i> ย้อนหลัง 6 ปีงบประมาณ
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 320px; width: 100%;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Status Distribution with Slim 3px Share Bars -->
        <div class="col-lg-4">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-pie-chart-fill text-info me-2"></i> สถานะการดำเนินงานโครงการ
                    </h5>
                    @if(!empty($selectedYear))
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                            ปี {{ $selectedYear->year_name }}
                        </span>
                    @endif
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                    <div style="height: 180px; width: 100%; position: relative;">
                        <canvas id="statusDonutChart"></canvas>
                    </div>

                    @if(!empty($statusRankings))
                        <div class="mt-3 pt-3 border-top border-subtle">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-body-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">สัดส่วนสถานะโครงการ</span>
                                <span class="text-body-secondary small" style="font-size: 0.72rem; font-variant-numeric: tabular-nums;">รวม {{ number_format($totalProjects) }}</span>
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
    </div>

    <!-- Charts Row 2: Faculty Research Performance Comparison -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-bar-chart-steps text-success me-2"></i> ผลผลิตงานวิจัยเปรียบเทียบจำแนกรายคณะ
                        @if(!empty($deptId) && $facultyStats->isNotEmpty())
                            <span class="text-primary fw-normal fs-6">({{ $facultyStats->first()['name'] }})</span>
                        @endif
                    </h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-layers me-1"></i> โครงการ • บทความตีพิมพ์ • การใช้ประโยชน์
                        @if(!empty($selectedYear))
                            • ปี {{ $selectedYear->year_name }}
                        @else
                            • ทุกปีงบประมาณ
                        @endif
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 350px; width: 100%;">
                        <canvas id="facultyCompareChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table: Top Funded Projects & Quick Links -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-star-fill text-warning me-2"></i> โครงการวิจัยงบประมาณสูงสุด (Top Funded Projects)
                    </h5>
                    <a href="{{ route('frontend.dashboard.projects', !empty($selectedYearId) ? ['year_id' => $selectedYearId] : []) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        ดูโครงการทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;" class="text-center">ปีงบฯ</th>
                                <th style="min-width: 280px;">ชื่อโครงการวิจัย / แหล่งทุน</th>
                                <th style="min-width: 230px;">คณะ / ผู้วิจัย</th>
                                <th style="width: 150px;" class="text-end">งบประมาณ</th>
                                <th style="width: 140px;" class="text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentProjects as $proj)
                                <tr>
                                    <td class="text-center fw-bold text-body-secondary">{{ $proj->year->year_name ?? '-' }}</td>
                                    <td>
                                        <div class="fw-bold mb-1">
                                            @if($proj->pro_code)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1.5 py-0.5 me-1 small">{{ $proj->pro_code }}</span>
                                            @endif
                                            <a href="{{ route('frontend.rdbproject.show', $proj->pro_id) }}" target="_blank" class="fw-bold text-decoration-none text-body hover-primary">
                                                {{ strip_tags((string) $proj->pro_nameTH) }}
                                            </a>
                                        </div>
                                        @php
                                            $masterName = $proj->type->masterType->master_pt_name ?? null;
                                            $typeName = $proj->type->pt_name ?? null;
                                        @endphp
                                        @if($masterName || $typeName)
                                            <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                                @if($masterName)
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5" style="font-size: 0.72rem;">
                                                        <i class="bi bi-cash-stack me-1"></i>{{ $masterName }}
                                                    </span>
                                                @endif
                                                @if($typeName && (!$masterName || $typeName !== $masterName))
                                                    <span class="badge bg-secondary-subtle text-body border px-2 py-0.5" style="font-size: 0.72rem;">
                                                        <i class="bi bi-tag me-1"></i>{{ $typeName }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $firstWork = $proj->rdbProjectWorks->first();
                                            $remainingWorks = $proj->rdbProjectWorks->slice(1);
                                            $remainingCount = $remainingWorks->count();
                                            $remainingNames = $remainingWorks->map(function($work) {
                                                return $work->researcher ? e($work->researcher->full_name) : '';
                                            })->filter()->implode('<br>• ');
                                        @endphp
                                        <div class="mb-1">
                                            @if($firstWork && $firstWork->researcher)
                                                <a href="{{ route('frontend.rdbresearcher.show', $firstWork->researcher->researcher_id) }}" 
                                                   target="_blank" rel="noopener noreferrer"
                                                   class="text-decoration-none text-body fw-bold hover-primary"
                                                   title="หัวหน้าโครงการ: {{ $firstWork->researcher->full_name }}">
                                                    <i class="bi bi-person-fill text-primary me-1"></i>{{ $firstWork->researcher->full_name }}
                                                </a>
                                                @if($remainingCount > 0)
                                                    <span class="badge bg-secondary-subtle text-body border ms-1" 
                                                          style="font-size: 0.72rem; cursor: pointer;"
                                                          data-bs-toggle="tooltip" 
                                                          data-bs-placement="top" 
                                                          data-bs-html="true" 
                                                          title="<strong>ผู้ร่วมวิจัย:</strong><br>• {!! $remainingNames !!}">
                                                        และผู้ร่วม ({{ $remainingCount }})
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-body-secondary small">-</span>
                                            @endif
                                        </div>
                                        @if($proj->department)
                                            <div class="small text-body-secondary text-truncate" style="max-width: 230px;" title="{{ $proj->department->department_nameTH }}">
                                                <i class="bi bi-buildings me-1 text-secondary"></i>{{ $proj->department->department_nameTH }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success text-nowrap">
                                        ฿{{ number_format($proj->pro_budget, 0) }}
                                    </td>
                                    <td class="text-center">
                                        @if($proj->status)
                                            <span class="badge px-2.5 py-1" style="background-color: {{ $proj->status->ps_color ?? '#6c757d' }}; color: #fff;">
                                                {{ $proj->status->ps_name }}
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
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#475569';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

    // 1. Trend Chart (Dual Axis: Budget in Millions & Project Count)
    const trendData = @json($yearlyTrend);
    const trendLabels = trendData.map(d => 'ปี ' + d.year_name);
    const trendBudgets = trendData.map(d => (d.budget / 1000000).toFixed(2));
    const trendProjects = trendData.map(d => d.projects);

    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'bar',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    type: 'line',
                    label: 'งบประมาณ (ล้านบาท)',
                    data: trendBudgets,
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'y1'
                },
                {
                    type: 'bar',
                    label: 'จำนวนโครงการ',
                    data: trendProjects,
                    backgroundColor: 'rgba(16, 185, 129, 0.75)',
                    borderRadius: 6,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: textColor, font: { family: 'Plus Jakarta Sans, Prompt' } } }
            },
            scales: {
                x: { ticks: { color: textColor }, grid: { display: false } },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: { display: true, text: 'จำนวนโครงการ', color: textColor },
                    ticks: { color: textColor, precision: 0, maxTicksLimit: 5 },
                    grid: { color: gridColor }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    title: { display: true, text: 'ล้านบาท (MB)', color: textColor },
                    ticks: { color: textColor, maxTicksLimit: 5 },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

    // 2. Status Donut Chart
    const statusData = @json($projectsByStatus);
    const statusLabels = statusData.filter(d => d.status).map(d => d.status.ps_name);
    const statusCounts = statusData.filter(d => d.status).map(d => d.total);
    const statusColors = statusData.filter(d => d.status).map(d => d.status.ps_color || '#6c757d');

    const ctxStatus = document.getElementById('statusDonutChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: statusColors,
                borderWidth: 2,
                borderColor: isDark ? '#1E293B' : '#FFFFFF'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            cutout: '72%'
        }
    });

    // 3. Faculty Performance Comparison Bar Chart
    const facultyData = @json($facultyStats);
    const facultyLabels = facultyData.map(d => d.short_name);
    const facProjects = facultyData.map(d => d.projects);
    const facPubs = facultyData.map(d => d.publications);
    const facUtils = facultyData.map(d => d.utilizations);

    const ctxFac = document.getElementById('facultyCompareChart').getContext('2d');
    new Chart(ctxFac, {
        type: 'bar',
        data: {
            labels: facultyLabels,
            datasets: [
                {
                    label: 'โครงการวิจัย',
                    data: facProjects,
                    backgroundColor: '#3B82F6',
                    borderRadius: 4
                },
                {
                    label: 'ผลงานตีพิมพ์',
                    data: facPubs,
                    backgroundColor: '#10B981',
                    borderRadius: 4
                },
                {
                    label: 'การนำไปใช้ประโยชน์',
                    data: facUtils,
                    backgroundColor: '#EF4444',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { color: textColor } }
            },
            scales: {
                x: { ticks: { color: textColor, font: { size: 11 } }, grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: { color: textColor, precision: 0, maxTicksLimit: 5 },
                    grid: { color: gridColor }
                }
            }
        }
    });
});
</script>
@endsection
