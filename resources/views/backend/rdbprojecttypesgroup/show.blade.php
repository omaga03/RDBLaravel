@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-page-header 
        title="รายละเอียดกลุ่มประเภทงบประมาณ" 
        icon="bi-collection-fill"
        :backRoute="route('backend.rdbprojecttypesgroup.index')"
        :editRoute="route('backend.rdbprojecttypesgroup.edit', $item->pttg_id)"
    />

    <div class="row justify-content-center">
        <div class="col-md-8">
            <x-card title="ข้อมูลกลุ่มประเภทโครงการ" icon="bi-info-circle" color="blue">
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        <div class="display-6 fw-bold mb-2">{{ $item->pttg_name }}</div>
                        <span class="badge bg-secondary fs-6 rounded-pill px-3 py-2">
                            รหัสอ้างอิง: {{ $item->pttg_id }}
                        </span>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <label class="col-md-4 text-end text-muted fw-bold">กลุ่มประเภทงบประมาณ:</label>
                    <div class="col-md-8">
                        @if(isset($groupList[$item->pttg_group]))
                            <span class="badge bg-primary fs-6">
                                <i class="bi bi-tag-fill me-1"></i> {{ $groupList[$item->pttg_group] }}
                            </span>
                        @else
                            <span class="text-muted">{{ $item->pttg_group ?? '-' }}</span>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-md-10">
             <x-system-info 
                :created_at="$item->created_at"
                :created_by="$item->user_created"
                :updated_at="$item->updated_at"
                :updated_by="$item->user_updated"
             />
        </div>
    </div>

    <div class="row justify-content-center mt-3">
        <div class="col-md-10">
             <x-action-buttons 
                :backRoute="route('backend.rdbprojecttypesgroup.index')"
                :editRoute="route('backend.rdbprojecttypesgroup.edit', $item->pttg_id)"
                :deleteRoute="route('backend.rdbprojecttypesgroup.destroy', $item->pttg_id)"
                :canDelete="$item->canDelete()"
            />
        </div>
    </div>
</div>
@endsection
