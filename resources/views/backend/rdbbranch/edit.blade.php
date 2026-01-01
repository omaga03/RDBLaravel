@extends('layouts.app')

@section('content')
<x-form-wrapper
    title="แก้ไขสาขาการวิจัย"
    icon="bi-pencil-square"
    mode="edit"
    :actionRoute="route('backend.rdbbranch.update', $item->branch_id)"
    method="PUT"
    :backRoute="route('backend.rdbbranch.show', $item->branch_id)"
>
    <div class="row g-3">
        <div class="col-md-12">
            <label for="branch_name" class="form-label">
                ชื่อสาขาการวิจัย <span class="text-danger">*</span>
            </label>
            <input type="text" name="branch_name" id="branch_name" 
                   class="form-control @error('branch_name') is-invalid @enderror" 
                   value="{{ old('branch_name', $item->branch_name) }}" required maxlength="255">
            @error('branch_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</x-form-wrapper>
@endsection
