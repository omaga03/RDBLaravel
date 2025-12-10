@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขประเภท DIP</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbdiptype.update', $item->dipt_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="dipt_name" class="form-label">ชื่อประเภท <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('dipt_name') is-invalid @enderror" id="dipt_name" name="dipt_name" value="{{ old('dipt_name', $item->dipt_name) }}" required>
                    @error('dipt_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbdiptype.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
