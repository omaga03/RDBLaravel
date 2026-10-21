<div class="row g-3">
    <div class="col-md-4">
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

    <div class="col-md-5">
        <label for="department_id" class="form-label fw-bold">คณะ / หน่วยงาน <span class="text-danger">*</span></label>
        <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
            <option value="">-- เลือกคณะ/หน่วยงาน --</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->department_id }}" {{ old('department_id', $item->department_id ?? '') == $dept->department_id ? 'selected' : '' }}>
                    {{ $dept->department_nameTH }}
                </option>
            @endforeach
        </select>
        @error('department_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="depcat_id" class="form-label fw-bold">กลุ่มสาขาวิชา</label>
        <select name="depcat_id" id="depcat_id" class="form-select @error('depcat_id') is-invalid @enderror">
            <option value="">-- ไม่ระบุ --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->depcat_id }}" {{ old('depcat_id', $item->depcat_id ?? '') == $cat->depcat_id ? 'selected' : '' }}>
                    {{ $cat->depcat_name }}
                </option>
            @endforeach
        </select>
        @error('depcat_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="personnel_num" class="form-label fw-bold">จำนวนบุคลากรสายวิชาการ (คน) <span class="text-danger">*</span></label>
        <input type="number" step="0.5" name="personnel_num" id="personnel_num" class="form-control @error('personnel_num') is-invalid @enderror" value="{{ old('personnel_num', $item->personnel_num ?? '') }}" placeholder="เช่น 47.5" required>
        <div class="form-text small">จำนวนรวมอาจารย์/บุคลากรสายวิชาการในคณะ</div>
        @error('personnel_num')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="personnel_numedu" class="form-label fw-bold">ด้านการจัดการเรียนการสอน (คน)</label>
        <input type="number" step="0.5" name="personnel_numedu" id="personnel_numedu" class="form-control @error('personnel_numedu') is-invalid @enderror" value="{{ old('personnel_numedu', $item->personnel_numedu ?? '') }}" placeholder="เช่น 47.5">
        @error('personnel_numedu')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="personnel_numbud" class="form-label fw-bold">ด้านเงินงบประมาณ (คน)</label>
        <input type="number" step="0.5" name="personnel_numbud" id="personnel_numbud" class="form-control @error('personnel_numbud') is-invalid @enderror" value="{{ old('personnel_numbud', $item->personnel_numbud ?? '') }}" placeholder="เช่น 47.5">
        @error('personnel_numbud')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
