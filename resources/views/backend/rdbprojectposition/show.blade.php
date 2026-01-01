@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-page-header 
        title="รายละเอียดตำแหน่งในโครงการ"
        icon="bi-person-workspace"
        :backRoute="route('backend.rdbprojectposition.index')"
        :editRoute="route('backend.rdbprojectposition.edit', $item->position_id)"
        :deleteRoute="route('backend.rdbprojectposition.destroy', $item->position_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />

    <div class="row">
        <div class="col-lg-6">
            <x-card title="ข้อมูลตำแหน่ง" icon="bi-info-circle" color="primary">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 40%;">รหัส:</th>
                        <td><code>{{ $item->position_id }}</code></td>
                    </tr>
                    <tr>
                        <th>ชื่อตำแหน่ง:</th>
                        <td class="fw-bold fs-5">{{ $item->position_nameTH ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>คำอธิบาย:</th>
                        <td>{{ $item->position_desc ?? '-' }}</td>
                    </tr>
                </table>
            </x-card>
        </div>

        <div class="col-lg-6">
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
        :backRoute="route('backend.rdbprojectposition.index')"
        :editRoute="route('backend.rdbprojectposition.edit', $item->position_id)"
        :deleteRoute="route('backend.rdbprojectposition.destroy', $item->position_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />
</div>
@endsection