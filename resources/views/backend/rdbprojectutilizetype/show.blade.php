@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-page-header 
        title="รายละเอียดประเภทการนำไปใช้ประโยชน์"
        icon="bi-rocket-takeoff"
        :backRoute="route('backend.rdbprojectutilizetype.index')"
        :editRoute="route('backend.rdbprojectutilizetype.edit', $item->utz_type_id)"
        :deleteRoute="route('backend.rdbprojectutilizetype.destroy', $item->utz_type_id)"
        :showPrint="true"
    />

    <div class="row">
        <div class="col-lg-6">
            <x-card title="ข้อมูลประเภท" icon="bi-info-circle" color="blue">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 40%;">รหัสประเภท:</th>
                        <td><code>{{ $item->utz_type_id }}</code></td>
                    </tr>
                    <tr>
                        <th>ชื่อประเภท:</th>
                        <td class="fw-bold fs-5">{{ $item->utz_typr_name }}</td>
                    </tr>
                    <tr>
                        <th>ลำดับการแสดงผล:</th>
                        <td>{{ $item->utz_type_index ?? '-' }}</td>
                    </tr>
                </table>
            </x-card>
        </div>

        <div class="col-lg-6">
            <x-system-info :created_at="$item->created_at" :updated_at="$item->updated_at" />
        </div>
    </div>

    <x-action-buttons 
        :backRoute="route('backend.rdbprojectutilizetype.index')"
        :editRoute="route('backend.rdbprojectutilizetype.edit', $item->utz_type_id)"
        :deleteRoute="route('backend.rdbprojectutilizetype.destroy', $item->utz_type_id)"
        :showPrint="true"
    />
</div>
@endsection
