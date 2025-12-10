@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> ลงทะเบียนอบรม</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbtrainingregister.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="tra_id" class="form-label">การอบรม</label>
                    <select class="form-select" id="tra_id" name="tra_id">
                        <option value="">-- เลือกการอบรม --</option>
                        @foreach($trainings as $training)
                            <option value="{{ $training->tra_id }}" {{ old('tra_id') == $training->tra_id ? 'selected' : '' }}>{{ $training->tra_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="treg_perfix" class="form-label">คำนำหน้า</label>
                        <input type="text" class="form-control" id="treg_perfix" name="treg_perfix" value="{{ old('treg_perfix') }}">
                    </div>
                    <div class="col-md-10 mb-3">
                        <label for="treg_name" class="form-label">ชื่อ-สกุล <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('treg_name') is-invalid @enderror" id="treg_name" name="treg_name" value="{{ old('treg_name') }}" required>
                        @error('treg_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="treg_department" class="form-label">หน่วยงาน</label>
                        <input type="text" class="form-control" id="treg_department" name="treg_department" value="{{ old('treg_department') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="treg_position" class="form-label">ตำแหน่ง</label>
                        <input type="text" class="form-control" id="treg_position" name="treg_position" value="{{ old('treg_position') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="treg_address" class="form-label">ที่อยู่</label>
                    <textarea class="form-control" id="treg_address" name="treg_address" rows="2">{{ old('treg_address') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="treg_tel" class="form-label">โทรศัพท์</label>
                        <input type="text" class="form-control" id="treg_tel" name="treg_tel" value="{{ old('treg_tel') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="treg_email" class="form-label">อีเมล</label>
                        <input type="email" class="form-control" id="treg_email" name="treg_email" value="{{ old('treg_email') }}">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbtrainingregister.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
