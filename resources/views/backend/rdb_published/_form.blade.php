@php
    $isEdit = isset($item) && $item->id;
    // Split abstract logic
    $abstractParts = isset($item->pub_abstract) ? explode('<br><br><br><br>', $item->pub_abstract, 2) : ['', ''];
    $abstractTH = old('pub_abstract_th', $abstractParts[0] ?? '');
    $abstractEN = old('pub_abstract_en', $abstractParts[1] ?? '');
@endphp

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold"><i class="bi bi-pencil-square"></i> {{ $isEdit ? 'แก้ไขข้อมูลการตีพิมพ์' : 'เพิ่มข้อมูลการตีพิมพ์ใหม่' }}</h6>
        @if($isEdit)
        <small class="text-white-50">{{ $item->pub_name }}</small>
        @endif
    </div>
    <div class="card-body">

        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="pub_name" class="form-label">ชื่อบทความ/ผลงานตีพิมพ์ <span class="text-danger">*</span></label>
                <textarea class="form-control ckeditor-basic" id="pub_name" name="pub_name" rows="2">{{ old('pub_name', $item->pub_name ?? '') }}</textarea>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="pub_abstract_th" class="form-label">บทคัดย่อ</label>
                <textarea class="form-control ckeditor-standard" id="pub_abstract_th" name="pub_abstract_th">{{ $abstractTH }}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="pub_abstract_en" class="form-label">Abstract</label>
                <textarea class="form-control ckeditor-standard" id="pub_abstract_en" name="pub_abstract_en">{{ $abstractEN }}</textarea>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="pub_keyword" class="form-label">คำสำคัญ (Keywords)</label>
                <input type="text" class="form-control" id="pub_keyword" name="pub_keyword" value="{{ old('pub_keyword', $item->pub_keyword ?? '') }}" placeholder="เช่น AI, Machine Learning, Education">
            </div>
        </div>

        <div class="row">
            @if(!$isEdit)
            <div class="col-md-6 mb-3">
                <label for="researcher_id" class="form-label">ผู้แต่งบทความ (Author) <span class="text-danger">*</span></label>
                <select class="form-select" id="researcher_id" name="researcher_id" required>
                    <option value="">-- พิมพ์เพื่อค้นหานักวิจัย --</option>
                    @if(isset($item) && $item->researcher_id)
                        @php $res = \App\Models\RdbResearcher::with(['prefix','department'])->find($item->researcher_id); @endphp
                        @if($res)
                            @php 
                                $prefix = $res->prefix ? $res->prefix->prefix_nameTH : '';
                                $dept = $res->department ? $res->department->department_nameTH : '';
                                $fullname = "{$prefix}{$res->researcher_fname} {$res->researcher_lname} [{$dept}]";
                            @endphp
                            <option value="{{ $res->researcher_id }}" selected>{{ $fullname }}</option>
                        @endif
                    @endif
                </select>
            </div>
            @endif
            <div class="{{ $isEdit ? 'col-md-12' : 'col-md-6' }} mb-3">
                <label for="pro_id" class="form-label">โครงการวิจัยที่เกี่ยวข้อง (ถ้ามี)</label>
                <select class="form-select" id="pro_id" name="pro_id">
                    <option value="">-- พิมพ์เพื่อค้นหาโครงการ --</option>
                    @if(isset($item) && $item->pro_id)
                        @php $pro = \App\Models\RdbProject::find($item->pro_id); @endphp
                        @if($pro)
                            <option value="{{ $pro->pro_id }}" selected>{{ $pro->pro_nameTH }}</option>
                        @endif
                    @endif
                </select>
            </div>
        </div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label for="pub_name_journal" class="form-label">ชื่อวารสาร/แหล่งเผยแพร่ (Journal Name)</label>
        <textarea class="form-control" id="pub_name_journal" name="pub_name_journal" rows="2">{{ old('pub_name_journal', $item->pub_name_journal ?? '') }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="select_group" class="form-label">กลุ่มประเภทการตีพิมพ์</label>
        <select class="form-select" id="select_group">
            <option value="">-- เลือกกลุ่ม --</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="select_grouptype" class="form-label">ระดับการตีพิมพ์</label>
        <select class="form-select" id="select_grouptype" disabled>
            <option value="">-- เลือกระดับ --</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="pubtype_id" class="form-label">ประเภทย่อย <span class="text-danger">*</span></label>
        <select class="form-select" id="pubtype_id" name="pubtype_id" disabled required>
            <option value="">-- เลือกประเภทย่อย --</option>
        </select>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pubTypes = @json($pubTypes ?? []);
    const oldPubTypeId = "{{ old('pubtype_id', $item->pubtype_id ?? '') }}";

    const selectGroup = document.getElementById('select_group');
    const selectType = document.getElementById('select_grouptype');
    const selectSubType = document.getElementById('pubtype_id');

    // Helper: Get Unique values
    const getUnique = (arr, key) => [...new Set(arr.map(item => item[key]).filter(x => x))];

    // 1. Populate Groups
    const groups = getUnique(pubTypes, 'pubtype_group');
    groups.forEach(g => {
        selectGroup.add(new Option(g, g));
    });

    // Event: Group Change
    selectGroup.addEventListener('change', function() {
        const group = this.value;
        selectType.innerHTML = '<option value="">-- เลือกระดับ --</option>';
        selectSubType.innerHTML = '<option value="">-- เลือกประเภทย่อย --</option>';
        selectType.disabled = true;
        selectSubType.disabled = true;
        // Don't validate if disabled
        selectSubType.removeAttribute('required');

        if (group) {
            const filtered = pubTypes.filter(item => item.pubtype_group === group);
            const types = getUnique(filtered, 'pubtype_grouptype');
            types.forEach(t => {
                selectType.add(new Option(t, t));
            });
            selectType.disabled = false;
        }
    });

    // Event: Type Change
    selectType.addEventListener('change', function() {
        const group = selectGroup.value;
        const type = this.value;
        selectSubType.innerHTML = '<option value="">-- เลือกประเภทย่อย --</option>';
        selectSubType.disabled = true;
        selectSubType.removeAttribute('required'); // Reset

        if (group && type) {
            const filtered = pubTypes.filter(item => item.pubtype_group === group && item.pubtype_grouptype === type);
            filtered.forEach(item => {
                selectSubType.add(new Option(item.pubtype_subgroup, item.pubtype_id));
            });
            selectSubType.disabled = false;
            selectSubType.setAttribute('required', 'required'); // Enable validation
        }
    });

    // Pre-select logic if old value exists
    if (oldPubTypeId) {
        const target = pubTypes.find(item => item.pubtype_id == oldPubTypeId);
        if (target) {
            // Set Group
            selectGroup.value = target.pubtype_group;
            
            // Trigger Change to load Types
            selectGroup.dispatchEvent(new Event('change'));
            
            // Set Type
            selectType.value = target.pubtype_grouptype;
            
            // Trigger Change to load SubTypes
            selectType.dispatchEvent(new Event('change'));
            
            // Set SubType
            selectSubType.value = target.pubtype_id;
        }
    }
});
</script>
@endpush

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="pub_date" class="form-label">วันที่นำเสนอ/ตีพิมพ์ (เริ่มต้น) <span class="text-danger">*</span></label>
                <input type="date" class="form-control datepicker" id="pub_date" name="pub_date" value="{{ old('pub_date', $item->pub_date ?? date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="pub_date_end" class="form-label">วันที่นำเสนอ/ตีพิมพ์ (สิ้นสุด)</label>
                <input type="date" class="form-control datepicker" id="pub_date_end" name="pub_date_end" value="{{ old('pub_date_end', $item->pub_date_end ?? '') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label for="pub_budget" class="form-label">งบประมาณ (Budget) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" id="pub_budget" name="pub_budget" value="{{ old('pub_budget', $item->pub_budget ?? '0') }}" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="pub_score" class="form-label">สัดส่วนคะแนน (Weight/Score) <span class="text-danger">*</span></label>
                <div class="d-flex gap-3 mt-2">
                    @foreach(['0.00', '0.20', '0.40', '0.60', '0.80', '1.00'] as $score)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pub_score" id="score_{{ $loop->index }}" value="{{ $score }}" {{ (old('pub_score', $item->pub_score ?? '0.00') == $score) ? 'checked' : '' }} required>
                            <label class="form-check-label" for="score_{{ $loop->index }}">
                                {{ $score }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="pub_note" class="form-label">หมายเหตุ (Note)</label>
                <textarea class="form-control" id="pub_note" name="pub_note" rows="2">{{ old('pub_note', $item->pub_note ?? '') }}</textarea>
            </div>
        </div>

    </div>
</div>



<hr>
<div class="d-flex justify-content-end mb-4">
    <a href="{{ route('backend.rdb_published.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>

@push('scripts')
<!-- TomSelect CDN -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    /* Fix TomSelect for Dark/Theme usage */
    .ts-control {
        background-color: var(--bs-body-bg); /* Use Bootstrap variable if available, or inherit */
        color: var(--bs-body-color);
        border: 1px solid #d1d3e2; /* Match project border color */
    }
    .ts-control.focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .ts-dropdown {
        background-color: #fff;
        color: #333;
    }
    /* Ensure input text within TomSelect is visible */
    .ts-control > input {
        color: inherit !important;
    }
</style>

    @include('layouts.partials.ckeditor_setup')

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // TomSelect: Researcher
        if (document.getElementById('researcher_id')) {
            new TomSelect("#researcher_id", {
                create: false,
                openOnFocus: true,
                persist: false,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                placeholder: '-- พิมพ์เพื่อค้นหานักวิจัย --',
                loadThrottle: 300,
                load: function(query, callback) {
                    if (!query.length || query.length < 2) return callback();
                    fetch('{{ route("backend.rdb_published.search_researcher") }}?q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(json => {
                            callback(json.results);
                        }).catch(() => {
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

        // TomSelect: Project
        new TomSelect("#pro_id", {
            create: false,
            openOnFocus: true,
            persist: false,
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            placeholder: '-- พิมพ์เพื่อค้นหาโครงการ --',
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();
                fetch('{{ route("backend.rdb_published.search_project") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => {
                        callback(json.results);
                    }).catch(() => {
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
                    return '<div class="no-results p-2 text-muted">ไม่พบข้อมูล</div>';
                }
            }
        });

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
                    if (instance.element.id === 'pub_date' && selectedDates.length > 0) {
                        const endDateInput = document.getElementById('pub_date_end');
                        if (endDateInput && endDateInput._flatpickr) {
                            endDateInput._flatpickr.set('minDate', selectedDates[0]);
                        }
                    }
                    if (instance.element.id === 'pub_date_end') {
                        const startDateInput = document.getElementById('pub_date');
                        if (startDateInput && startDateInput._flatpickr && startDateInput._flatpickr.selectedDates.length > 0) {
                            instance.set('minDate', startDateInput._flatpickr.selectedDates[0]);
                        }
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    if (instance.element.id === 'pub_date') {
                         const endDateInput = document.getElementById('pub_date_end');
                         if (endDateInput && endDateInput._flatpickr) {
                             endDateInput._flatpickr.set('minDate', selectedDates[0] || null);
                         }
                    }
                }
            });

            function adjustCalendarYear(instance) {
                setTimeout(() => {
                    const yearInput = instance.currentYearElement;
                    if (yearInput) {
                         const currentYear = instance.currentYear;
                         const buddhistYear = currentYear + 543;
                         if (yearInput.value != buddhistYear) {
                            yearInput.value = buddhistYear;
                         }
                    }
                }, 0);
            }
        }
    });
    </script>
@endpush
