@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มตำแหน่งในโครงการ</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojectposition.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="position_nameTH" class="form-label">ชื่อตำแหน่ง <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('position_nameTH') is-invalid @enderror" id="position_nameTH" name="position_nameTH" value="{{ old('position_nameTH') }}" required>
                    @error('position_nameTH')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="position_desc" class="form-label">คำอธิบาย</label>
                    <textarea class="form-control" id="position_desc" name="position_desc" rows="3">{{ old('position_desc') }}</textarea>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojectposition.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
