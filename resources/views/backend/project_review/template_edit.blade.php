@extends('layouts.app')

@section('content')
<x-page-header
    title="แก้ไขแบบฟอร์มเกณฑ์การประเมิน: {{ $template->title }}"
    icon="bi-pencil-square"
    :backRoute="route('backend.project_review.templates')"
    :showPrint="false"
    :breadcrumbs="[
        'แดชบอร์ด' => route('backend.site.index'),
        'ระบบประเมินเล่มรายงานวิจัย' => route('backend.project_review.index'),
        'จัดการแบบฟอร์มเกณฑ์ประเมิน' => route('backend.project_review.templates'),
        'แก้ไขแบบฟอร์ม' => ''
    ]"
>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('backend.project_review.templates') }}" class="btn btn-outline-secondary d-inline-flex justify-content-center align-items-center" style="height: 38px; font-size: 0.85rem;">
            <i class="bi bi-arrow-left me-1.5"></i> กลับหน้ารายการ
        </a>
    </div>
</x-page-header>

@if(session('success'))
    <div class="alert alert-success border border-success-subtle shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-check-circle-fill fs-5 me-2 text-success-emphasis"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger border border-danger-subtle shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger-emphasis"></i>
        <div>{{ session('error') }}</div>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger border border-danger-subtle shadow-sm mb-4">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon me-1"></i> กรุณาตรวจสอบข้อมูล:</div>
        <ul class="mb-0 small">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('backend.project_review.templates.update', $template->id) }}" method="POST" id="editTemplateForm">
    @csrf
    @method('PUT')

    {{-- 1. General Template Settings --}}
    <x-card title="ข้อมูลทั่วไปของแบบฟอร์มเกณฑ์การประเมิน" icon="bi bi-gear-wide-connected" color="primary" class="mb-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">ชื่อแบบฟอร์มเกณฑ์การประเมิน <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control border-secondary-subtle" required value="{{ old('title', $template->title) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">รูปแบบการประเมินหลัก <span class="text-danger">*</span></label>
                <select name="evaluation_mode" id="evaluation_mode" class="form-select border-secondary-subtle" required onchange="handleModeChange()">
                    <option value="hybrid" @selected(old('evaluation_mode', $template->evaluation_mode) === 'hybrid')>แบบผสมผสาน (คะแนนสเกล + ข้อเสนอแนะรายข้อ)</option>
                    <option value="scoring_scale" @selected(old('evaluation_mode', $template->evaluation_mode) === 'scoring_scale')>แบบให้คะแนนสเกล (Scoring Scale)</option>
                    <option value="pass_fail" @selected(old('evaluation_mode', $template->evaluation_mode) === 'pass_fail')>แบบผ่าน / แก้ไข / ไม่ผ่าน (Checklist)</option>
                    <option value="qualitative_only" @selected(old('evaluation_mode', $template->evaluation_mode) === 'qualitative_only')>แบบความเห็นเชิงคุณภาพ (ปลายเปิดรายข้อ)</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label fw-semibold">สเกลตั้งต้น</label>
                <input type="number" name="scale_max" id="default_scale_max" class="form-control border-secondary-subtle" value="{{ old('scale_max', $template->scale_max) }}" min="1" max="100" style="font-variant-numeric: tabular-nums;" onchange="updateAllPreviews()">
                <div class="form-text small">ค่าเริ่มต้น</div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">เกณฑ์ผ่านขั้นต่ำ (%)</label>
                <input type="number" step="0.01" name="pass_threshold" class="form-control border-secondary-subtle" value="{{ old('pass_threshold', $template->pass_threshold) }}" min="0" max="100" style="font-variant-numeric: tabular-nums;">
                <div class="form-text small">เกณฑ์ % ผ่าน</div>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">คำชี้แจงสำหรับผู้ทรงคุณวุฒิ</label>
                <textarea name="description" rows="2" class="form-control border-secondary-subtle" placeholder="ระบุคำแนะนำในการให้คะแนนหรือเกณฑ์การพิจารณาสำหรับผู้ทรงคุณวุฒิ...">{{ old('description', $template->description) }}</textarea>
            </div>
            <div class="col-md-4 d-flex flex-column justify-content-center gap-2 pt-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $template->is_active))>
                    <label class="form-check-label fw-semibold" for="is_active">เปิดใช้งานแบบฟอร์มนี้</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" @checked(old('is_default', $template->is_default))>
                    <label class="form-check-label fw-semibold" for="is_default">ตั้งเป็นแบบฟอร์มเริ่มต้น (Default)</label>
                </div>
            </div>
        </div>
    </x-card>

    {{-- 2. Criteria Items Designer --}}
    <x-card title="รายการหัวข้อและเกณฑ์การประเมิน (Criteria Items)" icon="bi bi-list-check" color="success" class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="text-body-secondary small">
                <i class="bi bi-info-circle me-1"></i> ท่านสามารถกำหนด <strong>คะแนนเต็ม (Max)</strong> และ <strong>ระยะห่างสเกล (Step)</strong> ในแต่ละข้อได้อิสระ เช่น เต็ม 25 คะแนน ห่างทีละ 5 ระบบจะสร้างสเกล 5 ระดับ (5, 10, 15, 20, 25)
            </div>
            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center shadow-sm" onclick="addCriterionRow()">
                <i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อประเมิน
            </button>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-bordered align-middle mb-0 border-secondary-subtle" id="criteriaTable">
                <thead class="bg-body-tertiary text-body-secondary small">
                    <tr>
                        <th style="width: 16%;">หมวดหมู่ (Category)</th>
                        <th style="width: 25%;">หัวข้อการประเมิน <span class="text-danger">*</span></th>
                        <th style="width: 18%;">คำอธิบายย่อย</th>
                        <th style="width: 14%;">ชนิดการประเมิน</th>
                        <th style="width: 15%;">สเกลคะแนน (Max / Step)</th>
                        <th style="width: 6%;">น้ำหนัก (%)</th>
                        <th style="width: 6%;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="criteriaBody">
                    @foreach($template->criteria as $idx => $crit)
                        <tr class="criterion-row">
                            <td>
                                <input type="hidden" name="criteria[{{ $idx }}][id]" value="{{ $crit->id }}">
                                <input type="text" name="criteria[{{ $idx }}][category]" class="form-control form-control-sm border-secondary-subtle" placeholder="หมวดหมู่..." value="{{ $crit->category }}">
                            </td>
                            <td>
                                <textarea name="criteria[{{ $idx }}][name]" rows="2" class="form-control form-control-sm border-secondary-subtle" required placeholder="ระบุหัวข้อเกณฑ์การประเมิน...">{{ $crit->name }}</textarea>
                            </td>
                            <td>
                                <textarea name="criteria[{{ $idx }}][description]" rows="2" class="form-control form-control-sm border-secondary-subtle" placeholder="คำอธิบายย่อย (ถ้ามี)">{{ $crit->description }}</textarea>
                            </td>
                            <td>
                                <select name="criteria[{{ $idx }}][item_type]" class="form-select form-select-sm border-secondary-subtle item-type-select" onchange="handleRowTypeChange(this)">
                                    <option value="hybrid" @selected($crit->item_type === 'hybrid')>คะแนน + ความเห็น</option>
                                    <option value="scale" @selected($crit->item_type === 'scale')>ระดับคะแนนอย่างเดียว</option>
                                    <option value="pass_fail" @selected($crit->item_type === 'pass_fail')>ผ่าน/ไม่ผ่าน</option>
                                    <option value="open_ended" @selected($crit->item_type === 'open_ended')>ความเห็นปลายเปิด</option>
                                </select>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="checkbox" name="criteria[{{ $idx }}][is_required]" value="1" id="req_{{ $idx }}" @checked($crit->is_required)>
                                    <label class="form-check-label small" for="req_{{ $idx }}">บังคับ</label>
                                </div>
                            </td>
                            <td>
                                <div class="scale-controls" style="{{ in_array($crit->item_type, ['pass_fail', 'open_ended']) ? 'display: none;' : '' }}">
                                    @php
                                        $cMax = $crit->scale_max ?: $template->scale_max ?: 5;
                                        $cStep = $crit->scale_step ?: 1;
                                    @endphp
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text px-1.5 small text-body-secondary">เต็ม</span>
                                        <input type="number" name="criteria[{{ $idx }}][scale_max]" class="form-control px-1 text-center scale-max-input" value="{{ $cMax }}" min="1" max="100" placeholder="{{ $cMax }}" onchange="updateRowPreview(this)" onkeyup="updateRowPreview(this)" style="font-variant-numeric: tabular-nums;">
                                        <span class="input-group-text px-1.5 small text-body-secondary">ทีละ</span>
                                        <input type="number" name="criteria[{{ $idx }}][scale_step]" class="form-control px-1 text-center scale-step-input" value="{{ $cStep }}" min="1" max="100" placeholder="1" onchange="updateRowPreview(this)" onkeyup="updateRowPreview(this)" style="font-variant-numeric: tabular-nums;">
                                    </div>
                                    <div class="scale-preview-badge text-wrap small text-primary fw-semibold" style="font-size: 0.72rem; font-variant-numeric: tabular-nums;">
                                        สเกล: [ {{ implode(', ', $crit->scale_options) }} ] ({{ count($crit->scale_options) }} ระดับ)
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="criteria[{{ $idx }}][weight]" class="form-control form-control-sm border-secondary-subtle text-end weight-input" value="{{ $crit->weight }}" min="0" max="100" onchange="calculateTotalWeight()" style="font-variant-numeric: tabular-nums;">
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary py-0 px-1" onclick="moveRowUp(this)" title="ย้ายขึ้น"><i class="bi bi-arrow-up"></i></button>
                                    <button type="button" class="btn btn-outline-secondary py-0 px-1" onclick="moveRowDown(this)" title="ย้ายลง"><i class="bi bi-arrow-down"></i></button>
                                    <button type="button" class="btn btn-outline-danger py-0 px-1" onclick="removeRow(this)" title="ลบข้อนี้"><i class="bi bi-trash3"></i></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-body-secondary fw-semibold small">
                    <tr>
                        <td colspan="5" class="text-end text-body-emphasis">รวมน้ำหนักคะแนนทั้งหมด:</td>
                        <td class="text-end">
                            <span id="totalWeightDisplay" class="fw-bold text-success font-monospace" style="font-size: 0.95rem;">0.00%</span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary-subtle">
            <a href="{{ route('backend.project_review.templates') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle me-1"></i> ยกเลิก
            </a>
            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-check2-circle me-1.5"></i> บันทึกการแก้ไขแบบฟอร์ม
            </button>
        </div>
    </x-card>
</form>

<script>
    let rowCount = {{ $template->criteria->count() }};

    function addCriterionRow() {
        const tbody = document.getElementById('criteriaBody');
        const defaultScale = document.getElementById('default_scale_max').value || 5;
        const currentMode = document.getElementById('evaluation_mode').value;
        const defaultType = currentMode === 'pass_fail' ? 'pass_fail' : (currentMode === 'qualitative_only' ? 'open_ended' : (currentMode === 'scoring_scale' ? 'scale' : 'hybrid'));

        const tr = document.createElement('tr');
        tr.className = 'criterion-row';
        tr.innerHTML = `
            <td>
                <input type="text" name="criteria[${rowCount}][category]" class="form-control form-control-sm border-secondary-subtle" placeholder="หมวดหมู่...">
            </td>
            <td>
                <textarea name="criteria[${rowCount}][name]" rows="2" class="form-control form-control-sm border-secondary-subtle" required placeholder="ระบุหัวข้อเกณฑ์การประเมิน..."></textarea>
            </td>
            <td>
                <textarea name="criteria[${rowCount}][description]" rows="2" class="form-control form-control-sm border-secondary-subtle" placeholder="คำอธิบายย่อย (ถ้ามี)"></textarea>
            </td>
            <td>
                <select name="criteria[${rowCount}][item_type]" class="form-select form-select-sm border-secondary-subtle item-type-select" onchange="handleRowTypeChange(this)">
                    <option value="hybrid" ${defaultType === 'hybrid' ? 'selected' : ''}>คะแนน + ความเห็น</option>
                    <option value="scale" ${defaultType === 'scale' ? 'selected' : ''}>ระดับคะแนนอย่างเดียว</option>
                    <option value="pass_fail" ${defaultType === 'pass_fail' ? 'selected' : ''}>ผ่าน/ไม่ผ่าน</option>
                    <option value="open_ended" ${defaultType === 'open_ended' ? 'selected' : ''}>ความเห็นปลายเปิด</option>
                </select>
                <div class="form-check form-check-inline mt-1">
                    <input class="form-check-input" type="checkbox" name="criteria[${rowCount}][is_required]" value="1" id="req_${rowCount}" checked>
                    <label class="form-check-label small" for="req_${rowCount}">บังคับ</label>
                </div>
            </td>
            <td>
                <div class="scale-controls" style="${defaultType === 'pass_fail' || defaultType === 'open_ended' ? 'display: none;' : ''}">
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text px-1.5 small text-body-secondary">เต็ม</span>
                        <input type="number" name="criteria[${rowCount}][scale_max]" class="form-control px-1 text-center scale-max-input" value="${defaultScale}" min="1" max="100" placeholder="${defaultScale}" onchange="updateRowPreview(this)" onkeyup="updateRowPreview(this)" style="font-variant-numeric: tabular-nums;">
                        <span class="input-group-text px-1.5 small text-body-secondary">ทีละ</span>
                        <input type="number" name="criteria[${rowCount}][scale_step]" class="form-control px-1 text-center scale-step-input" value="1" min="1" max="100" placeholder="1" onchange="updateRowPreview(this)" onkeyup="updateRowPreview(this)" style="font-variant-numeric: tabular-nums;">
                    </div>
                    <div class="scale-preview-badge text-wrap small text-primary fw-semibold" style="font-size: 0.72rem; font-variant-numeric: tabular-nums;">
                        สเกล: [ ${computeScaleOptionsString(defaultScale, 1)} ]
                    </div>
                </div>
            </td>
            <td>
                <input type="number" step="0.01" name="criteria[${rowCount}][weight]" class="form-control form-control-sm border-secondary-subtle text-end weight-input" value="10" min="0" max="100" onchange="calculateTotalWeight()" style="font-variant-numeric: tabular-nums;">
            </td>
            <td class="text-center">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary py-0 px-1" onclick="moveRowUp(this)" title="ย้ายขึ้น"><i class="bi bi-arrow-up"></i></button>
                    <button type="button" class="btn btn-outline-secondary py-0 px-1" onclick="moveRowDown(this)" title="ย้ายลง"><i class="bi bi-arrow-down"></i></button>
                    <button type="button" class="btn btn-outline-danger py-0 px-1" onclick="removeRow(this)" title="ลบข้อนี้"><i class="bi bi-trash3"></i></button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
        rowCount++;
        calculateTotalWeight();
    }

    function computeScaleOptionsString(maxVal, stepVal) {
        const max = parseInt(maxVal) || 5;
        const step = Math.max(1, parseInt(stepVal) || 1);
        const options = [];
        for (let v = step; v <= max; v += step) {
            options.push(v);
        }
        if (options.length === 0 || options[options.length - 1] !== max) {
            options.push(max);
        }
        return options.join(', ');
    }

    function updateRowPreview(input) {
        const row = input.closest('tr');
        const maxInput = row.querySelector('.scale-max-input');
        const stepInput = row.querySelector('.scale-step-input');
        const badge = row.querySelector('.scale-preview-badge');
        if (maxInput && stepInput && badge) {
            const str = computeScaleOptionsString(maxInput.value, stepInput.value);
            const count = str.split(',').length;
            badge.innerText = `สเกล: [ ${str} ] (${count} ระดับ)`;
        }
    }

    function handleRowTypeChange(select) {
        const row = select.closest('tr');
        const scaleControls = row.querySelector('.scale-controls');
        if (scaleControls) {
            if (select.value === 'pass_fail' || select.value === 'open_ended') {
                scaleControls.style.display = 'none';
            } else {
                scaleControls.style.display = '';
                updateRowPreview(row.querySelector('.scale-max-input'));
            }
        }
    }

    function handleModeChange() {
        const mode = document.getElementById('evaluation_mode').value;
        const rows = document.querySelectorAll('#criteriaBody .criterion-row');
        rows.forEach(row => {
            const select = row.querySelector('.item-type-select');
            if (select) {
                if (mode === 'pass_fail') select.value = 'pass_fail';
                else if (mode === 'qualitative_only') select.value = 'open_ended';
                else if (mode === 'scoring_scale') select.value = 'scale';
                else select.value = 'hybrid';
                handleRowTypeChange(select);
            }
        });
    }

    function updateAllPreviews() {
        const defaultScale = document.getElementById('default_scale_max').value || 5;
        document.querySelectorAll('#criteriaBody .criterion-row').forEach(row => {
            const maxInput = row.querySelector('.scale-max-input');
            if (maxInput && (!maxInput.value || maxInput.value === '5')) {
                maxInput.value = defaultScale;
            }
            updateRowPreview(maxInput);
        });
    }

    function moveRowUp(btn) {
        const row = btn.closest('tr');
        if (row.previousElementSibling) {
            row.parentNode.insertBefore(row, row.previousElementSibling);
        }
    }

    function moveRowDown(btn) {
        const row = btn.closest('tr');
        if (row.nextElementSibling) {
            row.parentNode.insertBefore(row.nextElementSibling, row);
        }
    }

    function removeRow(btn) {
        const tbody = document.getElementById('criteriaBody');
        if (tbody.querySelectorAll('tr').length <= 1) {
            alert('ต้องมีหัวข้อเกณฑ์การประเมินอย่างน้อย 1 ข้อ');
            return;
        }
        btn.closest('tr').remove();
        calculateTotalWeight();
    }

    function calculateTotalWeight() {
        let total = 0;
        document.querySelectorAll('.weight-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        const display = document.getElementById('totalWeightDisplay');
        if (display) {
            display.innerText = total.toFixed(2) + '%';
            if (Math.abs(total - 100) < 0.01) {
                display.className = 'fw-bold text-success font-monospace';
            } else {
                display.className = 'fw-bold text-warning-emphasis font-monospace';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        calculateTotalWeight();
        document.querySelectorAll('.scale-max-input').forEach(input => updateRowPreview(input));
    });
</script>
@endsection
