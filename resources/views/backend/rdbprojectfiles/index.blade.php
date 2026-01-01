@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-search-bar 
        title="จัดการไฟล์แนบโครงการ" 
        :searchRoute="route('backend.rdbprojectfiles.index')"
        :collapsed="true"
    >
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control" placeholder="ค้นหาตามชื่อไฟล์..." value="{{ request()->q }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> ค้นหา
                </button>
            </div>
        </div>
    </x-search-bar>

    <x-card>
        <!-- Note: Files are usually added via Project Show Page -->
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>ชื่อไฟล์</th>
                        <th>โครงการ</th>
                        <th class="text-center" style="width: 100px;">ดาวน์โหลด</th>
                        <th class="text-center" style="width: 150px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $item->rf_filesname }}</div>
                            <small class="text-muted">{{ \App\Helpers\ThaiDateHelper::format($item->created_at) }}</small>
                        </td>
                        <td>
                            @if($item->project)
                                <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" target="_blank" class="text-decoration-none">
                                    {{ Str::limit($item->project->pro_nameTH, 50) }}
                                </a>
                            @else
                                <span class="text-danger">ไม่พบโครงการ</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary rounded-pill">{{ $item->rf_download }} ครั้ง</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('backend.rdbprojectfiles.show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('backend.rdbprojectfiles.edit', $item->id) }}" class="btn btn-sm btn-outline-warning" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($item->canDelete())
                                <form action="{{ route('backend.rdbprojectfiles.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบไฟล์นี้?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
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