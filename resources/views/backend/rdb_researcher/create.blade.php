@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Create Rdb Researcher</div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.rdb_researcher.store') }}" enctype="multipart/form-data">
                @csrf
                @include('backend.rdb_researcher._form')
            </form>
        </div>
    </div>
</div>
@endsection
