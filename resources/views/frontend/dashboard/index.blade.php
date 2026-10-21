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
            <div class="card h-100 border-0 shadow-sm text-white overflow-hidden position-relative" 
                 style="background: var(--rdb-gradient-primary); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative" style="z-index: 1;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-light-subtle bg-opacity-25 text-white border-0">
                            <i class="bi bi-cash-stack me-1"></i> งบประมาณวิจัย
                        </span>
                        <i class="bi bi-graph-up text-white-50"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-white text-nowrap" style="font-size: 1.55rem;">
                        ฿{{ number_format($totalBudget, 0) }}
                    </h3>
                    <div class="small text-white-50 mt-1 d-flex align-items-center text-truncate" title="งบประมาณที่ได้รับการสนับสนุนจัดสรร">
                        <i class="bi bi-info-circle me-1"></i> งบประมาณจัดสรร
                    </div>
                </div>
                <i class="bi bi-cash-coin position-absolute end-0 bottom-0 text-white" style="font-size: 4rem; opacity: 0.12; transform: translate(10px, 10px);"></i>
            </div>
        </div>

        <!-- 2. Projects -->
        <div class="col-xxl-2 col-xl-4 col-md-6 col-sm-6">
            <div class="card h-100 border-0 shadow-sm text-white overflow-hidden position-relative" 
                 style="background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative" style="z-index: 1;">
                    <span class="badge bg-light-subtle bg-opacity-25 text-white border-0 mb-2">
                        <i class="bi bi-folder2-open me-1"></i> โครงการวิจัย
                    </span>
                    <h2 class="fw-bold mb-1 text-white">{{ number_format($totalProjects) }}</h2>
                    <small class="text-white-50 d-block">โครงการทั้งหมด</small>
                </div>
                <i class="bi bi-folder-check position-absolute end-0 bottom-0 text-white" style="font-size: 4rem; opacity: 0.15; transform: translate(10px, 10px);"></i>
            </div>
        </div>

        <!-- 3. Publications -->
        <div class="col-xxl-2 col-xl-4 col-md-6 col-sm-6">
            <div class="card h-100 border-0 shadow-sm text-white overflow-hidden position-relative" 
                 style="background: linear-gradient(135deg, #065F46 0%, #10B981 100%); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative" style="z-index: 1;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-light-subtle bg-opacity-25 text-white border-0">
                            <i class="bi bi-journal-richtext me-1"></i> ผลงานตีพิมพ์
                        </span>
                    </div>
                    <h2 class="fw-bold mb-1 text-white">{{ number_format($totalPublished) }}</h2>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-1.5 py-0.5 rounded-pill" style="font-size: 0.72rem;">
                        <i class="bi bi-globe me-0.5"></i> Scopus/WoS: {{ number_format($internationalPubsCount) }}
                    </span>
                </div>
                <i class="bi bi-award position-absolute end-0 bottom-0 text-white" style="font-size: 4rem; opacity: 0.15; transform: translate(10px, 10px);"></i>
            </div>
        </div>

        <!-- 4. Utilizations -->
        <div class="col-xxl-2 col-xl-4 col-md-6 col-sm-6">
            <div class="card h-100 border-0 shadow-sm text-white overflow-hidden position-relative" 
                 style="background: linear-gradient(135deg, #991B1B 0%, #EF4444 100%); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative" style="z-index: 1;">
                    <span class="badge bg-light-subtle bg-opacity-25 text-white border-0 mb-2">
                        <i class="bi bi-geo-alt me-1"></i> การใช้ประโยชน์
                    </span>
                    <h2 class="fw-bold mb-1 text-white">{{ number_format($totalUtilizations) }}</h2>
                    <small class="text-white-50 d-block">ชุมชนและพื้นที่รับประโยชน์</small>
                </div>
                <i class="bi bi-pin-map-fill position-absolute end-0 bottom-0 text-white" style="font-size: 4rem; opacity: 0.15; transform: translate(10px, 10px);"></i>
            </div>
        </div>

        <!-- 5. Intellectual Property (IP) -->
        <div class="col-xxl-2 col-xl-4 col-md-6 col-sm-6">
            <div class="card h-100 border-0 shadow-sm text-white overflow-hidden position-relative" 
                 style="background: linear-gradient(135deg, #7C2D12 0%, #F59E0B 100%); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative" style="z-index: 1;">
                    <span class="badge bg-light-subtle bg-opacity-25 text-white border-0 mb-2">
                        <i class="bi bi-lightbulb me-1"></i> ทรัพย์สินทางปัญญา
                    </span>
                    <h2 class="fw-bold mb-1 text-white">{{ number_format($totalDips) }}</h2>
                    <small class="text-white-50 d-block">สิทธิบัตร/อนุฯ/ลิขสิทธิ์</small>
                </div>
                <i class="bi bi-patch-check position-absolute end-0 bottom-0 text-white" style="font-size: 4rem; opacity: 0.15; transform: translate(10px, 10px);"></i>
            </div>
        </div>

        <!-- 6. Researchers -->
        <div class="col-xxl-2 col-xl-4 col-md-6 col-sm-6">
            <div class="card h-100 border-0 shadow-sm text-white overflow-hidden position-relative" 
                 style="background: linear-gradient(135deg, #4338CA 0%, #6366F1 100%); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 position-relative" style="z-index: 1;">
                    <span class="badge bg-light-subtle bg-opacity-25 text-white border-0 mb-2">
                        <i class="bi bi-people-fill me-1"></i> นักวิจัย
                    </span>
                    <h2 class="fw-bold mb-1 text-white">{{ number_format($totalResearchers) }}</h2>
                    <small class="text-white-50 d-block">นักวิจัยทั้งหมด (ไม่นับซ้ำ)</small>
                </div>
                <i class="bi bi-people position-absolute end-0 bottom-0 text-white" style="font-size: 4rem; opacity: 0.15; transform: translate(10px, 10px);"></i>
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

        <!-- Project Status Distribution (Direct from DB ps_color) -->
        <div class="col-lg-4">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-pie-chart-fill text-info me-2"></i> สถานะการดำเนินงานโครงการ
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                    <div style="height: 230px; width: 100%; position: relative;">
                        <canvas id="statusDonutChart"></canvas>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <div class="row g-2">
                            @foreach($projectsByStatus as $st)
                                @if($st->status)
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-1.5 small">
                                            <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: {{ $st->status->ps_color ?? '#6c757d' }};"></span>
                                            <span class="text-truncate" title="{{ $st->status->ps_name }}">{{ $st->status->ps_name }}</span>
                                            <strong class="ms-auto">{{ number_format($st->total) }}</strong>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
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
                                <th>ชื่อโครงการวิจัย</th>
                                <th style="width: 220px;">คณะ / หน่วยงาน</th>
                                <th style="width: 150px;" class="text-end">งบประมาณ</th>
                                <th style="width: 140px;" class="text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentProjects as $proj)
                                <tr>
                                    <td class="text-center fw-bold text-body-secondary">{{ $proj->year->year_name ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('frontend.rdbproject.show', $proj->pro_id) }}" target="_blank" class="fw-bold text-decoration-none text-body hover-primary">
                                            {{ strip_tags((string) $proj->pro_nameTH) }}
                                        </a>
                                        @if($proj->pro_nameEN)
                                            <small class="text-body-secondary d-block">{{ strip_tags((string) $proj->pro_nameEN) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-body-secondary text-body border text-truncate" style="max-width: 210px;" title="{{ $proj->department->department_nameTH ?? '-' }}">
                                            {{ $proj->department->department_nameTH ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
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
                    title: { display: true, text: 'จำนวนโครงการ', color: textColor },
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'ล้านบาท (MB)', color: textColor },
                    ticks: { color: textColor },
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
            cutout: '65%'
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
                y: { ticks: { color: textColor }, grid: { color: gridColor } }
            }
        }
    });
});
</script>
@endsection
