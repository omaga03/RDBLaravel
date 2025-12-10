@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขคำนำหน้าชื่อ</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprefix.update', $item->prefix_id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="prefix_nameTH" class="form-label">คำนำหน้า (ไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('prefix_nameTH') is-invalid @enderror" id="prefix_nameTH" name="prefix_nameTH" value="{{ old('prefix_nameTH', $item->prefix_nameTH) }}" required>
                    @error('prefix_nameTH')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="prefix_nameEN" class="form-label">คำนำหน้า (English)</label>
                    <input type="text" class="form-control @error('prefix_nameEN') is-invalid @enderror" id="prefix_nameEN" name="prefix_nameEN" value="{{ old('prefix_nameEN', $item->prefix_nameEN) }}">
                    @error('prefix_nameEN')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprefix.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
