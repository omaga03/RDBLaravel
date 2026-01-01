@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="แก้ไขปีงบประมาณ"
    icon="bi-pencil-square"
    mode="edit"
    :actionRoute="route('backend.rdbyear.update', $item->year_id)"
    method="PUT"
    :backRoute="route('backend.rdbyear.show', $item->year_id)"
>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="year_name" class="form-label">
                ปีงบประมาณ <span class="text-danger">*</span>
            </label>
            <input type="text" name="year_name" id="year_name" 
                   class="form-control @error('year_name') is-invalid @enderror" 
                   value="{{ old('year_name', $item->year_name) }}" required maxlength="50">
            @error('year_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</x-form-wrapper>
@endsection