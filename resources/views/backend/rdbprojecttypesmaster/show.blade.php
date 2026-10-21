@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body">
                <span class="badge rounded-pill me-2" style="background-color: {{ $item->color_code ?: '#3B82F6' }};">&nbsp;</span>
                {{ $item->master_pt_name }}
            </h4>
            <div class="text-secondary small">รายละเอียดหมวดหมู่ทุนหลักและประเภททุนย่อยที่สังกัด</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('backend.rdbprojecttypesmaster.edit', $item->master_pt_id) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i> แก้ไข
            </a>
            <a href="{{ route('backend.rdbprojecttypesmaster.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-body-tertiary fw-bold">ข้อมูลหมวดหมู่</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">รหัส:</span>
                            <span class="fw-semibold">#{{ $item->master_pt_id }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">กลุ่มประเภททุน:</span>
                            <span class="fw-semibold">{{ $item->projectTypeGroup->pttg_name ?? '-' }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">ลำดับแสดงผล:</span>
                            <span class="fw-semibold">{{ $item->order_index }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-secondary">รหัสสี:</span>
                            <span>
                                <span class="badge rounded-circle p-2 me-1" style="background-color: {{ $item->color_code }};">&nbsp;</span>
                                <code>{{ $item->color_code }}</code>
                            </span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">จำนวนประเภททุนที่ผูก:</span>
                            <span class="badge bg-primary rounded-pill">{{ $item->projectTypes->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
                    <span class="fw-bold"><i class="bi bi-diagram-3 me-2"></i>ประเภททุนวิจัยที่สังกัดหมวดหมู่นี้</span>
                    <a href="{{ route('backend.rdbprojecttype.create', ['master_pt_id' => $item->master_pt_id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg me-1"></i> เพิ่มประเภททุน
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">รหัส</th>
                                    <th>ชื่อประเภททุน</th>
                                    <th>ปีงบฯ</th>
                                    <th class="text-center" style="width: 100px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->projectTypes as $pt)
                                <tr>
                                    <td><code>#{{ $pt->pt_id }}</code></td>
                                    <td>
                                        <a href="{{ route('backend.rdbprojecttype.show', $pt->pt_id) }}" class="text-decoration-none fw-semibold text-body hover-primary">
                                            {{ $pt->pt_name }}
                                        </a>
                                    </td>
                                    <td>{{ $pt->year->year_name ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('backend.rdbprojecttype.edit', $pt->pt_id) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary">ยังไม่มีประเภททุนวิจัยที่ผูกกับหมวดหมู่นี้</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
