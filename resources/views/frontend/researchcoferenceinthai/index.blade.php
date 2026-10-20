@extends('layouts.app')

@section('content')
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active" aria-current="page">การประชุมวิชาการ</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-megaphone-fill me-2 text-primary"></i>การประชุมวิชาการ (Research Conferences)
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($items->total()) }} รายการ
            </span>
        </div>
    </div>

    <!-- Search Component -->
    <x-search-bar :searchRoute="route('frontend.researchcoferenceinthai.index')"
        simplePlaceholder="ค้นหาจาก: ชื่อการประชุม, สถานที่จัดงาน, หรือรายละเอียด...">

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">คำค้นหา</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                       placeholder="ระบุชื่อการประชุม, สถานที่จัดงาน, หรือรายละเอียด...">
            </div>
            <div class="col-md-3">
                <label class="form-label">วันจัดงาน (เริ่มต้น)</label>
                <input type="date" class="form-control" id="date_start" name="date_start" value="{{ request('date_start') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">วันจัดงาน (สิ้นสุด)</label>
                <input type="date" class="form-control" id="date_end" name="date_end" value="{{ request('date_end') }}">
            </div>
        </div>
    </x-search-bar>

    <!-- Results Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-semibold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการการประชุมวิชาการ
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2">{{ number_format($items->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%; min-width: 130px;" class="text-center">รูปภาพ</th>
                            <th style="width: 70%;">รายละเอียดการประชุม</th>
                            <th style="width: 15%; min-width: 130px;" class="text-center text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="text-center p-3">
                                @php
                                    $conImg = $item->image_url;
                                @endphp
                                <div class="ratio ratio-16x9 rounded overflow-hidden bg-body-secondary border mx-auto" style="max-width: 140px;">
                                    @if($conImg)
                                        <img src="{{ $conImg }}" class="object-fit-cover w-100 h-100" alt="{{ $item->con_name }}" loading="lazy">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 text-body-secondary">
                                            <i class="bi bi-megaphone fs-4"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3">
                                <h6 class="fw-bold mb-1">
                                    <a href="{{ route('frontend.researchcoferenceinthai.show', $item->id) }}" class="text-decoration-none text-body hover-primary text-break" title="{{ html_entity_decode(strip_tags($item->con_name)) }}">
                                        {{ Str::limit(html_entity_decode(strip_tags($item->con_name)), 120) }}
                                    </a>
                                </h6>
                                <div class="small d-flex flex-wrap gap-2 align-items-center my-1 text-body-secondary">
                                    @if($item->con_even_date)
                                        <span>
                                            <i class="bi bi-calendar-event me-1 text-primary"></i>
                                            {{ \App\Helpers\ThaiDateHelper::format($item->con_even_date, false, true) }}
                                        </span>
                                    @endif
                                    @if($item->con_venue)
                                        <span>
                                            <i class="bi bi-geo-alt-fill me-1 text-danger"></i>
                                            {{ Str::limit($item->con_venue, 40) }}
                                        </span>
                                    @endif
                                    @if($item->con_sub_deadline)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                            <i class="bi bi-stopwatch me-1"></i> ปิดรับ: {{ \App\Helpers\ThaiDateHelper::format($item->con_sub_deadline) }}
                                        </span>
                                    @endif
                                    <span>
                                        <i class="bi bi-eye me-1"></i> {{ number_format($item->con_count ?? 0) }} ครั้ง
                                    </span>
                                </div>
                                <p class="mb-0 text-body-secondary small text-break" style="line-height: 1.5;">
                                    {{ Str::limit(html_entity_decode(strip_tags($item->con_detail)), 180) }}
                                </p>
                            </td>
                            <td class="text-center p-3">
                                <a href="{{ route('frontend.researchcoferenceinthai.show', $item->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-eye me-1"></i> ดูรายละเอียด
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-megaphone fs-1 d-block mb-2 opacity-50"></i>
                                ไม่พบข้อมูลการประชุมวิชาการ
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center p-4">
                {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

<style>
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
</style>
@endsection