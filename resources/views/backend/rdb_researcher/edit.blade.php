@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Edit Rdb Researcher</div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.rdb_researcher.update', $researcher->getKey()) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('backend.rdb_researcher._form', ['researcher' => $researcher])
            </form>
        </div>
    </div>
</div>
@endsection
