@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">เพิ่มประเภทโครงการทุนย่อย</h1>
        <a href="{{ route('backend.rdbprojecttypesub.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> ย้อนกลับ
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('backend.rdbprojecttypesub.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('backend.rdbprojecttypesub._form')
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> บันทึก</button>
                    <a href="{{ route('backend.rdbprojecttypesub.index') }}" class="btn btn-secondary ms-2"><i class="bi bi-x-circle"></i> ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
