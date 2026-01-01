<div class="mb-3">
    <label for="rf_filesname" class="form-label">ชื่อไฟล์ <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="rf_filesname" name="rf_filesname" value="{{ old('rf_filesname', $item->rf_filesname ?? '') }}" required maxlength="255">
</div>

<div class="mb-3">
    <label for="rf_note" class="form-label">หมายเหตุ</label>
    <textarea class="form-control" id="rf_note" name="rf_note" rows="3">{{ old('rf_note', $item->rf_note ?? '') }}</textarea>
</div>

<div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="rf_files_show" name="rf_files_show" value="1" {{ old('rf_files_show', $item->rf_files_show ?? '0') == '1' ? 'checked' : '' }}>
    <label class="form-check-label" for="rf_files_show">แสดงไฟล์</label>
</div>

<!-- Note: Actual file upload usually handled in project form, here we editing metadata or maybe simple replace if needed, but keeping it simple for metadata editing as requested -->


