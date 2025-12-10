@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขเปอร์เซ็นต์สาขา</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbpublishedbranchper.update', $item->branchper_id) }}" method="POST">
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
                <div class="mb-3">
                    <label for="branch_id" class="form-label">สาขา</label>
                    <select class="form-select" id="branch_id" name="branch_id">
                        <option value="">-- เลือกสาขา --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->branch_id }}" {{ old('branch_id', $item->branch_id) == $branch->branch_id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="branchper_percent" class="form-label">เปอร์เซ็นต์</label>
                    <input type="number" step="0.01" class="form-control" id="branchper_percent" name="branchper_percent" value="{{ old('branchper_percent', $item->branchper_percent) }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbpublishedbranchper.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
