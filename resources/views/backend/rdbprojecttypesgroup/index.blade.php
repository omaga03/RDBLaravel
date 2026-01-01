@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-collection me-2"></i>จัดการกลุ่มประเภทงบประมาณ</h2>
        <a href="{{ route('backend.rdbprojecttypesgroup.create') }}" class="btn btn-success d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มกลุ่มใหม่
        </a>
    </div>

    <x-search-bar :searchRoute="route('backend.rdbprojecttypesgroup.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-12">
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="ค้นหาชื่อกลุ่มประเภทงบประมาณ...">
            </div>
        </div>
    </x-search-bar>

    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>ชื่อกลุ่มประเภทงบประมาณ</th>
                        <th style="width: 150px;">กลุ่ม</th>
                        <th style="width: 150px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->pttg_id }}</td>
                        <td>{{ $item->pttg_name }}</td>
                        <td>
                            @if(isset($groupList[$item->pttg_group]))
                                <span class="badge bg-secondary">{{ $groupList[$item->pttg_group] }}</span>
                            @else
                                {{ $item->pttg_group ?? '-' }}
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('backend.rdbprojecttypesgroup.show', $item->pttg_id) }}" class="btn btn-sm btn-outline-primary" title="ดู"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('backend.rdbprojecttypesgroup.edit', $item->pttg_id) }}" class="btn btn-sm btn-outline-warning" title="แก้ไข"><i class="bi bi-pencil"></i></a>
                            @if($item->canDelete())
                            <form action="{{ route('backend.rdbprojecttypesgroup.destroy', $item->pttg_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบข้อมูลนี้?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            ไม่พบข้อมูล
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $items->withQueryString()->links() }}
        </div>
    </x-card>
</div>
@endsection
