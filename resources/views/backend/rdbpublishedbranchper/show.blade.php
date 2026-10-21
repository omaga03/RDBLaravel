@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body">
                <i class="bi bi-pie-chart text-primary me-2"></i>
                {{ $item->branch->branch_name ?? '-' }} (ปี {{ $item->year->year_name ?? '-' }})
            </h4>
            <div class="text-secondary small">ข้อมูลสัดส่วนเปอร์เซ็นต์สาขาการวิจัยประจำปีงบประมาณ</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('backend.rdbpublishedbranchper.edit', $item->branchper_id) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i> แก้ไข
            </a>
            <a href="{{ route('backend.rdbpublishedbranchper.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 col-md-6 mx-auto">
        <div class="card-header bg-body-tertiary fw-bold">ข้อมูลสัดส่วน</div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">ปีงบประมาณ:</span>
                    <span class="fw-semibold">ปี {{ $item->year->year_name ?? '-' }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">สาขางานตีพิมพ์:</span>
                    <span class="fw-semibold">{{ $item->branch->branch_name ?? '-' }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                    <span class="text-secondary">สัดส่วนเปอร์เซ็นต์:</span>
                    <span class="badge bg-primary fs-6 px-3 py-1">
                        {{ number_format($item->branchper_percent, 2) }} %
                    </span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
