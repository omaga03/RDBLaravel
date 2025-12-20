@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="bi bi-folder2-open"></i> จัดการโครงการวิจัย (Manage Projects)</h2>
                <a href="{{ route('backend.rdb_project.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> เพิ่มโครงการใหม่
                </a>
            </div>
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> ค้นหาโครงการวิจัย</h5>
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-expanded="{{ request()->anyFilled(['pro_nameTH', 'year_id', 'department_id', 'pt_id', 'ps_id']) ? 'true' : 'false' }}" aria-controls="searchCollapse">
                        <i class="bi bi-chevron-down"></i> แสดง/ซ่อน
                    </button>
                </div>
                <div class="collapse {{ request()->anyFilled(['pro_nameTH', 'year_id', 'department_id', 'pt_id', 'ps_id']) ? 'show' : '' }}" id="searchCollapse">
                    <div class="card-body">
                        <form action="{{ route('backend.rdb_project.index') }}" method="GET">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="pro_nameTH" class="form-label">ชื่อโครงการ</label>
                                    <input type="text" class="form-control" id="pro_nameTH" name="pro_nameTH" value="{{ request('pro_nameTH') }}" placeholder="ระบุชื่อโครงการ...">
                                </div>
                                <div class="col-md-2">
                                    <label for="year_id" class="form-label">ปีงบประมาณ</label>
                                    <select class="form-select" id="year_id" name="year_id">
                                        <option value="">-- ทั้งหมด --</option>
                                        @foreach($years as $year)
                                            <option value="{{ $year->year_id }}" {{ request('year_id') == $year->year_id ? 'selected' : '' }}>
                                                {{ $year->year_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="department_id" class="form-label">หน่วยงาน</label>
                                    <select class="form-select" id="department_id" name="department_id">
                                        <option value="">-- ทั้งหมด --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                                {{ $dept->department_nameTH ?? $dept->department_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="pt_id" class="form-label">ประเภท</label>
                                    <select class="form-select" id="pt_id" name="pt_id">
                                        <option value="">-- ทั้งหมด --</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->pt_id }}" {{ request('pt_id') == $type->pt_id ? 'selected' : '' }}>
                                                {{ $type->pt_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="ps_id" class="form-label">สถานะ</label>
                                    <select class="form-select" id="ps_id" name="ps_id">
                                        <option value="">-- ทั้งหมด --</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->ps_id }}" {{ request('ps_id') == $status->ps_id ? 'selected' : '' }}>
                                                {{ $status->ps_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 d-flex justify-content-end align-items-end gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> ค้นหา</button>
                                    <a href="{{ route('backend.rdb_project.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> ล้างค่า</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-top">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50%;">ชื่อโครงการ</th>
                                    <th style="width: 30%;">นักวิจัย / สังกัด</th>
                                    <th style="width: 20%;" class="text-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                <tr>
                                    <td>
                                        <div class="fw-bold">
                                            @if($project->pro_id)
                                                <span class="badge bg-secondary me-1">#{{ $project->pro_id }}</span>
                                            @endif
                                            {!! $project->pro_nameTH !!}
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            @if($project->year)
                                                <span class="badge bg-info text-dark">ปี {{ $project->year->year_name }}</span>
                                            @endif
                                            @if($project->type)
                                                <span class="badge bg-light text-dark border">{{ $project->type->pt_name }}</span>
                                            @endif
                                            @if($project->pro_budget)
                                                <span class="badge bg-success bg-opacity-10 text-success">฿ {{ number_format($project->pro_budget, 0) }}</span>
                                            @endif
                                        </small>
                                        @if($project->status)
                                            <div class="mt-1">
                                                <span class="badge bg-primary">{{ $project->status->ps_name }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->rdbProjectWorks->isNotEmpty())
                                            @php
                                                $leaders = $project->rdbProjectWorks->whereIn('position_id', [1, 2]);
                                                $firstLeader = $leaders->first();
                                                $remainingCount = $project->rdbProjectWorks->count() - 1;
                                            @endphp
                                            @if($firstLeader && $firstLeader->researcher)
                                            <div>
                                                <i class="bi bi-person-circle"></i> 
                                                {{ $firstLeader->researcher->researcher_fname }} {{ $firstLeader->researcher->researcher_lname }}
                                                @if($remainingCount > 0)
                                                    <span class="text-muted small">(+{{ $remainingCount }})</span>
                                                @endif
                                            </div>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif

                                        @if($project->department)
                                            <div class="text-muted mt-1 small">
                                                <i class="bi bi-building"></i> {{ $project->department->department_nameTH ?? $project->department->department_name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('backend.rdb_project.show', $project->getKey()) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        ไม่พบข้อมูลโครงการวิจัย
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $projects->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
