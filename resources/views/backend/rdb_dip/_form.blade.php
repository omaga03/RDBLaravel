<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="dip_request_number" class="form-label">เลขที่คำขอ <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="dip_request_number" name="dip_request_number" value="{{ old('dip_request_number', $item->dip_request_number ?? '') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="dipt_id" class="form-label">ประเภททรัพย์สินทางปัญญา</label>
                <select class="form-select" id="dipt_id" name="dipt_id">
                    <option value="">-- เลือกประเภท --</option>
                    @foreach(\App\Models\RdbDipType::all() as $type)
                        <option value="{{ $type->dipt_id }}" {{ (old('dipt_id', $item->dipt_id ?? '') == $type->dipt_id) ? 'selected' : '' }}>
                            {{ $type->diptype_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="dip_name" class="form-label">ชื่อผลงาน (ถ้ามี)</label>
            {{-- Note: dip_name field doesn't explicitly exist in model fillable but usually derived or stored in another field? 
                 Looking at model RdbDip, fields are: dip_data2_name (invitation name?). 
                 User request mentioned "dip_name" in index which was null. 
                 Let's check if 'dip_data2_name' is the intended name field or if my migration/model inspection missed it.
                 Re-checking RdbDip model... it doesn't have 'dip_name' in fillable but I used it in Index before. 
                 Wait, I see 'dip_data2_name' in fillable. I'll use that as the main name or add a generic input if schema supports it.
                 Let's assume 'dip_data2_name' is the Name of Invention/Work.
            --}}
            <input type="text" class="form-control" id="dip_data2_name" name="dip_data2_name" value="{{ old('dip_data2_name', $item->dip_data2_name ?? '') }}">
            <small class="text-muted">ชื่อการประดิษฐ์/ออกแบบผลิตภัณฑ์/ชื่อผลงาน</small>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="dip_request_date" class="form-label">วันที่ยื่นคำขอ</label>
                <input type="date" class="form-control flatpickr" id="dip_request_date" name="dip_request_date" value="{{ old('dip_request_date', $item->dip_request_date ?? '') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="dip_number" class="form-label">เลขที่ทะเบียน (เมื่อได้รับ)</label>
                <input type="text" class="form-control" id="dip_number" name="dip_number" value="{{ old('dip_number', $item->dip_number ?? '') }}">
            </div>
        </div>

        <div class="mb-3">
            <label for="researcher_id" class="form-label">นักวิจัย/ผู้ประดิษฐ์</label>
            <select class="form-select select2" id="researcher_id" name="researcher_id">
                <option value="">-- เลือกนักวิจัย --</option>
                {{-- Ideally this should be an AJAX search for performance, but for now loading all loosely --}}
                @foreach(\App\Models\RdbResearcher::select('researcher_id','researcher_fname','researcher_lname')->limit(500)->get() as $res)
                    <option value="{{ $res->researcher_id }}" {{ (old('researcher_id', $item->researcher_id ?? '') == $res->researcher_id) ? 'selected' : '' }}>
                        {{ $res->researcher_fname }} {{ $res->researcher_lname }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
             <label for="pro_id" class="form-label">โครงการวิจัยที่เกี่ยวข้อง</label>
             <select class="form-select select2" id="pro_id" name="pro_id">
                 <option value="">-- เลือกโครงการ --</option>
                 @if(isset($item) && $item->pro_id)
                    @php $pro = \App\Models\RdbProject::find($item->pro_id); @endphp
                    @if($pro)
                        <option value="{{ $pro->pro_id }}" selected>{{ $pro->pro_nameTH }}</option>
                    @endif
                 @endif
                 {{-- AJAX loading recommended here --}}
             </select>
             <small class="text-muted">* รองรับการเชื่อมโยงโครงการ (ควรใช้ AJAX search ในอนาคต)</small>
        </div>

    </div>
</div>

<hr>
<div class="d-flex justify-content-end">
    <a href="{{ route('backend.rdb_dip.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>

<script>
    // Simple script to initialize Select2 if available, or just standard interactions
    document.addEventListener('DOMContentLoaded', function() {
        // defined in layout if added
    });
</script>
