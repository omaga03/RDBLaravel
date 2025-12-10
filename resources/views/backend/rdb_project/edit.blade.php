@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Edit Rdb Project</div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.rdb_project.update', $project->getKey()) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('backend.rdb_project._form', ['project' => $project])
            </form>
        </div>
    </div>
</div>
@endsection
