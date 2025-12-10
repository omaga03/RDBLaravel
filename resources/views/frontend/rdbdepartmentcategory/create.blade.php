@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มประเภทหน่วยงาน</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbdepartmentcategory.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="depcat_name" class="form-label">ชื่อประเภท <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('depcat_name') is-invalid @enderror" id="depcat_name" name="depcat_name" value="{{ old('depcat_name') }}" required>
                    @error('depcat_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbdepartmentcategory.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
