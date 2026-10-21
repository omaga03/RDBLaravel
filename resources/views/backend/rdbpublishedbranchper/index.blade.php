@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body"><i class="bi bi-pie-chart-fill text-primary me-2"></i>จัดการสัดส่วนสาขาการวิจัย รายปี (งานตีพิมพ์)</h4>
            <div class="text-secondary small">กำหนดเปอร์เซ็นต์สัดส่วนสาขาการวิจัย/ตีพิมพ์ในแต่ละปีงบประมาณ สำหรับการประเมินและสถิติคณะ</div>
        </div>
        <a href="{{ route('backend.rdbpublishedbranchper.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มสัดส่วนสาขา
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('backend.rdbpublishedbranchper.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <select name="year_id" class="form-select form-select-sm">
                        <option value="">-- ทุกปีงบประมาณ --</option>
                        @foreach($years as $y)
                            <option value="{{ $y->year_id }}" {{ request('year_id') == $y->year_id ? 'selected' : '' }}>
                                ปี {{ $y->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">-- ทุกสาขาวิจัย/ตีพิมพ์ --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->branch_id }}" {{ request('branch_id') == $b->branch_id ? 'selected' : '' }}>
                                {{ $b->branch_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    @if(request()->anyFilled(['year_id', 'branch_id']))
                        <a href="{{ route('backend.rdbpublishedbranchper.index') }}" class="btn btn-outline-secondary btn-sm">ล้างค่า</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 140px;">ปีงบประมาณ</th>
                            <th>สาขางานตีพิมพ์</th>
                            <th class="text-end" style="width: 180px;">สัดส่วนร้อยละ (%)</th>
                            <th class="text-center" style="width: 130px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    ปี {{ $item->year->year_name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('backend.rdbpublishedbranchper.show', $item->branchper_id) }}" class="text-decoration-none fw-semibold text-body hover-primary">
                                    {{ $item->branch->branch_name ?? '-' }}
                                </a>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2.5 py-1 fs-6">
                                    {{ number_format($item->branchper_percent, 2) }} %
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('backend.rdbpublishedbranchper.show', $item->branchper_id) }}" class="btn btn-sm btn-outline-info me-1" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('backend.rdbpublishedbranchper.edit', $item->branchper_id) }}" class="btn btn-sm btn-outline-warning me-1" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('backend.rdbpublishedbranchper.destroy', $item->branchper_id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-secondary">ไม่พบข้อมูลสัดส่วนสาขา</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
                <div class="p-3 border-top">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
