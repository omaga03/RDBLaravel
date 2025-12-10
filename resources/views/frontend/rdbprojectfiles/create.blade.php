@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มไฟล์โครงการ</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojectfiles.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="pro_id" class="form-label">โครงการ <span class="text-danger">*</span></label>
                    <select class="form-select @error('pro_id') is-invalid @enderror" id="pro_id" name="pro_id" required>
                        <option value="">-- เลือกโครงการ --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->pro_id }}" {{ old('pro_id') == $project->pro_id ? 'selected' : '' }}>{{ $project->pro_nameTH }}</option>
                        @endforeach
                    </select>
                    @error('pro_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="rf_files" class="form-label">ไฟล์แนบ</label>
                    <input type="file" class="form-control" id="rf_files" name="rf_files">
                    <div class="form-text">รองรับไฟล์ขนาดไม่เกิน 10MB</div>
                </div>
                <div class="mb-3">
                    <label for="rf_note" class="form-label">หมายเหตุ</label>
                    <textarea class="form-control" id="rf_note" name="rf_note" rows="3">{{ old('rf_note') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="rf_download" class="form-label">จำนวนดาวน์โหลด</label>
                        <input type="number" class="form-control" id="rf_download" name="rf_download" value="{{ old('rf_download', 0) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="rf_files_show" class="form-label">แสดงไฟล์</label>
                        <select class="form-select" id="rf_files_show" name="rf_files_show">
                            <option value="1" {{ old('rf_files_show') == 1 ? 'selected' : '' }}>แสดง</option>
                            <option value="0" {{ old('rf_files_show') == 0 ? 'selected' : '' }}>ซ่อน</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojectfiles.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
