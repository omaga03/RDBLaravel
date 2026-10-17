@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-person-workspace me-2"></i>จัดการตำแหน่งในโครงการ</h2>
        <a href="{{ route('backend.rdbprojectposition.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มตำแหน่งใหม่
        </a>
    </div>

    <x-search-bar :searchRoute="route('backend.rdbprojectposition.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">ชื่อตำแหน่ง</label>
                <input type="text" name="position_nameTH" class="form-control" value="{{ request('position_nameTH') }}" placeholder="ค้นหาตำแหน่ง...">
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-table me-2"></i>รายการตำแหน่งในโครงการ</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ number_format($items->total()) }} รายการ</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>ชื่อตำแหน่ง</th>
                            <th>คำอธิบาย</th>
                            <th style="width: 100px;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td><code>{{ $item->position_id }}</code></td>
                            <td class="fw-bold">{{ $item->position_nameTH ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($item->position_desc, 50) ?? '-' }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('backend.rdbprojectposition.show', $item->position_id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('backend.rdbprojectposition.edit', $item->position_id) }}" class="btn btn-outline-warning" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
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
        </div>
        @if($items->hasPages())
        <div class="card-footer bg-body-tertiary py-3">
            {{ $items->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection