@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-paperclip me-2"></i>จัดการไฟล์แนบโครงการ</h2>
        <a href="{{ route('backend.rdbprojectfiles.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มไฟล์แนบ
        </a>
    </div>

    <x-search-bar 
        :searchRoute="route('backend.rdbprojectfiles.index')"
        :collapsed="true"
    >
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">ชื่อไฟล์</label>
                <input type="text" name="q" class="form-control" placeholder="ค้นหาตามชื่อไฟล์..." value="{{ request()->q }}">
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-table me-2"></i>รายการไฟล์แนบโครงการ</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ number_format($items->total()) }} รายการ</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>ชื่อไฟล์</th>
                            <th>โครงการ</th>
                            <th class="text-center" style="width: 120px;">ดาวน์โหลด</th>
                            <th class="text-center" style="width: 100px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td><code>{{ $item->id }}</code></td>
                            <td>
                                <div class="fw-bold">{{ $item->rf_filesname }}</div>
                                <small class="text-muted">{{ \App\Helpers\ThaiDateHelper::format($item->created_at) }}</small>
                            </td>
                            <td>
                                @if($item->project)
                                    <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" target="_blank" class="text-decoration-none">
                                        {{ Str::limit($item->project->pro_nameTH, 50) }}
                                    </a>
                                @else
                                    <span class="text-danger">ไม่พบโครงการ</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill">{{ $item->rf_download }} ครั้ง</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('backend.rdbprojectfiles.show', $item->id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('backend.rdbprojectfiles.edit', $item->id) }}" class="btn btn-outline-warning" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
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