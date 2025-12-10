@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มการอบรม</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbtraining.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="tra_name" class="form-label">ชื่อการอบรม <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('tra_name') is-invalid @enderror" id="tra_name" name="tra_name" value="{{ old('tra_name') }}" required>
                    @error('tra_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="tra_description" class="form-label">รายละเอียด</label>
                    <textarea class="form-control" id="tra_description" name="tra_description" rows="3">{{ old('tra_description') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tra_datetimestart" class="form-label">วันเริ่ม</label>
                        <input type="date" class="form-control" id="tra_datetimestart" name="tra_datetimestart" value="{{ old('tra_datetimestart') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tra_datetimeend" class="form-label">วันสิ้นสุด</label>
                        <input type="date" class="form-control" id="tra_datetimeend" name="tra_datetimeend" value="{{ old('tra_datetimeend') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tra_place" class="form-label">สถานที่</label>
                        <input type="text" class="form-control" id="tra_place" name="tra_place" value="{{ old('tra_place') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tra_fee" class="form-label">ค่าลงทะเบียน</label>
                        <input type="number" step="0.01" class="form-control" id="tra_fee" name="tra_fee" value="{{ old('tra_fee') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="tra_url" class="form-label">URL</label>
                    <input type="url" class="form-control" id="tra_url" name="tra_url" value="{{ old('tra_url') }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbtraining.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
