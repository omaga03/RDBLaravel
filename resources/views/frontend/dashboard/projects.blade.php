@extends('layouts.app')

@section('title', 'แดชบอร์ดโครงการวิจัยและงบประมาณ - มหาวิทยาลัยราชภัฏเพชรบูรณ์')

@section('content')
<div class="container-fluid py-4 px-md-4">
    @include('frontend.dashboard._subnav', [
        'pageTitle' => 'โครงการวิจัยและงบประมาณ (Research Projects & Budget)',
        'pageIcon' => 'bi bi-folder2-open'
    ])

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Budget -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm text-white" 
                 style="background: var(--rdb-gradient-primary); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <span class="badge bg-light-subtle bg-opacity-25 text-white border-0 mb-2">
                        <i class="bi bi-cash-stack me-1"></i> งบประมาณรวม
                    </span>
                    <h3 class="fw-bold mb-1 text-white text-nowrap">฿{{ number_format($totalBudget, 0) }}</h3>
                    <small class="text-white-50 d-block">เฉลี่ย ฿{{ number_format($avgBudget, 0) }} ต่อโครงการ</small>
                </div>
            </div>
        </div>

        <!-- Total Projects -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3.5">
                    <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-folder2 text-primary me-1"></i> โครงการทั้งหมด
                    </span>
                    <h3 class="fw-bold mb-0 text-body">{{ number_format($totalProjects) }}</h3>
                    <small class="text-body-secondary">โครงการวิจัย</small>
                </div>
            </div>
        </div>

        <!-- Completed -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3.5">
                    <span class="text-success small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-check-circle-fill me-1"></i> เสร็จสิ้นแล้ว
                    </span>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($completedCount) }}</h3>
                    <small class="text-body-secondary">{{ $totalProjects > 0 ? number_format(($completedCount / $totalProjects) * 100, 1) : 0 }}% ของทั้งหมด</small>
                </div>
            </div>
        </div>

        <!-- Ongoing & Extended -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3.5">
                    <span class="text-primary small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-hourglass-split me-1"></i> กำลังดำเนินการ / ขยายเวลา
                    </span>
                    <h3 class="fw-bold mb-0 text-primary">{{ number_format($ongoingCount + $extendedCount) }}</h3>
                    <small class="text-body-secondary">ขยายเวลา {{ number_format($extendedCount) }} โครงการ</small>
                </div>
            </div>
        </div>

        <!-- Overdue Alert -->
        <div class="col-xl-1 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm {{ $overdueCount > 0 ? 'border-danger-subtle bg-danger-subtle bg-opacity-10' : '' }}">
                <div class="card-body p-3 text-center">
                    <span class="text-danger small fw-bold d-block mb-1">
                        <i class="bi bi-exclamation-triangle-fill"></i> ค้างส่ง
                    </span>
                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($overdueCount) }}</h4>
                </div>
            </div>
        </div>

        <!-- Reports Available -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3.5">
                    <span class="text-info-emphasis small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> เล่มรายงานสมบูรณ์
                    </span>
                    <h3 class="fw-bold mb-0 text-body">{{ number_format($fullReportCount) }}</h3>
                    <small class="text-body-secondary">ดาวน์โหลด {{ number_format($totalDownloads) }} ครั้ง</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Funding Sources & Faculty Budgets -->
    <div class="row g-4 mb-4">
        <!-- Funding Source Types -->
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-pie-chart-fill text-primary me-2"></i> สัดส่วนงบประมาณตามประเภททุนวิจัย
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 280px; width: 100%;">
                        <canvas id="fundingDoughnutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget by Faculty -->
        <div class="col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-building-check text-success me-2"></i> การจัดสรรงบประมาณและโครงการรายคณะ
                    </h5>
                    <span class="badge bg-body-secondary text-body border">เรียงตามงบประมาณ</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 280px; width: 100%;">
                        <canvas id="facultyBudgetChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: 8-Year Budget Evolution -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-graph-up text-primary me-2"></i> สถิติงบประมาณวิจัยและขนาดทุนเฉลี่ยต่อโครงการย้อนหลัง 8 ปี
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 320px; width: 100%;">
                        <canvas id="yearlyBudgetTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deep-Dive Table: Top Funded Projects -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-list-stars text-primary me-2"></i> รายชื่อโครงการวิจัยเด่นตามงบประมาณ
                    </h5>
                    <a href="{{ route('frontend.rdbproject.index', !empty($selectedYearId) ? ['year_id' => $selectedYearId] : []) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        ไปที่หน้ารายการค้นหาละเอียด <i class="bi bi-arrow-right ms-1"></i>
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
                                <th style="width: 90px;" class="text-center">เอกสาร</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProjects as $proj)
                                <tr>
                                    <td class="text-center fw-bold text-body-secondary">{{ $proj->year->year_name ?? '-' }}</td>
                                    <td>
                                        <div class="fw-bold mb-1">
                                            @if($proj->pro_code)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1.5 py-0.5 me-1 small">{{ $proj->pro_code }}</span>
                                            @endif
                                            <a href="{{ route('frontend.rdbproject.show', $proj->pro_id) }}" target="_blank" class="text-body fw-bold text-decoration-none hover-primary">
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
                                    <td class="text-center text-nowrap">
                                        @if($proj->status)
                                            <span class="badge px-2.5 py-1" style="background-color: {{ $proj->status->ps_color ?? '#6c757d' }}; color: #fff;">
                                                {{ $proj->status->ps_name }}
                                            </span>
                                        @else
                                            <span class="text-body-secondary">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap">
                                        @if($proj->pro_file_path)
                                            <a href="{{ route('frontend.rdbproject.view_report', $proj->pro_id) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-1.5" title="รายงานฉบับสมบูรณ์">
                                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                            </a>
                                        @elseif($proj->pro_abstract_file_path)
                                            <a href="{{ route('frontend.rdbproject.view_abstract', $proj->pro_id) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-1.5" title="บทคัดย่อ">
                                                <i class="bi bi-file-text"></i>
                                            </a>
                                        @else
                                            <span class="text-body-secondary small">-</span>
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

    // 1. Funding Types Doughnut
    const fundingData = @json($fundingTypes);
    const fundingLabels = fundingData.map(d => d.type_name);
    const fundingBudgets = fundingData.map(d => (d.budget / 1000000).toFixed(2));
    const defaultPalette = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#06B6D4', '#EC4899', '#14B8A6', '#F97316', '#64748B'];
    const fundingColors = fundingData.map((d, i) => d.color || defaultPalette[i % defaultPalette.length]);

    const ctxFund = document.getElementById('fundingDoughnutChart').getContext('2d');
    new Chart(ctxFund, {
        type: 'doughnut',
        data: {
            labels: fundingLabels,
            datasets: [{
                data: fundingBudgets,
                backgroundColor: fundingColors,
                borderWidth: 2,
                borderColor: isDark ? '#1E293B' : '#FFFFFF'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { color: textColor, boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            return ' ' + ctx.label + ': ' + ctx.raw + ' ล้านบาท';
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // 2. Faculty Budget Bar
    const deptData = @json($deptBudgets);
    const deptLabels = deptData.map(d => d.department_nameTH.replace('คณะ', ''));
    const deptBudgetsArr = deptData.map(d => (d.total_budget / 1000000).toFixed(2));

    const ctxDept = document.getElementById('facultyBudgetChart').getContext('2d');
    new Chart(ctxDept, {
        type: 'bar',
        data: {
            labels: deptLabels,
            datasets: [{
                label: 'งบประมาณ (ล้านบาท)',
                data: deptBudgetsArr,
                backgroundColor: 'rgba(59, 130, 246, 0.85)',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } },
                y: { ticks: { color: textColor }, grid: { color: gridColor }, title: { display: true, text: 'ล้านบาท (MB)', color: textColor } }
            }
        }
    });

    // 3. Yearly Budget Trend Chart
    const trendData = @json($yearlyBudgetTrend);
    const trendLabels = trendData.map(d => 'ปี ' + d.year_name);
    const trendBudgets = trendData.map(d => (d.budget / 1000000).toFixed(2));
    const trendAvgs = trendData.map(d => (d.avg / 1000).toFixed(1));

    const ctxYear = document.getElementById('yearlyBudgetTrendChart').getContext('2d');
    new Chart(ctxYear, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'งบประมาณรวม (ล้านบาท)',
                    data: trendBudgets,
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    yAxisID: 'y'
                },
                {
                    label: 'ขนาดทุนเฉลี่ย (พันบาท/โครงการ)',
                    data: trendAvgs,
                    borderColor: '#F59E0B',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.2,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: textColor } }
            },
            scales: {
                x: { ticks: { color: textColor }, grid: { display: false } },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'งบประมาณรวม (ล้านบาท)', color: textColor },
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'เฉลี่ย (พันบาท)', color: textColor },
                    ticks: { color: textColor },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
});
</script>
@endsection
