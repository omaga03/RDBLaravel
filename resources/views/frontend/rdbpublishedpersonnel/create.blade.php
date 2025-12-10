@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มบุคลากรผลงาน</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbpublishedpersonnel.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="year_id" class="form-label">ปี</label>
                        <select class="form-select" id="year_id" name="year_id">
                            <option value="">-- เลือกปี --</option>
                            @foreach($years as $year)
                                <option value="{{ $year->year_id }}" {{ old('year_id') == $year->year_id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="department_id" class="form-label">หน่วยงาน</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">-- เลือกหน่วยงาน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_nameTH }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="depcat_id" class="form-label">ประเภทหน่วยงาน</label>
                        <select class="form-select" id="depcat_id" name="depcat_id">
                            <option value="">-- เลือกประเภท --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->depcat_id }}" {{ old('depcat_id') == $cat->depcat_id ? 'selected' : '' }}>{{ $cat->depcat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="personnel_num" class="form-label">จำนวนบุคลากร</label>
                        <input type="number" class="form-control" id="personnel_num" name="personnel_num" value="{{ old('personnel_num') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="personnel_numedu" class="form-label">จำนวนการศึกษา</label>
                        <input type="number" class="form-control" id="personnel_numedu" name="personnel_numedu" value="{{ old('personnel_numedu') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="personnel_numbud" class="form-label">จำนวนงบประมาณ</label>
                        <input type="number" class="form-control" id="personnel_numbud" name="personnel_numbud" value="{{ old('personnel_numbud') }}">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbpublishedpersonnel.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
