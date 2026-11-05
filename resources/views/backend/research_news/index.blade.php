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
            <a href="{{ route('backend.research_news.reports') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm" style="height: 38px;" title="ดูรายงานสรุปข่าวประจำเดือน">
                <i class="bi bi-file-earmark-bar-graph me-1.5"></i> รายงานประจำเดือน
            </a>
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

    @if(session('sync_report'))
        @php
            $report = session('sync_report');
            $stats = $report['stats'] ?? ['synced' => 0, 'skipped' => 0, 'failed' => 0];
            $itemsList = $report['items'] ?? [];
            $syncedItems = array_filter($itemsList, fn($x) => ($x['status'] ?? '') === 'synced');
            $skippedItems = array_filter($itemsList, fn($x) => ($x['status'] ?? '') === 'skipped');
        @endphp
        <div class="card border-info shadow-sm mb-4">
            <div class="card-header bg-info-subtle border-info-subtle text-info-emphasis d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-cloud-arrow-down-fill me-2"></i>ผลการดึงข้อมูลข่าวทุนวิจัยจาก NRIIS (Sync Output Report)
                    </h5>
                    <div class="small mt-1">
                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle me-1">บันทึกใหม่: {{ count($syncedItems) }} รายการ</span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle me-1">ซ้ำ/ข้าม: {{ count($skippedItems) }} รายการ</span>
                        @if(($stats['failed'] ?? 0) > 0)
                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">ผิดพลาด: {{ $stats['failed'] }} รายการ</span>
                        @endif
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="this.closest('.card').remove();" aria-label="Close"></button>
            </div>
            <div class="card-body">
                @if(count($syncedItems) > 0)
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-check-circle-fill me-1"></i> รายการข่าวที่ดึงและบันทึกสำเร็จ ({{ count($syncedItems) }} รายการ):</h6>
                    <div class="d-flex flex-column gap-3 mb-4">
                        @foreach($syncedItems as $idx => $sItem)
                            <div class="border rounded p-3 bg-body">
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-2 text-center">
                                        @if(!empty($sItem['image_url']))
                                            <img src="{{ $sItem['image_url'] }}" alt="รูปข่าว" class="img-fluid rounded border shadow-sm mb-2" style="max-height: 160px; object-fit: cover;">
                                        @else
                                            <div class="p-3 bg-body-secondary rounded border text-body-secondary text-center">
                                                <i class="bi bi-image fs-1 d-block"></i>
                                                <small>ไม่มีรูปภาพ</small>
                                            </div>
                                        @endif
                                        <div class="small text-body-secondary font-monospace">ID: {{ $sItem['news_id'] }}</div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold mb-0">
                                                <span class="badge bg-primary me-1">{{ $idx + 1 }}</span>
                                                <a href="{{ $sItem['news_link'] }}" target="_blank" class="text-decoration-none text-body hover-primary">
                                                    {{ $sItem['title'] }}
                                                </a>
                                                <span class="text-body-secondary small fw-normal">({{ $sItem['title_len'] }} ตัวอักษร)</span>
                                            </h6>
                                            <a href="{{ $sItem['detail_url'] }}" target="_blank" class="btn btn-sm btn-outline-primary text-nowrap ms-2">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> เปิดดูในระบบ
                                            </a>
                                        </div>

                                        <ul class="list-unstyled small mb-2 text-body-secondary">
                                            <li class="mb-1"><strong>1. news_id:</strong> <code>{{ $sItem['news_id'] }}</code></li>
                                            <li class="mb-1"><strong>2. news_link:</strong> <a href="{{ $sItem['news_link'] }}" target="_blank" class="text-break">{{ $sItem['news_link'] }}</a></li>
                                            <li class="mb-1"><strong>3. news_img:</strong> <code>{{ $sItem['news_img'] ?? '-' }}</code></li>
                                            <li class="mb-1"><strong>4. news_date:</strong> {{ $sItem['news_date'] }}</li>
                                            <li class="mb-1"><strong>5. tbNTNAME:</strong> <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $sItem['news_type'] }}</span></li>
                                            @if(!empty($sItem['make_status']))
                                                <li class="mb-1"><strong>6. Make to Facebook:</strong> <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><i class="bi bi-send-check me-1"></i>{{ $sItem['make_status'] }}</span></li>
                                            @endif
                                        </ul>

                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="collapse" data-bs-target="#detail-{{ $sItem['news_id'] }}">
                                                <i class="bi bi-chevron-down me-1"></i> แสดงเนื้อหาข่าวฉบับเต็ม (news_detail)
                                            </button>
                                            <div class="collapse mt-2" id="detail-{{ $sItem['news_id'] }}">
                                                <div class="card card-body bg-body-tertiary border small">
                                                    {!! $sItem['news_detail'] !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-secondary py-2 mb-3">
                        <i class="bi bi-info-circle me-1"></i> ไม่มีรายการข่าวใหม่ที่ต้องบันทึก (รายการทั้งหมดที่มีอยู่ในระบบแล้ว)
                    </div>
                @endif

                @if(count($skippedItems) > 0)
                    <div class="border-top pt-3">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#skipped-list">
                            <i class="bi bi-list-check me-1"></i> ดูรายการที่ซ้ำ/ข้ามไป ({{ count($skippedItems) }} รายการ)
                        </button>
                        <div class="collapse mt-2" id="skipped-list">
                            <div class="p-3 bg-body-tertiary border rounded font-monospace small" style="max-height: 220px; overflow-y: auto;">
                                @foreach($skippedItems as $sk)
                                    <div class="text-body-secondary py-1 border-bottom border-light">
                                        {{ $sk['message'] ?? "ID: {$sk['news_id']} -- ซ้ำ (ข้ามไป) --" }}
                                        @if(!empty($sk['title']))
                                            <span class="text-body ms-2">{{ Str::limit($sk['title'], 60) }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

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
                                            <button type="submit" class="btn btn-sm btn-success" title="กู้คืนข้อมูล"><i class="bi bi-arrow-counterclockwise me-1.5"></i> กู้คืน</button>
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
