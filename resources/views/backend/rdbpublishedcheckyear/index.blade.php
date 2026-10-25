@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body"><i class="bi bi-calendar-range text-primary me-2"></i>กำหนดระยะเวลาปีการศึกษา / ปีงบประมาณ (งานตีพิมพ์ & นำไปใช้ประโยชน์)</h4>
            <div class="text-secondary small">กำหนดช่วงวันที่เริ่มต้น-สิ้นสุดของปีการศึกษาและปีงบประมาณ เพื่อใช้คำนวณปีอัตโนมัติเมื่อบันทึกผลงานตีพิมพ์และการนำไปใช้ประโยชน์</div>
        </div>
        <a href="{{ route('backend.rdbpublishedcheckyear.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มช่วงเวลาปี
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('backend.rdbpublishedcheckyear.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <select name="year_id" class="form-select form-select-sm">
                        <option value="">-- ทุกปี พ.ศ. --</option>
                        @foreach($years as $y)
                            <option value="{{ $y->year_id }}" {{ request('year_id') == $y->year_id ? 'selected' : '' }}>
                                ปี {{ $y->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="ค้นหาปี พ.ศ. ...">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    @if(request()->anyFilled(['year_id', 'search']))
                        <a href="{{ route('backend.rdbpublishedcheckyear.index') }}" class="btn btn-outline-secondary btn-sm">ล้างค่า</a>
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
                            <th style="width: 120px;">ปี พ.ศ.</th>
                            <th>ช่วงปีการศึกษา (เริ่มต้น – สิ้นสุด)</th>
                            <th>ช่วงปีงบประมาณ (เริ่มต้น – สิ้นสุด)</th>
                            <th class="text-center" style="width: 140px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                                    ปี {{ $item->year->year_name ?? $item->year_id }}
                                </span>
                            </td>
                            <td>
                                <i class="bi bi-mortarboard text-info me-1"></i>
                                {{ \App\Helpers\ThaiDateHelper::format($item->rdbyearedu_start, false, true) }}
                                <span class="text-muted mx-1">ถึง</span>
                                {{ \App\Helpers\ThaiDateHelper::format($item->rdbyearedu_end, false, true) }}
                            </td>
                            <td>
                                <i class="bi bi-cash-coin text-success me-1"></i>
                                {{ \App\Helpers\ThaiDateHelper::format($item->rdbyearbud_start, false, true) }}
                                <span class="text-muted mx-1">ถึง</span>
                                {{ \App\Helpers\ThaiDateHelper::format($item->rdbyearbud_end, false, true) }}
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('backend.rdbpublishedcheckyear.show', $item->id) }}" class="btn btn-sm btn-outline-info me-1" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('backend.rdbpublishedcheckyear.edit', $item->id) }}" class="btn btn-sm btn-outline-warning me-1" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('backend.rdbpublishedcheckyear.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบข้อมูลนี้?');">
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
                            <td colspan="4" class="text-center py-4 text-secondary">ไม่พบข้อมูลกำหนดระยะเวลา</td>
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
