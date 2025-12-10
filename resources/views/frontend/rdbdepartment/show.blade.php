@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-building"></i> รายละเอียดหน่วยงาน</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="30%">ID</th>
                    <td>{{ $item->department_id }}</td>
                </tr>
                <tr>
                    <th>รหัสหน่วยงาน</th>
                    <td><span class="badge bg-secondary">{{ $item->department_code }}</span></td>
                </tr>
                <tr>
                    <th>ชื่อหน่วยงาน (ไทย)</th>
                    <td>{{ $item->department_nameTH }}</td>
                </tr>
                <tr>
                    <th>ชื่อหน่วยงาน (English)</th>
                    <td>{{ $item->department_nameEN ?? '-' }}</td>
                </tr>
                <tr>
                    <th>ชนิดหน่วยงาน</th>
                    <td>{{ $item->departmentType->tdepartment_nameTH ?? '-' }}</td>
                </tr>
                <tr>
                    <th>สี</th>
                    <td>
                        @if($item->department_color)
                            <span class="badge" style="background-color: {{ $item->department_color }}">{{ $item->department_color }}</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </table>
            <div class="mt-3">
                <a href="{{ route('frontend.rdbdepartment.index') }}" class="btn btn-secondary">กลับ</a>
                <a href="{{ route('frontend.rdbdepartment.edit', $item->department_id) }}" class="btn btn-warning">แก้ไข</a>
            </div>
        </div>
    </div>
</div>
@endsection