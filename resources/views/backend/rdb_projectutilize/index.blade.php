@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-rocket-takeoff"></i> จัดการการใช้ประโยชน์ (Utilization)</h1>
         <a href="{{ route('backend.rdbprojectutilize.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มข้อมูล
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-top">
                    <thead>
                        <tr>
                            <th>โครงการ</th>
                            <th>หน่วยงานที่นำไปใช้</th>
                            <th>วันที่</th>
                            <th>ที่อยู่/จังหวัด</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                @if($item->project)
                                    <div class="fw-bold">{{ $item->project->pro_nameTH }}</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $item->utz_department_name ?? '-' }}</td>
                            <td>{{ \App\Helpers\ThaiDateHelper::format($item->utz_date, false, true) }}</td>
                            <td>{{ $item->changwat->changwat_t ?? '-' }}</td>
                            <td>
                                <a href="{{ route('backend.rdbprojectutilize.show', $item->utz_id) }}" class="btn btn-sm btn-info text-white" title="ดูรายละเอียด"><i class="bi bi-eye"></i></a>
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
