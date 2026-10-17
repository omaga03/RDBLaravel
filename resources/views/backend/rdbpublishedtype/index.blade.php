@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-journal-text me-2"></i>จัดการประเภทผลงานตีพิมพ์</h2>
        <a href="{{ route('backend.rdbpublishedtype.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มประเภทใหม่
        </a>
    </div>

    <x-search-bar :searchRoute="route('backend.rdbpublishedtype.index')" placeholder="ค้นหาประเภท..." />

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-table me-2"></i>รายการประเภทผลงานตีพิมพ์</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ number_format($items->total()) }} รายการ</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>กลุ่มประเภท (Group)</th>
                            <th>ประเภทย่อย (Group Type)</th>
                            <th>กลุ่มย่อย (Subgroup)</th>
                            <th class="text-center" style="width: 100px;">คะแนน</th>
                            <th class="text-center" style="width: 100px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                        <tr>
                            <td>{{ $items->firstItem() + $index }}</td>
                            <td class="fw-bold">{{ $item->pubtype_group }}</td>
                            <td>{{ $item->pubtype_grouptype ?? '-' }}</td>
                            <td>{{ $item->pubtype_subgroup ?? '-' }}</td>
                            <td class="text-center">
                                @if($item->pubtype_score)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $item->pubtype_score }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('backend.rdbpublishedtype.show', $item->pubtype_id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('backend.rdbpublishedtype.edit', $item->pubtype_id) }}" class="btn btn-outline-warning" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
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
