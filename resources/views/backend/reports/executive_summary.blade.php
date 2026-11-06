@extends('layouts.app')

@section('content')
<div class="container-fluid px-2 px-sm-3 px-md-4 executive-report-wrapper">
    <!-- Non-Print Filter & Action Toolbar -->
    <div class="d-print-none mb-3 mb-md-4">
        <div class="p-3 bg-body-tertiary border rounded-3 shadow-sm">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2.5 rounded-3 bg-primary bg-opacity-10 text-primary flex-shrink-0">
                        <i class="bi bi-file-earmark-bar-graph-fill fs-3"></i>
                    </div>
                    <div>
                        <h1 class="h5 h-md-4 mb-0 fw-bold text-body-emphasis">เล่มรายงานสรุปภาพรวมงานวิจัยสำหรับผู้บริหาร (Executive Strategic Report)</h1>
                        <p class="text-body-secondary small mb-0">ระบบสารสนเทศวิจัยเชิงยุทธศาสตร์ — สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์</p>
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 flex-wrap">
                    <form method="GET" action="{{ route('backend.reports.executive_summary') }}" class="d-flex flex-wrap align-items-center gap-2 m-0 flex-grow-1 flex-sm-grow-0">
                        <!-- Year Filter -->
                        <div class="flex-grow-1 flex-sm-grow-0" style="min-width: 150px;">
                            <select name="year_id" class="form-select form-select-sm w-100">
                                <option value="all">ปีงบประมาณทั้งหมด</option>
                                @foreach($years as $y)
                                    <option value="{{ $y->year_id }}" {{ (string)$filter_year_id === (string)$y->year_id ? 'selected' : '' }}>
                                        ปีงบประมาณ พ.ศ. {{ $y->year_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Faculty Filter -->
                        <div class="flex-grow-1 flex-sm-grow-0" style="min-width: 170px;">
                            <select name="department_id" class="form-select form-select-sm w-100">
                                <option value="all">คณะ/หน่วยงานทั้งหมด</option>
                                @foreach($faculties as $f)
                                    <option value="{{ $f->department_id }}" {{ (string)$filter_dept_id === (string)$f->department_id ? 'selected' : '' }}>
                                        {{ $f->department_nameTH ?? $f->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-funnel-fill"></i> คัดกรอง
                        </button>
                        @if($filter_year_id || $filter_dept_id)
                            <a href="{{ route('backend.reports.executive_summary') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center">
                                ล้างตัวกรอง
                            </a>
                        @endif
                    </form>

                    <button type="button" class="btn btn-sm btn-success d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm text-nowrap" onclick="window.print()">
                        <i class="bi bi-printer-fill"></i> <span>พิมพ์เล่มรายงาน / บันทึก PDF</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Printable Report Sheet -->
    <div class="card border shadow-sm printable-sheet p-3 p-sm-4 p-md-5 bg-body">
        <!-- Report Header / Letterhead -->
        <div class="report-header pb-3 mb-4 border-bottom">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="https://research.pcru.ac.th/rdb/images/pcru.png" alt="PCRU Logo" class="report-logo flex-shrink-0">
                    <div>
                        <div class="text-uppercase small fw-bold text-primary tracking-wider" style="letter-spacing: 0.05em; font-size: 0.72rem;">PCRU Research Intelligence & Strategic Portfolio</div>
                        <h2 class="h4 h-md-3 fw-bold text-body-emphasis mb-1">รายงานสรุปสารสนเทศงานวิจัย นวัตกรรม และงานสร้างสรรค์</h2>
                        <div class="text-body-secondary small">
                            สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์ · 
                            <strong>
                                {{ $selected_year ? 'ประจำปีงบประมาณ พ.ศ. ' . $selected_year->year_name : 'ข้อมูลภาพรวมสะสมทุกปีงบประมาณ' }}
                            </strong>
                            @if($selected_department)
                                · <strong>{{ $selected_department->department_nameTH ?? $selected_department->department_name }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-sm-end d-flex d-sm-block flex-wrap align-items-center gap-2 pt-2 pt-sm-0 border-top border-sm-0 w-100 w-sm-auto">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 mb-sm-1" style="font-size: 0.78rem;">
                        <i class="bi bi-shield-check me-1"></i> ข้อมูลทางการ (Official)
                    </span>
                    <div class="text-body-tertiary small" style="font-size: 0.72rem;">
                        ประมวลผลเมื่อ: {{ \App\Helpers\ThaiDateHelper::formatShort(date('Y-m-d H:i:s')) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 1: Executive KPI Strip (3 Columns x 2 Rows) -->
        <div class="mb-4">
            <div class="section-title-strip mb-3">
                <h3 class="h6 fw-bold text-uppercase text-body-secondary mb-0 d-flex align-items-center gap-1.5">
                    <i class="bi bi-speedometer text-primary"></i> ตัวชี้วัดผลผลิตสำคัญ (Executive Key Performance Indicators)
                </h3>
            </div>
            <div class="row g-2 g-sm-3">
                <!-- Row 1: Col 1 - โครงการวิจัย -->
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="p-3 rounded-3 border bg-body-tertiary text-center h-100 d-flex flex-column justify-content-center">
                        <div class="text-secondary small mb-1 fw-semibold text-truncate"><i class="bi bi-folder2-open text-primary me-1"></i> โครงการวิจัย</div>
                        <div class="kpi-stat-number fw-bold text-primary mb-0 font-monospace text-truncate" style="font-variant-numeric: tabular-nums;">
                            {{ number_format($kpis['total_projects']) }}
                        </div>
                        <div class="text-body-tertiary small mt-1" style="font-size: 0.72rem;">โครงการ</div>
                    </div>
                </div>
                <!-- Row 1: Col 2 - งบประมาณรวม -->
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="p-3 rounded-3 border bg-body-tertiary text-center h-100 d-flex flex-column justify-content-center">
                        <div class="text-secondary small mb-1 fw-semibold text-truncate"><i class="bi bi-cash-stack text-success me-1"></i> งบประมาณรวม</div>
                        <div class="kpi-stat-number fw-bold text-success mb-0 font-monospace text-truncate" style="font-variant-numeric: tabular-nums;">
                            {{ number_format($kpis['total_budget'], 0) }}
                        </div>
                        <div class="text-body-tertiary small mt-1" style="font-size: 0.72rem;">บาท</div>
                    </div>
                </div>
                <!-- Row 1: Col 3 - ผลงานตีพิมพ์ -->
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="p-3 rounded-3 border bg-body-tertiary text-center h-100 d-flex flex-column justify-content-center">
                        <div class="text-secondary small mb-1 fw-semibold text-truncate"><i class="bi bi-journal-richtext text-info me-1"></i> งานตีพิมพ์</div>
                        <div class="kpi-stat-number fw-bold text-info mb-0 font-monospace text-truncate" style="font-variant-numeric: tabular-nums;">
                            {{ number_format($kpis['total_published']) }}
                        </div>
                        <div class="text-body-tertiary small mt-1" style="font-size: 0.72rem;">บทความ</div>
                    </div>
                </div>
                <!-- Row 2: Col 1 - ทรัพย์สินทางปัญญา -->
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="p-3 rounded-3 border bg-body-tertiary text-center h-100 d-flex flex-column justify-content-center">
                        <div class="text-secondary small mb-1 fw-semibold text-truncate"><i class="bi bi-lightbulb text-warning me-1"></i> ทรัพย์สินทางปัญญา</div>
                        <div class="kpi-stat-number fw-bold text-warning mb-0 font-monospace text-truncate" style="font-variant-numeric: tabular-nums;">
                            {{ number_format($kpis['total_dips']) }}
                        </div>
                        <div class="text-body-tertiary small mt-1" style="font-size: 0.72rem;">ผลงาน/คำขอ</div>
                    </div>
                </div>
                <!-- Row 2: Col 2 - การใช้ประโยชน์ -->
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="p-3 rounded-3 border bg-body-tertiary text-center h-100 d-flex flex-column justify-content-center">
                        <div class="text-secondary small mb-1 fw-semibold text-truncate"><i class="bi bi-rocket-takeoff text-danger me-1"></i> การใช้ประโยชน์</div>
                        <div class="kpi-stat-number fw-bold text-danger mb-0 font-monospace text-truncate" style="font-variant-numeric: tabular-nums;">
                            {{ number_format($kpis['total_utilizations']) }}
                        </div>
                        <div class="text-body-tertiary small mt-1" style="font-size: 0.72rem;">เรื่อง/พื้นที่</div>
                    </div>
                </div>
                <!-- Row 2: Col 3 - นักวิจัยที่มีผลงาน -->
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="p-3 rounded-3 border bg-body-tertiary text-center h-100 d-flex flex-column justify-content-center">
                        <div class="text-secondary small mb-1 fw-semibold text-truncate"><i class="bi bi-people text-primary me-1"></i> นักวิจัย active</div>
                        <div class="kpi-stat-number fw-bold text-body-emphasis mb-0 font-monospace text-truncate" style="font-variant-numeric: tabular-nums;">
                            {{ number_format($kpis['active_researchers']) }}
                        </div>
                        <div class="text-body-tertiary small mt-1" style="font-size: 0.72rem;">ท่าน</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Budget Distribution (Master Types) & Quality Matrix (2 Columns) -->
        <div class="row g-3 g-md-4 mb-4 page-break-inside-avoid">
            <!-- Left: Budget by Master Project Type (rdb_project_types_master) -->
            <div class="col-12 col-lg-6">
                <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h4 class="h6 fw-bold text-body-emphasis mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span><i class="bi bi-pie-chart-fill text-primary me-1.5"></i> งบประมาณจำแนกตามประเภททุนวิจัย</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">ประเภททุนหลัก</span>
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.84rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>ประเภททุนหลัก</th>
                                        <th class="text-center" style="width: 70px;">จำนวน</th>
                                        <th class="text-end" style="width: 120px;">งบประมาณ (บาท)</th>
                                        <th class="text-end" style="width: 70px;">สัดส่วน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($budget_by_type as $bt)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <span class="d-inline-block rounded-circle flex-shrink-0" style="width: 10px; height: 10px; background-color: {{ $bt['color'] }};"></span>
                                                    <span class="fw-medium text-body text-truncate" style="max-width: 180px;" title="{{ $bt['name'] }}">
                                                        {{ $bt['name'] }}
                                                    </span>
                                                </div>
                                                <div class="progress mt-1 ms-3" style="height: 3px; max-width: 120px;">
                                                    <div class="progress-bar" style="width: {{ $bt['percentage'] }}%; background-color: {{ $bt['color'] }};"></div>
                                                </div>
                                            </td>
                                            <td class="text-center font-monospace" style="font-variant-numeric: tabular-nums;">{{ number_format($bt['count']) }}</td>
                                            <td class="text-end font-monospace fw-semibold text-success" style="font-variant-numeric: tabular-nums;">{{ number_format($bt['budget']) }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace" style="font-variant-numeric: tabular-nums;">
                                                    {{ $bt['percentage'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-body-secondary py-3">ไม่พบข้อมูลงบประมาณ</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="text-body-tertiary small pt-2 border-top mt-3" style="font-size: 0.72rem;">
                        <i class="bi bi-info-circle me-1"></i> จำแนกตามกลุ่มประเภททุนหลัก (Master Project Types) ของมหาวิทยาลัย
                    </div>
                </div>
            </div>

            <!-- Right: Publication Quality Matrix (Scopus & TCI) -->
            <div class="col-12 col-lg-6">
                <div class="border rounded-3 p-3 h-100">
                    <h4 class="h6 fw-bold text-body-emphasis mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span><i class="bi bi-award-fill text-warning me-1.5"></i> คุณภาพผลงานตีพิมพ์ระดับสากลและระดับชาติ</span>
                        <span class="badge bg-info-subtle text-info border border-info-subtle">Scopus &amp; TCI</span>
                    </h4>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-sm-3">
                            <div class="p-2 border rounded text-center bg-body-tertiary">
                                <div class="small fw-bold text-primary">Scopus Q1</div>
                                <div class="h5 mb-0 fw-bold font-monospace text-primary" style="font-variant-numeric: tabular-nums;">
                                    {{ $publication_quality['scopus_q1'] }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2 border rounded text-center bg-body-tertiary">
                                <div class="small fw-bold text-info">Scopus Q2</div>
                                <div class="h5 mb-0 fw-bold font-monospace text-info" style="font-variant-numeric: tabular-nums;">
                                    {{ $publication_quality['scopus_q2'] }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2 border rounded text-center bg-body-tertiary">
                                <div class="small fw-bold text-secondary">Scopus Q3</div>
                                <div class="h5 mb-0 fw-bold font-monospace text-secondary" style="font-variant-numeric: tabular-nums;">
                                    {{ $publication_quality['scopus_q3'] }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2 border rounded text-center bg-body-tertiary">
                                <div class="small fw-bold text-secondary">Scopus Q4</div>
                                <div class="h5 mb-0 fw-bold font-monospace text-secondary" style="font-variant-numeric: tabular-nums;">
                                    {{ $publication_quality['scopus_q4'] }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.84rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>ระดับมาตรฐานวารสารวิชาการ</th>
                                    <th class="text-center" style="width: 70px;">จำนวน</th>
                                    <th class="text-end" style="width: 75px;">สัดส่วน %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totPub = $kpis['total_published'] > 0 ? $kpis['total_published'] : 1;
                                    $scopusPct = round(($publication_quality['total_scopus'] / $totPub) * 100, 1);
                                    $tci1Pct = round(($publication_quality['tci_tier1'] / $totPub) * 100, 1);
                                    $tci2Pct = round(($publication_quality['tci_tier2'] / $totPub) * 100, 1);
                                    $intConfPct = round(($publication_quality['international_conf'] / $totPub) * 100, 1);
                                    $otherPubsSum = $publication_quality['national_conf'] + $publication_quality['other_pubs'];
                                    $otherPubsPct = round(($otherPubsSum / $totPub) * 100, 1);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5">
                                            <i class="bi bi-star-fill text-warning flex-shrink-0"></i>
                                            <span class="fw-semibold text-body">รวมฐานข้อมูลสากล Scopus (Q1-Q4)</span>
                                        </div>
                                        <div class="progress mt-1 ms-3" style="height: 3px; max-width: 140px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $scopusPct }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-primary" style="font-variant-numeric: tabular-nums;">
                                        {{ $publication_quality['total_scopus'] }}
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-variant-numeric: tabular-nums;">
                                            {{ $scopusPct }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5">
                                            <i class="bi bi-check2-circle text-success flex-shrink-0"></i>
                                            <span class="fw-medium text-body">วารสารระดับชาติ TCI กลุ่มที่ 1</span>
                                        </div>
                                        <div class="progress mt-1 ms-3" style="height: 3px; max-width: 140px;">
                                            <div class="progress-bar bg-success" style="width: {{ $tci1Pct }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace" style="font-variant-numeric: tabular-nums;">
                                        {{ $publication_quality['tci_tier1'] }}
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-variant-numeric: tabular-nums;">
                                            {{ $tci1Pct }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5">
                                            <i class="bi bi-check2 text-secondary flex-shrink-0"></i>
                                            <span class="fw-medium text-body">วารสารระดับชาติ TCI กลุ่มที่ 2</span>
                                        </div>
                                        <div class="progress mt-1 ms-3" style="height: 3px; max-width: 140px;">
                                            <div class="progress-bar bg-secondary" style="width: {{ $tci2Pct }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace" style="font-variant-numeric: tabular-nums;">
                                        {{ $publication_quality['tci_tier2'] }}
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace" style="font-variant-numeric: tabular-nums;">
                                            {{ $tci2Pct }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5">
                                            <i class="bi bi-globe2 text-info flex-shrink-0"></i>
                                            <span class="fw-medium text-body">รายงานสืบเนื่องการประชุมนานาชาติ</span>
                                        </div>
                                        <div class="progress mt-1 ms-3" style="height: 3px; max-width: 140px;">
                                            <div class="progress-bar bg-info" style="width: {{ $intConfPct }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace" style="font-variant-numeric: tabular-nums;">
                                        {{ $publication_quality['international_conf'] }}
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace" style="font-variant-numeric: tabular-nums;">
                                            {{ $intConfPct }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5">
                                            <i class="bi bi-people text-secondary flex-shrink-0"></i>
                                            <span class="fw-medium text-body">การประชุมระดับชาติ / อื่นๆ</span>
                                        </div>
                                        <div class="progress mt-1 ms-3" style="height: 3px; max-width: 140px;">
                                            <div class="progress-bar bg-secondary-subtle" style="width: {{ $otherPubsPct }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace" style="font-variant-numeric: tabular-nums;">
                                        {{ $otherPubsSum }}
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace" style="font-variant-numeric: tabular-nums;">
                                            {{ $otherPubsPct }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Faculty Breakdown & Utilization 4 Pillars -->
        <div class="row g-3 g-md-4 mb-4 page-break-inside-avoid">
            <!-- Faculty Performance -->
            <div class="col-12 col-lg-7">
                <div class="border rounded-3 p-3 h-100">
                    <h4 class="h6 fw-bold text-body-emphasis mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span><i class="bi bi-buildings-fill text-primary me-1.5"></i> ผลงานและงบประมาณสะสมรายคณะ / หน่วยงาน</span>
                        <span class="badge bg-body-secondary text-body-secondary small">8 คณะ &amp; สถาบัน</span>
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>สังกัด / คณะ</th>
                                    <th class="text-center" style="width: 70px;">โครงการ</th>
                                    <th class="text-end" style="width: 110px;">งบประมาณ (บาท)</th>
                                    <th class="text-end" style="width: 60px;">สัดส่วน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($budget_by_faculty as $bf)
                                    <tr>
                                        <td>
                                            <div class="text-truncate fw-medium text-body" style="max-width: 250px;" title="{{ $bf['name'] }}">
                                                {{ $bf['name'] }}
                                            </div>
                                            <div class="progress mt-1" style="height: 3px; max-width: 140px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $bf['percentage'] }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="text-center font-monospace" style="font-variant-numeric: tabular-nums;">{{ number_format($bf['count']) }}</td>
                                        <td class="text-end font-monospace text-success fw-semibold" style="font-variant-numeric: tabular-nums;">{{ number_format($bf['budget']) }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace" style="font-variant-numeric: tabular-nums;">
                                                {{ $bf['percentage'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-body-secondary py-3">ไม่พบข้อมูลคณะ</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Utilization 4 Pillars -->
            <div class="col-12 col-lg-5">
                <div class="border rounded-3 p-3 h-100">
                    <h4 class="h6 fw-bold text-body-emphasis mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span><i class="bi bi-compass-fill text-danger me-1.5"></i> มิติการนำผลงานวิจัยไปใช้ประโยชน์ 4 ด้าน</span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Impact</span>
                    </h4>
                    <div class="space-y-3">
                        <!-- Commercial -->
                        <div class="p-2.5 rounded border bg-body-tertiary mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold text-primary"><i class="bi bi-shop me-1"></i> เชิงพาณิชย์ (Commercial)</span>
                                <span class="fw-bold font-monospace" style="font-variant-numeric: tabular-nums;">{{ number_format($utilization_pillars['commercial']) }} เรื่อง</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-primary" style="width: {{ $kpis['total_utilizations'] > 0 ? ($utilization_pillars['commercial'] / $kpis['total_utilizations']) * 100 : 0 }}%;"></div>
                            </div>
                        </div>

                        <!-- Policy -->
                        <div class="p-2.5 rounded border bg-body-tertiary mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold text-info"><i class="bi bi-file-earmark-ruled me-1"></i> เชิงนโยบาย (Policy)</span>
                                <span class="fw-bold font-monospace" style="font-variant-numeric: tabular-nums;">{{ number_format($utilization_pillars['policy']) }} เรื่อง</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-info" style="width: {{ $kpis['total_utilizations'] > 0 ? ($utilization_pillars['policy'] / $kpis['total_utilizations']) * 100 : 0 }}%;"></div>
                            </div>
                        </div>

                        <!-- Public -->
                        <div class="p-2.5 rounded border bg-body-tertiary mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold text-success"><i class="bi bi-globe me-1"></i> เชิงสาธารณะ (Public)</span>
                                <span class="fw-bold font-monospace" style="font-variant-numeric: tabular-nums;">{{ number_format($utilization_pillars['public']) }} เรื่อง</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: {{ $kpis['total_utilizations'] > 0 ? ($utilization_pillars['public'] / $kpis['total_utilizations']) * 100 : 0 }}%;"></div>
                            </div>
                        </div>

                        <!-- Community & Area -->
                        <div class="p-2.5 rounded border bg-body-tertiary">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold text-danger"><i class="bi bi-geo-alt-fill me-1"></i> เชิงชุมชน/พื้นที่ (Area-Based)</span>
                                <span class="fw-bold font-monospace" style="font-variant-numeric: tabular-nums;">{{ number_format($utilization_pillars['community']) }} เรื่อง</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-danger" style="width: {{ $kpis['total_utilizations'] > 0 ? ($utilization_pillars['community'] / $kpis['total_utilizations']) * 100 : 0 }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Top Research Highlights -->
        <div class="mb-2 page-break-inside-avoid">
            <div class="border rounded-3 p-3">
                <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                    <h4 class="h6 fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-1.5">
                        <i class="bi bi-trophy-fill text-warning"></i> โครงการวิจัยที่ได้รับงบประมาณสูงสุด (Top Funded Research Projects)
                    </h4>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Top 5</span>
                </div>

                <div class="d-block d-md-none text-body-secondary small mb-2" style="font-size: 0.72rem;">
                    <i class="bi bi-arrows-expand me-1"></i> เลื่อนตารางแนวนอนเพื่อดูคอลัมน์ทั้งหมด
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0" style="font-size: 0.82rem; min-width: 620px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th>ชื่อโครงการวิจัย</th>
                                <th style="width: 150px;">หัวหน้าโครงการ</th>
                                <th style="width: 140px;">สังกัดคณะ</th>
                                <th style="width: 75px;" class="text-center">ปี พ.ศ.</th>
                                <th style="width: 110px;" class="text-end">งบประมาณ (บาท)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($top_projects as $idx => $p)
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-body-secondary">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold text-body text-truncate" style="max-width: 380px;" title="{{ $p->pro_nameTH ?? $p->pro_nameEN }}">
                                            {{ $p->pro_nameTH ?? $p->pro_nameEN }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $leaderWork = $p->rdbProjectWorks ? $p->rdbProjectWorks->whereIn('position_id', [1, 2])->first() ?? $p->rdbProjectWorks->first() : null;
                                            $leaderName = $leaderWork && $leaderWork->researcher 
                                                ? ($leaderWork->researcher->full_name ?? ($leaderWork->researcher->researcher_fname . ' ' . $leaderWork->researcher->researcher_lname))
                                                : '-';
                                        @endphp
                                        <div class="text-truncate text-secondary" style="max-width: 140px;" title="{{ $leaderName }}">
                                            {{ $leaderName }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate text-secondary" style="max-width: 130px;" title="{{ $p->department->department_nameTH ?? '-' }}">
                                            {{ $p->department->department_nameTH ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace">{{ $p->year->year_name ?? '-' }}</td>
                                    <td class="text-end font-monospace fw-bold text-success" style="font-variant-numeric: tabular-nums;">
                                        {{ number_format($p->pro_budget) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-body-secondary py-3">ไม่พบข้อมูลโครงการวิจัย</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Report Sign-off / Signature Footer (Print only) -->
        <div class="report-signoff mt-5 pt-4 border-top d-none d-print-block">
            <div class="row text-center">
                <div class="col-6">
                    <p class="small text-secondary mb-5">ผู้จัดทำและประมวลผลข้อมูล</p>
                    <p class="fw-bold mb-0">........................................................................</p>
                    <p class="small text-secondary mt-1">( เจ้าหน้าที่วิเคราะห์นโยบายและแผน สถาบันวิจัยฯ )</p>
                </div>
                <div class="col-6">
                    <p class="small text-secondary mb-5">ผู้รับรองรายงานสารสนเทศ</p>
                    <p class="fw-bold mb-0">........................................................................</p>
                    <p class="small text-secondary mt-1">( ผู้อำนวยการสถาบันวิจัยและพัฒนา มรภ.เพชรบูรณ์ )</p>
                </div>
            </div>
            <div class="text-center text-secondary small mt-4 pt-3 border-top" style="font-size: 0.72rem;">
                รายงานสารสนเทศวิจัยฉบับนี้สร้างโดยระบบ PCRU Research Database (PCRU RDB) · มหาวิทยาลัยราชภัฏเพชรบูรณ์
            </div>
        </div>
    </div>
</div>

<style>
/* Printable Editorial Sheet Styles */
.printable-sheet {
    max-width: 1140px;
    margin: 0 auto;
    font-family: 'Sarabun', 'Plus Jakarta Sans', sans-serif;
}

.report-header .report-logo {
    height: 60px;
    width: auto;
}

.kpi-stat-number {
    font-size: clamp(1.15rem, 2.2vw, 1.75rem);
}

@media (max-width: 767.98px) {
    .printable-sheet {
        padding: 0.85rem !important;
        border-radius: 0.5rem !important;
    }
    .report-header .report-logo {
        height: 48px !important;
    }
    .kpi-stat-number {
        font-size: 1.15rem !important;
    }
}

@media print {
    /* Hide everything outside printable sheet */
    body {
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 11.5pt;
    }
    .d-print-none,
    nav,
    header,
    footer,
    #rdbDesktopSidebarTrigger,
    .rdb-backend-sidebar,
    .rdb-backend-main > .container-fluid > .mb-4:first-child,
    .btn,
    .form-select {
        display: none !important;
    }

    .executive-report-wrapper,
    .rdb-backend-main,
    .rdb-backend-wrapper {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .printable-sheet {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }

    .page-break-inside-avoid {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .table {
        font-size: 9.5pt !important;
    }

    @page {
        size: A4 portrait;
        margin: 12mm 15mm 15mm 15mm;
    }
}
</style>
@endsection
