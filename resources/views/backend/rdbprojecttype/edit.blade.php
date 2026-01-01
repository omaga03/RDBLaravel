@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">แก้ไขประเภททุน</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('backend.rdbprojecttype.update', $item->pt_id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('backend.rdbprojecttype._form')
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกการแก้ไข</button>
                    <a href="{{ route('backend.rdbprojecttype.show', $item->pt_id) }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection