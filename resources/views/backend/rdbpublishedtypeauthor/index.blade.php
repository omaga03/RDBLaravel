@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body"><i class="bi bi-person-check text-primary me-2"></i>จัดการประเภทผู้แต่งบทความ (Author Types)</h4>
            <div class="text-secondary small">กำหนดประเภทผู้แต่งในผลงานตีพิมพ์เผยแพร่ (เช่น ชื่อแรก First Author, ผู้ประพันธ์บรรณกิจ Corresponding Author, ผู้ร่วมวิจัย)</div>
        </div>
        <a href="{{ route('backend.rdbpublishedtypeauthor.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มประเภทผู้แต่ง
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('backend.rdbpublishedtypeauthor.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="ค้นหาชื่อประเภทผู้แต่ง (ไทย / อังกฤษ)...">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    @if(request()->filled('search'))
                        <a href="{{ route('backend.rdbpublishedtypeauthor.index') }}" class="btn btn-outline-secondary btn-sm">ล้างค่า</a>
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
                            <th style="width: 80px;">รหัส</th>
                            <th>ชื่อประเภทผู้แต่ง (ภาษาไทย)</th>
                            <th>ชื่อประเภทผู้แต่ง (ภาษาอังกฤษ)</th>
                            <th class="text-end" style="width: 140px;">ค่าคะแนน</th>
                            <th class="text-center" style="width: 140px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td><span class="badge bg-secondary-subtle text-secondary-emphasis">#{{ $item->pubta_id }}</span></td>
                            <td class="fw-semibold">{{ $item->pubta_nameTH }}</td>
                            <td class="text-secondary">{{ $item->pubta_nameEN ?? '-' }}</td>
                            <td class="text-end">{{ $item->pubta_score !== null ? number_format($item->pubta_score, 2) : '-' }}</td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('backend.rdbpublishedtypeauthor.show', $item->pubta_id) }}" class="btn btn-sm btn-outline-info me-1" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('backend.rdbpublishedtypeauthor.edit', $item->pubta_id) }}" class="btn btn-sm btn-outline-warning me-1" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('backend.rdbpublishedtypeauthor.destroy', $item->pubta_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบข้อมูลนี้?');">
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
                            <td colspan="5" class="text-center py-4 text-secondary">ไม่พบข้อมูลประเภทผู้แต่งบทความ</td>
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
