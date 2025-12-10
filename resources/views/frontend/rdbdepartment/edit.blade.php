@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขหน่วยงาน</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbdepartment.update', $item->department_id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="department_code" class="form-label">รหัสหน่วยงาน</label>
                        <input type="text" class="form-control @error('department_code') is-invalid @enderror" id="department_code" name="department_code" value="{{ old('department_code', $item->department_code) }}">
                        @error('department_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tdepartment_id" class="form-label">ชนิดหน่วยงาน</label>
                        <select class="form-select @error('tdepartment_id') is-invalid @enderror" id="tdepartment_id" name="tdepartment_id">
                            <option value="">-- เลือก --</option>
                            @foreach($departmentTypes as $type)
                                <option value="{{ $type->tdepartment_id }}" {{ old('tdepartment_id', $item->tdepartment_id) == $type->tdepartment_id ? 'selected' : '' }}>
                                    {{ $type->tdepartment_nameTH }}
                                </option>
                            @endforeach
                        </select>
                        @error('tdepartment_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label for="department_nameTH" class="form-label">ชื่อหน่วยงาน (ไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('department_nameTH') is-invalid @enderror" id="department_nameTH" name="department_nameTH" value="{{ old('department_nameTH', $item->department_nameTH) }}" required>
                    @error('department_nameTH')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="department_nameEN" class="form-label">ชื่อหน่วยงาน (English)</label>
                    <input type="text" class="form-control @error('department_nameEN') is-invalid @enderror" id="department_nameEN" name="department_nameEN" value="{{ old('department_nameEN', $item->department_nameEN) }}">
                    @error('department_nameEN')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="department_color" class="form-label">สี</label>
                    <input type="text" class="form-control @error('department_color') is-invalid @enderror" id="department_color" name="department_color" value="{{ old('department_color', $item->department_color) }}" placeholder="#FFFFFF">
                    @error('department_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbdepartment.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
