<div class="row g-3">
    <div class="col-md-8">
        <label for="pttg_name" class="form-label">ชื่อกลุ่มประเภทงบประมาณ <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('pttg_name') is-invalid @enderror" id="pttg_name" name="pttg_name" value="{{ old('pttg_name', $item->pttg_name ?? '') }}" required maxlength="255">
        @error('pttg_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="pttg_group" class="form-label">กลุ่มประเภท <span class="text-danger">*</span></label>
        <select class="form-select @error('pttg_group') is-invalid @enderror" id="pttg_group" name="pttg_group" required>
            <option value="">-- เลือกกลุ่มประเภท --</option>
            @foreach($groupList as $key => $value)
                <option value="{{ $key }}" {{ old('pttg_group', $item->pttg_group ?? '') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
        @error('pttg_group')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
