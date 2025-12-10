@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person-badge"></i> คำนำหน้าชื่อ (Name Prefixes)</h2>
        <a href="{{ route('frontend.rdbprefix.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มคำนำหน้า
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('frontend.rdbprefix.index') }}" method="GET" class="row g-3 mb-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="ค้นหาคำนำหน้า...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้นหา</button>
                </div>
            </form>

            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="10%">#</th>
                        <th width="40%">ชื่อ (ไทย)</th>
                        <th width="40%">ชื่อ (English)</th>
                        <th width="10%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->prefix_id }}</td>
                        <td>{{ $item->prefix_nameTH }}</td>
                        <td>{{ $item->prefix_nameEN ?? '-' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('frontend.rdbprefix.show', $item->prefix_id) }}" class="btn btn-info" title="ดู">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('frontend.rdbprefix.edit', $item->prefix_id) }}" class="btn btn-warning" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('frontend.rdbprefix.destroy', $item->prefix_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
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
                        <td colspan="4" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
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