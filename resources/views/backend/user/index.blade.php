@extends('layouts.app')

@push('styles')
<style>
    /* Elegant Subtle Row Highlights for Onboarding Status */
    .user-row-pending {
        --bs-table-bg: rgba(245, 158, 11, 0.05);
        border-left: 3px solid #f59e0b !important;
    }
    [data-bs-theme="dark"] .user-row-pending {
        --bs-table-bg: rgba(245, 158, 11, 0.09);
        border-left: 3px solid #f59e0b !important;
    }
    .user-row-pending:hover {
        --bs-table-bg: rgba(245, 158, 11, 0.12) !important;
    }
    [data-bs-theme="dark"] .user-row-pending:hover {
        --bs-table-bg: rgba(245, 158, 11, 0.16) !important;
    }

    .user-researcher-card {
        background-color: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 8px;
    }
    .user-pending-card {
        background-color: rgba(245, 158, 11, 0.06);
        border: 1px solid rgba(245, 158, 11, 0.22);
        border-radius: 8px;
    }
    [data-bs-theme="dark"] .user-pending-card {
        background-color: rgba(245, 158, 11, 0.1);
        border: 1px solid rgba(245, 158, 11, 0.28);
    }

    /* TomSelect inside Bootstrap Modal */
    .modal .ts-wrapper {
        width: 100% !important;
    }
    .modal .ts-control {
        min-height: 38px;
        border-radius: var(--bs-border-radius);
    }
    .modal .ts-dropdown {
        z-index: 1070 !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>แดชบอร์ด</a></li>
                    <li class="breadcrumb-item active" aria-current="page">จัดการผู้ใช้งาน</li>
                </ol>
            </nav>
            <h2 class="mb-1 fw-bold text-body">
                <i class="bi bi-person-gear me-2 text-primary"></i>จัดการผู้ใช้งาน <span class="text-body-secondary small fw-normal">(User Management)</span>
            </h2>
            <p class="text-body-secondary mb-0">จัดการบัญชีผู้ใช้งาน ตรวจสอบคำขอยืนยันตัวตน และการเชื่อมโยงกับข้อมูลนักวิจัย</p>
        </div>
        <div>
            <a href="{{ route('backend.user.create') }}" class="btn btn-primary d-inline-flex align-items-center shadow-sm">
                <i class="bi bi-person-plus-fill me-2"></i> เพิ่มผู้ใช้งานใหม่
            </a>
        </div>
    </div>

    <!-- Quick Status Tabs -->
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <a href="{{ route('backend.user.index') }}" class="btn btn-sm {{ !request()->filled('onboarding_status') && !request()->filled('role') ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}">
            <i class="bi bi-people me-1"></i> ทั้งหมด
        </a>
        <a href="{{ route('backend.user.index', ['role' => 'admin_all']) }}" class="btn btn-sm {{ request('role') === 'admin_all' ? 'btn-danger text-white shadow-sm' : 'btn-outline-danger' }}">
            <i class="bi bi-shield-lock-fill me-1"></i> ระดับ Admin ทั้งหมด
            @if(isset($adminCount) && $adminCount > 0)
                <span class="badge {{ request('role') === 'admin_all' ? 'bg-white text-danger' : 'bg-danger text-white' }} rounded-pill ms-1">{{ $adminCount }}</span>
            @endif
        </a>
        <a href="{{ route('backend.user.index', ['onboarding_status' => 'pending']) }}" class="btn btn-sm {{ request('onboarding_status') === 'pending' ? 'btn-warning text-dark shadow-sm' : 'btn-outline-warning' }}">
            <i class="bi bi-hourglass-split me-1"></i> คำขอยืนยันตัวตนรอตรวจสอบ / ปลดล็อก
            @if($pendingCount > 0)
                <span class="badge bg-danger rounded-pill ms-1">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('backend.user.index', ['onboarding_status' => 'approved']) }}" class="btn btn-sm {{ request('onboarding_status') === 'approved' ? 'btn-success text-white shadow-sm' : 'btn-outline-success' }}">
            <i class="bi bi-check-circle me-1"></i> อนุมัติแล้ว
        </a>
        <a href="{{ route('backend.user.index', ['onboarding_status' => 'none']) }}" class="btn btn-sm {{ request('onboarding_status') === 'none' ? 'btn-secondary text-white shadow-sm' : 'btn-outline-secondary' }}">
            <i class="bi bi-question-circle me-1"></i> ยังไม่ส่งข้อมูล
        </a>
    </div>

    {{-- Search Bar --}}
    <x-search-bar :searchRoute="route('backend.user.index')" :collapsed="true">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold"><i class="bi bi-shield-check me-1 text-primary"></i>บทบาท (Role)</label>
                <select name="role" class="form-select">
                    <option value="">-- ทุกบทบาท --</option>
                    <option value="admin_all" {{ request('role') === 'admin_all' ? 'selected' : '' }}>
                        ★ กลุ่มระดับ Admin ทั้งหมด (All Admins)
                    </option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') == $r->name ? 'selected' : '' }}>
                            {{ $r->name }} {{ $r->description ? '('.$r->description.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold"><i class="bi bi-person-check me-1 text-warning"></i>สถานะการยืนยันตัวตน (Onboarding)</label>
                <select name="onboarding_status" class="form-select">
                    <option value="">-- ทุกสถานะยืนยัน --</option>
                    <option value="pending" {{ request('onboarding_status') == 'pending' ? 'selected' : '' }}>รอยืนยันและปลดล็อก (Pending)</option>
                    <option value="approved" {{ request('onboarding_status') == 'approved' ? 'selected' : '' }}>อนุมัติสิทธิ์นักวิจัยแล้ว (Approved)</option>
                    <option value="rejected" {{ request('onboarding_status') == 'rejected' ? 'selected' : '' }}>ไม่อนุมัติ / ปฏิเสธ (Rejected)</option>
                    <option value="none" {{ request('onboarding_status') == 'none' ? 'selected' : '' }}>ยังไม่ส่งข้อมูลยืนยันตัวตน (None)</option>
                </select>
            </div>
        </div>
    </x-search-bar>

    <div class="card shadow-sm border">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold text-body">
                <i class="bi bi-people me-2 text-primary"></i>รายชื่อผู้ใช้งาน
            </h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                <i class="bi bi-database me-1"></i> ทั้งหมด {{ number_format($users->total()) }} บัญชี
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;" class="text-center"><i class="bi bi-hash me-1 text-secondary"></i>ID</th>
                        <th><i class="bi bi-person me-1 text-primary"></i>ผู้ใช้งาน &amp; ข้อมูลนักวิจัย</th>
                        <th style="width: 170px;"><i class="bi bi-shield-check me-1 text-success"></i>บทบาท (Role)</th>
                        <th style="width: 160px;" class="text-center"><i class="bi bi-toggle2-on me-1 text-warning"></i>สถานะยืนยัน</th>
                        <th style="width: 180px;" class="text-center"><i class="bi bi-gear me-1 text-secondary"></i>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="{{ $user->onboarding_status === 'pending' ? 'user-row-pending' : '' }}">
                        <td class="text-center text-body-secondary"><code>{{ $user->id }}</code></td>
                        <td>
                            <!-- Account Identification -->
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-body fs-6">{{ $user->username }}</span>
                                @if($user->email_verified_at)
                                    <i class="bi bi-patch-check-fill text-primary small" title="อีเมลยืนยันแล้ว"></i>
                                @endif
                            </div>
                            <div class="text-body-secondary small mb-1">
                                <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                            </div>

                            <!-- Associated Researcher or Onboarding Request Information -->
                            @if($user->researcher)
                                <div class="user-researcher-card p-2 mt-2 small">
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-person-check-fill me-1"></i>นักวิจัยในระบบ
                                        </span>
                                        <a href="{{ route('backend.rdb_researcher.show', $user->researcher->researcher_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-semibold text-body">
                                            {{ $user->researcher->prefix->prefix_nameTH ?? '' }}{{ $user->researcher->researcher_fname }} {{ $user->researcher->researcher_lname }}
                                        </a>
                                    </div>
                                    <div class="text-body-secondary d-flex align-items-center gap-1">
                                        <i class="bi bi-building text-primary me-1"></i>
                                        <span>{{ $user->researcher->department->department_nameTH ?? '-' }}</span>
                                    </div>
                                </div>
                            @elseif($user->onboarding_status === 'pending')
                                <div class="user-pending-card p-2 mt-2 small">
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                            <i class="bi bi-hourglass-split me-1"></i>ข้อมูลที่ยื่นขอ
                                        </span>
                                        <span class="fw-bold text-body">
                                            {{ $user->onboardingPrefix?->prefix_nameTH ?? '' }}{{ $user->onboarding_fname }} {{ $user->onboarding_lname }}
                                        </span>
                                        @if($user->onboarding_fname_en || $user->onboarding_lname_en)
                                            <span class="text-body-secondary">({{ trim(($user->onboarding_fname_en ?? '') . ' ' . ($user->onboarding_lname_en ?? '')) }})</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-3 text-body-secondary">
                                        <span><i class="bi bi-building me-1 text-primary"></i>{{ $user->onboardingDepartment?->department_nameTH ?? 'ไม่ได้ระบุสังกัด' }}</span>
                                        @if($user->onboarding_tel)
                                            <span><i class="bi bi-telephone me-1 text-success"></i>{{ $user->onboarding_tel }}</span>
                                        @endif
                                        @if($user->onboarding_position)
                                            <span><i class="bi bi-briefcase me-1 text-info"></i>{{ $user->onboarding_position }}</span>
                                        @endif
                                    </div>
                                </div>
                            @elseif($user->onboarding_status === 'rejected' && $user->onboarding_reject_reason)
                                <div class="p-2 mt-2 rounded bg-danger-subtle border border-danger-subtle text-danger-emphasis small">
                                    <i class="bi bi-info-circle me-1"></i><strong>เหตุผลที่ปฏิเสธ:</strong> {{ $user->onboarding_reject_reason }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @forelse($user->roles as $role)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace me-1 mb-1">
                                    {{ $role->item_name }}
                                </span>
                            @empty
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">ไม่มีบทบาท</span>
                            @endforelse
                        </td>
                        <td class="text-center">
                            @if($user->onboarding_status === 'pending')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                    <i class="bi bi-hourglass-split me-1"></i>รอยืนยัน/ปลดล็อก
                                </span>
                            @elseif($user->onboarding_status === 'approved')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i>อนุมัติแล้ว
                                </span>
                            @elseif($user->onboarding_status === 'rejected')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" title="{{ $user->onboarding_reject_reason }}">
                                    <i class="bi bi-x-circle me-1"></i>ไม่อนุมัติ
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                    ยังไม่ส่งข้อมูล
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                                @if($user->onboarding_status === 'pending')
                                    <!-- Quick Approve & Unlock Button -->
                                    <button type="button" class="btn btn-sm btn-success text-nowrap d-inline-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $user->id }}" title="ตรวจสอบและปลดล็อกการใช้งาน">
                                        <i class="bi bi-unlock-fill me-1"></i>ปลดล็อก
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $user->id }}" title="ปฏิเสธคำขอ">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                @endif

                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('backend.user.show', $user->id) }}" class="btn btn-outline-secondary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('backend.user.edit', $user->id) }}" class="btn btn-outline-secondary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($user->id !== auth()->id() && !$user->hasRole('The Creator') && (auth()->user()?->hasRole('The Creator') || auth()->id() === 129))
                                        <form action="{{ route('backend.user.impersonate', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการเข้าสู่ระบบในฐานะ (Login As): {{ $user->username }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-info" title="สวมสิทธิ์เข้าใช้งาน (Login As: {{ $user->username }})">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($user->id !== auth()->id() && !$user->hasRole('The Creator'))
                                        <form action="{{ route('backend.user.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="ลบผู้ใช้งาน">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            @if($user->onboarding_status === 'pending')
                                <!-- Modal: Approve & Unlock -->
                                <div class="modal fade text-start" id="verifyModal{{ $user->id }}" tabindex="-1" aria-labelledby="verifyModalLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content shadow">
                                            <form action="{{ route('backend.user.approve_onboarding', $user->id) }}" method="POST" onsubmit="return validateApproveForm(this, {{ $user->id }});">
                                                @csrf
                                                <div class="modal-header bg-success-subtle text-success-emphasis">
                                                    <h6 class="modal-title fw-bold" id="verifyModalLabel{{ $user->id }}">
                                                        <i class="bi bi-shield-check me-1"></i> ยืนยันและปลดล็อกสิทธิ์นักวิจัย: {{ $user->username }}
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="bg-body-tertiary p-3 rounded-3 mb-3 border small">
                                                        <div class="fw-bold text-primary mb-2"><i class="bi bi-person-lines-fill me-1"></i> ข้อมูลที่ผู้ใช้ยื่นขอ:</div>
                                                        <div><strong>ชื่อ-สกุล (ไทย):</strong> {{ $user->onboardingPrefix?->prefix_nameTH }}{{ $user->onboarding_fname }} {{ $user->onboarding_lname }}</div>
                                                        @if($user->onboarding_fname_en || $user->onboarding_lname_en)
                                                            <div><strong>Name (EN):</strong> {{ $user->onboarding_fname_en }} {{ $user->onboarding_lname_en }}</div>
                                                        @endif
                                                        <div><strong>คณะ/สังกัด:</strong> <span class="text-primary fw-semibold">{{ $user->onboardingDepartment?->department_nameTH ?? 'ไม่ได้ระบุ' }}</span></div>
                                                        @if($user->onboarding_tel)
                                                            <div><strong>เบอร์โทร:</strong> {{ $user->onboarding_tel }}</div>
                                                        @endif
                                                        @if($user->onboarding_position)
                                                            <div><strong>ตำแหน่ง:</strong> {{ $user->onboarding_position }}</div>
                                                        @endif
                                                        <div><strong>อีเมล:</strong> {{ $user->email }}</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-body-secondary">รูปแบบการเชื่อมโยงข้อมูลนักวิจัย</label>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="radio" name="mode" id="mode_new_{{ $user->id }}" value="create_new" checked onchange="toggleResearcherMode({{ $user->id }}, 'create_new')">
                                                            <label class="form-check-label fw-semibold" for="mode_new_{{ $user->id }}">
                                                                สร้างข้อมูลนักวิจัยใหม่ตามข้อมูลที่ยื่นขอ (แนะนำ)
                                                            </label>
                                                            <div class="form-text small">ระบบจะสร้างโปรไฟล์นักวิจัยใหม่ในสังกัดที่เลือก เชื่อมโยงบัญชี และกำหนดบทบาท 'Researcher' ให้อัตโนมัติ</div>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="mode" id="mode_exist_{{ $user->id }}" value="link_existing" onchange="toggleResearcherMode({{ $user->id }}, 'link_existing')">
                                                            <label class="form-check-label fw-semibold" for="mode_exist_{{ $user->id }}">
                                                                เชื่อมโยงกับโปรไฟล์นักวิจัยเดิมที่มีอยู่แล้วในระบบ
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3" id="existingResearcherBox{{ $user->id }}" style="display: none;">
                                                        <label class="form-label small fw-semibold" for="existing_researcher_id_{{ $user->id }}">
                                                            <i class="bi bi-search me-1 text-primary"></i>เลือกนักวิจัยเดิมในระบบ <span class="text-danger">*</span>
                                                        </label>
                                                        <select name="existing_researcher_id" id="existing_researcher_id_{{ $user->id }}" class="form-select form-select-sm">
                                                            <option value="">-- พิมพ์ค้นหาชื่อนักวิจัย หรือคณะ/สังกัด --</option>
                                                            @foreach($researchers as $r)
                                                                <option value="{{ $r->researcher_id }}">
                                                                    {{ $r->prefix->prefix_nameTH ?? '' }}{{ $r->researcher_fname }} {{ $r->researcher_lname }} ({{ $r->department->department_nameTH ?? '-' }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="form-text small text-body-secondary mt-1">
                                                            <i class="bi bi-info-circle me-1"></i>สามารถพิมพ์ค้นหาได้ทั้งชื่อ, สกุล หรือคณะ/สังกัด
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-body-tertiary">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                    <button type="submit" class="btn btn-success shadow-sm">
                                                        <i class="bi bi-unlock-fill me-1"></i> ยืนยันและปลดล็อกการใช้งาน
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal: Reject -->
                                <div class="modal fade text-start" id="rejectModal{{ $user->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content shadow">
                                            <form action="{{ route('backend.user.reject_onboarding', $user->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-danger-subtle text-danger-emphasis">
                                                    <h6 class="modal-title fw-bold" id="rejectModalLabel{{ $user->id }}">
                                                        <i class="bi bi-x-circle me-1"></i> ปฏิเสธคำขอยืนยันตัวตน: {{ $user->username }}
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">ระบุเหตุผลการปฏิเสธคำขอ <span class="text-danger">*</span></label>
                                                        <textarea name="reason" class="form-control" rows="3" placeholder="เช่น ไม่พบบัญชีในรายชื่อบุคลากรของคณะ กรุณาระบุชื่อ-นามสกุลให้ตรงกับฐานข้อมูล" required></textarea>
                                                        <div class="form-text small">ข้อความนี้จะแสดงให้ผู้ใช้เห็น เพื่อให้ปรับปรุงข้อมูลและยื่นคำขอใหม่</div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-body-tertiary">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                    <button type="submit" class="btn btn-danger shadow-sm">
                                                        <i class="bi bi-x-circle-fill me-1"></i> ยืนยันปฏิเสธคำขอ
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-body-secondary">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            ไม่พบข้อมูลผู้ใช้งานตามเงื่อนไขที่เลือก
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer bg-body-tertiary py-3">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleResearcherMode(userId, mode) {
        const box = document.getElementById('existingResearcherBox' + userId);
        const selectEl = document.getElementById('existing_researcher_id_' + userId);
        if (!box || !selectEl) return;

        if (mode === 'link_existing') {
            box.style.display = 'block';
            if (!selectEl.tomselect) {
                new TomSelect(selectEl, {
                    placeholder: '-- พิมพ์ค้นหาชื่อนักวิจัย หรือคณะ/สังกัด --',
                    allowEmptyOption: true,
                    maxOptions: 1000,
                    plugins: ['clear_button'],
                    render: {
                        no_results: function(data, escape) {
                            return '<div class="no-results p-2 text-body-secondary small"><i class="bi bi-search me-1"></i>ไม่พบข้อมูลนักวิจัยที่ตรงกับ "' + escape(data.input) + '"</div>';
                        }
                    }
                });
            }
            // Auto focus the search field for smooth typing
            if (selectEl.tomselect) {
                setTimeout(() => {
                    selectEl.tomselect.focus();
                }, 100);
            }
        } else {
            box.style.display = 'none';
            if (selectEl.tomselect) {
                selectEl.tomselect.clear();
            }
        }
    }

    function validateApproveForm(form, userId) {
        const mode = form.querySelector('input[name="mode"]:checked')?.value;
        if (mode === 'link_existing') {
            const selectEl = document.getElementById('existing_researcher_id_' + userId);
            if (!selectEl || !selectEl.value) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'กรุณาเลือกนักวิจัย',
                        text: 'คุณได้เลือกรูปแบบ "เชื่อมโยงกับโปรไฟล์นักวิจัยเดิม" กรุณาพิมพ์ค้นหาและเลือกนักวิจัยในระบบก่อนยืนยัน',
                        confirmButtonText: 'ตกลง'
                    });
                } else {
                    alert('คุณได้เลือกรูปแบบ "เชื่อมโยงกับโปรไฟล์นักวิจัยเดิม" กรุณาพิมพ์ค้นหาและเลือกนักวิจัยในระบบก่อนยืนยัน');
                }
                return false;
            }
        }
        return true;
    }

    // Reset mode back to default when modal is closed
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.modal[id^="verifyModal"]').forEach(function(modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                const userId = this.id.replace('verifyModal', '');
                const radioNew = document.getElementById('mode_new_' + userId);
                if (radioNew) {
                    radioNew.checked = true;
                }
                toggleResearcherMode(userId, 'create_new');
            });
        });
    });
</script>
@endpush
