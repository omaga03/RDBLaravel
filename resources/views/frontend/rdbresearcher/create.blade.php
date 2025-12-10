@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-person-plus"></i> เพิ่มข้อมูลนักวิจัย</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbresearcher.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <h5 class="text-primary mb-3">ข้อมูลส่วนตัว</h5>
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="prefix_id" class="form-label">คำนำหน้า <span class="text-danger">*</span></label>
                        <select class="form-select @error('prefix_id') is-invalid @enderror" id="prefix_id" name="prefix_id" required>
                            <option value="">-- เลือก --</option>
                            @foreach($prefixes as $prefix)
                                <option value="{{ $prefix->prefix_id }}" {{ old('prefix_id') == $prefix->prefix_id ? 'selected' : '' }}>{{ $prefix->prefix_name }}</option>
                            @endforeach
                        </select>
                        @error('prefix_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="researcher_fname" class="form-label">ชื่อ (ไทย) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('researcher_fname') is-invalid @enderror" id="researcher_fname" name="researcher_fname" value="{{ old('researcher_fname') }}" required>
                        @error('researcher_fname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="researcher_lname" class="form-label">นามสกุล (ไทย) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('researcher_lname') is-invalid @enderror" id="researcher_lname" name="researcher_lname" value="{{ old('researcher_lname') }}" required>
                        @error('researcher_lname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="researcher_fnameEN" class="form-label">ชื่อ (อังกฤษ)</label>
                        <input type="text" class="form-control" id="researcher_fnameEN" name="researcher_fnameEN" value="{{ old('researcher_fnameEN') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="researcher_lnameEN" class="form-label">นามสกุล (อังกฤษ)</label>
                        <input type="text" class="form-control" id="researcher_lnameEN" name="researcher_lnameEN" value="{{ old('researcher_lnameEN') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">เพศ</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="researcher_gender" id="genderM" value="M" {{ old('researcher_gender') == 'M' ? 'checked' : '' }}>
                            <label class="form-check-label" for="genderM">ชาย</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="researcher_gender" id="genderF" value="F" {{ old('researcher_gender') == 'F' ? 'checked' : '' }}>
                            <label class="form-check-label" for="genderF">หญิง</label>
                        </div>
                    </div>
                </div>

                <h5 class="text-primary mb-3 mt-4">สังกัดและสถานะ</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="department_id" class="form-label">หน่วยงาน <span class="text-danger">*</span></label>
                        <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id" required>
                            <option value="">-- เลือกหน่วยงาน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_nameTH }}</option>
                            @endforeach
                        </select>
                        @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="maj_id" class="form-label">สาขาวิชา</label>
                        <select class="form-select" id="maj_id" name="maj_id">
                            <option value="">-- เลือกสาขาวิชา --</option>
                            @foreach($majors as $major)
                                <option value="{{ $major->maj_id }}" {{ old('maj_id') == $major->maj_id ? 'selected' : '' }}>{{ $major->maj_nameTH }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="restatus_id" class="form-label">สถานะการทำงาน</label>
                    <select class="form-select" id="restatus_id" name="restatus_id">
                        <option value="">-- เลือกสถานะ --</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->restatus_id }}" {{ old('restatus_id') == $status->restatus_id ? 'selected' : '' }}>{{ $status->restatus_name }}</option>
                        @endforeach
                    </select>
                </div>

                <h5 class="text-primary mb-3 mt-4">ข้อมูลติดต่อ</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="researcher_email" class="form-label">อีเมล</label>
                        <input type="email" class="form-control" id="researcher_email" name="researcher_email" value="{{ old('researcher_email') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="researcher_tel" class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control" id="researcher_tel" name="researcher_tel" value="{{ old('researcher_tel') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="researcher_mobile" class="form-label">เบอร์มือถือ</label>
                        <input type="text" class="form-control" id="researcher_mobile" name="researcher_mobile" value="{{ old('researcher_mobile') }}">
                    </div>
                </div>

                <h5 class="text-primary mb-3 mt-4">อื่นๆ</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="scopus_authorId" class="form-label">Scopus Author ID</label>
                        <input type="text" class="form-control" id="scopus_authorId" name="scopus_authorId" value="{{ old('scopus_authorId') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="orcid" class="form-label">ORCID</label>
                        <input type="text" class="form-control" id="orcid" name="orcid" value="{{ old('orcid') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="researcher_picture" class="form-label">รูปถ่าย</label>
                    <input type="file" class="form-control" id="researcher_picture" name="researcher_picture">
                </div>
                <div class="mb-3">
                    <label for="researcher_note" class="form-label">หมายเหตุ</label>
                    <textarea class="form-control" id="researcher_note" name="researcher_note" rows="3">{{ old('researcher_note') }}</textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="{{ route('frontend.rdbresearcher.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
