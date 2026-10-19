@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-page-header title="รายละเอียดการเปลี่ยนแปลง (Audit Log Diff #{{ $log->id }})" description="ตรวจสอบ Snapshot ข้อมูลเดิมและข้อมูลใหม่ที่มีการเปลี่ยนแปลง">
        <a href="{{ route('backend.audit_log.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> ย้อนกลับรายการ
        </a>
    </x-page-header>

    <!-- Metadata Card -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-light py-3 border-bottom">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary px-2.5 py-1.5">{{ strtoupper($log->event) }}</span>
                    <h6 class="mb-0 fw-bold">{{ $log->auditable_type }} #{{ $log->auditable_id }}</h6>
                </div>
                <div class="text-muted small">
                    <i class="bi bi-clock me-1"></i> {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }} น.
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 text-sm">
                <div class="col-md-3 col-sm-6">
                    <div class="text-muted small">ผู้ดำเนินการ</div>
                    <div class="fw-bold text-dark">{{ $log->user_name }} (ID: {{ $log->user_id ?? '-' }})</div>
                    <span class="badge bg-secondary-subtle text-secondary border small">{{ $log->user_role ?? 'User' }}</span>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="text-muted small">ขอบเขต / Scope</div>
                    <div class="fw-medium text-dark">{{ $log->scope_info ?? 'Global / ส่วนกลาง' }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="text-muted small">IP Address</div>
                    <code class="text-dark">{{ $log->ip_address ?? '-' }}</code>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="text-muted small">URL Endpoint</div>
                    <div class="text-truncate text-muted small" title="{{ $log->url }}">{{ $log->url ?? '-' }}</div>
                </div>
                @if($log->remarks)
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3 border">
                            <span class="fw-semibold text-secondary small d-block mb-1">หมายเหตุ / เหตุผลการทำรายการ (Remarks):</span>
                            <div class="text-dark">{{ $log->remarks }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Visual Diff Table -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-file-diff text-primary me-2"></i>เปรียบเทียบค่าข้อมูลที่มีการเปลี่ยนแปลง (Field-by-Field Diff)
                </h6>
                <span class="text-muted small">{{ $log->diff_summary }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            @php
                $old = $log->old_values ?? [];
                $new = $log->new_values ?? [];
                $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
            @endphp

            @if(empty($allKeys))
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-info-circle fs-3 d-block mb-2 text-secondary"></i>
                    ไม่มีข้อมูล Diff ของฟิลด์ย่อย (อาจเป็นการทำรายการแบบภาพรวม เช่น Login / Impersonate)
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 align-middle">
                        <thead class="table-light small">
                            <tr>
                                <th style="width: 22%;">ชื่อฟิลด์ (Attribute)</th>
                                <th style="width: 39%; background-color: #fee2e2; color: #991b1b;">
                                    <i class="bi bi-dash-circle me-1"></i> ค่าเดิมก่อนแก้ไข (Old Value)
                                </th>
                                <th style="width: 39%; background-color: #dcfce7; color: #166534;">
                                    <i class="bi bi-plus-circle me-1"></i> ค่าใหม่หลังแก้ไข (New Value)
                                </th>
                            </tr>
                        </thead>
                        <tbody class="small font-monospace">
                            @foreach($allKeys as $key)
                                @php
                                    $oldVal = $old[$key] ?? null;
                                    $newVal = $new[$key] ?? null;
                                    $isChanged = ($oldVal !== $newVal);
                                    
                                    $formatVal = function($val) {
                                        if (is_null($val)) return '<span class="text-muted fst-italic">null</span>';
                                        if (is_bool($val)) return $val ? 'true' : 'false';
                                        if (is_array($val)) return json_encode($val, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                                        return e((string)$val);
                                    };
                                @endphp
                                <tr class="{{ $isChanged ? '' : 'table-light text-muted' }}">
                                    <td class="fw-bold font-sans text-dark ps-3">
                                        {{ $key }}
                                        @if($isChanged)
                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 0.68rem;">CHANGED</span>
                                        @endif
                                    </td>
                                    <td style="{{ $isChanged ? 'background-color: #fff1f2;' : '' }}">
                                        {!! $formatVal($oldVal) !!}
                                    </td>
                                    <td style="{{ $isChanged ? 'background-color: #f0fdf4;' : '' }}">
                                        {!! $formatVal($newVal) !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
