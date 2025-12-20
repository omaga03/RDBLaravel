@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-award"></i> จัดการทรัพย์สินทางปัญญา (Intellectual Property)</h1>
         <a href="{{ route('backend.rdb_dip.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มข้อมูล
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-top">
                    <thead>
                        <tr>
                            <th>เลขที่คำขอ/ชื่อ</th>
                            <th>ประเภท</th>
                            <th>วันที่ยื่นคำขอ</th>
                            <th>นักวิจัย</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $item->dip_request_number }}</div>
                                <small class="text-muted">{{ $item->dip_name ?? '-' }}</small>
                            </td>
                            <td>{{ $item->dipType->diptype_name ?? '-' }}</td>
                            <td>{{ \App\Helpers\ThaiDateHelper::format($item->dip_request_date, false, true) }}</td>
                            <td>{{ $item->researcher->researcher_fname ?? '' }} {{ $item->researcher->researcher_lname ?? '' }}</td>
                            <td>
                                <a href="{{ route('backend.rdb_dip.show', $item->dip_id) }}" class="btn btn-sm btn-info text-white" title="ดูรายละเอียด"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $items->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
