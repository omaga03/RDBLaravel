@extends('layouts.app')

@section('title', 'แดชบอร์ดการใช้ประโยชน์งานวิจัยเชิงพื้นที่และแผนที่ GIS - มหาวิทยาลัยราชภัฏเพชรบูรณ์')

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #pcruGisMap {
        height: 480px;
        width: 100%;
        border-radius: var(--rdb-radius-md);
        z-index: 10;
    }
    .leaflet-popup-content-wrapper {
        border-radius: var(--rdb-radius-sm);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    .leaflet-popup-content {
        font-family: 'Plus Jakarta Sans', 'Prompt', sans-serif;
        font-size: 0.85rem;
        line-height: 1.4;
    }
</style>

<div class="container-fluid py-4 px-md-4">
    @include('frontend.dashboard._subnav', [
        'pageTitle' => 'การนำไปใช้ประโยชน์เชิงพื้นที่ & แผนที่ GIS (Community Utilization & GIS)',
        'pageIcon' => 'bi bi-geo-alt-fill'
    ])

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Utilizations -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm text-white" 
                 style="background: linear-gradient(135deg, #991B1B 0%, #EF4444 100%); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <span class="badge bg-white bg-opacity-25 text-white border-0 mb-2">
                        <i class="bi bi-pin-map-fill me-1"></i> การนำไปใช้ประโยชน์รวม
                    </span>
                    <h3 class="fw-bold mb-1 text-white text-nowrap">{{ number_format($totalUtilizations) }}</h3>
                    <small class="text-white-50 d-block">บันทึกการนำไปใช้ประโยชน์เชิงประจักษ์</small>
                </div>
            </div>
        </div>

        <!-- Districts Covered -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm border-danger-subtle">
                <div class="card-body p-3.5">
                    <span class="text-danger small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-geo-fill me-1"></i> ครอบคลุมพื้นที่
                    </span>
                    <h3 class="fw-bold mb-0 text-danger">{{ number_format($districtsRanking->count()) }}</h3>
                    <small class="text-body-secondary">อำเภอ/เขตพื้นที่</small>
                </div>
            </div>
        </div>

        <!-- Partner Agencies -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3.5">
                    <span class="text-primary small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-building me-1"></i> หน่วยงานผู้รับประโยชน์
                    </span>
                    <h3 class="fw-bold mb-0 text-primary">{{ number_format($partnerCount) }}</h3>
                    <small class="text-body-secondary">อปท. / วิสาหกิจ / กลุ่มอาชีพ</small>
                </div>
            </div>
        </div>

        <!-- QA Certified -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm border-success-subtle">
                <div class="card-body p-3.5">
                    <span class="text-success small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-check2-all me-1"></i> ผ่านการรับรอง QA
                    </span>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($qaCount) }}</h3>
                    <small class="text-body-secondary">มีหลักฐานรับรองคุณภาพ</small>
                </div>
            </div>
        </div>

        <!-- Total Budget / Value -->
        <div class="col-xl-3 col-md-8 col-sm-12">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3.5">
                    <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-cash me-1 text-success"></i> มูลค่าผลกระทบ / งบสนับสนุน
                    </span>
                    <h3 class="fw-bold mb-0 text-body">฿{{ number_format($totalBudget, 0) }}</h3>
                    <small class="text-body-secondary">มูลค่าการพัฒนาและต่อยอดเศรษฐกิจ</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Leaflet GIS Map Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fw-bold text-body">
                            <i class="bi bi-map-fill text-danger me-2"></i> แผนที่สารสนเทศภูมิศาสตร์การนำผลงานวิจัยไปใช้ประโยชน์ (GIS Interactive Map)
                        </h5>
                        <small class="text-body-secondary">ปักหมุดพิกัดจริงระดับตำบล/อำเภอทั่วจังหวัดเพชรบูรณ์และพื้นที่ข้างเคียง</small>
                    </div>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5">
                        <i class="bi bi-geo-alt-fill me-1"></i> {{ number_format($mapMarkers->count()) }} จุดพิกัดในแผนที่
                    </span>
                </div>
                <div class="card-body p-3">
                    <div id="pcruGisMap"></div>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-1 small text-body-secondary flex-wrap gap-2">
                        <span><i class="bi bi-info-circle me-1"></i> คลิกที่หมุดในแผนที่เพื่อดูข้อมูลสรุปโครงการวิจัยที่ลงพัฒนาในพื้นที่นั้น</span>
                        <span class="text-muted fst-italic">หมายเหตุ: พิกัดบนแผนที่เป็นตำแหน่งโดยประมาณระดับพื้นที่/ตำบล</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row: 4 Pillars & Districts Ranking -->
    <div class="row g-4 mb-4">
        <!-- 4 Pillars of Utilization -->
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-pie-chart-fill text-danger me-2"></i> สัดส่วน 4 มิติการนำไปใช้ประโยชน์
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 280px; width: 100%;">
                        <canvas id="fourPillarsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Districts Ranking Bar Chart -->
        <div class="col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i> อันดับพื้นที่/อำเภอที่มีการนำงานวิจัยไปใช้ประโยชน์สูงสุด
                    </h5>
                    <span class="badge bg-body-secondary text-body border">แยกตามอำเภอ</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 280px; width: 100%;">
                        <canvas id="districtsBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deep-Dive Table: Utilizations List -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-list-check text-danger me-2"></i> รายการการนำไปใช้ประโยชน์ล่าสุด
                    </h5>
                    <a href="{{ route('frontend.rdbprojectutilize.index', !empty($selectedYearId) ? ['year_id' => $selectedYearId] : []) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                        ดูรายการทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 140px;" class="text-center text-nowrap">วันที่นำไปใช้</th>
                                <th>หน่วยงาน / องค์กรผู้รับประโยชน์</th>
                                <th style="width: 220px;">พื้นที่ดำเนินการ</th>
                                <th>โครงการวิจัยที่เกี่ยวข้อง</th>
                                <th style="width: 120px;" class="text-center">รับรอง QA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($utilizationsList as $util)
                                <tr>
                                    <td class="text-center small text-body-secondary text-nowrap">
                                        @if($util->utz_date)
                                            <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($util->utz_date, false, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('frontend.rdbprojectutilize.show', $util->utz_id) }}" target="_blank" class="fw-bold text-decoration-none text-body hover-primary">
                                            {!! $util->utz_department_name ?: 'หน่วยงานในพื้นที่' !!}
                                        </a>
                                        @if($util->utz_leading)
                                            <small class="text-body-secondary d-block">
                                                <i class="bi bi-person me-1"></i>ผู้ลงนาม: {!! $util->utz_leading !!}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($util->changwat)
                                            <small class="text-body text-break d-block">
                                                <i class="bi bi-geo-alt text-danger me-1"></i>
                                                @if($util->changwat->tambon_t) ต.{{ $util->changwat->tambon_t }} @endif
                                                @if($util->changwat->amphoe_t) {{ $util->changwat->amphoe_t }} @endif
                                                @if($util->changwat->changwat_t) {{ $util->changwat->changwat_t }} @endif
                                            </small>
                                        @else
                                            <span class="text-body-secondary small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($util->project)
                                            <a href="{{ route('frontend.rdbproject.show', $util->project->pro_id) }}" target="_blank" class="small text-primary text-decoration-none hover-underline">
                                                <i class="bi bi-folder2 me-1"></i>{!! $util->project->pro_nameTH !!}
                                            </a>
                                        @else
                                            <span class="text-body-secondary small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($util->utz_group_qa == '1')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-patch-check-fill me-1"></i> ผ่าน QA
                                            </span>
                                        @else
                                            <span class="badge bg-body-secondary text-body-secondary border px-2 py-1">
                                                ทั่วไป
                                            </span>
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

<!-- Leaflet JS & Chart.js -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#475569';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

    // 1. Initialize Leaflet Map (Centered on Phetchabun)
    const map = L.map('pcruGisMap').setView([16.42, 101.15], 9);

    // OpenStreetMap Tile Layer
    const tileUrl = isDark 
        ? 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png'
        : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

    L.tileLayer(tileUrl, {
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Custom Marker Icon
    const customIcon = L.divIcon({
        className: 'custom-div-icon',
        html: '<div style="background-color: #EF4444; width: 14px; height: 14px; border-radius: 50%; border: 2.5px solid #FFFFFF; box-shadow: 0 0 6px rgba(0,0,0,0.4);"></div>',
        iconSize: [14, 14],
        iconAnchor: [7, 7]
    });

    const markersData = @json($mapMarkers);
    markersData.forEach(function (pt) {
        if (pt.lat && pt.long) {
            const marker = L.marker([parseFloat(pt.lat), parseFloat(pt.long)], { icon: customIcon }).addTo(map);
            const popupHtml = `
                <div style="min-width: 170px;">
                    <strong style="color: #EF4444; font-size: 0.95rem;">${pt.amphoe_t || 'เพชรบูรณ์'}</strong>
                    <div style="color: #64748B; font-size: 0.8rem; margin-bottom: 4px;">ต.${pt.tambon_t || '-'}, ${pt.changwat_t || 'จ.เพชรบูรณ์'}</div>
                    <div style="background: #F1F5F9; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 0.82rem; margin-top: 4px;">
                        📌 ผลงานวิจัยลงพื้นที่: <strong>${pt.count}</strong> โครงการ
                    </div>
                </div>
            `;
            marker.bindPopup(popupHtml);
        }
    });

    // 2. 4 Pillars Doughnut Chart
    const pillars = @json($fourPillars);
    const ctxPillars = document.getElementById('fourPillarsChart').getContext('2d');
    new Chart(ctxPillars, {
        type: 'doughnut',
        data: {
            labels: ['เชิงสาธารณะ (Public)', 'เชิงนโยบาย (Policy)', 'เชิงพาณิชย์ (Commercial)', 'เชิงชุมชน/พื้นที่ (Community)'],
            datasets: [{
                data: [pillars.public, pillars.policy, pillars.commercial, pillars.community],
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'],
                borderWidth: 2,
                borderColor: isDark ? '#1E293B' : '#FFFFFF'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor, boxWidth: 12, font: { size: 10 } } }
            },
            cutout: '55%'
        }
    });

    // 3. Districts Ranking Bar Chart
    const distData = @json($districtsRanking).slice(0, 11);
    const distLabels = distData.map(d => d.amphoe_t.replace('อ.', ''));
    const distCounts = distData.map(d => d.count);

    const ctxDist = document.getElementById('districtsBarChart').getContext('2d');
    new Chart(ctxDist, {
        type: 'bar',
        data: {
            labels: distLabels,
            datasets: [{
                label: 'จำนวนการนำไปใช้ประโยชน์',
                data: distCounts,
                backgroundColor: '#EF4444',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } },
                y: { ticks: { color: textColor }, grid: { color: gridColor }, title: { display: true, text: 'จำนวนโครงการ', color: textColor } }
            }
        }
    });
});
</script>
@endsection
