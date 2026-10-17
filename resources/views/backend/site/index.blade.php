@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1 fw-bold text-body">
                <i class="bi bi-speedometer2 me-2 text-primary"></i>แดชบอร์ดภาพรวมระบบ (Admin Dashboard)
            </h2>
            <p class="text-body-secondary mb-0">สรุปข้อมูลสถิติและการดำเนินงานด้านการวิจัย สถาบันวิจัยและพัฒนา</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontend.site.index') }}" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i> ดูหน้าบ้าน (Public Site)
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards (6 Key Modules) -->
    <div class="row g-3 mb-4">
        <!-- 1. Projects -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">โครงการวิจัย</span>
                            <h3 class="fw-bold mb-0 text-primary">{{ number_format($totalProjects) }}</h3>
                        </div>
                        <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-folder2-open fs-4"></i>
                        </div>
                    </div>
                </div>
                <a href="{{ route('backend.rdb_project.index') }}" class="card-footer bg-transparent border-top py-2 px-3 text-decoration-none small text-primary d-flex justify-content-between align-items-center">
                    <span>จัดการโครงการ</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 2. Researchers -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">นักวิจัย</span>
                            <h3 class="fw-bold mb-0 text-success">{{ number_format($totalResearchers) }}</h3>
                        </div>
                        <div class="p-3 rounded-circle bg-success bg-opacity-10 text-success">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                </div>
                <a href="{{ route('backend.rdb_researcher.index') }}" class="card-footer bg-transparent border-top py-2 px-3 text-decoration-none small text-success d-flex justify-content-between align-items-center">
                    <span>จัดการนักวิจัย</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 3. Publications -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">ผลงานตีพิมพ์</span>
                            <h3 class="fw-bold mb-0 text-info">{{ number_format($totalPublished) }}</h3>
                        </div>
                        <div class="p-3 rounded-circle bg-info bg-opacity-10 text-info">
                            <i class="bi bi-file-earmark-text-fill fs-4"></i>
                        </div>
                    </div>
                </div>
                <a href="{{ route('backend.rdb_published.index') }}" class="card-footer bg-transparent border-top py-2 px-3 text-decoration-none small text-info d-flex justify-content-between align-items-center">
                    <span>จัดการงานตีพิมพ์</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 4. Intellectual Property -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">ทรัพย์สินทางปัญญา</span>
                            <h3 class="fw-bold mb-0 text-warning">{{ number_format($totalDip) }}</h3>
                        </div>
                        <div class="p-3 rounded-circle bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-award-fill fs-4"></i>
                        </div>
                    </div>
                </div>
                <a href="{{ route('backend.rdb_dip.index') }}" class="card-footer bg-transparent border-top py-2 px-3 text-decoration-none small text-warning d-flex justify-content-between align-items-center">
                    <span>จัดการทรัพย์สินฯ</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 5. Utilization -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">การใช้ประโยชน์</span>
                            <h3 class="fw-bold mb-0 text-danger">{{ number_format($totalUtilize) }}</h3>
                        </div>
                        <div class="p-3 rounded-circle bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-rocket-takeoff-fill fs-4"></i>
                        </div>
                    </div>
                </div>
                <a href="{{ route('backend.rdbprojectutilize.index') }}" class="card-footer bg-transparent border-top py-2 px-3 text-decoration-none small text-danger d-flex justify-content-between align-items-center">
                    <span>จัดการการนำไปใช้</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 6. News & Conferences -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">ข่าว & ประชุม</span>
                            <h3 class="fw-bold mb-0 text-secondary">{{ number_format($totalNews + $totalConference) }}</h3>
                        </div>
                        <div class="p-3 rounded-circle bg-secondary bg-opacity-10 text-secondary">
                            <i class="bi bi-newspaper fs-4"></i>
                        </div>
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
        <!-- Status Chart -->
        <div class="col-lg-5">
            <div class="card shadow-sm border h-100">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-pie-chart-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-bold text-body">สัดส่วนโครงการวิจัยตามสถานะ</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <div style="max-height: 280px; width: 100%;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Year Trend Chart -->
        <div class="col-lg-7">
            <div class="card shadow-sm border h-100">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-bold text-body">จำนวนโครงการวิจัยย้อนหลัง 5 ปีงบประมาณ</h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 280px; width: 100%;">
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
                    </h6>
                    <a href="{{ route('backend.rdb_project.index') }}" class="btn btn-sm btn-outline-primary">
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
                                    <td colspan="3" class="text-center py-4 text-body-secondary">ยังไม่มีข้อมูลโครงการวิจัย</td>
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
                    <h6 class="mb-0 fw-bold text-body"><i class="bi bi-grid-fill text-primary me-2"></i>เมนูลัดผู้ดูแลระบบ</h6>
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
    // Status Pie Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($projectsByStatus->pluck('status.ps_name')->map(fn($n) => $n ?? 'ไม่ระบุ')) !!},
            datasets: [{
                data: {!! json_encode($projectsByStatus->pluck('total')) !!},
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#6b7280'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: { size: 11 }
                    }
                }
            }
        }
    });

    // Year Bar Chart
    const yearCtx = document.getElementById('yearChart').getContext('2d');
    new Chart(yearCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($projectsByYear->pluck('year.year_name')->map(fn($y) => 'ปี ' . $y)) !!},
            datasets: [{
                label: 'จำนวนโครงการ',
                data: {!! json_encode($projectsByYear->pluck('total')) !!},
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: '#2563eb',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endsection
