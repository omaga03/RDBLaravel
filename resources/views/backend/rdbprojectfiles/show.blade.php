@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-page-header 
        title="รายละเอียดไฟล์แนบ" 
        icon="bi-file-earmark"
        :backRoute="route('backend.rdbprojectfiles.index')"
        :editRoute="route('backend.rdbprojectfiles.edit', $item->id)"
    />

    <div class="row">
        <div class="col-lg-8">
            <x-card title="ข้อมูลไฟล์" icon="bi-info-circle" color="blue">
                <div class="row mb-3">
                    <label class="col-md-3 text-end text-muted fw-bold">ID:</label>
                    <div class="col-md-9"><code>{{ $item->id }}</code></div>
                </div>
                <div class="row mb-3">
                    <label class="col-md-3 text-end text-muted fw-bold">ชื่อไฟล์:</label>
                    <div class="col-md-9 fw-bold text-primary">{{ $item->rf_filesname }}</div>
                </div>
                <div class="row mb-3">
                    <label class="col-md-3 text-end text-muted fw-bold">โครงการ:</label>
                    <div class="col-md-9">
                        @if($item->project)
                            <a href="{{ route('backend.rdb_project.show', $item->project->pro_id) }}" target="_blank" class="text-decoration-none">
                                {{ $item->project->pro_nameTH }}
                            </a>
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-md-3 text-end text-muted fw-bold">ดาวน์โหลด:</label>
                    <div class="col-md-9"><span class="badge bg-secondary rounded-pill">{{ $item->rf_download }} ครั้ง</span></div>
                </div>
                <div class="row mb-3">
                    <label class="col-md-3 text-end text-muted fw-bold">สถานะการแสดงผล:</label>
                    <div class="col-md-9">
                        @if($item->rf_files_show == 1)
                            <span class="badge bg-success">แสดง</span>
                        @else
                            <span class="badge bg-secondary">ซ่อน</span>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    
    <div class="mt-4">
        <x-action-buttons 
            :backRoute="route('backend.rdbprojectfiles.index')"
            :editRoute="route('backend.rdbprojectfiles.edit', $item->id)"
            :deleteRoute="route('backend.rdbprojectfiles.destroy', $item->id)"
        />
    </div>
</div>
@endsection