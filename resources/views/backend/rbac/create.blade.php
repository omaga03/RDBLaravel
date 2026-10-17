@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-form-wrapper 
        title="เพิ่มบทบาทใหม่ (Add New Role)" 
        icon="bi-shield-plus"
        mode="create" 
        :actionRoute="route('backend.rbac.store')"
        :backRoute="route('backend.rbac.index')"
    >
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                <input class="form-control @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Admin, Editor">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="description" class="form-label">Description</label>
                <input class="form-control @error('description') is-invalid @enderror" type="text" id="description" name="description" value="{{ old('description') }}" placeholder="Role description...">
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </x-form-wrapper>
</div>
@endsection
