@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มประเภทโครงการ</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojecttype.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="pt_name" class="form-label">ชื่อประเภท <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('pt_name') is-invalid @enderror" id="pt_name" name="pt_name" value="{{ old('pt_name') }}" required>
                    @error('pt_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="pt_for" class="form-label">สำหรับ</label>
                    <input type="text" class="form-control" id="pt_for" name="pt_for" value="{{ old('pt_for') }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojecttype.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
