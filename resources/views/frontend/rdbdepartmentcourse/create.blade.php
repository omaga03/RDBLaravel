@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มหลักสูตร</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbdepartmentcourse.store') }}" method="POST">
                @csrf
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
                    <label for="depcat_id" class="form-label">ประเภทหน่วยงาน</label>
                    <select class="form-select" id="depcat_id" name="depcat_id">
                        <option value="">-- เลือกประเภท --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->depcat_id }}" {{ old('depcat_id') == $cat->depcat_id ? 'selected' : '' }}>{{ $cat->depcat_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="cou_name" class="form-label">ชื่อหลักสูตร <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('cou_name') is-invalid @enderror" id="cou_name" name="cou_name" value="{{ old('cou_name') }}" required>
                    @error('cou_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="cou_name_sh" class="form-label">ชื่อย่อหลักสูตร</label>
                    <input type="text" class="form-control" id="cou_name_sh" name="cou_name_sh" value="{{ old('cou_name_sh') }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbdepartmentcourse.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
