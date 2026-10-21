@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <x-page-header 
        title="รายละเอียดผู้ใช้งาน: {{ $user->username }}" 
        icon="bi-person-badge"
        :backRoute="route('backend.user.index')" 
        :editRoute="auth()->user()->can('update', $user) ? route('backend.user.edit', $user->id) : null" 
        :deleteRoute="auth()->user()->can('delete', $user) ? route('backend.user.destroy', $user->id) : null" 
        :canDelete="auth()->user()->can('delete', $user) && $user->id !== auth()->id() && !$user->hasRole('The Creator')" 
        :showPrint="true"
    />

    <div class="row g-4">
        <div class="col-lg-6">
            <x-card title="ข้อมูลบัญชีผู้ใช้งาน" icon="bi-person-circle" color="primary">
                <div class="table-responsive">
<table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th style="width: 35%;">รหัสผู้ใช้ (User ID):</th>
                        <td><code>{{ $user->id }}</code></td>
                    </tr>
                    <tr>
                        <th>ชื่อผู้ใช้ (Username):</th>
                        <td class="fw-bold fs-5 text-primary">{{ $user->username }}</td>
                    </tr>
                    <tr>
                        <th>อีเมล:</th>
                        <td><a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a></td>
                    </tr>
                    <tr>
                        <th>บทบาทในระบบ (Role):</th>
                        <td>
                            @forelse($user->roles as $role)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fs-6">
                                    {{ $role->item_name }}
                                </span>
                            @empty
                                <span class="text-body-secondary">-</span>
                            @endforelse
                        </td>
                    </tr>
                    <tr>
                        <th>สถานะบัญชี:</th>
                        <td>
                            @if($user->status == 10)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>ใช้งานได้ (Active)
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-x-circle me-1"></i>ระงับการใช้งาน (Blocked)
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>
</div>
            </x-card>
        </div>

        <div class="col-lg-6">
            <x-card title="ข้อมูลนักวิจัยที่เชื่อมโยง" icon="bi-person-vcard" color="info">
                @if($user->researcher)
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            @if($user->researcher->profile_image_url)
                                <img src="{{ $user->researcher->profile_image_url }}" 
                                     alt="{{ $user->researcher->researcher_fname }}" 
                                     class="rounded-circle object-fit-cover shadow-sm border"
                                     style="width: 60px; height: 60px;">
                            @else
                                <div class="rounded-circle bg-secondary-subtle text-secondary-emphasis d-flex align-items-center justify-content-center shadow-sm" 
                                     style="width: 60px; height: 60px; font-size: 1.5rem;">
                                    {{ strtoupper(substr($user->researcher->researcher_fnameEN ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">
                                {{ $user->researcher->prefix->prefix_nameTH ?? '' }}{{ $user->researcher->researcher_fname }} {{ $user->researcher->researcher_lname }}
                            </h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                {{ $user->researcher->department->department_nameTH ?? '-' }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('backend.rdb_researcher.show', $user->researcher->researcher_id) }}" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-box-arrow-up-right me-1"></i> ดูประวัตินักวิจัยฉบับเต็ม
                        </a>
                    </div>
                @else
                    <div class="text-center py-4 text-body-secondary">
                        <i class="bi bi-person-x fs-1 d-block mb-2 opacity-50"></i>
                        บัญชีผู้ใช้นี้ยังไม่ได้เชื่อมโยงกับข้อมูลนักวิจัย
                    </div>
                @endif
            </x-card>

            <div class="mt-4">
                @php
                    $createdBy = $user->user_created ? \App\Models\User::find($user->user_created) : null;
                    $updatedBy = $user->user_updated ? \App\Models\User::find($user->user_updated) : null;
                    $createdByName = $createdBy?->username ?? '-';
                    $updatedByName = $updatedBy?->username ?? '-';
                @endphp
                <x-system-info :created_at="$user->created_at" :created_by="$createdByName" :updated_at="$user->updated_at" :updated_by="$updatedByName" />
            </div>
        </div>
    </div>
</div>
@endsection
