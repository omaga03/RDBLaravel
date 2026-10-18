@extends('layouts.app')

@section('content')
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ผลงานตีพิมพ์</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-journal-text me-2 text-info"></i>ผลงานตีพิมพ์ (Published Works)
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($items->total()) }} รายการ
            </span>
            <a href="{{ route('frontend.rdbpublished.export', request()->query()) }}" class="btn btn-outline-success btn-sm d-inline-flex align-items-center" style="height: 38px;">
                <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
            </a>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    @if(isset($project))
    <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 border" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
            <div>
                กำลังแสดงรายการเฉพาะโครงการ: <strong>{!! $project->pro_nameTH !!}</strong>
            </div>
        </div>
        <a href="{{ route('frontend.rdbpublished.index') }}" class="btn btn-sm btn-outline-info bg-body text-info text-decoration-none shadow-sm">
            แสดงทั้งหมด
        </a>
    </div>
    @endif

    <!-- Search Component -->
    <x-search-bar :searchRoute="route('frontend.rdbpublished.index')"
        simplePlaceholder="ค้นหาจาก: ชื่อผลงาน, วารสาร, นักวิจัย, หรือชื่อโครงการ...">

        @if(request('pro_id'))
            <input type="hidden" name="pro_id" value="{{ request('pro_id') }}">
        @endif

        <div class="row g-3">
            {{-- Row 1: Search, Type, Year --}}
            <div class="col-md-6">
                <label class="form-label">คำค้นหา</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                    placeholder="ระบุชื่อผลงาน, วารสาร, นักวิจัย, หรือชื่อโครงการ...">
            </div>
            <div class="col-md-3">
                <label class="form-label">ประเภทผลงาน</label>
                <select class="form-select" name="pubtype_id">
                    <option value="">-- ทั้งหมด --</option>
                    @if(isset($pubTypes))
                        @foreach($pubTypes as $type)
                            <option value="{{ $type->pubtype_id }}" {{ request('pubtype_id') == $type->pubtype_id ? 'selected' : '' }}>
                                {{ $type->pubtype_group }}{{ $type->pubtype_subgroup ? ' (' . $type->pubtype_subgroup . ')' : '' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">ปีงบประมาณ</label>
                <select class="form-select" name="year_id">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($years as $year)
                        <option value="{{ $year->year_id }}" {{ request('year_id') == $year->year_id ? 'selected' : '' }}>
                            {{ $year->year_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Row 2: Date Range --}}
            <div class="col-md-6">
                <label class="form-label">วันที่ตีพิมพ์ (เริ่มต้น)</label>
                <input type="date" class="form-control" name="date_start" value="{{ request('date_start') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">วันที่ตีพิมพ์ (สิ้นสุด)</label>
                <input type="date" class="form-control" name="date_end" value="{{ request('date_end') }}">
            </div>
        </div>
    </x-search-bar>

    <!-- Results Table Card -->
    <div class="card shadow-sm border">
        <div class="card-header bg-body-tertiary border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการผลงานตีพิมพ์
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50%;">รายละเอียดผลงาน</th>
                            <th style="width: 25%;">ผู้ประพันธ์ / สังกัด</th>
                            <th style="width: 15%;" class="text-center text-nowrap">วันที่ตีพิมพ์</th>
                            <th style="width: 10%;" class="text-center text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="fw-bold mb-1">
                                    <a href="{{ route('frontend.rdbpublished.show', $item->id) }}" class="text-body fw-bold text-decoration-none hover-primary text-break">
                                        {!! $item->pub_name !!}
                                    </a>
                                </div>
                                @if($item->pub_name_journal)
                                    <small class="text-body-secondary d-block mb-1 text-break">
                                        <i class="bi bi-book me-1"></i>{!! $item->pub_name_journal !!}
                                    </small>
                                @endif
                                <div class="small d-flex flex-wrap gap-2 align-items-center mt-1">
                                    @if($item->pubtype)
                                        <span class="badge bg-body-secondary text-body border">
                                            {{ $item->pubtype->pubtype_name ?? $item->pubtype->pubtype_subgroup ?? '-' }}
                                        </span>
                                    @endif
                                    @if($item->year)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                            ปี {{ $item->year->year_name }}
                                        </span>
                                    @endif
                                    @if($item->project)
                                        <span class="text-body-secondary text-break">
                                            <i class="bi bi-link-45deg"></i>โครงการ:
                                            <a href="{{ route('frontend.rdbproject.show', $item->project->pro_id) }}" target="_blank" class="text-primary text-decoration-none hover-underline">
                                                {!! $item->project->pro_nameTH !!}
                                            </a>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $mainAuthor = null;
                                    $otherCount = 0;
                                    
                                    if ($item->authors && $item->authors->count() > 0) {
                                        $mainAuthor = $item->authors->first();
                                        $otherCount = $item->authors->count() - 1;
                                    } elseif ($item->researcher) {
                                        $mainAuthor = $item->researcher;
                                    }
                                @endphp

                                @if($mainAuthor)
                                    <div class="fw-bold">
                                        <a href="{{ route('frontend.rdbresearcher.show', $mainAuthor->researcher_id) }}" 
                                           class="text-decoration-none text-body fw-bold hover-primary">
                                            <i class="bi bi-person-circle text-primary me-1"></i>{!! $mainAuthor->researcher_fname !!} {!! $mainAuthor->researcher_lname !!}
                                        </a>
                                        @if($otherCount > 0)
                                            <span class="text-body-secondary fw-normal">และ {{ $otherCount }} คน</span>
                                        @endif
                                    </div>
                                    @if($mainAuthor->department)
                                    <small class="text-body-secondary d-block mt-1">
                                        <i class="bi bi-building me-1"></i>{!! $mainAuthor->department->department_nameTH !!}
                                    </small>
                                    @endif
                                @else
                                    <span class="text-body-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-center text-body-secondary small text-nowrap">
                                @if($item->pub_date)
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ \App\Helpers\ThaiDateHelper::format($item->pub_date, false, true) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('frontend.rdbpublished.show', $item->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i> ดูข้อมูล
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-journal-x display-6 d-block mb-2"></i>
                                ไม่พบข้อมูลผลงานตีพิมพ์
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
    </div>

<style>
.hover-primary {
    transition: color 0.15s ease-in-out;
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
    text-decoration: underline !important;
}
.hover-underline:hover {
    text-decoration: underline !important;
}
</style>
@endsection