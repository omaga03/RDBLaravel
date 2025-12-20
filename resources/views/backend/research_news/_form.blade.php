<div class="row">
    <div class="col-md-8">
        <!-- News Name -->
        <div class="mb-3">
            <label for="news_name" class="form-label">หัวข้อข่าว/กิจกรรม <span class="text-danger">*</span></label>
            <textarea class="form-control ckeditor-basic" id="news_name" name="news_name" rows="2" required>{{ old('news_name', $item->news_name ?? '') }}</textarea>
        </div>

        <!-- News Detail (CKEditor or Textarea) -->
        <div class="mb-3">
            <label for="news_detail" class="form-label">รายละเอียด</label>
            <textarea class="form-control ckeditor-standard" id="news_detail" name="news_detail" rows="5">{{ old('news_detail', $item->news_detail ?? '') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="news_type" class="form-label">ประเภทข่าว</label>
                <select class="form-select" id="news_type" name="news_type">
                    <option value="1" {{ (old('news_type', $item->news_type ?? '') == 1) ? 'selected' : '' }}>ข่าวประชาสัมพันธ์</option>
                    <option value="2" {{ (old('news_type', $item->news_type ?? '') == 2) ? 'selected' : '' }}>ข่าวการประชุม/อบรม</option>
                    <option value="3" {{ (old('news_type', $item->news_type ?? '') == 3) ? 'selected' : '' }}>อื่นๆ</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="news_date" class="form-label">วันที่ลงข่าว</label>
                <input type="date" class="form-control flatpickr" id="news_date" name="news_date" value="{{ old('news_date', $item->news_date ?? date('Y-m-d')) }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="news_event_start" class="form-label">วันที่เริ่มกิจกรรม (ถ้ามี)</label>
                <input type="date" class="form-control flatpickr" id="news_event_start" name="news_event_start" value="{{ old('news_event_start', $item->news_event_start ?? '') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="news_event_end" class="form-label">วันที่สิ้นสุดกิจกรรม (ถ้ามี)</label>
                <input type="date" class="form-control flatpickr" id="news_event_end" name="news_event_end" value="{{ old('news_event_end', $item->news_event_end ?? '') }}">
            </div>
        </div>
        
        <div class="mb-3">
             <label for="news_link" class="form-label">ลิ้งก์ที่เกี่ยวข้อง (URL)</label>
             <input type="url" class="form-control" id="news_link" name="news_link" value="{{ old('news_link', $item->news_link ?? '') }}" placeholder="https://example.com">
        </div>

    </div>
    
    <div class="col-md-4">
        <!-- Cover Image -->
        <div class="card mb-3">
            <div class="card-header">รูปภาพปก</div>
            <div class="card-body text-center">
                @if(isset($item) && $item->news_img)
                    <img src="{{ asset('storage/uploads/news/' . $item->news_img) }}" class="img-fluid mb-2 rounded" style="max-height: 200px;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center mb-2 rounded" style="height: 200px;">
                        <span class="text-muted">No Image</span>
                    </div>
                @endif
                <input type="file" class="form-control" name="news_img" accept="image/*">
                <small class="text-muted mt-1 d-block">รองรับไฟล์ภาพ .jpg, .png</small>
            </div>
        </div>
    </div>
</div>

<hr>
<div class="d-flex justify-content-end">
    <a href="{{ route('backend.research_news.index') }}" class="btn btn-secondary me-2">ยกเลิก</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
</div>
</div>
@push('scripts')
    @include('layouts.partials.ckeditor_setup')
@endpush
