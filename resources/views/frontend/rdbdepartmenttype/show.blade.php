@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-diagram-3"></i> รายละเอียดชนิดหน่วยงาน</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="30%">ID</th>
                    <td>{{ $item->tdepartment_id }}</td>
                </tr>
                <tr>
                    <th>ชื่อชนิด (ไทย)</th>
                    <td>{{ $item->tdepartment_nameTH }}</td>
                </tr>
                <tr>
                    <th>ชื่อชนิด (English)</th>
                    <td>{{ $item->tdepartment_nameEN ?? '-' }}</td>
                </tr>
            </table>
            <div class="mt-3">
                <a href="{{ route('frontend.rdbdepartmenttype.index') }}" class="btn btn-secondary">กลับ</a>
                <a href="{{ route('frontend.rdbdepartmenttype.edit', $item->tdepartment_id) }}" class="btn btn-warning">แก้ไข</a>
            </div>
        </div>
    </div>
</div>
@endsection
