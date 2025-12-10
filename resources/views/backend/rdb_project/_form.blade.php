<div class="row">
    <div class="col-md-6 mb-3">
        <label for="pro_nameTH" class="form-label">Project Name (TH)</label>
        <input type="text" class="form-control" id="pro_nameTH" name="pro_nameTH" value="{{ old('pro_nameTH', $project->pro_nameTH ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="pro_nameEN" class="form-label">Project Name (EN)</label>
        <input type="text" class="form-control" id="pro_nameEN" name="pro_nameEN" value="{{ old('pro_nameEN', $project->pro_nameEN ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="pgroup_id" class="form-label">Project Group</label>
        <select class="form-select" id="pgroup_id" name="pgroup_id">
            <option value="">Select Group</option>
            @foreach($groups as $group)
                <option value="{{ $group->gp_id }}" {{ (old('pgroup_id', $project->pgroup_id ?? '') == $group->gp_id) ? 'selected' : '' }}>
                    {{ $group->gp_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="pt_id" class="form-label">Project Type</label>
        <select class="form-select" id="pt_id" name="pt_id">
            <option value="">Select Type</option>
            @foreach($types as $type)
                <option value="{{ $type->pt_id }}" {{ (old('pt_id', $project->pt_id ?? '') == $type->pt_id) ? 'selected' : '' }}>
                    {{ $type->pt_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="department_id" class="form-label">Department</label>
        <select class="form-select" id="department_id" name="department_id">
            <option value="">Select Department</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->department_id }}" {{ (old('department_id', $project->department_id ?? '') == $dept->department_id) ? 'selected' : '' }}>
                    {{ $dept->department_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="year_id" class="form-label">Year</label>
        <select class="form-select" id="year_id" name="year_id">
            <option value="">Select Year</option>
            @foreach($years as $year)
                <option value="{{ $year->year_id }}" {{ (old('year_id', $project->year_id ?? '') == $year->year_id) ? 'selected' : '' }}>
                    {{ $year->year_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="strategic_id" class="form-label">Strategic</label>
        <select class="form-select" id="strategic_id" name="strategic_id">
            <option value="">Select Strategic</option>
            @foreach($strategics as $strategic)
                <option value="{{ $strategic->strategic_id }}" {{ (old('strategic_id', $project->strategic_id ?? '') == $strategic->strategic_id) ? 'selected' : '' }}>
                    {{ $strategic->strategic_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="ps_id" class="form-label">Status</label>
        <select class="form-select" id="ps_id" name="ps_id">
            <option value="">Select Status</option>
            @foreach($statuses as $status)
                <option value="{{ $status->ps_id }}" {{ (old('ps_id', $project->ps_id ?? '') == $status->ps_id) ? 'selected' : '' }}>
                    {{ $status->ps_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="pro_budget" class="form-label">Budget</label>
        <input type="number" step="0.01" class="form-control" id="pro_budget" name="pro_budget" value="{{ old('pro_budget', $project->pro_budget ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label for="pro_date_start" class="form-label">Start Date</label>
        <input type="date" class="form-control" id="pro_date_start" name="pro_date_start" value="{{ old('pro_date_start', $project->pro_date_start ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label for="pro_date_end" class="form-label">End Date</label>
        <input type="date" class="form-control" id="pro_date_end" name="pro_date_end" value="{{ old('pro_date_end', $project->pro_date_end ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label for="pro_abstract" class="form-label">Abstract</label>
    <textarea class="form-control" id="pro_abstract" name="pro_abstract" rows="4">{{ old('pro_abstract', $project->pro_abstract ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label for="pro_keyword" class="form-label">Keywords</label>
    <textarea class="form-control" id="pro_keyword" name="pro_keyword" rows="2">{{ old('pro_keyword', $project->pro_keyword ?? '') }}</textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="pro_abstract_file" class="form-label">Abstract File (PDF)</label>
        <input type="file" class="form-control" id="pro_abstract_file" name="pro_abstract_file" accept=".pdf">
        @if(isset($project) && $project->pro_abstract_file)
            <div class="mt-1">
                <small>Current file: <a href="{{ asset('storage/uploads/projects/' . $project->pro_abstract_file) }}" target="_blank">{{ $project->pro_abstract_file }}</a></small>
            </div>
        @endif
    </div>
    <div class="col-md-6 mb-3">
        <label for="pro_file" class="form-label">Full Report File (PDF)</label>
        <input type="file" class="form-control" id="pro_file" name="pro_file" accept=".pdf">
        @if(isset($project) && $project->pro_file)
            <div class="mt-1">
                <small>Current file: <a href="{{ asset('storage/uploads/projects/' . $project->pro_file) }}" target="_blank">{{ $project->pro_file }}</a></small>
            </div>
        @endif
    </div>
</div>

<button type="submit" class="btn btn-primary">{{ isset($project) ? 'Update' : 'Create' }}</button>
<a href="{{ route('backend.rdb_project.index') }}" class="btn btn-secondary">Cancel</a>
