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
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm" onclick="openGitSyncModal()">
                <i class="bi bi-git me-0.5"></i> อัปเดตขึ้น Git (Git Push)
                @php
                    $gitPendingCount = ($gitStatus['changed_count'] ?? 0) + ($gitStatus['ahead_count'] ?? 0);
                @endphp
                <span id="gitHeaderBadge" class="badge {{ $gitPendingCount > 0 ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-success-subtle text-success-emphasis border border-success-subtle' }} ms-1" style="font-variant-numeric: tabular-nums;">
                    {{ $gitPendingCount > 0 ? ($gitStatus['changed_count'] ?? 0) . ' ไฟล์' : 'ซิงค์แล้ว ✓' }}
                </span>
            </button>
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
                        <form id="formBackupDb" class="no-loading" action="{{ route('backend.system_backup.create') }}" method="POST" onsubmit="return triggerBackup(event, 'db', 'ยืนยันสำรองเฉพาะฐานข้อมูล MariaDB (.sql.gz) ทันที?');">
                            @csrf
                            <input type="hidden" name="type" value="db">
                            <button type="submit" class="btn btn-outline-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1.5 py-2 btn-backup-trigger" {{ $isBackupRunning ? 'disabled' : '' }}>
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
                        <form id="formBackupFiles" class="no-loading" action="{{ route('backend.system_backup.create') }}" method="POST" onsubmit="return triggerBackup(event, 'files', 'ยืนยันสำรองไฟล์เอกสาร Storage (.zip)? ระบบจะทำงานในเบื้องหลังพร้อมแถบแสดงสถานะสด (Storage ~{{ $storageSizeFormatted }})');">
                            @csrf
                            <input type="hidden" name="type" value="files">
                            <button type="submit" class="btn btn-outline-info btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1.5 py-2 btn-backup-trigger" {{ $isBackupRunning ? 'disabled' : '' }}>
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
                        <form id="formBackupAll" class="no-loading" action="{{ route('backend.system_backup.create') }}" method="POST" onsubmit="return triggerBackup(event, 'all', 'ยืนยันสำรองทั้งระบบแบบเต็ม (Full Archive DB + Files)? ระบบจะทำงานในเบื้องหลังพร้อมแถบแสดงสถานะสด');">
                            @csrf
                            <input type="hidden" name="type" value="all">
                            <button type="submit" class="btn btn-success btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1.5 py-2 btn-backup-trigger" {{ $isBackupRunning ? 'disabled' : '' }}>
                                <i class="bi bi-cloud-arrow-down-fill"></i> สำรองทั้งระบบเต็มรูปแบบ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2.5 Live Backup Progress Card -->
    <div id="backupProgressCard" class="card border border-primary-subtle shadow-sm mb-4 {{ ($isBackupRunning || (!empty($currentProgress) && in_array(($currentProgress['status'] ?? ''), ['running', 'completed', 'failed']))) ? '' : 'd-none' }}" style="background: var(--bs-card-bg);">
        <div class="card-header bg-primary-subtle py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span id="backupSpinnerIcon" class="spinner-border spinner-border-sm text-primary {{ ($currentProgress['status'] ?? '') === 'running' ? '' : 'd-none' }}" role="status"></span>
                <i id="backupStatusIcon" class="bi {{ ($currentProgress['status'] ?? '') === 'completed' ? 'bi-check-circle-fill text-success' : (($currentProgress['status'] ?? '') === 'failed' ? 'bi-exclamation-triangle-fill text-danger' : 'bi-activity text-primary') }} fs-5"></i>
                <h6 class="mb-0 fw-bold text-body" id="backupHeaderTitle">สถานะการสำรองข้อมูลสด (Live Backup Progress)</h6>
                <span id="backupTypeBadge" class="badge bg-primary text-white ms-1">{{ $currentProgress['type_label'] ?? 'กำลังประมวลผล' }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-body border text-body-secondary fw-semibold px-2.5 py-1.5" style="font-variant-numeric: tabular-nums;">
                    <i class="bi bi-stopwatch me-1 text-primary"></i>เวลาที่ใช้ไป: <span id="backupElapsedTime">{{ isset($currentProgress['elapsed_seconds']) ? sprintf('%02d:%02d', floor($currentProgress['elapsed_seconds'] / 60), $currentProgress['elapsed_seconds'] % 60) : '00:00' }}</span>
                </span>
                <button type="button" id="btnCancelBackup" class="btn btn-outline-secondary btn-sm py-0.5 px-2" onclick="cancelBackupProcess()" title="ปิด/ล้างแถบสถานะ">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            <!-- Progress Bar -->
            <div class="d-flex align-items-center justify-content-between mb-1.5">
                <div class="fw-semibold text-body small d-flex align-items-center gap-1.5">
                    <span id="backupStageLabel">{{ $currentProgress['stage_label'] ?? 'กำลังจัดเตรียมข้อมูล...' }}</span>
                </div>
                <div class="fw-bold text-primary small" id="backupPercentLabel" style="font-variant-numeric: tabular-nums;">
                    {{ $currentProgress['percent'] ?? 0 }}%
                </div>
            </div>
            <div class="progress mb-2.5" style="height: 18px; border-radius: 6px; background-color: var(--bs-tertiary-bg);" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                <div id="backupProgressBar" 
                     class="progress-bar progress-bar-striped {{ ($currentProgress['status'] ?? '') === 'running' ? 'progress-bar-animated' : '' }} {{ ($currentProgress['status'] ?? '') === 'completed' ? 'bg-success' : (($currentProgress['status'] ?? '') === 'failed' ? 'bg-danger' : 'bg-primary') }} fw-bold" 
                     style="width: {{ $currentProgress['percent'] ?? 0 }}%; font-size: 0.75rem; transition: width 0.3s ease;">
                    {{ $currentProgress['percent'] ?? 0 }}%
                </div>
            </div>

            <!-- Details Strip -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 small text-body-secondary">
                <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 70%;">
                    <i class="bi bi-file-earmark-code text-body-tertiary"></i>
                    <span id="backupCurrentFile" class="font-monospace text-truncate text-body-tertiary" style="font-size: 0.8rem;">
                        {{ $currentProgress['current_file'] ?? '' }}
                    </span>
                </div>
                <div id="backupCounter" class="fw-semibold" style="font-variant-numeric: tabular-nums;">
                    @if(isset($currentProgress['total_files']) && $currentProgress['total_files'] > 0)
                        {{ number_format($currentProgress['processed_files'] ?? 0) }} / {{ number_format($currentProgress['total_files']) }} ไฟล์
                    @endif
                </div>
            </div>

            <!-- Completed Message Banner -->
            <div id="backupCompleteBanner" class="alert alert-success d-flex align-items-center mt-3 mb-0 py-2.5 px-3 {{ ($currentProgress['status'] ?? '') === 'completed' ? '' : 'd-none' }}">
                <i class="bi bi-check-circle-fill fs-5 text-success me-2.5"></i>
                <div class="flex-grow-1 small">
                    <strong class="d-block text-success-emphasis">การสำรองข้อมูลเสร็จสมบูรณ์ 100%!</strong>
                    <span id="backupCompleteMsg">{{ $currentProgress['stage_label'] ?? '' }}</span>
                </div>
                <a href="{{ route('backend.system_backup.index') }}" class="btn btn-success btn-sm ms-2">
                    <i class="bi bi-arrow-clockwise me-1"></i>รีเฟรชตาราง
                </a>
            </div>

            <!-- Error Message Banner -->
            <div id="backupErrorBanner" class="alert alert-danger d-flex align-items-center mt-3 mb-0 py-2.5 px-3 {{ ($currentProgress['status'] ?? '') === 'failed' ? '' : 'd-none' }}">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger me-2.5"></i>
                <div class="flex-grow-1 small">
                    <strong class="d-block text-danger-emphasis">เกิดข้อผิดพลาดในการสำรองข้อมูล</strong>
                    <span id="backupErrorMsg">{{ $currentProgress['error'] ?? 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ' }}</span>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm ms-2" onclick="cancelBackupProcess()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>ปิดและลองใหม่
                </button>
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
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        @endif

                                        <!-- Delete Button -->
                                        <form action="{{ route('backend.system_backup.delete') }}" 
                                              method="POST" 
                                              class="d-inline no-loading" 
                                              onsubmit="if(this.dataset.submitting === '1') return false; if(!confirm('ยืนยันลบไฟล์สำรอง {{ $b['filename'] }} ออกจากเซิร์ฟเวอร์?')) return false; this.dataset.submitting = '1'; const btn = this.querySelector('button[type=submit]'); if(btn) btn.disabled = true; return true;">
                                            @csrf
                                            <input type="hidden" name="filename" value="{{ $b['filename'] }}">
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

<!-- 6. Git Sync & Push Modal -->
<div class="modal fade" id="gitSyncModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success-subtle border-bottom border-success-subtle py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success text-white p-2 rounded-2"><i class="bi bi-git fs-5"></i></span>
                    <div>
                        <h6 class="modal-title fw-bold text-body mb-0">อัปเดตโค้ดขึ้น GitHub Repository (One-Click Git Commit &amp; Push)</h6>
                        <small class="text-body-secondary">ตรวจสอบไฟล์ที่มีการแก้ไข บันทึก Commit และส่งขึ้น Remote Repository โดยอัตโนมัติ</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Repository Info Strip -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="p-2.5 rounded-3 bg-body-tertiary border h-100">
                            <div class="text-body-secondary small mb-0.5"><i class="bi bi-diagram-2 me-1 text-primary"></i>สาขาปัจจุบัน (Branch)</div>
                            <div class="fw-bold text-body font-monospace small" id="gitBranchDisplay">{{ $gitStatus['branch'] ?? 'main' }}</div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="p-2.5 rounded-3 bg-body-tertiary border h-100">
                            <div class="d-flex justify-content-between align-items-center mb-0.5">
                                <span class="text-body-secondary small"><i class="bi bi-clock-history me-1 text-success"></i>Commit ล่าสุด</span>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle font-monospace" id="gitLastHashDisplay">{{ $gitStatus['last_commit_hash'] ?? '-' }}</span>
                            </div>
                            <div class="fw-semibold text-body small text-truncate" id="gitLastMsgDisplay" title="{{ $gitStatus['last_commit_message'] ?? '-' }}">
                                {{ $gitStatus['last_commit_message'] ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Changed Files Header -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-bold text-body small">
                        <i class="bi bi-file-earmark-diff me-1 text-warning"></i>รายการไฟล์ที่มีการเปลี่ยนแปลง (<span id="gitChangedCountText" style="font-variant-numeric: tabular-nums;">{{ $gitStatus['changed_count'] ?? 0 }}</span> ไฟล์)
                    </div>
                    <button type="button" id="btnRefreshGitStatus" class="btn btn-outline-secondary btn-sm py-0.5 px-2 small" onclick="refreshGitStatus()">
                        <i class="bi bi-arrow-clockwise me-1"></i>ตรวจสอบสถานะล่าสุด
                    </button>
                </div>

                <!-- Changed Files List Box -->
                <div id="gitFilesListContainer" class="border rounded-3 bg-body-tertiary p-2.5 mb-3" style="max-height: 210px; overflow-y: auto;">
                    <!-- Populated dynamically by JS -->
                </div>

                <!-- Commit Message Input -->
                <div class="mb-3">
                    <label for="gitCommitMessageInput" class="form-label small fw-bold text-body mb-1">
                        ข้อความบันทึกการเปลี่ยนแปลง (Commit Message) <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="gitCommitMessageInput"
                           class="form-control form-control-sm font-monospace"
                           maxlength="255"
                           placeholder="เช่น chore: update system changes">
                    <div class="form-text small text-body-secondary">
                        ระบบจะรันคำสั่ง <code>git add -A</code> ➔ <code>git commit -m "..."</code> ➔ <code>git push origin <span id="gitBranchCode">{{ $gitStatus['branch'] ?? 'main' }}</span></code> ตามลำดับ (เคารพกฎ <code>.gitignore</code> และไม่ส่งไฟล์ <code>.env</code> หรือไฟล์สำรองขนาดใหญ่)
                    </div>
                </div>

                <!-- Git Execution Output Console -->
                <div id="gitOutputSection" class="d-none mt-3">
                    <div class="small fw-bold text-body mb-1"><i class="bi bi-terminal me-1"></i>ผลลัพธ์การทำงาน (Git Console Output):</div>
                    <pre id="gitOutputConsole" class="p-3 bg-dark text-light rounded-3 font-monospace mb-0" style="font-size: 0.78rem; max-height: 180px; overflow-y: auto; white-space: pre-wrap;"></pre>
                </div>
            </div>
            <div class="modal-footer bg-body-tertiary border-top py-2.5 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                <button type="button" id="btnExecuteGitPush" class="btn btn-success btn-sm px-4 d-inline-flex align-items-center gap-1.5 fw-semibold" onclick="executeGitPush()">
                    <span id="gitPushSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    <i id="gitPushIcon" class="bi bi-cloud-upload-fill"></i>
                    <span id="gitPushBtnText">Commit &amp; Push ขึ้น GitHub ทันที</span>
                </button>
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

    // Auto-resume polling if backup is currently running
    const isRunning = {{ $isBackupRunning ? 'true' : 'false' }};
    if (isRunning) {
        document.querySelectorAll('.btn-backup-trigger').forEach(b => b.disabled = true);
        startProgressPolling();
    }
});

// Live Backup Tracking Logic
let backupProgressInterval = null;

function formatElapsedSeconds(sec) {
    if (!sec || sec < 0) return '00:00';
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
}

function resetProgressUi(type) {
    const card = document.getElementById('backupProgressCard');
    const typeLabels = {
        'db': 'ฐานข้อมูล MariaDB (.sql.gz)',
        'files': 'ไฟล์เอกสาร Storage (.zip)',
        'all': 'ทั้งระบบเต็มรูปแบบ (Full Archive)'
    };

    card.classList.remove('d-none');
    document.getElementById('backupSpinnerIcon').classList.remove('d-none');
    document.getElementById('backupStatusIcon').className = 'bi bi-activity text-primary fs-5';
    document.getElementById('backupTypeBadge').innerText = typeLabels[type] || 'กำลังประมวลผล';
    document.getElementById('backupHeaderTitle').innerText = 'สถานะการสำรองข้อมูลสด (Live Backup Progress)';
    document.getElementById('backupElapsedTime').innerText = '00:00';

    document.getElementById('backupStageLabel').innerText = 'กำลังส่งคำสั่งไปยังเซิร์ฟเวอร์...';
    document.getElementById('backupPercentLabel').innerText = '2%';
    
    const bar = document.getElementById('backupProgressBar');
    bar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary fw-bold';
    bar.style.width = '2%';
    bar.innerText = '2%';

    document.getElementById('backupCurrentFile').innerText = '';
    document.getElementById('backupCounter').innerText = '';
    document.getElementById('backupCompleteBanner').classList.add('d-none');
    document.getElementById('backupErrorBanner').classList.add('d-none');
}

function updateProgressUi(p) {
    if (!p) return;

    const card = document.getElementById('backupProgressCard');
    card.classList.remove('d-none');

    const spinner = document.getElementById('backupSpinnerIcon');
    const statusIcon = document.getElementById('backupStatusIcon');
    const bar = document.getElementById('backupProgressBar');
    const percent = Math.min(100, Math.max(0, parseInt(p.percent || 0)));

    document.getElementById('backupElapsedTime').innerText = formatElapsedSeconds(p.elapsed_seconds || 0);
    document.getElementById('backupStageLabel').innerText = p.stage_label || 'กำลังดำเนินการ...';
    document.getElementById('backupPercentLabel').innerText = percent + '%';
    if (p.type_label) {
        document.getElementById('backupTypeBadge').innerText = p.type_label;
    }

    bar.style.width = percent + '%';
    bar.innerText = percent + '%';

    if (p.current_file) {
        document.getElementById('backupCurrentFile').innerText = p.current_file;
    }
    if (p.total_files && p.total_files > 0) {
        const processed = p.processed_files || 0;
        document.getElementById('backupCounter').innerText = `${Number(processed).toLocaleString()} / ${Number(p.total_files).toLocaleString()} ไฟล์`;
    }

    if (p.status === 'running') {
        spinner.classList.remove('d-none');
        statusIcon.className = 'bi bi-activity text-primary fs-5';
        bar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary fw-bold';
        document.getElementById('backupCompleteBanner').classList.add('d-none');
        document.getElementById('backupErrorBanner').classList.add('d-none');
    } else if (p.status === 'completed') {
        spinner.classList.add('d-none');
        statusIcon.className = 'bi bi-check-circle-fill text-success fs-5';
        bar.className = 'progress-bar bg-success fw-bold';
        document.getElementById('backupCompleteBanner').classList.remove('d-none');
        document.getElementById('backupCompleteMsg').innerText = p.stage_label || 'สำรองข้อมูลสำเร็จ';
        document.getElementById('backupErrorBanner').classList.add('d-none');
    } else if (p.status === 'failed') {
        spinner.classList.add('d-none');
        statusIcon.className = 'bi bi-exclamation-triangle-fill text-danger fs-5';
        bar.className = 'progress-bar bg-danger fw-bold';
        document.getElementById('backupCompleteBanner').classList.add('d-none');
        document.getElementById('backupErrorBanner').classList.remove('d-none');
        document.getElementById('backupErrorMsg').innerText = p.error || p.stage_label || 'เกิดข้อผิดพลาดในการสำรองข้อมูล';
    }
}

function pollBackupProgress() {
    fetch('{{ route('backend.system_backup.progress') }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data || !data.progress) return;
        const p = data.progress;
        updateProgressUi(p);

        if (p.status === 'completed') {
            if (backupProgressInterval) clearInterval(backupProgressInterval);
            backupProgressInterval = null;
            document.querySelectorAll('.btn-backup-trigger').forEach(b => b.disabled = false);

            // Give user 1.5 seconds to see 100% success animation, then reload table while keeping completion card visible
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else if (p.status === 'failed') {
            if (backupProgressInterval) clearInterval(backupProgressInterval);
            backupProgressInterval = null;
            document.querySelectorAll('.btn-backup-trigger').forEach(b => b.disabled = false);
        }
    })
    .catch(err => {
        console.error('Error polling backup progress:', err);
    });
}

function startProgressPolling() {
    if (backupProgressInterval) clearInterval(backupProgressInterval);
    backupProgressInterval = setInterval(pollBackupProgress, 1000);
    pollBackupProgress();
}

function triggerBackup(event, type, confirmMessage) {
    if (event) event.preventDefault();

    if (!confirm(confirmMessage)) {
        return false;
    }

    document.querySelectorAll('.btn-backup-trigger').forEach(b => b.disabled = true);
    resetProgressUi(type);

    const card = document.getElementById('backupProgressCard');
    card.scrollIntoView({ behavior: 'smooth', block: 'center' });

    fetch('{{ route('backend.system_backup.create') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ type: type })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            startProgressPolling();
        } else {
            alert(data.message || 'ไม่สามารถเริ่มต้นสำรองข้อมูลได้');
            if (data.progress && data.progress.status === 'running') {
                startProgressPolling();
            } else {
                document.querySelectorAll('.btn-backup-trigger').forEach(b => b.disabled = false);
            }
        }
    })
    .catch(err => {
        console.error('Failed to dispatch backup:', err);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        document.querySelectorAll('.btn-backup-trigger').forEach(b => b.disabled = false);
    });

    return false;
}

function cancelBackupProcess() {
    if (confirm('ยืนยันปิดแถบสถานะการสำรองข้อมูลนี้หรือไม่?')) {
        if (backupProgressInterval) {
            clearInterval(backupProgressInterval);
            backupProgressInterval = null;
        }

        fetch('{{ route('backend.system_backup.cancel') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(() => {
            document.getElementById('backupProgressCard').classList.add('d-none');
            document.querySelectorAll('.btn-backup-trigger').forEach(b => b.disabled = false);
        })
        .catch(() => {
            document.getElementById('backupProgressCard').classList.add('d-none');
            document.querySelectorAll('.btn-backup-trigger').forEach(b => b.disabled = false);
        });
    }
}

// ==========================================
// Git Sync & Push Modal Logic
// ==========================================
function getDefaultCommitMessage() {
    const now = new Date();
    const pad = n => (n < 10 ? '0' + n : n);
    const ts = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
    return `chore: update system changes (${ts})`;
}

function openGitSyncModal() {
    const msgInput = document.getElementById('gitCommitMessageInput');
    if (msgInput && !msgInput.value.trim()) {
        msgInput.value = getDefaultCommitMessage();
    }
    document.getElementById('gitOutputSection').classList.add('d-none');
    document.getElementById('gitOutputConsole').innerText = '';

    const modalEl = document.getElementById('gitSyncModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
    refreshGitStatus();
}

function renderGitStatus(status) {
    if (!status) return;

    document.getElementById('gitBranchDisplay').innerText = status.branch || 'main';
    document.getElementById('gitBranchCode').innerText = status.branch || 'main';
    document.getElementById('gitLastHashDisplay').innerText = status.last_commit_hash || '-';
    document.getElementById('gitLastMsgDisplay').innerText = status.last_commit_message || '-';
    document.getElementById('gitChangedCountText').innerText = status.changed_count || 0;

    // Update header badge
    const headerBadge = document.getElementById('gitHeaderBadge');
    const pendingTotal = (status.changed_count || 0) + (status.ahead_count || 0);
    if (headerBadge) {
        if (pendingTotal > 0) {
            headerBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1';
            headerBadge.innerText = (status.changed_count > 0)
                ? `${status.changed_count} ไฟล์`
                : `รอ Push ${status.ahead_count}`;
        } else {
            headerBadge.className = 'badge bg-success-subtle text-success-emphasis border border-success-subtle ms-1';
            headerBadge.innerText = 'ซิงค์แล้ว ✓';
        }
    }

    const container = document.getElementById('gitFilesListContainer');
    const btnPush = document.getElementById('btnExecuteGitPush');
    const files = status.changed_files || [];

    if (files.length === 0) {
        if ((status.ahead_count || 0) > 0) {
            container.innerHTML = `
                <div class="text-center py-3 text-warning-emphasis small">
                    <i class="bi bi-cloud-arrow-up fs-4 d-block mb-1"></i>
                    มี <strong>${status.ahead_count} Commit</strong> ที่บันทึกไว้แล้วและกำลังรอส่งขึ้น GitHub (กดปุ่มด้านล่างเพื่อ Push ได้ทันที)
                </div>`;
            if (btnPush) btnPush.disabled = false;
        } else {
            container.innerHTML = `
                <div class="text-center py-3 text-success small">
                    <i class="bi bi-check-circle-fill fs-4 d-block mb-1"></i>
                    โค้ดทั้งหมดเป็นปัจจุบันตรงกับ GitHub แล้ว (Working tree clean — ไม่มีไฟล์ค้าง)
                </div>`;
            if (btnPush) btnPush.disabled = true;
        }
        return;
    }

    if (btnPush) btnPush.disabled = false;

    let html = '<div class="d-flex flex-column gap-1.5">';
    files.forEach(item => {
        const safeFile = item.file.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        html += `
            <div class="d-flex align-items-center justify-content-between py-1 px-2 rounded bg-body border small">
                <span class="font-monospace text-truncate me-2 text-body" style="max-width: 75%;" title="${safeFile}">${safeFile}</span>
                <span class="badge border ${item.badge || 'bg-secondary-subtle text-secondary-emphasis'}">${item.label || item.code}</span>
            </div>`;
    });
    html += '</div>';
    container.innerHTML = html;
}

function refreshGitStatus() {
    const container = document.getElementById('gitFilesListContainer');
    const btnRefresh = document.getElementById('btnRefreshGitStatus');
    if (btnRefresh) btnRefresh.disabled = true;
    if (container) {
        container.innerHTML = '<div class="text-center py-3 text-body-secondary small"><span class="spinner-border spinner-border-sm me-2"></span>กำลังตรวจสอบสถานะไฟล์กับ Git...</div>';
    }

    fetch('{{ route('backend.system_backup.git_status') }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (btnRefresh) btnRefresh.disabled = false;
        if (data && data.status) {
            renderGitStatus(data.status);
        }
    })
    .catch(() => {
        if (btnRefresh) btnRefresh.disabled = false;
        if (container) {
            container.innerHTML = '<div class="text-center py-3 text-danger small">ไม่สามารถดึงสถานะ Git ได้</div>';
        }
    });
}

function executeGitPush() {
    const msgInput = document.getElementById('gitCommitMessageInput');
    const commitMsg = msgInput ? msgInput.value.trim() : '';

    if (!confirm('ยืนยันบันทึกการเปลี่ยนแปลง (Commit) และอัปเดตโค้ดขึ้น GitHub ทันทีหรือไม่?')) {
        return;
    }

    const btn = document.getElementById('btnExecuteGitPush');
    const spinner = document.getElementById('gitPushSpinner');
    const icon = document.getElementById('gitPushIcon');
    const btnText = document.getElementById('gitPushBtnText');
    const outSec = document.getElementById('gitOutputSection');
    const outConsole = document.getElementById('gitOutputConsole');

    btn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');
    btnText.innerText = 'กำลัง Commit & Push ขึ้น GitHub...';
    outSec.classList.remove('d-none');
    outConsole.className = 'p-3 bg-dark text-info rounded-3 font-monospace mb-0';
    outConsole.innerText = 'กำลังรันคำสั่ง git add -A, git commit และ git push origin... กรุณารอสักครู่';

    fetch('{{ route('backend.system_backup.git_push') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ commit_message: commitMsg })
    })
    .then(res => res.json())
    .then(data => {
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        btnText.innerText = 'Commit & Push ขึ้น GitHub ทันที';

        if (data.status) {
            renderGitStatus(data.status);
        }

        if (data.success) {
            outConsole.className = 'p-3 bg-dark text-success rounded-3 font-monospace mb-0';
            outConsole.innerText = '✓ ' + (data.message || 'สำเร็จ') + '\n\n' + (data.output || '');
            if (msgInput) {
                msgInput.value = getDefaultCommitMessage();
            }
        } else {
            btn.disabled = false;
            outConsole.className = 'p-3 bg-dark text-danger rounded-3 font-monospace mb-0';
            outConsole.innerText = '✗ เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถ Push ขึ้น GitHub ได้');
        }
    })
    .catch(err => {
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        btn.disabled = false;
        btnText.innerText = 'Commit & Push ขึ้น GitHub ทันที';
        outConsole.className = 'p-3 bg-dark text-danger rounded-3 font-monospace mb-0';
        outConsole.innerText = '✗ เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
    });
}
</script>
@endsection
