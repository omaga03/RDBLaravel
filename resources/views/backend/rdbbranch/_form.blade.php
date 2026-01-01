<div class="mb-3">
    <label for="branch_name" class="form-label">ชื่อสาขาการวิจัย <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="branch_name" name="branch_name" value="{{ old('branch_name', $item->branch_name ?? '') }}" required maxlength="255">
</div>

{{-- Example of date picker (not used for branch but showing usage) --}}
{{-- 
<div class="mb-3">
    <label class="form-label">วันที่</label>
    <x-date-picker name="some_date" :value="$item->some_date ?? ''" />
</div> 
--}}

<hr>
<div class="d-flex justify-content-end gap-2">
    {{-- Back Button --}}
    @if(isset($item) && $item->branch_id)
        {{-- Edit Mode: Back to Show --}}
        <a href="{{ route('backend.rdbbranch.show', $item->branch_id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> ย้อนกลับ
        </a>
    @else
        {{-- Create Mode: Back to Index --}}
        <a href="{{ route('backend.rdbbranch.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> ย้อนกลับ
        </a>
    @endif

    <button type="button" class="btn btn-primary" onclick="this.form.submit()">
        <i class="bi bi-save"></i> บันทึกข้อมูล
    </button>
</div>
