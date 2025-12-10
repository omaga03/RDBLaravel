@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Rdb Researcher Details</div>
        <div class="card-body">
            <p><strong>ID:</strong> {{ $researcher->getKey() }}</p>
            <p><strong>Name (TH):</strong> {{ $researcher->researcher_name_th }}</p>
            <!-- Add other details -->
            <a href="{{ route('backend.rdb_researcher.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
