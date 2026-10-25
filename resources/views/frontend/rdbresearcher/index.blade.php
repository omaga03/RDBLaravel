@extends('layouts.app')

@section('content')
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active" aria-current="page">นักวิจัย</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-people-fill me-2 text-primary"></i>นักวิจัย (Researchers)
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-body border text-body-secondary px-3 py-2">
                <i class="bi bi-database me-1 text-primary"></i> ทั้งหมด {{ number_format($items->total()) }} รายการ
            </span>
            <a href="{{ route('frontend.rdbresearcher.export', request()->query()) }}" class="btn btn-outline-success btn-sm d-inline-flex align-items-center" style="height: 38px;">
                <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Search Component -->
    <x-search-bar :searchRoute="route('frontend.rdbresearcher.index')"
        simplePlaceholder="ค้นหาจาก: ชื่อ-นามสกุล (ไทย/อังกฤษ)">

        <div class="row g-3">
            {{-- Name Search --}}
            <div class="col-md-4">
                <label class="form-label">ชื่อ-นามสกุล (ไทย/อังกฤษ)</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                    placeholder="ระบุชื่อ หรือนามสกุล (ไทยหรืออังกฤษ)...">
            </div>
            {{-- Other Keywords / Note Search --}}
            <div class="col-md-4">
                <label class="form-label">คำค้นอื่น ๆ (เช่น หมายเหตุ, อีเมล)</label>
                <input type="text" class="form-control" name="note" value="{{ request('note') }}"
                    placeholder="ระบุคำค้นในหมายเหตุ, ความเชี่ยวชาญ, หรืออีเมล...">
            </div>
            {{-- Department --}}
            <div class="col-md-4">
                <label class="form-label">หน่วยงาน/คณะ</label>
                <select class="form-select" name="department">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($departments as $dep)
                        <option value="{{ $dep->department_id }}" {{ (request('department') == $dep->department_id || request('department_id') == $dep->department_id) ? 'selected' : '' }}>
                            {{ $dep->department_nameTH }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-search-bar>

    <!-- Results Table Card -->
    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายชื่อนักวิจัย
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;" class="text-center text-nowrap">#</th>
                        <th style="width: 70px;" class="text-center text-nowrap">รูป</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>หน่วยงาน</th>
                        <th class="text-center text-nowrap" style="width: 100px;">H-Index</th>
                        <th class="text-center text-nowrap" style="width: 100px;">สถานะ</th>
                        <th class="text-center text-nowrap" style="width: 110px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                    <tr>
                        <td class="text-center text-body-secondary small">
                            {{ ($items->currentPage() - 1) * $items->perPage() + $index + 1 }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('frontend.rdbresearcher.show', $item->researcher_id) }}" class="d-inline-block text-decoration-none">
                                @if($item->profile_image_url)
                                    <img src="{{ $item->profile_image_url }}"
                                         alt="{{ $item->researcher_fname }}"
                                         class="rounded-circle object-fit-cover shadow-sm"
                                         style="width: 46px; height: 46px; border: 2px solid var(--bs-border-color);">
                                @else
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto text-white fw-bold shadow-sm"
                                         style="width: 46px; height: 46px; font-size: 1.1rem; border: 2px solid var(--bs-border-color);">
                                        {{ strtoupper(mb_substr($item->researcher_fnameEN ?? $item->researcher_fname ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                            </a>
                        </td>
                        <td>
                            <div>
                                <a href="{{ route('frontend.rdbresearcher.show', $item->researcher_id) }}"
                                   class="fw-semibold text-body text-decoration-none hover-primary text-break">
                                    {{ $item->prefix->prefix_nameTH ?? '' }}{{ $item->researcher_fname }} {{ $item->researcher_lname }}
                                </a>
                            </div>
                            @if($item->researcher_fnameEN || $item->researcher_lnameEN)
                                <small class="text-body-secondary d-block text-break">
                                    {{ $item->researcher_fnameEN }} {{ $item->researcher_lnameEN }}
                                </small>
                            @endif
                            @if($item->scopus_authorId)
                                <small class="text-body-secondary d-block">
                                    <i class="bi bi-journal-text me-1 text-primary"></i>Scopus: {{ $item->scopus_authorId }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <span class="text-primary text-break">{{ $item->department->department_nameTH ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            @if($item->researcher_hindex)
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle fs-6 px-3">{{ $item->researcher_hindex }}</span>
                            @else
                                <span class="text-body-secondary">-</span>
                            @endif
                        </td>
                        <td class="text-center text-nowrap">
                            @if($item->status)
                                <span class="badge bg-{{ $item->status->restatus_id == 1 ? 'success-subtle text-success-emphasis border border-success-subtle' : 'secondary-subtle text-secondary-emphasis border border-secondary-subtle' }}">
                                    {{ $item->status->rst_name }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('frontend.rdbresearcher.show', $item->researcher_id) }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> ดูข้อมูล
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-body-secondary">
                            <i class="bi bi-people display-5 d-block mb-2"></i>
                            ไม่พบข้อมูลนักวิจัย
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
</style>
@endsection