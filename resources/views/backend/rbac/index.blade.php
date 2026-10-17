@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-primary">
                <i class="bi bi-shield-lock me-2"></i>การจัดการบทบาท (Role Management)
            </h4>
            <div class="text-muted small">กำหนดสิทธิ์และบทบาทผู้ใช้งานในระบบ</div>
        </div>
        <a href="{{ route('backend.rbac.create') }}" class="btn btn-success d-inline-flex align-items-center shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> เพิ่มบทบาทใหม่
        </a>
    </div>

    <div class="card shadow-sm border">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-list-check me-2"></i>รายการบทบาท (Roles)
            </h6>
            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">{{ count($roles) }} บทบาท</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 20%;">ชื่อบทบาท (Role Name)</th>
                        <th>คำอธิบาย (Description)</th>
                        <th style="width: 220px;">สิทธิ์การใช้งาน (Permissions)</th>
                        <th style="width: 220px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle fs-6 font-monospace">
                                    {{ $role->name }}
                                </span>
                            </td>
                            <td>
                                <span class="text-body">{{ $role->description ?? '-' }}</span>
                            </td>
                            <td>
                                @if($role->name === 'The Creator')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="bi bi-shield-shaded me-1"></i>ทุกสิทธิ์ในระบบ (Superadmin)
                                    </span>
                                @else
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                        <i class="bi bi-key-fill me-1"></i>{{ $role->children_count }} สิทธิ์ที่กำหนด
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('backend.rbac.permissions', $role->name) }}" class="btn btn-outline-primary" title="กำหนดสิทธิ์ (Permission Matrix)">
                                        <i class="bi bi-key-fill me-1"></i>กำหนดสิทธิ์
                                    </a>
                                    <a href="{{ route('backend.rbac.edit', $role->name) }}" class="btn btn-outline-warning" title="แก้ไขข้อมูล">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('backend.rbac.destroy', $role->name) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="ลบ">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">ไม่พบข้อมูลบทบาท</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
