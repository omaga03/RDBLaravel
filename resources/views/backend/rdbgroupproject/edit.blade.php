@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="แก้ไขกลุ่มโครงการ"
    icon="bi-pencil-square"
    mode="edit"
    :actionRoute="route('backend.rdbgroupproject.update', $item->pgroup_id)"
    method="PUT"
    :backRoute="route('backend.rdbgroupproject.show', $item->pgroup_id)"
>
    <div class="row g-3">
        <div class="col-md-12">
            <label for="pgroup_nameTH" class="form-label">ชื่อกลุ่มโครงการ (ภาษาไทย) <span class="text-danger">*</span></label>
            <input type="text" name="pgroup_nameTH" id="pgroup_nameTH" 
                   class="form-control @error('pgroup_nameTH') is-invalid @enderror" 
                   value="{{ old('pgroup_nameTH', $item->pgroup_nameTH) }}" required maxlength="255">
            @error('pgroup_nameTH')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-12">
            <label for="pgroup_nameEN" class="form-label">ชื่อกลุ่มโครงการ (ภาษาอังกฤษ)</label>
            <input type="text" name="pgroup_nameEN" id="pgroup_nameEN" class="form-control" value="{{ old('pgroup_nameEN', $item->pgroup_nameEN) }}" maxlength="255">
        </div>
    </div>
</x-form-wrapper>
@endsection