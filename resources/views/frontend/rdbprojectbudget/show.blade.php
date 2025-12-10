@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Rdbprojectbudget Details</div>
        <div class="card-body">
            <p>ID: {{ $item->getKey() }}</p>
            <a href="{{ route('frontend.rdbprojectbudget.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection