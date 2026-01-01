@extends('layouts.app')

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-search-bar 
        title="จัดการงานวิจัย (ผู้ร่วมโครงการ)" 
        :searchRoute="route('backend.rdbprojectwork.index')"
        :collapsed="true"
    >
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control" placeholder="ค้นหาตามชื่อนักวิจัย หรือ ชื่อโครงการ..." value="{{ request()->q }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> ค้นหา
                </button>
            </div>
        </div>
    </x-search-bar>

    <x-card>
        <x-slot name="actions">
            <a href="{{ route('backend.rdbprojectwork.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg"></i> เพิ่มข้อมูลใหม่
            </a>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>โครงการวิจัย</th>
                        <th>นักวิจัย</th>
                        <th>สัดส่วน</th>
                        <th class="text-center" style="width: 150px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->id ?? '-' }}</td>
                        <td>
                            @if($item->project)
                                <div class="fw-bold text-primary">{{ $item->project->pro_nameTH }}</div>
                                <small class="text-muted">{{ $item->project->pro_code ?? '-' }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->researcher)
                                <div class="fw-bold">{{ $item->researcher->researcher_nameTH ?? $item->researcher->researcher_fname . ' ' . $item->researcher->researcher_lname }}</div>
                                <small class="text-muted">{{ $item->position->position_nameTH ?? '-' }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info text-dark">{{ $item->ratio }}%</span>
                        </td>
                        <td class="text-center">
                            @if(isset($item->id))
                            <form action="{{ route('backend.rdbprojectwork.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบข้อมูลนี้?')">
                                @csrf
                                @method('DELETE')
                                <div class="btn-group" role="group">
                                    <a href="{{ route('backend.rdbprojectwork.show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('backend.rdbprojectwork.edit', $item->id) }}" class="btn btn-sm btn-outline-warning" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
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