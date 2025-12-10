@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขกลุ่มโครงการ</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojectgroup.update', $item->group_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="pro_id" class="form-label">โครงการ <span class="text-danger">*</span></label>
                    <select class="form-select @error('pro_id') is-invalid @enderror" id="pro_id" name="pro_id" required>
                        <option value="">-- เลือกโครงการ --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->pro_id }}" {{ old('pro_id', $item->pro_id) == $project->pro_id ? 'selected' : '' }}>{{ $project->pro_nameTH }}</option>
                        @endforeach
                    </select>
                    @error('pro_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="pgroup_id" class="form-label">กลุ่มโครงการ <span class="text-danger">*</span></label>
                    <select class="form-select @error('pgroup_id') is-invalid @enderror" id="pgroup_id" name="pgroup_id" required>
                        <option value="">-- เลือกกลุ่มโครงการ --</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->pgroup_id }}" {{ old('pgroup_id', $item->pgroup_id) == $group->pgroup_id ? 'selected' : '' }}>{{ $group->pgroup_nameTH }}</option>
                        @endforeach
                    </select>
                    @error('pgroup_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojectgroup.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
