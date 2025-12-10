@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขการนำไปใช้ประโยชน์</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojectutilization.update', $item->uti_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="uti_nameTH" class="form-label">ชื่อการนำไปใช้ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('uti_nameTH') is-invalid @enderror" id="uti_nameTH" name="uti_nameTH" value="{{ old('uti_nameTH', $item->uti_nameTH) }}" required>
                    @error('uti_nameTH')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojectutilization.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
