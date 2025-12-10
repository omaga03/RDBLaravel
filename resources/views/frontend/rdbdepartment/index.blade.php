@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-building"></i> หน่วยงาน (Departments)</h2>
        <a href="{{ route('frontend.rdbdepartment.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มหน่วยงาน
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('frontend.rdbdepartment.index') }}" method="GET" class="row g-3 mb-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="ค้นหาหน่วยงาน (รหัส, ชื่อ)...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้นหา</button>
                </div>
            </form>

            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="10%">รหัส</th>
                        <th width="30%">ชื่อ (ไทย)</th>
                        <th width="30%">ชื่อ (EN)</th>
                        <th width="15%">ชนิด</th>
                        <th width="10%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->department_id }}</td>
                        <td><span class="badge bg-secondary">{{ $item->department_code }}</span></td>
                        <td>{{ $item->department_nameTH }}</td>
                        <td>{{ $item->department_nameEN }}</td>
                        <td>{{ $item->departmentType->tdepartment_nameTH ?? '-' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('frontend.rdbdepartment.show', $item->department_id) }}" class="btn btn-info" title="ดู">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('frontend.rdbdepartment.edit', $item->department_id) }}" class="btn btn-warning" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('frontend.rdbdepartment.destroy', $item->department_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="ลบ">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">
                {{ $items->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection