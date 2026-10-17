@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <p class="mb-0">Edit: {{ $item->name }}</p>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('backend.rbac.update', $item) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name" class="form-control-label">Name</label>
                                    <input class="form-control" type="text" id="name" name="name" value="{{ $item->name }}" readonly>
                                    <small class="form-text text-muted">The name cannot be changed.</small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description" class="form-control-label">Description</label>
                                    <input class="form-control @error('description') is-invalid @enderror" type="text" id="description" name="description" value="{{ old('description', $item->description) }}">
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark">
                        <button type="submit" class="btn btn-primary">Update Role</button>
                        <a href="{{ route('backend.rbac.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
