@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="แก้ไขตำแหน่ง"
    icon="bi-pencil-square"
    mode="edit"
    :actionRoute="route('backend.rdbprojectposition.update', $item->position_id)"
    method="PUT"
    :backRoute="route('backend.rdbprojectposition.show', $item->position_id)"
>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="position_nameTH" class="form-label">ชื่อตำแหน่ง <span class="text-danger">*</span></label>
            <input type="text" name="position_nameTH" id="position_nameTH" 
                   class="form-control @error('position_nameTH') is-invalid @enderror" 
                   value="{{ old('position_nameTH', $item->position_nameTH) }}" required maxlength="255">
            @error('position_nameTH')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="position_desc" class="form-label">คำอธิบาย</label>
            <input type="text" name="position_desc" id="position_desc" class="form-control" value="{{ old('position_desc', $item->position_desc) }}" maxlength="255">
        </div>
    </div>
</x-form-wrapper>
@endsection