<div class="row">
    <div class="col-md-12">
        <div class="mb-3">
             <label for="pro_id" class="form-label">โครงการวิจัยที่นำไปใช้ประโยชน์ <span class="text-danger">*</span></label>
             <select class="form-select select2" id="pro_id" name="pro_id">
                 <option value="">-- เลือกโครงการ --</option>
                 {{-- AJAX recommended --}}
                 @foreach(\App\Models\RdbProject::select('pro_id','pro_nameTH')->orderBy('created_at','desc')->limit(100)->get() as $pro)
                    <option value="{{ $pro->pro_id }}" {{ (old('pro_id', $item->pro_id ?? '') == $pro->pro_id) ? 'selected' : '' }}>
                        {{ $pro->pro_nameTH }}
                    </option>
                 @endforeach
             </select>
        </div>

        <div class="mb-3">
            <label for="utz_department_name" class="form-label">หน่วยงานที่นำไปใช้ประโยชน์</label>
            <input type="text" class="form-control" id="utz_department_name" name="utz_department_name" value="{{ old('utz_department_name', $item->utz_department_name ?? '') }}" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                 <label for="utz_date" class="form-label">วันที่นำไปใช้</label>
                 <input type="date" class="form-control flatpickr" id="utz_date" name="utz_date" value="{{ old('utz_date', $item->utz_date ?? '') }}">
            </div>
            <div class="col-md-6 mb-3">
                 <label for="chw_id" class="form-label">จังหวัด</label>
                 <select class="form-select select2" id="chw_id" name="chw_id">
                     <option value="">-- เลือกจังหวัด --</option>
                     @foreach(\App\Models\RdbChangwat::orderBy('changwat_t', 'asc')->get() as $chw)
                        <option value="{{ $chw->id }}" {{ (old('chw_id', $item->chw_id ?? '') == $chw->id) ? 'selected' : '' }}>
                            {{ $chw->changwat_t }}
                        </option>
                     @endforeach
                 </select>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="utz_detail" class="form-label">รายละเอียดการนำไปใช้ประโยชน์</label>
            <textarea class="form-control" id="utz_detail" name="utz_detail" rows="4">{{ old('utz_detail', $item->utz_detail ?? '') }}</textarea>
        </div>

    </div>
</div>

<hr>
<div class="d-flex justify-content-end">
    <a href="{{ route('backend.rdbprojectutilize.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>
