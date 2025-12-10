@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มบุคลากรโครงการ (ตามหน่วยงาน)</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbprojectpersonneldep.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="year_id" class="form-label">ปี</label>
                        <select class="form-select" id="year_id" name="year_id">
                            <option value="">-- เลือกปี --</option>
                            @foreach($years as $year)
                                <option value="{{ $year->year_id }}" {{ old('year_id') == $year->year_id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="department_id" class="form-label">หน่วยงาน</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">-- เลือกหน่วยงาน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_nameTH }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="depcou_id" class="form-label">หลักสูตร</label>
                        <select class="form-select" id="depcou_id" name="depcou_id">
                            <option value="">-- เลือกหลักสูตร --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->depcou_id }}" {{ old('depcou_id') == $course->depcou_id ? 'selected' : '' }}>{{ $course->cou_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="major_id" class="form-label">สาขาวิชา</label>
                        <select class="form-select" id="major_id" name="major_id">
                            <option value="">-- เลือกสาขาวิชา --</option>
                            @foreach($majors as $major)
                                <option value="{{ $major->maj_code }}" {{ old('major_id') == $major->maj_code ? 'selected' : '' }}>{{ $major->maj_nameTH }}</option>
                            @endforeach
                        </select>
                    </div>
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
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pp_num" class="form-label">จำนวนบุคลากร</label>
                        <input type="number" class="form-control" id="pp_num" name="pp_num" value="{{ old('pp_num') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pp_standard" class="form-label">มาตรฐาน</label>
                        <input type="number" class="form-control" id="pp_standard" name="pp_standard" value="{{ old('pp_standard') }}">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbprojectpersonneldep.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
