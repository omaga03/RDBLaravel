@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มสถานะวารสาร</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.journalstatus.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="jou_name" class="form-label">ชื่อวารสาร <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('jou_name') is-invalid @enderror" id="jou_name" name="jou_name" value="{{ old('jou_name') }}" required>
                    @error('jou_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jou_respon" class="form-label">ผู้รับผิดชอบ</label>
                        <input type="text" class="form-control" id="jou_respon" name="jou_respon" value="{{ old('jou_respon') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="jou_email" class="form-label">อีเมล</label>
                        <input type="email" class="form-control" id="jou_email" name="jou_email" value="{{ old('jou_email') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="jou_files" class="form-label">ไฟล์แนบ</label>
                    <input type="file" class="form-control" id="jou_files" name="jou_files">
                    <div class="form-text">รองรับไฟล์ขนาดไม่เกิน 10MB</div>
                </div>
                <div class="mb-3">
                    <label for="jou_note" class="form-label">หมายเหตุ</label>
                    <textarea class="form-control" id="jou_note" name="jou_note" rows="3">{{ old('jou_note') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jou_status" class="form-label">สถานะ</label>
                        <select class="form-select" id="jou_status" name="jou_status">
                            <option value="1" {{ old('jou_status') == 1 ? 'selected' : '' }}>ใช้งาน</option>
                            <option value="0" {{ old('jou_status') == 0 ? 'selected' : '' }}>ยกเลิก</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="data_show" class="form-label">แสดงข้อมูล</label>
                        <select class="form-select" id="data_show" name="data_show">
                            <option value="1" {{ old('data_show') == 1 ? 'selected' : '' }}>แสดง</option>
                            <option value="0" {{ old('data_show') == 0 ? 'selected' : '' }}>ซ่อน</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.journalstatus.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
