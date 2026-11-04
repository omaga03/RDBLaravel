@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 px-3 px-md-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แผงควบคุมระบบ</a></li>
            @if($canManageRoles)
                <li class="breadcrumb-item"><a href="{{ route('backend.user.index') }}" class="text-decoration-none">จัดการผู้ใช้งาน</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $user->id === auth()->id() ? 'จัดการข้อมูลส่วนตัว & การแจ้งเตือน' : 'แก้ไขผู้ใช้: ' . $user->username }}</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2.5">
            <div class="p-2.5 rounded-3 bg-primary-subtle text-primary border border-primary-subtle">
                <i class="bi bi-person-gear fs-3"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-body">
                    {{ $user->id === auth()->id() ? 'จัดการข้อมูลส่วนตัวและการตั้งค่าการแจ้งเตือน (Profile & Notifications)' : 'แก้ไขข้อมูลผู้ใช้งาน: ' . $user->username }}
                </h4>
                <p class="text-body-secondary small mb-0 mt-0.5">
                    ปรับปรุงข้อมูลบัญชี รหัสผ่าน และเลือกช่องทางการรับแจ้งเตือนผ่าน Email และ Telegram Bot
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($canManageRoles)
                <a href="{{ route('backend.user.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm">
                    <i class="bi bi-arrow-left"></i> กลับหน้ารายการผู้ใช้
                </a>
            @else
                <a href="{{ route('backend.site.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm">
                    <i class="bi bi-arrow-left"></i> กลับแดชบอร์ด
                </a>
            @endif
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2.5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2.5 text-warning"></i>
            <div>{{ session('warning') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm mb-4" role="alert">
            <i class="bi bi-x-circle-fill fs-5 me-2.5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('backend.user.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column: User Account & Identity Credentials -->
            <div class="col-lg-6">
                <div class="card border shadow-sm bg-body h-100">
                    <div class="card-header bg-body-tertiary border-bottom py-3 d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge-fill text-primary fs-5"></i>
                        <h5 class="card-title fw-bold text-body mb-0">ข้อมูลบัญชีผู้ใช้งาน (Account Credentials)</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="username" class="form-label fw-bold">ชื่อผู้ใช้ (Username) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label fw-bold">อีเมล (Email) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="submitTestMail()" title="ส่งอีเมลทดสอบ">
                                        <i class="bi bi-send me-1"></i>ทดสอบส่งเมล
                                    </button>
                                </div>
                                <span class="text-body-secondary small d-block mt-1" style="font-size: 0.76rem;">ใช้สำหรับการแจ้งเตือนงานวิจัยและรหัส OTP กู้คืนระบบ</span>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="password" class="form-label fw-bold">รหัสผ่านใหม่ (ปล่อยว่างหากไม่ต้องการเปลี่ยน)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="กรอกเฉพาะเมื่อต้องการตั้งรหัสผ่านใหม่">
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">บทบาท (Role) <span class="text-danger">*</span></label>
                                @if($canManageRoles)
                                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                        <option value="">-- ค้นหาและเลือกบทบาท --</option>
                                        @foreach($roles as $r)
                                            <option value="{{ $r->name }}" {{ old('role', $userRole) == $r->name ? 'selected' : '' }}>
                                                {{ $r->name }} {{ $r->description ? '('.$r->description.')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @else
                                    <div class="form-control bg-body-tertiary d-flex align-items-center justify-content-between">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">
                                            <i class="bi bi-shield-lock me-1"></i>{{ $userRole ?? 'นักวิจัย' }}
                                        </span>
                                        <small class="text-body-secondary"><i class="bi bi-lock-fill me-1"></i>เฉพาะผู้ดูแลระบบ</small>
                                    </div>
                                @endif
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">เชื่อมโยงข้อมูลนักวิจัย (Linked Researcher)</label>
                                @if($canManageRoles)
                                    <select name="researcher_id" id="researcher_id" class="form-select @error('researcher_id') is-invalid @enderror">
                                        <option value="">-- ไม่ระบุ / ไม่ใช่นักวิจัย --</option>
                                        @foreach($researchers as $res)
                                            <option value="{{ $res->researcher_id }}" {{ old('researcher_id', $user->researcher_id) == $res->researcher_id ? 'selected' : '' }}>
                                                {{ $res->prefix->prefix_nameTH ?? '' }}{{ $res->researcher_fname }} {{ $res->researcher_lname }} ({{ $res->department->department_nameTH ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('researcher_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @else
                                    <div class="form-control bg-body-tertiary d-flex align-items-center justify-content-between">
                                        <span>
                                            @if($user->researcher)
                                                <i class="bi bi-person-check text-success me-1"></i>{{ $user->researcher->full_name }} ({{ $user->researcher->department->department_nameTH ?? '-' }})
                                            @else
                                                <span class="text-body-secondary">ไม่ได้เชื่อมโยง</span>
                                            @endif
                                        </span>
                                        <small class="text-body-secondary"><i class="bi bi-lock-fill me-1"></i>เฉพาะผู้ดูแลระบบ</small>
                                    </div>
                                @endif
                            </div>

                            @if($canManageRoles)
                            <div class="col-md-6">
                                <label class="form-label fw-bold">สถานะการใช้งาน <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="10" {{ old('status', $user->status) == 10 ? 'selected' : '' }}>ใช้งานได้ (Active)</option>
                                    <option value="0" {{ old('status', $user->status) == 0 ? 'selected' : '' }}>ระงับการใช้งาน (Blocked)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">สถานะการยืนยันตัวตน</label>
                                <select name="onboarding_status" id="onboarding_status" class="form-select @error('onboarding_status') is-invalid @enderror">
                                    <option value="approved" {{ old('onboarding_status', $user->onboarding_status) == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว (Approved)</option>
                                    <option value="pending" {{ old('onboarding_status', $user->onboarding_status) == 'pending' ? 'selected' : '' }}>รอยืนยัน (Pending)</option>
                                    <option value="rejected" {{ old('onboarding_status', $user->onboarding_status) == 'rejected' ? 'selected' : '' }}>ไม่อนุมัติ (Rejected)</option>
                                    <option value="none" {{ old('onboarding_status', $user->onboarding_status) == 'none' ? 'selected' : '' }}>ยังไม่ส่งข้อมูล (None)</option>
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Personal Notification Preferences (Mail & Telegram) -->
            <div class="col-lg-6" id="notification-settings">
                <div class="card border shadow-sm bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-bell-fill text-warning fs-5"></i>
                            <h5 class="card-title fw-bold text-body mb-0">การแจ้งเตือนส่วนบุคคล (Personal Notifications)</h5>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            Dual-Channel
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <!-- Telegram Chat ID Section -->
                        <div class="mb-3.5">
                            <label for="telegram_chat_id" class="form-label fw-bold">
                                <i class="bi bi-telegram text-info me-1"></i>Telegram Chat ID สำหรับรับแจ้งเตือนส่วนตัว
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary"><i class="bi bi-chat-dots-fill"></i></span>
                                <input type="text" class="form-control font-monospace @error('telegram_chat_id') is-invalid @enderror" id="telegram_chat_id" name="telegram_chat_id" value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}" placeholder="เช่น 123456789">
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="submitTestTelegram()" title="ทดสอบส่งเข้า Telegram ทันที">
                                    <i class="bi bi-send me-1"></i>ทดสอบส่ง Telegram
                                </button>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1 flex-wrap gap-1">
                                <span class="text-body-secondary small" style="font-size: 0.76rem;">
                                    วิธีหา ID: เปิด Telegram ค้นหา <strong>@userinfobot</strong> แล้วกด Start บอทจะแจ้งหมายเลข Id ของคุณ
                                </span>
                            </div>
                            @error('telegram_chat_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Channel Toggles -->
                        <label class="form-label fw-bold text-body mb-2">เลือกเปิด/ปิดช่องทางการแจ้งเตือน:</label>
                        <div class="row g-2 mb-3.5">
                            <div class="col-sm-6">
                                <div class="form-check form-switch p-3 rounded-2 border bg-body-tertiary d-flex align-items-center justify-content-between">
                                    <div>
                                        <label class="form-check-label fw-semibold text-body d-block" for="pref_mail_enabled">
                                            <i class="bi bi-envelope-check text-primary me-1"></i>อีเมล (Email)
                                        </label>
                                        <small class="text-body-secondary" style="font-size: 0.72rem;">ส่งเข้า {{ $user->email ?: 'อีเมลของคุณ' }}</small>
                                    </div>
                                    <input class="form-check-input ms-2 fs-5" type="checkbox" role="switch" id="pref_mail_enabled" name="pref_mail_enabled" value="1" {{ old('pref_mail_enabled', $userPrefs['mail_enabled'] ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check form-switch p-3 rounded-2 border bg-body-tertiary d-flex align-items-center justify-content-between">
                                    <div>
                                        <label class="form-check-label fw-semibold text-body d-block" for="pref_telegram_enabled">
                                            <i class="bi bi-telegram text-info me-1"></i>Telegram
                                        </label>
                                        <small class="text-body-secondary" style="font-size: 0.72rem;">ส่งเข้า Telegram Chat ID</small>
                                    </div>
                                    <input class="form-check-input ms-2 fs-5" type="checkbox" role="switch" id="pref_telegram_enabled" name="pref_telegram_enabled" value="1" {{ old('pref_telegram_enabled', $userPrefs['telegram_enabled'] ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- Topics Subscription List -->
                        <label class="form-label fw-bold text-body mb-2">เลือกหัวข้อการแจ้งเตือนที่ต้องการรับ:</label>
                        <div class="d-flex flex-column gap-2 mb-2">
                            <div class="form-check p-2.5 rounded-2 border bg-body">
                                <input class="form-check-input ms-0 me-2.5" type="checkbox" id="pref_topic_research_approved" name="pref_topic_research_approved" value="1" {{ old('pref_topic_research_approved', $userPrefs['topics']['research_approved'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label text-body small fw-medium" for="pref_topic_research_approved">
                                    🎉 แจ้งเตือนเมื่อผลงานวิจัยได้รับการอนุมัติเผยแพร่สู่ระบบ
                                </label>
                            </div>
                            <div class="form-check p-2.5 rounded-2 border bg-body">
                                <input class="form-check-input ms-0 me-2.5" type="checkbox" id="pref_topic_research_rejected" name="pref_topic_research_rejected" value="1" {{ old('pref_topic_research_rejected', $userPrefs['topics']['research_rejected'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label text-body small fw-medium" for="pref_topic_research_rejected">
                                    ⚠️ แจ้งเตือนเมื่อผลงานวิจัยมีข้อเสนอแนะให้ปรับปรุงแก้ไข (Revision Required)
                                </label>
                            </div>
                            <div class="form-check p-2.5 rounded-2 border bg-body">
                                <input class="form-check-input ms-0 me-2.5" type="checkbox" id="pref_topic_review_assigned" name="pref_topic_review_assigned" value="1" {{ old('pref_topic_review_assigned', $userPrefs['topics']['review_assigned'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label text-body small fw-medium" for="pref_topic_review_assigned">
                                    📋 แจ้งเตือนเมื่อได้รับมอบหมายงานประเมินเล่มรายงานวิจัย (ผู้ทรงคุณวุฒิ)
                                </label>
                            </div>
                            <div class="form-check p-2.5 rounded-2 border bg-body">
                                <input class="form-check-input ms-0 me-2.5" type="checkbox" id="pref_topic_review_final_approved" name="pref_topic_review_final_approved" value="1" {{ old('pref_topic_review_final_approved', $userPrefs['topics']['review_final_approved'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label text-body small fw-medium" for="pref_topic_review_final_approved">
                                    ✅ แจ้งเตือนเมื่อเล่มรายงานวิจัยฉบับสมบูรณ์ผ่านการอนุมัติรอบสุดท้าย
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Only: Global Telegram Bot & System Event Subscriptions -->
                @if($isSuperAdmin)
                <div class="card border border-warning-subtle shadow-sm bg-body">
                    <div class="card-header bg-warning-subtle border-bottom border-warning-subtle py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill text-warning fs-5"></i>
                            <h5 class="card-title fw-bold text-body mb-0">ระบบแจ้งเตือนส่วนกลางสำหรับผู้ดูแลระบบ (Super Admin Only)</h5>
                        </div>
                        <span class="badge bg-warning text-dark border border-warning" style="font-size: 0.68rem;">Admin Audit Alerts</span>
                    </div>
                    <div class="card-body p-4 small">
                        <input type="hidden" name="admin_notification_settings_submitted" value="1">

                        <!-- Global Bot Token & Admin Chat ID -->
                        <h6 class="fw-bold text-body mb-2"><i class="bi bi-robot text-primary me-1"></i>การตั้งค่า Telegram Bot ของระบบ:</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold text-body" for="global_telegram_bot_token">Telegram Bot HTTP API Token:</label>
                                <input type="text" class="form-control form-control-sm font-monospace" id="global_telegram_bot_token" name="global_telegram_bot_token" value="{{ old('global_telegram_bot_token', $globalSettings['bot_token'] ?? '') }}" placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-body" for="global_telegram_admin_chat_id">Admin Central Chat ID:</label>
                                <input type="text" class="form-control form-control-sm font-monospace" id="global_telegram_admin_chat_id" name="global_telegram_admin_chat_id" value="{{ old('global_telegram_admin_chat_id', $globalSettings['admin_chat_id'] ?? '') }}" placeholder="123456789">
                            </div>
                        </div>

                        <!-- Admin Channels -->
                        <label class="form-label fw-bold text-body mb-2">ช่องทางที่ต้องการให้ระบบแจ้งเตือนถึง Admin:</label>
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <div class="form-check form-switch p-2.5 rounded-2 border bg-body-tertiary d-flex align-items-center justify-content-between">
                                    <label class="form-check-label fw-semibold text-body" for="admin_notify_telegram">
                                        <i class="bi bi-telegram text-info me-1"></i>ส่งเข้า Telegram Admin
                                    </label>
                                    <input class="form-check-input ms-2" type="checkbox" role="switch" id="admin_notify_telegram" name="admin_notify_telegram" value="1" {{ old('admin_notify_telegram', $globalSettings['notify_telegram'] ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check form-switch p-2.5 rounded-2 border bg-body-tertiary d-flex align-items-center justify-content-between">
                                    <label class="form-check-label fw-semibold text-body" for="admin_notify_mail">
                                        <i class="bi bi-envelope-fill text-primary me-1"></i>ส่งเข้า Email Admin
                                    </label>
                                    <input class="form-check-input ms-2" type="checkbox" role="switch" id="admin_notify_mail" name="admin_notify_mail" value="1" {{ old('admin_notify_mail', $globalSettings['notify_mail'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Event Subscriptions -->
                        <label class="form-label fw-bold text-body mb-2">เลือกเหตุการณ์ที่ต้องการให้แจ้งเตือน Admin ทันที (Instant Audit Events):</label>
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <div class="form-check p-2 rounded-2 border bg-body">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" id="admin_notify_create" name="admin_notify_create" value="1" {{ old('admin_notify_create', $globalSettings['notify_create'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="admin_notify_create">🟢 เพิ่มข้อมูลใหม่ (Create)</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check p-2 rounded-2 border bg-body">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" id="admin_notify_update" name="admin_notify_update" value="1" {{ old('admin_notify_update', $globalSettings['notify_update'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="admin_notify_update">🟡 แก้ไขข้อมูล (Update)</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check p-2 rounded-2 border bg-body">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" id="admin_notify_delete" name="admin_notify_delete" value="1" {{ old('admin_notify_delete', $globalSettings['notify_delete'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="admin_notify_delete">🔴 ลบข้อมูล (Delete)</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check p-2 rounded-2 border bg-body">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" id="admin_notify_upload" name="admin_notify_upload" value="1" {{ old('admin_notify_upload', $globalSettings['notify_upload'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="admin_notify_upload">📎 อัปโหลดไฟล์ (File Upload)</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check p-2 rounded-2 border bg-body">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" id="admin_notify_backup" name="admin_notify_backup" value="1" {{ old('admin_notify_backup', $globalSettings['notify_backup'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="admin_notify_backup">💾 สำรอง &amp; กู้คืนระบบ (Backup/Restore)</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check p-2 rounded-2 border bg-body">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" id="admin_notify_auth" name="admin_notify_auth" value="1" {{ old('admin_notify_auth', $globalSettings['notify_auth'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="admin_notify_auth">🔐 เข้าสู่ระบบ/สวมสิทธิ์ (Auth Login)</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check p-2 rounded-2 border bg-body">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" id="admin_notify_scheduler" name="admin_notify_scheduler" value="1" {{ old('admin_notify_scheduler', $globalSettings['notify_scheduler'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="admin_notify_scheduler">⚡ รันคำสั่งบำรุงรักษา / Scheduler Manual Run</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Submit Button Bar -->
            <div class="col-12">
                <div class="card border shadow-sm bg-body">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="text-body-secondary small">
                            <i class="bi bi-shield-check text-success me-1"></i>บันทึกการเปลี่ยนแปลงทั้งหมดจะถูกนำไปใช้กับการแจ้งเตือนในระบบทันที
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ $canManageRoles ? route('backend.user.index') : route('backend.site.index') }}" class="btn btn-secondary px-4">
                                ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-save-fill"></i>
                                <span>บันทึกข้อมูลและการตั้งค่า</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Standalone hidden forms for instant testing -->
<form id="standaloneTestTelegramForm" action="{{ route('backend.user.test_my_telegram') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="chat_id" id="hidden_test_chat_id">
</form>

<form id="standaloneTestMailForm" action="{{ route('backend.user.test_my_mail') }}" method="POST" class="d-none">
    @csrf
</form>

@endsection

@push('scripts')
<script>
    function submitTestTelegram() {
        const input = document.getElementById('telegram_chat_id');
        const hidden = document.getElementById('hidden_test_chat_id');
        const form = document.getElementById('standaloneTestTelegramForm');

        if (!input || !input.value.trim()) {
            alert('กรุณากรอก Telegram Chat ID ของท่านในช่องก่อนกดทดสอบ');
            input.focus();
            return;
        }

        hidden.value = input.value.trim();
        form.submit();
    }

    function submitTestMail() {
        if (confirm('ระบบจะส่งอีเมลทดสอบ 1 ฉบับไปยังที่อยู่อีเมลของคุณ ยืนยันหรือไม่?')) {
            document.getElementById('standaloneTestMailForm').submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TomSelect !== 'undefined') {
            if (document.getElementById('role')) {
                new TomSelect('#role', {
                    placeholder: '-- ค้นหาและเลือกบทบาท --',
                    allowEmptyOption: true,
                    plugins: ['clear_button'],
                });
            }

            if (document.getElementById('researcher_id')) {
                new TomSelect('#researcher_id', {
                    placeholder: '-- ค้นหาชื่อนักวิจัย หรือคณะ/หน่วยงาน --',
                    allowEmptyOption: true,
                    maxOptions: 1000,
                    plugins: ['clear_button'],
                });
            }
        }
    });
</script>
@endpush
