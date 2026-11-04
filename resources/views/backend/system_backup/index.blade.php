@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แผงควบคุมระบบ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('backend.system_commands.index') }}" class="text-decoration-none">คำสั่งระบบและการบำรุงรักษา</a></li>
            <li class="breadcrumb-item active" aria-current="page">สำรองและกู้คืนข้อมูลระบบ (Backup &amp; Disaster Recovery)</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2.5">
            <div class="p-2.5 rounded-3 bg-danger-subtle text-danger border border-danger-subtle">
                <i class="bi bi-shield-shaded fs-3"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="mb-0 fw-bold text-body">ศูนย์สำรองและกู้คืนข้อมูลระบบ (System Backup &amp; Disaster Recovery Hub)</h4>
                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                        <i class="bi bi-crown me-1"></i>The Creator Only
                    </span>
                </div>
                <p class="text-body-secondary small mb-0 mt-0.5">
                    ระบบสร้างแพ็กเกจสำรองฐานข้อมูล MariaDB และไฟล์เอกสาร Storage พร้อมระบบกู้คืนยืนยัน 2 ชั้นผ่าน Email OTP
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#rcloneGuideModal">
                <i class="bi bi-cloud-arrow-up me-1"></i> ซิงค์ Google Drive (Rclone)
            </button>
            <a href="{{ route('backend.system_commands.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-arrow-left"></i> กลับหน้าคำสั่งระบบ
            </a>
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
            <i class="bi bi-exclamation-octagon-fill fs-5 me-2.5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 1. System Metrics & Status Strip -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="small text-body-secondary fw-semibold mb-1"><i class="bi bi-database me-1 text-primary"></i>ขนาดฐานข้อมูล MariaDB</div>
                    <div class="h4 mb-0 fw-bold text-body" style="font-variant-numeric: tabular-nums;">{{ $dbSizeFormatted }}</div>
                    <div class="text-body-tertiary small mt-1">คลังข้อมูลงานวิจัยทั้งหมดในระบบ</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="small text-body-secondary fw-semibold mb-1"><i class="bi bi-folder2-open me-1 text-info"></i>ขนาดไฟล์เอกสาร Storage</div>
                    <div class="h4 mb-0 fw-bold text-body" style="font-variant-numeric: tabular-nums;">{{ $storageSizeFormatted }}</div>
                    <div class="text-body-tertiary small mt-1">ไฟล์ PDF, ภาพปก, เอกสารแนบ</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="small text-body-secondary fw-semibold mb-1"><i class="bi bi-archive me-1 text-success"></i>ไฟล์สำรองบนเซิร์ฟเวอร์</div>
                    <div class="h4 mb-0 fw-bold text-body" style="font-variant-numeric: tabular-nums;">{{ count($backupList) }} ไฟล์</div>
                    <div class="text-body-tertiary small mt-1">พาธ: <code>storage/app/backups/</code></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="small text-body-secondary fw-semibold mb-1"><i class="bi bi-terminal me-1 text-warning"></i>ไบนารี mysqldump</div>
                    <div class="h4 mb-0 fw-bold {{ $mysqldumpAvailable ? 'text-success' : 'text-danger' }}">
                        {{ $mysqldumpAvailable ? 'พร้อมใช้งาน ✓' : 'ไม่พบไบนารี ✗' }}
                    </div>
                    <div class="text-body-tertiary small mt-1">
                        MySQL: <span class="{{ $mysqlAvailable ? 'text-success' : 'text-danger' }}">{{ $mysqlAvailable ? 'พร้อม ✓' : 'ไม่พบ ✗' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Manual Backup Trigger Section -->
    <div class="card border shadow-sm mb-4">
        <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2.5 px-3">
            <h6 class="mb-0 fw-bold text-body">
                <i class="bi bi-play-circle-fill text-danger me-2"></i>สั่งสำรองข้อมูลทันที (Manual System Backup)
            </h6>
            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                ความปลอดภัยระดับเซิร์ฟเวอร์
            </span>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-body-tertiary rounded-3 border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary text-white p-1.5 rounded-2"><i class="bi bi-database"></i></span>
                                <h6 class="fw-bold mb-0 text-body">1. สำรองเฉพาะฐานข้อมูล</h6>
                            </div>
                            <p class="text-body-secondary small mb-3">
                                สกัดตารางทั้งหมดในฐานข้อมูล MariaDB เป็นไฟล์บีบอัด <code>.sql.gz</code> รวดเร็ว ประหยัดพื้นที่ เหมาะสำหรับสำรองประจำวัน
                            </p>
                        </div>
                        <form action="{{ route('backend.system_backup.create') }}" method="POST" onsubmit="return confirm('ยืนยันสำรองเฉพาะฐานข้อมูล (Database .sql.gz)?');">
                            @csrf
                            <input type="hidden" name="type" value="db">
                            <button type="submit" class="btn btn-outline-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1.5 py-2">
                                <i class="bi bi-database-down"></i> สำรองฐานข้อมูลทันที (.sql.gz)
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-body-tertiary rounded-3 border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-info text-white p-1.5 rounded-2"><i class="bi bi-folder2-open"></i></span>
                                <h6 class="fw-bold mb-0 text-body">2. สำรองเฉพาะไฟล์ Storage</h6>
                            </div>
                            <p class="text-body-secondary small mb-3">
                                บีบอัดเอกสาร PDF รายงานวิจัยและภาพปกทั้งหมดใน <code>storage/app/public/</code> เป็นไฟล์ <code>.zip</code> ป้องกันไฟล์เอกสารสูญหาย
                            </p>
                        </div>
                        <form action="{{ route('backend.system_backup.create') }}" method="POST" onsubmit="return confirm('ยืนยันสำรองไฟล์เอกสาร Storage (.zip)?');">
                            @csrf
                            <input type="hidden" name="type" value="files">
                            <button type="submit" class="btn btn-outline-info btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1.5 py-2">
                                <i class="bi bi-file-earmark-zip"></i> สำรองไฟล์เอกสารทันที (.zip)
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-body-tertiary rounded-3 border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success text-white p-1.5 rounded-2"><i class="bi bi-shield-check"></i></span>
                                <h6 class="fw-bold mb-0 text-body">3. สำรองทั้งระบบเต็มรูปแบบ</h6>
                            </div>
                            <p class="text-body-secondary small mb-3">
                                รวมทั้งฐานข้อมูล MariaDB และไฟล์ Storage ทั้งหมดไว้ในแพ็กเกจเดียว (Full Archive) สำหรับเตรียมย้ายเซิร์ฟเวอร์หรือกู้คืนฉุกเฉิน
                            </p>
                        </div>
                        <form action="{{ route('backend.system_backup.create') }}" method="POST" onsubmit="return confirm('ยืนยันสำรองทั้งระบบแบบเต็ม (Full Archive DB + Files)? อาจใช้เวลา 1-3 นาทีขึ้นอยู่กับขนาดไฟล์');">
                            @csrf
                            <input type="hidden" name="type" value="all">
                            <button type="submit" class="btn btn-success btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1.5 py-2">
                                <i class="bi bi-cloud-arrow-down-fill"></i> สำรองทั้งระบบเต็มรูปแบบ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Existing Backup Files Table -->
    <div class="card border shadow-sm mb-4">
        <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2.5 px-3 flex-wrap gap-2">
            <h6 class="mb-0 fw-bold text-body">
                <i class="bi bi-archive-fill text-warning me-2"></i>รายการไฟล์สำรองข้อมูลบนเซิร์ฟเวอร์ (Backup Archives List)
            </h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                {{ count($backupList) }} รายการ
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 32%;">ชื่อไฟล์สำรอง (Filename)</th>
                            <th style="width: 15%;">ประเภท (Type)</th>
                            <th style="width: 15%;">ขนาด (Size)</th>
                            <th style="width: 20%;">วัน-เวลาที่สร้าง (Created Date)</th>
                            <th class="text-end pe-3" style="width: 18%;">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backupList as $b)
                            <tr>
                                <td class="ps-3 font-monospace small">
                                    @php
                                        $isDb = in_array($b['type'], ['db', 'database', 'safety_snapshot']);
                                        $isFiles = $b['type'] === 'files';
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi {{ $isDb ? 'bi-database text-primary' : ($isFiles ? 'bi-folder-symlink text-info' : 'bi-shield-fill-check text-success') }} fs-5"></i>
                                        <div>
                                            <span class="fw-bold text-body">{{ $b['filename'] }}</span>
                                            @if($isFiles && isset($b['file_count']))
                                                <small class="text-body-secondary d-block">({{ number_format($b['file_count']) }} ไฟล์)</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($isDb)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $b['type'] === 'safety_snapshot' ? 'Safety Snapshot' : 'Database' }}</span>
                                    @elseif($isFiles)
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">Storage Files</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Full Archive</span>
                                    @endif
                                </td>
                                <td class="small fw-semibold text-body" style="font-variant-numeric: tabular-nums;">
                                    {{ $b['size_formatted'] }}
                                </td>
                                <td class="small text-body-secondary" style="font-variant-numeric: tabular-nums;">
                                    {{ $b['created_at_thai'] ?? $b['created_at'] ?? $b['date'] ?? '-' }}
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Download Button -->
                                        <a href="{{ route('backend.system_backup.download', $b['filename']) }}" 
                                           class="btn btn-outline-primary" 
                                           title="ดาวน์โหลดไฟล์สำรอง">
                                            <i class="bi bi-download"></i>
                                        </a>

                                        <!-- Safe Restore Button (Database only) -->
                                        @if($isDb)
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    title="กู้คืนฐานข้อมูล (Restore with OTP)" 
                                                    onclick="openRestoreModal('{{ $b['filename'] }}', '{{ $b['type'] }}', '{{ $b['size_formatted'] }}')">
                                                <i class="bi bi-arrow-counterclockwise"></i> กู้คืน
                                            </button>
                                        @endif

                                        <!-- Delete Button -->
                                        <form action="{{ route('backend.system_backup.destroy', $b['filename']) }}" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('ยืนยันลบไฟล์สำรอง {{ $b['filename'] }} ออกจากเซิร์ฟเวอร์?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-secondary" title="ลบไฟล์สำรอง">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-body-secondary">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                    ยังไม่มีไฟล์สำรองในระบบ คลิกปุ่ม <strong>"สำรองเฉพาะฐานข้อมูล"</strong> ด้านบนเพื่อเริ่มสร้างไฟล์แรก
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Two-Factor OTP Restore Confirmation -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="restoreModalLabel">ยืนยันการกู้คืนฐานข้อมูล (Emergency Restore)</h5>
                        <small class="text-white-50">การกู้คืนจะทับข้อมูลปัจจุบัน และสร้าง Safety Snapshot ให้อัตโนมัติ</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('backend.system_backup.confirm_restore') }}" method="POST" id="restoreConfirmForm">
                @csrf
                <input type="hidden" name="filename" id="restoreFilenameInput" value="">

                <div class="modal-body p-4">
                    <div class="alert alert-warning-subtle border border-warning-subtle d-flex align-items-center mb-3 p-3 rounded-3">
                        <i class="bi bi-shield-exclamation text-warning fs-4 me-2.5"></i>
                        <div class="small">
                            <strong>คำเตือนความปลอดภัยสูงสุด:</strong> ระบบจะเขียนทับฐานข้อมูลปัจจุบันด้วยข้อมูลจากไฟล์สำรอง โดยระบบจะสร้าง Safety Snapshot สำรองข้อมูลปัจจุบันให้อัตโนมัติก่อนเริ่มกู้คืนเสมอ
                        </div>
                    </div>

                    <div class="p-3 bg-body-tertiary rounded-3 border mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-body-secondary">ไฟล์ที่จะกู้คืน:</span>
                            <span class="fw-bold font-monospace text-body" id="restoreFilenameDisplay">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-body-secondary">ขนาดไฟล์:</span>
                            <span class="fw-bold text-body" id="restoreFileSizeDisplay">-</span>
                        </div>
                    </div>

                    <!-- Step 1: Request OTP -->
                    <div id="otpRequestSection" class="text-center py-2">
                        <p class="small text-body-secondary mb-3">
                            เพื่อความปลอดภัยสูงสุด กรุณากดปุ่มด้านล่างเพื่อขอรับรหัสความปลอดภัย OTP 6 หลัก<br>
                            ระบบจะจัดส่งรหัสยืนยันไปยังอีเมลเฉพาะของ <strong>The Creator</strong>
                        </p>
                        <button type="button" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1.5" id="btnSendOtp" onclick="sendRestoreOtp()">
                            <i class="bi bi-envelope-arrow-up-fill"></i> กดส่งรหัส OTP ไปยังอีเมลของฉัน
                        </button>
                        <div id="otpLoadingSpinner" class="d-none mt-2">
                            <span class="spinner-border spinner-border-sm text-danger me-1" role="status" aria-hidden="true"></span>
                            <span class="small text-body-secondary">กำลังจัดส่งรหัสความปลอดภัย OTP...</span>
                        </div>
                    </div>

                    <!-- Step 2: Input OTP & Confirmation Phrase (Hidden initially) -->
                    <div id="otpInputSection" class="d-none">
                        <div class="alert alert-success-subtle border border-success-subtle small mb-3 p-2.5 rounded-3 d-flex align-items-center" id="otpSentAlert">
                            <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                            <span id="otpSentMessage">จัดส่งรหัส OTP เรียบร้อยแล้ว กรุณาตรวจสอบอีเมล</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-body">
                                1. รหัสความปลอดภัย OTP 6 หลัก: <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="otp" 
                                   id="otpInput" 
                                   class="form-control text-center font-monospace fw-bold fs-4" 
                                   maxlength="6" 
                                   placeholder="● ● ● ● ● ●" 
                                   required 
                                   autocomplete="off"
                                   style="letter-spacing: 6px;">
                            <div class="form-text small text-body-tertiary">รหัสมีอายุการใช้งาน 10 นาที</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-body">
                                2. พิมพ์ยืนยันคำว่า <code class="text-danger fw-bold">CONFIRM RESTORE</code>: <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="confirm_text" 
                                   id="confirmTextInput" 
                                   class="form-control text-center font-monospace" 
                                   placeholder="พิมพ์ CONFIRM RESTORE ที่นี่" 
                                   required 
                                   autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-body-tertiary border-top py-2.5">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1.5" id="btnSubmitRestore" disabled>
                        <i class="bi bi-arrow-counterclockwise"></i> ยืนยันเริ่มการกู้คืนระบบ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Google Drive & Cloud Backup Sync Guide (Rclone) -->
<div class="modal fade" id="rcloneGuideModal" tabindex="-1" aria-labelledby="rcloneGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-body-tertiary border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-2 bg-info-subtle text-info">
                        <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body" id="rcloneGuideModalLabel">การเชื่อมต่อสำรองข้อมูลขึ้น Google Drive อัตโนมัติ (Rclone Sync Guide)</h5>
                        <small class="text-body-secondary">แนวทางมาตรฐานสากลสำหรับ SysAdmin ในการซิงค์ไฟล์สำรองขึ้นคลาวด์โดยไม่ต้องเขียนโค้ด API</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 small">
                <div class="alert alert-info-subtle border border-info-subtle d-flex align-items-center mb-3 p-3 rounded-3">
                    <i class="bi bi-info-circle-fill text-info fs-5 me-2.5"></i>
                    <div>
                        <strong>ทำไม Rclone จึงเป็นทางเลือกที่ดีที่สุด:</strong> ไม่ต้องเสี่ยงกับ Google OAuth Token หมดอายุทุก 7 วัน ไม่กินหน่วยความจำของ PHP และทำงานแบบ Background Synchronization ซิงค์เฉพาะไฟล์ใหม่หรือไฟล์ที่มีการเปลี่ยนแปลงอย่างรวดเร็ว
                    </div>
                </div>

                <h6 class="fw-bold text-body mb-2">1. ติดตั้ง Rclone:</h6>
                <div class="mb-3">
                    <span class="text-body-secondary d-block mb-1">บน Windows / Laragon:</span>
                    <ul class="mb-2 ps-3 text-body-secondary">
                        <li>ดาวน์โหลด Rclone for Windows จาก <a href="https://rclone.org/downloads/" target="_blank">rclone.org</a></li>
                        <li>แตกไฟล์นำ <code>rclone.exe</code> ไปวางไว้ใน <code>C:\laragon\bin\rclone\rclone.exe</code></li>
                    </ul>
                    <span class="text-body-secondary d-block mb-1">บน Linux (Ubuntu / Debian Production):</span>
                    <pre class="p-2.5 bg-dark text-light rounded font-monospace" style="font-size: 0.8rem;">sudo apt update && sudo apt install -y rclone</pre>
                </div>

                <h6 class="fw-bold text-body mb-2">2. เชื่อมต่อบัญชี Google Drive ของมหาวิทยาลัย:</h6>
                <p class="text-body-secondary mb-2">เปิด Terminal / CMD แล้วรันคำสั่งตัวช่วยตั้งค่าแบบโต้ตอบ:</p>
                <pre class="p-2.5 bg-dark text-warning rounded mb-3 font-monospace" style="font-size: 0.8rem;">rclone config</pre>
                <ol class="ps-3 text-body-secondary mb-3">
                    <li>พิมพ์ <code>n</code> (New remote) ➔ ตั้งชื่อ เช่น <code>gdrive</code></li>
                    <li>เลือกประเภทสตอเรจ ➔ พิมพ์หมายเลขของ <code>Google Drive</code> (ปกติคือ <code>drive</code>)</li>
                    <li>ข้าม Client ID และ Secret (กด Enter)</li>
                    <li>เลือกสิทธิ์ ➔ <code>1</code> (Full access all files)</li>
                    <li>ทำตามขั้นตอนล็อกอินด้วยบัญชี Google ของมหาวิทยาลัยบนเบราว์เซอร์จนเสร็จสิ้น</li>
                </ol>

                <h6 class="fw-bold text-body mb-2">3. ทดสอบคำสั่งซิงค์ไฟล์สำรองขึ้น Google Drive:</h6>
                <pre class="p-2.5 bg-dark text-light rounded mb-3 font-monospace" style="font-size: 0.8rem;">rclone sync storage/app/backups/ gdrive:PCRU_Research_Backups/ -v</pre>

                <h6 class="fw-bold text-body mb-2">4. ตั้งเวลาอัตโนมัติ (Automated Nightly Cloud Sync):</h6>
                <p class="text-body-secondary mb-1">ตั้ง Cron Job ให้ซิงค์ไฟล์สำรองขึ้น Google Drive อัตโนมัติทุกคืนเวลา 03:00 น.:</p>
                <pre class="p-2.5 bg-dark text-success rounded mb-0 font-monospace" style="font-size: 0.8rem;">0 3 * * * rclone sync /var/www/laravel_rdb/storage/app/backups/ gdrive:PCRU_Research_Backups/ --quiet</pre>
            </div>
            <div class="modal-footer bg-body-tertiary border-top py-2.5">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentRestoreFilename = '';

function openRestoreModal(filename, type, sizeFormatted) {
    currentRestoreFilename = filename;
    document.getElementById('restoreFilenameInput').value = filename;
    document.getElementById('restoreFilenameDisplay').innerText = filename;
    document.getElementById('restoreFileSizeDisplay').innerText = sizeFormatted;
    
    // Reset modal state
    document.getElementById('otpRequestSection').classList.remove('d-none');
    document.getElementById('otpInputSection').classList.add('d-none');
    document.getElementById('btnSubmitRestore').disabled = true;
    document.getElementById('otpInput').value = '';
    document.getElementById('confirmTextInput').value = '';
    document.getElementById('btnSendOtp').disabled = false;
    document.getElementById('otpLoadingSpinner').classList.add('d-none');

    const modalEl = document.getElementById('restoreModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

function sendRestoreOtp() {
    if (!currentRestoreFilename) return;

    const btn = document.getElementById('btnSendOtp');
    const spinner = document.getElementById('otpLoadingSpinner');
    btn.disabled = true;
    spinner.classList.remove('d-none');

    fetch('{{ route('backend.system_backup.request_otp') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ filename: currentRestoreFilename })
    })
    .then(res => res.json())
    .then(data => {
        spinner.classList.add('d-none');
        if (data.success) {
            document.getElementById('otpRequestSection').classList.add('d-none');
            document.getElementById('otpInputSection').classList.remove('d-none');
            document.getElementById('otpSentMessage').innerText = data.message;
            if (data.dev_otp) {
                // In local/log mailer, provide handy OTP helper for testing
                document.getElementById('otpInput').value = data.dev_otp;
                checkRestoreInputs();
            }
        } else {
            btn.disabled = false;
            alert(data.message || 'ไม่สามารถส่ง OTP ได้');
        }
    })
    .catch(err => {
        spinner.classList.add('d-none');
        btn.disabled = false;
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
    });
}

function checkRestoreInputs() {
    const otp = document.getElementById('otpInput').value.trim();
    const text = document.getElementById('confirmTextInput').value.trim();
    const btn = document.getElementById('btnSubmitRestore');
    if (otp.length === 6 && text.toUpperCase() === 'CONFIRM RESTORE') {
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const otpEl = document.getElementById('otpInput');
    const confirmEl = document.getElementById('confirmTextInput');
    if (otpEl) otpEl.addEventListener('input', checkRestoreInputs);
    if (confirmEl) confirmEl.addEventListener('input', checkRestoreInputs);
});
</script>
@endsection
