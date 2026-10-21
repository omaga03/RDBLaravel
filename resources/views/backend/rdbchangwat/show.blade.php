@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body">
                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                {{ $item->tambon_t }} {{ $item->amphoe_t }} {{ $item->changwat_t }}
            </h4>
            <div class="text-secondary small">ข้อมูลพื้นที่และการระบุตำแหน่งพิกัดทางภูมิศาสตร์</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('backend.rdbchangwat.edit', $item->id) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i> แก้ไข
            </a>
            <a href="{{ route('backend.rdbchangwat.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-body-tertiary fw-bold">ข้อมูลเขตการปกครอง</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">ตำบล:</span>
                            <span class="fw-bold">{{ $item->tambon_t }} <small class="text-secondary">({{ $item->tambon_e ?: '-' }})</small></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">อำเภอ:</span>
                            <span class="fw-bold">{{ $item->amphoe_t }} <small class="text-secondary">({{ $item->amphoe_e ?: '-' }})</small></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">จังหวัด:</span>
                            <span class="fw-bold">{{ $item->changwat_t }} <small class="text-secondary">({{ $item->changwat_e ?: '-' }})</small></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-body-tertiary fw-bold">พิกัดทางภูมิศาสตร์ (GPS)</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">ละติจูด (Latitude):</span>
                            <code class="fs-6 fw-bold text-primary">{{ $item->lat ?: '-' }}</code>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">ลองจิจูด (Longitude):</span>
                            <code class="fs-6 fw-bold text-primary">{{ $item->long ?: '-' }}</code>
                        </li>
                        @if($item->lat && $item->long)
                        <li class="list-group-item px-0 pt-3">
                            <a href="https://www.google.com/maps?q={{ $item->lat }},{{ $item->long }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger btn-sm w-100">
                                <i class="bi bi-box-arrow-up-right me-1"></i> เปิดดูบน Google Maps
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
