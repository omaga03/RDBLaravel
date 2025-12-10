@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขประเภทกิจกรรม</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbdateeventtype.update', $item->evt_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="evt_name" class="form-label">ชื่อประเภท <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('evt_name') is-invalid @enderror" id="evt_name" name="evt_name" value="{{ old('evt_name', $item->evt_name) }}" required>
                    @error('evt_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="evt_color" class="form-label">สี</label>
                    <input type="text" class="form-control" id="evt_color" name="evt_color" value="{{ old('evt_color', $item->evt_color) }}" placeholder="#FFFFFF">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbdateeventtype.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
