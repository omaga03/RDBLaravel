@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-wallet2 me-2"></i>จัดการประเภททุน</h2>
        <a href="{{ route('backend.rdbprojecttype.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มประเภทใหม่
        </a>
    </div>

    <x-search-bar :searchRoute="route('backend.rdbprojecttype.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">ชื่อประเภททุน</label>
                <input type="text" name="pt_name" class="form-control" value="{{ request('pt_name') }}" placeholder="ค้นหาประเภททุน...">
            </div>
            <div class="col-md-6">
                <label class="form-label">ปีงบประมาณ</label>
                <input type="text" name="year" class="form-control" value="{{ request('year') }}" placeholder="ค้นหาปี...">
            </div>
        </div>
    </x-search-bar>

    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>ชื่อประเภททุน / รายละเอียด</th>
                        <th style="width: 80px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $fors = \App\Models\RdbProjectType::getPtforlist();
                        $creates = \App\Models\RdbProjectType::getCreatelist();
                    @endphp
                    @forelse($items as $item)
                    <tr>
                        <td><code>{{ $item->pt_id }}</code></td>
                        <td>
                            <div class="fw-bold text-primary">
                                @if($item->year)
                                    <span class="me-2 text-danger" title="ปีงบประมาณ">
                                        <i class="bi bi-calendar-event"></i> {{ $item->year->year_name }} : 
                                    </span>
                                @endif
                                {{ $item->pt_name }}
                            </div>
                            <div class="small text-muted mt-1">
                                @if($item->projectTypeGroup)
                                    <span class="me-3" title="กลุ่มประเภททุน">
                                        <i class="bi bi-collection text-secondary"></i> {{ $item->projectTypeGroup->pttg_name }}
                                    </span>
                                @endif
                                @if(isset($fors[$item->pt_for]))
                                    <span class="me-3" title="สำหรับ">
                                        <i class="bi bi-person-badge text-info"></i> {{ $fors[$item->pt_for] }}
                                    </span>
                                @endif
                                @if($item->pt_type == '1')
                                    <span class="me-3 text-warning" title="คำนวณงบประมาณ">
                                        <i class="bi bi-calculator"></i> คำนวณเงิน
                                    </span>
                                @endif
                                @if($item->pt_utz == '1')
                                    <span class="text-success" title="การนำไปใช้ประโยชน์ (QA)">
                                        <i class="bi bi-check-circle-fill"></i> คำนวณ QA
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('backend.rdbprojecttype.show', $item->pt_id) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="mt-3">{{ $items->withQueryString()->links() }}</div>
        @endif
    </x-card>
</div>
@endsection