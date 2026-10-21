@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>
                {{ $item->department->department_nameTH ?? '-' }} (ปีงบฯ {{ $item->year->year_name ?? '-' }})
            </h4>
            <div class="text-secondary small">ข้อมูลเป้าหมายจำนวนบุคลากรสายวิชาการ (งานตีพิมพ์)</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('backend.rdbpublishedpersonnel.edit', $item->personnel_id) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i> แก้ไข
            </a>
            <a href="{{ route('backend.rdbpublishedpersonnel.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 col-md-8 mx-auto">
        <div class="card-header bg-body-tertiary fw-bold">ข้อมูลรายละเอียด</div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">ปีงบประมาณ:</span>
                    <span class="fw-semibold">ปี {{ $item->year->year_name ?? '-' }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">คณะ / หน่วยงาน:</span>
                    <span class="fw-semibold">{{ $item->department->department_nameTH ?? '-' }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">กลุ่มสาขาวิชา:</span>
                    <span>{{ $item->departmentCategory->depcat_name ?? '-' }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">จำนวนบุคลากรสายวิชาการรวม:</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-1">
                        {{ number_format($item->personnel_num, 1) }} คน
                    </span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">ด้านการจัดการเรียนการสอน:</span>
                    <span class="fw-semibold">{{ number_format($item->personnel_numedu, 1) }} คน</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">ด้านเงินงบประมาณ:</span>
                    <span class="fw-semibold">{{ number_format($item->personnel_numbud, 1) }} คน</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
