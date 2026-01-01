<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="maj_id" class="form-label">รหัสสาขา</label>
            <input type="text" class="form-control" id="maj_id" name="maj_id" value="{{ old('maj_id', $item->maj_id ?? '') }}" maxlength="20">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="department_id" class="form-label">หน่วยงาน/คณะ</label>
            <select class="form-select" id="department_id" name="department_id">
                <option value="">-- เลือกหน่วยงาน --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->department_id }}" {{ old('department_id', $item->department_id ?? '') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_nameTH }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="maj_nameTH" class="form-label">ชื่อสาขา (ภาษาไทย) <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="maj_nameTH" name="maj_nameTH" value="{{ old('maj_nameTH', $item->maj_nameTH ?? '') }}" required maxlength="255">
</div>

<div class="mb-3">
    <label for="maj_nameEN" class="form-label">ชื่อสาขา (ภาษาอังกฤษ)</label>
    <input type="text" class="form-control" id="maj_nameEN" name="maj_nameEN" value="{{ old('maj_nameEN', $item->maj_nameEN ?? '') }}" maxlength="255">
</div>

<hr>
<div class="d-flex justify-content-end">
    <a href="{{ route('backend.rdbdepmajor.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>
