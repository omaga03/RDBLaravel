@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-page-header 
        title="รายละเอียดประเภทโครงการทุนย่อย"
        icon="bi-diagram-3"
        :backRoute="route('backend.rdbprojecttypesub.index')"
        :editRoute="route('backend.rdbprojecttypesub.edit', $item->pts_id)"
        :deleteRoute="route('backend.rdbprojecttypesub.destroy', $item->pts_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />

    <div class="row">
        <div class="col-lg-8">
            <x-card title="ข้อมูลโครงการทุนย่อย" icon="bi-info-circle" color="primary">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 30%;">รหัสอ้างอิง:</th>
                        <td><code>{{ $item->pts_id }}</code></td>
                    </tr>
                    <tr>
                        <th>ชื่อโครงการทุนย่อย:</th>
                        <td class="fw-bold fs-5">{{ $item->pts_name }}</td>
                    </tr>
                    <tr>
                        <th>ประเภททุนหลัก:</th>
                        <td>
                            <span class="fw-bold">{{ $item->projectType->pt_name ?? '-' }}</span>
                            @if(isset($item->projectType->year->year_name))
                                <span class="badge bg-info ms-2">
                                    <i class="bi bi-calendar-event me-1"></i> {{ $item->projectType->year->year_name }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>ไฟล์แนบ:</th>
                        <td>
                            @if($item->pts_file)
                                <a href="{{ asset('storage/uploads/project_types_sub/' . $item->pts_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark-text me-1"></i> ดาวน์โหลดไฟล์
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </x-card>
        </div>

        <div class="col-lg-4">
            @php
                $createdBy = $item->user_created ? \App\Models\User::find($item->user_created) : null;
                $updatedBy = $item->user_updated ? \App\Models\User::find($item->user_updated) : null;
                $createdByName = $createdBy?->researcher ? ($createdBy->researcher->researcher_fname . ' ' . $createdBy->researcher->researcher_lname) : ($createdBy?->username ?? '-');
                $updatedByName = $updatedBy?->researcher ? ($updatedBy->researcher->researcher_fname . ' ' . $updatedBy->researcher->researcher_lname) : ($updatedBy?->username ?? '-');
            @endphp
            <x-system-info :created_at="$item->created_at" :created_by="$createdByName" :updated_at="$item->updated_at" :updated_by="$updatedByName" />
        </div>
    </div>

    <x-action-buttons 
        :backRoute="route('backend.rdbprojecttypesub.index')"
        :editRoute="route('backend.rdbprojecttypesub.edit', $item->pts_id)"
        :deleteRoute="route('backend.rdbprojecttypesub.destroy', $item->pts_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />
</div>
@endsection
