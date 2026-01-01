@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-page-header 
        title="รายละเอียดยุทธศาสตร์"
        icon="bi-bullseye"
        :backRoute="route('backend.rdbstrategic.index')"
        :editRoute="route('backend.rdbstrategic.edit', $item->strategic_id)"
        :deleteRoute="route('backend.rdbstrategic.destroy', $item->strategic_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />

    <div class="row">
        <div class="col-lg-6">
            <x-card title="ข้อมูลยุทธศาสตร์" icon="bi-info-circle" color="primary">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 40%;">รหัส:</th>
                        <td><code>{{ $item->strategic_id }}</code></td>
                    </tr>
                    <tr>
                        <th>ชื่อยุทธศาสตร์:</th>
                        <td class="fw-bold fs-5">{{ $item->strategic_nameTH ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>ปีงบประมาณ:</th>
                        <td>
                            @if($item->year)
                                <span class="badge bg-info">{{ $item->year->year_name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
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
        :backRoute="route('backend.rdbstrategic.index')"
        :editRoute="route('backend.rdbstrategic.edit', $item->strategic_id)"
        :deleteRoute="route('backend.rdbstrategic.destroy', $item->strategic_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />
</div>
@endsection