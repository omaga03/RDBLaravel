<div class="card shadow-sm mb-4 search-box">
    <div class="card-header py-3">
        <ul class="nav nav-tabs card-header-tabs" id="searchTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link {{ request('search_mode') != 'advanced' ? 'active' : '' }}" id="simple-tab" data-bs-toggle="tab" data-bs-target="#simple-search" type="button" role="tab"><i class="bi bi-search"></i> ค้นหาทั่วไป</button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ request('search_mode') == 'advanced' ? 'active' : '' }}" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced-search" type="button" role="tab"><i class="bi bi-sliders"></i> ค้นหาละเอียด</button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="searchTabsContent">
            <!-- Simple Search -->
            <div class="tab-pane fade {{ request('search_mode') != 'advanced' ? 'show active' : '' }}" id="simple-search" role="tabpanel">
                <form action="{{ route('backend.rdb_published.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" name="q" value="{{ request('q') }}" placeholder="พิมพ์ชื่อผลงาน, ชื่อวารสาร, หรือชื่อนักวิจัย...">
                        <button class="btn btn-primary px-4" type="submit"><i class="bi bi-search"></i> ค้นหา</button>
                        @if(request('q'))
                            <a href="{{ route('backend.rdb_published.index') }}" class="btn btn-secondary px-3" title="ล้างค่า"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Advanced Search -->
            <div class="tab-pane fade {{ request('search_mode') == 'advanced' ? 'show active' : '' }}" id="advanced-search" role="tabpanel">
                <form action="{{ route('backend.rdb_published.index') }}" method="GET" id="advancedForm">
                    <input type="hidden" name="search_mode" value="advanced">
                    
                    <!-- Row 1: Year -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">ประเภทปี</label>
                            <select class="form-select" name="year_type" id="s_year_type">
                                <option value="">-- เลือกประเภทปี --</option>
                                <option value="calendar" {{ request('year_type') == 'calendar' ? 'selected' : '' }}>ปี พ.ศ. (ตีพิมพ์)</option>
                                <option value="budget" {{ request('year_type') == 'budget' ? 'selected' : '' }}>ปีงบประมาณ</option>
                                <option value="education" {{ request('year_type') == 'education' ? 'selected' : '' }}>ปีการศึกษา</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">เลือกปี</label>
                            <select class="form-select" name="year_id" id="s_year_id" disabled>
                                <option value="">-- ทั้งหมด --</option>
                                @foreach($years as $year)
                                    <option value="{{ $year->year_id }}" {{ request('year_id') == $year->year_id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="text-secondary">

                    <!-- Row 2: Type (Hierarchical) -->
                    <div class="row g-3 mb-3">
                         <div class="col-md-4">
                            <label class="form-label">กลุ่มประเภท (Group)</label>
                            <select class="form-select" id="s_pubtype_group" name="pubtype_group">
                                <option value="">-- ทั้งหมด --</option>
                                {{-- populated by JS --}}
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ประเภทหลัก (Type)</label>
                            <select class="form-select" id="s_pubtype_grouptype" name="pubtype_grouptype" disabled>
                                <option value="">-- ทั้งหมด --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ประเภทย่อย (Subtype)</label>
                            <select class="form-select" id="s_pubtype_id" name="pubtype_id" disabled>
                                <option value="">-- ทั้งหมด --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 3: Dept / Branch -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">หน่วยงาน/คณะ</label>
                            <select class="form-select" name="department_id">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_nameTH }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">สาขาทางวิชาการ</label>
                            <select class="form-select" name="branch_id">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach($branches as $br)
                                    <option value="{{ $br->branch_id }}" {{ request('branch_id') == $br->branch_id ? 'selected' : '' }}>{{ $br->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Row 4: Score / Budget -->
                    <div class="row g-3 mb-3">
                         <div class="col-md-4">
                            <label class="form-label">คะแนน (Score)</label>
                            <select class="form-select" name="pub_score">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach(['0.00', '0.20', '0.40', '0.60', '0.80', '1.00'] as $score)
                                    <option value="{{ $score }}" {{ request('pub_score') == $score ? 'selected' : '' }}>{{ $score }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">งบประมาณเริ่มต้น</label>
                            <input type="number" step="0.01" class="form-control" name="budget_min" value="{{ request('budget_min') }}" placeholder="ต่ำสุด">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">งบประมาณสิ้นสุด</label>
                            <input type="number" step="0.01" class="form-control" name="budget_max" value="{{ request('budget_max') }}" placeholder="สูงสุด">
                        </div>
                    </div>

                    <!-- Row 5: Date Range -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ตั้งแต่วันที่</label>
                            <input type="date" class="form-control datepicker" id="s_date_start" name="date_start" value="{{ request('date_start') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ถึงวันที่</label>
                            <input type="date" class="form-control datepicker" id="s_date_end" name="date_end" value="{{ request('date_end') }}">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end mt-3 gap-2">
                         <a href="{{ route('backend.rdb_published.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> ล้างค่า</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> ค้นหา</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 0. Year Logic
    const sYearType = document.getElementById('s_year_type');
    const sYearId = document.getElementById('s_year_id');

    if (sYearType && sYearId) {
        // Init state
        if (sYearType.value) {
            sYearId.disabled = false;
        }

        sYearType.addEventListener('change', function() {
            if (this.value) {
                sYearId.disabled = false;
            } else {
                sYearId.disabled = true;
                sYearId.value = '';
            }
        });
    }

    // 1. PubType Logic
    const pubTypes = @json($pubTypes);
    
    // 2. Datepicker Logic (Thai) with Validation
    if (typeof initThaiFlatpickr === 'function') {
        const startPicker = initThaiFlatpickr('#s_date_start', {
            onChange: function(selectedDates, dateStr, instance) {
                const endInput = document.getElementById('s_date_end');
                if (endInput && endInput._flatpickr) {
                    // 1. Set Min Date constraints
                    endInput._flatpickr.set('minDate', selectedDates[0] || null);

                    // 2. Auto-fill End Date to match Start Date (User Request)
                    // Only if End Date is currently empty or user wants strict one-day selection flow
                    if (selectedDates.length > 0) {
                         endInput._flatpickr.setDate(selectedDates[0]);
                    }
                }
            }
        });

        const endPicker = initThaiFlatpickr('#s_date_end', {
             onReady: function(selectedDates, dateStr, instance) {
                 // Initial check: validation based on current start date value
                 const startInput = document.getElementById('s_date_start');
                 if (startInput && startInput.value) {
                     // We need to parse the existing value if it wasn't picked via flatpickr yet
                     // But initThaiFlatpickr attaches flatpickr, so we can access it
                     if (startInput._flatpickr && startInput._flatpickr.selectedDates.length > 0) {
                         instance.set('minDate', startInput._flatpickr.selectedDates[0]);
                     }
                 }
             }
        });
    } else {
        // Fallback
    }
    
    // Select Elements
    const sGroup = document.getElementById('s_pubtype_group');
    const sType = document.getElementById('s_pubtype_grouptype');
    const sSub = document.getElementById('s_pubtype_id');

    // Values from Request (Old)
    const oldGroup = "{{ request('pubtype_group') }}";
    const oldType = "{{ request('pubtype_grouptype') }}";
    const oldSub = "{{ request('pubtype_id') }}";

    // Extract Unique Groups
    const uniqueGroups = [...new Set(pubTypes.map(item => item.pubtype_group))].filter(Boolean);
    uniqueGroups.forEach(g => {
        sGroup.add(new Option(g, g));
    });

    // Event: Group Change
    sGroup.addEventListener('change', function() {
        const val = this.value;
        sType.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        sSub.innerHTML = '<option value="">-- ทั้งหมด --</option>';
        sSub.disabled = true;

        if (val) {
             const filteredInfo = pubTypes.filter(item => item.pubtype_group === val);
             const subTypes = [...new Set(filteredInfo.map(item => item.pubtype_grouptype))].filter(Boolean);
             subTypes.forEach(t => {
                 sType.add(new Option(t, t));
             });
             sType.disabled = false;
        } else {
            sType.disabled = true;
        }
    });

    // Event: Type Change
    sType.addEventListener('change', function() {
        const gVal = sGroup.value;
        const val = this.value;
        sSub.innerHTML = '<option value="">-- ทั้งหมด --</option>';

        if (val && gVal) {
             const filteredInfo = pubTypes.filter(item => item.pubtype_group === gVal && item.pubtype_grouptype === val);
             filteredInfo.forEach(item => {
                 sSub.add(new Option(item.pubtype_subgroup || item.pubtype_grouptype, item.pubtype_id));
             });
             sSub.disabled = false;
        } else {
            sSub.disabled = true;
        }
    });

    // Init Old Values
    if (oldGroup) {
        sGroup.value = oldGroup;
        sGroup.dispatchEvent(new Event('change'));
        if (oldType) {
            sType.value = oldType;
            sType.dispatchEvent(new Event('change'));
            if (oldSub) {
                sSub.value = oldSub;
            }
        }
    }
});
</script>
