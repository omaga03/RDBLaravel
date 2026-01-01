@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-newspaper me-2"></i>จัดการข่าว/กิจกรรม (News & Activities)</h2>
        <a href="{{ route('backend.research_news.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มข่าวใหม่
        </a>
    </div>

    <!-- Search Bar -->
    <x-search-bar :searchRoute="route('backend.research_news.index')">
        <div class="row g-3">
             <div class="col-md-12">
                <label class="form-label">คำค้นหา (Keyword)</label>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="พิมพ์หัวข้อข่าว, รายละเอียด...">
            </div>
        </div>
    </x-search-bar>

    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">รูปภาพ</th>
                        <th>หัวข้อข่าว/กิจกรรม</th>
                        <th style="width: 80px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>
                            @if($item->news_img)
                                <img src="{{ asset('storage/uploads/news/' . $item->news_img) }}" 
                                        alt="รูปข่าว" class="rounded shadow-sm" 
                                        style="width: 60px; height: 45px; object-fit: cover;">
                            @else
                                <div class="bg-secondary bg-opacity-10 rounded d-flex align-items-center justify-content-center border" 
                                        style="width: 60px; height: 45px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold fs-6">
                                {{ $item->news_name }}
                            </div>
                            <small class="text-muted mt-1 d-block">
                                <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($item->news_date, false, true) }}
                                @if($item->news_type)
                                    <span class="ms-2 badge bg-light text-dark border"><i class="bi bi-tag me-1"></i>{{ $item->news_type }}</span>
                                @endif
                                <span class="ms-2"><i class="bi bi-eye me-1"></i>{{ number_format($item->news_count ?? 0) }}</span>
                            </small>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('backend.research_news.show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="ดูรายละเอียด"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            ไม่พบข้อมูล
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="mt-3">
                {{ $items->withQueryString()->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
