@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Page Title -->
    <div class="mb-4">
        <h2 class="mb-0 fw-bold text-body">
            <i class="bi bi-newspaper me-2 text-primary"></i> ข่าวกิจกรรมงานวิจัย (Research News)
        </h2>
    </div>

    <!-- Search Component -->
    <x-search-bar :searchRoute="route('frontend.researchnews.index')"
        simplePlaceholder="ค้นหาจาก: ชื่อข่าว, รายละเอียด, หรือแหล่งอ้างอิง...">

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">คำค้นหา</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                       placeholder="ระบุชื่อข่าว, รายละเอียด, หรือแหล่งอ้างอิง...">
            </div>
            <div class="col-md-6">
                <label class="form-label">ประเภทข่าว</label>
                <select class="form-select" name="news_type">
                    <option value="">-- ทั้งหมด --</option>
                    @if(isset($newsTypes))
                        @foreach($newsTypes as $type)
                            <option value="{{ $type }}" {{ request('news_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">วันที่ลงข่าว (เริ่มต้น)</label>
                <input type="date" class="form-control" id="date_start" name="date_start" value="{{ request('date_start') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">วันที่ลงข่าว (สิ้นสุด)</label>
                <input type="date" class="form-control" id="date_end" name="date_end" value="{{ request('date_end') }}">
            </div>
        </div>
    </x-search-bar>

    <!-- Results Card -->
    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการข่าวกิจกรรม
                <span class="badge bg-primary rounded-pill ms-2">{{ number_format($items->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%; min-width: 130px;" class="text-center">รูปภาพ</th>
                            <th style="width: 70%;">รายละเอียดข่าว</th>
                            <th style="width: 15%; min-width: 130px;" class="text-center text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="text-center p-3">
                                @php
                                    $newsImg = null;
                                    if (!empty($item->news_path)) {
                                        $newsImg = asset('storage/' . $item->news_path);
                                    } elseif (!empty($item->news_img)) {
                                        $newsImg = Storage::url($item->news_img);
                                    }
                                @endphp
                                <div class="ratio ratio-16x9 rounded overflow-hidden bg-body-secondary border mx-auto" style="max-width: 140px;">
                                    @if($newsImg)
                                        <img src="{{ $newsImg }}" class="object-fit-cover w-100 h-100" alt="{{ $item->news_name }}" loading="lazy">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 text-body-secondary">
                                            <i class="bi bi-image fs-4"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3">
                                <h6 class="fw-bold mb-1">
                                    <a href="{{ route('frontend.researchnews.show', $item->id) }}" class="text-decoration-none text-body hover-primary text-break" title="{{ html_entity_decode(strip_tags($item->news_name)) }}">
                                        {{ Str::limit(html_entity_decode(strip_tags($item->news_name)), 120) }}
                                    </a>
                                </h6>
                                <div class="small d-flex flex-wrap gap-2 align-items-center my-1 text-body-secondary">
                                    @if($item->news_date)
                                        <span>
                                            <i class="bi bi-calendar-event me-1 text-primary"></i>
                                            {{ \App\Helpers\ThaiDateHelper::format($item->news_date, false, true) }}
                                        </span>
                                    @endif
                                    @if($item->news_type)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                            {{ $item->news_type }}
                                        </span>
                                    @endif
                                    <span>
                                        <i class="bi bi-eye me-1"></i> {{ number_format($item->news_count ?? 0) }} ครั้ง
                                    </span>
                                </div>
                                <p class="mb-0 text-body-secondary small text-break" style="line-height: 1.5;">
                                    {{ Str::limit(html_entity_decode(strip_tags($item->news_detail)), 180) }}
                                </p>
                            </td>
                            <td class="text-center p-3">
                                <a href="{{ route('frontend.researchnews.show', $item->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-eye me-1"></i> ดูรายละเอียด
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-newspaper fs-1 d-block mb-2 opacity-50"></i>
                                ไม่พบข้อมูลข่าวกิจกรรมงานวิจัย
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
</div>

<style>
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
</style>
@endsection