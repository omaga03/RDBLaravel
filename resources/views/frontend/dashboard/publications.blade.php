@extends('layouts.app')

@section('title', 'แดชบอร์ดผลงานตีพิมพ์และคุณภาพวิชาการ - มหาวิทยาลัยราชภัฏเพชรบูรณ์')

@section('content')
<div class="container-fluid py-4 px-md-4">
    @include('frontend.dashboard._subnav', [
        'pageTitle' => 'ผลงานตีพิมพ์และคุณภาพวิชาการ (Publications & Academic Impact)',
        'pageIcon' => 'bi bi-journal-richtext'
    ])

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Publications -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm text-white" 
                 style="background: linear-gradient(135deg, #065F46 0%, #10B981 100%); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <span class="badge bg-light-subtle bg-opacity-25 text-white border-0 mb-2">
                        <i class="bi bi-journal-check me-1"></i> ผลงานตีพิมพ์รวม
                    </span>
                    <h3 class="fw-bold mb-1 text-white text-nowrap">{{ number_format($totalPublished) }}</h3>
                    <small class="text-white-50 d-block">วารสารวิชาการและการประชุมวิชาการ</small>
                </div>
            </div>
        </div>

        <!-- Scopus / WoS (International) -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm border-warning-subtle">
                <div class="card-body p-3.5">
                    <span class="text-warning-emphasis small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-globe-americas me-1 text-warning"></i> Scopus & WoS
                    </span>
                    <h3 class="fw-bold mb-0 text-warning">{{ number_format($scopusCount + $wosCount) }}</h3>
                    <small class="text-body-secondary">Scopus {{ number_format($scopusCount) }} | WoS {{ number_format($wosCount) }}</small>
                </div>
            </div>
        </div>

        <!-- TCI 1 -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm border-success-subtle">
                <div class="card-body p-3.5">
                    <span class="text-success small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-patch-check-fill me-1"></i> TCI กลุ่ม 1
                    </span>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($tci1Count) }}</h3>
                    <small class="text-body-secondary">วารสารระดับชาติกลุ่มที่ 1</small>
                </div>
            </div>
        </div>

        <!-- TCI 2 -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm border-info-subtle">
                <div class="card-body p-3.5">
                    <span class="text-info small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-award-fill me-1"></i> TCI กลุ่ม 2
                    </span>
                    <h3 class="fw-bold mb-0 text-info">{{ number_format($tci2Count) }}</h3>
                    <small class="text-body-secondary">วารสารระดับชาติกลุ่มที่ 2</small>
                </div>
            </div>
        </div>

        <!-- Proceedings -->
        <div class="col-xl-1 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3 text-center">
                    <span class="text-secondary small fw-bold d-block mb-1">
                        <i class="bi bi-megaphone me-1.5"></i> ประชุม
                    </span>
                    <h4 class="fw-bold mb-0 text-body">{{ number_format($confCount) }}</h4>
                </div>
            </div>
        </div>

        <!-- Linked with Grants -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3.5">
                    <span class="text-primary small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-link-45deg me-1"></i> ต่อยอดจากทุนวิจัย
                    </span>
                    <h3 class="fw-bold mb-0 text-primary">{{ number_format($linkedProjectCount) }}</h3>
                    <small class="text-body-secondary">{{ $totalPublished > 0 ? number_format(($linkedProjectCount / $totalPublished) * 100, 1) : 0 }}% มีโครงการรองรับ</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Quality Tiers Breakdown & Faculty Publications -->
    <div class="row g-4 mb-4">
        <!-- Quality Tiers Chart -->
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-pie-chart-fill text-success me-2"></i> สัดส่วนมาตรฐานวารสารและระดับสากล
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="qualityTiersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Publications by Faculty -->
        <div class="col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-building text-primary me-2"></i> จำนวนผลงานตีพิมพ์จำแนกรายคณะ
                    </h5>
                    <span class="badge bg-body-secondary text-body border">เรียงตามจำนวนผลงาน</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="facultyPubChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: Top Journals Ranking -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-book-half text-info me-2"></i> 10 อันดับวารสารวิชาการที่นักวิจัยได้รับการตีพิมพ์มากที่สุด
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 320px; width: 100%;">
                        <canvas id="topJournalsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deep-Dive Table: Publications List -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-card-text text-success me-2"></i> รายการผลงานตีพิมพ์ล่าสุด
                    </h5>
                    <a href="{{ route('frontend.rdbpublished.index', !empty($selectedYearId) ? ['year_id' => $selectedYearId] : []) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                        ดูผลงานทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ชื่อผลงานตีพิมพ์</th>
                                <th style="width: 170px;" class="text-center">มาตรฐานวารสาร</th>
                                <th style="width: 130px;" class="text-center">วันที่ตีพิมพ์</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($publicationsList as $pub)
                                <tr>
                                    <td>
                                        <a href="{{ route('frontend.rdbpublished.show', $pub->id) }}" target="_blank" class="fw-bold text-decoration-none text-body hover-primary d-block">
                                            {{ strip_tags((string) $pub->pub_name) }}
                                        </a>
                                        @if($pub->pub_name_journal)
                                            <small class="text-body-secondary d-block mt-1">
                                                <i class="bi bi-journal-text text-success me-1"></i>{{ strip_tags((string) $pub->pub_name_journal) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($pub->pubtype)
                                            @php
                                                $tierName = $pub->pubtype->pubtype_subgroup ?: ($pub->pubtype->pubtype_name ?: '-');
                                                $tierBadge = 'bg-body-secondary text-body border';
                                                if(str_contains($tierName, 'Scopus') || str_contains($tierName, 'Web of Science')) {
                                                    $tierBadge = 'bg-warning text-dark border-warning';
                                                } elseif(str_contains($tierName, 'TCI') && str_contains($tierName, '1')) {
                                                    $tierBadge = 'bg-success text-white';
                                                } elseif(str_contains($tierName, 'TCI')) {
                                                    $tierBadge = 'bg-info text-dark';
                                                }
                                            @endphp
                                            <span class="badge {{ $tierBadge }} text-truncate" style="max-width: 160px;" title="{{ $tierName }}">
                                                {{ $tierName }}
                                            </span>
                                        @else
                                            <span class="text-body-secondary small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center small text-body-secondary text-nowrap">
                                        @if($pub->pub_date)
                                            <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($pub->pub_date, false, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-body-secondary">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i> ไม่พบข้อมูลผลงานตีพิมพ์ตามเงื่อนไขที่เลือก
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#475569';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

    // 1. Quality Tiers Doughnut
    const tierData = @json($qualityTiers);
    const tierLabels = tierData.map(d => d.tier_name);
    const tierCounts = tierData.map(d => d.count);
    const tierColors = ['#10B981', '#3B82F6', '#F59E0B', '#8B5CF6', '#EC4899', '#14B8A6', '#6366F1', '#94A3B8', '#F97316'];

    const ctxTier = document.getElementById('qualityTiersChart').getContext('2d');
    new Chart(ctxTier, {
        type: 'doughnut',
        data: {
            labels: tierLabels,
            datasets: [{
                data: tierCounts,
                backgroundColor: tierColors,
                borderWidth: 2,
                borderColor: isDark ? '#1E293B' : '#FFFFFF'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { color: textColor, boxWidth: 12, font: { size: 10 } } }
            },
            cutout: '55%'
        }
    });

    // 2. Publications by Faculty Bar
    const facData = @json($byFaculty);
    const facLabels = facData.map(d => d.department_nameTH.replace('คณะ', ''));
    const facCounts = facData.map(d => d.count);

    const ctxFac = document.getElementById('facultyPubChart').getContext('2d');
    new Chart(ctxFac, {
        type: 'bar',
        data: {
            labels: facLabels,
            datasets: [{
                label: 'จำนวนบทความ',
                data: facCounts,
                backgroundColor: '#10B981',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } },
                y: { ticks: { color: textColor }, grid: { color: gridColor }, title: { display: true, text: 'จำนวนผลงาน', color: textColor } }
            }
        }
    });

    // 3. Top Journals Horizontal Bar Chart
    const journalData = @json($topJournals);
    const journalLabels = journalData.map(d => d.pub_name_journal.length > 35 ? d.pub_name_journal.substring(0, 35) + '...' : d.pub_name_journal);
    const journalCounts = journalData.map(d => d.count);

    const ctxJournal = document.getElementById('topJournalsChart').getContext('2d');
    new Chart(ctxJournal, {
        type: 'bar',
        data: {
            labels: journalLabels,
            datasets: [{
                label: 'จำนวนบทความที่ตีพิมพ์',
                data: journalCounts,
                backgroundColor: '#3B82F6',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: textColor }, grid: { color: gridColor }, title: { display: true, text: 'จำนวนบทความ', color: textColor } },
                y: { ticks: { color: textColor, font: { size: 11 } }, grid: { display: false } }
            }
        }
    });
});
</script>
@endsection
