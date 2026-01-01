@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="เพิ่มสาขาการวิจัยใหม่"
    icon="bi-plus-circle"
    mode="create"
    :actionRoute="route('backend.rdbbranch.store')"
    :backRoute="route('backend.rdbbranch.index')"
>
    <div class="row g-3">
        <div class="col-md-12">
            <label for="branch_name" class="form-label">
                ชื่อสาขาการวิจัย <span class="text-danger">*</span>
            </label>
            <input type="text" name="branch_name" id="branch_name" 
                   class="form-control @error('branch_name') is-invalid @enderror" 
                   value="{{ old('branch_name') }}" required maxlength="255"
                   placeholder="กรอกชื่อสาขาการวิจัย">
            @error('branch_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</x-form-wrapper>
@endsection
