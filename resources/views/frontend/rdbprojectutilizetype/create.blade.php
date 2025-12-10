@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มประเภทการนำไปใช้</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojectutilizetype.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="utz_typr_name" class="form-label">ชื่อประเภท <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('utz_typr_name') is-invalid @enderror" id="utz_typr_name" name="utz_typr_name" value="{{ old('utz_typr_name') }}" required>
                    @error('utz_typr_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="utz_type_index" class="form-label">ลำดับ</label>
                    <input type="number" class="form-control" id="utz_type_index" name="utz_type_index" value="{{ old('utz_type_index', 0) }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojectutilizetype.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
