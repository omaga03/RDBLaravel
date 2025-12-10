@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar3"></i> ปีงบประมาณ (Academic Years)</h2>
        <a href="{{ route('frontend.rdbyear.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มปีงบประมาณ
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="20%">#</th>
                        <th width="60%">ปีงบประมาณ (พ.ศ.)</th>
                        <th width="20%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->year_id }}</td>
                        <td class="fw-bold">{{ $item->year_name }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('frontend.rdbyear.show', $item->year_id) }}" class="btn btn-info" title="ดู">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('frontend.rdbyear.edit', $item->year_id) }}" class="btn btn-warning" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('frontend.rdbyear.destroy', $item->year_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
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