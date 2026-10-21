<div class="row g-3">
    <div class="col-md-8">
        <label for="master_pt_name" class="form-label fw-bold">ชื่อหมวดหมู่ทุนหลัก (Master Category) <span class="text-danger">*</span></label>
        <input type="text" name="master_pt_name" id="master_pt_name" class="form-control @error('master_pt_name') is-invalid @enderror" value="{{ old('master_pt_name', $item->master_pt_name ?? '') }}" placeholder="เช่น ววน. / สกสว., รายได้มหาวิทยาลัย" required>
        @error('master_pt_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="pttg_id" class="form-label fw-bold">สังกัดกลุ่มประเภททุน</label>
        <select name="pttg_id" id="pttg_id" class="form-select @error('pttg_id') is-invalid @enderror">
            <option value="">-- ไม่ระบุ --</option>
            @foreach($groups as $group)
                <option value="{{ $group->pttg_id }}" {{ old('pttg_id', $item->pttg_id ?? '') == $group->pttg_id ? 'selected' : '' }}>
                    {{ $group->pttg_name }}
                </option>
            @endforeach
        </select>
        @error('pttg_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="order_index" class="form-label fw-bold">ลำดับการแสดงผล</label>
        <input type="number" name="order_index" id="order_index" class="form-control @error('order_index') is-invalid @enderror" value="{{ old('order_index', $item->order_index ?? 0) }}">
        @error('order_index')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="color_code" class="form-label fw-bold">รหัสสี (Hex Color)</label>
        <div class="input-group">
            <input type="color" class="form-control form-control-color" id="colorPicker" value="{{ old('color_code', $item->color_code ?? '#3B82F6') }}" onchange="document.getElementById('color_code').value = this.value">
            <input type="text" name="color_code" id="color_code" class="form-control @error('color_code') is-invalid @enderror" value="{{ old('color_code', $item->color_code ?? '#3B82F6') }}" placeholder="#3B82F6">
        </div>
        @error('color_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
