@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-building me-2"></i>จัดการหน่วยงาน/คณะ</h2>
        <a href="{{ route('backend.rdbdepartment.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มข้อมูล
        </a>
    </div>

    <x-search-bar :searchRoute="route('backend.rdbdepartment.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">ชื่อหน่วยงาน/รหัส</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ค้นหาหน่วยงาน...">
            </div>
            <div class="col-md-6">
                <label class="form-label">ประเภท</label>
                <input type="text" name="type" class="form-control" value="{{ request('type') }}" placeholder="ค้นหาประเภท...">
            </div>
        </div>
    </x-search-bar>

    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">รหัส</th>
                        <th>ชื่อหน่วยงาน</th>
                        <th style="width: 150px;">ประเภท</th>
                        <th style="width: 60px;">สี</th>
                        <th style="width: 80px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td><code>{{ $item->department_code }}</code></td>
                        <td class="fw-bold">{{ $item->department_nameTH }}</td>
                        <td>
                            @if($item->departmentType)
                                <span class="badge bg-info text-dark">{{ $item->departmentType->tdepartment_nameTH }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->department_color)
                                <div style="width: 24px; height: 24px; background-color: {{ $item->department_color }}; border-radius: 4px; border: 1px solid #ccc;"></div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('backend.rdbdepartment.show', $item->department_id) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
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