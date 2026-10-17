@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0 fw-bold text-body">
            <i class="bi bi-megaphone-fill me-2 text-primary"></i>จัดการการประชุมวิชาการ (Research Conference)
        </h2>
        <a href="{{ route('backend.research_conference.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มการประชุมใหม่
        </a>
    </div>

    <!-- Search Bar -->
    <x-search-bar :searchRoute="route('backend.research_conference.index')">
        <div class="row g-3">
             <div class="col-md-12">
                <label class="form-label">คำค้นหา (Keyword)</label>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="พิมพ์ชื่องานประชุม, สถานที่จัดงาน...">
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการการประชุมวิชาการ
                <span class="badge bg-primary rounded-pill ms-2">{{ number_format($items->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;" class="text-center">รูปภาพ</th>
                            <th>ชื่องานประชุมวิชาการ</th>
                            <th style="width: 120px;" class="text-center text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="text-center p-3">
                                @php
                                    $conImg = null;
                                    if (!empty($item->file_path)) {
                                        $conImg = asset('storage/' . $item->file_path);
                                    } elseif (!empty($item->con_img)) {
                                        $conImg = asset('storage/uploads/conference/' . $item->con_img);
                                    }
                                @endphp
                                <div class="ratio ratio-16x9 rounded overflow-hidden bg-body-secondary border mx-auto" style="width: 80px;">
                                    @if($conImg)
                                        <img src="{{ $conImg }}" alt="รูปงานประชุม" class="object-fit-cover w-100 h-100">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 text-body-secondary">
                                            <i class="bi bi-megaphone fs-5"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3">
                                <div class="fw-bold fs-6 mb-1">
                                    <a href="{{ route('backend.research_conference.show', $item->id) }}" class="text-decoration-none text-body fw-bold hover-primary text-break">
                                        {!! $item->con_name !!}
                                    </a>
                                </div>
                                <div class="small text-body-secondary d-flex flex-wrap gap-2 align-items-center mt-1">
                                    @if($item->con_even_date)
                                        <span>
                                            <i class="bi bi-calendar3 me-1 text-primary"></i>{{ \App\Helpers\ThaiDateHelper::format($item->con_even_date, false, true) }}
                                        </span>
                                    @endif
                                    @if($item->con_venue)
                                        <span>
                                            <i class="bi bi-geo-alt-fill me-1 text-danger"></i>{{ Str::limit(strip_tags($item->con_venue), 40) }}
                                        </span>
                                    @endif
                                    <span>
                                        <i class="bi bi-eye me-1"></i>{{ number_format($item->con_count ?? 0) }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center p-3 text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('backend.research_conference.show', $item->id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('backend.research_conference.edit', $item->id) }}" class="btn btn-outline-secondary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-inbox fs-2 d-block mb-3 opacity-50"></i>
                                ไม่พบข้อมูลการประชุมวิชาการ
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($items->hasPages())
                <div class="d-flex justify-content-center p-4">
                    {{ $items->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
</style>
@endsection
