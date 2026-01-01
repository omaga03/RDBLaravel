@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-tags me-2"></i>จัดการประเภทหน่วยงาน</h2>
        <a href="{{ route('backend.rdbdepartmenttype.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มประเภทใหม่
        </a>
    </div>

    <x-search-bar :searchRoute="route('backend.rdbdepartmenttype.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">ชื่อประเภท</label>
                <input type="text" name="tdepartment_nameTH" class="form-control" value="{{ request('tdepartment_nameTH') }}" placeholder="ค้นหาประเภทหน่วยงาน...">
            </div>
        </div>
    </x-search-bar>

    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>ชื่อประเภท</th>
                        <th style="width: 80px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td><code>{{ $item->tdepartment_id }}</code></td>
                        <td class="fw-bold">{{ $item->tdepartment_nameTH }}</td>
                        <td class="text-center">
                            <a href="{{ route('backend.rdbdepartmenttype.show', $item->tdepartment_id) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
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
        <div class="mt-3">{{ $items->withQueryString()->links() }}</div>
        @endif
    </x-card>
</div>
@endsection