@php
    $isEdit = isset($item) && $item->id;
    // Split abstract logic
    $abstractParts = isset($item->pub_abstract) ? explode('<br><br><br><br>', $item->pub_abstract, 2) : ['', ''];
    $abstractTH = old('pub_abstract_th', $abstractParts[0] ?? '');
    $abstractEN = old('pub_abstract_en', $abstractParts[1] ?? '');
@endphp



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
                <label for="pub_keyword_input" class="form-label">คำสำคัญ (Keywords)</label>
                <div class="keyword-input-container form-control d-flex flex-wrap align-items-center gap-1" style="min-height: 38px; cursor: text;" onclick="document.getElementById('pub_keyword_input').focus()">
                    <div id="keyword-badges" class="d-flex flex-wrap gap-1"></div>
                    <input type="text" id="pub_keyword_input" class="border-0 flex-grow-1" style="outline: none; min-width: 150px;" placeholder="พิมพ์แล้วกด Enter หรือ ,">
                </div>
                <input type="hidden" id="pub_keyword" name="pub_keyword" value="{{ old('pub_keyword', $item->pub_keyword ?? '') }}">
                <small class="text-muted">กด Enter หรือ , เพื่อเพิ่มคำ • คลิกที่ badge เพื่อลบ</small>
            </div>
        </div>

        <div class="row">
            @if(!$isEdit)
            <div class="col-md-5 mb-3">
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
            <div class="col-md-4 mb-3">
                <label for="pubta_id" class="form-label">ประเภทผู้แต่ง <span class="text-danger">*</span></label>
                <select class="form-select" id="pubta_id" name="pubta_id" required>
                    <option value="">-- เลือกประเภท --</option>
                    @foreach($authorTypes ?? [] as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">ได้รับการสนับสนุน</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="pubw_bud" value="1" id="pubw_bud" {{ old('pubw_bud') ? 'checked' : '' }}>
                    <label class="form-check-label" for="pubw_bud">Budget Support</label>
                </div>
            </div>
            @endif
        </div>
        
        <div class="row">
            <div class="col-md-12 mb-3">

                @if($isEdit && $item->pro_id)
                    @php
                        $proText = '';
                        $proObj = \App\Models\RdbProject::with(['year','rdbProjectWorks.researcher'])->find($item->pro_id);
                        if($proObj) {
                            $y = $proObj->year->year_name ?? '-';
                            $n = strip_tags($proObj->pro_nameTH);
                            $r = '-';
                            if($proObj->rdbProjectWorks->isNotEmpty()){
                                $mw = $proObj->rdbProjectWorks->sortBy('position_id')->first();
                                if($mw && $mw->researcher) {
                                    $r = $mw->researcher->researcher_fname.' '.$mw->researcher->researcher_lname;
                                }
                            }
                            $proText = "{$y} • {$n} • {$r}";
                        }
                    @endphp
                @endif
                
                <x-backend.form.project-search 
                    label="โครงการวิจัยที่เกี่ยวข้อง (ถ้ามี)" 
                    :selected="$isEdit && $item->pro_id ? $item->pro_id : null"
                    :initial-text="$isEdit && $item->pro_id && isset($proText) ? $proText : ''"
                />
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



@push('scripts')
<!-- TomSelect CDN -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

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
                        return '<div>' + (data._highlight || escape(data.text)) + '</div>';
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



        // Initialize Flatpickr for Thai Buddhist Date using global helper
        if (typeof initThaiFlatpickr !== 'undefined') {
            const startDatePicker = initThaiFlatpickr("#pub_date", {
                onChange: function(selectedDates, dateStr, instance) {
                    if (endDatePicker) {
                        endDatePicker.set('minDate', dateStr || null);
                    }
                }
            });

            const endDatePicker = initThaiFlatpickr("#pub_date_end", {
                onReady: function(selectedDates, dateStr, instance) {
                    if (startDatePicker && startDatePicker.selectedDates.length > 0) {
                        instance.set('minDate', startDatePicker.selectedDates[0]);
                    }
                }
            });
        }

        // Keyword Tag Input System
        const keywordInput = document.getElementById('pub_keyword_input');
        const keywordHidden = document.getElementById('pub_keyword');
        const badgesContainer = document.getElementById('keyword-badges');
        
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
                    // Handle comma-separated paste on blur
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
