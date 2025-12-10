@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขกิจกรรม/เหตุการณ์</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbdateevent.update', $item->ev_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label for="evt_id" class="form-label">ประเภทกิจกรรม</label>
                    <select class="form-select" id="evt_id" name="evt_id">
                        <option value="">-- เลือก --</option>
                        @foreach($eventTypes as $type)
                            <option value="{{ $type->evt_id }}" {{ old('evt_id', $item->evt_id) == $type->evt_id ? 'selected' : '' }}>{{ $type->evt_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="ev_title" class="form-label">หัวข้อ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('ev_title') is-invalid @enderror" id="ev_title" name="ev_title" value="{{ old('ev_title', $item->ev_title) }}" required>
                    @error('ev_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="ev_detail" class="form-label">รายละเอียด</label>
                    <textarea class="form-control" id="ev_detail" name="ev_detail" rows="4">{{ old('ev_detail', $item->ev_detail) }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ev_datestart" class="form-label">วันเริ่ม</label>
                        <input type="date" class="form-control" id="ev_datestart" name="ev_datestart" value="{{ old('ev_datestart', $item->ev_datestart) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ev_dateend" class="form-label">วันสิ้นสุด</label>
                        <input type="date" class="form-control" id="ev_dateend" name="ev_dateend" value="{{ old('ev_dateend', $item->ev_dateend) }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="ev_url" class="form-label">URL</label>
                    <input type="url" class="form-control" id="ev_url" name="ev_url" value="{{ old('ev_url', $item->ev_url) }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbdateevent.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
