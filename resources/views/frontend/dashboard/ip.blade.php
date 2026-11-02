@extends('layouts.app')

@section('title', 'แดชบอร์ดทรัพย์สินทางปัญญาและนวัตกรรม - มหาวิทยาลัยราชภัฏเพชรบูรณ์')

@section('content')
<div class="container-fluid py-4 px-md-4">
    @include('frontend.dashboard._subnav', [
        'pageTitle' => 'ทรัพย์สินทางปัญญาและนวัตกรรม (Intellectual Property & Innovation)',
        'pageIcon' => 'bi bi-lightbulb-fill'
    ])

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total IP -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm text-white" 
                 style="background: linear-gradient(135deg, #7C2D12 0%, #F59E0B 100%); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <span class="badge bg-light-subtle bg-opacity-25 text-white border-0 mb-2">
                        <i class="bi bi-shield-check me-1"></i> ทรัพย์สินทางปัญญาทั้งหมด
                    </span>
                    <h3 class="fw-bold mb-1 text-white text-nowrap">{{ number_format($totalDips) }}</h3>
                    <small class="text-white-50 d-block">ผลงานที่ได้รับการคุ้มครองสิทธิ</small>
                </div>
            </div>
        </div>

        <!-- Patents (สิทธิบัตรการประดิษฐ์) -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm border-danger-subtle">
                <div class="card-body p-3.5">
                    <span class="text-danger small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-patch-check-fill me-1"></i> สิทธิบัตร
                    </span>
                    <h3 class="fw-bold mb-0 text-danger">{{ number_format($patentsCount) }}</h3>
                    <small class="text-body-secondary">สิทธิบัตรการประดิษฐ์</small>
                </div>
            </div>
        </div>

        <!-- Petty Patents (อนุสิทธิบัตร) -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm border-primary-subtle">
                <div class="card-body p-3.5">
                    <span class="text-primary small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-gear-wide-connected me-1"></i> อนุสิทธิบัตร
                    </span>
                    <h3 class="fw-bold mb-0 text-primary">{{ number_format($pettyPatentsCount) }}</h3>
                    <small class="text-body-secondary">สิ่งประดิษฐ์และนวัตกรรม</small>
                </div>
            </div>
        </div>

        <!-- Copyrights (ลิขสิทธิ์) -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm border-info-subtle">
                <div class="card-body p-3.5">
                    <span class="text-info small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-c-circle-fill me-1"></i> ลิขสิทธิ์
                    </span>
                    <h3 class="fw-bold mb-0 text-info">{{ number_format($copyrightsCount) }}</h3>
                    <small class="text-body-secondary">วรรณกรรมและงานสร้างสรรค์</small>
                </div>
            </div>
        </div>

        <!-- Registered -->
        <div class="col-xl-1.5 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm border-success-subtle bg-success-subtle bg-opacity-10">
                <div class="card-body p-3 text-center">
                    <span class="text-success small fw-bold d-block mb-1">
                        <i class="bi bi-check-circle-fill me-1.5"></i> ทะเบียนแล้ว
                    </span>
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($registeredCount) }}</h4>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="col-xl-1.5 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3 text-center">
                    <span class="text-body-secondary small fw-bold d-block mb-1">
                        <i class="bi bi-hourglass-top me-1.5"></i> ยื่นคำขอ
                    </span>
                    <h4 class="fw-bold mb-0 text-body">{{ number_format($pendingCount) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row: IP Types & Faculty Distribution -->
    <div class="row g-4 mb-4">
        <!-- IP Types Chart -->
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-pie-chart-fill text-warning me-2"></i> สัดส่วนประเภททรัพย์สินทางปัญญา
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 280px; width: 100%;">
                        <canvas id="ipTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- IP by Faculty -->
        <div class="col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-building text-primary me-2"></i> การกระจายทรัพย์สินทางปัญญาจำแนกตามคณะ
                    </h5>
                    <span class="badge bg-body-secondary text-body border">เรียงตามจำนวนผลงาน</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 280px; width: 100%;">
                        <canvas id="ipFacultyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deep-Dive Table: Intellectual Property Directory -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-award-fill text-warning me-2"></i> รายการทรัพย์สินทางปัญญา
                    </h5>
                    <a href="{{ route('frontend.rdbdip.index') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                        ดูรายการทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 170px;">เลขที่คำขอ / ทะเบียน</th>
                                <th>ชื่อผลงาน / ข้อมูลทรัพย์สินทางปัญญา</th>
                                <th style="width: 150px;" class="text-center">ประเภท</th>
                                <th style="width: 200px;">ผู้ประดิษฐ์ / เจ้าของสิทธิ</th>
                                <th style="width: 130px;" class="text-center">วันที่คำขอ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ipList as $dip)
                                <tr>
                                    <td>
                                        @if($dip->dip_patent_number ?? $dip->dip_number)
                                            <div class="fw-bold">
                                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle border">
                                                    ทะเบียน: {{ $dip->dip_patent_number ?? $dip->dip_number }}
                                                </span>
                                            </div>
                                        @endif
                                        @if($dip->dip_request_number)
                                            <small class="text-body-secondary d-block mt-0.5">
                                                คำขอ: {{ $dip->dip_request_number }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('frontend.rdbdip.show', $dip->dip_id) }}" target="_blank" class="fw-bold text-decoration-none text-body hover-primary">
                                            {{ strip_tags((string) ($dip->dip_data2_name ?: ($dip->dip_name ?: 'ไม่มีชื่อผลงาน'))) }}
                                        </a>
                                        @if($dip->project)
                                            <small class="text-body-secondary d-block mt-0.5">
                                                <i class="bi bi-link-45deg text-primary me-1.5"></i> โครงการ: {{ strip_tags((string) $dip->project->pro_nameTH) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($dip->dipType)
                                            <span class="badge bg-body-secondary text-body border">
                                                {{ $dip->dipType->dipt_name }}
                                            </span>
                                        @else
                                            <span class="text-body-secondary small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dip->researcher)
                                            <small class="text-body d-block fw-semibold">
                                                <i class="bi bi-person me-1"></i>{{ $dip->researcher->prefix->prefix_nameTH ?? '' }}{{ $dip->researcher->researcher_fname }} {{ $dip->researcher->researcher_lname }}
                                            </small>
                                            @if($dip->researcher->department)
                                                <small class="text-body-secondary d-block text-truncate" style="max-width: 190px;">
                                                    {{ $dip->researcher->department->department_nameTH }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-body-secondary small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center small text-body-secondary text-nowrap">
                                        @if($dip->dip_request_date)
                                            <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($dip->dip_request_date, false, true) }}
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
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#475569';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

    // 1. IP Types Doughnut Chart
    const ipTypeData = @json($byType);
    const ipLabels = ipTypeData.map(d => d.dipt_name || 'ไม่ระบุ');
    const ipCounts = ipTypeData.map(d => d.count);
    const ipColors = ['#F59E0B', '#3B82F6', '#10B981', '#8B5CF6'];

    const ctxType = document.getElementById('ipTypeChart').getContext('2d');
    new Chart(ctxType, {
        type: 'doughnut',
        data: {
            labels: ipLabels,
            datasets: [{
                data: ipCounts,
                backgroundColor: ipColors,
                borderWidth: 2,
                borderColor: isDark ? '#1E293B' : '#FFFFFF'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor } }
            },
            cutout: '55%'
        }
    });

    // 2. IP by Faculty Bar Chart
    const facData = @json($byFaculty);
    const facLabels = facData.map(d => d.department_nameTH.replace('คณะ', ''));
    const facCounts = facData.map(d => d.count);

    const ctxFac = document.getElementById('ipFacultyChart').getContext('2d');
    new Chart(ctxFac, {
        type: 'bar',
        data: {
            labels: facLabels,
            datasets: [{
                label: 'จำนวนทรัพย์สินทางปัญญา',
                data: facCounts,
                backgroundColor: '#F59E0B',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } },
                y: { ticks: { color: textColor }, grid: { color: gridColor }, title: { display: true, text: 'จำนวนรายการ', color: textColor } }
            }
        }
    });
});
</script>
@endsection
