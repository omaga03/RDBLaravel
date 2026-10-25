@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item active" aria-current="page">จัดการการประชุมวิชาการ</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-megaphone-fill me-2 text-primary"></i>จัดการการประชุมวิชาการ <span class="text-body-secondary small fw-normal">(Research Conferences)</span>
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2 shadow-sm">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($items->total()) }} รายการ
            </span>
            @if(request('trash'))
                <a href="{{ route('backend.research_conference.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                    <i class="bi bi-arrow-left me-1.5"></i> รายการปกติ
                </a>
            @else
                <a href="{{ route('backend.research_conference.index', ['trash' => 1]) }}" class="btn btn-outline-danger d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                    <i class="bi bi-trash3 me-1.5"></i> ถังขยะ
                </a>
            @endif
            @can('create', \App\Models\ResearchConference::class)
            <form action="{{ route('backend.research_conference.sync_facebook') }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการดึงข่าวงานประชุมวิชาการล่าสุดจาก Facebook หรือไม่?');">
                @csrf
                <button type="submit" class="btn btn-outline-primary d-inline-flex align-items-center shadow-sm" style="height: 38px;" title="ดึงข้อมูลข่าวงานประชุมวิชาการจากเพจ Facebook">
                    <i class="bi bi-facebook me-1.5 text-primary"></i> ดึงข่าว Facebook
                </button>
            </form>
            <a href="{{ route('backend.research_conference.create') }}" class="btn btn-primary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                <i class="bi bi-plus-circle me-1.5"></i> เพิ่มการประชุมใหม่
            </a>
            @endcan
        </div>
    </div>

    <!-- Search Bar -->
    <x-search-bar :searchRoute="route('backend.research_conference.index')" simplePlaceholder="พิมพ์ชื่องานประชุม, สถานที่จัดงาน, หรือคำสำคัญ...">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-medium">ชื่องานประชุมวิชาการ</label>
                <input type="text" class="form-control" name="con_name" value="{{ request('con_name', request('q')) }}" placeholder="ค้นหาตามชื่องานประชุม...">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">สถานที่จัดงาน (Venue)</label>
                <input type="text" class="form-control" name="con_venue" value="{{ request('con_venue') }}" placeholder="เช่น มหาวิทยาลัย, โรงแรม, จังหวัด, Hybrid...">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">ประเภทวันที่ค้นหา</label>
                <select class="form-select" name="date_type">
                    <option value="created_at" {{ request('date_type', 'created_at') === 'created_at' ? 'selected' : '' }}>วันที่ลงข่าว / บันทึกข้อมูล</option>
                    <option value="event_date" {{ request('date_type') === 'event_date' ? 'selected' : '' }}>วันที่จัดงานประชุมวิชาการ</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">วันที่เริ่มต้น</label>
                <input type="text" class="form-control datepicker" name="date_start" value="{{ request('date_start') }}" placeholder="วว/ดด/ปปปป" autocomplete="off">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">วันที่สิ้นสุด</label>
                <input type="text" class="form-control datepicker" name="date_end" value="{{ request('date_end') }}" placeholder="วว/ดด/ปปปป" autocomplete="off">
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการการประชุมวิชาการ
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2">{{ number_format($items->total()) }} รายการ</span>
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
                                <div class="ratio ratio-16x9 rounded overflow-hidden bg-body-secondary border mx-auto" style="width: 80px;">
                                    @if($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="รูปงานประชุม" class="object-fit-cover w-100 h-100">
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
                                        {{ strip_tags($item->con_name) }}
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
                                    @can('update', $item)
                                    <a href="{{ route('backend.research_conference.edit', $item->id) }}" class="btn btn-outline-secondary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                    @if((int)$item->data_show === 0)
                                        @can('delete', $item)
                                        <form action="{{ route('backend.research_conference.restore', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันกู้คืนข่าวงานประชุมนี้จากถังขยะ?');">
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
