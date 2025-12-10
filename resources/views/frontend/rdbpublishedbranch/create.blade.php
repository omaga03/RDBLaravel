@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มสาขาผลงาน</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbpublishedbranch.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="branch_name" class="form-label">ชื่อสาขา <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('branch_name') is-invalid @enderror" id="branch_name" name="branch_name" value="{{ old('branch_name') }}" required>
                    @error('branch_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbpublishedbranch.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
