@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มข่าวประชาสัมพันธ์</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.researchnews.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="news_name" class="form-label">หัวข้อข่าว <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('news_name') is-invalid @enderror" id="news_name" name="news_name" value="{{ old('news_name') }}" required>
                    @error('news_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="news_detail" class="form-label">รายละเอียด</label>
                    <textarea class="form-control" id="news_detail" name="news_detail" rows="5">{{ old('news_detail') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="news_date" class="form-label">วันที่ลงข่าว</label>
                        <input type="date" class="form-control" id="news_date" name="news_date" value="{{ old('news_date') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="news_img" class="form-label">รูปภาพประกอบ</label>
                        <input type="file" class="form-control" id="news_img" name="news_img">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="news_event_start" class="form-label">วันที่เริ่มกิจกรรม</label>
                        <input type="date" class="form-control" id="news_event_start" name="news_event_start" value="{{ old('news_event_start') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="news_event_end" class="form-label">วันที่สิ้นสุดกิจกรรม</label>
                        <input type="date" class="form-control" id="news_event_end" name="news_event_end" value="{{ old('news_event_end') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="news_event_guarantee" class="form-label">การรับรอง</label>
                    <input type="text" class="form-control" id="news_event_guarantee" name="news_event_guarantee" value="{{ old('news_event_guarantee') }}">
                </div>
                <div class="mb-3">
                    <label for="news_reference" class="form-label">อ้างอิง</label>
                    <input type="text" class="form-control" id="news_reference" name="news_reference" value="{{ old('news_reference') }}">
                </div>
                <div class="mb-3">
                    <label for="news_link" class="form-label">ลิงก์ที่เกี่ยวข้อง</label>
                    <input type="text" class="form-control" id="news_link" name="news_link" value="{{ old('news_link') }}">
                </div>
                <div class="mb-3">
                    <label for="news_count" class="form-label">จำนวนผู้เข้าชม</label>
                    <input type="number" class="form-control" id="news_count" name="news_count" value="{{ old('news_count', 0) }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.researchnews.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
