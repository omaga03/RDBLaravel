@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="เพิ่มตำแหน่งใหม่"
    icon="bi-plus-circle"
    mode="create"
    :actionRoute="route('backend.rdbprojectposition.store')"
    :backRoute="route('backend.rdbprojectposition.index')"
>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="position_nameTH" class="form-label">ชื่อตำแหน่ง <span class="text-danger">*</span></label>
            <input type="text" name="position_nameTH" id="position_nameTH" 
                   class="form-control @error('position_nameTH') is-invalid @enderror" 
                   value="{{ old('position_nameTH') }}" required maxlength="255" placeholder="เช่น หัวหน้าโครงการ">
            @error('position_nameTH')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="position_desc" class="form-label">คำอธิบาย</label>
            <input type="text" name="position_desc" id="position_desc" class="form-control" value="{{ old('position_desc') }}" maxlength="255" placeholder="คำอธิบายเพิ่มเติม">
        </div>
    </div>
</x-form-wrapper>
@endsection