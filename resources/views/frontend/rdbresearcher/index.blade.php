@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="bi bi-people-fill"></i> นักวิจัย (Researchers)</h2>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('frontend.rdbresearcher.index') }}" method="GET" class="row g-3">
                        <div class="col-md-5">
                            <label for="search" class="form-label">ค้นหา (ชื่อ-นามสกุล)</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="ระบุชื่อ หรือนามสกุล...">
                        </div>
                        <div class="col-md-5">
                            <label for="department" class="form-label">หน่วยงาน</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach($departments as $dep)
                                    <option value="{{ $dep->department_id }}" {{ request('department') == $dep->department_id ? 'selected' : '' }}>
                                        {{ $dep->department_nameTH }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 d-flex justify-content-end align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="bi bi-search"></i> ค้นหา</button>
                    <a href="{{ route('frontend.rdbresearcher.index') }}" class="btn btn-secondary me-2">ล้างค่า</a>
                    <button type="submit" formaction="{{ route('frontend.rdbresearcher.export') }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Export CSV</button>
                </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @forelse($items as $item)
        <div class="col">
            <div class="card h-100 shadow-sm hover-card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($item->researcher_picture)
                            <img src="{{ asset('storage/uploads/researchers/' . $item->researcher_picture) }}" 
                                 alt="{{ $item->researcher_fname }}" 
                                 class="rounded-circle object-fit-cover"
                                 style="width: 120px; height: 120px; border: 3px solid #f8f9fa;">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto text-white" 
                                 style="width: 120px; height: 120px; font-size: 3rem;">
                                {{ strtoupper(substr($item->researcher_fnameEN ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <h5 class="card-title mb-1">
                        {{ $item->prefix->prefix_nameTH ?? '' }}{{ $item->researcher_fname }} {{ $item->researcher_lname }}
                    </h5>
                    <p class="text-muted small mb-2">
                        {{ $item->researcher_fnameEN }} {{ $item->researcher_lnameEN }}
                    </p>
                    <p class="card-text text-primary small mb-3">
                        {{ $item->department->department_nameTH ?? '-' }}
                    </p>
                    <a href="{{ route('frontend.rdbresearcher.show', $item->researcher_id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                        ดูประวัติ
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                ไม่พบข้อมูลนักวิจัย
            </div>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $items->withQueryString()->links() }}
    </div>
</div>

<style>
.hover-card {
    transition: transform 0.2s;
}
.hover-card:hover {
    transform: translateY(-5px);
}
</style>
@endsection