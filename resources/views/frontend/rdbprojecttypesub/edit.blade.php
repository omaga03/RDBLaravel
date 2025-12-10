@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขประเภทโครงการย่อย</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojecttypesub.update', $item->prot_sub_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="prot_id" class="form-label">ประเภทโครงการ</label>
                    <select class="form-select" id="prot_id" name="prot_id">
                        <option value="">-- เลือกประเภทโครงการ --</option>
                        @foreach($projectTypes as $type)
                            <option value="{{ $type->prot_id }}" {{ old('prot_id', $item->prot_id) == $type->prot_id ? 'selected' : '' }}>{{ $type->prot_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="prot_sub_name" class="form-label">ชื่อประเภทโครงการย่อย <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('prot_sub_name') is-invalid @enderror" id="prot_sub_name" name="prot_sub_name" value="{{ old('prot_sub_name', $item->prot_sub_name) }}" required>
                    @error('prot_sub_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojecttypesub.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
