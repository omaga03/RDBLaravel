@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-list-stars"></i> รายละเอียดแผนยุทธศาสตร์</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="30%">ID</th>
                    <td>{{ $item->strategic_id }}</td>
                </tr>
                <tr>
                    <th>ชื่อแผน (ภาษาไทย)</th>
                    <td>{{ $item->strategic_nameTH }}</td>
                </tr>
                <tr>
                    <th>ชื่อแผน (English)</th>
                    <td>{{ $item->strategic_nameEN }}</td>
                </tr>
            </table>
            <div class="mt-3">
                <a href="{{ route('frontend.rdbstrategic.index') }}" class="btn btn-secondary">กลับ</a>
                <a href="{{ route('frontend.rdbstrategic.edit', $item->strategic_id) }}" class="btn btn-warning">แก้ไข</a>
            </div>
        </div>
    </div>
</div>
@endsection