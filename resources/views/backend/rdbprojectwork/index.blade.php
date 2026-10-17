@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-people me-2"></i>จัดการงานวิจัย (ผู้ร่วมโครงการ)</h2>
        <a href="{{ route('backend.rdbprojectwork.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มข้อมูลใหม่
        </a>
    </div>

    <x-search-bar 
        :searchRoute="route('backend.rdbprojectwork.index')"
        :collapsed="true"
    >
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">คำค้นหา</label>
                <input type="text" name="q" class="form-control" placeholder="ค้นหาตามชื่อนักวิจัย หรือ ชื่อโครงการ..." value="{{ request()->q }}">
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-table me-2"></i>รายการงานวิจัย (ผู้ร่วมโครงการ)</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ number_format($items->total()) }} รายการ</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>โครงการวิจัย</th>
                            <th>นักวิจัย</th>
                            <th style="width: 100px;">สัดส่วน</th>
                            <th class="text-center" style="width: 100px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td><code>{{ $item->id ?? '-' }}</code></td>
                            <td>
                                @if($item->project)
                                    <div class="fw-bold text-primary">{{ $item->project->pro_nameTH }}</div>
                                    <small class="text-muted">{{ $item->project->pro_code ?? '-' }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->researcher)
                                    <div class="fw-bold">{{ $item->researcher->researcher_nameTH ?? $item->researcher->researcher_fname . ' ' . $item->researcher->researcher_lname }}</div>
                                    <small class="text-muted">{{ $item->position->position_nameTH ?? '-' }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $item->ratio }}%</span>
                            </td>
                            <td class="text-center">
                                @if(isset($item->id))
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('backend.rdbprojectwork.show', $item->id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('backend.rdbprojectwork.edit', $item->id) }}" class="btn btn-outline-warning" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                                @endif
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