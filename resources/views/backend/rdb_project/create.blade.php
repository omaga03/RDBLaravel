@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Create Rdb Project</div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.rdb_project.store') }}" enctype="multipart/form-data">
                @csrf
                @include('backend.rdb_project._form')
            </form>
        </div>
    </div>
</div>
@endsection
