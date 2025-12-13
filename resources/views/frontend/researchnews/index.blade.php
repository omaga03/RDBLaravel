@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="bi bi-newspaper"></i> ข่าวกิจกรรมงานวิจัย (Research News)</h2>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="bi bi-search"></i> ค้นหาข่าวกิจกรรม</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('frontend.researchnews.index') }}" method="GET" id="searchForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="search" class="form-label fw-bold">คีย์เวิร์ด</label>
                                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อข่าว, รายละเอียด...">
                            </div>
                            <div class="col-md-2">
                                <label for="date_start" class="form-label fw-bold">วันที่เริ่ม</label>
                                <input type="date" class="form-control" id="date_start" name="date_start" value="{{ request('date_start') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_end" class="form-label fw-bold">วันที่สิ้นสุด</label>
                                <input type="date" class="form-control" id="date_end" name="date_end" value="{{ request('date_end') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้นหา</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%;" class="text-center">รูปภาพ</th>
                                    <th style="width: 70%;">รายละเอียดข่าว</th>
                                    <th style="width: 15%;" class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td class="text-center">
                                        <div class="ratio ratio-4x3 rounded-3 overflow-hidden bg-light border" style="width: 120px; margin: 0 auto;">
                                            @if($item->news_img)
                                                <img src="{{ Storage::url($item->news_img) }}" class="object-fit-cover" alt="{{ $item->news_name }}">
                                            @else
                                                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                                    <i class="bi bi-image fs-3"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <h6 class="fw-bold mb-1" style="font-size: 1rem;">
                                            <a href="{{ route('frontend.researchnews.show', $item->id) }}" class="text-decoration-none text-body" title="{{ html_entity_decode(strip_tags($item->news_name)) }}">
                                                📰 {{ Str::limit(html_entity_decode(strip_tags($item->news_name)), 100) }}
                                            </a>
                                        </h6>
                                        <div class="text-muted small mb-2">
                                            @php
                                                $dateDisplay = '-';
                                                if($item->news_date) {
                                                    try {
                                                        $dateDisplay = \Carbon\Carbon::parse($item->news_date)->locale('th')->addYears(543)->translatedFormat('d M Y');
                                                    } catch (\Exception $e) {
                                                        $dateDisplay = $item->news_date;
                                                    }
                                                }
                                            @endphp
                                            <i class="bi bi-calendar-event"></i> {{ $dateDisplay }}
                                        </div>
                                        <p class="mb-0 text-muted text-truncate-2" style="font-size: 0.85rem;">
                                            {{ Str::limit(html_entity_decode(strip_tags($item->news_detail)), 150) }}
                                        </p>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('frontend.researchnews.show', $item->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            <i class="bi bi-eye"></i> ดูรายละเอียด
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <i class="bi bi-file-earmark-x fs-1 d-block mb-2 opacity-50"></i>
                                        ไม่พบข้อมูลข่าวกิจกรรม
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const dateStart = document.getElementById('date_start');
    const dateEnd = document.getElementById('date_end');

    searchForm.addEventListener('submit', function(e) {
        const startVal = dateStart.value;
        const endVal = dateEnd.value;

        if (startVal && endVal && new Date(endVal) < new Date(startVal)) {
            e.preventDefault();
            alert('วันที่สิ้นสุดต้องมากกว่าหรือเท่ากับวันที่เริ่ม');
        }
    });
});
</script>
@endpush