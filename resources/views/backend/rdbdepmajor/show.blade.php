@extends('layouts.app')

@section('content')
<div class="py-4">
    {{-- Page Header with Actions --}}
    <x-page-header 
        title="รายละเอียดสาขาวิชา"
        icon="bi-mortarboard"
        :backRoute="route('backend.rdbdepmajor.index')"
        :editRoute="route('backend.rdbdepmajor.edit', $item->maj_code)"
        :deleteRoute="route('backend.rdbdepmajor.destroy', $item->maj_code)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />

    <div class="row">
        {{-- Main Info --}}
        <div class="col-lg-6">
            <x-card title="ข้อมูลสาขาวิชา" icon="bi-info-circle" color="primary">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 40%;">รหัสสาขา:</th>
                        <td><code>{{ $item->maj_id ?? $item->maj_code }}</code></td>
                    </tr>
                    <tr>
                        <th>ชื่อ (ภาษาไทย):</th>
                        <td class="fw-bold fs-5">{{ $item->maj_nameTH ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>ชื่อ (ภาษาอังกฤษ):</th>
                        <td>{{ $item->maj_nameEN ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>หน่วยงาน/คณะ:</th>
                        <td>
                            @if($item->department)
                                <span class="badge bg-secondary">{{ $item->department->department_nameTH }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </x-card>
        </div>

        {{-- System Info --}}
        <div class="col-lg-6">
            @php
                $createdBy = $item->user_created ? \App\Models\User::find($item->user_created) : null;
                $updatedBy = $item->user_updated ? \App\Models\User::find($item->user_updated) : null;
                $createdByName = $createdBy?->researcher ? ($createdBy->researcher->researcher_fname . ' ' . $createdBy->researcher->researcher_lname) : ($createdBy?->username ?? '-');
                $updatedByName = $updatedBy?->researcher ? ($updatedBy->researcher->researcher_fname . ' ' . $updatedBy->researcher->researcher_lname) : ($updatedBy?->username ?? '-');
            @endphp
            <x-system-info 
                :created_at="$item->created_at"
                :created_by="$createdByName"
                :updated_at="$item->updated_at"
                :updated_by="$updatedByName"
            />
        </div>
    </div>

    {{-- Bottom Action Buttons --}}
    <x-action-buttons 
        :backRoute="route('backend.rdbdepmajor.index')"
        :editRoute="route('backend.rdbdepmajor.edit', $item->maj_code)"
        :deleteRoute="route('backend.rdbdepmajor.destroy', $item->maj_code)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />
</div>
@endsection