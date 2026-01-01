@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="เพิ่มประเภทหน่วยงานใหม่"
    icon="bi-plus-circle"
    mode="create"
    :actionRoute="route('backend.rdbdepartmenttype.store')"
    :backRoute="route('backend.rdbdepartmenttype.index')"
>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="tdepartment_nameTH" class="form-label">
                ชื่อประเภท (ภาษาไทย) <span class="text-danger">*</span>
            </label>
            <input type="text" name="tdepartment_nameTH" id="tdepartment_nameTH" 
                   class="form-control @error('tdepartment_nameTH') is-invalid @enderror" 
                   value="{{ old('tdepartment_nameTH') }}" required maxlength="255"
                   placeholder="กรอกชื่อประเภทหน่วยงาน">
            @error('tdepartment_nameTH')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-md-6">
            <label for="tdepartment_nameEN" class="form-label">ชื่อประเภท (ภาษาอังกฤษ)</label>
            <input type="text" name="tdepartment_nameEN" id="tdepartment_nameEN" class="form-control" 
                   value="{{ old('tdepartment_nameEN') }}" maxlength="255"
                   placeholder="Department type name in English">
        </div>
    </div>
</x-form-wrapper>
@endsection