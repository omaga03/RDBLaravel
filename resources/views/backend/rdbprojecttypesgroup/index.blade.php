@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-collection me-2"></i>จัดการกลุ่มประเภทงบประมาณ</h2>
        <a href="{{ route('backend.rdbprojecttypesgroup.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มกลุ่มใหม่
        </a>
    </div>

    <x-search-bar :searchRoute="route('backend.rdbprojecttypesgroup.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-12">
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="ค้นหาชื่อกลุ่มประเภทงบประมาณ...">
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-table me-2"></i>รายการกลุ่มประเภทงบประมาณ</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ number_format($items->total()) }} รายการ</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>ชื่อกลุ่มประเภทงบประมาณ</th>
                            <th style="width: 150px;">กลุ่ม</th>
                            <th style="width: 100px;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td><code>{{ $item->pttg_id }}</code></td>
                            <td class="fw-bold">{{ $item->pttg_name }}</td>
                            <td>
                                @if(isset($groupList[$item->pttg_group]))
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">{{ $groupList[$item->pttg_group] }}</span>
                                @else
                                    {{ $item->pttg_group ?? '-' }}
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('backend.rdbprojecttypesgroup.show', $item->pttg_id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('backend.rdbprojecttypesgroup.edit', $item->pttg_id) }}" class="btn btn-outline-warning" title="แก้ไข"><i class="bi bi-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
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
