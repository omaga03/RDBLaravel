<div class="row">
    <div class="col-md-2 mb-3">
        <label for="prefix_id" class="form-label">Prefix</label>
        <select class="form-select" id="prefix_id" name="prefix_id">
            <option value="">Select</option>
            @foreach($prefixes as $prefix)
                <option value="{{ $prefix->prefix_id }}" {{ (old('prefix_id', $researcher->prefix_id ?? '') == $prefix->prefix_id) ? 'selected' : '' }}>
                    {{ $prefix->prefix_nameTH }}
                </option>
            @endforeach
        <select class="form-select" id="department_id" name="department_id">
            <option value="">Select Department</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->department_id }}" {{ (old('department_id', $researcher->department_id ?? '') == $dept->department_id) ? 'selected' : '' }}>
                    {{ $dept->department_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="maj_id" class="form-label">Major</label>
        <select class="form-select" id="maj_id" name="maj_id">
            <option value="">Select Major</option>
            @foreach($majors as $major)
                <option value="{{ $major->maj_id }}" {{ (old('maj_id', $researcher->maj_id ?? '') == $major->maj_id) ? 'selected' : '' }}>
                    {{ $major->maj_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="depcat_id" class="form-label">Department Category</label>
        <select class="form-select" id="depcat_id" name="depcat_id">
            <option value="">Select Category</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->depcat_id }}" {{ (old('depcat_id', $researcher->depcat_id ?? '') == $cat->depcat_id) ? 'selected' : '' }}>
                    {{ $cat->depcat_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="depcou_id" class="form-label">Department Course</label>
        <select class="form-select" id="depcou_id" name="depcou_id">
            <option value="">Select Course</option>
            @foreach($courses as $course)
                <option value="{{ $course->depcou_id }}" {{ (old('depcou_id', $researcher->depcou_id ?? '') == $course->depcou_id) ? 'selected' : '' }}>
                    {{ $course->depcou_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="researcher_email" class="form-label">Email</label>
        <input type="email" class="form-control" id="researcher_email" name="researcher_email" value="{{ old('researcher_email', $researcher->researcher_email ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label for="researcher_mobile" class="form-label">Mobile</label>
        <input type="text" class="form-control" id="researcher_mobile" name="researcher_mobile" value="{{ old('researcher_mobile', $researcher->researcher_mobile ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label for="restatus_id" class="form-label">Status</label>
        <select class="form-select" id="restatus_id" name="restatus_id">
            <option value="">Select Status</option>
            @foreach($statuses as $status)
                <option value="{{ $status->restatus_id }}" {{ (old('restatus_id', $researcher->restatus_id ?? '') == $status->restatus_id) ? 'selected' : '' }}>
                    {{ $status->restatus_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="researcher_birthdate" class="form-label">Birthdate</label>
        <input type="date" class="form-control" id="researcher_birthdate" name="researcher_birthdate" value="{{ old('researcher_birthdate', $researcher->researcher_birthdate ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="researcher_gender" class="form-label">Gender</label>
        <select class="form-select" id="researcher_gender" name="researcher_gender">
            <option value="">Select Gender</option>
            <option value="Male" {{ (old('researcher_gender', $researcher->researcher_gender ?? '') == 'Male') ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ (old('researcher_gender', $researcher->researcher_gender ?? '') == 'Female') ? 'selected' : '' }}>Female</option>
        </select>
    </div>
</div>

<button type="submit" class="btn btn-primary">{{ isset($researcher) ? 'Update' : 'Create' }}</button>
<a href="{{ route('backend.rdb_researcher.index') }}" class="btn btn-secondary">Cancel</a>
