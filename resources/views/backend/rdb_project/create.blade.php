@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มข้อมูลโครงการวิจัย</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
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
    </div>
</div>

@endsection
