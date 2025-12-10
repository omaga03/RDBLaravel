@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มสถานะโครงการ</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojectstatus.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="ps_name" class="form-label">ชื่อสถานะ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('ps_name') is-invalid @enderror" id="ps_name" name="ps_name" value="{{ old('ps_name') }}" required>
                    @error('ps_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ps_color" class="form-label">สี</label>
                        <input type="text" class="form-control" id="ps_color" name="ps_color" value="{{ old('ps_color') }}" placeholder="#FFFFFF">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ps_rank" class="form-label">ลำดับ</label>
                        <input type="number" class="form-control" id="ps_rank" name="ps_rank" value="{{ old('ps_rank', 0) }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="ps_icon" class="form-label">Icon</label>
                    <input type="text" class="form-control" id="ps_icon" name="ps_icon" value="{{ old('ps_icon') }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojectstatus.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
