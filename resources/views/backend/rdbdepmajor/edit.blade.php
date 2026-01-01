@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="แก้ไขสาขาวิชา"
    icon="bi-pencil-square"
    mode="edit"
    :actionRoute="route('backend.rdbdepmajor.update', $item->maj_code)"
    method="PUT"
    :backRoute="route('backend.rdbdepmajor.show', $item->maj_code)"
>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="maj_id" class="form-label">รหัสสาขา</label>
            <input type="text" name="maj_id" id="maj_id" class="form-control" 
                   value="{{ old('maj_id', $item->maj_id) }}" maxlength="20">
        </div>
        
        <div class="col-md-6">
            <label for="department_id" class="form-label">หน่วยงาน/คณะ</label>
            <x-tom-select 
                name="department_id" 
                :options="$departments->pluck('department_nameTH', 'department_id')->toArray()"
                :value="old('department_id', $item->department_id)"
                placeholder="เลือกหน่วยงาน..."
            />
        </div>
        
        <div class="col-md-12">
            <label for="maj_nameTH" class="form-label">
                ชื่อสาขา (ภาษาไทย) <span class="text-danger">*</span>
            </label>
            <input type="text" name="maj_nameTH" id="maj_nameTH" 
                   class="form-control @error('maj_nameTH') is-invalid @enderror" 
                   value="{{ old('maj_nameTH', $item->maj_nameTH) }}" required maxlength="255">
            @error('maj_nameTH')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-md-12">
            <label for="maj_nameEN" class="form-label">ชื่อสาขา (ภาษาอังกฤษ)</label>
            <input type="text" name="maj_nameEN" id="maj_nameEN" class="form-control" 
                   value="{{ old('maj_nameEN', $item->maj_nameEN) }}" maxlength="255">
        </div>
    </div>
</x-form-wrapper>
@endsection