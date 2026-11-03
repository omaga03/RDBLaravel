@extends('layouts.app')

@section('title', 'แดชบอร์ดการใช้ประโยชน์งานวิจัยเชิงพื้นที่และแผนที่ GIS - มหาวิทยาลัยราชภัฏเพชรบูรณ์')

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<style>
    #pcruGisMap {
        height: 520px;
        width: 100%;
        border-radius: var(--rdb-radius-md);
        z-index: 1;
        background-color: #e2e8f0;
    }
    .leaflet-popup-content-wrapper {
        border-radius: var(--rdb-radius-sm);
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    }
    .leaflet-popup-content {
        font-family: 'Plus Jakarta Sans', 'Prompt', sans-serif;
        font-size: 0.85rem;
        line-height: 1.4;
    }
    .custom-div-icon {
        background: transparent !important;
        border: none !important;
    }
    .gis-pin-pulse {
        background-color: #EF4444;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2.5px solid #FFFFFF;
        box-shadow: 0 0 8px rgba(239, 68, 68, 0.7);
        cursor: pointer;
        transition: transform 0.15s ease;
    }
    .gis-pin-pulse:hover {
        transform: scale(1.35);
        background-color: #DC2626;
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
            <div class="card h-100 border shadow-sm position-relative overflow-hidden hover-card" 
                 style="border-top: 3px solid #DC2626 !important; background-color: rgba(220, 38, 38, 0.04); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                <i class="bi bi-pin-map-fill me-1"></i> การนำไปใช้ประโยชน์รวม
                            </span>
                            <div class="p-1.5 rounded-circle bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-geo-alt fs-6"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-1 text-danger text-nowrap" style="font-variant-numeric: tabular-nums;">{{ number_format($totalUtilizations) }}</h3>
                        <small class="text-body-secondary d-block">บันทึกการนำไปใช้ประโยชน์เชิงประจักษ์</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Districts Covered -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm" style="border-top: 3px solid #EF4444 !important; background-color: rgba(239, 68, 68, 0.04); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <span class="text-danger small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-geo-fill me-1"></i> ครอบคลุมพื้นที่
                    </span>
                    <h3 class="fw-bold mb-0 text-danger" style="font-variant-numeric: tabular-nums;">{{ number_format($districtsRanking->count()) }}</h3>
                    <small class="text-body-secondary">อำเภอ/เขตพื้นที่</small>
                </div>
            </div>
        </div>

        <!-- Partner Agencies -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm" style="border-top: 3px solid #2563EB !important; background-color: rgba(37, 99, 235, 0.04); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <span class="text-primary small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-building me-1"></i> หน่วยงานผู้รับประโยชน์
                    </span>
                    <h3 class="fw-bold mb-0 text-primary" style="font-variant-numeric: tabular-nums;">{{ number_format($partnerCount) }}</h3>
                    <small class="text-body-secondary">อปท. / วิสาหกิจ / กลุ่มอาชีพ</small>
                </div>
            </div>
        </div>

        <!-- QA Certified -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm" style="border-top: 3px solid #10B981 !important; background-color: rgba(16, 185, 129, 0.04); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <span class="text-success small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-check2-all me-1"></i> ผ่านการรับรอง QA
                    </span>
                    <h3 class="fw-bold mb-0 text-success" style="font-variant-numeric: tabular-nums;">{{ number_format($qaCount) }}</h3>
                    <small class="text-body-secondary">มีหลักฐานรับรองคุณภาพ</small>
                </div>
            </div>
        </div>

        <!-- Total Budget / Value -->
        <div class="col-xl-3 col-md-8 col-sm-12">
            <div class="card h-100 border shadow-sm" style="border-top: 3px solid #059669 !important; background-color: rgba(5, 150, 105, 0.04); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-cash me-1 text-success"></i> มูลค่าผลกระทบ / งบสนับสนุน
                    </span>
                    <h3 class="fw-bold mb-0 text-body" style="font-variant-numeric: tabular-nums;">฿{{ number_format($totalBudget, 0) }}</h3>
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
                        <small class="text-body-secondary">ปักหมุดพิกัดจริงระดับตำบล/อำเภอทั่วจังหวัดเพชรบูรณ์และพื้นที่ข้างเคียง (สไตล์ Google Maps คมชัด ภาษาไทย)</small>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetMapView" title="รีเซ็ตมุมมองและซูมครอบคลุมทุกจุด">
                            <i class="bi bi-arrows-fullscreen me-1"></i> จัดมุมมองภาพรวม
                        </button>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode('จังหวัดเพชรบูรณ์') }}" target="_blank" class="btn btn-sm btn-outline-danger" title="เปิดดูแผนที่จังหวัดเพชรบูรณ์บน Google Maps">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Google Maps
                        </a>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5">
                            <i class="bi bi-geo-alt-fill me-1"></i> {{ number_format($mapMarkers->count()) }} จุดพิกัดในแผนที่
                        </span>
                    </div>
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

    <!-- Charts Row: 5 Pillars & Districts Ranking -->
    <div class="row g-4 mb-4">
        <!-- 5 Pillars of Utilization -->
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-pie-chart-fill text-danger me-2"></i> สัดส่วน 5 มิติการนำไปใช้ประโยชน์
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
                                <th>หน่วยงาน / องค์กรผู้รับประโยชน์ และ พื้นที่ดำเนินการ</th>
                                <th>โครงการวิจัยที่เกี่ยวข้อง</th>
                                <th style="width: 120px;" class="text-center">รับรอง QA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($utilizationsList as $util)
                                <tr>
                                    <td class="text-center small text-body-secondary text-nowrap">
                                        @if($util->utz_date)
                                            <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($util->utz_date, false, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('frontend.rdbprojectutilize.show', $util->utz_id) }}" target="_blank" class="fw-bold text-decoration-none text-body hover-primary d-block mb-1">
                                            {{ strip_tags((string) ($util->utz_department_name ?: 'หน่วยงานในพื้นที่')) }}
                                        </a>
                                        @if($util->utz_leading)
                                            <div class="small text-body-secondary mb-1">
                                                <i class="bi bi-person me-1"></i>ผู้ลงนาม: {{ strip_tags((string) $util->utz_leading) }}
                                            </div>
                                        @endif
                                        @if($util->changwat)
                                            @php
                                                $t = $util->changwat->tambon_t;
                                                $tStr = $t ? ((str_starts_with($t, 'ต.') || str_starts_with($t, 'แขวง')) ? $t : 'ต.' . $t) : '';
                                                $a = $util->changwat->amphoe_t;
                                                $aStr = $a ? ((str_starts_with($a, 'อ.') || str_starts_with($a, 'เขต')) ? $a : 'อ.' . $a) : '';
                                                $c = $util->changwat->changwat_t;
                                                $cStr = $c ? ((str_starts_with($c, 'จ.') || str_contains($c, 'กรุงเทพ')) ? $c : 'จ.' . $c) : '';
                                                $locStr = trim("$tStr $aStr $cStr");
                                            @endphp
                                            @if($locStr)
                                                <div class="small text-body-secondary text-break">
                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $locStr }}
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($util->project)
                                            <a href="{{ route('frontend.rdbproject.show', $util->project->pro_id) }}" target="_blank" class="small text-primary text-decoration-none hover-underline">
                                                <i class="bi bi-folder2 me-1"></i>{{ strip_tags((string) $util->project->pro_nameTH) }}
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
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-body-secondary">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i> ไม่พบข้อมูลการนำไปใช้ประโยชน์
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS & Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#475569';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

    // 1. Initialize Leaflet Map (Centered on Phetchabun)
    const map = L.map('pcruGisMap', {
        center: [16.42, 101.15],
        zoom: 9,
        scrollWheelZoom: false
    });

    // Tile Layers: Google Maps & CartoDB (High-Speed & Reliable in Thailand)
    const googleRoadmap = L.tileLayer('https://mt{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['0', '1', '2', '3'],
        attribution: '&copy; Google Maps'
    });

    const googleHybrid = L.tileLayer('https://mt{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['0', '1', '2', '3'],
        attribution: '&copy; Google Maps'
    });

    const cartoVoyager = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        subdomains: 'abcd',
        attribution: '&copy; CARTO'
    });

    const cartoDark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        subdomains: 'abcd',
        attribution: '&copy; CARTO'
    });

    // Set Default Active Layer
    const defaultLayer = isDark ? googleHybrid : googleRoadmap;
    defaultLayer.addTo(map);

    // Layer Switcher Control
    const baseLayers = {
        '🗺️ แผนที่ Google Maps': googleRoadmap,
        '🛰️ ภาพดาวเทียม Google': googleHybrid,
        '🎨 แผนที่ CartoDB': cartoVoyager,
        '🌙 แผนที่ธีมมืด (Dark)': cartoDark
    };
    L.control.layers(baseLayers, null, { position: 'topright' }).addTo(map);

    // Invalidate Size to ensure tiles render immediately across container
    setTimeout(function () {
        map.invalidateSize();
    }, 250);

    // Custom Marker Icon with Pulse Animation
    const customIcon = L.divIcon({
        className: 'custom-div-icon',
        html: '<div class="gis-pin-pulse"></div>',
        iconSize: [16, 16],
        iconAnchor: [8, 8],
        popupAnchor: [0, -8]
    });

    const markersData = @json($mapMarkers);
    const bounds = [];

    markersData.forEach(function (pt) {
        if (pt.lat && pt.long) {
            const lat = parseFloat(pt.lat);
            const lng = parseFloat(pt.long);
            if (!isNaN(lat) && !isNaN(lng)) {
                bounds.push([lat, lng]);
                const marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
                const popupHtml = `
                    <div style="min-width: 180px;">
                        <strong style="color: #DC2626; font-size: 0.95rem;">${pt.amphoe_t || 'เพชรบูรณ์'}</strong>
                        <div style="color: #64748B; font-size: 0.8rem; margin-bottom: 4px;">ต.${pt.tambon_t || '-'}, ${pt.changwat_t || 'จ.เพชรบูรณ์'}</div>
                        <div style="background: #F1F5F9; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 0.82rem; margin-top: 4px; color: #1E293B;">
                            📌 ผลงานวิจัยลงพื้นที่: <strong>${pt.count}</strong> โครงการ
                        </div>
                        <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 4px;">
                            พิกัด: ${lat.toFixed(4)}, ${lng.toFixed(4)}
                        </div>
                    </div>
                `;
                marker.bindPopup(popupHtml);
            }
        }
    });

    // Auto-fit map to markers bounds
    if (bounds.length > 0) {
        map.fitBounds(L.latLngBounds(bounds), { padding: [35, 35], maxZoom: 12 });
    }

    // Reset View Button Handler
    const btnReset = document.getElementById('btnResetMapView');
    if (btnReset) {
        btnReset.addEventListener('click', function () {
            if (bounds.length > 0) {
                map.fitBounds(L.latLngBounds(bounds), { padding: [35, 35], maxZoom: 12 });
            } else {
                map.setView([16.42, 101.15], 9);
            }
        });
    }

    // 2. 5 Pillars Doughnut Chart
    const pillars = @json($fivePillars ?? $fourPillars);
    const ctxPillars = document.getElementById('fourPillarsChart').getContext('2d');
    new Chart(ctxPillars, {
        type: 'doughnut',
        data: {
            labels: [
                'เชิงสาธารณะ (Public)', 
                'เชิงนโยบาย (Policy)', 
                'เชิงพาณิชย์ (Commercial)', 
                'เชิงพื้นที่ / ชุมชน (Community)', 
                'อื่น ๆ (Others)'
            ],
            datasets: [{
                data: [
                    pillars.public || 0, 
                    pillars.policy || 0, 
                    pillars.commercial || 0, 
                    pillars.community || 0, 
                    pillars.other || 0
                ],
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
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
    const hasMultipleProvs = @json(!empty($selectedChangwats) && count($selectedChangwats) > 1);
    const distLabels = distData.map(d => {
        let name = (d.amphoe_t || '-').replace('อ.', '');
        if (hasMultipleProvs && d.changwat_t) {
            name += ' (' + d.changwat_t.replace('จ.', '') + ')';
        }
        return name;
    });
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
