<div class="mb-3">
    <label for="pro_id" class="form-label">โครงการ <span class="text-danger">*</span></label>
    <!-- In a real scenario, this should be a searchable select (select2) due to large number of projects -->
    <select class="form-select" id="pro_id" name="pro_id" required>
        <option value="">-- เลือกโครงการ --</option>
        @foreach($projects as $project)
            <option value="{{ $project->pro_id }}" {{ old('pro_id', $item->pro_id ?? '') == $project->pro_id ? 'selected' : '' }}>
                {{ Str::limit($project->pro_nameTH, 80) }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="researcher_id" class="form-label">นักวิจัย <span class="text-danger">*</span></label>
    <select class="form-select" id="researcher_id" name="researcher_id" required>
        <option value="">-- เลือกนักวิจัย --</option>
        @foreach($researchers as $researcher)
            <option value="{{ $researcher->researcher_id }}" {{ old('researcher_id', $item->researcher_id ?? '') == $researcher->researcher_id ? 'selected' : '' }}>
                {{ $researcher->researcher_fname }} {{ $researcher->researcher_lname }}
            </option>
        @endforeach
    </select>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="position_id" class="form-label">ตำแหน่ง <span class="text-danger">*</span></label>
            <select class="form-select" id="position_id" name="position_id" required>
                <option value="">-- เลือกตำแหน่ง --</option>
                @foreach($positions as $position)
                    <option value="{{ $position->position_id }}" {{ old('position_id', $item->position_id ?? '') == $position->position_id ? 'selected' : '' }}>
                        {{ $position->position_nameTH }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="ratio" class="form-label">สัดส่วน (%) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="ratio" name="ratio" value="{{ old('ratio', $item->ratio ?? '0') }}" required min="0" max="100">
        </div>
    </div>
</div>


