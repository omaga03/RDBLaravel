@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="เพิ่มหน่วยงานใหม่"
    icon="bi-plus-circle"
    mode="create"
    :actionRoute="route('backend.rdbdepartment.store')"
    :backRoute="route('backend.rdbdepartment.index')"
>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="department_code" class="form-label">รหัสหน่วยงาน</label>
            <input type="text" name="department_code" id="department_code" class="form-control" 
                   value="{{ old('department_code') }}" maxlength="20" placeholder="รหัสหน่วยงาน">
        </div>
        <div class="col-md-6">
            <label for="tdepartment_id" class="form-label">ประเภทหน่วยงาน</label>
            <x-tom-select 
                name="tdepartment_id" 
                :options="$departmentTypes->pluck('tdepartment_nameTH', 'tdepartment_id')->toArray()"
                :value="old('tdepartment_id')"
                placeholder="เลือกประเภท..."
            />
        </div>
        <div class="col-md-12">
            <label for="department_nameTH" class="form-label">
                ชื่อหน่วยงาน (ภาษาไทย) <span class="text-danger">*</span>
            </label>
            <input type="text" name="department_nameTH" id="department_nameTH" 
                   class="form-control @error('department_nameTH') is-invalid @enderror" 
                   value="{{ old('department_nameTH') }}" required maxlength="255"
                   placeholder="กรอกชื่อหน่วยงาน">
            @error('department_nameTH')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-12">
            <label for="department_nameEN" class="form-label">ชื่อหน่วยงาน (ภาษาอังกฤษ)</label>
            <input type="text" name="department_nameEN" id="department_nameEN" class="form-control" 
                   value="{{ old('department_nameEN') }}" maxlength="255" placeholder="Department name in English">
        </div>
        <div class="col-md-4">
            <label for="department_color" class="form-label">สีประจำหน่วยงาน</label>
            <input type="color" name="department_color" id="department_color" 
                   class="form-control form-control-color" value="{{ old('department_color', '#ffffff') }}">
        </div>
    </div>
</x-form-wrapper>
@endsection