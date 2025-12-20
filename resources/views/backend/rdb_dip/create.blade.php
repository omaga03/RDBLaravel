@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">เพิ่มข้อมูลทรัพย์สินทางปัญญาใหม่</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('backend.rdb_dip.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('backend.rdb_dip._form')
            </form>
        </div>
    </div>
</div>
@endsection
