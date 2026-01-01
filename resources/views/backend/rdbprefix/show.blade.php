@extends('layouts.app')

@section('content')
<div class="py-4">
    {{-- Page Header with Actions --}}
    <x-page-header 
        title="รายละเอียดคำนำหน้า"
        icon="bi-person-badge"
        :backRoute="route('backend.rdbprefix.index')"
        :editRoute="route('backend.rdbprefix.edit', $item->prefix_id)"
        :deleteRoute="route('backend.rdbprefix.destroy', $item->prefix_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />

    <div class="row">
        {{-- Main Info --}}
        <div class="col-lg-6">
            <x-card title="ข้อมูลคำนำหน้า" icon="bi-info-circle" color="primary">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 40%;">รหัส (ID):</th>
                        <td><code>{{ $item->prefix_id }}</code></td>
                    </tr>
                    <tr>
                        <th>คำนำหน้า (ภาษาไทย):</th>
                        <td class="fw-bold fs-5">{{ $item->prefix_nameTH ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>คำนำหน้า (ตัวย่อ):</th>
                        <td>{{ $item->prefix_abbreviationTH ?? '-' }}</td>
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
        :backRoute="route('backend.rdbprefix.index')"
        :editRoute="route('backend.rdbprefix.edit', $item->prefix_id)"
        :deleteRoute="route('backend.rdbprefix.destroy', $item->prefix_id)"
        :canDelete="$item->canDelete()"
        :showPrint="false"
    />
</div>
@endsection