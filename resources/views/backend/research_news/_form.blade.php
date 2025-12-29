<div class="row">
    <div class="col-md-12">
        <!-- News ID (only show on create page) -->
        @if(!isset($item) || !$item->id)
        <div class="mb-3">
            <label for="news_id" class="form-label">รหัสข่าว (News ID)</label>
            <input type="text" class="form-control" id="news_id" name="news_id" value="{{ old('news_id', $item->news_id ?? '') }}" placeholder="ระบบจะสร้างอัตโนมัติถ้าไม่กรอก">
        </div>
        @endif

        <!-- Cover Image (Moved before Title) -->
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-image me-2"></i>รูปภาพปก</div>
            <div class="card-body text-center">
                @if(isset($item) && $item->news_img)
                    <img src="{{ asset('storage/uploads/news/' . $item->news_img) }}" class="img-fluid mb-2 rounded" style="max-height: 200px;">
                @else
                    <div class="bg-secondary-subtle d-flex align-items-center justify-content-center mb-2 rounded" style="height: 150px;">
                        <span class="text-muted"><i class="bi bi-image fs-1"></i></span>
                    </div>
                @endif
                <input type="file" class="form-control" name="news_img" accept="image/*">
                <small class="text-muted mt-1 d-block">รองรับไฟล์ภาพ .jpg, .png</small>
            </div>
        </div>

        <!-- News Name -->
        <div class="mb-3">
            <label for="news_name" class="form-label">หัวข้อข่าว/กิจกรรม <span class="text-danger">*</span></label>
            <textarea class="form-control ckeditor-basic" id="news_name" name="news_name" rows="2" required>{{ old('news_name', $item->news_name ?? '') }}</textarea>
        </div>

        <!-- News Detail (CKEditor with Link Support) -->
        <div class="mb-3">
            <label for="news_detail" class="form-label">รายละเอียด</label>
            <textarea class="form-control ckeditor-full" id="news_detail" name="news_detail" rows="5">{{ old('news_detail', $item->news_detail ?? '') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="news_type" class="form-label">ประเภทข่าว</label>
                <select class="form-select" id="news_type" name="news_type">
                    <option value="ข่าวประชาสัมพันธ์" {{ (old('news_type', $item->news_type ?? '') == 'ข่าวประชาสัมพันธ์') ? 'selected' : '' }}>ข่าวประชาสัมพันธ์</option>
                    <option value="ข่าวการประชุม/อบรม" {{ (old('news_type', $item->news_type ?? '') == 'ข่าวการประชุม/อบรม') ? 'selected' : '' }}>ข่าวการประชุม/อบรม</option>
                    <option value="อื่นๆ" {{ (old('news_type', $item->news_type ?? '') == 'อื่นๆ') ? 'selected' : '' }}>อื่นๆ</option>
                </select>
            </div>
            @if(!isset($item) || !$item->id)
            <div class="col-md-6 mb-3">
                <label for="news_date" class="form-label">วันที่ลงข่าว</label>
                <input type="text" class="form-control flatpickr" id="news_date" name="news_date" value="{{ old('news_date', date('Y-m-d')) }}">
            </div>
            @endif
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="news_event_start" class="form-label">วันที่เริ่มกิจกรรม (ถ้ามี)</label>
                <input type="text" class="form-control flatpickr" id="news_event_start" name="news_event_start" value="{{ old('news_event_start', $item->news_event_start ?? '') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="news_event_end" class="form-label">วันที่สิ้นสุดกิจกรรม (ถ้ามี)</label>
                <input type="text" class="form-control flatpickr" id="news_event_end" name="news_event_end" value="{{ old('news_event_end', $item->news_event_end ?? '') }}">
            </div>
        </div>
        
        <div class="mb-3">
             <label for="news_link" class="form-label">ลิ้งก์ที่เกี่ยวข้อง (URL)</label>
             <input type="url" class="form-control" id="news_link" name="news_link" value="{{ old('news_link', $item->news_link ?? '') }}" placeholder="https://example.com">
        </div>

    </div>
</div>

<hr>
<div class="d-flex justify-content-end">
    @if(isset($item) && $item->id)
        <a href="{{ route('backend.research_news.show', $item->id) }}" class="btn btn-secondary me-2">ยกเลิก</a>
    @else
        <a href="{{ route('backend.research_news.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    @endif
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>
</div>
@push('scripts')
    @include('layouts.partials.ckeditor_setup')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initThaiFlatpickr === 'function') {
            initThaiFlatpickr(".flatpickr");
        }

        // Date Range Validation
        const startDateInput = document.getElementById('news_event_start');
        const endDateInput = document.getElementById('news_event_end');

        if (startDateInput && endDateInput) {
            startDateInput.addEventListener('change', function() {
                 if (this._flatpickr && endDateInput._flatpickr) {
                     endDateInput._flatpickr.set('minDate', this.value);
                     
                     // If end date is before start date, clear it
                     if (endDateInput.value && endDateInput.value < this.value) {
                         endDateInput._flatpickr.clear();
                     }
                 }
            });
        }
    });
    </script>
@endpush
