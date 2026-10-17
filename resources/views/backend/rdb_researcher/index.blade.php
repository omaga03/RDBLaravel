@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-people-fill me-2"></i>จัดการนักวิจัย (Researchers)</h2>
        <a href="{{ route('backend.rdb_researcher.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มนักวิจัยใหม่
        </a>
    </div>

    {{-- Central Template Search --}}
    <x-search-bar :searchRoute="route('backend.rdb_researcher.index')" :exportRoute="route('backend.rdb_researcher.export')" :collapsed="true">
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

    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการข้อมูลนักวิจัย
                <span class="badge bg-primary rounded-pill ms-2">{{ number_format($researchers->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50%" class="ps-4">ข้อมูลนักวิจัย</th>
                            <th style="width: 35%">หน่วยงาน / สาขา</th>
                            <th style="width: 15%" class="text-center text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($researchers as $researcher)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        @if($researcher->profile_image_url)
                                            <img src="{{ $researcher->profile_image_url }}" 
                                                 alt="Profile" 
                                                 class="rounded-circle border object-fit-cover"
                                                 style="width: 55px; height: 55px;">
                                        @else
                                            <div class="rounded-circle bg-body-secondary d-flex align-items-center justify-content-center border" style="width: 55px; height: 55px;">
                                                <i class="bi bi-person text-body-secondary" style="font-size: 1.5rem;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-6 mb-1">
                                            <a href="{{ route('backend.rdb_researcher.show', $researcher->getKey()) }}" class="text-decoration-none text-body fw-bold hover-primary">
                                                {{ $researcher->prefix->prefix_nameTH ?? '' }} {{ $researcher->researcher_fname }} {{ $researcher->researcher_lname }}
                                            </a>
                                        </div>
                                        <div class="small text-body-secondary mb-1">
                                            @if($researcher->researcher_email)
                                                <span><i class="bi bi-envelope me-1"></i>{{ $researcher->researcher_email }}</span>
                                            @endif
                                            @if($researcher->researcher_mobile)
                                                <span class="ms-2"><i class="bi bi-phone me-1"></i>{{ $researcher->researcher_mobile }}</span>
                                            @endif
                                        </div>
                                        <div class="small text-body-secondary">
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
                            <td class="py-3">
                                @if($researcher->department)
                                    <div class="fw-semibold text-body"><i class="bi bi-building me-1 text-primary"></i>{{ $researcher->department->department_nameTH ?? $researcher->department->department_nameEN }}</div>
                                @elseif($researcher->researcher_note)
                                    <div class="text-body-secondary"><i class="bi bi-card-text me-1"></i>{{ $researcher->researcher_note }}</div>
                                @else
                                    <span class="text-body-secondary fst-italic">- ไม่ระบุ -</span>
                                @endif

                                @if($researcher->departmentCategory)
                                    <div class="small text-body-secondary mt-1"><i class="bi bi-tag me-1"></i>{{ $researcher->departmentCategory->depcat_name }}</div>
                                @endif
                            </td>
                            <td class="text-center py-3 text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('backend.rdb_researcher.show', $researcher->getKey()) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('backend.rdb_researcher.edit', $researcher->getKey()) }}" class="btn btn-outline-secondary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                ไม่พบข้อมูลนักวิจัย
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($researchers->hasPages())
            <div class="d-flex justify-content-center p-4">
                {{ $researchers->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
