@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มปีงบประมาณ</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbyear.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="year_name" class="form-label">ปีงบประมาณ (พ.ศ.) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('year_name') is-invalid @enderror" id="year_name" name="year_name" value="{{ old('year_name', $nextYear) }}" required>
                    @error('year_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">ระบบจะเพิ่มปีถัดไปจากปีล่าสุดโดยอัตโนมัติ</div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbyear.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
