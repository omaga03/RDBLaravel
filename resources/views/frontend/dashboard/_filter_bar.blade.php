<div class="card border shadow-sm mb-4 bg-body-tertiary" style="border-radius: var(--rdb-radius-md) !important;">
    <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-2.5 px-3 collapsed"
         style="cursor: pointer;"
         data-bs-toggle="collapse" 
         data-bs-target="#intelligenceFiltersCollapse" 
         aria-expanded="false" 
         aria-controls="intelligenceFiltersCollapse">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-sliders text-primary fs-5"></i>
            <span class="fw-bold text-body" style="font-size: 0.95rem;">ตัวกรองข้อมูลสารสนเทศ (Intelligence Filters)</span>
            @if(isset($activeFilterCount) && $activeFilterCount > 0)
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                    <i class="bi bi-funnel-fill me-1"></i> ใช้งาน {{ $activeFilterCount }} ตัวกรอง
                </span>
            @else
                <span class="badge bg-secondary-subtle text-body-secondary rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                    <i class="bi bi-chevron-expand me-1"></i>คลิกเพื่อเปิด/ซ่อนตัวกรอง
                </span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(isset($activeFilterCount) && $activeFilterCount > 0)
                <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary py-0.5 px-2 text-nowrap" style="font-size: 0.78rem;" title="ล้างตัวกรองทั้งหมด" onclick="event.stopPropagation();">
                    <i class="bi bi-x-circle me-1"></i> ล้างตัวกรอง
                </a>
            @endif
            <button type="button" class="btn btn-sm btn-light border py-0.5 px-2 text-body-secondary" aria-label="Toggle filters">
                <i class="bi bi-chevron-down filter-collapse-icon"></i>
            </button>
        </div>
    </div>
    <div class="collapse" id="intelligenceFiltersCollapse">
        <div class="card-body p-3">
            <form action="{{ url()->current() }}" method="GET" id="dashboardFiltersForm">
            <div class="row g-2.5 align-items-end">

                {{-- ========================================================= --}}
                {{-- 1. Executive Overview & 2. Projects Dashboards Filters     --}}
                {{-- ========================================================= --}}
                @if(request()->routeIs('frontend.dashboard.index') || request()->routeIs('dashboard') || request()->routeIs('frontend.dashboard.projects'))
                    <!-- Filter 1: Year -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-calendar3 text-primary me-1"></i> ปีงบประมาณ</label>
                        <select name="year_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($selectedYearId) ? 'selected' : '' }}>-- ทุกปีงบประมาณ --</option>
                            @foreach($years as $y)
                                <option value="{{ $y->year_id }}" {{ (isset($selectedYearId) && $selectedYearId == $y->year_id) ? 'selected' : '' }}>
                                    ปีงบประมาณ {{ $y->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 2: Faculty / Department -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-building text-primary me-1"></i> คณะ / หน่วยงาน</label>
                        <select name="department_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($deptId) ? 'selected' : '' }}>-- ทุกคณะ / หน่วยงาน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" {{ (isset($deptId) && $deptId == $dept->department_id) ? 'selected' : '' }}>
                                    {{ $dept->department_nameTH }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 3: Project Status -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-check2-circle text-primary me-1"></i> สถานะโครงการ</label>
                        <select name="ps_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($psId) ? 'selected' : '' }}>-- ทุกสถานะ --</option>
                            @foreach($statuses as $st)
                                <option value="{{ $st->ps_id }}" {{ (isset($psId) && $psId == $st->ps_id) ? 'selected' : '' }}>
                                    {{ $st->ps_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 4: Standard Master Funding Category -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <label class="form-label text-body-secondary small fw-medium mb-1">
                            <i class="bi bi-tags text-primary me-1"></i> ประเภททุนวิจัย
                        </label>
                        <select name="master_pt_id" id="filter_master_pt_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($masterPtId) ? 'selected' : '' }}>-- ทุกประเภททุน --</option>
                            @if(isset($masterProjectTypes))
                                @foreach($masterProjectTypes as $mpt)
                                    <option value="{{ $mpt->master_pt_id }}" {{ (isset($masterPtId) && $masterPtId == $mpt->master_pt_id) ? 'selected' : '' }}>
                                        {{ $mpt->master_pt_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    @if(request()->routeIs('frontend.dashboard.projects'))
                    <!-- Filter 5: Budget Range (Projects specific) -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-cash-stack text-success me-1"></i> วงเงินงบประมาณ</label>
                        <select name="budget_range" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($budgetRange) ? 'selected' : '' }}>-- ทุกช่วงงบประมาณ --</option>
                            <option value="0-50k" {{ (isset($budgetRange) && $budgetRange === '0-50k') ? 'selected' : '' }}>< 50,000 บาท</option>
                            <option value="50k-100k" {{ (isset($budgetRange) && $budgetRange === '50k-100k') ? 'selected' : '' }}>50,000 - 100,000 บาท</option>
                            <option value="100k-500k" {{ (isset($budgetRange) && $budgetRange === '100k-500k') ? 'selected' : '' }}>100,000 - 500,000 บาท</option>
                            <option value="500k+" {{ (isset($budgetRange) && $budgetRange === '500k+') ? 'selected' : '' }}>> 500,000 บาท</option>
                        </select>
                    </div>
                    @else
                    <!-- Filter 5: Full Report Availability (Overview specific) -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-file-earmark-pdf text-danger me-1"></i> เล่มรายงานสมบูรณ์</label>
                        <select name="has_report" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($hasReport) ? 'selected' : '' }}>-- ทั้งหมด --</option>
                            <option value="1" {{ (isset($hasReport) && $hasReport === '1') ? 'selected' : '' }}>มีเล่มรายงานสมบูรณ์</option>
                            <option value="0" {{ (isset($hasReport) && $hasReport === '0') ? 'selected' : '' }}>ยังไม่มีเล่มรายงาน</option>
                        </select>
                    </div>
                    @endif

                {{-- ========================================================= --}}
                {{-- 3. Publications Dashboard Filters                         --}}
                {{-- ========================================================= --}}
                @elseif(request()->routeIs('frontend.dashboard.publications'))
                    <!-- Filter 1: Year -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-calendar3 text-primary me-1"></i> ปีงบประมาณ / เผยแพร่</label>
                        <select name="year_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($selectedYearId) ? 'selected' : '' }}>-- ทุกปีงบประมาณ --</option>
                            @foreach($years as $y)
                                <option value="{{ $y->year_id }}" {{ (isset($selectedYearId) && $selectedYearId == $y->year_id) ? 'selected' : '' }}>
                                    ปี {{ $y->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 2: Faculty / Department -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-building text-primary me-1"></i> คณะ / หน่วยงาน</label>
                        <select name="department_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($deptId) ? 'selected' : '' }}>-- ทุกคณะ / หน่วยงาน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" {{ (isset($deptId) && $deptId == $dept->department_id) ? 'selected' : '' }}>
                                    {{ $dept->department_nameTH }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 3: Database & Quality Tier -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-award text-warning me-1"></i> ระดับฐานข้อมูล</label>
                        <select name="quality_group" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($qualityGroup) ? 'selected' : '' }}>-- ทุกระดับฐานข้อมูล --</option>
                            <option value="scopus" {{ (isset($qualityGroup) && $qualityGroup === 'scopus') ? 'selected' : '' }}>Scopus (นานาชาติ)</option>
                            <option value="wos" {{ (isset($qualityGroup) && $qualityGroup === 'wos') ? 'selected' : '' }}>Web of Science</option>
                            <option value="tci1" {{ (isset($qualityGroup) && $qualityGroup === 'tci1') ? 'selected' : '' }}>TCI กลุ่มที่ 1</option>
                            <option value="tci2" {{ (isset($qualityGroup) && $qualityGroup === 'tci2') ? 'selected' : '' }}>TCI กลุ่มที่ 2</option>
                            <option value="conf" {{ (isset($qualityGroup) && $qualityGroup === 'conf') ? 'selected' : '' }}>การประชุมวิชาการ</option>
                        </select>
                    </div>

                    <!-- Filter 4: Project Linkage -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-link-45deg text-primary me-1"></i> ความเชื่อมโยงโครงการ</label>
                        <select name="has_project" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($hasProject) ? 'selected' : '' }}>-- ทั้งหมด --</option>
                            <option value="1" {{ (isset($hasProject) && $hasProject === '1') ? 'selected' : '' }}>เชื่อมโยงโครงการวิจัย</option>
                            <option value="0" {{ (isset($hasProject) && $hasProject === '0') ? 'selected' : '' }}>ไม่ได้ระบุโครงการ</option>
                        </select>
                    </div>

                    <!-- Filter 5: Full Text Availability -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-file-earmark-text text-info me-1"></i> ไฟล์บทความ</label>
                        <select name="has_file" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($hasFile) ? 'selected' : '' }}>-- ทั้งหมด --</option>
                            <option value="1" {{ (isset($hasFile) && $hasFile === '1') ? 'selected' : '' }}>มีไฟล์บทความแนบ</option>
                            <option value="0" {{ (isset($hasFile) && $hasFile === '0') ? 'selected' : '' }}>ไม่มีไฟล์แนบ</option>
                        </select>
                    </div>

                {{-- ========================================================= --}}
                {{-- 4. Utilizations & GIS Dashboard Filters                   --}}
                {{-- ========================================================= --}}
                @elseif(request()->routeIs('frontend.dashboard.utilizations'))
                    <!-- Filter 1: Year -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-calendar3 text-primary me-1"></i> ปีงบประมาณ</label>
                        <select name="year_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($selectedYearId) ? 'selected' : '' }}>-- ทุกปีงบประมาณ --</option>
                            @foreach($years as $y)
                                <option value="{{ $y->year_id }}" {{ (isset($selectedYearId) && $selectedYearId == $y->year_id) ? 'selected' : '' }}>
                                    ปี {{ $y->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 2: Faculty / Department -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-building text-primary me-1"></i> คณะ / หน่วยงาน</label>
                        <select name="department_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($deptId) ? 'selected' : '' }}>-- ทุกคณะ / หน่วยงาน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" {{ (isset($deptId) && $deptId == $dept->department_id) ? 'selected' : '' }}>
                                    {{ $dept->department_nameTH }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 3: 5 Pillars of Utilization -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-diagram-3 text-primary me-1"></i> มิติการใช้ประโยชน์</label>
                        <select name="utz_pillar" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($utzPillar) ? 'selected' : '' }}>-- ทุกมิติการใช้ประโยชน์ --</option>
                            <option value="1" {{ (isset($utzPillar) && $utzPillar === '1') ? 'selected' : '' }}>1. เชิงสาธารณะ</option>
                            <option value="2" {{ (isset($utzPillar) && $utzPillar === '2') ? 'selected' : '' }}>2. เชิงนโยบาย</option>
                            <option value="3" {{ (isset($utzPillar) && $utzPillar === '3') ? 'selected' : '' }}>3. เชิงพาณิชย์</option>
                            <option value="4" {{ (isset($utzPillar) && $utzPillar === '4') ? 'selected' : '' }}>4. เชิงพื้นที่ / ชุมชน</option>
                            <option value="5" {{ (isset($utzPillar) && $utzPillar === '5') ? 'selected' : '' }}>5. อื่น ๆ</option>
                        </select>
                    </div>

                    <!-- Filter 4: Province (Changwat) - Multi-select -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1 d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-geo-alt-fill text-danger me-1"></i> จังหวัด</span>
                            @if(!empty($selectedChangwats))
                                <span class="badge bg-danger-subtle text-danger" style="font-size: 0.65rem;">เลือก {{ count($selectedChangwats) }} จว.</span>
                            @endif
                        </label>
                        <select name="changwat[]" id="filter_changwat" class="form-select form-select-sm" multiple placeholder="เลือกจังหวัด (เลือกได้หลายจังหวัด)...">
                            @foreach($allProvinces ?? [] as $prov)
                                <option value="{{ $prov }}" {{ in_array($prov, $selectedChangwats ?? []) ? 'selected' : '' }}>
                                    {{ $prov }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 5: Amphoe - Multi-select (Only enabled if 1 changwat is selected) -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1 d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-pin-map-fill text-danger me-1"></i> อำเภอ</span>
                            @if(count($selectedChangwats ?? []) === 1 && !empty($selectedAmphoes))
                                <span class="badge bg-primary-subtle text-primary" style="font-size: 0.65rem;">เลือก {{ count($selectedAmphoes) }} อ.</span>
                            @endif
                        </label>
                        @php
                            $changwatCount = count($selectedChangwats ?? []);
                            $amphoeDisabled = $changwatCount !== 1;
                            if ($changwatCount === 1) {
                                $amphoePlaceholder = 'เลือกอำเภอ (เลือกได้มากกว่า 1)...';
                            } elseif ($changwatCount > 1) {
                                $amphoePlaceholder = 'เลือกได้เมื่อระบุ 1 จังหวัดเท่านั้น';
                            } else {
                                $amphoePlaceholder = 'เลือก 1 จังหวัดก่อนเพื่อกรองอำเภอ';
                            }
                        @endphp
                        <select name="amphoe[]" id="filter_amphoe" class="form-select form-select-sm" multiple {{ $amphoeDisabled ? 'disabled' : '' }} placeholder="{{ $amphoePlaceholder }}">
                            @if($changwatCount === 1)
                                @foreach($availableAmphoes ?? [] as $amp)
                                    <option value="{{ $amp }}" {{ in_array($amp, $selectedAmphoes ?? []) ? 'selected' : '' }}>
                                        {{ $amp }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Filter 6: QA Accreditation -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-shield-check text-success me-1"></i> รับรองเกณฑ์ QA</label>
                        <select name="qa_status" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($qaStatus) ? 'selected' : '' }}>-- ทั้งหมด --</option>
                            <option value="1" {{ (isset($qaStatus) && $qaStatus === '1') ? 'selected' : '' }}>ผ่านเกณฑ์ QA (1)</option>
                            <option value="0" {{ (isset($qaStatus) && $qaStatus === '0') ? 'selected' : '' }}>ยังไม่ได้รับรอง QA</option>
                        </select>
                    </div>

                {{-- ========================================================= --}}
                {{-- 5. Intellectual Property Dashboard Filters                --}}
                {{-- ========================================================= --}}
                @elseif(request()->routeIs('frontend.dashboard.ip'))
                    <!-- Filter 1: Year -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-calendar3 text-primary me-1"></i> ปีงบประมาณ / ยื่นขอ</label>
                        <select name="year_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($selectedYearId) ? 'selected' : '' }}>-- ทุกปีงบประมาณ --</option>
                            @foreach($years as $y)
                                <option value="{{ $y->year_id }}" {{ (isset($selectedYearId) && $selectedYearId == $y->year_id) ? 'selected' : '' }}>
                                    ปี {{ $y->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 2: Faculty / Department -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-building text-primary me-1"></i> คณะ / หน่วยงาน</label>
                        <select name="department_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($deptId) ? 'selected' : '' }}>-- ทุกคณะ / หน่วยงาน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" {{ (isset($deptId) && $deptId == $dept->department_id) ? 'selected' : '' }}>
                                    {{ $dept->department_nameTH }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 3: IP Category -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-award text-warning me-1"></i> ประเภททรัพย์สิน</label>
                        <select name="dipt_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($diptId) ? 'selected' : '' }}>-- ทุกประเภท --</option>
                            @foreach($dipTypes as $dt)
                                <option value="{{ $dt->dipt_id }}" {{ (isset($diptId) && $diptId == $dt->dipt_id) ? 'selected' : '' }}>
                                    {{ $dt->dipt_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 4: Registration Status -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-check-all text-success me-1"></i> สถานะการจดทะเบียน</label>
                        <select name="reg_status" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($regStatus) ? 'selected' : '' }}>-- ทุกสถานะ --</option>
                            <option value="registered" {{ (isset($regStatus) && $regStatus === 'registered') ? 'selected' : '' }}>จดทะเบียนแล้ว (มีเลข)</option>
                            <option value="pending" {{ (isset($regStatus) && $regStatus === 'pending') ? 'selected' : '' }}>อยู่ระหว่างยื่นขอ</option>
                        </select>
                    </div>

                    <!-- Filter 5: File Availability -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-file-earmark-check text-info me-1"></i> ไฟล์เอกสารแนบ</label>
                        <select name="has_file" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($hasFile) ? 'selected' : '' }}>-- ทั้งหมด --</option>
                            <option value="1" {{ (isset($hasFile) && $hasFile === '1') ? 'selected' : '' }}>มีไฟล์เอกสาร</option>
                            <option value="0" {{ (isset($hasFile) && $hasFile === '0') ? 'selected' : '' }}>ไม่มีไฟล์แนบ</option>
                        </select>
                    </div>

                {{-- ========================================================= --}}
                {{-- 6. Researchers & Faculty Matrix Filters                   --}}
                {{-- ========================================================= --}}
                @elseif(request()->routeIs('frontend.dashboard.researchers'))
                    <!-- Filter 1: Faculty / Department -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-building text-primary me-1"></i> คณะ / หน่วยงาน</label>
                        <select name="department_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($deptId) ? 'selected' : '' }}>-- ทุกคณะ / หน่วยงาน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" {{ (isset($deptId) && $deptId == $dept->department_id) ? 'selected' : '' }}>
                                    {{ $dept->department_nameTH }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 2: Academic Prefix / Title -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-mortarboard text-primary me-1"></i> ตำแหน่งทางวิชาการ</label>
                        <select name="prefix_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($prefixId) ? 'selected' : '' }}>-- ทุกคำนำหน้า --</option>
                            @foreach($prefixes as $pf)
                                <option value="{{ $pf->prefix_id }}" {{ (isset($prefixId) && $prefixId == $pf->prefix_id) ? 'selected' : '' }}>
                                    {{ $pf->prefix_nameTH }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 3: International Profiles (Scopus/ORCID) -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-globe2 text-info me-1"></i> ฐานข้อมูลสากล</label>
                        <select name="profile_tier" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($profileTier) ? 'selected' : '' }}>-- ทั้งหมด --</option>
                            <option value="both" {{ (isset($profileTier) && $profileTier === 'both') ? 'selected' : '' }}>มีทั้ง Scopus & ORCID</option>
                            <option value="scopus" {{ (isset($profileTier) && $profileTier === 'scopus') ? 'selected' : '' }}>มี Scopus Author ID</option>
                            <option value="orcid" {{ (isset($profileTier) && $profileTier === 'orcid') ? 'selected' : '' }}>มี ORCID ID</option>
                        </select>
                    </div>

                    <!-- Filter 4: Project Track Record -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-folder2 text-primary me-1"></i> ประวัติโครงการวิจัย</label>
                        <select name="has_projects" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($hasProjects) ? 'selected' : '' }}>-- ทั้งหมด --</option>
                            <option value="1" {{ (isset($hasProjects) && $hasProjects === '1') ? 'selected' : '' }}>มีโครงการวิจัยในระบบ</option>
                            <option value="0" {{ (isset($hasProjects) && $hasProjects === '0') ? 'selected' : '' }}>ยังไม่มีโครงการวิจัย</option>
                        </select>
                    </div>

                    <!-- Filter 5: Publication Track Record -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-journal-text text-primary me-1"></i> ประวัติตีพิมพ์</label>
                        <select name="has_publications" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($hasPublications) ? 'selected' : '' }}>-- ทั้งหมด --</option>
                            <option value="1" {{ (isset($hasPublications) && $hasPublications === '1') ? 'selected' : '' }}>มีผลงานตีพิมพ์ในระบบ</option>
                            <option value="0" {{ (isset($hasPublications) && $hasPublications === '0') ? 'selected' : '' }}>ยังไม่มีผลงานตีพิมพ์</option>
                        </select>
                    </div>
                @elseif(request()->routeIs('frontend.dashboard.qa'))
                    <input type="hidden" name="tab" value="{{ $activeTab ?? 'pub' }}">

                    <!-- Filter 1: Year (ปีงบประมาณ / ปีการศึกษา) -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-calendar3 text-primary me-1"></i> ปีงบประมาณ / ปีการศึกษา</label>
                        <select name="year_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($selectedYearId) ? 'selected' : '' }}>-- ทุกปีงบประมาณ / ปีการศึกษา --</option>
                            @foreach($years as $y)
                                <option value="{{ $y->year_id }}" {{ (isset($selectedYearId) && $selectedYearId == $y->year_id) ? 'selected' : '' }}>
                                    ปี พ.ศ. {{ $y->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 2: Calculation Scope (ขอบเขตการคำนวณ) -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-diagram-3-fill text-info me-1"></i> ขอบเขตข้อมูล (Scope)</label>
                        <select name="scope" class="form-select form-select-sm fw-semibold">
                            <option value="faculty" {{ (!isset($scope) || $scope === 'faculty') ? 'selected' : '' }}>🎓 เฉพาะคณะวิชาการ (Faculties Only)</option>
                            <option value="all" {{ (isset($scope) && $scope === 'all') ? 'selected' : '' }}>🏛️ ทั้งหมด (รวมสำนัก/สถาบัน/ส่วนกลาง)</option>
                        </select>
                    </div>

                    <!-- Filter 3: Faculty / Department -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <label class="form-label text-body-secondary small fw-medium mb-1"><i class="bi bi-building text-primary me-1"></i> คณะ / หน่วยงาน</label>
                        <select name="department_id" class="form-select form-select-sm fw-medium">
                            <option value="all" {{ empty($deptId) ? 'selected' : '' }}>-- ทุกหน่วยงานในขอบเขต --</option>
                            @php
                                $facIds = [2, 3, 4, 5, 6, 13];
                                $facList = $departments->whereIn('department_id', $facIds);
                                $otherList = $departments->whereNotIn('department_id', $facIds);
                            @endphp
                            <optgroup label="คณะวิชาการ (6 คณะ)">
                                @foreach($facList as $dept)
                                    <option value="{{ $dept->department_id }}" {{ (isset($deptId) && $deptId == $dept->department_id) ? 'selected' : '' }}>
                                        {{ $dept->department_nameTH }}
                                    </option>
                                @endforeach
                            </optgroup>
                            @if(isset($scope) && $scope === 'all')
                            <optgroup label="หน่วยงานส่วนกลาง / สำนัก / สถาบัน">
                                @foreach($otherList as $dept)
                                    <option value="{{ $dept->department_id }}" {{ (isset($deptId) && $deptId == $dept->department_id) ? 'selected' : '' }}>
                                        {{ $dept->department_nameTH }}
                                    </option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                    </div>
                @endif

                <!-- Submit & Reset Action Buttons -->
                <div class="col-12 col-lg-auto d-flex align-items-center gap-1.5 ms-lg-auto">
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3 shadow-xs d-inline-flex align-items-center gap-1">
                        <i class="bi bi-funnel-fill me-1.5"></i> กรองข้อมูล
                    </button>
                    @if(isset($activeFilterCount) && $activeFilterCount > 0)
                        <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center" title="ล้างค่าทั้งหมด">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>

            </div>
        </form>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('dashboardFiltersForm');
    if (!form) return;

    const ptSelect = document.getElementById('filter_pt_id');

    // Auto-submit on change for standard single selects in the filter form
    form.querySelectorAll('select:not([multiple])').forEach(function(select) {
        select.addEventListener('change', function() {
            // If year was changed, reset pt_id to 'all' so it doesn't send mismatched type ID
            if (this.name === 'year_id' && ptSelect) {
                ptSelect.value = 'all';
                ptSelect.disabled = true; // prevent sending stale pt_id
            }
            form.submit();
        });
    });

    // Handle Utilizations Dashboard: Multi-select for Changwat & Cascading Amphoe
    const changwatSelect = document.getElementById('filter_changwat');
    const amphoeSelect = document.getElementById('filter_amphoe');

    if (changwatSelect && typeof TomSelect !== 'undefined') {
        let tsAmphoe = null;
        if (amphoeSelect) {
            tsAmphoe = new TomSelect(amphoeSelect, {
                plugins: ['remove_button', 'clear_button'],
                maxItems: null,
                placeholder: amphoeSelect.getAttribute('placeholder') || 'เลือกอำเภอ (เลือกได้มากกว่า 1)...',
                closeAfterSelect: false,
                onDropdownClose: function() {
                    debounceSubmit(600);
                },
                onItemRemove: function() {
                    debounceSubmit(800);
                }
            });
        }

        let submitTimer = null;
        function debounceSubmit(delay = 1000) {
            clearTimeout(submitTimer);
            submitTimer = setTimeout(() => {
                if (tsAmphoe && tsAmphoe.isOpen) return;
                if (tsChangwat && tsChangwat.isOpen) return;
                form.submit();
            }, delay);
        }

        const tsChangwat = new TomSelect(changwatSelect, {
            plugins: ['remove_button', 'clear_button'],
            maxItems: null,
            placeholder: changwatSelect.getAttribute('placeholder') || 'เลือกจังหวัด (เลือกได้หลายจังหวัด)...',
            closeAfterSelect: false,
            onChange: function(values) {
                if (!tsAmphoe) return;
                const count = values.length;
                if (count === 1) {
                    const selectedProv = values[0];
                    tsAmphoe.enable();
                    fetch('{{ route("frontend.dashboard.amphoes") }}?changwat=' + encodeURIComponent(selectedProv))
                        .then(r => r.json())
                        .then(amphoes => {
                            tsAmphoe.clear();
                            tsAmphoe.clearOptions();
                            amphoes.forEach(a => {
                                tsAmphoe.addOption({ value: a, text: a });
                            });
                            tsAmphoe.settings.placeholder = 'เลือกอำเภอ (เลือกได้มากกว่า 1)...';
                            tsAmphoe.input.placeholder = 'เลือกอำเภอ (เลือกได้มากกว่า 1)...';
                            tsAmphoe.refreshOptions(false);
                        })
                        .catch(() => {});
                } else {
                    tsAmphoe.clear();
                    tsAmphoe.clearOptions();
                    tsAmphoe.disable();
                    const msg = count > 1 ? `เลือกได้เมื่อระบุ 1 จังหวัดเท่านั้น (เลือก ${count} จังหวัด)` : 'เลือก 1 จังหวัดก่อนเพื่อกรองอำเภอ';
                    tsAmphoe.settings.placeholder = msg;
                    tsAmphoe.input.placeholder = msg;
                }
            },
            onDropdownClose: function() {
                debounceSubmit(600);
            },
            onItemRemove: function() {
                debounceSubmit(800);
            }
        });
    }

    // Handle collapse icon rotation
    const collapseEl = document.getElementById('intelligenceFiltersCollapse');
    const iconEl = document.querySelector('.filter-collapse-icon');
    if (collapseEl && iconEl) {
        collapseEl.addEventListener('show.bs.collapse', function () {
            iconEl.classList.add('rotate-180');
        });
        collapseEl.addEventListener('hide.bs.collapse', function () {
            iconEl.classList.remove('rotate-180');
        });
    }
});
</script>
<style>
.filter-collapse-icon {
    transition: transform 0.25s ease-in-out;
    display: inline-block;
}
.filter-collapse-icon.rotate-180 {
    transform: rotate(180deg);
}
.ts-wrapper.form-select-sm .ts-control {
    min-height: 31px;
    padding: 2px 8px;
    font-size: 0.82rem;
    border-radius: var(--bs-border-radius-sm, 0.25rem);
}
.ts-wrapper .ts-control .item {
    background-color: rgba(220, 38, 38, 0.1);
    color: #DC2626;
    border: 1px solid rgba(220, 38, 38, 0.25);
    border-radius: 4px;
    font-size: 0.75rem;
    padding: 0 5px;
    margin: 1px 3px 1px 0;
}
.ts-wrapper.disabled .ts-control {
    background-color: var(--bs-secondary-bg, #e9ecef);
    opacity: 0.75;
    cursor: not-allowed;
}
</style>
