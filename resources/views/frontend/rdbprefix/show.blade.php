@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-person-badge"></i> รายละเอียดคำนำหน้าชื่อ</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="30%">ID</th>
                    <td>{{ $item->prefix_id }}</td>
                </tr>
                <tr>
                    <th>คำนำหน้า (ไทย)</th>
                    <td>{{ $item->prefix_nameTH }}</td>
                </tr>
                <tr>
                    <th>คำนำหน้า (English)</th>
                    <td>{{ $item->prefix_nameEN ?? '-' }}</td>
                </tr>
            </table>
            <div class="mt-3">
                <a href="{{ route('frontend.rdbprefix.index') }}" class="btn btn-secondary">กลับ</a>
                <a href="{{ route('frontend.rdbprefix.edit', $item->prefix_id) }}" class="btn btn-warning">แก้ไข</a>
            </div>
        </div>
    </div>
</div>
@endsection