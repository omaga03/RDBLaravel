@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-building"></i> ประเภทหน่วยงาน (Department Categories)</h2>
        <a href="{{ route('frontend.rdbdepartmentcategory.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มประเภท
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('frontend.rdbdepartmentcategory.index') }}" method="GET" class="row g-3 mb-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="ค้นหาประเภทหน่วยงาน...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้นหา</button>
                </div>
            </form>

            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="15%">#</th>
                        <th width="70%">ชื่อประเภท</th>
                        <th width="15%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->depcat_id }}</td>
                        <td>{{ $item->depcat_name }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('frontend.rdbdepartmentcategory.show', $item->depcat_id) }}" class="btn btn-info" title="ดู">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('frontend.rdbdepartmentcategory.edit', $item->depcat_id) }}" class="btn btn-warning" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('frontend.rdbdepartmentcategory.destroy', $item->depcat_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
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
                        <td colspan="3" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
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