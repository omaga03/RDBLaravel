@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขข้อมูลการประชุมวิชาการ</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.researchcoferenceinthai.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="con_name" class="form-label">ชื่อการประชุม <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('con_name') is-invalid @enderror" id="con_name" name="con_name" value="{{ old('con_name', $item->con_name) }}" required>
                    @error('con_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="con_detail" class="form-label">รายละเอียด</label>
                    <textarea class="form-control" id="con_detail" name="con_detail" rows="4">{{ old('con_detail', $item->con_detail) }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="con_even_date" class="form-label">วันที่จัดงาน</label>
                        <input type="date" class="form-control" id="con_even_date" name="con_even_date" value="{{ old('con_even_date', $item->con_even_date) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="con_sub_deadline" class="form-label">วันหมดเขตส่งบทความ</label>
                        <input type="date" class="form-control" id="con_sub_deadline" name="con_sub_deadline" value="{{ old('con_sub_deadline', $item->con_sub_deadline) }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="con_venue" class="form-label">สถานที่จัดงาน</label>
                    <input type="text" class="form-control" id="con_venue" name="con_venue" value="{{ old('con_venue', $item->con_venue) }}">
                </div>
                <div class="mb-3">
                    <label for="con_website" class="form-label">เว็บไซต์</label>
                    <input type="text" class="form-control" id="con_website" name="con_website" value="{{ old('con_website', $item->con_website) }}">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="con_img" class="form-label">รูปภาพปก</label>
                        @if($item->con_img)
                            <div class="mb-2">
                                <img src="{{ Storage::url($item->con_img) }}" alt="Cover Image" style="max-height: 100px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="con_img" name="con_img">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="con_site_img" class="form-label">รูปภาพเว็บไซต์</label>
                        @if($item->con_site_img)
                            <div class="mb-2">
                                <img src="{{ Storage::url($item->con_site_img) }}" alt="Site Image" style="max-height: 100px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="con_site_img" name="con_site_img">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="con_count" class="form-label">จำนวนผู้เข้าชม</label>
                    <input type="number" class="form-control" id="con_count" name="con_count" value="{{ old('con_count', $item->con_count) }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.researchcoferenceinthai.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
