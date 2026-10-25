@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body">
                <i class="bi bi-calendar-range text-primary me-2"></i>
                กำหนดระยะเวลา ปี พ.ศ. {{ $item->year->year_name ?? $item->year_id }}
            </h4>
            <div class="text-secondary small">รายละเอียดช่วงเวลาปีการศึกษาและปีงบประมาณ</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('backend.rdbpublishedcheckyear.edit', $item->id) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i> แก้ไข
            </a>
            <a href="{{ route('backend.rdbpublishedcheckyear.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 col-md-8 mx-auto">
        <div class="card-header bg-body-tertiary fw-bold">ข้อมูลรายละเอียด</div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">ปี พ.ศ.:</span>
                    <span class="fw-semibold">ปี {{ $item->year->year_name ?? $item->year_id }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">วันเริ่มต้นปีการศึกษา:</span>
                    <span class="fw-semibold">{{ \App\Helpers\ThaiDateHelper::format($item->rdbyearedu_start, false, true) }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">วันสิ้นสุดปีการศึกษา:</span>
                    <span class="fw-semibold">{{ \App\Helpers\ThaiDateHelper::format($item->rdbyearedu_end, false, true) }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">วันเริ่มต้นปีงบประมาณ:</span>
                    <span class="fw-semibold">{{ \App\Helpers\ThaiDateHelper::format($item->rdbyearbud_start, false, true) }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">วันสิ้นสุดปีงบประมาณ:</span>
                    <span class="fw-semibold">{{ \App\Helpers\ThaiDateHelper::format($item->rdbyearbud_end, false, true) }}</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
