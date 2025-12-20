@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('backend.rdb_published.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('backend.rdb_published._form')
    </form>
</div>
@endsection
