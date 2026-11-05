@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backend.research_news.index') }}" class="text-decoration-none">จัดการข่าวและกิจกรรม</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายงานสรุปประจำเดือน</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>รายงานสรุปข่าวสารและการประชุมวิชาการประจำเดือน
            </h3>
        </div>
        <form method="GET" action="{{ route('backend.research_news.reports') }}" class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 fw-medium text-nowrap">เลือกเดือน:</label>
            <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" onchange="this.form.submit()">
            <a href="{{ route('backend.research_news.index') }}" class="btn btn-outline-secondary btn-sm text-nowrap">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
            </a>
        </form>
    </div>

    <!-- NRIIS News Card -->
    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-newspaper me-2 text-primary"></i>ข่าวทุนอุดหนุนงานวิจัย (NRIIS) ประจำเดือน {{ $month }}
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2">{{ $nriis->count() }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;" class="text-center">รหัส</th>
                            <th>หัวข้อข่าวทุนวิจัย</th>
                            <th style="width: 180px;">วันที่บันทึก</th>
                            <th style="width: 100px;" class="text-center">ยอดเข้าชม</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nriis as $row)
                        <tr>
                            <td class="text-center fw-mono">#{{ $row->id }}</td>
                            <td>
                                <a href="{{ route('backend.research_news.show', $row->id) }}" class="text-decoration-none fw-bold text-body hover-primary">
                                    {{ $row->news_name }}
                                </a>
                            </td>
                            <td>{{ \App\Helpers\ThaiDateHelper::format($row->created_at, true, true) }}</td>
                            <td class="text-center">{{ number_format($row->news_count ?? 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-body-secondary">ไม่พบรายการข่าวทุนวิจัยในเดือนนี้</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Conferences Card -->
    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-megaphone-fill me-2 text-primary"></i>ข่าวงานประชุมวิชาการ ประจำเดือน {{ $month }}
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2">{{ $conferences->count() }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;" class="text-center">รหัส</th>
                            <th>ชื่องานประชุมวิชาการ</th>
                            <th style="width: 180px;">วันที่บันทึก</th>
                            <th style="width: 100px;" class="text-center">ยอดเข้าชม</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conferences as $cRow)
                        <tr>
                            <td class="text-center fw-mono">#{{ $cRow->id }}</td>
                            <td>
                                <a href="{{ route('backend.research_conference.show', $cRow->id) }}" class="text-decoration-none fw-bold text-body hover-primary">
                                    {{ strip_tags($cRow->con_name) }}
                                </a>
                            </td>
                            <td>{{ \App\Helpers\ThaiDateHelper::format($cRow->created_at, true, true) }}</td>
                            <td class="text-center">{{ number_format($cRow->con_count ?? 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-body-secondary">ไม่พบรายการงานประชุมวิชาการในเดือนนี้</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
