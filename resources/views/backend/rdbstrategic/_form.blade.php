<div class="row">
    <div class="col-md-8">
        <div class="mb-3">
            <label for="strategic_nameTH" class="form-label">ชื่อยุทธศาสตร์ <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="strategic_nameTH" name="strategic_nameTH" value="{{ old('strategic_nameTH', $item->strategic_nameTH ?? '') }}" required maxlength="255">
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="year_id" class="form-label">ปีงบประมาณ</label>
            <select class="form-select" id="year_id" name="year_id">
                <option value="">-- เลือกปี --</option>
                @foreach($years as $year)
                    <option value="{{ $year->year_id }}" {{ old('year_id', $item->year_id ?? '') == $year->year_id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<hr>
<div class="d-flex justify-content-end">
    <a href="{{ route('backend.rdbstrategic.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>
