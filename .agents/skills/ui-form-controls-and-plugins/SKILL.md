---
name: ui-form-controls-and-plugins
description: Standards for form controls, TomSelect AJAX search, multi-select array handling with @json, CKEditor setup, Multi-Step Wizard UI, and validation feedback.
---

# Form Controls & Plugin Standards (laravel_rdb)

Use this skill whenever building or updating forms that involve TomSelect, CKEditor, file upload inputs, Multi-Step Wizard UI, or complex validation.

## 1. Multi-Select TomSelect with Safe Array `old()`
When using TomSelect with multiple selections (e.g. `name="utz_group[]"`), NEVER use `{{ old('fieldName') }}` because in PHP 8.4 passing an array to `htmlspecialchars` throws a `TypeError`.

Always parse `old()` safely with `@php` and pass to JS via `@json`:
```blade
<select class="form-select" id="utz_group" name="utz_group[]" multiple>
    {{-- Loaded dynamically --}}
</select>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectElem = document.getElementById('utz_group');
    @php
        $rawValues = old('utz_group', $item->utz_group ?? []);
        if (is_array($rawValues)) {
            $selectedIds = array_values(array_map('strval', $rawValues));
        } elseif (is_string($rawValues) && $rawValues !== '') {
            $selectedIds = array_values(array_filter(array_map('trim', explode(',', $rawValues))));
        } else {
            $selectedIds = [];
        }
    @endphp
    const selectedIds = @json($selectedIds);

    if (selectElem && typeof TomSelect !== 'undefined') {
        fetch('{{ route("backend.search_endpoint") }}')
            .then(res => res.json())
            .then(data => {
                let options = '';
                data.forEach(t => {
                    const isSelected = selectedIds.includes(String(t.value)) ? 'selected' : '';
                    options += `<option value="${t.value}" ${isSelected}>${t.text}</option>`;
                });
                selectElem.innerHTML = options;

                new TomSelect(selectElem, {
                    plugins: ['remove_button'],
                    create: false,
                    maxItems: null,
                    placeholder: '-- กรุณาเลือก --'
                });
            });
    }
});
</script>
@endpush
```

## 2. Remote AJAX Search Component (`<x-backend.form.project-search>`)
For central project selection with researcher and fiscal year details, use the pre-built component:
```blade
<x-backend.form.project-search 
    label="โครงการวิจัย" 
    required="true"
    :selected="$isEdit && $item->project ? $item->project->pro_id : null"
    :initial-text="$isEdit && $item->project ? $pText : ''"
/>
```

## 3. CKEditor Standard Integration
For rich text fields:
1. Add class `ckeditor-standard` to `<textarea>`.
2. Include the shared script configuration at the bottom:
```blade
<textarea class="form-control ckeditor-standard" id="pro_detail" name="pro_detail">{{ old('pro_detail', $item->pro_detail ?? '') }}</textarea>

@push('scripts')
@include('layouts.partials.ckeditor_setup')
@endpush
```

## 4. Multi-Step Form / Wizard UI Standard (Bootstrap 5.3)
สำหรับฟอร์มกรอกข้อมูลงานวิจัยที่มีรายละเอียดมาก (เช่น โครงการวิจัย, ผลงานตีพิมพ์) ให้แบ่งขั้นตอนออกเป็น Steps ด้วย Bootstrap 5.3 Nav-Pills เพื่อลด Cognitive Load ของผู้ใช้งาน:
```blade
<div class="card bg-body-tertiary border mb-4">
    <div class="card-body p-3">
        <ul class="nav nav-pills nav-fill gap-2" id="researchFormSteps" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-medium" id="step1-tab" data-bs-toggle="pill" data-bs-target="#step1" type="button" role="tab">
                    <span class="badge bg-primary me-2">1</span> ข้อมูลทั่วไปและชื่อเรื่อง
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-medium" id="step2-tab" data-bs-toggle="pill" data-bs-target="#step2" type="button" role="tab">
                    <span class="badge bg-secondary me-2">2</span> นักวิจัยและงบประมาณ
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-medium" id="step3-tab" data-bs-toggle="pill" data-bs-target="#step3" type="button" role="tab">
                    <span class="badge bg-secondary me-2">3</span> บทคัดย่อและเอกสารแนบ
                </button>
            </li>
        </ul>
    </div>
</div>
```
- ในปุ่มเปลี่ยน Step ด้านล่าง ให้มีปุ่ม `ย้อนกลับ` (Previous) และ `ถัดไป` (Next) เพื่อนำทาง พร้อมการตรวจสอบ Input เบื้องต้นก่อนข้าม Step

## 5. File Upload & PDF Preview Modal Standard
สำหรับการอัปโหลดเอกสารรายงานวิจัย/บทความ PDF:
1. แสดงขอบเขตประเภทไฟล์ (`.pdf`, `.doc`, `.docx`) และขนาดสูงสุดอย่างชัดเจน
2. มีปุ่มกดดูเอกสารผ่าน Inline Modal สำหรับไฟล์ที่อัปโหลดไว้แล้ว โดยไม่ต้องโหลดหน้าใหม่:
```blade
<!-- ปุ่มเปิด PDF Preview Modal -->
<button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#pdfPreviewModal">
    <i class="bi bi-eye me-1"></i> ดูตัวอย่าง PDF
</button>
```

## 6. Standard Form Validation Feedback
Every required or validated form group must display server-side validation errors:
```blade
<div class="mb-3">
    <label for="pro_nameTH" class="form-label">ชื่อโครงการ (ภาษาไทย) <span class="text-danger">*</span></label>
    <input type="text" 
           class="form-control @error('pro_nameTH') is-invalid @enderror" 
           id="pro_nameTH" 
           name="pro_nameTH" 
           value="{{ old('pro_nameTH', $item->pro_nameTH ?? '') }}" 
           required>
    @error('pro_nameTH')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```
