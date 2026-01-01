@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-people-fill me-2"></i>จัดการนักวิจัย (Researchers)</h2>
        <a href="{{ route('backend.rdb_researcher.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มนักวิจัยใหม่
        </a>
    </div>

    {{-- Central Template Search --}}
    <x-search-bar :searchRoute="route('backend.rdb_researcher.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">คำค้นหา (Keywords)</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="ชื่อ, อีเมล, หมายเหตุ...">
            </div>
            <div class="col-md-4">
                <label class="form-label">หน่วยงาน (Department)</label>
                <x-tom-select 
                    name="department_id" 
                    :options="$departments->pluck('department_nameTH', 'department_id')" 
                    :value="request('department_id')" 
                    placeholder="-- ทั้งหมด --"
                />
            </div>
            <div class="col-md-4">
                <label class="form-label">สาขาทางวิชาการ</label>
                <x-tom-select 
                    name="depcat_id" 
                    :options="$categories->pluck('depcat_name', 'depcat_id')" 
                    :value="request('depcat_id')" 
                    placeholder="-- ทั้งหมด --"
                />
            </div>
        </div>
    </x-search-bar>

    {{-- Data Table --}}
    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-top mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50%" class="ps-4">ข้อมูลนักวิจัย</th>
                        <th style="width: 35%">หน่วยงาน</th>
                        <th style="width: 15%" class="text-center">จัดการ</th>
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
                            ไม่พบข้อมูลนักวิจัย
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($researchers->hasPages())
        <div class="mt-3">
            {{ $researchers->withQueryString()->links() }}
        </div>
        @endif
    </x-card>
</div>
@endsection
