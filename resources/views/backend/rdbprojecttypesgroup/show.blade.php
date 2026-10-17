@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-page-header 
        title="รายละเอียดกลุ่มประเภทงบประมาณ" 
        icon="bi-collection-fill"
        :backRoute="route('backend.rdbprojecttypesgroup.index')"
        :editRoute="route('backend.rdbprojecttypesgroup.edit', $item->pttg_id)"
    />

    <div class="row">
        <div class="col-lg-6">
            <x-card title="ข้อมูลกลุ่มประเภทโครงการ" icon="bi-info-circle" color="primary">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 40%;">รหัสอ้างอิง:</th>
                        <td><code>{{ $item->pttg_id }}</code></td>
                    </tr>
                    <tr>
                        <th>ชื่อกลุ่มประเภท:</th>
                        <td class="fw-bold fs-5 text-primary">{{ $item->pttg_name }}</td>
                    </tr>
                    <tr>
                        <th>กลุ่มประเภทงบประมาณ:</th>
                        <td>
                            @if(isset($groupList[$item->pttg_group]))
                                <span class="badge bg-primary">
                                    <i class="bi bi-tag-fill me-1"></i> {{ $groupList[$item->pttg_group] }}
                                </span>
                            @else
                                <span class="text-muted">{{ $item->pttg_group ?? '-' }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </x-card>
        </div>

        <div class="col-lg-6">
             <x-system-info 
                :created_at="$item->created_at"
                :created_by="$item->user_created"
                :updated_at="$item->updated_at"
                :updated_by="$item->user_updated"
             />
        </div>
    </div>

    <div class="mt-4">
         <x-action-buttons 
            :backRoute="route('backend.rdbprojecttypesgroup.index')"
            :editRoute="route('backend.rdbprojecttypesgroup.edit', $item->pttg_id)"
            :deleteRoute="route('backend.rdbprojecttypesgroup.destroy', $item->pttg_id)"
            :canDelete="$item->canDelete()"
        />
    </div>
</div>
@endsection
