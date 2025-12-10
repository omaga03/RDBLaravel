@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-list-stars"></i> แผนยุทธศาสตร์ (Strategic Plans)</h2>
        <a href="{{ route('frontend.rdbstrategic.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มแผนฯ
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('frontend.rdbstrategic.index') }}" method="GET" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อแผน...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้นหา</button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="10%">#</th>
                        <th width="40%">ชื่อแผน (ภาษาไทย)</th>
                        <th width="40%">ชื่อแผน (English)</th>
                        <th width="10%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->strategic_id }}</td>
                        <td>{{ $item->strategic_nameTH }}</td>
                        <td>{{ $item->strategic_nameEN }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('frontend.rdbstrategic.show', $item->strategic_id) }}" class="btn btn-info" title="ดู">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('frontend.rdbstrategic.edit', $item->strategic_id) }}" class="btn btn-warning" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('frontend.rdbstrategic.destroy', $item->strategic_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="ลบ">
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