@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มการทำงานในโครงการ</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojectwork.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="pro_id" class="form-label">โครงการ <span class="text-danger">*</span></label>
                    <select class="form-select @error('pro_id') is-invalid @enderror" id="pro_id" name="pro_id" required>
                        <option value="">-- เลือกโครงการ --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->pro_id }}" {{ old('pro_id') == $project->pro_id ? 'selected' : '' }}>{{ $project->pro_nameTH }}</option>
                        @endforeach
                    </select>
                    @error('pro_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="researcher_id" class="form-label">นักวิจัย</label>
                    <select class="form-select" id="researcher_id" name="researcher_id">
                        <option value="">-- เลือกนักวิจัย --</option>
                        @foreach($researchers as $researcher)
                            <option value="{{ $researcher->researcher_id }}" {{ old('researcher_id') == $researcher->researcher_id ? 'selected' : '' }}>{{ $researcher->researcher_nameTH }} {{ $researcher->researcher_surnameTH }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="position_id" class="form-label">ตำแหน่งในโครงการ</label>
                    <select class="form-select" id="position_id" name="position_id">
                        <option value="">-- เลือกตำแหน่ง --</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->position_id }}" {{ old('position_id') == $position->position_id ? 'selected' : '' }}>{{ $position->position_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="ratio" class="form-label">สัดส่วน (%)</label>
                    <input type="number" step="0.01" class="form-control" id="ratio" name="ratio" value="{{ old('ratio') }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojectwork.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
