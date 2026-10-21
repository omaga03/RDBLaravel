@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body"><i class="bi bi-tags-fill text-primary me-2"></i>จัดการหมวดหมู่ทุนหลัก (Master Category)</h4>
            <div class="text-secondary small">จัดกลุ่มประเภททุนข้ามปี (ววน./สกสว., แหล่งทุนภายนอก, ทุนภายในคณะ, รายได้มหาวิทยาลัย ฯลฯ) เพื่อการวิเคราะห์และการสืบค้น</div>
        </div>
        <a href="{{ route('backend.rdbprojecttypesmaster.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มหมวดหมู่ทุนหลัก
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('backend.rdbprojecttypesmaster.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหาชื่อหมวดหมู่ทุน..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="pttg_id" class="form-select form-select-sm">
                        <option value="">-- ทุกกลุ่มประเภททุน --</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->pttg_id }}" {{ request('pttg_id') == $g->pttg_id ? 'selected' : '' }}>
                                {{ $g->pttg_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    @if(request()->anyFilled(['search', 'pttg_id']))
                        <a href="{{ route('backend.rdbprojecttypesmaster.index') }}" class="btn btn-outline-secondary btn-sm">ล้างค่า</a>
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
                            <th style="width: 70px;" class="text-center">ลำดับ</th>
                            <th>ชื่อหมวดหมู่ทุนหลัก</th>
                            <th>สังกัดกลุ่มประเภททุน</th>
                            <th class="text-center" style="width: 140px;">จำนวนประเภททุน</th>
                            <th class="text-center" style="width: 130px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="text-center text-secondary">{{ $item->order_index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge rounded-circle p-2 me-2" style="background-color: {{ $item->color_code ?: '#3B82F6' }};">&nbsp;</span>
                                    <a href="{{ route('backend.rdbprojecttypesmaster.show', $item->master_pt_id) }}" class="text-decoration-none fw-semibold text-body hover-primary">
                                        {{ $item->master_pt_name }}
                                    </a>
                                </div>
                            </td>
                            <td>
                                @if($item->projectTypeGroup)
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                        {{ $item->projectTypeGroup->pttg_name }}
                                    </span>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('backend.rdbprojecttype.index', ['master_pt_id' => $item->master_pt_id]) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none px-2.5 py-1">
                                    {{ $item->project_types_count }} รายการ
                                </a>
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('backend.rdbprojecttypesmaster.show', $item->master_pt_id) }}" class="btn btn-sm btn-outline-info me-1" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('backend.rdbprojecttypesmaster.edit', $item->master_pt_id) }}" class="btn btn-sm btn-outline-warning me-1" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('backend.rdbprojecttypesmaster.destroy', $item->master_pt_id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ" {{ $item->project_types_count > 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-secondary">ไม่พบข้อมูลหมวดหมู่ทุนหลัก</td>
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
