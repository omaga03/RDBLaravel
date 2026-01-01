<div class="mb-3">
    <label for="position_nameTH" class="form-label">ชื่อตำแหน่ง <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="position_nameTH" name="position_nameTH" value="{{ old('position_nameTH', $item->position_nameTH ?? '') }}" required maxlength="255">
</div>

<div class="mb-3">
    <label for="position_desc" class="form-label">คำอธิบาย</label>
    <textarea class="form-control" id="position_desc" name="position_desc" rows="3">{{ old('position_desc', $item->position_desc ?? '') }}</textarea>
</div>

<hr>
<div class="d-flex justify-content-end">
    <a href="{{ route('backend.rdbprojectposition.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>
