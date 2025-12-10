@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Create Rdbdepmajor</div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.rdbdepmajor.store') }}">
                @csrf
                <!-- Fields -->
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>
@endsection