@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-bullseye me-2"></i>จัดการยุทธศาสตร์</h2>
        <a href="{{ route('backend.rdbstrategic.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มยุทธศาสตร์ใหม่
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

    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>ชื่อยุทธศาสตร์</th>
                        <th style="width: 120px;">ปีงบประมาณ</th>
                        <th style="width: 80px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td><code>{{ $item->strategic_id }}</code></td>
                        <td class="fw-bold">{{ $item->strategic_nameTH }}</td>
                        <td>
                            @if($item->year)
                                <span class="badge bg-info">{{ $item->year->year_name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('backend.rdbstrategic.show', $item->strategic_id) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
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
        <div class="mt-3">{{ $items->withQueryString()->links() }}</div>
        @endif
    </x-card>
</div>
@endsection