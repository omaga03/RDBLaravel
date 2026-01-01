@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>จัดการประเภทโครงการทุนย่อย</h2>
        <a href="{{ route('backend.rdbprojecttypesub.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มโครงการใหม่
        </a>
    </div>

    <x-search-bar :searchRoute="route('backend.rdbprojecttypesub.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">ชื่อโครงการ</label>
                <input type="text" name="pts_name" class="form-control" value="{{ request('pts_name') }}" placeholder="ค้นหาโครงการ...">
            </div>
            <div class="col-md-6">
                <label class="form-label">ประเภททุน</label>
                <input type="text" name="pt_name" class="form-control" value="{{ request('pt_name') }}" placeholder="ค้นหาประเภททุน...">
            </div>
        </div>
    </x-search-bar>

    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>ชื่อโครงการทุนสนับสนุน</th>
                        <th style="width: 120px;">ไฟล์แนบ</th>
                        <th style="width: 80px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td><code>{{ $item->pts_id }}</code></td>
                        <td>
                            <div class="fw-bold">
                                @if(isset($item->projectType->year->year_name))
                                    <span class="badge bg-secondary me-1">
                                        <i class="bi bi-calendar-event me-1"></i> {{ $item->projectType->year->year_name }}
                                    </span>
                                @endif
                                {{ $item->pts_name }}
                            </div>
                            <div class="small text-muted ms-3">
                                <i class="bi bi-arrow-return-right"></i> {{ $item->projectType->pt_name ?? '-' }}
                            </div>
                        </td>
                        <td>
                            @if($item->pts_file)
                                <a href="{{ asset('storage/uploads/project_types_sub/' . $item->pts_file) }}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-file-earmark-text"></i> ดาวน์โหลด
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('backend.rdbprojecttypesub.show', $item->pts_id) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
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
