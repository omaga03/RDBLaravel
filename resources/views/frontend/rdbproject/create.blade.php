@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มโครงการวิจัยใหม่</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbproject.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <h5 class="text-primary mb-3">ข้อมูลทั่วไป</h5>
                <div class="mb-3">
                    <label for="pro_nameTH" class="form-label">ชื่อโครงการ (ภาษาไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('pro_nameTH') is-invalid @enderror" id="pro_nameTH" name="pro_nameTH" value="{{ old('pro_nameTH') }}" required>
                    @error('pro_nameTH')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="pro_nameEN" class="form-label">ชื่อโครงการ (ภาษาอังกฤษ)</label>
                    <input type="text" class="form-control" id="pro_nameEN" name="pro_nameEN" value="{{ old('pro_nameEN') }}">
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="pro_code" class="form-label">รหัสโครงการ</label>
                        <input type="text" class="form-control" id="pro_code" name="pro_code" value="{{ old('pro_code') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="year_id" class="form-label">ปีงบประมาณ <span class="text-danger">*</span></label>
                        <select class="form-select @error('year_id') is-invalid @enderror" id="year_id" name="year_id" required>
                            <option value="">-- เลือกปี --</option>
                            @foreach($years as $year)
                                <option value="{{ $year->year_id }}" {{ old('year_id') == $year->year_id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                            @endforeach
                        </select>
                        @error('year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="pro_budget" class="form-label">งบประมาณ (บาท)</label>
                        <input type="number" step="0.01" class="form-control" id="pro_budget" name="pro_budget" value="{{ old('pro_budget') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pt_id" class="form-label">ประเภททุนวิจัย</label>
                        <select class="form-select" id="pt_id" name="pt_id">
                            <option value="">-- เลือกประเภท --</option>
                            @foreach($projectTypes as $type)
                                <option value="{{ $type->pt_id }}" {{ old('pt_id') == $type->pt_id ? 'selected' : '' }}>{{ $type->pt_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ps_id" class="form-label">สถานะโครงการ</label>
                        <select class="form-select" id="ps_id" name="ps_id">
                            <option value="">-- เลือกสถานะ --</option>
                            @foreach($projectStatuses as $status)
                                <option value="{{ $status->ps_id }}" {{ old('ps_id') == $status->ps_id ? 'selected' : '' }}>{{ $status->ps_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="department_id" class="form-label">หน่วยงาน</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">-- เลือกหน่วยงาน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_nameTH }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="strategic_id" class="form-label">ยุทธศาสตร์</label>
                        <select class="form-select" id="strategic_id" name="strategic_id">
                            <option value="">-- เลือกยุทธศาสตร์ --</option>
                            @foreach($strategics as $strat)
                                <option value="{{ $strat->strategic_id }}" {{ old('strategic_id') == $strat->strategic_id ? 'selected' : '' }}>{{ $strat->strategic_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pro_date_start" class="form-label">วันที่เริ่มต้น</label>
                        <input type="date" class="form-control" id="pro_date_start" name="pro_date_start" value="{{ old('pro_date_start') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pro_date_end" class="form-label">วันที่สิ้นสุด</label>
                        <input type="date" class="form-control" id="pro_date_end" name="pro_date_end" value="{{ old('pro_date_end') }}">
                    </div>
                </div>

                <h5 class="text-primary mb-3 mt-4">นักวิจัยในโครงการ</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="researcher_table">
                        <thead>
                            <tr>
                                <th>นักวิจัย</th>
                                <th width="20%">ตำแหน่ง</th>
                                <th width="15%">สัดส่วน (%)</th>
                                <th width="5%">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="researcher_row_0">
                                <td>
                                    <select class="form-select" name="researcher_id[]">
                                        <option value="">-- เลือกนักวิจัย --</option>
                                        @foreach($researchers as $r)
                                            <option value="{{ $r->researcher_id }}">{{ $r->researcher_nameTH }} {{ $r->researcher_surnameTH }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select" name="position_id[]">
                                        <option value="">-- ตำแหน่ง --</option>
                                        @foreach($positions as $p)
                                            <option value="{{ $p->position_id }}">{{ $p->position_nameTH }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="ratio[]" placeholder="%" min="0" max="100">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(0)"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick="addRow()"><i class="bi bi-plus"></i> เพิ่มนักวิจัย</button>
                </div>

                <h5 class="text-primary mb-3 mt-4">รายละเอียดและไฟล์แนบ</h5>
                <div class="mb-3">
                    <label for="pro_abstract" class="form-label">บทคัดย่อ</label>
                    <textarea class="form-control" id="pro_abstract" name="pro_abstract" rows="5">{{ old('pro_abstract') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pro_abstract_file" class="form-label">ไฟล์บทคัดย่อ</label>
                        <input type="file" class="form-control" id="pro_abstract_file" name="pro_abstract_file">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pro_file" class="form-label">ไฟล์โครงการฉบับสมบูรณ์</label>
                        <input type="file" class="form-control" id="pro_file" name="pro_file">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="data_show" class="form-label">การแสดงผล</label>
                    <select class="form-select" id="data_show" name="data_show">
                        <option value="1" {{ old('data_show') == 1 ? 'selected' : '' }}>แสดง</option>
                        <option value="0" {{ old('data_show') == 0 ? 'selected' : '' }}>ซ่อน</option>
                    </select>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึกโครงการ</button>
                    <a href="{{ route('frontend.rdbproject.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let rowCount = 1;
    function addRow() {
        let table = document.getElementById("researcher_table").getElementsByTagName('tbody')[0];
        let newRow = table.insertRow(table.rows.length);
        newRow.id = "researcher_row_" + rowCount;
        
        let cell1 = newRow.insertCell(0);
        let cell2 = newRow.insertCell(1);
        let cell3 = newRow.insertCell(2);
        let cell4 = newRow.insertCell(3);

        cell1.innerHTML = `
            <select class="form-select" name="researcher_id[]">
                <option value="">-- เลือกนักวิจัย --</option>
                @foreach($researchers as $r)
                    <option value="{{ $r->researcher_id }}">{{ $r->researcher_nameTH }} {{ $r->researcher_surnameTH }}</option>
                @endforeach
            </select>
        `;
        cell2.innerHTML = `
            <select class="form-select" name="position_id[]">
                <option value="">-- ตำแหน่ง --</option>
                @foreach($positions as $p)
                    <option value="{{ $p->position_id }}">{{ $p->position_nameTH }}</option>
                @endforeach
            </select>
        `;
        cell3.innerHTML = '<input type="number" class="form-control" name="ratio[]" placeholder="%" min="0" max="100">';
        cell4.innerHTML = '<button type="button" class="btn btn-danger btn-sm" onclick="removeRow(' + rowCount + ')"><i class="bi bi-trash"></i></button>';
        
        rowCount++;
    }

    function removeRow(id) {
        let row = document.getElementById("researcher_row_" + id);
        row.parentNode.removeChild(row);
    }
</script>
@endsection
