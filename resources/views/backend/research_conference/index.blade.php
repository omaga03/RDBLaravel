@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-calendar-event me-2"></i>จัดการข่าวงานประชุมวิชาการ (Research Conference)</h2>
        <a href="{{ route('backend.research_conference.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มข่าวใหม่
        </a>
    </div>

    <!-- Search Bar -->
    <x-search-bar :searchRoute="route('backend.research_conference.index')">
        <div class="row g-3">
             <div class="col-md-12">
                <label class="form-label">คำค้นหา (Keyword)</label>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="พิมพ์ชื่องานประชุม, สถานที่...">
            </div>
        </div>
    </x-search-bar>

    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">รูปภาพ</th>
                        <th>ชื่องานประชุม</th>
                        <th style="width: 80px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>
                            @if($item->con_img)
                                <img src="{{ asset('storage/uploads/conference/' . $item->con_img) }}" 
                                        alt="รูปงานประชุม" class="rounded shadow-sm" 
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
                                {!! $item->con_name !!}
                            </div>
                            <small class="text-muted mt-1 d-block">
                                @if($item->con_even_date)
                                    <i class="bi bi-calendar3 me-1"></i>{{ $item->con_even_date }}
                                @endif
                                @if($item->con_venue)
                                    <span class="ms-2"><i class="bi bi-geo-alt me-1"></i>{{ Str::limit(strip_tags($item->con_venue), 30) }}</span>
                                @endif
                                <span class="ms-2"><i class="bi bi-eye me-1"></i>{{ number_format($item->con_count ?? 0) }}</span>
                            </small>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('backend.research_conference.show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="ดูรายละเอียด"><i class="bi bi-eye"></i></a>
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
