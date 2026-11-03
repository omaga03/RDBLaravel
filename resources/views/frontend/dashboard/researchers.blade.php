@extends('layouts.app')

@section('title', 'แดชบอร์ดศักยภาพนักวิจัยและรายคณะ - มหาวิทยาลัยราชภัฏเพชรบูรณ์')

@section('content')
<div class="container-fluid py-4 px-md-4">
    @include('frontend.dashboard._subnav', [
        'pageTitle' => 'ศักยภาพนักวิจัยและรายคณะ (Researchers & Faculty Matrix)',
        'pageIcon' => 'bi bi-people-fill'
    ])

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Researchers -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm position-relative overflow-hidden hover-card" 
                 style="border-top: 3px solid #4F46E5 !important; border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-people me-1"></i> นักวิจัยในระบบทั้งหมด
                            </span>
                            <div class="p-1.5 rounded-circle bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-people-fill fs-6"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-1 text-primary text-nowrap" style="font-variant-numeric: tabular-nums;">{{ number_format($totalResearchers) }} ท่าน</h3>
                        <small class="text-body-secondary d-block">ครอบคลุม 13 คณะและหน่วยงาน</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scopus Author ID Profiles -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm" style="border-top: 3px solid #D97706 !important; border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <span class="text-warning-emphasis small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-journal-text me-1 text-warning"></i> Scopus Author ID
                    </span>
                    <h3 class="fw-bold mb-0 text-warning" style="font-variant-numeric: tabular-nums;">{{ number_format($scopusProfileCount) }}</h3>
                    <small class="text-body-secondary">นักวิจัยที่มีรหัสฐานข้อมูลสากล Scopus</small>
                </div>
            </div>
        </div>

        <!-- ORCID ID Profiles -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm" style="border-top: 3px solid #10B981 !important; border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <span class="text-success small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-person-badge me-1"></i> ORCID ID
                    </span>
                    <h3 class="fw-bold mb-0 text-success" style="font-variant-numeric: tabular-nums;">{{ number_format($orcidProfileCount) }}</h3>
                    <small class="text-body-secondary">รหัสนักวิจัยมาตรฐานสากล Open Researcher</small>
                </div>
            </div>
        </div>

        <!-- Faculty Count -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm" style="border-top: 3px solid #2563EB !important; border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <span class="text-primary small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-building me-1"></i> หน่วยงานวิชาการ
                    </span>
                    <h3 class="fw-bold mb-0 text-body" style="font-variant-numeric: tabular-nums;">{{ number_format($researchersByFaculty->count()) }}</h3>
                    <small class="text-body-secondary">คณะและสถาบันในสังกัด</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboards: Top Researchers by Projects & Publications -->
    <div class="row g-4 mb-4">
        <!-- Leaderboard: Top by Projects -->
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-trophy-fill text-warning me-2"></i> 10 อันดับนักวิจัยที่มีโครงการวิจัยสูงสุด
                    </h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">โครงการ</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th>ชื่อ - สกุล นักวิจัย / สังกัดคณะ</th>
                                <th style="width: 95px;" class="text-center">โครงการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topByProjects as $idx => $r)
                                <tr>
                                    <td class="text-center fw-bold">
                                        @if($idx === 0)
                                            <span class="badge rounded-circle bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">1</span>
                                        @elseif($idx === 1)
                                            <span class="badge rounded-circle bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2 py-1">2</span>
                                        @elseif($idx === 2)
                                            <span class="badge rounded-circle bg-danger-subtle text-danger px-2 py-1">3</span>
                                        @else
                                            <span class="text-body-secondary small">{{ $idx + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <a href="{{ route('frontend.rdbresearcher.show', $r->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="fw-bold text-decoration-none text-body hover-primary">
                                                <i class="bi bi-person-fill text-primary me-1"></i>{{ $r->prefix_nameTH ?? '' }}{{ $r->researcher_fname }} {{ $r->researcher_lname }}
                                            </a>
                                        </div>
                                        @if(!empty($r->department_nameTH))
                                            <small class="text-body-secondary d-flex align-items-center gap-1 mt-0.5 text-truncate" title="{{ $r->department_nameTH }}">
                                                <i class="bi bi-buildings text-secondary me-1.5"></i> {{ $r->department_nameTH }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold text-primary">
                                        {{ number_format($r->project_count) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Leaderboard: Top by Publications -->
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-award-fill text-success me-2"></i> 10 อันดับนักวิจัยที่มีผลงานตีพิมพ์สูงสุด
                    </h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">ผลงานตีพิมพ์</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th>ชื่อ - สกุล นักวิจัย / สังกัดคณะ</th>
                                <th style="width: 95px;" class="text-center">ผลงาน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topByPubs as $idx => $r)
                                <tr>
                                    <td class="text-center fw-bold">
                                        @if($idx === 0)
                                            <span class="badge rounded-circle bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">1</span>
                                        @elseif($idx === 1)
                                            <span class="badge rounded-circle bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2 py-1">2</span>
                                        @elseif($idx === 2)
                                            <span class="badge rounded-circle bg-danger-subtle text-danger px-2 py-1">3</span>
                                        @else
                                            <span class="text-body-secondary small">{{ $idx + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <a href="{{ route('frontend.rdbresearcher.show', $r->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="fw-bold text-decoration-none text-body hover-primary">
                                                <i class="bi bi-person-fill text-success me-1"></i>{{ $r->prefix_nameTH ?? '' }}{{ $r->researcher_fname }} {{ $r->researcher_lname }}
                                            </a>
                                        </div>
                                        @if(!empty($r->department_nameTH))
                                            <small class="text-body-secondary d-flex align-items-center gap-1 mt-0.5 text-truncate" title="{{ $r->department_nameTH }}">
                                                <i class="bi bi-buildings text-secondary me-1.5"></i> {{ $r->department_nameTH }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold text-success">
                                        {{ number_format($r->pub_count) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row: Faculty Researchers & Academic Titles -->
    <div class="row g-4 mb-4">
        <!-- Faculty Researchers Bar Chart -->
        <div class="col-lg-8">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i> จำนวนคณาจารย์และนักวิจัยจำแนกตามคณะ
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="facultyResearchersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Titles Doughnut -->
        <div class="col-lg-4">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-mortarboard-fill text-info me-2"></i> สัดส่วนตำแหน่งทางวิชาการ
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="academicTitlesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deep-Dive Table: Researchers Directory -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-people text-primary me-2"></i> ทำเนียบคณาจารย์และนักวิจัยเด่น
                    </h5>
                    <a href="{{ route('frontend.rdbresearcher.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        ดูรายชื่อนักวิจัยทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ชื่อ - สกุล นักวิจัย / สังกัดคณะ</th>
                                <th style="width: 130px;" class="text-center">โครงการวิจัย</th>
                                <th style="width: 130px;" class="text-center">งานตีพิมพ์</th>
                                <th style="width: 140px;" class="text-center">โปรไฟล์สากล</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($researchersList as $res)
                                <tr>
                                    <td>
                                        <div>
                                            <a href="{{ route('frontend.rdbresearcher.show', $res->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="fw-bold text-decoration-none text-body hover-primary">
                                                <i class="bi bi-person-fill text-primary me-1"></i>{{ $res->prefix->prefix_nameTH ?? '' }}{{ $res->researcher_fname }} {{ $res->researcher_lname }}
                                            </a>
                                            @if($res->researcher_fnameEN || $res->researcher_lnameEN)
                                                <small class="text-body-secondary ms-1">({{ trim(($res->researcher_fnameEN ?? '') . ' ' . ($res->researcher_lnameEN ?? '')) }})</small>
                                            @endif
                                        </div>
                                        @if($res->department)
                                            <small class="text-body-secondary d-flex align-items-center gap-1 mt-0.5" title="{{ $res->department->department_nameTH }}">
                                                <i class="bi bi-buildings text-secondary me-1.5"></i> {{ $res->department->department_nameTH }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold text-primary">
                                        {{ number_format($res->rdb_projects_count) }}
                                    </td>
                                    <td class="text-center fw-bold text-success">
                                        {{ number_format($res->rdb_publisheds_count) }}
                                    </td>
                                    <td class="text-center">
                                        @if($res->scopus_authorId)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" title="Scopus Author ID: {{ $res->scopus_authorId }}">
                                                Scopus
                                            </span>
                                        @endif
                                        @if($res->orcid)
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" title="ORCID: {{ $res->orcid }}">
                                                ORCID
                                            </span>
                                        @endif
                                        @if(!$res->scopus_authorId && !$res->orcid)
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

    {{-- รายงานนักวิจัยรุ่นใหม่ (First Project) --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body"><i class="bi bi-person-plus-fill text-success me-2"></i> นักวิจัยรุ่นใหม่ (เริ่มทำโครงการครั้งแรก){{ !empty($selectedYear) ? ' ปี '.$selectedYear->year_name : '' }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>คณะ / หน่วยงาน</th><th class="text-end">จำนวน (ท่าน)</th></tr></thead>
                        <tbody>
                            @forelse($firstProjectByFaculty as $r)
                                <tr><td>{{ $r->department_nameTH }}</td><td class="text-end fw-bold">{{ number_format($r->new_researcher_count) }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-body-secondary py-3">ไม่พบข้อมูล</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold text-body"><i class="bi bi-list-check text-primary me-2"></i> รายชื่อนักวิจัยรุ่นใหม่ล่าสุด</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>นักวิจัย</th><th>โครงการแรก</th><th class="text-center">ปีงบฯ</th></tr></thead>
                        <tbody>
                            @forelse($firstProjectList as $r)
                                <tr>
                                    <td><a href="{{ route('frontend.rdbresearcher.show', $r->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-bold text-body hover-primary">{{ $r->prefix_nameTH }}{{ $r->researcher_fname }} {{ $r->researcher_lname }}</a><small class="d-block text-body-secondary">{{ $r->department_nameTH }}</small></td>
                                    <td><a href="{{ route('frontend.rdbproject.show', $r->pro_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body hover-primary">{{ \Illuminate\Support\Str::limit(strip_tags((string) $r->pro_nameTH), 70) }}</a></td>
                                    <td class="text-center">{{ $r->year_name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-body-secondary py-3">ไม่พบข้อมูล</td></tr>
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

    // 1. Faculty Researchers Bar Chart
    const facData = @json($researchersByFaculty);
    const facLabels = facData.map(d => d.department_nameTH.replace('คณะ', ''));
    const facCounts = facData.map(d => d.count);

    const ctxFac = document.getElementById('facultyResearchersChart').getContext('2d');
    new Chart(ctxFac, {
        type: 'bar',
        data: {
            labels: facLabels,
            datasets: [{
                label: 'จำนวนนักวิจัย (ท่าน)',
                data: facCounts,
                backgroundColor: '#6366F1',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } },
                y: { ticks: { color: textColor }, grid: { color: gridColor }, title: { display: true, text: 'จำนวนนักวิจัย (ท่าน)', color: textColor } }
            }
        }
    });

    // 2. Academic Titles Doughnut Chart
    const titleData = @json($academicTitles);
    const titleLabels = titleData.map(d => d.title_name);
    const titleCounts = titleData.map(d => d.count);
    const titleColors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6', '#64748B'];

    const ctxTitle = document.getElementById('academicTitlesChart').getContext('2d');
    new Chart(ctxTitle, {
        type: 'doughnut',
        data: {
            labels: titleLabels,
            datasets: [{
                data: titleCounts,
                backgroundColor: titleColors,
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
});
</script>
@endsection
