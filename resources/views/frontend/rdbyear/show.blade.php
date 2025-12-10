@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-calendar3"></i> รายละเอียดปีงบประมาณ</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="30%">ID</th>
                    <td>{{ $item->year_id }}</td>
                </tr>
                <tr>
                    <th>ปีงบประมาณ (พ.ศ.)</th>
                    <td class="fw-bold fs-5">{{ $item->year_name }}</td>
                </tr>
            </table>
            <div class="mt-3">
                <a href="{{ route('frontend.rdbyear.index') }}" class="btn btn-secondary">กลับ</a>
                <a href="{{ route('frontend.rdbyear.edit', $item->year_id) }}" class="btn btn-warning">แก้ไข</a>
            </div>
        </div>
    </div>
</div>
@endsection