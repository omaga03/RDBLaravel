@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แผงควบคุมระบบ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('backend.system_commands.index') }}" class="text-decoration-none">การดูแลระบบ</a></li>
            <li class="breadcrumb-item active" aria-current="page">ระบบตั้งเวลาทำงานอัตโนมัติ (Automated Task Scheduler)</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2.5">
            <div class="p-2.5 rounded-3 bg-primary-subtle text-primary border border-primary-subtle">
                <i class="bi bi-calendar2-range-fill fs-3"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="mb-0 fw-bold text-body">ศูนย์จัดการระบบตั้งเวลาทำงานอัตโนมัติ (Task Scheduler Hub)</h4>
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                        <i class="bi bi-cpu me-1"></i>Active Cron Runner
                    </span>
                </div>
                <p class="text-body-secondary small mb-0 mt-0.5">
                    ตรวจสอบรอบการประมวลผลงานอัตโนมัติ สั่งรันคำสั่งทันที (Run Now) และควบคุมระบบแจ้งเตือนผ่าน Telegram Bot
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#cronSetupGuideModal">
                <i class="bi bi-terminal-split"></i> คู่มือตั้งค่า Crontab
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#telegramSetupGuideModal">
                <i class="bi bi-telegram"></i> ตั้งค่า Telegram Bot
            </button>
            <a href="{{ route('backend.system_scheduler.index') }}" class="btn btn-secondary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-arrow-clockwise"></i> รีเฟรชสถานะ
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
            <i class="bi bi-x-circle-fill fs-5 me-2.5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 4 Diagnostics / Stat Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Active Tasks -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 bg-primary-subtle text-primary border border-primary-subtle flex-shrink-0">
                        <i class="bi bi-list-check fs-4"></i>
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-body-secondary small fw-medium d-block text-truncate">งานอัตโนมัติในระบบ</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-4 fw-bold text-body tabular-nums">{{ count($tasksWithRunInfo) }}</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">พร้อมทำงาน</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Next Due Job -->
        @php
            $earliestJob = collect($tasksWithRunInfo)->sortBy('next_run_carbon')->first();
        @endphp
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 bg-info-subtle text-info border border-info-subtle flex-shrink-0">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-body-secondary small fw-medium d-block text-truncate">รอบทำงานถัดไป</span>
                        <span class="fs-6 fw-bold text-body d-block text-truncate tabular-nums">
                            {{ $earliestJob['next_run_diff'] ?? '-' }}
                        </span>
                        <small class="text-body-secondary text-truncate d-block" style="font-size: 0.74rem;">
                            {{ $earliestJob['name'] ?? '-' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Server Time & Timezone -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 bg-warning-subtle text-warning border border-warning-subtle flex-shrink-0">
                        <i class="bi bi-stopwatch fs-4"></i>
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-body-secondary small fw-medium d-block text-truncate">เวลาเครื่องเซิร์ฟเวอร์</span>
                        <span class="fs-6 fw-bold text-body d-block text-truncate tabular-nums">
                            {{ now()->format('H:i:s') }} น. (ICT)
                        </span>
                        <small class="text-body-secondary text-truncate d-block" style="font-size: 0.74rem;">
                            {{ config('app.timezone') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Telegram Notification Status -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 {{ $telegramAdminConfigured ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} flex-shrink-0">
                        <i class="bi bi-telegram fs-4"></i>
                    </div>
                    <div class="overflow-hidden w-100">
                        <div class="d-flex align-items-center justify-content-between mb-0.5">
                            <span class="text-body-secondary small fw-medium text-truncate">แจ้งเตือน Telegram</span>
                            @if($telegramAdminConfigured)
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">ออนไลน์</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.65rem;">รอตั้งค่า</span>
                            @endif
                        </div>
                        @if($telegramAdminConfigured)
                            <div class="d-flex align-items-center justify-content-between gap-1 mt-1">
                                <span class="small text-body-secondary font-monospace" style="font-size: 0.75rem;">ID: {{ Str::mask($adminChatId, '*', 2, -2) }}</span>
                                <form action="{{ route('backend.system_scheduler.test_telegram') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-xs px-2 py-0.5" style="font-size: 0.7rem;" title="ส่งข้อความทดสอบเข้า Telegram">
                                        <i class="bi bi-send me-1"></i>ทดสอบ
                                    </button>
                                </form>
                            </div>
                        @else
                            <button type="button" class="btn btn-link btn-xs p-0 text-start text-primary" data-bs-toggle="modal" data-bs-target="#telegramSetupGuideModal" style="font-size: 0.75rem;">
                                <i class="bi bi-gear-fill me-1"></i>คลิกดูวิธีตั้งค่าบอท
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Console Output Preview Window (if recently run) -->
    @if(session('command_output'))
        <div class="card border mb-4 shadow-sm bg-body overflow-hidden">
            <div class="card-header bg-dark text-light d-flex align-items-center justify-content-between py-2.5 px-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex gap-1.5 me-2">
                        <span class="rounded-circle bg-danger" style="width: 10px; height: 10px;"></span>
                        <span class="rounded-circle bg-warning" style="width: 10px; height: 10px;"></span>
                        <span class="rounded-circle bg-success" style="width: 10px; height: 10px;"></span>
                    </span>
                    <span class="fw-bold font-monospace small">
                        <i class="bi bi-terminal me-1 text-warning"></i>
                        {{ session('executed_task_name') ?: 'Artisan Console Output' }}
                    </span>
                    @if(session('executed_command'))
                        <span class="badge bg-secondary font-monospace" style="font-size: 0.72rem;">{{ session('executed_command') }}</span>
                    @endif
                </div>
                <span class="badge bg-{{ session('command_status') === 'success' ? 'success' : (session('command_status') === 'warning' ? 'warning' : 'danger') }}">
                    {{ session('command_status') === 'success' ? 'Status: 0 (SUCCESS)' : 'Execution Completed' }}
                </span>
            </div>
            <div class="card-body p-3 bg-dark">
                <pre class="mb-0 text-light font-monospace small" style="max-height: 280px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; font-size: 0.82rem;">{{ session('command_output') }}</pre>
            </div>
        </div>
    @endif

    <!-- Main Scheduled Tasks Table -->
    <div class="card border shadow-sm bg-body mb-4">
        <div class="card-header bg-body-tertiary border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-list-stars text-primary fs-5"></i>
                <h5 class="card-title fw-bold text-body mb-0">รายการงานประมวลผลอัตโนมัติ (Registered Cron Jobs)</h5>
            </div>
            <span class="small text-body-secondary">
                <i class="bi bi-info-circle me-1"></i>ระบบจะสั่งรันคำสั่งเหล่านี้ตามความถี่ที่กำหนดไว้ใน <code>routes/console.php</code>
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-body-secondary" style="font-size: 0.8rem; letter-spacing: 0.03em;">
                    <tr>
                        <th style="width: 38%;">ชื่องาน / คำสั่งระบบ (Task &amp; Command)</th>
                        <th style="width: 22%;">ความถี่การทำงาน (Frequency)</th>
                        <th style="width: 24%;">รอบถัดไป (Next Due Date)</th>
                        <th style="width: 16%;" class="text-end pe-3">สั่งการ (Action)</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($tasksWithRunInfo as $task)
                        <tr>
                            <td>
                                <div class="d-flex align-items-start gap-2.5">
                                    <div class="p-2 rounded-2 {{ $task['badge_class'] }} mt-0.5 flex-shrink-0">
                                        <i class="bi {{ $task['icon'] }} fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                            <span class="fw-bold text-body">{{ $task['name'] }}</span>
                                            <span class="badge {{ $task['badge_class'] }}" style="font-size: 0.68rem;">{{ $task['category'] }}</span>
                                        </div>
                                        <code class="px-2 py-0.5 rounded bg-body-tertiary text-primary-emphasis border border-subtle d-inline-block font-monospace small mb-1" style="font-size: 0.78rem;">
                                            php artisan {{ $task['command'] }}
                                        </code>
                                        <p class="text-body-secondary small mb-0" style="font-size: 0.79rem; line-height: 1.4;">
                                            {{ $task['description'] }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-semibold text-body mb-1" style="font-size: 0.85rem;">
                                        <i class="bi bi-repeat me-1 text-primary"></i>{{ $task['schedule_text'] }}
                                    </div>
                                    <span class="badge bg-body-secondary text-body border font-monospace tabular-nums" style="font-size: 0.74rem;">
                                        {{ $task['expression'] }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle mb-1 tabular-nums" style="font-size: 0.74rem;">
                                        <i class="bi bi-hourglass-split me-1"></i>{{ $task['next_run_diff'] }}
                                    </span>
                                    <small class="text-body-secondary d-block tabular-nums" style="font-size: 0.78rem;">
                                        {{ $task['next_run_thai'] }}
                                    </small>
                                </div>
                            </td>
                            <td class="text-end pe-3">
                                <form action="{{ route('backend.system_scheduler.run') }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการสั่งรันงาน \'{{ $task['name'] }}\' ทันทีหรือไม่?\n(คำสั่งจะเริ่มประมวลผลบนเซิร์ฟเวอร์)');">
                                    @csrf
                                    <input type="hidden" name="task_id" value="{{ $task['id'] }}">
                                    <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm d-inline-flex align-items-center gap-1.5" title="สั่งรันงานนี้ทันที">
                                        <i class="bi bi-lightning-charge-fill"></i>
                                        <span>รันทันที</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-body-secondary">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                ไม่พบรายการงานตั้งเวลาอัตโนมัติในระบบ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Server Maintenance Link Strip -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border shadow-sm bg-body h-100">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2.5 rounded-3 bg-danger-subtle text-danger border border-danger-subtle">
                            <i class="bi bi-shield-shaded fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-body mb-0.5">ศูนย์สำรองและกู้คืนข้อมูลระบบ (Disaster Recovery Hub)</h6>
                            <small class="text-body-secondary">จัดการแพ็กเกจสำรองฐานข้อมูล กู้คืนระบบผ่าน OTP และซิงค์ Google Drive</small>
                        </div>
                    </div>
                    <a href="{{ route('backend.system_backup.index') }}" class="btn btn-outline-danger btn-sm text-nowrap px-3">
                        เปิดหน้าสำรอง <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border shadow-sm bg-body h-100">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2.5 rounded-3 bg-warning-subtle text-warning border border-warning-subtle">
                            <i class="bi bi-terminal-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-body mb-0.5">ศูนย์จัดการคำสั่งระบบและการบำรุงรักษา (System Commands)</h6>
                            <small class="text-body-secondary">ล้างแคช ตรวจสอบสถานะเอ็นจิน PDF Cover และส่งออกข้อมูลวิชาการ</small>
                        </div>
                    </div>
                    <a href="{{ route('backend.system_commands.index') }}" class="btn btn-outline-warning btn-sm text-nowrap px-3">
                        เปิดหน้าคำสั่ง <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Crontab Setup Guide -->
<div class="modal fade" id="cronSetupGuideModal" tabindex="-1" aria-labelledby="cronSetupGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-body-tertiary border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-2 bg-info-subtle text-info">
                        <i class="bi bi-terminal-split fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body" id="cronSetupGuideModalLabel">คู่มือการเปิดใช้งาน Laravel Task Scheduling บนเซิร์ฟเวอร์</h5>
                        <small class="text-body-secondary">ตั้งค่าเพียง 1 บรรทัดบน Cron เพื่อให้งานอัตโนมัติทั้ง 6 ตัวทำงานตลอด 24 ชั่วโมง</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 small">
                <div class="alert alert-info-subtle border border-info-subtle d-flex align-items-center mb-3 p-3 rounded-3">
                    <i class="bi bi-info-circle-fill text-info fs-5 me-2.5"></i>
                    <div>
                        <strong>สถาปัตยกรรม Laravel Scheduler:</strong> ระบบต้องการเพียง Cron Job บรรทัดเดียวที่รันคำสั่ง <code>php artisan schedule:run</code> ทุก 1 นาที จากนั้น Laravel จะตรวจสอบตารางเวลาและเรียกงานแต่ละตัวตามเวลาที่กำหนดโดยอัตโนมัติ
                    </div>
                </div>

                <h6 class="fw-bold text-body mb-2">1. ตั้งค่าบน Linux Server (Ubuntu / CentOS Production):</h6>
                <p class="text-body-secondary mb-1.5">เปิด Terminal ในฐานะผู้ใช้เว็บเซิร์ฟเวอร์ (เช่น <code>www-data</code> หรือ <code>root</code>) แล้วพิมพ์คำสั่ง:</p>
                <pre class="p-2.5 bg-dark text-warning rounded mb-2 font-monospace" style="font-size: 0.8rem;">crontab -e</pre>
                <p class="text-body-secondary mb-1.5">เพิ่มบรรทัดนี้ลงท้ายไฟล์:</p>
                <pre class="p-2.5 bg-dark text-success rounded mb-3 font-monospace" style="font-size: 0.8rem;">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</pre>

                <h6 class="fw-bold text-body mb-2">2. ทดสอบจำลองการทำงานบนเครื่อง Development / Laragon (Windows):</h6>
                <p class="text-body-secondary mb-1.5">เปิด Terminal ในโฟลเดอร์โปรเจกต์ แล้วรันคำสั่ง Worker จำลอง:</p>
                <pre class="p-2.5 bg-dark text-light rounded mb-3 font-monospace" style="font-size: 0.8rem;">php artisan schedule:work</pre>
                <p class="text-body-secondary mb-0">คำสั่งนี้จะรันค้างไว้ใน Terminal และจะเรียก <code>schedule:run</code> อัตโนมัติทุก 1 นาที เพื่อให้ท่านทดสอบพฤติกรรมของระบบเหมือนอยู่บน Production จริง</p>
            </div>
            <div class="modal-footer bg-body-tertiary border-top py-2.5">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Telegram Setup Guide -->
<div class="modal fade" id="telegramSetupGuideModal" tabindex="-1" aria-labelledby="telegramSetupGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-body-tertiary border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-2 bg-primary-subtle text-primary">
                        <i class="bi bi-telegram fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body" id="telegramSetupGuideModalLabel">การเชื่อมต่อระบบแจ้งเตือนผ่าน Telegram Bot</h5>
                        <small class="text-body-secondary">แจ้งเตือน Super Admin ทันทีเมื่อมีการ เพิ่ม/ลบ/แก้ไข/อัปโหลดไฟล์ และสำรองข้อมูล</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 small">
                <h6 class="fw-bold text-body mb-2">ขั้นตอนที่ 1: สร้าง Telegram Bot รับ Token</h6>
                <ol class="ps-3 text-body-secondary mb-3">
                    <li>เปิดแอป Telegram แล้วค้นหาบัญชี <strong>@BotFather</strong></li>
                    <li>พิมพ์คำสั่ง <code>/newbot</code> แล้วตั้งชื่อบอท เช่น <code>PCRU RDB Notifier</code> และชื่อ username เช่น <code>pcru_rdb_bot</code></li>
                    <li>@BotFather จะส่ง <strong>HTTP API Token</strong> (เช่น <code>123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ</code>)</li>
                </ol>

                <h6 class="fw-bold text-body mb-2">ขั้นตอนที่ 2: ค้นหา Chat ID ของ Admin</h6>
                <ol class="ps-3 text-body-secondary mb-3">
                    <li>ค้นหาบัญชี <strong>@userinfobot</strong> ใน Telegram แล้วกด Start</li>
                    <li>บอทจะแจ้งหมายเลข <strong>Id: 123456789</strong> ของท่านทันที</li>
                    <li>เปิดแชทกับบอทที่ท่านสร้างไว้ในขั้นตอนที่ 1 แล้วกด <strong>/start</strong> เพื่อเปิดรับข้อความ</li>
                </ol>

                <h6 class="fw-bold text-body mb-2">ขั้นตอนที่ 3: ระบุค่าลงในไฟล์ <code>.env</code> ของระบบ:</h6>
                <pre class="p-3 bg-dark text-warning rounded mb-3 font-monospace" style="font-size: 0.8rem;">TELEGRAM_BOT_TOKEN="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ"
TELEGRAM_ADMIN_CHAT_ID="123456789"
TELEGRAM_NOTIFICATIONS_ENABLED=true
TELEGRAM_TIMEOUT=4</pre>

                <h6 class="fw-bold text-body mb-2">ขั้นตอนที่ 4: การแจ้งเตือนส่วนบุคคลของนักวิจัย / ผู้ใช้งาน</h6>
                <p class="text-body-secondary mb-0">
                    นักวิจัยหรือผู้ใช้ทั่วไปสามารถนำ <code>Chat ID</code> ของตนเองมากรอกในหน้า <strong>จัดการผู้ใช้งาน (Users)</strong> หรือข้อมูลส่วนตัว เพื่อเปิดรับการแจ้งเตือนผลการอนุมัติงานวิจัย และงานมอบหมายประเมินผ่าน Telegram ควบคู่กับอีเมลได้โดยตรง
                </p>
            </div>
            <div class="modal-footer bg-body-tertiary border-top py-2.5 d-flex justify-content-between">
                <form action="{{ route('backend.system_scheduler.test_telegram') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm px-3" {{ !$telegramAdminConfigured ? 'disabled' : '' }}>
                        <i class="bi bi-send me-1"></i>ส่งข้อความทดสอบตอนนี้
                    </button>
                </form>
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>
@endsection
