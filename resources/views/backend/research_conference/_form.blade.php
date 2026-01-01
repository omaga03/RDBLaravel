<div class="row">
    <div class="col-md-12">
        <!-- Conference ID (only show on create page) -->
        @if(!isset($item) || !$item->id)
        <div class="mb-3">
            <label for="con_id" class="form-label">รหัสงานประชุม (Conference ID)</label>
            <input type="text" class="form-control" id="con_id" name="con_id" value="{{ old('con_id', $item->con_id ?? '') }}" placeholder="ระบบจะสร้างอัตโนมัติถ้าไม่กรอก">
        </div>
        @endif

        <!-- Cover Image (Moved before Title) -->
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-image me-2"></i>รูปภาพปก</div>
            <div class="card-body text-center">
                @if(isset($item) && $item->con_img)
                    <img src="{{ asset('storage/uploads/conference/' . $item->con_img) }}" class="img-fluid mb-2 rounded" style="max-height: 200px;">
                @else
                    <div class="bg-secondary-subtle d-flex align-items-center justify-content-center mb-2 rounded" style="height: 150px;">
                        <span class="text-muted"><i class="bi bi-image fs-1"></i></span>
                    </div>
                @endif
                <input type="file" class="form-control" name="con_img" accept="image/*">
                <small class="text-muted mt-1 d-block">รองรับไฟล์ภาพ .jpg, .png</small>
            </div>
        </div>

        <!-- Conference Name -->
        <div class="mb-3">
            <label for="con_name" class="form-label">ชื่องานประชุม <span class="text-danger">*</span></label>
            <textarea class="form-control ckeditor-basic" id="con_name" name="con_name" rows="2" required>{{ old('con_name', $item->con_name ?? '') }}</textarea>
        </div>

        <!-- Conference Detail (CKEditor with Link Support) -->
        <div class="mb-3">
            <label for="con_detail" class="form-label">รายละเอียด</label>
            <textarea class="form-control ckeditor-full" id="con_detail" name="con_detail" rows="5">{{ old('con_detail', $item->con_detail ?? '') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="con_even_date" class="form-label">วันที่จัดงาน</label>
                <input type="text" class="form-control" id="con_even_date" name="con_even_date" value="{{ old('con_even_date', $item->con_even_date ?? '') }}" placeholder="เช่น 15-17 มกราคม 2568">
            </div>
            <div class="col-md-6 mb-3">
                <label for="con_sub_deadline" class="form-label">วันปิดรับผลงาน</label>
                <input type="text" class="form-control" id="con_sub_deadline" name="con_sub_deadline" value="{{ old('con_sub_deadline', $item->con_sub_deadline ?? '') }}" placeholder="เช่น 30 ธันวาคม 2567">
            </div>
        </div>
        
        <div class="mb-3">
            <label for="con_venue" class="form-label">สถานที่จัดงาน</label>
            <textarea class="form-control" id="con_venue" name="con_venue" rows="2" placeholder="เช่น โรงแรม ABC กรุงเทพฯ">{{ old('con_venue', $item->con_venue ?? '') }}</textarea>
        </div>
        
        <div class="mb-3">
             <label for="con_website" class="form-label">เว็บไซต์งานประชุม (URL)</label>
             <input type="url" class="form-control" id="con_website" name="con_website" value="{{ old('con_website', $item->con_website ?? '') }}" placeholder="https://example.com">
        </div>

    </div>
</div>

<hr>
<div class="d-flex justify-content-end">
    @if(isset($item) && $item->id)
        <a href="{{ route('backend.research_conference.show', $item->id) }}" class="btn btn-secondary me-2">ยกเลิก</a>
    @else
        <a href="{{ route('backend.research_conference.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    @endif
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>
</div>
@push('scripts')
    @include('layouts.partials.ckeditor_setup')
@endpush
