@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-3">
        <div class="card-header" data-bs-toggle="collapse" href="#collapseSearch" role="button" aria-expanded="false" aria-controls="collapseSearch">
            <i class="bi bi-search"></i> Advanced Search
        </div>
        <div class="collapse {{ request()->anyFilled(['pro_nameTH', 'year_id', 'department_id', 'pt_id', 'ps_id']) ? 'show' : '' }}" id="collapseSearch">
            <div class="card-body">
                <form action="{{ route('backend.rdb_project.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="pro_nameTH" class="form-label">Project Name</label>
                            <input type="text" class="form-control" id="pro_nameTH" name="pro_nameTH" value="{{ request('pro_nameTH') }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="year_id" class="form-label">Year</label>
                            <select class="form-select" id="year_id" name="year_id">
                                <option value="">All</option>
                                @foreach($years as $year)
                                    <option value="{{ $year->year_id }}" {{ request('year_id') == $year->year_id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">All</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="pt_id" class="form-label">Type</label>
                            <select class="form-select" id="pt_id" name="pt_id">
                                <option value="">All</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->pt_id }}" {{ request('pt_id') == $type->pt_id ? 'selected' : '' }}>{{ $type->pt_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="ps_id" class="form-label">Status</label>
                            <select class="form-select" id="ps_id" name="ps_id">
                                <option value="">All</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->ps_id }}" {{ request('ps_id') == $status->ps_id ? 'selected' : '' }}>{{ $status->ps_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
                            <a href="{{ route('backend.rdb_project.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <span>Rdb Projects</span>
                <a href="{{ route('backend.rdb_project.create') }}" class="btn btn-primary btn-sm">Create New</a>
            </div>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title (TH)</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                    <tr>
                        <td>{{ $project->pro_id }}</td>
                        <td>{{ $project->pro_nameTH }}</td>
                        <td>{{ number_format($project->pro_budget, 2) }}</td>
                        <td>
                            @if($project->status)
                                <span class="badge bg-secondary">{{ $project->status->ps_name }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('backend.rdb_project.show', $project->getKey()) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('backend.rdb_project.edit', $project->getKey()) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('backend.rdb_project.destroy', $project->getKey()) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $projects->links() }}
        </div>
    </div>
</div>
@endsection
