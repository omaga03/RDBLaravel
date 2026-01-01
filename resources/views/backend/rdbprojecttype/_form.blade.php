<div class="row g-3">
    <div class="col-md-8">
        <label for="pt_name" class="form-label">ชื่อประเภททุนสนับสนุน <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('pt_name') is-invalid @enderror" id="pt_name" name="pt_name" value="{{ old('pt_name', $item->pt_name ?? '') }}" required maxlength="255">
        @error('pt_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="year_id" class="form-label">ปีงบประมาณ <span class="text-danger">*</span></label>
        <select class="form-select @error('year_id') is-invalid @enderror" id="year_id" name="year_id" required>
            <option value="">-- เลือกปีงบประมาณ --</option>
            @foreach($yearList as $year)
                <option value="{{ $year->year_id }}" {{ old('year_id', $item->year_id ?? '') == $year->year_id ? 'selected' : '' }}>
                    {{ $year->year_name }}
                </option>
            @endforeach
        </select>
        @error('year_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="pttg_id" class="form-label">กลุ่มประเภทงบประมาณ</label>
        <select class="form-select @error('pttg_id') is-invalid @enderror" id="pttg_id" name="pttg_id">
            <option value="">-- เลือกกลุ่ม --</option>
            @foreach($groupList as $group)
                <option value="{{ $group->pttg_id }}" {{ old('pttg_id', $item->pttg_id ?? '') == $group->pttg_id ? 'selected' : '' }}>
                    {{ $group->pttg_name }}
                </option>
            @endforeach
        </select>
        @error('pttg_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="pt_for" class="form-label">ทุนสนับสนุนสำหรับ</label>
        <select class="form-select @error('pt_for') is-invalid @enderror" id="pt_for" name="pt_for">
            <option value="">-- เลือก --</option>
            @foreach($forList as $key => $value)
                <option value="{{ $key }}" {{ old('pt_for', $item->pt_for ?? '') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label for="pt_created" class="form-label">ทุนสนับสนุนสร้างโดย</label>
        <select class="form-select @error('pt_created') is-invalid @enderror" id="pt_created" name="pt_created">
            <option value="">-- เลือก --</option>
            @foreach($createList as $key => $value)
                <option value="{{ $key }}" {{ old('pt_created', $item->pt_created ?? '') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label d-block">คำนวณงบประมาณ</label>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="pt_type" name="pt_type" {{ old('pt_type', $item->pt_type ?? '') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="pt_type">
                คำนวณ
            </label>
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label d-block">การนำไปใช้ประโยชน์ (QA)</label>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="pt_utz" name="pt_utz" {{ old('pt_utz', $item->pt_utz ?? '') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="pt_utz">
                คำนวณ
            </label>
        </div>
    </div>

    <div class="col-md-12">
        <label for="pt_note" class="form-label">รายละเอียดเพิ่มเติม</label>
        <textarea class="form-control" id="pt_note" name="pt_note" rows="3">{{ old('pt_note', $item->pt_note ?? '') }}</textarea>
    </div>
</div>
