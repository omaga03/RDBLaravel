@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มแผนยุทธศาสตร์</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbstrategic.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="strategic_nameTH" class="form-label">ชื่อแผน (ภาษาไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('strategic_nameTH') is-invalid @enderror" id="strategic_nameTH" name="strategic_nameTH" value="{{ old('strategic_nameTH') }}" required>
                    @error('strategic_nameTH')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="strategic_nameEN" class="form-label">ชื่อแผน (English)</label>
                    <input type="text" class="form-control @error('strategic_nameEN') is-invalid @enderror" id="strategic_nameEN" name="strategic_nameEN" value="{{ old('strategic_nameEN') }}">
                    @error('strategic_nameEN')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbstrategic.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
