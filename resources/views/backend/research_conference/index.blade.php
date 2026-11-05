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

    @if(session('sync_report'))
        @php
            $report = session('sync_report');
            $stats = $report['stats'] ?? ['synced' => 0, 'skipped' => 0, 'failed' => 0];
            $itemsList = $report['items'] ?? [];
            $discoveredLinks = $report['discovered_links'] ?? [];
            $syncedItems = array_filter($itemsList, fn($x) => ($x['status'] ?? '') === 'synced');
            $skippedItems = array_filter($itemsList, fn($x) => ($x['status'] ?? '') === 'skipped');
        @endphp
        <div class="card border-primary shadow-sm mb-4">
            <div class="card-header bg-primary-subtle border-primary-subtle text-primary-emphasis d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-facebook me-2 text-primary"></i>ผลการดึงข้อมูลงานประชุมวิชาการจาก Facebook (Sync Output Report)
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
                @if(count($discoveredLinks) > 0)
                    <div class="mb-3 p-3 bg-body-tertiary border rounded">
                        <div class="fw-bold mb-2 text-body">
                            <i class="bi bi-link-45deg me-1"></i> รายการลิงก์ภาพถ่ายที่ตรวจพบจากหน้า Facebook ({{ count($discoveredLinks) }} รายการ):
                        </div>
                        <div class="small font-monospace" style="max-height: 120px; overflow-y: auto;">
                            @foreach($discoveredLinks as $dl)
                                <div class="py-0.5">
                                    {{ $dl['index'] }}. <a href="{{ $dl['url'] }}" target="_blank" class="text-decoration-none">{{ $dl['label'] }}</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(count($syncedItems) > 0)
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-check-circle-fill me-1"></i> รายการงานประชุมวิชาการที่บันทึกสำเร็จ ({{ count($syncedItems) }} รายการ):</h6>
                    <div class="d-flex flex-column gap-3 mb-4">
                        @foreach($syncedItems as $idx => $cItem)
                            <div class="border rounded p-3 bg-body">
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-2 text-center">
                                        @if(!empty($cItem['image_url']))
                                            <img src="{{ $cItem['image_url'] }}" alt="รูปงานประชุม" class="img-fluid rounded border shadow-sm mb-2" style="max-height: 160px; object-fit: cover;">
                                        @else
                                            <div class="p-3 bg-body-secondary rounded border text-body-secondary text-center">
                                                <i class="bi bi-image fs-1 d-block"></i>
                                                <small>ไม่มีรูปภาพ</small>
                                            </div>
                                        @endif
                                        <div class="small text-body-secondary font-monospace">FBID: {{ $cItem['con_id'] }}</div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold mb-0">
                                                <span class="badge bg-primary me-1">{{ $idx + 1 }}</span>
                                                <a href="{{ $cItem['detail_url'] }}" target="_blank" class="text-decoration-none text-body hover-primary">
                                                    {{ $cItem['name'] }}
                                                </a>
                                            </h6>
                                            <a href="{{ $cItem['detail_url'] }}" target="_blank" class="btn btn-sm btn-outline-primary text-nowrap ms-2">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> เปิดดูในระบบ
                                            </a>
                                        </div>

                                        <ul class="list-unstyled small mb-2 text-body-secondary">
                                            <li class="mb-1"><strong>Link:</strong> <a href="{{ $cItem['website'] }}" target="_blank" class="text-break">{{ $cItem['website'] }}</a></li>
                                            <li class="mb-1"><strong>EvenDate:</strong> <span class="badge bg-primary-subtle text-primary-emphasis">{{ $cItem['even_date'] ?: 'ไม่ได้ระบุ' }}</span></li>
                                            <li class="mb-1"><strong>Deadline:</strong> <span class="badge bg-danger-subtle text-danger-emphasis">{{ $cItem['deadline'] ?: 'ไม่ได้ระบุ' }}</span></li>
                                            <li class="mb-1"><strong>Venue:</strong> {{ $cItem['venue'] ?: 'ไม่ได้ระบุ' }}</li>
                                            @if(!empty($cItem['make_status']))
                                                <li class="mb-1"><strong>MakeToFb:</strong> <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><i class="bi bi-send-check me-1"></i>{{ $cItem['make_status'] }}</span></li>
                                            @endif
                                        </ul>

                                        <div class="small text-body-secondary bg-body-tertiary p-2 rounded border">
                                            <strong>Detail Preview:</strong> {{ $cItem['detail_preview'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-secondary py-2 mb-3">
                        <i class="bi bi-info-circle me-1"></i> {{ $report['message'] ?? 'ไม่มีรายการงานประชุมใหม่ที่ต้องบันทึก (รายการที่มีอยู่ในระบบแล้ว)' }}
                    </div>
                @endif

                @if(count($skippedItems) > 0)
                    <div class="border-top pt-3">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#skipped-conf-list">
                            <i class="bi bi-list-check me-1"></i> ดูรายการที่ซ้ำ/ข้ามไป ({{ count($skippedItems) }} รายการ)
                        </button>
                        <div class="collapse mt-2" id="skipped-conf-list">
                            <div class="p-3 bg-body-tertiary border rounded font-monospace small" style="max-height: 200px; overflow-y: auto;">
                                @foreach($skippedItems as $sk)
                                    <div class="text-body-secondary py-1 border-bottom border-light">
                                        {{ $sk['message'] ?? "FBID: {$sk['con_id']} -- not save (ID ซ้ำ) --" }}
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
                            <th style="width: 90px;" class="text-center">รูปภาพ</th>
                            <th>ชื่องานประชุมวิชาการ / วันจัดงาน</th>
                            <th style="width: 15%;">หมดเขตรับบทความ</th>
                            <th style="width: 18%;">สถานที่จัดงาน</th>
                            <th style="width: 90px;" class="text-center">เข้าชม</th>
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
                                @if($item->con_even_date)
                                    <div class="small text-primary mt-1">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $item->con_even_date }}
                                    </div>
                                @endif
                                <div class="small text-body-secondary mt-1">
                                    <i class="bi bi-clock-history me-1"></i>ประกาศเมื่อ: {{ \App\Helpers\ThaiDateHelper::format($item->created_at, false, true) }}
                                </div>
                            </td>
                            <td class="p-3">
                                @if(!empty($item->con_sub_deadline))
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                        <i class="bi bi-calendar-x me-1"></i>{{ $item->con_sub_deadline }}
                                    </span>
                                @else
                                    <span class="text-body-secondary small">-</span>
                                @endif
                            </td>
                            <td class="p-3 small text-body">
                                @if(!empty($item->con_venue))
                                    <i class="bi bi-geo-alt-fill me-1 text-danger"></i>{{ Str::limit(strip_tags($item->con_venue), 50) }}
                                @else
                                    <span class="text-body-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-center p-3 font-monospace">
                                {{ number_format($item->con_count ?? 0) }}
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
                                            <button type="submit" class="btn btn-sm btn-success" title="กู้คืนข้อมูล"><i class="bi bi-arrow-counterclockwise me-1.5"></i> กู้คืน</button>
                                        </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-body-secondary">
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
