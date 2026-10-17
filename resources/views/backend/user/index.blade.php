@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1 fw-bold text-body">
                <i class="bi bi-people-gear me-2 text-primary"></i>จัดการผู้ใช้งาน (User Management)
            </h2>
            <p class="text-body-secondary mb-0">จัดการบัญชีผู้ใช้งาน สิทธิ์การเข้าถึง และการเชื่อมโยงกับข้อมูลนักวิจัย</p>
        </div>
        <div>
            <a href="{{ route('backend.user.create') }}" class="btn btn-primary d-inline-flex align-items-center shadow-sm">
                <i class="bi bi-person-plus-fill me-2"></i> เพิ่มผู้ใช้งานใหม่
            </a>
        </div>
    </div>

    {{-- Search Bar --}}
    <x-search-bar :searchRoute="route('backend.user.index')" :collapsed="request()->hasAny(['search', 'role']) ? false : true">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">บทบาท (Role)</label>
                <select name="role" class="form-select">
                    <option value="">-- ทุกบทบาท --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') == $r->name ? 'selected' : '' }}>
                            {{ $r->name }} {{ $r->description ? '('.$r->description.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold text-body">
                <i class="bi bi-people me-2"></i>รายชื่อผู้ใช้งานที่มีบทบาทในระบบ
            </h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                ทั้งหมด {{ number_format($users->total()) }} บัญชี
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th style="width: 25%;">ชื่อผู้ใช้ / อีเมล</th>
                        <th style="width: 20%;">บทบาท (Role)</th>
                        <th>นักวิจัยที่เชื่อมโยง</th>
                        <th style="width: 120px;" class="text-center">สถานะ</th>
                        <th style="width: 140px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td><code>{{ $user->id }}</code></td>
                        <td>
                            <div class="fw-bold text-body">{{ $user->username }}</div>
                            <small class="text-body-secondary"><i class="bi bi-envelope me-1"></i>{{ $user->email }}</small>
                        </td>
                        <td>
                            @forelse($user->roles as $role)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace me-1">
                                    {{ $role->item_name }}
                                </span>
                            @empty
                                <span class="badge bg-secondary-subtle text-secondary">ไม่มีบทบาท</span>
                            @endforelse
                        </td>
                        <td>
                            @if($user->researcher)
                                <a href="{{ route('backend.rdb_researcher.show', $user->researcher->researcher_id) }}" class="text-decoration-none text-body fw-semibold">
                                    <i class="bi bi-person me-1"></i>{{ $user->researcher->prefix->prefix_nameTH ?? '' }}{{ $user->researcher->researcher_fname }} {{ $user->researcher->researcher_lname }}
                                </a>
                                <div class="small text-body-secondary">{{ $user->researcher->department->department_nameTH ?? '-' }}</div>
                            @else
                                <span class="text-body-secondary">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($user->status == 10)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>ใช้งานได้
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-x-circle me-1"></i>ระงับการใช้งาน
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('backend.user.show', $user->id) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('backend.user.edit', $user->id) }}" class="btn btn-outline-warning" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-body-secondary">
                            <i class="bi bi-people fs-1 d-block mb-3 opacity-50"></i>
                            ไม่พบข้อมูลผู้ใช้งาน
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer bg-transparent py-3">
            {{ $users->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
