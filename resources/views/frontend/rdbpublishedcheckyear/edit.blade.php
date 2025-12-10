@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขปีตรวจสอบผลงาน</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbpublishedcheckyear.update', $item->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="year_id" class="form-label">ปี</label>
                    <select class="form-select" id="year_id" name="year_id">
                        <option value="">-- เลือกปี --</option>
                        @foreach($years as $year)
                            <option value="{{ $year->year_id }}" {{ old('year_id', $item->year_id) == $year->year_id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="rdbyearedu_start" class="form-label">ปีการศึกษา เริ่ม</label>
                        <input type="number" class="form-control" id="rdbyearedu_start" name="rdbyearedu_start" value="{{ old('rdbyearedu_start', $item->rdbyearedu_start) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="rdbyearedu_end" class="form-label">ปีการศึกษา สิ้นสุด</label>
                        <input type="number" class="form-control" id="rdbyearedu_end" name="rdbyearedu_end" value="{{ old('rdbyearedu_end', $item->rdbyearedu_end) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="rdbyearbud_start" class="form-label">ปีงบประมาณ เริ่ม</label>
                        <input type="number" class="form-control" id="rdbyearbud_start" name="rdbyearbud_start" value="{{ old('rdbyearbud_start', $item->rdbyearbud_start) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="rdbyearbud_end" class="form-label">ปีงบประมาณ สิ้นสุด</label>
                        <input type="number" class="form-control" id="rdbyearbud_end" name="rdbyearbud_end" value="{{ old('rdbyearbud_end', $item->rdbyearbud_end) }}">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbpublishedcheckyear.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
