@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-3">
        <div class="card-header" data-bs-toggle="collapse" href="#collapseSearch" role="button" aria-expanded="false" aria-controls="collapseSearch">
            <i class="bi bi-search"></i> Advanced Search
        </div>
        <div class="collapse {{ request()->anyFilled(['researcher_fname', 'researcher_lname', 'department_id']) ? 'show' : '' }}" id="collapseSearch">
            <div class="card-body">
                <form action="{{ route('backend.rdb_researcher.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="researcher_fname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="researcher_fname" name="researcher_fname" value="{{ request('researcher_fname') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="researcher_lname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="researcher_lname" name="researcher_lname" value="{{ request('researcher_lname') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">All</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
                            <a href="{{ route('backend.rdb_researcher.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <span>Rdb Researchers</span>
                <a href="{{ route('backend.rdb_researcher.create') }}" class="btn btn-primary btn-sm">Create New</a>
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
                        <th>Picture</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($researchers as $researcher)
                    <tr>
                        <td>{{ $researcher->researcher_id }}</td>
                        <td>
                            @if($researcher->researcher_picture)
                                <img src="{{ asset('storage/uploads/researchers/' . $researcher->researcher_picture) }}" alt="Pic" style="height: 40px; width: auto;">
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            {{ $researcher->prefix->prefix_nameTH ?? '' }} 
                            {{ $researcher->researcher_fname }} {{ $researcher->researcher_lname }}
                        </td>
                        <td>{{ $researcher->department->department_name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('backend.rdb_researcher.show', $researcher->getKey()) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('backend.rdb_researcher.edit', $researcher->getKey()) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('backend.rdb_researcher.destroy', $researcher->getKey()) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $researchers->links() }}
        </div>
    </div>
</div>
@endsection
