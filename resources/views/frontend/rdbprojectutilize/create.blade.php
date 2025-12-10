@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มข้อมูลการนำไปใช้ประโยชน์</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojectutilize.store') }}" method="POST" enctype="multipart/form-data">
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
                    <label for="utz_detail" class="form-label">รายละเอียดการนำไปใช้ประโยชน์</label>
                    <textarea class="form-control" id="utz_detail" name="utz_detail" rows="4">{{ old('utz_detail') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="utz_date" class="form-label">วันที่</label>
                        <input type="date" class="form-control" id="utz_date" name="utz_date" value="{{ old('utz_date') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="chw_id" class="form-label">จังหวัด</label>
                        <select class="form-select" id="chw_id" name="chw_id">
                            <option value="">-- เลือกจังหวัด --</option>
                            @foreach($changwats as $changwat)
                                <option value="{{ $changwat->chw_id }}" {{ old('chw_id') == $changwat->chw_id ? 'selected' : '' }}>{{ $changwat->chw_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="utz_leading" class="form-label">ผู้นำไปใช้ประโยชน์</label>
                        <input type="text" class="form-control" id="utz_leading" name="utz_leading" value="{{ old('utz_leading') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="utz_leading_position" class="form-label">ตำแหน่ง</label>
                        <input type="text" class="form-control" id="utz_leading_position" name="utz_leading_position" value="{{ old('utz_leading_position') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="utz_department_name" class="form-label">หน่วยงาน</label>
                    <input type="text" class="form-control" id="utz_department_name" name="utz_department_name" value="{{ old('utz_department_name') }}">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="utz_budget" class="form-label">งบประมาณ</label>
                        <input type="number" step="0.01" class="form-control" id="utz_budget" name="utz_budget" value="{{ old('utz_budget') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="utz_files" class="form-label">ไฟล์แนบ</label>
                        <input type="file" class="form-control" id="utz_files" name="utz_files">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="utz_year_id" class="form-label">ปี พ.ศ.</label>
                        <input type="number" class="form-control" id="utz_year_id" name="utz_year_id" value="{{ old('utz_year_id') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="utz_year_bud" class="form-label">ปีงบประมาณ</label>
                        <input type="number" class="form-control" id="utz_year_bud" name="utz_year_bud" value="{{ old('utz_year_bud') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="utz_year_edu" class="form-label">ปีการศึกษา</label>
                        <input type="number" class="form-control" id="utz_year_edu" name="utz_year_edu" value="{{ old('utz_year_edu') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="data_show" class="form-label">แสดงข้อมูล</label>
                    <select class="form-select" id="data_show" name="data_show">
                        <option value="1" {{ old('data_show') == 1 ? 'selected' : '' }}>แสดง</option>
                        <option value="0" {{ old('data_show') == 0 ? 'selected' : '' }}>ซ่อน</option>
                    </select>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
