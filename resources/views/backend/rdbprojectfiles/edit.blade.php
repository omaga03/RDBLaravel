@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Edit Rdbprojectfiles</div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.rdbprojectfiles.update', $item->getKey()) }}">
                @csrf
                @method('PUT')
                <!-- Fields -->
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection