@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขประเภทผู้แต่ง</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbpublishedtypeauthor.update', $item->pubta_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="pubta_nameTH" class="form-label">ชื่อประเภท (ไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('pubta_nameTH') is-invalid @enderror" id="pubta_nameTH" name="pubta_nameTH" value="{{ old('pubta_nameTH', $item->pubta_nameTH) }}" required>
                    @error('pubta_nameTH')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="pubta_nameEN" class="form-label">ชื่อประเภท (English)</label>
                    <input type="text" class="form-control" id="pubta_nameEN" name="pubta_nameEN" value="{{ old('pubta_nameEN', $item->pubta_nameEN) }}">
                </div>
                <div class="mb-3">
                    <label for="pubta_score" class="form-label">คะแนน</label>
                    <input type="number" step="0.01" class="form-control" id="pubta_score" name="pubta_score" value="{{ old('pubta_score', $item->pubta_score) }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbpublishedtypeauthor.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
