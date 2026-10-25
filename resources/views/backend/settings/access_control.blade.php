@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แผงควบคุมระบบ</a></li>
                    <li class="breadcrumb-item">ผู้ดูแลระบบ</li>
                    <li class="breadcrumb-item active" aria-current="page">ควบคุมระบบล็อกอิน & นโยบายความปลอดภัยดาวน์โหลด</li>
                </ol>
            </nav>
            <h2 class="mb-1 fw-bold text-body">
                <i class="bi bi-shield-lock-fill me-2 text-primary"></i>ควบคุมระบบล็อกอิน & นโยบายความปลอดภัยดาวน์โหลด
            </h2>
            <p class="text-body-secondary mb-0">กำหนดค่าการยืนยันตัวตน Google Workspace และมาตรการความปลอดภัยการเข้าถึงไฟล์รายงานวิจัย (Multi-Layer Protection)</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>เกิดข้อผิดพลาดในการตรวจสอบข้อมูล:</h6>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('backend.settings.access_control.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column: Google & Authentication Controls -->
            <div class="col-lg-6">
                <div class="card shadow-sm border h-100">
                    <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary p-2 rounded-2">
                                <i class="bi bi-google fs-5"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold text-body">1. ระบบการเข้าสู่ระบบ (Authentication & SSO)</h6>
                                <small class="text-body-secondary">การตั้งค่า Google Workspace SSO และการล็อกอินด้วยรหัสผ่าน</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Switch 1: Google Login Enabled -->
                        <div class="form-check form-switch mb-3 p-3 bg-body-tertiary rounded-3 border">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="google_login_enabled" name="google_login_enabled" value="1" {{ ($settings['google_login_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-body" for="google_login_enabled">
                                เปิดใช้งานการเข้าสู่ระบบด้วย Google (Google Workspace SSO)
                                <div class="text-body-secondary fw-normal small">แสดงปุ่ม Google บนหน้าเข้าสู่ระบบเป็นช่องทางหลัก</div>
                            </label>
                        </div>

                        <!-- Switch 2: Standard Login Enabled -->
                        <div class="form-check form-switch mb-4 p-3 bg-body-tertiary rounded-3 border">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="standard_login_enabled" name="standard_login_enabled" value="1" {{ ($settings['standard_login_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-body" for="standard_login_enabled">
                                เปิดใช้งานการเข้าสู่ระบบด้วยชื่อผู้ใช้ / รหัสผ่าน (แบบเดิม)
                                <div class="text-body-secondary fw-normal small">ซ่อนฟอร์มรหัสผ่านไว้เป็นตัวเลือกสำรอง (กดเปิดได้เมื่อต้องการ)</div>
                            </label>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-key-fill me-1"></i> ข้อมูลการเชื่อมต่อ Google Cloud OAuth 2.0
                        </h6>

                        <!-- Google Client ID -->
                        <div class="mb-3">
                            <label for="google_client_id" class="form-label fw-semibold text-body">
                                Google Client ID <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('google_client_id') is-invalid @enderror" 
                                   id="google_client_id" 
                                   name="google_client_id" 
                                   value="{{ old('google_client_id', $settings['google_client_id']) }}"
                                   placeholder="เช่น 1234567890-abcdefg.apps.googleusercontent.com">
                            <div class="form-text text-body-secondary">
                                นำมาจาก <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-decoration-none">Google Cloud Console <i class="bi bi-box-arrow-up-right"></i></a>
                            </div>
                        </div>

                        <!-- Google Client Secret -->
                        <div class="mb-3">
                            <label for="google_client_secret" class="form-label fw-semibold text-body">
                                Google Client Secret
                            </label>
                            <input type="password" 
                                   class="form-control @error('google_client_secret') is-invalid @enderror" 
                                   id="google_client_secret" 
                                   name="google_client_secret" 
                                   value="{{ old('google_client_secret', $settings['google_client_secret']) }}"
                                   placeholder="รหัสลับ Client Secret จาก Google Console">
                            <div class="form-text text-body-secondary">รหัสจะถูกจัดเก็บอย่างปลอดภัยในฐานข้อมูลระบบ</div>
                        </div>

                        <!-- Allowed Domains -->
                        <div class="mb-3">
                            <label for="google_allowed_domains" class="form-label fw-semibold text-body">
                                โดเมนอีเมลที่อนุญาตให้เข้าสู่ระบบ (Allowed Domains)
                            </label>
                            <input type="text" 
                                   class="form-control @error('google_allowed_domains') is-invalid @enderror" 
                                   id="google_allowed_domains" 
                                   name="google_allowed_domains" 
                                   value="{{ old('google_allowed_domains', $settings['google_allowed_domains']) }}"
                                   placeholder="เช่น pcru.ac.th (คั่นด้วยจุลภาคหากมีหลายโดเมน)">
                            <div class="form-text text-body-secondary">
                                ระบุ เช่น <code>pcru.ac.th</code> เพื่อจำกัดเฉพาะบุคลากร หรือเว้นว่างหากอนุญาตทุกบัญชี Google
                            </div>
                        </div>

                        <!-- Switch 3: Auto Register -->
                        <div class="form-check form-switch mb-4 p-3 bg-body-tertiary rounded-3 border">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="google_auto_register" name="google_auto_register" value="1" {{ ($settings['google_auto_register'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-body" for="google_auto_register">
                                สร้างบัญชีผู้ใช้งานใหม่อัตโนมัติ (Auto-Provisioning)
                                <div class="text-body-secondary fw-normal small">หากใช้อีเมลมหาวิทยาลัยเข้าสู่ระบบเป็นครั้งแรก ระบบจะจับคู่กับข้อมูลนักวิจัยและสร้างบัญชีให้อัตโนมัติ</div>
                            </label>
                        </div>

                        <!-- Google Console Callback URI Callout -->
                        <div class="p-3 bg-info-subtle border border-info-subtle rounded-3 text-info-emphasis small">
                            <div class="fw-bold mb-1"><i class="bi bi-info-circle-fill me-1"></i> Authorized Redirect URI สำหรับ Google Console:</div>
                            <div class="input-group input-group-sm mt-1">
                                <input type="text" class="form-control font-monospace" id="callbackUriInput" value="{{ $googleCallbackUrl }}" readonly>
                                <button class="btn btn-outline-info" type="button" onclick="navigator.clipboard.writeText(document.getElementById('callbackUriInput').value); alert('คัดลอก URL เรียบร้อยแล้ว');">
                                    <i class="bi bi-clipboard"></i> คัดลอก
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Document Download Policies -->
            <div class="col-lg-6">
                <div class="card shadow-sm border h-100">
                    <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success p-2 rounded-2">
                                <i class="bi bi-file-earmark-lock-fill fs-5"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold text-body">2. นโยบายความปลอดภัยการดาวน์โหลดเอกสาร (Download Security)</h6>
                                <small class="text-body-secondary">มาตรการป้องกันอีกชั้นนอกเหนือจากการกำหนดเปิดเผยของแต่ละโครงการ</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Policy 1: Full Report Download Policy -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                <span>เล่มรายงานวิจัยฉบับสมบูรณ์ (Full Research Report PDF)</span>
                            </label>
                            <div class="border rounded-3 p-3 bg-body-tertiary">
                                <div class="form-check mb-2.5">
                                    <input class="form-check-input" type="radio" name="download_policy_full_report" id="report_public" value="public" {{ ($settings['download_policy_full_report'] ?? 'public') === 'public' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-body" for="report_public">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle me-1.5">แนะนำ (ค่าพื้นฐาน)</span>
                                        เปิดให้บุคคลทั่วไปดาวน์โหลดได้ทันที (Public)
                                        <div class="text-body-secondary fw-normal small mt-0.5">เปิดเสรีตามสถานะเปิดเผยของแต่ละโครงการโดยไม่ต้องล็อกอิน</div>
                                    </label>
                                </div>
                                <div class="form-check mb-2.5">
                                    <input class="form-check-input" type="radio" name="download_policy_full_report" id="report_members_only" value="members_only" {{ ($settings['download_policy_full_report'] ?? '') === 'members_only' ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="report_members_only">
                                        ต้องเป็นสมาชิก / เข้าสู่ระบบก่อนเท่านั้น (Members Only)
                                        <div class="text-body-secondary small mt-0.5">บุคคลทั่วไปไม่สามารถดาวน์โหลดได้โดยตรง ต้องล็อกอินก่อน (ป้องกันการดึงข้อมูลและคุ้มครองลิขสิทธิ์)</div>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="download_policy_full_report" id="report_admin_only" value="admin_only" {{ ($settings['download_policy_full_report'] ?? '') === 'admin_only' ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="report_admin_only">
                                        เฉพาะผู้ดูแลระบบและเจ้าของผลงานเท่านั้น (Strict Admin Only)
                                        <div class="text-body-secondary small mt-0.5">ปิดกั้นการดาวน์โหลดจากบุคคลภายนอกและสมาชิกทั่วไปทั้งหมด</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Policy 2: Abstract Policy -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-text-fill text-primary"></i>
                                <span>บทคัดย่องานวิจัย (Abstract Document)</span>
                            </label>
                            <div class="border rounded-3 p-3 bg-body-tertiary">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="download_policy_abstract" id="abstract_public" value="public" {{ ($settings['download_policy_abstract'] ?? 'public') === 'public' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-body" for="abstract_public">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle me-1.5">แนะนำ (ค่าพื้นฐาน)</span>
                                        เปิดให้อ่านได้แบบสาธารณะ (Public Access)
                                        <div class="text-body-secondary fw-normal small mt-0.5">บุคคลทั่วไปสามารถเปิดอ่านบทคัดย่อเพื่อการสืบค้นและอ้างอิงทางวิชาการได้ทันที</div>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="download_policy_abstract" id="abstract_members_only" value="members_only" {{ ($settings['download_policy_abstract'] ?? '') === 'members_only' ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="abstract_members_only">
                                        ต้องเป็นสมาชิก / เข้าสู่ระบบก่อน (Members Only)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Policy 3: Attachments Policy -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-paperclip text-secondary"></i>
                                <span>เอกสารแนบโครงการอื่นๆ (Project Attachments & Files)</span>
                            </label>
                            <div class="border rounded-3 p-3 bg-body-tertiary">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="download_policy_attachments" id="attach_public" value="public" {{ ($settings['download_policy_attachments'] ?? 'public') === 'public' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-body" for="attach_public">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle me-1.5">แนะนำ (ค่าพื้นฐาน)</span>
                                        เปิดให้ดาวน์โหลดได้แบบสาธารณะ (Public)
                                        <div class="text-body-secondary fw-normal small mt-0.5">เปิดให้บุคคลทั่วไปและบุคคลภายนอกสามารถดาวน์โหลดเอกสารแนบได้ทันที</div>
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="download_policy_attachments" id="attach_members_only" value="members_only" {{ ($settings['download_policy_attachments'] ?? '') === 'members_only' ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="attach_members_only">
                                        ต้องเป็นสมาชิก / เข้าสู่ระบบก่อน (Members Only)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="download_policy_attachments" id="attach_admin_only" value="admin_only" {{ ($settings['download_policy_attachments'] ?? '') === 'admin_only' ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="attach_admin_only">
                                        เฉพาะผู้ดูแลระบบและนักวิจัยเจ้าของโครงการเท่านั้น
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Notice Message when Login is Required -->
                        <div class="mb-4">
                            <label for="download_require_login_message" class="form-label fw-bold text-body">
                                ข้อความแจ้งเตือนเมื่อผู้ใช้ทั่วไปคลิกดาวน์โหลด (Prompt Notice Text) <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('download_require_login_message') is-invalid @enderror" 
                                      id="download_require_login_message" 
                                      name="download_require_login_message" 
                                      rows="3">{{ old('download_require_login_message', $settings['download_require_login_message']) }}</textarea>
                            <div class="form-text text-body-secondary">
                                ข้อความนี้จะแสดงเมื่อผู้ใช้ที่ยังไม่ล็อกอินพยายามเข้าถึงไฟล์รายงานวิจัย และจะถูกส่งต่อไปยังหน้าเข้าสู่ระบบ
                            </div>
                        </div>

                        <!-- Switch 4: Download Audit Logging -->
                        <div class="form-check form-switch p-3 bg-body-tertiary rounded-3 border">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="download_logging_enabled" name="download_logging_enabled" value="1" {{ ($settings['download_logging_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-body" for="download_logging_enabled">
                                เก็บบันทึกสถิติและประวัติการดาวน์โหลดเอกสาร (Audit Log & Download Counters)
                                <div class="text-body-secondary fw-normal small">นับสถิติจำนวนการดาวน์โหลดและจัดเก็บประวัติสำหรับรายงานวิเคราะห์</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit & Save Bar -->
        <div class="card shadow-sm border mt-4">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3 py-3">
                <div class="text-body-secondary small">
                    <i class="bi bi-shield-check text-success me-1"></i> การเปลี่ยนแปลงการตั้งค่าจะมีผลทันทีต่อระบบการล็อกอินและหน้าดาวน์โหลดไฟล์
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('backend.site.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> ยกเลิก
                    </a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                        <i class="bi bi-save2-fill me-1.5"></i> บันทึกการตั้งค่าทั้งหมด
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
