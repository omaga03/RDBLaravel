@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="เพิ่มปีงบประมาณใหม่"
    icon="bi-plus-circle"
    mode="create"
    :actionRoute="route('backend.rdbyear.store')"
    :backRoute="route('backend.rdbyear.index')"
>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="year_name" class="form-label">
                ปีงบประมาณ <span class="text-danger">*</span>
            </label>
            <input type="text" name="year_name" id="year_name" 
                   class="form-control @error('year_name') is-invalid @enderror" 
                   value="{{ old('year_name') }}" required maxlength="50"
                   placeholder="เช่น 2568">
            @error('year_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</x-form-wrapper>
@endsection