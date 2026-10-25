<div class="row g-3">
    <div class="col-md-4">
        <label for="year_id" class="form-label fw-bold">ปี พ.ศ. <span class="text-danger">*</span></label>
        <select name="year_id" id="year_id" class="form-select @error('year_id') is-invalid @enderror" required>
            <option value="">-- เลือกปี พ.ศ. --</option>
            @foreach($years as $year)
                <option value="{{ $year->year_id }}" {{ old('year_id', $item->year_id ?? '') == $year->year_id ? 'selected' : '' }}>
                    ปี {{ $year->year_name }}
                </option>
            @endforeach
        </select>
        @error('year_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="rdbyearedu_start" class="form-label fw-bold">วันเริ่มต้นปีการศึกษา <span class="text-danger">*</span></label>
        <input type="text" name="rdbyearedu_start" id="rdbyearedu_start" class="form-control datepicker @error('rdbyearedu_start') is-invalid @enderror" value="{{ old('rdbyearedu_start', $item->rdbyearedu_start ?? '') }}" required placeholder="วว/ดด/ปปปป">
        @error('rdbyearedu_start')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="rdbyearedu_end" class="form-label fw-bold">วันสิ้นสุดปีการศึกษา <span class="text-danger">*</span></label>
        <input type="text" name="rdbyearedu_end" id="rdbyearedu_end" class="form-control datepicker @error('rdbyearedu_end') is-invalid @enderror" value="{{ old('rdbyearedu_end', $item->rdbyearedu_end ?? '') }}" required placeholder="วว/ดด/ปปปป">
        @error('rdbyearedu_end')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="rdbyearbud_start" class="form-label fw-bold">วันเริ่มต้นปีงบประมาณ <span class="text-danger">*</span></label>
        <input type="text" name="rdbyearbud_start" id="rdbyearbud_start" class="form-control datepicker @error('rdbyearbud_start') is-invalid @enderror" value="{{ old('rdbyearbud_start', $item->rdbyearbud_start ?? '') }}" required placeholder="วว/ดด/ปปปป">
        @error('rdbyearbud_start')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="rdbyearbud_end" class="form-label fw-bold">วันสิ้นสุดปีงบประมาณ <span class="text-danger">*</span></label>
        <input type="text" name="rdbyearbud_end" id="rdbyearbud_end" class="form-control datepicker @error('rdbyearbud_end') is-invalid @enderror" value="{{ old('rdbyearbud_end', $item->rdbyearbud_end ?? '') }}" required placeholder="วว/ดด/ปปปป">
        @error('rdbyearbud_end')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initThaiFlatpickr !== 'undefined') {
        initThaiFlatpickr(".datepicker");
    }
});
</script>
@endpush
