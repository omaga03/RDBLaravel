@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขงบประมาณโครงการ</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojectbudget.update', $item->ckb_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="pro_id" class="form-label">โครงการ</label>
                    <select class="form-select" id="pro_id" name="pro_id">
                        <option value="">-- เลือกโครงการ --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->pro_id }}" {{ old('pro_id', $item->pro_id) == $project->pro_id ? 'selected' : '' }}>{{ $project->pro_nameTH }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="ckb_annuity" class="form-label">ปีงบประมาณ</label>
                    <input type="number" class="form-control" id="ckb_annuity" name="ckb_annuity" value="{{ old('ckb_annuity', $item->ckb_annuity) }}">
                </div>
                <div class="mb-3">
                    <label for="ckb_note" class="form-label">หมายเหตุ</label>
                    <textarea class="form-control" id="ckb_note" name="ckb_note" rows="3">{{ old('ckb_note', $item->ckb_note) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="ckb_status" class="form-label">สถานะ</label>
                    <input type="number" class="form-control" id="ckb_status" name="ckb_status" value="{{ old('ckb_status', $item->ckb_status) }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojectbudget.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
