@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มจังหวัด/อำเภอ/ตำบล</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbchangwat.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="changwat_t" class="form-label">จังหวัด (ไทย) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('changwat_t') is-invalid @enderror" id="changwat_t" name="changwat_t" value="{{ old('changwat_t') }}" required>
                        @error('changwat_t')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="changwat_e" class="form-label">จังหวัด (อังกฤษ)</label>
                        <input type="text" class="form-control" id="changwat_e" name="changwat_e" value="{{ old('changwat_e') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ch_id" class="form-label">รหัสจังหวัด</label>
                        <input type="text" class="form-control" id="ch_id" name="ch_id" value="{{ old('ch_id') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="amphoe_t" class="form-label">อำเภอ (ไทย)</label>
                        <input type="text" class="form-control" id="amphoe_t" name="amphoe_t" value="{{ old('amphoe_t') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="amphoe_e" class="form-label">อำเภอ (อังกฤษ)</label>
                        <input type="text" class="form-control" id="amphoe_e" name="amphoe_e" value="{{ old('amphoe_e') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="am_id" class="form-label">รหัสอำเภอ</label>
                        <input type="text" class="form-control" id="am_id" name="am_id" value="{{ old('am_id') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="tambon_t" class="form-label">ตำบล (ไทย)</label>
                        <input type="text" class="form-control" id="tambon_t" name="tambon_t" value="{{ old('tambon_t') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tambon_e" class="form-label">ตำบล (อังกฤษ)</label>
                        <input type="text" class="form-control" id="tambon_e" name="tambon_e" value="{{ old('tambon_e') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ta_id" class="form-label">รหัสตำบล</label>
                        <input type="text" class="form-control" id="ta_id" name="ta_id" value="{{ old('ta_id') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="lat" class="form-label">ละติจูด</label>
                        <input type="text" class="form-control" id="lat" name="lat" value="{{ old('lat') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="long" class="form-label">ลองจิจูด</label>
                        <input type="text" class="form-control" id="long" name="long" value="{{ old('long') }}">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbchangwat.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
