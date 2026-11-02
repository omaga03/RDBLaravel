@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h3 class="fw-semibold mb-3"><i class="bi bi-diagram-3 text-info me-2"></i>แผนงานวิจัย <span class="text-body-secondary small fw-normal">(Research Programs)</span></h3>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="ค้นหาชื่อแผนงาน...">
        </div>
        <div class="col-md-3">
            <select name="year_id" class="form-select">
                <option value="">-- ทุกปีงบประมาณ --</option>
                @foreach($years as $y)
                    <option value="{{ $y->year_id }}" {{ request('year_id') == $y->year_id ? 'selected' : '' }}>{{ $y->year_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-search me-1.5"></i> ค้นหา</button>
            <a href="{{ route('frontend.rdbprojectgroup.index') }}" class="btn btn-outline-secondary">ล้าง</a>
        </div>
    </form>

    <div class="card shadow-sm border">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:90px;">ปีงบฯ</th>
                        <th>แผนงาน</th>
                        <th>หน่วยงาน</th>
                        <th class="text-center">โครงการย่อย</th>
                        <th class="text-end">งบประมาณรวม</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td class="text-center">{{ $item->year->year_name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('frontend.rdbprojectgroup.show', $item->pro_id) }}" class="fw-bold text-decoration-none text-body">{{ strip_tags((string) $item->pro_nameTH) }}</a>
                            @if($item->type)<small class="d-block text-body-secondary">{{ $item->type->pt_name }}</small>@endif
                        </td>
                        <td>{{ $item->department->department_nameTH ?? '-' }}</td>
                        <td class="text-center">{{ number_format($item->sub_projects_count) }}</td>
                        <td class="text-end fw-bold text-success">฿{{ number_format((float) $item->pro_budget + (float) $item->group_budget, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-body-secondary">ไม่พบข้อมูลแผนงาน</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection
