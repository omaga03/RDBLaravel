<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="department_code" class="form-label">รหัสหน่วยงาน</label>
            <input type="text" class="form-control" id="department_code" name="department_code" value="{{ old('department_code', $item->department_code ?? '') }}" maxlength="20">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="tdepartment_id" class="form-label">ประเภทหน่วยงาน</label>
            <select class="form-select" id="tdepartment_id" name="tdepartment_id">
                <option value="">-- เลือกประเภท --</option>
                @foreach($departmentTypes as $type)
                    <option value="{{ $type->tdepartment_id }}" {{ old('tdepartment_id', $item->tdepartment_id ?? '') == $type->tdepartment_id ? 'selected' : '' }}>{{ $type->tdepartment_nameTH }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="department_nameTH" class="form-label">ชื่อหน่วยงาน (ภาษาไทย) <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="department_nameTH" name="department_nameTH" value="{{ old('department_nameTH', $item->department_nameTH ?? '') }}" required maxlength="255">
</div>

<div class="mb-3">
    <label for="department_nameEN" class="form-label">ชื่อหน่วยงาน (ภาษาอังกฤษ)</label>
    <input type="text" class="form-control" id="department_nameEN" name="department_nameEN" value="{{ old('department_nameEN', $item->department_nameEN ?? '') }}" maxlength="255">
</div>

<div class="mb-3">
    <label for="department_color" class="form-label">สีประจำหน่วยงาน</label>
    <input type="color" class="form-control form-control-color" id="department_color" name="department_color" value="{{ old('department_color', $item->department_color ?? '#ffffff') }}">
</div>

<hr>
<div class="d-flex justify-content-end gap-2">
    {{-- Back Button --}}
    @if(isset($item) && $item->department_id)
        {{-- Edit Mode: Back to Show --}}
        <a href="{{ route('backend.rdbdepartment.show', $item->department_id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> ย้อนกลับ
        </a>
    @else
        {{-- Create Mode: Back to Index --}}
        <a href="{{ route('backend.rdbdepartment.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> ย้อนกลับ
        </a>
    @endif

    <button type="button" class="btn btn-primary" onclick="this.form.submit()">
        <i class="bi bi-save"></i> บันทึกข้อมูล
    </button>
</div>
