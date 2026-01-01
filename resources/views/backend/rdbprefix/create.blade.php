@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="เพิ่มคำนำหน้าใหม่"
    icon="bi-plus-circle"
    mode="create"
    :actionRoute="route('backend.rdbprefix.store')"
    :backRoute="route('backend.rdbprefix.index')"
>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="prefix_nameTH" class="form-label">
                คำนำหน้า (ภาษาไทย) <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   name="prefix_nameTH" 
                   id="prefix_nameTH" 
                   class="form-control @error('prefix_nameTH') is-invalid @enderror" 
                   value="{{ old('prefix_nameTH') }}"
                   placeholder="เช่น นาย, นาง, นางสาว"
                   required>
            @error('prefix_nameTH')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-md-6">
            <label for="prefix_abbreviationTH" class="form-label">
                ตัวย่อ (ถ้ามี)
            </label>
            <input type="text" 
                   name="prefix_abbreviationTH" 
                   id="prefix_abbreviationTH" 
                   class="form-control @error('prefix_abbreviationTH') is-invalid @enderror" 
                   value="{{ old('prefix_abbreviationTH') }}"
                   placeholder="เช่น น., นส.">
            @error('prefix_abbreviationTH')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</x-form-wrapper>
@endsection