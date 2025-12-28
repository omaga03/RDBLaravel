@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> เพิ่มข้อมูลการใช้ประโยชน์</h5>
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
            
            <form method="POST" action="{{ route('backend.rdbprojectutilize.store') }}" enctype="multipart/form-data">
                @csrf
                @include('backend.rdb_projectutilize._form')
            </form>
        </div>
    </div>
</div>

@endsection
