@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-people-fill"></i> จัดการนักวิจัย (Researchers)</h1>
        <a href="{{ route('backend.rdb_researcher.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มนักวิจัยใหม่
        </a>
    </div>

    <!-- Search Card -->
    <div class="card shadow-sm mb-4 search-box">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold"><i class="bi bi-search"></i> ค้นหา (Search)</h6>
            <button class="btn btn-sm btn-toggle-search" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSearch" aria-expanded="{{ request()->anyFilled(['q', 'department_id', 'depcat_id']) ? 'true' : 'false' }}" aria-controls="collapseSearch">
                <i class="bi bi-chevron-down"></i> แสดง/ซ่อน
            </button>
        </div>
        <div class="collapse {{ request()->anyFilled(['q', 'department_id', 'depcat_id']) ? 'show' : '' }}" id="collapseSearch">
            <div class="card-body">
                <form action="{{ route('backend.rdb_researcher.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="q" class="form-label">คำค้นหา (Keywords)</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="ชื่อ, อีเมล, หมายเหตุ...">
                        </div>
                        <div class="col-md-4">
                            <label for="department_id" class="form-label">หน่วยงาน (Department)</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">-- ทั้งหมด (All) --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                        {{ $dept->department_nameTH ?? $dept->department_nameEN }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="depcat_id" class="form-label">สาขาทางวิชาการ (Academic Category)</label>
                            <select class="form-select" id="depcat_id" name="depcat_id">
                                <option value="">-- ทั้งหมด (All) --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->depcat_id }}" {{ request('depcat_id') == $cat->depcat_id ? 'selected' : '' }}>
                                        {{ $cat->depcat_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> ค้นหา</button>
                            <a href="{{ route('backend.rdb_researcher.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> ล้างค่า</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Researchers List -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if (session('success'))
                <div class="alert alert-success m-3" role="alert">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-top mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50%" class="ps-4">ข้อมูลนักวิจัย (Researcher Info)</th>
                            <th style="width: 35%">หน่วยงาน (Affiliation)</th>
                            <th style="width: 15%" class="text-center">จัดการ (Actions)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($researchers as $researcher)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        @if($researcher->researcher_picture)
                                            <img src="{{ asset('storage/uploads/researchers/' . $researcher->researcher_picture) }}" 
                                                 alt="Profile" 
                                                 class="rounded-circle border"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width: 60px; height: 60px;">
                                                <i class="bi bi-person text-secondary" style="font-size: 1.5rem;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-5 text-primary">
                                            {{ $researcher->prefix->prefix_nameTH ?? '' }} {{ $researcher->researcher_fname }} {{ $researcher->researcher_lname }}
                                        </div>
                                        <div class="small text-muted mb-1">
                                            @if($researcher->researcher_email)
                                                <i class="bi bi-envelope"></i> {{ $researcher->researcher_email }}
                                            @endif
                                            @if($researcher->researcher_mobile)
                                                <span class="ms-2"><i class="bi bi-phone"></i> {{ $researcher->researcher_mobile }}</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted mb-1">
                                            @if($researcher->scopus_authorId)
                                                <span class="me-3" title="Scopus Author ID">
                                                    <i class="bi bi-journal-bookmark-fill text-info"></i> Scopus: 
                                                    <a href="https://www.scopus.com/authid/detail.uri?authorId={{ $researcher->scopus_authorId }}" target="_blank" class="text-decoration-none">
                                                        {{ $researcher->scopus_authorId }}
                                                    </a>
                                                    @if($researcher->researcher_hindex)
                                                        <span class="badge bg-info-subtle text-info-emphasis ms-1" title="h-index">h: {{ $researcher->researcher_hindex }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                            @if($researcher->orcid)
                                                <span title="ORCID">
                                                    <i class="bi bi-link-45deg text-success"></i> ORCID: 
                                                    <a href="https://orcid.org/{{ $researcher->orcid }}" target="_blank" class="text-decoration-none">
                                                        {{ $researcher->orcid }}
                                                    </a>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($researcher->department)
                                    <div class="fw-semibold"><i class="bi bi-building"></i> {{ $researcher->department->department_nameTH ?? $researcher->department->department_nameEN }}</div>
                                @elseif($researcher->researcher_note)
                                    <div class="text-muted"><i class="bi bi-card-text"></i> {{ $researcher->researcher_note }}</div>
                                @else
                                    <span class="text-muted fst-italic">- ไม่ระบุ -</span>
                                @endif

                                @if($researcher->departmentCategory)
                                    <div class="small text-muted mt-1"><i class="bi bi-tag"></i> สาขาทางวิชาการ: {{ $researcher->departmentCategory->depcat_name }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                    <a href="{{ route('backend.rdb_researcher.show', $researcher->getKey()) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                ไม่พบข้อมูลนักวิจัย (No available data)
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($researchers->hasPages())
                <div class="card-footer py-3">
                    {{ $researchers->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
