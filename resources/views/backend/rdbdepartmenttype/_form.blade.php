<div class="mb-3">
    <label for="tdepartment_nameTH" class="form-label">ชื่อประเภท (ภาษาไทย) <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="tdepartment_nameTH" name="tdepartment_nameTH" value="{{ old('tdepartment_nameTH', $item->tdepartment_nameTH ?? '') }}" required maxlength="255">
</div>

<div class="mb-3">
    <label for="tdepartment_nameEN" class="form-label">ชื่อประเภท (ภาษาอังกฤษ)</label>
    <input type="text" class="form-control" id="tdepartment_nameEN" name="tdepartment_nameEN" value="{{ old('tdepartment_nameEN', $item->tdepartment_nameEN ?? '') }}" maxlength="255">
</div>

<hr>
<div class="d-flex justify-content-end">
    <a href="{{ route('backend.rdbdepartmenttype.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>
