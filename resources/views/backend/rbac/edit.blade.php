@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="แก้ไขบทบาท (Edit Role): {{ $item->name }}" 
        icon="bi-shield-check"
        mode="edit" 
        :actionRoute="route('backend.rbac.update', $item)"
        method="PUT"
        :backRoute="route('backend.rbac.index')"
    >
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Role Name</label>
                <input class="form-control" type="text" id="name" name="name" value="{{ $item->name }}" readonly disabled>
                <div class="form-text text-muted">ชื่อบทบาทไม่สามารถแก้ไขได้ (Role name cannot be changed)</div>
            </div>
            <div class="col-md-6">
                <label for="description" class="form-label">Description</label>
                <input class="form-control @error('description') is-invalid @enderror" type="text" id="description" name="description" value="{{ old('description', $item->description) }}" placeholder="Role description...">
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </x-form-wrapper>
</div>
@endsection
