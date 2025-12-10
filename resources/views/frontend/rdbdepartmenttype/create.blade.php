@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มชนิดหน่วยงาน</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbdepartmenttype.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="tdepartment_nameTH" class="form-label">ชื่อชนิด (ไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('tdepartment_nameTH') is-invalid @enderror" id="tdepartment_nameTH" name="tdepartment_nameTH" value="{{ old('tdepartment_nameTH') }}" required>
                    @error('tdepartment_nameTH')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="tdepartment_nameEN" class="form-label">ชื่อชนิด (English)</label>
                    <input type="text" class="form-control @error('tdepartment_nameEN') is-invalid @enderror" id="tdepartment_nameEN" name="tdepartment_nameEN" value="{{ old('tdepartment_nameEN') }}">
                    @error('tdepartment_nameEN')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbdepartmenttype.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
