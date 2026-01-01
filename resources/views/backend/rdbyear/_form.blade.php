<div class="mb-3">
    <label for="year_name" class="form-label">ปีงบประมาณ <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="year_name" name="year_name" value="{{ old('year_name', $item->year_name ?? '') }}" required maxlength="50" placeholder="เช่น 2568">
</div>

<hr>
<div class="d-flex justify-content-end">
    <a href="{{ route('backend.rdbyear.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>
