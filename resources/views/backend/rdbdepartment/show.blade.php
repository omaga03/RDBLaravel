@extends('layouts.app')

@section('content')
<div class="py-4">
    <x-page-header 
        title="รายละเอียดหน่วยงาน"
        icon="bi-building"
        :backRoute="route('backend.rdbdepartment.index')"
        :editRoute="route('backend.rdbdepartment.edit', $item->department_id)"
        :deleteRoute="route('backend.rdbdepartment.destroy', $item->department_id)"
        :canDelete="$item->canDelete()"
        :showPrint="true"
    />

    <div class="row">
        <div class="col-lg-8">
            <x-card title="ข้อมูลหน่วยงาน" icon="bi-info-circle" color="blue">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 30%;">รหัสหน่วยงาน:</th>
                        <td><code>{{ $item->department_code }}</code></td>
                    </tr>
                    <tr>
                        <th>ชื่อหน่วยงาน (ไทย):</th>
                        <td class="fw-bold fs-5">{{ $item->department_nameTH }}</td>
                    </tr>
                    <tr>
                        <th>ชื่อหน่วยงาน (อังกฤษ):</th>
                        <td>{{ $item->department_nameEN ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>ประเภท:</th>
                        <td>
                            @if($item->departmentType)
                                <span class="badge bg-info text-dark">{{ $item->departmentType->tdepartment_nameTH }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>สีประจำหน่วยงาน:</th>
                        <td>
                            @if($item->department_color)
                                <span class="badge" style="background-color: {{ $item->department_color }}; color: #fff; text-shadow: 1px 1px 1px #000;">
                                    {{ $item->department_color }}
                                </span>
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
        :backRoute="route('backend.rdbdepartment.index')"
        :editRoute="route('backend.rdbdepartment.edit', $item->department_id)"
        :deleteRoute="route('backend.rdbdepartment.destroy', $item->department_id)"
        :canDelete="$item->canDelete()"
        :showPrint="true"
    />
</div>
@endsection