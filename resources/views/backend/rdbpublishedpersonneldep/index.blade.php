@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body"><i class="bi bi-diagram-3-fill text-primary me-2"></i>จัดการจำนวนบุคลากรแยกตามคณะ / หลักสูตร / สาขาวิชา (งานตีพิมพ์)</h4>
            <div class="text-secondary small">กำหนดจำนวนอาจารย์/บุคลากรสายวิชาการระดับคณะ หลักสูตร และสาขาวิชา เพื่อใช้คำนวณสัดส่วนผลงานตีพิมพ์</div>
        </div>
        <a href="{{ route('backend.rdbpublishedpersonneldep.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มข้อมูลจำนวนบุคลากร
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('backend.rdbpublishedpersonneldep.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
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
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">-- ทุกคณะ/หน่วยงาน --</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->department_id }}" {{ request('department_id') == $d->department_id ? 'selected' : '' }}>
                                {{ $d->department_nameTH }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    @if(request()->anyFilled(['year_id', 'department_id']))
                        <a href="{{ route('backend.rdbpublishedpersonneldep.index') }}" class="btn btn-outline-secondary btn-sm">ล้างค่า</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 110px;">ปี พ.ศ.</th>
                            <th>คณะ / หน่วยงาน</th>
                            <th>หลักสูตร / สาขาวิชา</th>
                            <th>กลุ่มสาขา</th>
                            <th class="text-end" style="width: 120px;">จำนวนรวม</th>
                            <th class="text-end" style="width: 120px;">ปีการศึกษา</th>
                            <th class="text-end" style="width: 120px;">ปีงบประมาณ</th>
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
                            <td class="fw-semibold">{{ $item->department->department_nameTH ?? '-' }}</td>
                            <td class="small">
                                <div>{{ $item->departmentCourse->depcou_name ?? '-' }}</div>
                                @if($item->depMajor)
                                    <div class="text-secondary">{{ $item->depMajor->maj_nameTH }}</div>
                                @endif
                            </td>
                            <td>{{ $item->departmentCategory->depcat_name ?? '-' }}</td>
                            <td class="text-end fw-bold">{{ number_format($item->personnel_num, 1) }}</td>
                            <td class="text-end text-secondary">{{ number_format($item->personnel_numedu, 1) }}</td>
                            <td class="text-end text-secondary">{{ number_format($item->personnel_numbud, 1) }}</td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('backend.rdbpublishedpersonneldep.show', $item->perpd_id) }}" class="btn btn-sm btn-outline-info me-1" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('backend.rdbpublishedpersonneldep.edit', $item->perpd_id) }}" class="btn btn-sm btn-outline-warning me-1" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('backend.rdbpublishedpersonneldep.destroy', $item->perpd_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบข้อมูลนี้?');">
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
                            <td colspan="8" class="text-center py-4 text-secondary">ไม่พบข้อมูลจำนวนบุคลากร</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
            <div class="card-footer bg-transparent border-top">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
