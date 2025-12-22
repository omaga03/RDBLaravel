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
            <h6 class="m-0 font-weight-bold"><i class="bi bi-search"></i> ค้นหาขั้นสูง (Advanced Search)</h6>
            <button class="btn btn-sm btn-toggle-search" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSearch" aria-expanded="{{ request()->anyFilled(['researcher_fname', 'researcher_lname', 'department_id']) ? 'true' : 'false' }}" aria-controls="collapseSearch">
                <i class="bi bi-chevron-down"></i> แสดง/ซ่อน
            </button>
        </div>
        <div class="collapse {{ request()->anyFilled(['researcher_fname', 'researcher_lname', 'department_id']) ? 'show' : '' }}" id="collapseSearch">
            <div class="card-body">
                <form action="{{ route('backend.rdb_researcher.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="researcher_fname" class="form-label">ชื่อ (First Name)</label>
                            <input type="text" class="form-control" id="researcher_fname" name="researcher_fname" value="{{ request('researcher_fname') }}" placeholder="ระบุชื่อ...">
                        </div>
                        <div class="col-md-4">
                            <label for="researcher_lname" class="form-label">นามสกุล (Last Name)</label>
                            <input type="text" class="form-control" id="researcher_lname" name="researcher_lname" value="{{ request('researcher_lname') }}" placeholder="ระบุนามสกุล...">
                        </div>
                        <div class="col-md-4">
                            <label for="department_id" class="form-label">หน่วยงาน (Department)</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">-- ทั้งหมด (All) --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
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
                                        @if($researcher->researcher_codeid)
                                            <div class="small text-muted">
                                                <i class="bi bi-person-badge"></i> {{ $researcher->researcher_codeid }}
                                            </div>
                                        @endif
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
