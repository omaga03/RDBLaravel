@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-pencil-square"></i> แก้ไขข้อมูลการใช้ประโยชน์</h5>
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
            
            <form method="POST" action="{{ route('backend.rdbprojectutilize.update', $item->utz_id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('backend.rdb_projectutilize._form')
            </form>
        </div>
    </div>
</div>

@endsection
