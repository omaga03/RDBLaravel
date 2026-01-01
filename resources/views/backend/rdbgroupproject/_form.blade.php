<div class="mb-3">
    <label for="pgroup_nameTH" class="form-label">ชื่อกลุ่มโครงการ <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="pgroup_nameTH" name="pgroup_nameTH" value="{{ old('pgroup_nameTH', $item->pgroup_nameTH ?? '') }}" required maxlength="255">
</div>

<hr>
<div class="d-flex justify-content-end">
    <a href="{{ route('backend.rdbgroupproject.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>
