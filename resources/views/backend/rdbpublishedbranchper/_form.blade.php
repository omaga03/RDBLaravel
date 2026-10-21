<div class="row g-3">
    <div class="col-md-6">
        <label for="year_id" class="form-label fw-bold">ปีงบประมาณ <span class="text-danger">*</span></label>
        <select name="year_id" id="year_id" class="form-select @error('year_id') is-invalid @enderror" required>
            <option value="">-- เลือกปีงบประมาณ --</option>
            @foreach($years as $year)
                <option value="{{ $year->year_id }}" {{ old('year_id', $item->year_id ?? '') == $year->year_id ? 'selected' : '' }}>
                    ปี {{ $year->year_name }}
                </option>
            @endforeach
        </select>
        @error('year_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="branch_id" class="form-label fw-bold">สาขางานตีพิมพ์ <span class="text-danger">*</span></label>
        <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
            <option value="">-- เลือกสาขาวิจัย/ตีพิมพ์ --</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->branch_id }}" {{ old('branch_id', $item->branch_id ?? '') == $branch->branch_id ? 'selected' : '' }}>
                    {{ $branch->branch_name }}
                </option>
            @endforeach
        </select>
        @error('branch_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="branchper_percent" class="form-label fw-bold">สัดส่วนร้อยละ (%) <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="number" step="0.01" min="0" max="100" name="branchper_percent" id="branchper_percent" class="form-control @error('branchper_percent') is-invalid @enderror" value="{{ old('branchper_percent', $item->branchper_percent ?? '') }}" placeholder="เช่น 20.00" required>
            <span class="input-group-text">%</span>
        </div>
        <div class="form-text small">สัดส่วนเป้าหมายหรือการถ่วงน้ำหนักของสาขางานวิจัยในปีนั้น</div>
        @error('branchper_percent')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
