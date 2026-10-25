@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body">
                <i class="bi bi-person-check text-primary me-2"></i>
                {{ $item->pubta_nameTH }}
            </h4>
            <div class="text-secondary small">รายละเอียดประเภทผู้แต่งบทความ</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('backend.rdbpublishedtypeauthor.edit', $item->pubta_id) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i> แก้ไข
            </a>
            <a href="{{ route('backend.rdbpublishedtypeauthor.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 col-md-8 mx-auto">
        <div class="card-header bg-body-tertiary fw-bold">ข้อมูลรายละเอียด</div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">รหัส:</span>
                    <span class="fw-semibold">#{{ $item->pubta_id }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">ชื่อประเภทผู้แต่ง (ภาษาไทย):</span>
                    <span class="fw-semibold">{{ $item->pubta_nameTH }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">ชื่อประเภทผู้แต่ง (ภาษาอังกฤษ):</span>
                    <span>{{ $item->pubta_nameEN ?? '-' }}</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-secondary">ค่าคะแนน:</span>
                    <span class="fw-semibold">{{ $item->pubta_score !== null ? number_format($item->pubta_score, 2) : '-' }}</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
