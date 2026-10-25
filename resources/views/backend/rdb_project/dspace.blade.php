@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backend.rdb_project.index') }}" class="text-decoration-none">จัดการโครงการวิจัย</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ส่งออก DSpace</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-file-earmark-zip me-2 text-info"></i>ส่งออกแพ็กเกจคลังปัญญา DSpace (Simple Archive Format)
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('backend.rdb_project.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                <i class="bi bi-arrow-left me-1.5"></i> กลับหน้ารายการ
            </a>
        </div>
    </div>

    <!-- Alert / Explanation -->
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
        <i class="bi bi-info-circle-fill fs-4 text-info"></i>
        <div>
            <strong>DSpace Simple Archive Format (SAF):</strong>
            ระบบจะสร้างไฟล์ <code>.zip</code> ที่บรรจุ <code>dublin_core.xml</code>, <code>metadata_dspace.xml</code>, <code>contents</code> พร้อมไฟล์รายงานฉบับเต็ม PDF เพื่อนำเข้าสู่ระบบคลังปัญญาสถาบัน (Institutional Repository) ได้ทันที
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow-sm border mb-4">
        <div class="card-body">
            <form action="{{ route('backend.rdb_project.dspace_index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">ค้นหาชื่อโครงการ / รหัส</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="พิมพ์ชื่อโครงการ หรือ รหัสโครงการ...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">ปีงบประมาณ</label>
                    <select name="year_id" class="form-select">
                        <option value="">-- ทั้งหมดทุกปี --</option>
                        @foreach($years as $year)
                            <option value="{{ $year->year_id }}" {{ request('year_id') == $year->year_id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">หน่วยงาน / คณะ</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- ทั้งหมดทุกหน่วยงาน --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_nameTH }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้น</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-list-ul me-2 text-primary"></i>รายการโครงการวิจัยที่พร้อมส่งออก
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2">{{ number_format($items->total()) }} รายการ</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 70px;">รหัส</th>
                            <th>ชื่อโครงการ</th>
                            <th style="width: 140px;">ปี / หน่วยงาน</th>
                            <th style="width: 180px;">คณะผู้วิจัย</th>
                            <th class="text-center" style="width: 120px;">ไฟล์เล่มรายงาน</th>
                            <th class="text-center" style="width: 160px;">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="text-center fw-bold text-secondary">{{ $item->pro_id }}</td>
                            <td>
                                @if($item->pro_code)
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle me-1">{{ $item->pro_code }}</span>
                                @endif
                                <a href="{{ route('backend.rdb_project.show', $item->pro_id) }}" class="fw-semibold text-decoration-none text-body hover-primary">
                                    {{ $item->pro_nameTH ?: $item->pro_nameEN }}
                                </a>
                            </td>
                            <td>
                                <div><i class="bi bi-calendar3 me-1 text-primary"></i>{{ $item->year->year_name ?? '-' }}</div>
                                <small class="text-body-secondary">{{ $item->department->department_nameTH ?? '-' }}</small>
                            </td>
                            <td>
                                @php
                                    $authorNames = [];
                                    foreach ($item->rdbProjectWorks as $w) {
                                        if ($w->researcher) {
                                            $prefix = $w->researcher->prefix->prefix_nameTH ?? '';
                                            $authorNames[] = trim("{$prefix}{$w->researcher->researcher_fname} {$w->researcher->researcher_lname}");
                                        }
                                    }
                                @endphp
                                <small class="text-body-secondary">{{ !empty($authorNames) ? implode(', ', array_slice($authorNames, 0, 2)) . (count($authorNames) > 2 ? '...' : '') : '-' }}</small>
                            </td>
                            <td class="text-center">
                                @if($item->pro_file || $item->pro_file_path)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-file-earmark-check me-1"></i>มีไฟล์ PDF</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">เฉพาะข้อมูล</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('backend.rdb_project.export_dspace', $item->pro_id) }}" class="btn btn-outline-info btn-sm d-inline-flex align-items-center shadow-sm">
                                    <i class="bi bi-download me-1.5"></i> ส่งออก ZIP
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-body-secondary">ไม่พบข้อมูลโครงการวิจัยตามเงื่อนไข</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
