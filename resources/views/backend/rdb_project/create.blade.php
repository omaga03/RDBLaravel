@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form method="POST" action="{{ route('backend.rdb_project.store') }}" enctype="multipart/form-data">
        @csrf
        @include('backend.rdb_project._form')
    </form>
</div>

@endsection
