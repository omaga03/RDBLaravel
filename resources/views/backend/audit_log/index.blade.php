@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-page-header title="ประวัติการใช้งานและกิจกรรมในระบบ (Audit Trail)" description="บันทึกกิจกรรมและการเปลี่ยนแปลงข้อมูลในระบบอย่างละเอียด พร้อมตรวจสอบย้อนหลัง">
        <a href="{{ route('backend.site.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> กลับแดชบอร์ด
        </a>
    </x-page-header>

    <!-- Filters Card -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('backend.audit_log.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small text-muted mb-1">ค้นหา (ชื่อผู้ใช้, หมายเหตุ, ID)</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="พิมพ์คำค้นหา..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small text-muted mb-1">ประเภทกิจกรรม (Event)</label>
                    <select name="event" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($events as $e)
                            <option value="{{ $e }}" {{ request('event') == $e ? 'selected' : '' }}>{{ strtoupper($e) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small text-muted mb-1">โมดูลข้อมูล (Model)</label>
                    <select name="auditable_type" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ request('auditable_type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small text-muted mb-1">ช่วงวันที่</label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}">
                        <span class="input-group-text">-</span>
                        <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}">
                    </div>
                </div>
                <div class="col-md-2 col-sm-12 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i> กรอง
                    </button>
                    @if(request()->hasAny(['search', 'event', 'auditable_type', 'date_start', 'date_end', 'user_id']))
                        <a href="{{ route('backend.audit_log.index') }}" class="btn btn-outline-secondary btn-sm" title="ล้างตัวกรอง">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-sm">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-3" style="width: 140px;">วันเวลา</th>
                            <th>ผู้ใช้งาน / สิทธิ์</th>
                            <th style="width: 110px;">กิจกรรม</th>
                            <th>ข้อมูลเป้าหมาย</th>
                            <th>สรุปการเปลี่ยนแปลง</th>
                            <th>IP / Scope</th>
                            <th class="text-end pe-3" style="width: 90px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="ps-3 text-muted small">
                                    <div class="fw-semibold text-dark">{{ $log->created_at ? $log->created_at->format('d/m/Y') : '-' }}</div>
                                    <small>{{ $log->created_at ? $log->created_at->format('H:i:s') : '' }} น.</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $log->user_name }}</div>
                                    <span class="badge bg-secondary-subtle text-secondary border small" style="font-size: 0.72rem;">
                                        {{ $log->user_role ?? 'User' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($log->event) {
                                            'create' => 'bg-success-subtle text-success border border-success-subtle',
                                            'update' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                            'delete' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            'approve' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                            'reject' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                            'impersonate' => 'bg-purple-subtle text-purple border',
                                            default => 'bg-light text-dark border',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} px-2 py-1">
                                        {{ strtoupper($log->event) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary">{{ $log->auditable_type }}</span>
                                    <span class="text-muted small">#{{ $log->auditable_id }}</span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 320px;" title="{{ $log->diff_summary ?? $log->remarks }}">
                                        {{ $log->diff_summary ?? $log->remarks ?? '-' }}
                                    </div>
                                    @if($log->remarks && $log->remarks !== $log->diff_summary)
                                        <small class="text-muted d-block text-truncate" style="max-width: 320px;">
                                            <em>{{ $log->remarks }}</em>
                                        </small>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    <div>{{ $log->ip_address ?? '-' }}</div>
                                    @if($log->scope_info)
                                        <small class="text-primary-emphasis d-block">{{ $log->scope_info }}</small>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('backend.audit_log.show', $log->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="ดูรายละเอียด Diff">
                                        <i class="bi bi-eye"></i> ดู Diff
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    ไม่พบประวัติการใช้งานตามเงื่อนไขที่กำหนด
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
