@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มข้อมูลทรัพย์สินทางปัญญา (DIP)</h4></div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbdip.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <h5 class="text-primary mb-3">ข้อมูลทั่วไป</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="dip_type" class="form-label">ประเภท</label>
                        <select class="form-select" id="dip_type" name="dip_type">
                            <option value="">-- เลือกประเภท --</option>
                            <option value="1" {{ old('dip_type') == 1 ? 'selected' : '' }}>สิทธิบัตร</option>
                            <option value="2" {{ old('dip_type') == 2 ? 'selected' : '' }}>อนุสิทธิบัตร</option>
                            <option value="3" {{ old('dip_type') == 3 ? 'selected' : '' }}>ลิขสิทธิ์</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="dipt_id" class="form-label">ชนิด</label>
                        <select class="form-select" id="dipt_id" name="dipt_id">
                            <option value="">-- เลือกชนิด --</option>
                            @foreach($dipTypes as $type)
                                <option value="{{ $type->dipt_id }}" {{ old('dipt_id') == $type->dipt_id ? 'selected' : '' }}>{{ $type->dipt_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pro_id" class="form-label">โครงการ</label>
                        <select class="form-select" id="pro_id" name="pro_id">
                            <option value="">-- เลือกโครงการ --</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->pro_id }}" {{ old('pro_id') == $project->pro_id ? 'selected' : '' }}>{{ $project->pro_nameTH }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="researcher_id" class="form-label">นักวิจัย</label>
                        <select class="form-select" id="researcher_id" name="researcher_id">
                            <option value="">-- เลือกนักวิจัย --</option>
                            @foreach($researchers as $researcher)
                                <option value="{{ $researcher->researcher_id }}" {{ old('researcher_id') == $researcher->researcher_id ? 'selected' : '' }}>{{ $researcher->researcher_nameTH }} {{ $researcher->researcher_surnameTH }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h5 class="text-primary mb-3 mt-4">เลขที่และวันที่</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="dip_request_number" class="form-label">เลขที่คำขอ</label>
                        <input type="text" class="form-control" id="dip_request_number" name="dip_request_number" value="{{ old('dip_request_number') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="dip_request_date" class="form-label">วันที่ยื่นคำขอ</label>
                        <input type="date" class="form-control" id="dip_request_date" name="dip_request_date" value="{{ old('dip_request_date') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="dip_request_dateget" class="form-label">วันที่ได้รับ</label>
                        <input type="date" class="form-control" id="dip_request_dateget" name="dip_request_dateget" value="{{ old('dip_request_dateget') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="dip_number" class="form-label">เลขที่ทะเบียน</label>
                        <input type="text" class="form-control" id="dip_number" name="dip_number" value="{{ old('dip_number') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="dip_publication_no" class="form-label">เลขที่ประกาศโฆษณา</label>
                        <input type="text" class="form-control" id="dip_publication_no" name="dip_publication_no" value="{{ old('dip_publication_no') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="dip_publication_date" class="form-label">วันที่ประกาศโฆษณา</label>
                        <input type="date" class="form-control" id="dip_publication_date" name="dip_publication_date" value="{{ old('dip_publication_date') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="dip_patent_number" class="form-label">เลขที่สิทธิบัตร</label>
                    <input type="text" class="form-control" id="dip_patent_number" name="dip_patent_number" value="{{ old('dip_patent_number') }}">
                </div>

                <h5 class="text-primary mb-3 mt-4">ไฟล์แนบ</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="dip_files" class="form-label">ไฟล์หลัก</label>
                        <input type="file" class="form-control" id="dip_files" name="dip_files">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="dip_data1_files" class="form-label">ไฟล์ข้อมูล 1</label>
                        <input type="file" class="form-control" id="dip_data1_files" name="dip_data1_files">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="dip_data2_files_con" class="form-label">ไฟล์สรุปผล</label>
                        <input type="file" class="form-control" id="dip_data2_files_con" name="dip_data2_files_con">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="dip_data_forms_request" class="form-label">แบบฟอร์มคำขอ</label>
                        <input type="file" class="form-control" id="dip_data_forms_request" name="dip_data_forms_request">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="dip_data3_filesass1" class="form-label">ไฟล์แนบ 1</label>
                        <input type="file" class="form-control" id="dip_data3_filesass1" name="dip_data3_filesass1">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="dip_data3_filesass2" class="form-label">ไฟล์แนบ 2</label>
                        <input type="file" class="form-control" id="dip_data3_filesass2" name="dip_data3_filesass2">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="dip_data3_filesass3" class="form-label">ไฟล์แนบ 3</label>
                        <input type="file" class="form-control" id="dip_data3_filesass3" name="dip_data3_filesass3">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="dip_data3_drawing_picture" class="form-label">รูปเขียน/รูปภาพ</label>
                    <input type="file" class="form-control" id="dip_data3_drawing_picture" name="dip_data3_drawing_picture">
                </div>

                <h5 class="text-primary mb-3 mt-4">อื่นๆ</h5>
                <div class="mb-3">
                    <label for="dip_note" class="form-label">หมายเหตุ</label>
                    <textarea class="form-control" id="dip_note" name="dip_note" rows="3">{{ old('dip_note') }}</textarea>
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
                    <a href="{{ route('frontend.rdbdip.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
