@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มสาขาวิชา</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbdepmajor.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="maj_id" class="form-label">รหัสสาขา</label>
                    <input type="text" class="form-control" id="maj_id" name="maj_id" value="{{ old('maj_id') }}">
                </div>
                <div class="mb-3">
                    <label for="depcou_id" class="form-label">หลักสูตร</label>
                    <select class="form-select" id="depcou_id" name="depcou_id">
                        <option value="">-- เลือกหลักสูตร --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->depcou_id }}" {{ old('depcou_id') == $course->depcou_id ? 'selected' : '' }}>{{ $course->cou_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="department_id" class="form-label">หน่วยงาน</label>
                    <select class="form-select" id="department_id" name="department_id">
                        <option value="">-- เลือกหน่วยงาน --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}" {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_nameTH }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="maj_nameTH" class="form-label">ชื่อสาขา (ไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('maj_nameTH') is-invalid @enderror" id="maj_nameTH" name="maj_nameTH" value="{{ old('maj_nameTH') }}" required>
                    @error('maj_nameTH')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="maj_nameEN" class="form-label">ชื่อสาขา (อังกฤษ)</label>
                    <input type="text" class="form-control" id="maj_nameEN" name="maj_nameEN" value="{{ old('maj_nameEN') }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbdepmajor.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
