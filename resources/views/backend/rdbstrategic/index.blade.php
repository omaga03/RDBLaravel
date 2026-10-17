@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-primary">
                <i class="bi bi-bullseye me-2"></i>จัดการยุทธศาสตร์
            </h4>
            <div class="text-muted small">กำหนดยุทธศาสตร์และเป้าหมายการวิจัยของมหาวิทยาลัย</div>
        </div>
        <a href="{{ route('backend.rdbstrategic.create') }}" class="btn btn-success d-inline-flex align-items-center shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> เพิ่มยุทธศาสตร์ใหม่
        </a>
    </div>

    <x-search-bar :searchRoute="route('backend.rdbstrategic.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">ชื่อยุทธศาสตร์</label>
                <input type="text" name="strategic_nameTH" class="form-control" value="{{ request('strategic_nameTH') }}" placeholder="ค้นหายุทธศาสตร์...">
            </div>
            <div class="col-md-6">
                <label class="form-label">ปีงบประมาณ</label>
                <input type="text" name="year" class="form-control" value="{{ request('year') }}" placeholder="ค้นหาปี...">
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-table me-2"></i>รายการยุทธศาสตร์
            </h6>
            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">{{ $items->total() }} รายการ</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>ชื่อยุทธศาสตร์</th>
                        <th style="width: 140px;">ปีงบประมาณ</th>
                        <th style="width: 120px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td><span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace">{{ $item->strategic_id }}</span></td>
                        <td class="fw-bold">{{ $item->strategic_nameTH }}</td>
                        <td>
                            @if($item->year)
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $item->year->year_name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('backend.rdbstrategic.show', $item->strategic_id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('backend.rdbstrategic.edit', $item->strategic_id) }}" class="btn btn-outline-warning" title="แก้ไข">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="card-footer bg-body-tertiary py-2">
            {{ $items->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection