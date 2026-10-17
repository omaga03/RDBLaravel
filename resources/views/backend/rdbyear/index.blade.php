@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-primary">
                <i class="bi bi-calendar3 me-2"></i>จัดการปีงบประมาณ
            </h4>
            <div class="text-muted small">รายการปีงบประมาณสำหรับจัดกลุ่มและบริหารจัดการงานวิจัย</div>
        </div>
        <a href="{{ route('backend.rdbyear.create') }}" class="btn btn-success d-inline-flex align-items-center shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> เพิ่มปีใหม่
        </a>
    </div>

    <x-search-bar :searchRoute="route('backend.rdbyear.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">ปีงบประมาณ</label>
                <input type="text" name="year_name" class="form-control" value="{{ request('year_name') }}" placeholder="ค้นหาปีงบประมาณ...">
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-table me-2"></i>รายการปีงบประมาณ
            </h6>
            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">{{ $items->total() }} รายการ</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 100px;">ID</th>
                        <th>ปีงบประมาณ</th>
                        <th style="width: 120px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td><span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace">{{ $item->year_id }}</span></td>
                        <td class="fw-bold fs-5">{{ $item->year_name }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('backend.rdbyear.show', $item->year_id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('backend.rdbyear.edit', $item->year_id) }}" class="btn btn-outline-warning" title="แก้ไข">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
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