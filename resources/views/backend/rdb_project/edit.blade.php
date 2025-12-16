@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('backend.rdb_project.update', $project->pro_id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('backend.rdb_project._form', ['project' => $project])
            </form>
        </div>
    </div>
</div>
@endsection
