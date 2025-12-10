@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-pencil"></i> แก้ไขข้อมูลผลงานตีพิมพ์</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('frontend.rdbpublished.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                
                <h5 class="text-primary mb-3">ข้อมูลทั่วไป</h5>
                <div class="mb-3">
                    <label for="pub_name" class="form-label">ชื่อผลงาน <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('pub_name') is-invalid @enderror" id="pub_name" name="pub_name" value="{{ old('pub_name', $item->pub_name) }}" required>
                    @error('pub_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="pub_name_journal" class="form-label">ชื่อวารสาร/แหล่งตีพิมพ์</label>
                    <input type="text" class="form-control" id="pub_name_journal" name="pub_name_journal" value="{{ old('pub_name_journal', $item->pub_name_journal) }}">
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="year_id" class="form-label">ปีงบประมาณ <span class="text-danger">*</span></label>
                        <select class="form-select @error('year_id') is-invalid @enderror" id="year_id" name="year_id" required>
                            <option value="">-- เลือกปี --</option>
                            @foreach($years as $year)
                                <option value="{{ $year->year_id }}" {{ old('year_id', $item->year_id) == $year->year_id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                            @endforeach
                        </select>
                        @error('year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="pubtype_id" class="form-label">ประเภทผลงาน</label>
                        <select class="form-select" id="pubtype_id" name="pubtype_id">
                            <option value="">-- เลือกประเภท --</option>
                            @foreach($pubtypes as $type)
                                <option value="{{ $type->pubtype_id }}" {{ old('pubtype_id', $item->pubtype_id) == $type->pubtype_id ? 'selected' : '' }}>{{ $type->pubtype_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="pub_date" class="form-label">วันที่ตีพิมพ์</label>
                        <input type="date" class="form-control" id="pub_date" name="pub_date" value="{{ old('pub_date', $item->pub_date) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="researcher_id" class="form-label">เจ้าของผลงานหลัก <span class="text-danger">*</span></label>
                        <select class="form-select @error('researcher_id') is-invalid @enderror" id="researcher_id" name="researcher_id" required>
                            <option value="">-- เลือกนักวิจัย --</option>
                            @foreach($researchers as $r)
                                <option value="{{ $r->researcher_id }}" {{ old('researcher_id', $item->researcher_id) == $r->researcher_id ? 'selected' : '' }}>{{ $r->researcher_nameTH }} {{ $r->researcher_surnameTH }}</option>
                            @endforeach
                        </select>
                        @error('researcher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="department_id" class="form-label">หน่วยงาน</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">-- เลือกหน่วยงาน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" {{ old('department_id', $item->department_id) == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_nameTH }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="pro_id" class="form-label">โครงการที่เกี่ยวข้อง (ถ้ามี)</label>
                    <select class="form-select" id="pro_id" name="pro_id">
                        <option value="">-- เลือกโครงการ --</option>
                        @foreach($projects as $pro)
                            <option value="{{ $pro->pro_id }}" {{ old('pro_id', $item->pro_id) == $pro->pro_id ? 'selected' : '' }}>{{ $pro->pro_nameTH }}</option>
                        @endforeach
                    </select>
                </div>

                <h5 class="text-primary mb-3 mt-4">ผู้เขียนบทความร่วม (Co-Authors)</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="author_table">
                        <thead>
                            <tr>
                                <th>นักวิจัย</th>
                                <th width="30%">ประเภทผู้เขียนบทความ</th>
                                <th width="5%">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item->authors as $index => $author)
                            <tr id="author_row_{{ $index }}">
                                <td>
                                    <select class="form-select" name="author_id[]">
                                        <option value="">-- เลือกนักวิจัย --</option>
                                        @foreach($researchers as $r)
                                            <option value="{{ $r->researcher_id }}" {{ $author->researcher_id == $r->researcher_id ? 'selected' : '' }}>{{ $r->researcher_nameTH }} {{ $r->researcher_surnameTH }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select" name="pubta_id[]">
                                        <option value="">-- เลือกประเภท --</option>
                                        @foreach($authorTypes as $type)
                                            <option value="{{ $type->pubta_id }}" {{ $author->pivot->pubta_id == $type->pubta_id ? 'selected' : '' }}>{{ $type->pubta_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow({{ $index }})"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick="addRow()"><i class="bi bi-plus"></i> เพิ่มผู้เขียนบทความร่วม</button>
                </div>

                <h5 class="text-primary mb-3 mt-4">รายละเอียดเพิ่มเติม</h5>
                <div class="mb-3">
                    <label for="pub_abstract" class="form-label">บทคัดย่อ</label>
                    <textarea class="form-control" id="pub_abstract" name="pub_abstract" rows="4">{{ old('pub_abstract', $item->pub_abstract) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="pub_keyword" class="form-label">คำสำคัญ (Keywords)</label>
                    <input type="text" class="form-control" id="pub_keyword" name="pub_keyword" value="{{ old('pub_keyword', $item->pub_keyword) }}">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pub_budget" class="form-label">งบประมาณสนับสนุน (บาท)</label>
                        <input type="number" step="0.01" class="form-control" id="pub_budget" name="pub_budget" value="{{ old('pub_budget', $item->pub_budget) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pub_file" class="form-label">ไฟล์แนบ (Full Paper)</label>
                        @if($item->pub_file)
                            <div class="mb-2">
                                <a href="{{ Storage::url($item->pub_file) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark"></i> ดูไฟล์เดิม</a>
                            </div>
                        @endif
                        <input type="file" class="form-control" id="pub_file" name="pub_file">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="data_show" class="form-label">การแสดงผล</label>
                    <select class="form-select" id="data_show" name="data_show">
                        <option value="1" {{ old('data_show', $item->data_show) == 1 ? 'selected' : '' }}>แสดง</option>
                        <option value="0" {{ old('data_show', $item->data_show) == 0 ? 'selected' : '' }}>ซ่อน</option>
                    </select>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                    <a href="{{ route('frontend.rdbpublished.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let rowCount = {{ count($item->authors) > 0 ? count($item->authors) : 1 }};
    function addRow() {
        let table = document.getElementById("author_table").getElementsByTagName('tbody')[0];
        let newRow = table.insertRow(table.rows.length);
        newRow.id = "author_row_" + rowCount;
        
        let cell1 = newRow.insertCell(0);
        let cell2 = newRow.insertCell(1);
        let cell3 = newRow.insertCell(2);

        cell1.innerHTML = `
            <select class="form-select" name="author_id[]">
                <option value="">-- เลือกนักวิจัย --</option>
                @foreach($researchers as $r)
                    <option value="{{ $r->researcher_id }}">{{ $r->researcher_nameTH }} {{ $r->researcher_surnameTH }}</option>
                @endforeach
            </select>
        `;
        cell2.innerHTML = `
            <select class="form-select" name="pubta_id[]">
                <option value="">-- เลือกประเภท --</option>
                @foreach($authorTypes as $type)
                    <option value="{{ $type->pubta_id }}">{{ $type->pubta_name }}</option>
                @endforeach
            </select>
        `;
        cell3.innerHTML = '<button type="button" class="btn btn-danger btn-sm" onclick="removeRow(' + rowCount + ')"><i class="bi bi-trash"></i></button>';
        
        rowCount++;
    }

    function removeRow(id) {
        let row = document.getElementById("author_row_" + id);
        row.parentNode.removeChild(row);
    }
</script>
@endsection
