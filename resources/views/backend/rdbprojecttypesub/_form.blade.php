<div class="row g-3">
    <div class="col-md-6">
        <label for="filter_year_id" class="form-label">ปีงบประมาณ</label>
        <select class="form-select" id="filter_year_id" name="filter_year_id">
            <option value="">-- เลือกปีงบประมาณ --</option>
            @foreach($years as $year)
                <option value="{{ $year->year_id }}"
                    @if(isset($item) && $item->projectType && $item->projectType->year_id == $year->year_id) selected @endif>
                    {{ $year->year_name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">เลือกปีงบประมาณเพื่อกรองประเภททุน</div>
    </div>

    <div class="col-md-6">
        <label for="pt_id" class="form-label">ประเภททุนสนับสนุน <span class="text-danger">*</span></label>
        <select class="form-select @error('pt_id') is-invalid @enderror" id="pt_id" name="pt_id" required>
            <option value="" data-year-id="">-- เลือกประเภททุน --</option>
            @foreach($projectTypes as $type)
                <option value="{{ $type->pt_id }}" data-year-id="{{ $type->year_id }}" 
                    {{ old('pt_id', $item->pt_id ?? '') == $type->pt_id ? 'selected' : '' }}>
                    {{ $type->pt_name }}
                </option>
            @endforeach
        </select>
        @error('pt_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="pts_name" class="form-label">ชื่อโครงการทุนสนับสนุน <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('pts_name') is-invalid @enderror" id="pts_name" name="pts_name" value="{{ old('pts_name', $item->pts_name ?? '') }}" required maxlength="255">
        @error('pts_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="pts_file" class="form-label">ไฟล์แนบ (PDF, Word, Zip)</label>
        <input type="file" class="form-control @error('pts_file') is-invalid @enderror" id="pts_file" name="pts_file">
        @if(isset($item) && $item->pts_file)
            <div class="form-text mt-2">
                ไฟล์ปัจจุบัน: <a href="{{ asset('storage/uploads/project_types_sub/' . $item->pts_file) }}" target="_blank">{{ $item->pts_file }}</a>
            </div>
        @endif
        @error('pts_file')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const yearSelect = document.getElementById('filter_year_id');
    const typeSelect = document.getElementById('pt_id');
    const typeOptions = Array.from(typeSelect.options);

    function filterTypes() {
        const selectedYear = yearSelect.value;
        
        // Hide all options first (except placeholder)
        typeOptions.forEach(option => {
            if (option.value === "") return; // Keep placeholder
            
            const optionYear = option.getAttribute('data-year-id');
            
            if (selectedYear === "" || optionYear === selectedYear) {
                option.style.display = '';
                option.hidden = false;
            } else {
                option.style.display = 'none';
                option.hidden = true;
            }
        });

        // Reset selection if current selection is hidden
        const currentSelected = typeSelect.selectedOptions[0];
        if (currentSelected && currentSelected.hidden && currentSelected.value !== "") {
            typeSelect.value = "";
        }
    }

    yearSelect.addEventListener('change', filterTypes);

    // Run on load to set initial state
    filterTypes();
});
</script>
