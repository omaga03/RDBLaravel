@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">เพิ่มข้อมูลการใช้ประโยชน์ใหม่</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('backend.rdbprojectutilize.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('backend.rdb_projectutilize._form')
            </form>
        </div>
    </div>
</div>
@endsection
