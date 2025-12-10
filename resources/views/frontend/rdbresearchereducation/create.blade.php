@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มประวัติการศึกษา</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbresearchereducation.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="researcher_id" class="form-label">นักวิจัย</label>
                    <select class="form-select" id="researcher_id" name="researcher_id">
                        <option value="">-- เลือกนักวิจัย --</option>
                        @foreach($researchers as $researcher)
                            <option value="{{ $researcher->researcher_id }}" {{ old('researcher_id') == $researcher->researcher_id ? 'selected' : '' }}>{{ $researcher->researcher_fullname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="reedu_status" class="form-label">สถานะ</label>
                        <input type="text" class="form-control" id="reedu_status" name="reedu_status" value="{{ old('reedu_status') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="reedu_year" class="form-label">ปีที่จบ</label>
                        <input type="number" class="form-control" id="reedu_year" name="reedu_year" value="{{ old('reedu_year') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="reedu_type" class="form-label">ระดับการศึกษา</label>
                    <input type="text" class="form-control" id="reedu_type" name="reedu_type" value="{{ old('reedu_type') }}">
                </div>
                <div class="mb-3">
                    <label for="reedu_name" class="form-label">ชื่อวุฒิการศึกษา <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('reedu_name') is-invalid @enderror" id="reedu_name" name="reedu_name" value="{{ old('reedu_name') }}" required>
                    @error('reedu_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="reedu_department" class="form-label">ภาควิชา</label>
                        <input type="text" class="form-control" id="reedu_department" name="reedu_department" value="{{ old('reedu_department') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="reedu_major" class="form-label">สาขาวิชา</label>
                        <input type="text" class="form-control" id="reedu_major" name="reedu_major" value="{{ old('reedu_major') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="reedu_cational" class="form-label">สถาบัน</label>
                    <input type="text" class="form-control" id="reedu_cational" name="reedu_cational" value="{{ old('reedu_cational') }}">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbresearchereducation.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
