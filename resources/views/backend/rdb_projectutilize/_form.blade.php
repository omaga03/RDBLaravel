{{-- TomSelect CSS is loaded in app layout --}}
@php
    $isEdit = isset($item) && $item->utz_id;
@endphp

        {{-- Row 1: โครงการวิจัย --}}
        <div class="row">
            <div class="col-md-12 mb-3">
                @if($isEdit && $item->project)
                    @php
                        $p = $item->project;
                        $pYear = $p->year->year_name ?? '-';
                        $pName = strip_tags($p->pro_nameTH);
                        $pRes = '-';
                        if($p->rdbProjectWorks->isNotEmpty()) {
                            $mw = $p->rdbProjectWorks->sortBy('position_id')->first();
                            if($mw && $mw->researcher) {
                                $pRes = $mw->researcher->researcher_fname . ' ' . $mw->researcher->researcher_lname;
                            }
                        }
                        $pText = "{$pYear} • {$pName} • {$pRes}";
                    @endphp
                @endif
                
                <x-backend.form.project-search 
                    label="โครงการวิจัยที่นำไปใช้ประโยชน์" 
                    required="true"
                    :selected="$isEdit && $item->project ? $item->project->pro_id : null"
                    :initial-text="$isEdit && $item->project ? $pText : ''"
                />
            </div>
        </div>

        {{-- Row 2: หน่วยงาน + ที่อยู่ --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="utz_department_name" class="form-label">หน่วยงานที่นำไปใช้ประโยชน์ <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="utz_department_name" name="utz_department_name" value="{{ old('utz_department_name', $item->utz_department_name ?? '') }}" required placeholder="ระบุหน่วยงาน...">
            </div>
            <div class="col-md-6 mb-3">
                <label for="utz_department_address" class="form-label">ที่อยู่หน่วยงาน</label>
                <textarea class="form-control" id="utz_department_address" name="utz_department_address" rows="1" placeholder="ระบุที่อยู่...">{{ old('utz_department_address', $item->utz_department_address ?? '') }}</textarea>
            </div>
        </div>

        {{-- Row 3: ที่ตั้ง (Group Search) + QA + รายได้ --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="location_search" class="form-label">ที่ตั้ง (ค้นหา ตําบล, อําเภอ, จังหวัด) <span class="text-danger">*</span></label>
                <select id="location_search" class="form-select" placeholder="พิมพ์ชื่อตําบล อําเภอ หรือจังหวัด...">
                    @if($isEdit && $item->changwat)
                        <option value="{{ $item->chw_id }}" selected>
                            {{ $item->changwat->tambon_t }} {{ $item->changwat->amphoe_t }} {{ $item->changwat->changwat_t }}
                        </option>
                    @endif
                </select>
                <input type="hidden" name="chw_id" id="chw_id" value="{{ old('chw_id', $item->chw_id ?? '') }}">
            </div>
            <div class="col-md-3 mb-3 d-flex align-items-center">
                 <div class="form-check form-switch pt-4">
                    <input class="form-check-input" type="checkbox" role="switch" name="utz_group_qa" value="1" id="utz_group_qa" {{ old('utz_group_qa', $item->utz_group_qa ?? 0) ? 'checked' : '' }}>
                    <label class="form-check-label ms-2" for="utz_group_qa" style="font-size: 0.85rem; line-height: 1.2;">
                        เป็นไปตามเกณฑ์การประกันคุณภาพการศึกษา (QA)
                    </label>
                 </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="utz_budget" class="form-label">รายได้ (บาท)</label>
                <input type="text" class="form-control" id="utz_budget" name="utz_budget" value="{{ old('utz_budget', $item->utz_budget ?? '0') }}">
            </div>
        </div>

        {{-- Row 5: Utilize Type & File Upload --}}
        <div class="row">
             <div class="col-md-6 mb-3">
                <label for="utz_group" class="form-label">ประเภทการใช้ประโยชน์</label>
                <select class="form-select" id="utz_group" name="utz_group[]" multiple>
                    {{-- Loaded via AJAX --}}
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="utz_files_upload" class="form-label">ไฟล์แนบ</label>
                @if($isEdit && $item->utz_files)
                    <div class="mb-2">
                        <small class="text-muted">ไฟล์ปัจจุบัน:</small>
                        <span class="badge bg-secondary me-1">
                            <i class="bi bi-file-earmark-pdf"></i> {{ $item->utz_files }}
                        </span>
                    </div>
                @endif
                <input type="file" class="form-control" id="utz_files_upload" name="utz_files_upload" accept=".pdf">
                <small class="text-muted">รองรับไฟล์ PDF เท่านั้น</small>
            </div>
        </div>

        {{-- Row 6: Detail --}}
        <div class="row">
            <div class="col-12 mb-3">
                <label for="utz_detail" class="form-label">รายละเอียดการนำไปใช้ประโยชน์</label>
                <textarea class="form-control ckeditor-standard" id="utz_detail" name="utz_detail">{{ old('utz_detail', $item->utz_detail ?? '') }}</textarea>
            </div>
        </div>

        {{-- Row 7: Date, Leader, Position --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="utz_date" class="form-label">วันที่นำไปใช้ประโยชน์ <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="utz_date" name="utz_date" value="{{ old('utz_date', $item->utz_date ?? date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="utz_leading" class="form-label">ชื่อผู้นำไปใช้ประโยชน์</label>
                <input type="text" class="form-control" id="utz_leading" name="utz_leading" value="{{ old('utz_leading', $item->utz_leading ?? '') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label for="utz_leading_position" class="form-label">ตำแหน่ง</label>
                <input type="text" class="form-control" id="utz_leading_position" name="utz_leading_position" value="{{ old('utz_leading_position', $item->utz_leading_position ?? '') }}">
            </div>
        </div>

        {{-- Buttons --}}
        <div class="form-group mt-4 border-top pt-3">
            <button type="submit" class="btn btn-{{ $isEdit ? 'primary' : 'success' }}">
                <i class="bi bi-save"></i> บันทึกข้อมูล
            </button>
            <a href="{{ $isEdit ? route('backend.rdbprojectutilize.show', $item->utz_id) : route('backend.rdbprojectutilize.index') }}" class="btn btn-warning">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
        </div>



@push('scripts')
@include('layouts.partials.ckeditor_setup')

{{-- TomSelect CDN, JS, and CSS are now handled by the component or needed for other fields --}}
{{-- We still need TomSelect for utz_group (Multi-select) so we keep the include if not already loaded by component --}}
@if(!defined('TOMSELECT_LOADED'))
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    // -------------------------------------------------------------------------
    // Project Search is now handled by x-backend.form.project-search component
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // TomSelect for Utilize Type (Multi-select)
    // -------------------------------------------------------------------------
    const utzGroupSelect = document.getElementById('utz_group');
    const oldUtzGroup = "{{ old('utz_group', $item->utz_group ?? '') }}";
    
    if (utzGroupSelect && typeof TomSelect !== 'undefined') {
        // Preload utilize types
        fetch('{{ route("backend.rdbprojectutilize.search_utilize_types") }}')
            .then(res => res.json())
            .then(data => {
                const selectedIds = oldUtzGroup ? oldUtzGroup.split(',').map(s => s.trim()) : [];
                
                let options = '';
                data.forEach(t => {
                    const isSelected = selectedIds.includes(String(t.value)) ? 'selected' : '';
                    options += `<option value="${t.value}" ${isSelected}>${t.text}</option>`;
                });
                utzGroupSelect.innerHTML = options;
                
                // Initialize TomSelect after options are loaded
                new TomSelect(utzGroupSelect, {
                    plugins: ['remove_button'],
                    create: false,
                    maxItems: null,
                    placeholder: '-- เลือกประเภทการใช้ประโยชน์ --'
                });
            });
    }

    // -------------------------------------------------------------------------
    // Global Location Search (Tambon > Amphoe > Changwat)
    // -------------------------------------------------------------------------
    const locationSelect = document.getElementById('location_search');
    const chwIdInput = document.getElementById('chw_id');

    if (locationSelect && typeof TomSelect !== 'undefined') {
        new TomSelect(locationSelect, {
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            placeholder: 'พิมพ์ชื่อตําบล อําเภอ หรือจังหวัด...',
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("backend.rdbprojectutilize.search_location") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => {
                        callback(json);
                    }).catch(() => {
                        callback();
                    });
            },
            render: {
                option: function(data, escape) {
                    return '<div>' + (data._highlight || escape(data.text)) + '</div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                }
            },
            onChange: function(value) {
                if (!value) {
                    chwIdInput.value = '';
                    return;
                }
                
                const data = this.options[value];
                if (data) {
                    chwIdInput.value = data.id;
                }
            }
        });
    }

    // -------------------------------------------------------------------------
    // Budget Input Formatting
    // -------------------------------------------------------------------------
    const budgetInput = document.getElementById('utz_budget');
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

    // -------------------------------------------------------------------------
    // Thai Buddhist Date Picker
    // -------------------------------------------------------------------------
    if (typeof initThaiFlatpickr === 'function') {
        initThaiFlatpickr('#utz_date');
    } else if (typeof flatpickr !== 'undefined') {
        flatpickr("#utz_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            locale: "th",
            disableMobile: true,
            formatDate: (date, format, locale) => {
                if (format !== "j F Y") {
                    return flatpickr.formatDate(date, format, locale);
                }
                const buddhistYear = date.getFullYear() + 543;
                return flatpickr.formatDate(date, "j F", locale) + " " + buddhistYear;
            }
        });
    }
});
</script>
@endpush
