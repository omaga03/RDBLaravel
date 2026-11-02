@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-body-tertiary">
            <h4 class="mb-0"><i class="bi bi-building me-1.5"></i> รายละเอียดประเภทหน่วยงาน</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
<table class="table table-bordered">
                <tr>
                    <th width="30%">ID</th>
                    <td>{{ $item->depcat_id }}</td>
                </tr>
                <tr>
                    <th>ชื่อประเภท</th>
                    <td>{{ $item->depcat_name }}</td>
                </tr>
            </table>
</div>
            <div class="mt-3">
                <a href="{{ route('frontend.rdbdepartmentcategory.index') }}" class="btn btn-secondary">กลับ</a>
            </div>
        </div>
    </div>
</div>
@endsection