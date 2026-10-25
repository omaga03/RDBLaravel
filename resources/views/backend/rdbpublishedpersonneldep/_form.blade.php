<div class="row g-3">
    <div class="col-md-4">
        <label for="year_id" class="form-label fw-bold">ปี พ.ศ. <span class="text-danger">*</span></label>
        <select name="year_id" id="year_id" class="form-select @error('year_id') is-invalid @enderror" required>
            <option value="">-- เลือกปี พ.ศ. --</option>
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

    <div class="col-md-6">
        <label for="depcou_id" class="form-label fw-bold">หลักสูตร</label>
        <select name="depcou_id" id="depcou_id" class="form-select @error('depcou_id') is-invalid @enderror">
            <option value="">-- ไม่ระบุ --</option>
            @foreach($courses as $c)
                <option value="{{ $c->depcou_id }}" {{ old('depcou_id', $item->depcou_id ?? '') == $c->depcou_id ? 'selected' : '' }}>
                    {{ $c->depcou_name }}
                </option>
            @endforeach
        </select>
        @error('depcou_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="major_id" class="form-label fw-bold">สาขาวิชา</label>
        <select name="major_id" id="major_id" class="form-select @error('major_id') is-invalid @enderror">
            <option value="">-- ไม่ระบุ --</option>
            @foreach($majors as $m)
                <option value="{{ $m->maj_code }}" {{ old('major_id', $item->major_id ?? '') == $m->maj_code ? 'selected' : '' }}>
                    {{ $m->maj_nameTH }}
                </option>
            @endforeach
        </select>
        @error('major_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="personnel_num" class="form-label fw-bold">จำนวนบุคลากรรวม (คน) <span class="text-danger">*</span></label>
        <input type="number" step="0.5" name="personnel_num" id="personnel_num" class="form-control @error('personnel_num') is-invalid @enderror" value="{{ old('personnel_num', $item->personnel_num ?? '') }}" required>
        @error('personnel_num')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="personnel_numedu" class="form-label fw-bold">ด้านปีการศึกษา (คน)</label>
        <input type="number" step="0.5" name="personnel_numedu" id="personnel_numedu" class="form-control @error('personnel_numedu') is-invalid @enderror" value="{{ old('personnel_numedu', $item->personnel_numedu ?? '') }}">
        @error('personnel_numedu')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="personnel_numbud" class="form-label fw-bold">ด้านปีงบประมาณ (คน)</label>
        <input type="number" step="0.5" name="personnel_numbud" id="personnel_numbud" class="form-control @error('personnel_numbud') is-invalid @enderror" value="{{ old('personnel_numbud', $item->personnel_numbud ?? '') }}">
        @error('personnel_numbud')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
