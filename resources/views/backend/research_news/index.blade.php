@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item active" aria-current="page">จัดการข่าวและกิจกรรม</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-newspaper me-2 text-primary"></i>จัดการข่าวและกิจกรรม <span class="text-body-secondary small fw-normal">(News & Activities)</span>
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2 shadow-sm">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($items->total()) }} รายการ
            </span>
            @if(request('trash'))
                <a href="{{ route('backend.research_news.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                    <i class="bi bi-arrow-left me-1.5"></i> รายการปกติ
                </a>
            @else
                <a href="{{ route('backend.research_news.index', ['trash' => 1]) }}" class="btn btn-outline-danger d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                    <i class="bi bi-trash3 me-1.5"></i> ถังขยะ
                </a>
            @endif
            @can('create', \App\Models\ResearchNews::class)
            <form action="{{ route('backend.research_news.sync_nriis') }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการดึงข่าวทุนวิจัยล่าสุดจากระบบ NRIIS หรือไม่?');">
                @csrf
                <button type="submit" class="btn btn-outline-info d-inline-flex align-items-center shadow-sm" style="height: 38px;" title="ดึงข้อมูลข่าวทุนวิจัยจาก NRIIS API">
                    <i class="bi bi-cloud-arrow-down me-1.5"></i> ดึงข่าว NRIIS
                </button>
            </form>
            <a href="{{ route('backend.research_news.create') }}" class="btn btn-primary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                <i class="bi bi-plus-circle me-1.5"></i> เพิ่มข่าวใหม่
            </a>
            @endcan
        </div>
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

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการข่าวและกิจกรรม
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2">{{ number_format($items->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;" class="text-center">รูปภาพ</th>
                            <th>หัวข้อข่าว/กิจกรรม</th>
                            <th style="width: 120px;" class="text-center text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="text-center p-3">
                                <div class="ratio ratio-16x9 rounded overflow-hidden bg-body-secondary border mx-auto" style="width: 80px;">
                                    @if($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="รูปข่าว" class="object-fit-cover w-100 h-100">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 text-body-secondary">
                                            <i class="bi bi-image fs-5"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3">
                                <div class="fw-bold fs-6 mb-1">
                                    <a href="{{ route('backend.research_news.show', $item->id) }}" class="text-decoration-none text-body fw-bold hover-primary text-break">
                                        {{ $item->news_name }}
                                    </a>
                                </div>
                                <div class="small text-body-secondary d-flex flex-wrap gap-2 align-items-center mt-1">
                                    <span>
                                        <i class="bi bi-calendar3 me-1 text-primary"></i>{{ \App\Helpers\ThaiDateHelper::format($item->news_date, false, true) }}
                                    </span>
                                    @if($item->news_type)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                            {{ $item->news_type }}
                                        </span>
                                    @endif
                                    <span>
                                        <i class="bi bi-eye me-1"></i>{{ number_format($item->news_count ?? 0) }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center p-3 text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('backend.research_news.show', $item->id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('update', $item)
                                    <a href="{{ route('backend.research_news.edit', $item->id) }}" class="btn btn-outline-secondary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                    @if((int)$item->data_show === 0)
                                        @can('delete', $item)
                                        <form action="{{ route('backend.research_news.restore', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันกู้คืนข่าวนี้จากถังขยะ?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="กู้คืนข้อมูล"><i class="bi bi-arrow-counterclockwise"></i> กู้คืน</button>
                                        </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-inbox fs-2 d-block mb-3 opacity-50"></i>
                                ไม่พบข้อมูลข่าวสาร
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
