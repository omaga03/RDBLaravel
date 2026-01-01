@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">เพิ่มประเภททุนใหม่</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('backend.rdbprojecttype.store') }}" method="POST">
                @csrf
                @include('backend.rdbprojecttype._form')
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึก</button>
                    <a href="{{ route('backend.rdbprojecttype.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection