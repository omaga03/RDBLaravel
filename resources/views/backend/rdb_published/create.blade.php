@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('backend.rdb_published.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('backend.rdb_published._form')
    </form>
</div>
@endsection
