@extends('layouts.app')

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Rdb Project Details</div>
        <div class="card-body">
            <h5 class="card-title">{{ $project->pro_nameTH }}</h5>
            <p class="card-text">{{ $project->pro_nameEN }}</p>
            
            <hr>
            
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Abstract File:</div>
                <div class="col-md-9">
                    @if($project->pro_abstract_file)
                        <a href="{{ asset('storage/uploads/projects/' . $project->pro_abstract_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-file-earmark-pdf"></i> Download Abstract
                        </a>
                    @else
                        <span class="text-muted">No file uploaded</span>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Full Report:</div>
                <div class="col-md-9">
                    @if($project->pro_file)
                        <a href="{{ asset('storage/uploads/projects/' . $project->pro_file) }}" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-file-earmark-pdf"></i> Download Report
                        </a>
                    @else
                        <span class="text-muted">No file uploaded</span>
                    @endif
                </div>
            </div>

            <a href="{{ route('backend.rdb_project.index') }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('backend.rdb_project.edit', $project->pro_id) }}" class="btn btn-warning">Edit</a>
        </div>
    </div>
</div>
@endsection
