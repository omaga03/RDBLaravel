@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขสถานะนักวิจัย</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbresearcherstatus.update', $item->restatus_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="restatus_name" class="form-label">ชื่อสถานะ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('restatus_name') is-invalid @enderror" id="restatus_name" name="restatus_name" value="{{ old('restatus_name', $item->restatus_name) }}" required>
                    @error('restatus_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbresearcherstatus.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
