@extends('layouts.app')

@section('content')
<div class="container">
    <form method="POST" action="{{ route('backend.rdb_researcher.update', $researcher->getKey()) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('backend.rdb_researcher._form', ['researcher' => $researcher])
    </form>
</div>
@endsection
