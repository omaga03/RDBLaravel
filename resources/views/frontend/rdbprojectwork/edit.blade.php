@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขการทำงานในโครงการ</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojectwork.update', $item->pro_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">โครงการ</label>
                    <input type="text" class="form-control" value="{{ $item->project->pro_nameTH ?? $item->pro_id }}" disabled>
                    <div class="form-text">ไม่สามารถแก้ไขโครงการได้</div>
                </div>
                <div class="mb-3">
                    <label for="researcher_id" class="form-label">นักวิจัย</label>
                    <select class="form-select" id="researcher_id" name="researcher_id">
                        <option value="">-- เลือกนักวิจัย --</option>
                        @foreach($researchers as $researcher)
                            <option value="{{ $researcher->researcher_id }}" {{ old('researcher_id', $item->researcher_id) == $researcher->researcher_id ? 'selected' : '' }}>{{ $researcher->researcher_nameTH }} {{ $researcher->researcher_surnameTH }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="position_id" class="form-label">ตำแหน่งในโครงการ</label>
                    <select class="form-select" id="position_id" name="position_id">
                        <option value="">-- เลือกตำแหน่ง --</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->position_id }}" {{ old('position_id', $item->position_id) == $position->position_id ? 'selected' : '' }}>{{ $position->position_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="ratio" class="form-label">สัดส่วน (%)</label>
                    <input type="number" step="0.01" class="form-control" id="ratio" name="ratio" value="{{ old('ratio', $item->ratio) }}">
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
