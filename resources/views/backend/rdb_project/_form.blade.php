{{-- TomSelect CSS is loaded in app layout --}}
@php
    $isEdit = isset($project) && $project->pro_id;
@endphp

<div class="card shadow-sm border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> {{ $isEdit ? 'แก้ไขข้อมูลโครงการวิจัย' : 'เพิ่มโครงการวิจัย' }}</h5>
        @if($isEdit)
        <small>{!! $project->pro_nameTH !!}</small>
        @endif
    </div>
    <div class="card-body">
        <div class="rdb-project-form">
            {{-- Row 1: ปีงบประมาณ, ประเภททุน, ประเภทโครงการย่อย --}}
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="year_id" class="form-label">ปีงบประมาณ <span class="text-danger">*</span></label>
                    <select class="form-select" id="year_id" name="year_id" required>
                        <option value="">เลือกปีงบประมาณ...</option>
                        @foreach($years as $year)
                            <option value="{{ $year->year_id }}" {{ (old('year_id', $project->year_id ?? '') == $year->year_id) ? 'selected' : '' }}>
                                {{ $year->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="pt_id" class="form-label">ประเภททุนอุดหนุนการวิจัย</label>
                    <select class="form-select" id="pt_id" name="pt_id">
                        <option value="">เลือกปีงบประมาณก่อน...</option>
                        {{-- Will be loaded via AJAX based on year_id --}}
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="pts_id" class="form-label">ประเภทโครงการย่อย</label>
                    <select class="form-select" id="pts_id" name="pts_id">
                        <option value="">เลือกประเภทโครงการย่อย...</option>
                        {{-- Will be loaded via AJAX based on pt_id --}}
                    </select>
                </div>
            </div>

            {{-- Row 2: รหัสโครงการ, กลุ่มโครงการ, งบประมาณ --}}
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="pro_code" class="form-label">รหัสโครงการ</label>
                    <input type="text" class="form-control" id="pro_code" name="pro_code" 
                           value="{{ old('pro_code', $project->pro_code ?? '') }}" maxlength="50">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="pgroup_id" class="form-label">กลุ่มโครงการ</label>
                    <select class="form-select" id="pgroup_id" name="pgroup_id">
                        <option value="">เลือกกลุ่มโครงการ...</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->pgroup_id }}" {{ (old('pgroup_id', $project->pgroup_id ?? '') == $group->pgroup_id) ? 'selected' : '' }}>
                                {{ $group->pgroup_nameTH }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="pro_budget" class="form-label">งบประมาณ (บาท)</label>
                    <input type="text" class="form-control budget-input" id="pro_budget" name="pro_budget" 
                           value="{{ old('pro_budget', $project->pro_budget ?? '0') }}"
                           onclick="this.select()">
                </div>
            </div>

            {{-- Row 3: แผนงานโครงการวิจัย (แสดงเมื่อ pgroup_id == 2) --}}
            <div class="row" id="progroup_row" style="display: none;">
                <div class="col-12 mb-3">
                    <label for="pro_group" class="form-label">แผนงานโครงการวิจัย</label>
                    <select class="form-select" id="pro_group" name="pro_group">
                        <option value="">เลือกแผนงานโครงการวิจัย...</option>
                        {{-- Will be loaded via AJAX --}}
                    </select>
                </div>
            </div>

            {{-- Row 4: ชื่อโครงการ TH/EN --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pro_nameTH" class="form-label">ชื่อโครงการวิจัย (ภาษาไทย) <span class="text-danger">*</span></label>
                    <textarea class="form-control ckeditor-basic" id="pro_nameTH" name="pro_nameTH">{{ old('pro_nameTH', $project->pro_nameTH ?? '') }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="pro_nameEN" class="form-label">ชื่อโครงการวิจัย (ภาษาอังกฤษ)</label>
                    <textarea class="form-control ckeditor-basic" id="pro_nameEN" name="pro_nameEN">{{ old('pro_nameEN', $project->pro_nameEN ?? '') }}</textarea>
                </div>
            </div>

            {{-- Row 5: สำหรับ Create - นักวิจัย, สัดส่วน, ตำแหน่ง --}}
            @if(!$isEdit)
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label for="researcher_id" class="form-label">นักวิจัย <span class="text-danger">*</span></label>
                    <select class="form-select" id="researcher_id" name="researcher_id" required>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="ratio" class="form-label">สัดส่วน (%)</label>
                    <input type="number" class="form-control" id="ratio" name="ratio" value="100" min="0" max="100">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="position_id" class="form-label">ตำแหน่งในโครงการ</label>
                    <select class="form-select" id="position_id" name="position_id">
                        @foreach($positions ?? [] as $position)
                            <option value="{{ $position->position_id }}" {{ (old('position_id') == $position->position_id || $position->position_id == 2) ? 'selected' : '' }}>
                                {{ $position->position_nameTH }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            {{-- Row 5b: สำหรับ Edit - คณะ, สาขาทางวิชาการ --}}
            @if($isEdit)
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="department_id" class="form-label">คณะ/หน่วยงาน</label>
                    <select class="form-select" id="department_id" name="department_id">
                        <option value="">เลือกคณะ/หน่วยงาน...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}" {{ (old('department_id', $project->department_id ?? '') == $dept->department_id) ? 'selected' : '' }}>
                                {{ $dept->department_nameTH ?? $dept->department_nameEN }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="depcat_id" class="form-label">สาขาทางวิชาการ</label>
                    <select class="form-select" id="depcat_id" name="depcat_id">
                        <option value="">เลือกสาขาทางวิชาการ...</option>
                        @foreach($categories as $cat)
                             <option value="{{ $cat->depcat_id }}" {{ (old('depcat_id', $project->depcat_id ?? '') == $cat->depcat_id) ? 'selected' : '' }}>
                                {{ $cat->depcat_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            {{-- Row 6: บทคัดย่อ (Thai + English) --}}
            @php
                // Split existing abstract into Thai and English parts
                $abstractParts = isset($project->pro_abstract) ? explode('<br><br><br><br>', $project->pro_abstract, 2) : ['', ''];
                $abstractTH = old('pro_abstract_th', $abstractParts[0] ?? '');
                $abstractEN = old('pro_abstract_en', $abstractParts[1] ?? '');
            @endphp
            <div class="row">
                <div class="col-12 mb-3">
                    <label for="pro_abstract_th" class="form-label">บทคัดย่อ (ภาษาไทย)</label>
                    <textarea class="form-control ckeditor-standard" id="pro_abstract_th" name="pro_abstract_th">{{ $abstractTH }}</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mb-3">
                    <label for="pro_abstract_en" class="form-label">บทคัดย่อ (ภาษาอังกฤษ)</label>
                    <textarea class="form-control ckeditor-standard" id="pro_abstract_en" name="pro_abstract_en">{{ $abstractEN }}</textarea>
                </div>
            </div>

            {{-- Row 7: คำสำคัญ, ระยะเวลา --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pro_keyword_input" class="form-label">คำสำคัญ</label>
                    <div class="keyword-input-container form-control d-flex flex-wrap align-items-center gap-1" style="min-height: 38px; cursor: text;" onclick="document.getElementById('pro_keyword_input').focus()">
                        <div id="pro-keyword-badges" class="d-flex flex-wrap gap-1"></div>
                        <input type="text" id="pro_keyword_input" class="border-0 flex-grow-1" style="outline: none; min-width: 150px;" placeholder="พิมพ์แล้วกด Enter หรือ ,">
                    </div>
                    <input type="hidden" id="pro_keyword" name="pro_keyword" value="{{ old('pro_keyword', $project->pro_keyword ?? '') }}">
                    <small class="text-muted">กด Enter หรือ , เพื่อเพิ่มคำ • คลิกที่ badge เพื่อลบ</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="pro_date_start" class="form-label">วันที่เริ่มต้น</label>
                    <input type="text" class="form-control datepicker" id="pro_date_start" name="pro_date_start" 
                           value="{{ old('pro_date_start', $project->pro_date_start ?? date('Y-m-d')) }}" placeholder="วว/ดด/ปปปป">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="pro_date_end" class="form-label">วันที่สิ้นสุด</label>
                    <input type="text" class="form-control datepicker" id="pro_date_end" name="pro_date_end" 
                           value="{{ old('pro_date_end', $project->pro_date_end ?? (date('m') >= 10 ? (date('Y')+1).'-09-30' : date('Y').'-09-30')) }}" placeholder="วว/ดด/ปปปป">
                </div>
            </div>

            {{-- Row 8: หมายเหตุ --}}
            <div class="row">
                <div class="col-12 mb-3">
                    <label for="pro_note" class="form-label">หมายเหตุ</label>
                    <input type="text" class="form-control" id="pro_note" name="pro_note" 
                           value="{{ old('pro_note', $project->pro_note ?? '') }}" maxlength="255">
                </div>
            </div>

            {{-- Row 9: สถานะ (สำหรับ Create) --}}
            @if(!$isEdit)
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ps_id" class="form-label">สถานะโครงการ</label>
                    <select class="form-select" id="ps_id" name="ps_id">
                        @foreach($statuses as $status)
                            <option value="{{ $status->ps_id }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $status->ps_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            {{-- Buttons --}}
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-{{ $isEdit ? 'primary' : 'success' }}">
                    <i class="bi bi-save"></i> บันทึกข้อมูล
                </button>
                <a href="{{ $isEdit ? route('backend.rdb_project.show', $project->pro_id) : route('backend.rdb_project.index') }}" class="btn btn-warning">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@push('scripts')
<!-- TomSelect CDN -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    @include('layouts.partials.ckeditor_setup')
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle progroup row based on pgroup_id
    const pgroupSelect = document.getElementById('pgroup_id');
    const progroupRow = document.getElementById('progroup_row');
    
    function toggleProgroupRow() {
        if (pgroupSelect && pgroupSelect.value == '2') {
            progroupRow.style.display = 'flex';
        } else if (progroupRow) {
            progroupRow.style.display = 'none';
        }
    }
    
    if (pgroupSelect) {
        pgroupSelect.addEventListener('change', function() {
            toggleProgroupRow();
            
            // Auto-set position_id based on pgroup_id
            // Case 1: แผนงานวิจัย (id=1) -> ผู้อำนวยการแผนงานวิจัย (id=1)
            // Case Other -> หัวหน้าโครงการ (id=2)
            const positionSelect = document.getElementById('position_id');
            if (positionSelect) {
                if (this.value == '1') {
                    // แผนงานวิจัย -> ผู้อำนวยการแผนงานวิจัย
                    positionSelect.value = '1';
                } else {
                    // อื่นๆ หรือ ไม่เลือก -> หัวหน้าโครงการ
                    positionSelect.value = '2';
                }
            }
        });
        toggleProgroupRow(); // Initial check
    }

    // Format budget input
    const budgetInput = document.getElementById('pro_budget');
    if (budgetInput) {
        budgetInput.addEventListener('blur', function() {
            let value = this.value.replace(/,/g, '');
            if (!isNaN(value) && value !== '') {
                this.value = parseFloat(value).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        });
        budgetInput.addEventListener('focus', function() {
            this.value = this.value.replace(/,/g, '');
        });
    }

    // Initialize TomSelect for researcher search
    const researcherSelect = document.getElementById('researcher_id');
    if (researcherSelect && typeof TomSelect !== 'undefined') {
        new TomSelect(researcherSelect, {
            create: false,
            openOnFocus: true,
            persist: false,
            maxOptions: 10,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            placeholder: '-- พิมพ์เพื่อค้นหานักวิจัย --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                
                fetch('{{ route("backend.rdb_project.search_researchers") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => {
                        callback(json);
                    })
                    .catch(() => {
                        callback();
                    });
            },
            render: {
                option: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                },
                no_results: function(data, escape) {
                    return '<div class="no-results p-2 text-muted">ไม่พบข้อมูล (พิมพ์อย่างน้อย 2 ตัวอักษร)</div>';
                }
            }
        });
    }

    // Initialize Flatpickr for Thai Buddhist Date
    if (typeof flatpickr !== 'undefined') {
        flatpickr.l10ns.th.firstDayOfWeek = 0; // Sunday start
        
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d", // Value sent to server
            altInput: true,
            altFormat: "j F Y", // Format for display
            locale: "th",
            disableMobile: true, // Force custom picker on mobile
            formatDate: (date, format, locale) => {
                // Return standard format if not the display format
                // This ensures "Y-m-d" for value attribute remains correct
                if (format !== "j F Y") {
                     return flatpickr.formatDate(date, format, locale);
                }
                
                // For display (altInput), convert Year to Buddhist Era
                const buddhistYear = date.getFullYear() + 543;
                return flatpickr.formatDate(date, "j F", locale) + " " + buddhistYear;
            },
            onMonthChange: function(selectedDates, dateStr, instance) {
                 adjustCalendarYear(instance);
            },
            onYearChange: function(selectedDates, dateStr, instance) {
                 adjustCalendarYear(instance);
            },
            onOpen: function(selectedDates, dateStr, instance) {
                 adjustCalendarYear(instance);
            },
            onReady: function(selectedDates, dateStr, instance) {
                // If this is the START date and it has a value, try to update the END date
                if (instance.element.id === 'pro_date_start' && selectedDates.length > 0) {
                    const endDateInput = document.getElementById('pro_date_end');
                    // Only update if end date instance is already ready
                    if (endDateInput && endDateInput._flatpickr) {
                        endDateInput._flatpickr.set('minDate', selectedDates[0]);
                    }
                }
                
                // If this is the END date, check if START date has a value and apply restriction
                if (instance.element.id === 'pro_date_end') {
                    const startDateInput = document.getElementById('pro_date_start');
                    if (startDateInput && startDateInput._flatpickr && startDateInput._flatpickr.selectedDates.length > 0) {
                        instance.set('minDate', startDateInput._flatpickr.selectedDates[0]);
                    }
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                // When start date changes, update end date's minDate
                if (instance.element.id === 'pro_date_start') {
                     const endDateInput = document.getElementById('pro_date_end');
                     if (endDateInput && endDateInput._flatpickr) {
                         endDateInput._flatpickr.set('minDate', selectedDates[0] || null);
                         
                         // Optional: Clear end date if it's now invalid (less than start date)
                         // But usually setting minDate just disables previous dates. 
                         // If we want to be strict, we can check value.
                         const currentEndDate = endDateInput._flatpickr.selectedDates[0];
                         if (currentEndDate && selectedDates.length > 0 && currentEndDate < selectedDates[0]) {
                             endDateInput._flatpickr.clear();
                         }
                     }
                }
            }
        });

        // Function to hack the year display in the calendar header to allow showing BE
        // Note: This is purely visual. The underlying logic uses AD.
        function adjustCalendarYear(instance) {
            setTimeout(() => {
                const yearInput = instance.currentYearElement;
                if (yearInput) {
                     // Get the ACTUAL Gregorian year from the instance
                     const currentYear = instance.currentYear;
                     const buddhistYear = currentYear + 543;
                     
                     // Visually update the input value
                     if (yearInput.value != buddhistYear) {
                        yearInput.value = buddhistYear;
                     }
                }
            }, 0);
        }


    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // -------------------------------------------------------------------------
    // Chain 1: Department (No longer chains to Course/Major)
    // -------------------------------------------------------------------------
    // Logic for loading Courses/Majors removed as per requirement to replace with Academic Branch (Category)
    


    // -------------------------------------------------------------------------
    // Chain 2: Year -> Project Type -> Project Type Sub
    // -------------------------------------------------------------------------
    const yearSelect = document.getElementById('year_id');
    const typeSelect = document.getElementById('pt_id');
    const subTypeSelect = document.getElementById('pts_id');
    const proGroupSelect = document.getElementById('pro_group'); // Added based on context from deleted code

    // Pre-selected values
    const selectedType    = "{{ old('pt_id', $project->pt_id ?? '') }}";
    const selectedSubType = "{{ old('pts_id', $project->pts_id ?? '') }}";
    const selectedProGroup = "{{ old('pro_group', $project->pro_group ?? '') }}";

    function loadProjectTypes(yearId, preSelectType = '', preSelectGroup = '') {
        if (typeSelect) typeSelect.innerHTML = '<option value="">กำลังโหลด...</option>';
        if (subTypeSelect) subTypeSelect.innerHTML = '<option value="">เลือกประเภทโครงการย่อย...</option>';
        if (proGroupSelect) proGroupSelect.innerHTML = '<option value="">เลือกแผนงานโครงการวิจัย...</option>';

        if(!yearId) {
            if (typeSelect) typeSelect.innerHTML = '<option value="">เลือกประเภททุนอุดหนุนการวิจัย...</option>';
            return;
        }

        // Load Project Types
        if (typeSelect) {
            fetch('{{ route("backend.rdb_project.search_project_type") }}?year_id=' + yearId)
                .then(res => res.json())
                .then(data => {
                    let options = '<option value="">เลือกประเภททุนอุดหนุนการวิจัย...</option>';
                    (data.results || []).forEach(item => {
                        const isSelected = String(item.id) === String(preSelectType) ? 'selected' : '';
                        options += `<option value="${item.id}" ${isSelected}>${item.text}</option>`;
                    });
                    typeSelect.innerHTML = options;

                    if (preSelectType) {
                        loadProjectTypeSubs(preSelectType, selectedSubType);
                    }
                })
                .catch(err => {
                    console.error(err);
                    typeSelect.innerHTML = '<option value="">เกิดข้อผิดพลาด</option>';
                });
        }
        
        // Load Project Groups (if element exists)
        if (proGroupSelect) {
             fetch('{{ route("backend.rdb_project.search_pro_group") }}?year_id=' + yearId)
                .then(res => res.json())
                .then(data => {
                    let options = '<option value="">เลือกแผนงานโครงการวิจัย...</option>';
                    (data.results || []).forEach(item => {
                         const isSelected = String(item.id) === String(preSelectGroup) ? 'selected' : '';
                        options += `<option value="${item.id}" ${isSelected}>${item.text}</option>`;
                    });
                    proGroupSelect.innerHTML = options;
                })
                .catch(err => {
                    console.error(err);
                    proGroupSelect.innerHTML = '<option value="">เกิดข้อผิดพลาด</option>';
                });
        }
    }

    function loadProjectTypeSubs(ptId, preSelect = '') {
        if (!subTypeSelect) return;
        subTypeSelect.innerHTML = '<option value="">กำลังโหลด...</option>';

        if(!ptId) {
            subTypeSelect.innerHTML = '<option value="">เลือกประเภทโครงการย่อย...</option>';
            return;
        }

        fetch('{{ route("backend.rdb_project.search_project_type_sub") }}?pt_id=' + ptId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">เลือกประเภทโครงการย่อย...</option>';
                (data.results || []).forEach(item => {
                    const isSelected = String(item.id) === String(preSelect) ? 'selected' : '';
                    options += `<option value="${item.id}" ${isSelected}>${item.text}</option>`;
                });
                subTypeSelect.innerHTML = options;
            })
            .catch(err => {
                console.error(err);
                subTypeSelect.innerHTML = '<option value="">เกิดข้อผิดพลาด</option>';
            });
    }

    if(yearSelect) {
        yearSelect.addEventListener('change', function() {
            loadProjectTypes(this.value);
        });
    }

    if(typeSelect) {
        typeSelect.addEventListener('change', function() {
            loadProjectTypeSubs(this.value);
        });
    }

    if (yearSelect && yearSelect.value) {
        loadProjectTypes(yearSelect.value, selectedType, selectedProGroup);
    }

    // Keyword Tag Input System
    const keywordInput = document.getElementById('pro_keyword_input');
    const keywordHidden = document.getElementById('pro_keyword');
    const badgesContainer = document.getElementById('pro-keyword-badges');
    
    if (keywordInput && keywordHidden && badgesContainer) {
        let keywords = [];
        
        // Initialize from existing value
        if (keywordHidden.value.trim()) {
            keywords = keywordHidden.value.split(',')
                .map(k => k.trim())
                .filter(k => k.length > 0);
            keywords = [...new Set(keywords)]; // Remove duplicates
            renderBadges();
        }
        
        function renderBadges() {
            badgesContainer.innerHTML = '';
            keywords.forEach((keyword, index) => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-secondary d-flex align-items-center';
                badge.style.cursor = 'pointer';
                badge.innerHTML = `${keyword} <i class="bi bi-x-circle ms-1"></i>`;
                badge.title = 'คลิกเพื่อลบ';
                badge.onclick = () => removeKeyword(index);
                badgesContainer.appendChild(badge);
            });
            keywordHidden.value = keywords.join(', ');
        }
        
        function addKeyword(value) {
            const trimmed = value.trim();
            if (trimmed && !keywords.includes(trimmed)) {
                keywords.push(trimmed);
                renderBadges();
            }
            keywordInput.value = '';
        }
        
        function removeKeyword(index) {
            keywords.splice(index, 1);
            renderBadges();
        }
        
        keywordInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addKeyword(this.value);
            }
            // Backspace on empty input removes last badge
            if (e.key === 'Backspace' && !this.value && keywords.length > 0) {
                keywords.pop();
                renderBadges();
            }
        });
        
        keywordInput.addEventListener('blur', function() {
            if (this.value.trim()) {
                const parts = this.value.split(',').map(k => k.trim()).filter(k => k.length > 0);
                parts.forEach(p => {
                    if (!keywords.includes(p)) {
                        keywords.push(p);
                    }
                });
                keywords = [...new Set(keywords)];
                renderBadges();
                this.value = '';
            }
        });
        
        keywordInput.addEventListener('paste', function() {
            setTimeout(() => {
                const parts = this.value.split(',').map(k => k.trim()).filter(k => k.length > 0);
                parts.forEach(p => {
                    if (!keywords.includes(p)) {
                        keywords.push(p);
                    }
                });
                keywords = [...new Set(keywords)];
                renderBadges();
                this.value = '';
            }, 100);
        });
    }
});
</script>
@endpush
