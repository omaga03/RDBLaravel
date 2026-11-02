@extends('layouts.app')

@section('title', 'แดชบอร์ดสารสนเทศเพื่อการประกันคุณภาพ (QA) - มหาวิทยาลัยราชภัฏเพชรบูรณ์')

@section('content')
<style>
.qa-branch-score {
    color: #0284c7 !important;
}
[data-bs-theme="dark"] .qa-branch-score {
    color: #38bdf8 !important;
}
.qa-tables-collapse {
    transition: all 0.25s ease-in-out;
}
.toggle-table-icon {
    transition: transform 0.2s ease;
}
[aria-expanded="true"] .toggle-table-icon {
    transform: rotate(180deg);
}
</style>
<div class="container-fluid py-4 px-md-4">
    @include('frontend.dashboard._subnav', [
        'pageTitle' => 'สารสนเทศเพื่อการประกันคุณภาพ (Quality Assurance & Accreditations)',
        'pageIcon' => 'bi bi-shield-check'
    ])

    <!-- Scope Switcher Quick Bar: เฉพาะคณะวิชาการ vs ทั้งหมด -->
    <div class="card border shadow-xs mb-4 bg-body-tertiary" style="border-radius: var(--rdb-radius-md) !important;">
        <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="text-body-secondary small fw-bold text-uppercase d-inline-flex align-items-center">
                    <i class="bi bi-diagram-3-fill text-primary me-1.5"></i> ขอบเขตการประเมิน QA:
                </span>
                <div class="btn-group btn-group-sm shadow-xs" role="group" aria-label="ขอบเขตการคำนวณ">
                    <a href="{{ request()->fullUrlWithQuery(['scope' => 'faculty']) }}" 
                       class="btn {{ ($scope ?? 'faculty') === 'faculty' ? 'btn-primary active' : 'btn-outline-primary' }} fw-semibold px-3">
                        <i class="bi bi-mortarboard-fill me-1"></i> เฉพาะคณะวิชาการ (6 คณะ)
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['scope' => 'all']) }}" 
                       class="btn {{ ($scope ?? 'faculty') === 'all' ? 'btn-primary active' : 'btn-outline-primary' }} fw-semibold px-3">
                        <i class="bi bi-buildings-fill me-1"></i> ทุกหน่วยงาน (รวมส่วนกลาง/สำนัก)
                    </a>
                </div>
            </div>
            <div>
                @if(($scope ?? 'faculty') === 'faculty')
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1.5 px-3 rounded-pill">
                        <i class="bi bi-shield-check me-1"></i> โหมดปัจจุบัน: คำนวณเฉพาะ 6 คณะวิชาการตามเกณฑ์ประกันคุณภาพ สกอ./อว.
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-body border py-1.5 px-3 rounded-pill">
                        <i class="bi bi-globe me-1"></i> โหมดปัจจุบัน: คำนวณครอบคลุมทุกหน่วยงานในมหาวิทยาลัย (15 หน่วยงาน)
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards with QA Benchmark Scores (เต็ม 5.00 คะแนน) -->
    <div class="row g-3 mb-4">
        <!-- 1. QA Publications Weighted Score & Score 5 -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm text-white" 
                 style="background: var(--rdb-gradient-primary); border-radius: var(--rdb-radius-md) !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-light-subtle bg-opacity-25 text-white border-0">
                            <i class="bi bi-award-fill me-1"></i> คะแนนถ่วงน้ำหนักผลงานตีพิมพ์
                        </span>
                        <span class="badge bg-warning text-dark fw-bold px-2 py-1 shadow-xs" title="คะแนนประเมินตัวบ่งชี้ 2.3 สกอ./อว. (เต็ม 5.00)">
                            ประเมิน: {{ number_format($avgPubQaScore5, 2) }} / 5.00
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1 text-white text-nowrap">{{ number_format($totalWeightedPubScore, 2) }}</h3>
                    <small class="text-white-50 d-block">จากผลงานตีพิมพ์ทั้งหมด {{ number_format($totalPubItems) }} เรื่อง (เทียบเกณฑ์กลุ่มสาขา)</small>
                </div>
            </div>
        </div>

        <!-- 2. Avg Research Budget per Personnel & Score 5 -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3.5">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-body-secondary small fw-bold text-uppercase">
                            <i class="bi bi-cash-stack text-success me-1"></i> งบวิจัยเฉลี่ย / บุคลากร
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-0.5" title="คะแนนประเมินตัวบ่งชี้ 2.2 สกอ./อว. (เต็ม 5.00)">
                            ประเมิน: {{ number_format($avgProjectQaScore5, 2) }} / 5.00
                        </span>
                    </div>
                    <h3 class="fw-bold mb-0 text-success">฿{{ number_format($avgBudgetPerPersonnel, 0) }}</h3>
                    <small class="text-body-secondary">งบรวม ฿{{ number_format($totalBudget, 0) }} (บุคลากร {{ number_format($totalPersonnel) }} คน)</small>
                </div>
            </div>
        </div>

        <!-- 3. QA Certified Utilizations -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3.5">
                    <span class="text-danger small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-patch-check-fill text-danger me-1"></i> ใช้ประโยชน์ผ่านเกณฑ์ QA
                    </span>
                    <h3 class="fw-bold mb-0 text-danger">{{ number_format($totalUtilCertified) }}</h3>
                    <small class="text-body-secondary">คิดเป็น {{ $totalUtil > 0 ? number_format(($totalUtilCertified / $totalUtil) * 100, 1) : 0 }}% ของการใช้ประโยชน์ทั้งหมด</small>
                </div>
            </div>
        </div>

        <!-- 4. First-time Grantee Researchers -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm">
                <div class="card-body p-3.5">
                    <span class="text-primary small fw-bold text-uppercase d-block mb-1">
                        <i class="bi bi-person-plus-fill text-primary me-1"></i> นักวิจัยรับทุนครั้งแรก
                    </span>
                    <h3 class="fw-bold mb-0 text-primary">{{ number_format($totalFirstResearchers) }} ท่าน</h3>
                    <small class="text-body-secondary">อาจารย์และนักวิจัยรุ่นใหม่ในระบบ</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs for QA Dimensions -->
    <div class="card border shadow-sm mb-4" style="border-radius: var(--rdb-radius-md) !important;">
        <div class="card-header bg-body-tertiary border-bottom px-3 py-2.5">
            <ul class="nav nav-tabs card-header-tabs" id="qaTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($activeTab ?? 'pub') === 'pub' ? 'active' : '' }} fw-semibold text-body" id="qa-pub-tab" data-bs-toggle="tab" data-bs-target="#qa-pub" type="button" role="tab">
                        <i class="bi bi-journal-richtext text-primary me-1.5"></i> 1. ผลงานตีพิมพ์ (แยกตามกลุ่มสาขา & คณะ)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($activeTab ?? 'pub') === 'project' ? 'active' : '' }} fw-semibold text-body" id="qa-project-tab" data-bs-toggle="tab" data-bs-target="#qa-project" type="button" role="tab">
                        <i class="bi bi-folder2-open text-success me-1.5"></i> 2. สัดส่วนโครงการและงบวิจัยต่อบุคลากร (แยกตามกลุ่มสาขา & คณะ)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($activeTab ?? 'pub') === 'util' ? 'active' : '' }} fw-semibold text-body" id="qa-util-tab" data-bs-toggle="tab" data-bs-target="#qa-util" type="button" role="tab">
                        <i class="bi bi-rocket-takeoff-fill text-danger me-1.5"></i> 3. ผลงานวิจัยนำไปใช้ประโยชน์ที่ผ่านเกณฑ์ QA
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($activeTab ?? 'pub') === 'first' ? 'active' : '' }} fw-semibold text-body" id="qa-first-tab" data-bs-toggle="tab" data-bs-target="#qa-first" type="button" role="tab">
                        <i class="bi bi-person-check-fill text-info me-1.5"></i> 4. นักวิจัยรุ่นใหม่ (รับทุนวิจัยครั้งแรก)
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="qaTabsContent">

                {{-- ========================================================= --}}
                {{-- Tab 1: QA Publications Weighted Score & By Branch          --}}
                {{-- ========================================================= --}}
                <div class="tab-pane fade {{ ($activeTab ?? 'pub') === 'pub' ? 'show active' : '' }} p-4" id="qa-pub" role="tabpanel">
                    
                    <!-- Tab 1 Charts Section: แสดงเป็นหลักด้านบน -->
                    <div class="mb-4">
                        <div class="row g-3 mb-3">
                            <!-- 1.1 คะแนนประเมิน QA ผลงานตีพิมพ์ระดับคณะ (เต็ม 5.00) (ตาราง 1.2) -->
                            <div class="col-lg-7">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-award text-primary me-1"></i>คะแนนประเมิน QA ผลงานตีพิมพ์ระดับคณะ (เต็ม 5.00)
                                            </span>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small" style="font-size: 0.72rem;">
                                                เส้นประสีแดง = เกณฑ์เป้าหมาย 5.00
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 250px;">
                                            <canvas id="chartPubFacultyScore"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 1.2 สัดส่วนคะแนนถ่วงน้ำหนักตาม 3 กลุ่มสาขาวิชา (ตาราง 1.1) -->
                            <div class="col-lg-5">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-pie-chart text-info me-1"></i>สัดส่วนคะแนนถ่วงน้ำหนักตาม 3 กลุ่มสาขาวิชา
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 250px;">
                                            <canvas id="chartPubBranchScore"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- 1.3 การแจกแจงผลงานตามค่าน้ำหนักคุณภาพ 0.20 - 1.00 ระดับคณะ (ตาราง 1.3) -->
                            <div class="col-lg-7">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-bar-chart-steps text-success me-1"></i>จำนวนผลงานตีพิมพ์แจกแจงตามค่าน้ำหนักคุณภาพ (0.20 – 1.00) ระดับคณะ
                                            </span>
                                            <span class="badge bg-secondary-subtle text-body border small" style="font-size: 0.72rem;">
                                                ก.พ.อ. / สกอ.
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 250px;">
                                            <canvas id="chartPubWeightStack"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 1.4 ร้อยละคะแนน/คน เทียบเกณฑ์ฐานร้อยละ (%) แยกตาม 3 กลุ่มสาขา (ตาราง 1.1) -->
                            <div class="col-lg-5">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-bar-chart text-warning me-1"></i>ร้อยละคะแนน/คน เทียบเกณฑ์ฐานร้อยละ (%)
                                            </span>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle small" style="font-size: 0.72rem;">
                                                เกณฑ์ สกอ./อว. 2.3
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 250px;">
                                            <canvas id="chartPubBranchCompare"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle Button for Tables: Tab 1 -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 mb-3 border-top">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-primary shadow-xs d-inline-flex align-items-center gap-1.5" 
                                    type="button" data-bs-toggle="collapse" data-bs-target="#qaPubTablesCollapse" 
                                    aria-expanded="false" aria-controls="qaPubTablesCollapse" id="btnTogglePubTables">
                                <i class="bi bi-table text-primary"></i>
                                <span class="toggle-table-text">แสดงตารางข้อมูลรายละเอียด (3 ตาราง)</span>
                                <i class="bi bi-chevron-down ms-1 toggle-table-icon"></i>
                            </button>
                            <span class="text-body-secondary small d-none d-md-inline">
                                <i class="bi bi-info-circle me-1"></i>(ตารางถูกซ่อนไว้เป็นค่าเริ่มต้นเพื่อความสบายตา คลิกเพื่อเปิดดูรายละเอียดตัวเลขและพิมพ์รายงาน)
                            </span>
                        </div>
                    </div>

                    <!-- Collapsible Tables: Tab 1 (ซ่อนไว้เป็นค่าเริ่มต้น) -->
                    <div class="collapse qa-tables-collapse" id="qaPubTablesCollapse">

                    <!-- 1.1 Branch QA Benchmark (ตัวบ่งชี้ 2.3 สกอ./อว.) -->
                    <div class="card border border-primary-subtle shadow-xs mb-4 bg-primary-subtle bg-opacity-10">
                        <div class="card-header bg-transparent border-primary-subtle d-flex justify-content-between align-items-center py-2.5 px-3">
                            <span class="fw-bold text-primary">
                                <i class="bi bi-calculator me-1.5"></i>เกณฑ์มาตรฐาน สกอ./อว. (ตัวบ่งชี้ 2.3): การคำนวณคะแนนประเมินจำแนกตาม "กลุ่มสาขาวิชา"
                            </span>
                            <span class="badge bg-primary text-white px-2.5 py-1">
                                คะแนนประเมินเฉลี่ยภาพรวม: {{ number_format($avgPubQaScore5, 2) }} / 5.00 คะแนน
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div class="alert alert-light border small text-body-secondary mb-3 py-2 px-3">
                                <i class="bi bi-info-circle-fill text-primary me-1"></i>
                                <strong>สูตรการคำนวณตามเกณฑ์ สกอ./อว.:</strong> 
                                <code>คะแนนประเมิน (เต็ม 5.00) = ( (คะแนนถ่วงน้ำหนักรวม × 100 ÷ จำนวนบุคลากร) × 5 ) ÷ เกณฑ์ร้อยละฐานของกลุ่มสาขา</code> 
                                (เกณฑ์ร้อยละฐาน: <strong>สาขาวิทยาศาสตร์ฯ 30%</strong>, <strong>สาขามนุษยศาสตร์และสังคมศาสตร์ 20%</strong>, <strong>สาขาวิทยาศาสตร์สุขภาพ 30%</strong>)
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle text-center mb-0 bg-body" style="font-size: 0.9rem;">
                                    <thead class="table-primary">
                                        <tr>
                                            <th class="text-start" style="min-width: 200px;">กลุ่มสาขาวิชา (Academic Field)</th>
                                            <th style="width: 120px;">จำนวนบุคลากร (คน)</th>
                                            <th style="width: 120px;">ผลงานทั้งหมด (เรื่อง)</th>
                                            <th style="width: 130px;">คะแนนถ่วงน้ำหนัก</th>
                                            <th style="width: 130px;">ร้อยละคะแนน/คน</th>
                                            <th style="width: 130px;" class="bg-warning-subtle text-warning-emphasis">เกณฑ์ฐานร้อยละ (%)</th>
                                            <th style="width: 140px;" class="bg-success-subtle text-success fw-bold">คะแนนประเมิน (เต็ม 5)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $bSumPer = 0; $bSumPub = 0; $bSumWeight = 0;
                                        @endphp
                                        @foreach($qaPubBranchTable as $bRow)
                                            @php
                                                $bSumPer += $bRow['per_num'];
                                                $bSumPub += $bRow['total_pubs'];
                                                $bSumWeight += $bRow['weighted_score'];
                                            @endphp
                                            <tr>
                                                <td class="text-start fw-semibold text-body">
                                                    @if($bRow['branch_id'] == 1)
                                                        <i class="bi bi-cpu text-info me-1.5"></i>
                                                    @elseif($bRow['branch_id'] == 2)
                                                        <i class="bi bi-book text-warning me-1.5"></i>
                                                    @else
                                                        <i class="bi bi-heart-pulse text-danger me-1.5"></i>
                                                    @endif
                                                    {{ $bRow['branch_name'] }}
                                                </td>
                                                <td class="tabular-nums">{{ number_format($bRow['per_num'], 1) }}</td>
                                                <td class="tabular-nums">{{ number_format($bRow['total_pubs']) }}</td>
                                                <td class="tabular-nums fw-bold text-primary">{{ number_format($bRow['weighted_score'], 2) }}</td>
                                                <td class="tabular-nums">{{ number_format($bRow['score_pct'], 2) }}%</td>
                                                <td class="tabular-nums bg-warning-subtle fw-semibold">{{ number_format($bRow['bench_percent']) }}%</td>
                                                <td class="tabular-nums fw-bold text-success fs-6 bg-success-subtle">
                                                    {{ number_format($bRow['qa_score_5'], 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-group-divider fw-bold bg-body-tertiary">
                                        <tr>
                                            <td class="text-start">รวม / ค่าเฉลี่ยระดับสถาบัน</td>
                                            <td class="tabular-nums">{{ number_format($bSumPer, 1) }}</td>
                                            <td class="tabular-nums">{{ number_format($bSumPub) }}</td>
                                            <td class="tabular-nums text-primary">{{ number_format($bSumWeight, 2) }}</td>
                                            <td class="tabular-nums">{{ $bSumPer > 0 ? number_format(($bSumWeight / $bSumPer) * 100, 2) : 0 }}%</td>
                                            <td class="tabular-nums bg-warning-subtle">-</td>
                                            <td class="tabular-nums text-success fs-6 bg-success-subtle">{{ number_format($avgPubQaScore5, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 1.2 Faculty and Branch QA Detailed Table (จัดเรียงเป็นกรุ๊ปคณะ และแสดงคะแนนภาพรวมระดับคณะ เต็ม 5) -->
                    <div class="card border shadow-xs mb-4">
                        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center flex-wrap gap-2 py-2.5 px-3">
                            <div>
                                <span class="fw-bold text-body">
                                    <i class="bi bi-diagram-2-fill text-primary me-1.5"></i>รายละเอียดผลงานตีพิมพ์แยกตาม "คณะและกลุ่มสาขาวิชา" (เกณฑ์ตัวบ่งชี้ 2.3)
                                </span>
                                <small class="text-body-secondary d-block">
                                    จัดกลุ่มข้อมูลตามสังกัดคณะ แสดงแจกแจงตามกลุ่มสาขาวิชา พร้อมคะแนนประเมินระดับสาขา และ<strong>คะแนนประเมินภาพรวมระดับคณะ (เต็ม 5.00 คะแนน)</strong>
                                </small>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                                รวม {{ $qaPubFacultyGroups->count() }} คณะ / หน่วยงาน
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm align-middle text-center mb-0" style="font-size: 0.9rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start" style="min-width: 170px;">คณะ / หน่วยงาน</th>
                                            <th class="text-start" style="min-width: 170px;">กลุ่มสาขาวิชา</th>
                                            <th style="width: 95px;">บุคลากร (คน)</th>
                                            <th style="width: 85px;">ผลงาน (เรื่อง)</th>
                                            <th style="width: 110px;" class="text-primary">คะแนนถ่วงน้ำหนัก</th>
                                            <th style="width: 105px;" class="text-primary">ร้อยละคะแนน/คน</th>
                                            <th style="width: 105px;" class="bg-warning-subtle text-warning-emphasis">เกณฑ์ร้อยละฐาน</th>
                                            <th style="width: 110px;" class="bg-info-subtle text-info-emphasis fw-bold">คะแนนสาขา (เต็ม 5)</th>
                                            <th style="width: 135px;" class="bg-success-subtle text-success fw-bold">คะแนนภาพรวมคณะ (เต็ม 5)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($qaPubFacultyGroups as $group)
                                            @php
                                                $branchCount = count($group['branches']);
                                            @endphp
                                            @if($branchCount === 1)
                                                @php $b = $group['branches']->first(); @endphp
                                                <tr>
                                                    <td class="text-start fw-bold text-body">
                                                        <i class="bi bi-buildings text-primary me-1"></i>{{ $group['department_nameTH'] }}
                                                    </td>
                                                    <td class="text-start">
                                                        @if($b['branch_id'] == 1)
                                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><i class="bi bi-cpu me-1"></i>{{ $b['branch_name'] }}</span>
                                                        @elseif($b['branch_id'] == 2)
                                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-book me-1"></i>{{ $b['branch_name'] }}</span>
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle"><i class="bi bi-heart-pulse me-1"></i>{{ $b['branch_name'] }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="tabular-nums">{{ number_format($b['per_num'], 1) }}</td>
                                                    <td class="tabular-nums fw-medium">{{ number_format($b['total_pubs']) }}</td>
                                                    <td class="tabular-nums fw-bold text-primary">{{ number_format($b['weighted_score'], 2) }}</td>
                                                    <td class="tabular-nums fw-semibold">{{ number_format($b['score_pct'], 2) }}%</td>
                                                    <td class="tabular-nums bg-warning-subtle fw-semibold">{{ number_format($b['bench_percent']) }}%</td>
                                                    <td class="tabular-nums bg-info-subtle fw-bold text-info-emphasis qa-branch-score fs-6">{{ number_format($b['qa_score_5'], 2) }}</td>
                                                    <td class="tabular-nums bg-success-subtle fw-bold text-success fs-6">
                                                        <span class="badge bg-success text-white px-2.5 py-1.5 shadow-xs">{{ number_format($group['faculty_qa_score_5'], 2) }}</span>
                                                    </td>
                                                </tr>
                                            @else
                                                @foreach($group['branches'] as $idx => $b)
                                                    <tr>
                                                        @if($idx === 0)
                                                            <td rowspan="{{ $branchCount + 1 }}" class="text-start fw-bold text-body align-middle bg-body-tertiary border-end">
                                                                <i class="bi bi-buildings-fill text-primary me-1"></i>{{ $group['department_nameTH'] }}
                                                                <span class="badge bg-secondary-subtle text-body border d-block mt-1 w-auto small">มี {{ $branchCount }} กลุ่มสาขา</span>
                                                            </td>
                                                        @endif
                                                        <td class="text-start">
                                                            @if($b['branch_id'] == 1)
                                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><i class="bi bi-cpu me-1"></i>{{ $b['branch_name'] }}</span>
                                                            @elseif($b['branch_id'] == 2)
                                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-book me-1"></i>{{ $b['branch_name'] }}</span>
                                                            @else
                                                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle"><i class="bi bi-heart-pulse me-1"></i>{{ $b['branch_name'] }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="tabular-nums">{{ number_format($b['per_num'], 1) }}</td>
                                                        <td class="tabular-nums fw-medium">{{ number_format($b['total_pubs']) }}</td>
                                                        <td class="tabular-nums fw-bold text-primary">{{ number_format($b['weighted_score'], 2) }}</td>
                                                        <td class="tabular-nums fw-semibold">{{ number_format($b['score_pct'], 2) }}%</td>
                                                        <td class="tabular-nums bg-warning-subtle fw-semibold">{{ number_format($b['bench_percent']) }}%</td>
                                                        <td class="tabular-nums bg-info-subtle fw-bold text-info-emphasis qa-branch-score fs-6">{{ number_format($b['qa_score_5'], 2) }}</td>
                                                        @if($idx === 0)
                                                            <td rowspan="{{ $branchCount + 1 }}" class="tabular-nums bg-success-subtle align-middle text-center border-start">
                                                                <span class="badge bg-success text-white fs-6 px-2.5 py-1.5 shadow-xs d-inline-block">{{ number_format($group['faculty_qa_score_5'], 2) }}</span>
                                                                <small class="text-success-emphasis d-block mt-1" style="font-size: 0.75rem;">(เฉลี่ยจากกลุ่มสาขา)</small>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                                <tr class="table-primary-subtle fw-bold border-bottom">
                                                    <td class="text-start text-primary"><i class="bi bi-check2-circle me-1"></i>สรุปภาพรวมคณะ</td>
                                                    <td class="tabular-nums text-primary">{{ number_format($group['total_personnel'], 1) }}</td>
                                                    <td class="tabular-nums text-primary">{{ number_format($group['total_pubs']) }}</td>
                                                    <td class="tabular-nums text-primary">{{ number_format($group['total_weighted_score'], 2) }}</td>
                                                    <td class="tabular-nums text-primary">{{ number_format($group['score_pct'], 2) }}%</td>
                                                    <td class="text-body-secondary">-</td>
                                                    <td class="text-body-secondary">-</td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="9" class="py-4 text-center text-body-secondary">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-group-divider fw-bold bg-body-tertiary">
                                        <tr>
                                            <td colspan="2" class="text-start">รวมทั้งหมด / ค่าเฉลี่ยระดับสถาบัน</td>
                                            <td class="tabular-nums">{{ number_format($qaPubFacultyGroups->sum('total_personnel'), 1) }}</td>
                                            <td class="tabular-nums">{{ number_format($qaPubFacultyGroups->sum('total_pubs')) }}</td>
                                            <td class="tabular-nums text-primary">{{ number_format($qaPubFacultyGroups->sum('total_weighted_score'), 2) }}</td>
                                            <td class="tabular-nums text-primary">
                                                @php $allPer = $qaPubFacultyGroups->sum('total_personnel'); @endphp
                                                {{ $allPer > 0 ? number_format(($qaPubFacultyGroups->sum('total_weighted_score') / $allPer) * 100, 2) : 0 }}%
                                            </td>
                                            <td class="tabular-nums bg-warning-subtle">-</td>
                                            <td class="tabular-nums bg-info-subtle text-info-emphasis">-</td>
                                            <td class="tabular-nums text-success fs-6 bg-success-subtle">
                                                <span class="badge bg-success text-white px-2.5 py-1.5 shadow-xs">{{ number_format($avgPubQaScore5, 2) }}</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 1.3 Faculty Detailed Table (ค่าน้ำหนัก 0.20 - 1.00) -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1 text-body">
                                <i class="bi bi-table text-primary me-2"></i>รายละเอียดผลงานตีพิมพ์แยกตามคณะและค่าน้ำหนัก (0.20 – 1.00)
                            </h5>
                            <p class="text-body-secondary small mb-0">
                                การแจกแจงผลงานตามค่าน้ำหนักคุณภาพ (ก.พ.อ. / สกอ.) และสัดส่วนคะแนนเทียบจำนวนบุคลากรสายวิชาการประจำคณะ
                            </p>
                        </div>
                        <div>
                            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-xs">
                                <i class="bi bi-printer me-1.5"></i> พิมพ์ตารางนี้
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0 text-center" style="font-size: 0.92rem;">
                            <thead class="table-light">
                                <tr>
                                    <th rowspan="2" class="align-middle text-start" style="min-width: 200px;">คณะ / หน่วยงาน</th>
                                    <th rowspan="2" class="align-middle" style="width: 100px;">จำนวนบุคลากร</th>
                                    <th colspan="5" class="text-center bg-primary-subtle border-primary-subtle">จำนวนผลงานตามค่าน้ำหนัก</th>
                                    <th rowspan="2" class="align-middle" style="width: 90px;">รวมผลงาน</th>
                                    <th rowspan="2" class="align-middle text-primary" style="width: 110px;">คะแนนถ่วงน้ำหนัก</th>
                                    <th rowspan="2" class="align-middle text-success" style="width: 120px;">ร้อยละคะแนน/คน</th>
                                </tr>
                                <tr>
                                    <th style="width: 70px;" class="bg-body-secondary">0.20</th>
                                    <th style="width: 70px;" class="bg-body-secondary">0.40</th>
                                    <th style="width: 70px;" class="bg-body-secondary">0.60</th>
                                    <th style="width: 70px;" class="bg-body-secondary">0.80</th>
                                    <th style="width: 70px;" class="bg-body-secondary">1.00</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sumPerNum = 0; $sumC020 = 0; $sumC040 = 0; $sumC060 = 0; $sumC080 = 0; $sumC100 = 0;
                                    $sumItems = 0; $sumWeighted = 0;
                                @endphp
                                @forelse($qaPubTable as $row)
                                    @php
                                        $sumPerNum += $row['per_num'];
                                        $sumC020 += $row['count_020'];
                                        $sumC040 += $row['count_040'];
                                        $sumC060 += $row['count_060'];
                                        $sumC080 += $row['count_080'];
                                        $sumC100 += $row['count_100'];
                                        $sumItems += $row['total_items'];
                                        $sumWeighted += $row['weighted_score'];
                                    @endphp
                                    <tr>
                                        <td class="text-start fw-medium text-body">{{ $row['department_nameTH'] }}</td>
                                        <td class="tabular-nums">{{ number_format($row['per_num']) }}</td>
                                        <td class="tabular-nums text-body-secondary">{{ number_format($row['count_020']) }}</td>
                                        <td class="tabular-nums text-body-secondary">{{ number_format($row['count_040']) }}</td>
                                        <td class="tabular-nums text-body-secondary">{{ number_format($row['count_060']) }}</td>
                                        <td class="tabular-nums text-body-secondary">{{ number_format($row['count_080']) }}</td>
                                        <td class="tabular-nums text-body-secondary">{{ number_format($row['count_100']) }}</td>
                                        <td class="tabular-nums fw-bold">{{ number_format($row['total_items']) }}</td>
                                        <td class="tabular-nums fw-bold text-primary">{{ number_format($row['weighted_score'], 2) }}</td>
                                        <td class="tabular-nums fw-bold text-success">{{ number_format($row['score_pct'], 2) }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="py-4 text-center text-body-secondary">ไม่พบข้อมูลสำหรับเงื่อนไขที่เลือก</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-group-divider fw-bold bg-body-tertiary">
                                <tr>
                                    <td class="text-start">รวมทั้งหมด</td>
                                    <td class="tabular-nums">{{ number_format($sumPerNum) }}</td>
                                    <td class="tabular-nums">{{ number_format($sumC020) }}</td>
                                    <td class="tabular-nums">{{ number_format($sumC040) }}</td>
                                    <td class="tabular-nums">{{ number_format($sumC060) }}</td>
                                    <td class="tabular-nums">{{ number_format($sumC080) }}</td>
                                    <td class="tabular-nums">{{ number_format($sumC100) }}</td>
                                    <td class="tabular-nums">{{ number_format($sumItems) }}</td>
                                    <td class="tabular-nums text-primary">{{ number_format($sumWeighted, 2) }}</td>
                                    <td class="tabular-nums text-success">{{ $sumPerNum > 0 ? number_format(($sumWeighted / $sumPerNum) * 100, 2) : 0 }}%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    </div> <!-- End #qaPubTablesCollapse -->
                </div>

                {{-- ========================================================= --}}
                {{-- Tab 2: QA Projects & Budget Ratio & By Branch              --}}
                {{-- ========================================================= --}}
                <div class="tab-pane fade {{ ($activeTab ?? 'pub') === 'project' ? 'show active' : '' }} p-4" id="qa-project" role="tabpanel">

                    <!-- Tab 2 Charts Section: แสดงเป็นหลักด้านบน -->
                    <div class="mb-4">
                        <div class="row g-3 mb-3">
                            <!-- 2.1 คะแนนประเมิน QA งบวิจัยต่อบุคลากรระดับคณะ (เต็ม 5.00) (ตาราง 2.2) -->
                            <div class="col-lg-7">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-award text-success me-1"></i>คะแนนประเมิน QA งบวิจัยต่อบุคลากรระดับคณะ (เต็ม 5.00)
                                            </span>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small" style="font-size: 0.72rem;">
                                                เส้นประสีแดง = เกณฑ์เป้าหมาย 5.00
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 250px;">
                                            <canvas id="chartProjectFacultyScore"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 2.2 งบเฉลี่ย/คน เทียบเกณฑ์มาตรฐาน สกอ./อว. (3 กลุ่มสาขา) (ตาราง 2.1) -->
                            <div class="col-lg-5">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-bar-chart-steps text-warning me-1"></i>งบเฉลี่ย/คน เทียบเกณฑ์มาตรฐาน สกอ./อว. (บาท)
                                            </span>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle small" style="font-size: 0.72rem;">
                                                ตัวบ่งชี้ 2.2
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 250px;">
                                            <canvas id="chartProjectBranchCompare"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- 2.3 งบประมาณวิจัยเฉลี่ย/คน (บาท) แยกตามคณะ (ตาราง 2.3) -->
                            <div class="col-lg-7">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-cash-stack text-success me-1"></i>งบประมาณวิจัยเฉลี่ยต่อบุคลากรระดับคณะ (บาท/คน)
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 250px;">
                                            <canvas id="chartProjectBudgetPerPerson"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 2.4 สัดส่วนโครงการวิจัยต่อบุคลากร (%) ระดับคณะ (ตาราง 2.3) -->
                            <div class="col-lg-5">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-percent text-primary me-1"></i>สัดส่วนจำนวนโครงการวิจัยต่อบุคลากร (%)
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 250px;">
                                            <canvas id="chartProjectRatioPct"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle Button for Tables: Tab 2 -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 mb-3 border-top">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-success shadow-xs d-inline-flex align-items-center gap-1.5" 
                                    type="button" data-bs-toggle="collapse" data-bs-target="#qaProjectTablesCollapse" 
                                    aria-expanded="false" aria-controls="qaProjectTablesCollapse" id="btnToggleProjectTables">
                                <i class="bi bi-table text-success"></i>
                                <span class="toggle-table-text">แสดงตารางข้อมูลรายละเอียด (3 ตาราง)</span>
                                <i class="bi bi-chevron-down ms-1 toggle-table-icon"></i>
                            </button>
                            <span class="text-body-secondary small d-none d-md-inline">
                                <i class="bi bi-info-circle me-1"></i>(ตารางถูกซ่อนไว้เป็นค่าเริ่มต้นเพื่อความสบายตา คลิกเพื่อเปิดดูรายละเอียดตัวเลขและพิมพ์รายงาน)
                            </span>
                        </div>
                    </div>

                    <!-- Collapsible Tables: Tab 2 (ซ่อนไว้เป็นค่าเริ่มต้น) -->
                    <div class="collapse qa-tables-collapse" id="qaProjectTablesCollapse">

                    <!-- 2.1 Branch QA Benchmark (ตัวบ่งชี้ 2.2 สกอ./อว.) -->
                    <div class="card border border-success-subtle shadow-xs mb-4 bg-success-subtle bg-opacity-10">
                        <div class="card-header bg-transparent border-success-subtle d-flex justify-content-between align-items-center py-2.5 px-3">
                            <span class="fw-bold text-success">
                                <i class="bi bi-cash-coin me-1.5"></i>เกณฑ์มาตรฐาน สกอ./อว. (ตัวบ่งชี้ 2.2): เงินสนับสนุนงานวิจัยต่อบุคลากรจำแนกตาม "กลุ่มสาขาวิชา"
                            </span>
                            <span class="badge bg-success text-white px-2.5 py-1">
                                คะแนนประเมินเฉลี่ยภาพรวม: {{ number_format($avgProjectQaScore5, 2) }} / 5.00 คะแนน
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div class="alert alert-light border small text-body-secondary mb-3 py-2 px-3">
                                <i class="bi bi-info-circle-fill text-success me-1"></i>
                                <strong>สูตรการคำนวณตามเกณฑ์ สกอ./อว.:</strong> 
                                <code>คะแนนประเมิน (เต็ม 5.00) = (งบวิจัยเฉลี่ยต่อคน ÷ เกณฑ์มาตรฐานของกลุ่มสาขา) × 5</code> 
                                (เกณฑ์มาตรฐาน: <strong>สาขาวิทยาศาสตร์ฯ 60,000 บาท/คน</strong>, <strong>สาขามนุษยศาสตร์และสังคมศาสตร์ 25,000 บาท/คน</strong>, <strong>สาขาวิทยาศาสตร์สุขภาพ 50,000 บาท/คน</strong>)
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle text-center mb-0 bg-body" style="font-size: 0.9rem;">
                                    <thead class="table-success">
                                        <tr>
                                            <th class="text-start" style="min-width: 200px;">กลุ่มสาขาวิชา (Academic Field)</th>
                                            <th style="width: 120px;">จำนวนบุคลากร (คน)</th>
                                            <th style="width: 120px;">จำนวนโครงการ (เรื่อง)</th>
                                            <th style="width: 170px;" class="text-end">งบวิจัยรวม (บาท)</th>
                                            <th style="width: 160px;" class="text-end">งบเฉลี่ย/คน (บาท)</th>
                                            <th style="width: 150px;" class="bg-warning-subtle text-warning-emphasis text-end">เกณฑ์มาตรฐาน (บาท/คน)</th>
                                            <th style="width: 140px;" class="bg-success-subtle text-success fw-bold">คะแนนประเมิน (เต็ม 5)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $pbSumPer = 0; $pbSumProj = 0; $pbSumBud = 0;
                                        @endphp
                                        @foreach($qaProjectBranchTable as $pbRow)
                                            @php
                                                $pbSumPer += $pbRow['per_num'];
                                                $pbSumProj += $pbRow['proj_count'];
                                                $pbSumBud += $pbRow['proj_budget'];
                                            @endphp
                                            <tr>
                                                <td class="text-start fw-semibold text-body">
                                                    @if($pbRow['branch_id'] == 1)
                                                        <i class="bi bi-cpu text-info me-1.5"></i>
                                                    @elseif($pbRow['branch_id'] == 2)
                                                        <i class="bi bi-book text-warning me-1.5"></i>
                                                    @else
                                                        <i class="bi bi-heart-pulse text-danger me-1.5"></i>
                                                    @endif
                                                    {{ $pbRow['branch_name'] }}
                                                </td>
                                                <td class="tabular-nums">{{ number_format($pbRow['per_num'], 1) }}</td>
                                                <td class="tabular-nums">{{ number_format($pbRow['proj_count']) }}</td>
                                                <td class="tabular-nums text-end">฿{{ number_format($pbRow['proj_budget'], 0) }}</td>
                                                <td class="tabular-nums text-end fw-semibold text-success">฿{{ number_format($pbRow['avg_budget_per_person'], 0) }}</td>
                                                <td class="tabular-nums text-end bg-warning-subtle fw-semibold">฿{{ number_format($pbRow['standard_budget'], 0) }}</td>
                                                <td class="tabular-nums fw-bold text-success fs-6 bg-success-subtle">
                                                    {{ number_format($pbRow['qa_score_5'], 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-group-divider fw-bold bg-body-tertiary">
                                        <tr>
                                            <td class="text-start">รวม / ค่าเฉลี่ยระดับสถาบัน</td>
                                            <td class="tabular-nums">{{ number_format($pbSumPer, 1) }}</td>
                                            <td class="tabular-nums">{{ number_format($pbSumProj) }}</td>
                                            <td class="tabular-nums text-end">฿{{ number_format($pbSumBud, 0) }}</td>
                                            <td class="tabular-nums text-end text-success">฿{{ $pbSumPer > 0 ? number_format($pbSumBud / $pbSumPer, 0) : 0 }}</td>
                                            <td class="tabular-nums text-end bg-warning-subtle">-</td>
                                            <td class="tabular-nums text-success fs-6 bg-success-subtle">{{ number_format($avgProjectQaScore5, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 2.2 Faculty and Branch QA Detailed Table (จัดเรียงเป็นกรุ๊ปคณะ และแสดงคะแนนภาพรวมระดับคณะ เต็ม 5) -->
                    <div class="card border shadow-xs mb-4">
                        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center flex-wrap gap-2 py-2.5 px-3">
                            <div>
                                <span class="fw-bold text-body">
                                    <i class="bi bi-diagram-2-fill text-success me-1.5"></i>รายละเอียดโครงการวิจัยและงบประมาณแยกตาม "คณะและกลุ่มสาขาวิชา" (เกณฑ์ตัวบ่งชี้ 2.2)
                                </span>
                                <small class="text-body-secondary d-block">
                                    จัดกลุ่มข้อมูลตามสังกัดคณะ แสดงแจกแจงตามกลุ่มสาขาวิชา พร้อมงบเฉลี่ยต่อคนเทียบเกณฑ์ และ<strong>คะแนนประเมินภาพรวมระดับคณะ (เต็ม 5.00 คะแนน)</strong>
                                </small>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                รวม {{ $qaProjectFacultyGroups->count() }} คณะ / หน่วยงาน
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm align-middle text-center mb-0" style="font-size: 0.9rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start" style="min-width: 170px;">คณะ / หน่วยงาน</th>
                                            <th class="text-start" style="min-width: 170px;">กลุ่มสาขาวิชา</th>
                                            <th style="width: 95px;">บุคลากร (คน)</th>
                                            <th style="width: 85px;">โครงการ (เรื่อง)</th>
                                            <th style="width: 135px;" class="text-end">งบวิจัยรวม (บาท)</th>
                                            <th style="width: 130px;" class="text-end text-success">งบเฉลี่ย/คน (บาท)</th>
                                            <th style="width: 130px;" class="text-end bg-warning-subtle text-warning-emphasis">เกณฑ์มาตรฐาน (บาท/คน)</th>
                                            <th style="width: 110px;" class="bg-info-subtle text-info-emphasis fw-bold">คะแนนสาขา (เต็ม 5)</th>
                                            <th style="width: 135px;" class="bg-success-subtle text-success fw-bold">คะแนนภาพรวมคณะ (เต็ม 5)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($qaProjectFacultyGroups as $group)
                                            @php
                                                $branchCount = count($group['branches']);
                                            @endphp
                                            @if($branchCount === 1)
                                                @php $b = $group['branches']->first(); @endphp
                                                <tr>
                                                    <td class="text-start fw-bold text-body">
                                                        <i class="bi bi-buildings text-primary me-1"></i>{{ $group['department_nameTH'] }}
                                                    </td>
                                                    <td class="text-start">
                                                        @if($b['branch_id'] == 1)
                                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><i class="bi bi-cpu me-1"></i>{{ $b['branch_name'] }}</span>
                                                        @elseif($b['branch_id'] == 2)
                                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-book me-1"></i>{{ $b['branch_name'] }}</span>
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle"><i class="bi bi-heart-pulse me-1"></i>{{ $b['branch_name'] }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="tabular-nums">{{ number_format($b['per_num'], 1) }}</td>
                                                    <td class="tabular-nums fw-medium">{{ number_format($b['proj_count']) }}</td>
                                                    <td class="tabular-nums text-end">฿{{ number_format($b['proj_budget'], 0) }}</td>
                                                    <td class="tabular-nums text-end fw-semibold text-success">฿{{ number_format($b['avg_budget_per_person'], 0) }}</td>
                                                    <td class="tabular-nums text-end bg-warning-subtle fw-semibold">฿{{ number_format($b['standard_budget'], 0) }}</td>
                                                    <td class="tabular-nums bg-info-subtle fw-bold text-info-emphasis qa-branch-score fs-6">{{ number_format($b['qa_score_5'], 2) }}</td>
                                                    <td class="tabular-nums bg-success-subtle fw-bold text-success fs-6">
                                                        <span class="badge bg-success text-white px-2.5 py-1.5 shadow-xs">{{ number_format($group['faculty_qa_score_5'], 2) }}</span>
                                                    </td>
                                                </tr>
                                            @else
                                                @foreach($group['branches'] as $idx => $b)
                                                    <tr>
                                                        @if($idx === 0)
                                                            <td rowspan="{{ $branchCount + 1 }}" class="text-start fw-bold text-body align-middle bg-body-tertiary border-end">
                                                                <i class="bi bi-buildings-fill text-primary me-1"></i>{{ $group['department_nameTH'] }}
                                                                <span class="badge bg-secondary-subtle text-body border d-block mt-1 w-auto small">มี {{ $branchCount }} กลุ่มสาขา</span>
                                                            </td>
                                                        @endif
                                                        <td class="text-start">
                                                            @if($b['branch_id'] == 1)
                                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><i class="bi bi-cpu me-1"></i>{{ $b['branch_name'] }}</span>
                                                            @elseif($b['branch_id'] == 2)
                                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-book me-1"></i>{{ $b['branch_name'] }}</span>
                                                            @else
                                                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle"><i class="bi bi-heart-pulse me-1"></i>{{ $b['branch_name'] }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="tabular-nums">{{ number_format($b['per_num'], 1) }}</td>
                                                        <td class="tabular-nums fw-medium">{{ number_format($b['proj_count']) }}</td>
                                                        <td class="tabular-nums text-end">฿{{ number_format($b['proj_budget'], 0) }}</td>
                                                        <td class="tabular-nums text-end fw-semibold text-success">฿{{ number_format($b['avg_budget_per_person'], 0) }}</td>
                                                        <td class="tabular-nums text-end bg-warning-subtle fw-semibold">฿{{ number_format($b['standard_budget'], 0) }}</td>
                                                        <td class="tabular-nums bg-info-subtle fw-bold text-info-emphasis qa-branch-score fs-6">{{ number_format($b['qa_score_5'], 2) }}</td>
                                                        @if($idx === 0)
                                                            <td rowspan="{{ $branchCount + 1 }}" class="tabular-nums bg-success-subtle align-middle text-center border-start">
                                                                <span class="badge bg-success text-white fs-6 px-2.5 py-1.5 shadow-xs d-inline-block">{{ number_format($group['faculty_qa_score_5'], 2) }}</span>
                                                                <small class="text-success-emphasis d-block mt-1" style="font-size: 0.75rem;">(เฉลี่ยจากกลุ่มสาขา)</small>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                                <tr class="table-primary-subtle fw-bold border-bottom">
                                                    <td class="text-start text-primary"><i class="bi bi-check2-circle me-1"></i>สรุปภาพรวมคณะ</td>
                                                    <td class="tabular-nums text-primary">{{ number_format($group['total_personnel'], 1) }}</td>
                                                    <td class="tabular-nums text-primary">{{ number_format($group['total_projects']) }}</td>
                                                    <td class="tabular-nums text-end text-primary">฿{{ number_format($group['total_budget'], 0) }}</td>
                                                    <td class="tabular-nums text-end text-success">฿{{ number_format($group['avg_budget_per_person'], 0) }}</td>
                                                    <td class="text-body-secondary">-</td>
                                                    <td class="text-body-secondary">-</td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="9" class="py-4 text-center text-body-secondary">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-group-divider fw-bold bg-body-tertiary">
                                        <tr>
                                            <td colspan="2" class="text-start">รวมทั้งหมด / ค่าเฉลี่ยระดับสถาบัน</td>
                                            <td class="tabular-nums">{{ number_format($qaProjectFacultyGroups->sum('total_personnel'), 1) }}</td>
                                            <td class="tabular-nums">{{ number_format($qaProjectFacultyGroups->sum('total_projects')) }}</td>
                                            <td class="tabular-nums text-end text-primary">฿{{ number_format($qaProjectFacultyGroups->sum('total_budget'), 0) }}</td>
                                            <td class="tabular-nums text-end text-success">
                                                @php $allProjPer = $qaProjectFacultyGroups->sum('total_personnel'); @endphp
                                                ฿{{ $allProjPer > 0 ? number_format($qaProjectFacultyGroups->sum('total_budget') / $allProjPer, 0) : 0 }}
                                            </td>
                                            <td class="tabular-nums text-end bg-warning-subtle">-</td>
                                            <td class="tabular-nums bg-info-subtle text-info-emphasis">-</td>
                                            <td class="tabular-nums text-success fs-6 bg-success-subtle">
                                                <span class="badge bg-success text-white px-2.5 py-1.5 shadow-xs">{{ number_format($avgProjectQaScore5, 2) }}</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 2.3 Faculty Detailed Table -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1 text-body">
                                <i class="bi bi-table text-success me-2"></i>รายละเอียดสัดส่วนโครงการวิจัยและงบประมาณแยกตามคณะ
                            </h5>
                            <p class="text-body-secondary small mb-0">
                                อัตราส่วนโครงการวิจัยและงบประมาณสนับสนุนงานวิจัยต่อจำนวนบุคลากรสายวิชาการประจำคณะ
                            </p>
                        </div>
                        <div>
                            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-xs">
                                <i class="bi bi-printer me-1.5"></i> พิมพ์ตารางนี้
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0 text-center" style="font-size: 0.92rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="align-middle text-start" style="min-width: 220px;">คณะ / หน่วยงาน</th>
                                    <th class="align-middle" style="width: 120px;">จำนวนบุคลากร (คน)</th>
                                    <th class="align-middle" style="width: 130px;">จำนวนโครงการ (เรื่อง)</th>
                                    <th class="align-middle text-end" style="width: 180px;">งบประมาณรวม (บาท)</th>
                                    <th class="align-middle text-end text-success" style="width: 170px;">งบประมาณเฉลี่ย/คน (บาท)</th>
                                    <th class="align-middle text-end text-primary" style="width: 140px;">สัดส่วนโครงการ/คน (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $pSumPer = 0; $pSumProj = 0; $pSumBud = 0;
                                @endphp
                                @forelse($qaProjectTable as $row)
                                    @php
                                        $pSumPer += $row['per_num'];
                                        $pSumProj += $row['project_count'];
                                        $pSumBud += $row['total_budget'];
                                    @endphp
                                    <tr>
                                        <td class="text-start fw-medium text-body">{{ $row['department_nameTH'] }}</td>
                                        <td class="tabular-nums">{{ number_format($row['per_num']) }}</td>
                                        <td class="tabular-nums fw-bold">{{ number_format($row['project_count']) }}</td>
                                        <td class="tabular-nums text-end">฿{{ number_format($row['total_budget'], 2) }}</td>
                                        <td class="tabular-nums text-end fw-bold text-success">฿{{ number_format($row['avg_budget_per_person'], 2) }}</td>
                                        <td class="tabular-nums text-end fw-bold text-primary">{{ number_format($row['project_ratio_pct'], 2) }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-body-secondary">ไม่พบข้อมูลสำหรับเงื่อนไขที่เลือก</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-group-divider fw-bold bg-body-tertiary">
                                <tr>
                                    <td class="text-start">รวมทั้งหมด</td>
                                    <td class="tabular-nums">{{ number_format($pSumPer) }}</td>
                                    <td class="tabular-nums">{{ number_format($pSumProj) }}</td>
                                    <td class="tabular-nums text-end">฿{{ number_format($pSumBud, 2) }}</td>
                                    <td class="tabular-nums text-end text-success">฿{{ $pSumPer > 0 ? number_format($pSumBud / $pSumPer, 2) : 0 }}</td>
                                    <td class="tabular-nums text-end text-primary">{{ $pSumPer > 0 ? number_format(($pSumProj / $pSumPer) * 100, 2) : 0 }}%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    </div> <!-- End #qaProjectTablesCollapse -->
                </div>

                {{-- ========================================================= --}}
                {{-- Tab 3: QA Certified Utilizations                           --}}
                {{-- ========================================================= --}}
                <div class="tab-pane fade {{ ($activeTab ?? 'pub') === 'util' ? 'show active' : '' }} p-4" id="qa-util" role="tabpanel">
                    
                    <!-- Tab 3 Charts Section: แสดงเป็นหลักด้านบน -->
                    <div class="mb-4">
                        <div class="row g-3 mb-3">
                            <!-- 3.1 เปรียบเทียบผลงานการใช้ประโยชน์ระดับคณะ (ผ่านเกณฑ์ QA vs ทั่วไป) -->
                            <div class="col-lg-8">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-bar-chart-fill text-danger me-1"></i>เปรียบเทียบผลงานการใช้ประโยชน์ระดับคณะ (ผ่านเกณฑ์ QA vs ทั่วไป)
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 250px;">
                                            <canvas id="chartUtilFaculty"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 3.2 สัดส่วนการผ่านเกณฑ์ QA ภาพรวมทั้งมหาวิทยาลัย -->
                            <div class="col-lg-4">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-pie-chart-fill text-danger me-1"></i>สัดส่วนการผ่านเกณฑ์ QA ภาพรวม
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 250px;">
                                            <canvas id="chartUtilOverallDonut"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- 3.3 ร้อยละผลงานที่ผ่านเกณฑ์ QA (%) แยกตามคณะ -->
                            <div class="col-12">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-patch-check-fill text-success me-1"></i>สัดส่วนร้อยละผลงานที่ผ่านเกณฑ์รับรอง QA (%) แยกตามคณะ
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 230px;">
                                            <canvas id="chartUtilPctFaculty"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle Button for Tables: Tab 3 -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 mb-3 border-top">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-danger shadow-xs d-inline-flex align-items-center gap-1.5" 
                                    type="button" data-bs-toggle="collapse" data-bs-target="#qaUtilTablesCollapse" 
                                    aria-expanded="false" aria-controls="qaUtilTablesCollapse" id="btnToggleUtilTables">
                                <i class="bi bi-table text-danger"></i>
                                <span class="toggle-table-text">แสดงตารางข้อมูลรายละเอียดการใช้ประโยชน์</span>
                                <i class="bi bi-chevron-down ms-1 toggle-table-icon"></i>
                            </button>
                            <span class="text-body-secondary small d-none d-md-inline">
                                <i class="bi bi-info-circle me-1"></i>(ตารางถูกซ่อนไว้เป็นค่าเริ่มต้นเพื่อความสบายตา)
                            </span>
                        </div>
                        <div>
                            <a href="{{ route('frontend.dashboard.utilizations') }}" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 shadow-xs">
                                <i class="bi bi-map me-1.5"></i> ดูแผนที่ GIS การใช้ประโยชน์
                            </a>
                        </div>
                    </div>

                    <!-- Collapsible Tables: Tab 3 (ซ่อนไว้เป็นค่าเริ่มต้น) -->
                    <div class="collapse qa-tables-collapse" id="qaUtilTablesCollapse">

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0 text-center" style="font-size: 0.92rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="align-middle text-start" style="min-width: 220px;">คณะ / หน่วยงาน</th>
                                    <th class="align-middle" style="width: 150px;">การใช้ประโยชน์ทั้งหมด</th>
                                    <th class="align-middle text-danger" style="width: 170px;">ผ่านการรับรองเกณฑ์ QA</th>
                                    <th class="align-middle text-success" style="width: 160px;">สัดส่วนที่ผ่านรับรอง (%)</th>
                                    <th class="align-middle" style="width: 120px;">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $uSumTotal = 0; $uSumQa = 0;
                                @endphp
                                @forelse($qaUtilTable as $row)
                                    @php
                                        $uSumTotal += $row['total_util'];
                                        $uSumQa += $row['qa_certified'];
                                    @endphp
                                    <tr>
                                        <td class="text-start fw-medium text-body">{{ $row['department_nameTH'] }}</td>
                                        <td class="tabular-nums">{{ number_format($row['total_util']) }}</td>
                                        <td class="tabular-nums fw-bold text-danger">{{ number_format($row['qa_certified']) }}</td>
                                        <td class="tabular-nums fw-bold text-success">{{ number_format($row['qa_pct'], 1) }}%</td>
                                        <td>
                                            @if($row['qa_certified'] > 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <i class="bi bi-check-circle-fill me-1"></i> มีผลงาน QA
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-body-secondary border px-2 py-1">
                                                    ไม่มีผลงาน QA
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-body-secondary">ไม่พบข้อมูลสำหรับเงื่อนไขที่เลือก</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-group-divider fw-bold bg-body-tertiary">
                                <tr>
                                    <td class="text-start">รวมทั้งหมด</td>
                                    <td class="tabular-nums">{{ number_format($uSumTotal) }}</td>
                                    <td class="tabular-nums text-danger">{{ number_format($uSumQa) }}</td>
                                    <td class="tabular-nums text-success">{{ $uSumTotal > 0 ? number_format(($uSumQa / $uSumTotal) * 100, 1) : 0 }}%</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    </div> <!-- End #qaUtilTablesCollapse -->
                </div>

                {{-- ========================================================= --}}
                {{-- Tab 4: First-time Researchers (reportfirstproject)         --}}
                {{-- ========================================================= --}}
                <div class="tab-pane fade {{ ($activeTab ?? 'pub') === 'first' ? 'show active' : '' }} p-4" id="qa-first" role="tabpanel">
                    
                    <!-- Tab 4 Charts Section: แสดงเป็นหลักด้านบน -->
                    <div class="mb-4">
                        <div class="row g-3">
                            <!-- 4.1 จำนวนนักวิจัยรุ่นใหม่ที่ได้รับทุนโครงการแรก แยกตามคณะ (ท่าน) (ตาราง 4.1) -->
                            <div class="col-lg-7">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-bar-chart text-info me-1"></i>จำนวนนักวิจัยรุ่นใหม่ที่ได้รับทุนโครงการแรก แยกตามคณะ (ท่าน)
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 260px;">
                                            <canvas id="chartFirstResearchersFaculty"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 4.2 สถิตินักวิจัยรุ่นใหม่ตามปีที่เริ่มรับทุน (ตาราง 4.2) -->
                            <div class="col-lg-5">
                                <div class="card h-100 border shadow-xs bg-body">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-body small">
                                                <i class="bi bi-calendar-event text-primary me-1"></i>สถิตินักวิจัยรุ่นใหม่ตามปีที่เริ่มรับทุน (ท่าน)
                                            </span>
                                        </div>
                                        <div style="position: relative; height: 260px;">
                                            <canvas id="chartFirstResearchersYear"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle Button for Tables: Tab 4 -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 mb-3 border-top">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-info shadow-xs d-inline-flex align-items-center gap-1.5" 
                                    type="button" data-bs-toggle="collapse" data-bs-target="#qaFirstTablesCollapse" 
                                    aria-expanded="false" aria-controls="qaFirstTablesCollapse" id="btnToggleFirstTables">
                                <i class="bi bi-table text-info"></i>
                                <span class="toggle-table-text">แสดงตารางข้อมูลรายละเอียด (สรุปตามคณะ & รายชื่อ)</span>
                                <i class="bi bi-chevron-down ms-1 toggle-table-icon"></i>
                            </button>
                            <span class="text-body-secondary small d-none d-md-inline">
                                <i class="bi bi-info-circle me-1"></i>(ตารางถูกซ่อนไว้เป็นค่าเริ่มต้นเพื่อความสบายตา)
                            </span>
                        </div>
                        <div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5">
                                <i class="bi bi-person-check me-1"></i> นักวิจัยรุ่นใหม่มีสังกัดรวม {{ number_format($totalFirstResearchers) }} ท่าน
                            </span>
                        </div>
                    </div>

                    <!-- Collapsible Tables: Tab 4 (ซ่อนไว้เป็นค่าเริ่มต้น) -->
                    <div class="collapse qa-tables-collapse" id="qaFirstTablesCollapse">

                    <div class="alert alert-light border small text-body-secondary py-2 px-3 mb-3">
                        <i class="bi bi-info-circle text-info me-1.5"></i>
                        <strong>เงื่อนไขการนับตามเกณฑ์ QA:</strong> ระบบทำการกรองและนับเฉพาะนักวิจัยที่ระบุสังกัดคณะหรือหน่วยงานทางการในฐานข้อมูลเท่านั้น 
                        @if(($scope ?? 'faculty') === 'faculty')
                            โดยในโหมดขอบเขตปัจจุบันจะแสดงเฉพาะอาจารย์/นักวิจัยสังกัด <strong>6 คณะวิชาการ</strong>
                        @else
                            ครอบคลุมนักวิจัยที่มีสังกัดทุกหน่วยงานในมหาวิทยาลัย
                        @endif
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-lg-4">
                            <div class="card border shadow-xs h-100">
                                <div class="card-header bg-body-tertiary py-2.5 px-3">
                                    <span class="fw-semibold text-body small">
                                        <i class="bi bi-buildings text-primary me-1"></i> สรุปตามคณะ / หน่วยงาน
                                    </span>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush" style="font-size: 0.9rem;">
                                        @forelse($firstProjectByFaculty as $fac)
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                                                <span>{{ $fac->department_nameTH }}</span>
                                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1">
                                                    {{ number_format($fac->new_researcher_count) }} ท่าน
                                                </span>
                                            </li>
                                        @empty
                                            <li class="list-group-item py-3 text-center text-body-secondary">ไม่พบข้อมูล</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card border shadow-xs h-100">
                                <div class="card-header bg-body-tertiary py-2.5 px-3 d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold text-body small">
                                        <i class="bi bi-list-stars text-info me-1"></i> รายชื่อนักวิจัยรุ่นใหม่และโครงการแรกที่ได้รับทุน
                                    </span>
                                    <small class="text-body-secondary">(แสดงสูงสุด 30 รายการล่าสุด)</small>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.88rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>นักวิจัย</th>
                                                    <th>คณะ / หน่วยงาน</th>
                                                    <th>ปี พ.ศ.</th>
                                                    <th>โครงการวิจัยแรกที่ได้รับทุน</th>
                                                    <th class="text-end" style="width: 120px;">งบประมาณ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($firstProjectList as $fp)
                                                    <tr>
                                                        <td class="text-nowrap fw-medium text-body">
                                                            <a href="{{ route('frontend.rdbresearcher.show', $fp->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-bold text-body hover-primary">
                                                                {{ $fp->prefix_nameTH }}{{ $fp->researcher_fname }} {{ $fp->researcher_lname }}
                                                            </a>
                                                        </td>
                                                        <td class="text-body-secondary small">{{ $fp->department_nameTH }}</td>
                                                        <td class="tabular-nums text-center"><span class="badge bg-secondary-subtle text-body-secondary border">{{ $fp->year_name }}</span></td>
                                                        <td>
                                                            <a href="{{ route('frontend.rdbproject.show', $fp->pro_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body hover-primary" title="{{ $fp->pro_nameTH }}">
                                                                {{ Str::limit($fp->pro_nameTH, 60) }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end tabular-nums text-nowrap fw-semibold text-success">
                                                            ฿{{ number_format($fp->pro_budget, 0) }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="py-4 text-center text-body-secondary">ไม่พบข้อมูลนักวิจัยรุ่นใหม่ในเงื่อนไขที่เลือก</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div> <!-- End #qaFirstTablesCollapse -->
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Tab persistence & Tab Switching
    const tabButtons = document.querySelectorAll('#qaTabs button[data-bs-toggle="tab"]');
    const filterTabInput = document.querySelector('input[name="tab"]');

    // 2. Toggle button text switching for collapsible tables
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(btn => {
        const targetSelector = btn.getAttribute('data-bs-target');
        const target = document.querySelector(targetSelector);
        if (target && target.classList.contains('qa-tables-collapse')) {
            target.addEventListener('show.bs.collapse', function() {
                const toggleText = btn.querySelector('.toggle-table-text');
                if (toggleText) toggleText.textContent = toggleText.textContent.replace('แสดงตาราง', 'ซ่อนตาราง');
            });
            target.addEventListener('hide.bs.collapse', function() {
                const toggleText = btn.querySelector('.toggle-table-text');
                if (toggleText) toggleText.textContent = toggleText.textContent.replace('ซ่อนตาราง', 'แสดงตาราง');
            });
        }
    });

    // 3. Theme & Colors
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#475569';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

    function shortenDept(name) {
        if (!name) return '';
        return name.replace(/^คณะ/, '').replace(/และ/g, '&').trim();
    }

    const chartInstances = {};
    const renderedTabs = {};

    // -------------------------------------------------------------
    // Tab 1: Publications Charts
    // -------------------------------------------------------------
    function renderPubCharts() {
        const pubFacData = @json($qaPubFacultyGroups);
        const pubBranchData = @json($qaPubBranchTable);
        const pubTableData = @json($qaPubTable);

        const facLabels = pubFacData.map(d => shortenDept(d.department_nameTH));
        const facFullNames = pubFacData.map(d => d.department_nameTH);
        const facScores = pubFacData.map(d => d.faculty_qa_score_5);
        const target5 = facScores.map(() => 5.00);

        // Chart 1.1: Pub Faculty Score vs 5.00 Target (ตาราง 1.2)
        const ctxPubFac = document.getElementById('chartPubFacultyScore');
        if (ctxPubFac && !chartInstances.pubFac) {
            chartInstances.pubFac = new Chart(ctxPubFac.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: facLabels,
                    datasets: [
                        {
                            type: 'line',
                            label: 'เกณฑ์เป้าหมาย (5.00)',
                            data: target5,
                            borderColor: '#EF4444',
                            borderWidth: 2,
                            borderDash: [6, 4],
                            pointRadius: 0,
                            fill: false
                        },
                        {
                            type: 'bar',
                            label: 'คะแนนประเมิน (เต็ม 5)',
                            data: facScores,
                            backgroundColor: '#0284C7',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } } },
                        tooltip: {
                            callbacks: {
                                title: (items) => facFullNames[items[0].dataIndex] || items[0].label,
                                label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw} คะแนน`
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } }, grid: { display: false } },
                        y: { min: 0, max: 5.5, ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } }
                    }
                }
            });
        }

        // Chart 1.2: Pub Branch Score Doughnut (ตาราง 1.1)
        const ctxPubBranch = document.getElementById('chartPubBranchScore');
        if (ctxPubBranch && !chartInstances.pubBranch) {
            const branchLabels = pubBranchData.map(b => b.branch_name);
            const branchWeights = pubBranchData.map(b => b.weighted_score);
            const branchColors = ['#06B6D4', '#F59E0B', '#EF4444'];

            chartInstances.pubBranch = new Chart(ctxPubBranch.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: branchLabels,
                    datasets: [{
                        data: branchWeights,
                        backgroundColor: branchColors,
                        borderWidth: 2,
                        borderColor: isDark ? '#1E293B' : '#FFFFFF'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } } },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const total = branchWeights.reduce((a, b) => a + b, 0) || 1;
                                    const pct = ((ctx.raw / total) * 100).toFixed(1);
                                    return ` คะแนนถ่วงน้ำหนัก: ${ctx.raw} (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // Chart 1.3: Pub Quality Weights Stacked Bar per Faculty (ตาราง 1.3)
        const ctxPubWeight = document.getElementById('chartPubWeightStack');
        if (ctxPubWeight && !chartInstances.pubWeight) {
            const tLabels = pubTableData.map(d => shortenDept(d.department_nameTH));
            const tFullNames = pubTableData.map(d => d.department_nameTH);

            chartInstances.pubWeight = new Chart(ctxPubWeight.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: tLabels,
                    datasets: [
                        { label: '0.20', data: pubTableData.map(d => d.count_020), backgroundColor: '#94A3B8', borderRadius: 2 },
                        { label: '0.40', data: pubTableData.map(d => d.count_040), backgroundColor: '#60A5FA', borderRadius: 2 },
                        { label: '0.60', data: pubTableData.map(d => d.count_060), backgroundColor: '#3B82F6', borderRadius: 2 },
                        { label: '0.80', data: pubTableData.map(d => d.count_080), backgroundColor: '#F59E0B', borderRadius: 2 },
                        { label: '1.00', data: pubTableData.map(d => d.count_100), backgroundColor: '#10B981', borderRadius: 2 },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { color: textColor, font: { size: 10, family: 'Plus Jakarta Sans, Prompt' } } },
                        tooltip: {
                            callbacks: {
                                title: (items) => tFullNames[items[0].dataIndex] || items[0].label,
                                label: (ctx) => ` ค่าน้ำหนัก ${ctx.dataset.label}: ${ctx.raw} เรื่อง`
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true, ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } }, grid: { display: false } },
                        y: { stacked: true, ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } }
                    }
                }
            });
        }

        // Chart 1.4: Pub Branch Compare (score_pct vs bench_percent) (ตาราง 1.1)
        const ctxPubBranchComp = document.getElementById('chartPubBranchCompare');
        if (ctxPubBranchComp && !chartInstances.pubBranchComp) {
            const bLabels = pubBranchData.map(b => shortenDept(b.branch_name).replace(/^สาขา/, ''));
            const bScorePct = pubBranchData.map(b => b.score_pct);
            const bBenchPct = pubBranchData.map(b => b.bench_percent);

            chartInstances.pubBranchComp = new Chart(ctxPubBranchComp.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: bLabels,
                    datasets: [
                        { label: 'ร้อยละคะแนน/คน (จริง)', data: bScorePct, backgroundColor: '#0284C7', borderRadius: 4 },
                        { label: 'เกณฑ์ฐานร้อยละ (%)', data: bBenchPct, backgroundColor: '#F59E0B', borderRadius: 4 },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } } },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw}%`
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } }, grid: { display: false } },
                        y: { ticks: { color: textColor, callback: (v) => v + '%' }, grid: { color: gridColor } }
                    }
                }
            });
        }
    }

    // -------------------------------------------------------------
    // Tab 2: Projects & Budget Charts
    // -------------------------------------------------------------
    function renderProjectCharts() {
        const projFacData = @json($qaProjectFacultyGroups);
        const projBranchData = @json($qaProjectBranchTable);
        const projTableData = @json($qaProjectTable);

        const facLabels = projFacData.map(d => shortenDept(d.department_nameTH));
        const facFullNames = projFacData.map(d => d.department_nameTH);
        const facScores = projFacData.map(d => d.faculty_qa_score_5);
        const target5 = facScores.map(() => 5.00);

        // Chart 2.1: Project Faculty Score vs 5.00 Target (ตาราง 2.2)
        const ctxProjFac = document.getElementById('chartProjectFacultyScore');
        if (ctxProjFac && !chartInstances.projFac) {
            chartInstances.projFac = new Chart(ctxProjFac.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: facLabels,
                    datasets: [
                        {
                            type: 'line',
                            label: 'เกณฑ์เป้าหมาย (5.00)',
                            data: target5,
                            borderColor: '#EF4444',
                            borderWidth: 2,
                            borderDash: [6, 4],
                            pointRadius: 0,
                            fill: false
                        },
                        {
                            type: 'bar',
                            label: 'คะแนนประเมิน (เต็ม 5)',
                            data: facScores,
                            backgroundColor: '#10B981',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } } },
                        tooltip: {
                            callbacks: {
                                title: (items) => facFullNames[items[0].dataIndex] || items[0].label,
                                label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw} คะแนน`
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } }, grid: { display: false } },
                        y: { min: 0, max: 5.5, ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } }
                    }
                }
            });
        }

        // Chart 2.2: Project Branch Comparison (Actual vs Standard) (ตาราง 2.1)
        const ctxProjBranch = document.getElementById('chartProjectBranchCompare');
        if (ctxProjBranch && !chartInstances.projBranch) {
            const bLabels = projBranchData.map(b => shortenDept(b.branch_name).replace(/^สาขา/, ''));
            const bActual = projBranchData.map(b => b.avg_budget_per_person);
            const bStandard = projBranchData.map(b => b.standard_budget);

            chartInstances.projBranch = new Chart(ctxProjBranch.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: bLabels,
                    datasets: [
                        { label: 'งบเฉลี่ยจริง (บาท/คน)', data: bActual, backgroundColor: '#10B981', borderRadius: 4 },
                        { label: 'เกณฑ์มาตรฐาน (บาท/คน)', data: bStandard, backgroundColor: '#F59E0B', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } } },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ` ${ctx.dataset.label}: ฿${Number(ctx.raw).toLocaleString()} บาท`
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } }, grid: { display: false } },
                        y: { 
                            ticks: { 
                                color: textColor, 
                                callback: (v) => '฿' + (v >= 1000 ? (v / 1000) + 'k' : v) 
                            }, 
                            grid: { color: gridColor } 
                        }
                    }
                }
            });
        }

        // Chart 2.3: Project Budget Per Person by Faculty (ตาราง 2.3)
        const ctxProjBudget = document.getElementById('chartProjectBudgetPerPerson');
        if (ctxProjBudget && !chartInstances.projBudget) {
            const tLabels = projTableData.map(d => shortenDept(d.department_nameTH));
            const tFullNames = projTableData.map(d => d.department_nameTH);
            const tBudgets = projTableData.map(d => d.avg_budget_per_person);

            chartInstances.projBudget = new Chart(ctxProjBudget.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: tLabels,
                    datasets: [{
                        label: 'งบวิจัยเฉลี่ย/คน (บาท)',
                        data: tBudgets,
                        backgroundColor: '#059669',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: (items) => tFullNames[items[0].dataIndex] || items[0].label,
                                label: (ctx) => ` งบเฉลี่ย/คน: ฿${Number(ctx.raw).toLocaleString()} บาท`
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } }, grid: { display: false } },
                        y: { 
                            ticks: { 
                                color: textColor, 
                                callback: (v) => '฿' + (v >= 1000 ? (v / 1000) + 'k' : v) 
                            }, 
                            grid: { color: gridColor } 
                        }
                    }
                }
            });
        }

        // Chart 2.4: Project Ratio Pct by Faculty (ตาราง 2.3)
        const ctxProjRatio = document.getElementById('chartProjectRatioPct');
        if (ctxProjRatio && !chartInstances.projRatio) {
            const tLabels = projTableData.map(d => shortenDept(d.department_nameTH));
            const tFullNames = projTableData.map(d => d.department_nameTH);
            const tRatios = projTableData.map(d => d.project_ratio_pct);

            chartInstances.projRatio = new Chart(ctxProjRatio.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: tLabels,
                    datasets: [{
                        label: 'สัดส่วนโครงการ/คน (%)',
                        data: tRatios,
                        backgroundColor: '#3B82F6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: (items) => tFullNames[items[0].dataIndex] || items[0].label,
                                label: (ctx) => ` สัดส่วนโครงการ/คน: ${ctx.raw}%`
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } }, grid: { display: false } },
                        y: { ticks: { color: textColor, callback: (v) => v + '%' }, grid: { color: gridColor } }
                    }
                }
            });
        }
    }

    // -------------------------------------------------------------
    // Tab 3: Utilizations Charts
    // -------------------------------------------------------------
    function renderUtilCharts() {
        const utilData = @json($qaUtilTable);
        const totalUtilCertified = {{ (int) $totalUtilCertified }};
        const totalUtil = {{ (int) $totalUtil }};

        const uLabels = utilData.map(d => shortenDept(d.department_nameTH));
        const uFullNames = utilData.map(d => d.department_nameTH);
        const uCertified = utilData.map(d => d.qa_certified);
        const uOther = utilData.map(d => Math.max(0, d.total_util - d.qa_certified));
        const uPct = utilData.map(d => d.qa_pct);

        // Chart 3.1: Stacked Bar per Faculty (ตาราง 3.1)
        const ctxUtilFac = document.getElementById('chartUtilFaculty');
        if (ctxUtilFac && !chartInstances.utilFac) {
            chartInstances.utilFac = new Chart(ctxUtilFac.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: uLabels,
                    datasets: [
                        { label: 'ผ่านเกณฑ์รับรอง QA', data: uCertified, backgroundColor: '#EF4444', borderRadius: 4 },
                        { label: 'การใช้ประโยชน์ทั่วไป', data: uOther, backgroundColor: isDark ? '#475569' : '#CBD5E1', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } } },
                        tooltip: {
                            callbacks: {
                                title: (items) => uFullNames[items[0].dataIndex] || items[0].label,
                                label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw} เรื่อง`
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true, ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } }, grid: { display: false } },
                        y: { stacked: true, ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } }
                    }
                }
            });
        }

        // Chart 3.2: Overall Donut (ตาราง 3.1)
        const ctxUtilDonut = document.getElementById('chartUtilOverallDonut');
        if (ctxUtilDonut && !chartInstances.utilDonut) {
            const otherCount = Math.max(0, totalUtil - totalUtilCertified);
            chartInstances.utilDonut = new Chart(ctxUtilDonut.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['ผ่านเกณฑ์รับรอง QA', 'ใช้ประโยชน์ทั่วไป'],
                    datasets: [{
                        data: [totalUtilCertified, otherCount],
                        backgroundColor: ['#EF4444', isDark ? '#334155' : '#E2E8F0'],
                        borderWidth: 2,
                        borderColor: isDark ? '#1E293B' : '#FFFFFF'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } } },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const total = totalUtil || 1;
                                    const pct = ((ctx.raw / total) * 100).toFixed(1);
                                    return ` ${ctx.label}: ${ctx.raw} เรื่อง (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }

        // Chart 3.3: QA Pct per Faculty (ตาราง 3.1)
        const ctxUtilPct = document.getElementById('chartUtilPctFaculty');
        if (ctxUtilPct && !chartInstances.utilPct) {
            chartInstances.utilPct = new Chart(ctxUtilPct.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: uLabels,
                    datasets: [{
                        label: 'สัดส่วนผ่านเกณฑ์ QA (%)',
                        data: uPct,
                        backgroundColor: '#10B981',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: (items) => uFullNames[items[0].dataIndex] || items[0].label,
                                label: (ctx) => ` สัดส่วนผ่านเกณฑ์ QA: ${ctx.raw}%`
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } }, grid: { display: false } },
                        y: { ticks: { color: textColor, callback: (v) => v + '%' }, grid: { color: gridColor } }
                    }
                }
            });
        }
    }

    // -------------------------------------------------------------
    // Tab 4: First-time Researchers Charts
    // -------------------------------------------------------------
    function renderFirstCharts() {
        const firstData = @json($firstProjectByFaculty);
        const firstYearData = @json($firstProjectByYear ?? []);

        const fLabels = firstData.map(d => shortenDept(d.department_nameTH));
        const fFullNames = firstData.map(d => d.department_nameTH);
        const fCounts = firstData.map(d => d.new_researcher_count);

        // Chart 4.1: First Researchers by Faculty (ตาราง 4.1)
        const ctxFirst = document.getElementById('chartFirstResearchersFaculty');
        if (ctxFirst && !chartInstances.first) {
            chartInstances.first = new Chart(ctxFirst.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: fLabels,
                    datasets: [{
                        label: 'นักวิจัยรับทุนครั้งแรก (ท่าน)',
                        data: fCounts,
                        backgroundColor: '#3B82F6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: (items) => fFullNames[items[0].dataIndex] || items[0].label,
                                label: (ctx) => ` นักวิจัยรับทุนครั้งแรก: ${ctx.raw} ท่าน`
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } }, grid: { display: false } },
                        y: { ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } }
                    }
                }
            });
        }

        // Chart 4.2: First Researchers by Year (ตาราง 4.2)
        const ctxFirstYear = document.getElementById('chartFirstResearchersYear');
        if (ctxFirstYear && !chartInstances.firstYear) {
            const yLabels = firstYearData.map(d => 'ปี ' + d.year_name);
            const yCounts = firstYearData.map(d => d.count);

            chartInstances.firstYear = new Chart(ctxFirstYear.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: yLabels,
                    datasets: [{
                        label: 'จำนวนนักวิจัย (ท่าน)',
                        data: yCounts,
                        backgroundColor: '#8B5CF6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ` จำนวนนักวิจัยใหม่: ${ctx.raw} ท่าน`
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans, Prompt' } }, grid: { display: false } },
                        y: { ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } }
                    }
                }
            });
        }
    }

    // 4. Tab Dispatcher
    function renderTabCharts(tabId) {
        if (tabId === 'pub') {
            if (!renderedTabs.pub) {
                renderPubCharts();
                renderedTabs.pub = true;
            } else {
                if (chartInstances.pubFac) chartInstances.pubFac.resize();
                if (chartInstances.pubBranch) chartInstances.pubBranch.resize();
                if (chartInstances.pubWeight) chartInstances.pubWeight.resize();
                if (chartInstances.pubBranchComp) chartInstances.pubBranchComp.resize();
            }
        } else if (tabId === 'project') {
            if (!renderedTabs.project) {
                renderProjectCharts();
                renderedTabs.project = true;
            } else {
                if (chartInstances.projFac) chartInstances.projFac.resize();
                if (chartInstances.projBranch) chartInstances.projBranch.resize();
                if (chartInstances.projBudget) chartInstances.projBudget.resize();
                if (chartInstances.projRatio) chartInstances.projRatio.resize();
            }
        } else if (tabId === 'util') {
            if (!renderedTabs.util) {
                renderUtilCharts();
                renderedTabs.util = true;
            } else {
                if (chartInstances.utilFac) chartInstances.utilFac.resize();
                if (chartInstances.utilDonut) chartInstances.utilDonut.resize();
                if (chartInstances.utilPct) chartInstances.utilPct.resize();
            }
        } else if (tabId === 'first') {
            if (!renderedTabs.first) {
                renderFirstCharts();
                renderedTabs.first = true;
            } else {
                if (chartInstances.first) chartInstances.first.resize();
                if (chartInstances.firstYear) chartInstances.firstYear.resize();
            }
        }
    }

    // Tab button click listener
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const targetId = e.target.getAttribute('data-bs-target').replace('#qa-', '');
            if (filterTabInput) {
                filterTabInput.value = targetId;
            }
            const url = new URL(window.location);
            url.searchParams.set('tab', targetId);
            window.history.replaceState({}, '', url);

            // Render charts for the newly active tab
            renderTabCharts(targetId);
        });
    });

    // Initial render for active tab on load
    const initialActiveTab = '{{ $activeTab ?? "pub" }}';
    renderTabCharts(initialActiveTab);
});
</script>
@endsection
