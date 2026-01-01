@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="แก้ไขประเภทหน่วยงาน"
    icon="bi-pencil-square"
    mode="edit"
    :actionRoute="route('backend.rdbdepartmenttype.update', $item->tdepartment_id)"
    method="PUT"
    :backRoute="route('backend.rdbdepartmenttype.show', $item->tdepartment_id)"
>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="tdepartment_nameTH" class="form-label">
                ชื่อประเภท (ภาษาไทย) <span class="text-danger">*</span>
            </label>
            <input type="text" name="tdepartment_nameTH" id="tdepartment_nameTH" 
                   class="form-control @error('tdepartment_nameTH') is-invalid @enderror" 
                   value="{{ old('tdepartment_nameTH', $item->tdepartment_nameTH) }}" required maxlength="255">
            @error('tdepartment_nameTH')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-md-6">
            <label for="tdepartment_nameEN" class="form-label">ชื่อประเภท (ภาษาอังกฤษ)</label>
            <input type="text" name="tdepartment_nameEN" id="tdepartment_nameEN" class="form-control" 
                   value="{{ old('tdepartment_nameEN', $item->tdepartment_nameEN) }}" maxlength="255">
        </div>
    </div>
</x-form-wrapper>
@endsection