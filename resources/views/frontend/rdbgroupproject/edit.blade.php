@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขกลุ่มโครงการ</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbgroupproject.update', $item->pgroup_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="pgroup_nameTH" class="form-label">ชื่อกลุ่ม (ไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('pgroup_nameTH') is-invalid @enderror" id="pgroup_nameTH" name="pgroup_nameTH" value="{{ old('pgroup_nameTH', $item->pgroup_nameTH) }}" required>
                    @error('pgroup_nameTH')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="pgroup_nameEN" class="form-label">ชื่อกลุ่ม (English)</label>
                    <input type="text" class="form-control" id="pgroup_nameEN" name="pgroup_nameEN" value="{{ old('pgroup_nameEN', $item->pgroup_nameEN) }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbgroupproject.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
