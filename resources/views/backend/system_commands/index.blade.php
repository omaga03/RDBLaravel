@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แผงควบคุมระบบ</a></li>
                    <li class="breadcrumb-item">ผู้ดูแลระบบ</li>
                    <li class="breadcrumb-item active" aria-current="page">คำสั่งระบบและบำรุงรักษา (System Commands)</li>
                </ol>
            </nav>
            <h2 class="mb-1 fw-bold text-body">
                <i class="bi bi-terminal-fill me-2 text-warning"></i>ศูนย์จัดการคำสั่งระบบและการบำรุงรักษา (System Commands &amp; Maintenance)
            </h2>
            <p class="text-body-secondary mb-0">เครื่องมือควบคุมการประมวลผลไฟล์ PDF, การเรนเดอร์ภาพหน้าแรก (.webp), การนับหน้าเอกสาร และการล้างแคชเพิ่มประสิทธิภาพ</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#engineSetupModal">
                <i class="bi bi-tools"></i> วิธีติดตั้งเอนจิน PDF &amp; ส่วนขยาย
            </button>
            <a href="{{ route('backend.system_commands.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5">
                <i class="bi bi-arrow-clockwise"></i> รีเฟรชสถานะ
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2.5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="bi bi-exclamation-octagon-fill fs-5 me-2.5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 1. System Health & Engine Overview Strip -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Active Rasterizer Engine -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold text-body-secondary"><i class="bi bi-cpu-fill me-1 text-primary"></i>เอนจินเรนเดอร์หน้าแรก PDF</span>
                        @if($engineInfo['pymupdf_available'])
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                <i class="bi bi-check-circle me-1"></i>MuPDF Ready
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                <i class="bi bi-exclamation-circle me-1"></i>GD Fallback
                            </span>
                        @endif
                    </div>
                    <div class="small fw-bold text-body text-truncate mb-1" title="{{ $engineInfo['active_cover_engine'] }}">
                        {{ $engineInfo['active_cover_engine'] }}
                    </div>
                    <div class="text-body-tertiary small d-flex justify-content-between align-items-center" style="font-size: 0.78rem;">
                        <span>Python: <code class="text-body-secondary">{{ basename($engineInfo['python_executable']) }}</code></span>
                        <a href="javascript:void(0)" class="text-primary text-decoration-none" onclick="openEngineModal('tab-mupdf-btn')"><i class="bi bi-question-circle me-1"></i>วิธีติดตั้ง</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: PDF Page Counting Tools -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold text-body-secondary"><i class="bi bi-file-earmark-binary me-1 text-info"></i>เครื่องมือนับหน้า PDF</span>
                        @if($engineInfo['pdftotext_available'])
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" title="Poppler pdftotext พร้อมใช้งาน แม่นยำ 99.99%">
                                <i class="bi bi-check-circle-fill me-1"></i>Poppler Ready
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" title="ไม่พบ Poppler pdftotext สลับไปใช้ Pure PHP แทน">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Pure PHP (Tier 2-4)
                            </span>
                        @endif
                    </div>
                    <div class="small fw-bold text-body mb-1">
                        @if($engineInfo['pdftotext_available'])
                            Poppler pdftotext (Tier 1 แม่นยำ 99.99%)
                        @else
                            Pure PHP Parser (Smalot / FPDI)
                        @endif
                    </div>
                    <div class="text-body-tertiary small d-flex justify-content-between align-items-center" style="font-size: 0.78rem;">
                        <span>
                            @if($engineInfo['pdftotext_available'])
                                ไบนารี: <code class="text-body-secondary" title="{{ $engineInfo['pdftotext_binary'] }}">{{ basename($engineInfo['pdftotext_binary']) }}</code>
                                <span class="text-success ms-1">({{ basename(dirname($engineInfo['pdftotext_binary'])) }})</span>
                            @else
                                Poppler: <span class="text-danger">ไม่พบ</span> (ใช้ Smalot {{ $engineInfo['smalot_available'] ? '✓' : '✗' }})
                            @endif
                        </span>
                        <a href="javascript:void(0)" class="text-primary text-decoration-none" onclick="openEngineModal('tab-poppler-btn')"><i class="bi bi-question-circle me-1"></i>วิธีติดตั้ง</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Storage & Symlink -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold text-body-secondary"><i class="bi bi-hdd-network me-1 text-success"></i>พื้นที่จัดเก็บไฟล์ (Storage)</span>
                        @if($engineInfo['storage_symlink_ok'])
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                <i class="bi bi-link-45deg me-1"></i>Symlink เชื่อมต่อแล้ว
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                <i class="bi bi-exclamation-triangle me-1"></i>Symlink หลุด
                            </span>
                        @endif
                    </div>
                    <div class="small fw-bold text-body mb-1">
                        ดิสก์เก็บข้อมูล: {{ $engineInfo['free_disk_space'] }}
                    </div>
                    <div class="text-body-tertiary small" style="font-size: 0.78rem;">
                        พาธ: <code>public/storage ➔ storage/app/public</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: PHP Graphic & WebP Support -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold text-body-secondary"><i class="bi bi-image me-1 text-warning"></i>การประมวลผล WebP / GD</span>
                        @if($engineInfo['gd_loaded'] && $engineInfo['gd_webp'])
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                <i class="bi bi-check-circle me-1"></i>WebP รองรับสมบูรณ์
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                ไม่รองรับ WebP
                            </span>
                        @endif
                    </div>
                    <div class="small fw-bold text-body mb-1">
                        PHP GD Extension &amp; FreeType TTF
                    </div>
                    <div class="text-body-tertiary small d-flex justify-content-between align-items-center" style="font-size: 0.78rem;">
                        <span>ฟอนต์: <code>Leelawadee UI / Sarabun</code></span>
                        <a href="javascript:void(0)" class="text-primary text-decoration-none" onclick="openEngineModal('tab-gd-btn')"><i class="bi bi-question-circle me-1"></i>วิธีติดตั้ง</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1.2 Advanced Server Health & Production Diagnostics Strip -->
    <div class="row g-3 mb-4">
        <!-- Health Card 1: Database Status & Latency -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold text-body-secondary"><i class="bi bi-database-fill-check me-1 text-primary"></i>ระบบฐานข้อมูล (Database)</span>
                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                            <i class="bi bi-shield-check me-1"></i>{{ $healthInfo['db_status'] }}
                        </span>
                    </div>
                    <div class="small fw-bold text-body text-truncate mb-1" title="{{ $healthInfo['db_name'] }}">
                        {{ $healthInfo['db_name'] }}
                    </div>
                    <div class="text-body-tertiary small d-flex justify-content-between align-items-center" style="font-size: 0.78rem;">
                        <span class="text-truncate" title="{{ $healthInfo['db_version'] }}">Engine: <code class="text-body-secondary">{{ \Illuminate\Support\Str::limit($healthInfo['db_version'], 18) }}</code></span>
                        <span class="badge bg-body-secondary text-body-secondary border border-secondary-subtle">utf8mb4</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Card 2: Queue Workers & Asynchronous Jobs -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold text-body-secondary"><i class="bi bi-layers-fill me-1 text-info"></i>คิวงานประมวลผล (Queue)</span>
                        @if($healthInfo['failed_jobs'] > 0)
                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>ตกค้าง {{ $healthInfo['failed_jobs'] }} งาน
                            </span>
                        @else
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                <i class="bi bi-check2-circle me-1"></i>ปกติ ไร้งานตกค้าง
                            </span>
                        @endif
                    </div>
                    <div class="small fw-bold text-body mb-1">
                        Driver: <code class="text-primary">{{ $healthInfo['queue_driver'] }}</code> (รันงานพื้นหลัง)
                    </div>
                    <div class="text-body-tertiary small d-flex justify-content-between align-items-center" style="font-size: 0.78rem;">
                        <span>รอดำเนินการ: <b class="text-body">{{ $healthInfo['pending_jobs'] }}</b></span>
                        <span>ผิดพลาด: <b class="{{ $healthInfo['failed_jobs'] > 0 ? 'text-danger' : 'text-body' }}">{{ $healthInfo['failed_jobs'] }}</b></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Card 3: Cron Task Scheduler -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold text-body-secondary"><i class="bi bi-clock-history me-1 text-success"></i>ระบบตั้งเวลา (Scheduler)</span>
                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                            <i class="bi bi-check-all me-1"></i>{{ $healthInfo['schedule_count'] }} งานพร้อมรัน
                        </span>
                    </div>
                    <div class="small fw-bold text-body mb-1">
                        สำรองข้อมูลอัตโนมัติ: <span class="text-success">02:00 น. ทุกวัน</span>
                    </div>
                    <div class="text-body-tertiary small d-flex justify-content-between align-items-center" style="font-size: 0.78rem;">
                        <span>สำรองเต็มระบบ: <span class="text-body">อาทิตย์ 03:00 น.</span></span>
                        <span>NRIIS: <span class="text-body">06:00 น.</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Card 4: Production Readiness & Memory Limit -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border shadow-sm bg-body">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold text-body-secondary"><i class="bi bi-shield-lock-fill me-1 text-warning"></i>ความพร้อมระบบ (Production)</span>
                        @if($healthInfo['app_debug'])
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" title="ควรปิด APP_DEBUG เมื่อใช้งาน Production">
                                <i class="bi bi-exclamation-triangle me-1"></i>Debug เปิดอยู่
                            </span>
                        @else
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                <i class="bi bi-shield-check me-1"></i>Production Hardened
                            </span>
                        @endif
                    </div>
                    <div class="small fw-bold text-body mb-1">
                        PHP {{ $healthInfo['php_version'] }} (RAM: {{ $healthInfo['memory_limit'] }})
                    </div>
                    <div class="text-body-tertiary small d-flex justify-content-between align-items-center" style="font-size: 0.78rem;">
                        <span>Env: <code class="text-body-secondary">{{ $healthInfo['app_env'] }}</code></span>
                        <span>Debug: <code class="{{ $healthInfo['app_debug'] ? 'text-warning fw-bold' : 'text-success' }}">{{ $healthInfo['app_debug'] ? 'true' : 'false' }}</code></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <!-- Project Coverage Card -->
        <div class="col-md-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2.5 px-3">
                    <h6 class="mb-0 fw-bold text-body">
                        <i class="bi bi-folder2-open text-primary me-2"></i>ความครอบคลุมโครงการวิจัย (Research Projects)
                    </h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-variant-numeric: tabular-nums;">
                        {{ number_format($projectTotalWithFile) }} โครงการที่มีไฟล์
                    </span>
                </div>
                <div class="card-body p-3">
                    @php
                        $projCoverPct = $projectTotalWithFile > 0 ? round(($projectWithCover / $projectTotalWithFile) * 100, 1) : 0;
                        $projPagePct = $projectTotalWithFile > 0 ? round(($projectWithPageNum / $projectTotalWithFile) * 100, 1) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center small mb-1">
                            <span class="fw-semibold text-body"><i class="bi bi-image me-1 text-success"></i>ภาพหน้าปก (.webp)</span>
                            <span class="fw-bold text-body" style="font-variant-numeric: tabular-nums;">
                                {{ number_format($projectWithCover) }} / {{ number_format($projectTotalWithFile) }} ({{ $projCoverPct }}%)
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $projCoverPct }}%;" aria-valuenow="{{ $projCoverPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center small mb-1">
                            <span class="fw-semibold text-body"><i class="bi bi-file-earmark-text me-1 text-info"></i>บันทึกจำนวนหน้าเอกสาร (Page Count)</span>
                            <span class="fw-bold text-body" style="font-variant-numeric: tabular-nums;">
                                {{ number_format($projectWithPageNum) }} / {{ number_format($projectTotalWithFile) }} ({{ $projPagePct }}%)
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $projPagePct }}%;" aria-valuenow="{{ $projPagePct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Published Coverage Card -->
        <div class="col-md-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2.5 px-3">
                    <h6 class="mb-0 fw-bold text-body">
                        <i class="bi bi-journal-richtext text-success me-2"></i>ความครอบคลุมผลงานตีพิมพ์ (Published Articles)
                    </h6>
                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-variant-numeric: tabular-nums;">
                        {{ number_format($publishedTotalWithFile) }} ผลงานที่มีไฟล์
                    </span>
                </div>
                <div class="card-body p-3">
                    @php
                        $pubCoverPct = $publishedTotalWithFile > 0 ? round(($publishedWithCover / $publishedTotalWithFile) * 100, 1) : 0;
                        $pubPagePct = $publishedTotalWithFile > 0 ? round(($publishedWithPageNum / $publishedTotalWithFile) * 100, 1) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center small mb-1">
                            <span class="fw-semibold text-body"><i class="bi bi-image me-1 text-success"></i>ภาพหน้าปก (.webp)</span>
                            <span class="fw-bold text-body" style="font-variant-numeric: tabular-nums;">
                                {{ number_format($publishedWithCover) }} / {{ number_format($publishedTotalWithFile) }} ({{ $pubCoverPct }}%)
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pubCoverPct }}%;" aria-valuenow="{{ $pubCoverPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center small mb-1">
                            <span class="fw-semibold text-body"><i class="bi bi-file-earmark-text me-1 text-info"></i>บันทึกจำนวนหน้าเอกสาร (Page Count)</span>
                            <span class="fw-bold text-body" style="font-variant-numeric: tabular-nums;">
                                {{ number_format($publishedWithPageNum) }} / {{ number_format($publishedTotalWithFile) }} ({{ $pubPagePct }}%)
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $pubPagePct }}%;" aria-valuenow="{{ $pubPagePct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Terminal Output Display (If previous command ran) -->
    @if(session('command_output'))
        <div class="card border shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between py-2.5 px-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ session('command_status', 'success') }}">
                        <i class="bi bi-terminal me-1"></i>{{ session('command_name') }}
                    </span>
                    <span class="small text-white-50">ผลลัพธ์การประมวลผลคำสั่งล่าสุด (Execution Output)</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" onclick="navigator.clipboard.writeText(document.getElementById('commandOutputText').innerText); alert('คัดลอกข้อความผลลัพธ์แล้ว');">
                    <i class="bi bi-clipboard me-1"></i>คัดลอก Log
                </button>
            </div>
            <div class="card-body p-0">
                <pre class="mb-0 p-3 bg-black text-success rounded-bottom overflow-auto" id="commandOutputText" style="max-height: 340px; font-family: Consolas, 'Cascadia Code', monospace; font-size: 0.82rem; line-height: 1.5;">{{ session('command_output') }}</pre>
            </div>
        </div>
    @endif

    <!-- 4. Interactive Command Runners (Cards Grid) -->
    <div class="row g-4">
        <!-- Command 1: Generate PDF Covers -->
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary d-flex align-items-center gap-2 py-3 px-3">
                    <div class="p-2 rounded-2 bg-primary-subtle text-primary">
                        <i class="bi bi-images fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-body">1. คำสั่งดึงและสร้างภาพหน้าแรกของ PDF (.webp)</h6>
                        <small class="text-body-secondary">คำสั่ง: <code>php artisan rdb:generate-pdf-covers</code></small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <p class="small text-body-secondary mb-3">
                        เรนเดอร์หน้าแรกของไฟล์ PDF รายงานวิจัยและผลงานตีพิมพ์ทั้งหน้าเป็นไฟล์ภาพ <code>.webp</code> คุณภาพสูง คมชัดทุกตัวอักษรและตาราง พร้อมระบบสร้างหน้าปกสำรองสไตล์วิชาการภาษาไทยอัตโนมัติ
                    </p>

                    <form action="{{ route('backend.system_commands.run') }}" method="POST" onsubmit="return confirm('ยืนยันเริ่มประมวลผลการสร้างภาพหน้าแรกของ PDF?');">
                        @csrf
                        <input type="hidden" name="command" value="generate_covers">

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">เลือกโมดูล:</label>
                                <select name="module" class="form-select form-select-sm">
                                    <option value="all">ทั้งหมด (โครงการ + ตีพิมพ์)</option>
                                    <option value="project">เฉพาะ โครงการวิจัย (rdb_project)</option>
                                    <option value="published">เฉพาะ ผลงานตีพิมพ์ (rdb_published)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">จำนวนรายการ (Limit):</label>
                                <select name="limit" class="form-select form-select-sm">
                                    <option value="10">ทดสอบ 10 รายการแรก</option>
                                    <option value="50">50 รายการ</option>
                                    <option value="100">100 รายการ</option>
                                    <option value="all" selected>ทั้งหมดในระบบ (Full System)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3 p-2.5 bg-body-tertiary rounded border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="force_cover" name="force" value="1">
                            <label class="form-check-label small fw-semibold text-body" for="force_cover">
                                บังคับสร้างใหม่ทับของเดิม (--force)
                                <div class="text-body-secondary fw-normal" style="font-size: 0.75rem;">สร้างภาพหน้าปกใหม่ทุกรายการ แม้จะมีภาพเดิมอยู่แล้ว</div>
                            </label>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <div class="small text-body-tertiary" style="font-size: 0.76rem;">
                                <i class="bi bi-info-circle me-1"></i>ใช้เวลา ~20–40 วินาทีในโหมดเต็ม
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 px-3">
                                <i class="bi bi-play-circle-fill"></i> รันคำสั่งสร้างภาพหน้าปก
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Command 2: Count PDF Pages -->
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary d-flex align-items-center gap-2 py-3 px-3">
                    <div class="p-2 rounded-2 bg-info-subtle text-info">
                        <i class="bi bi-123 fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-body">2. คำสั่งตรวจนับจำนวนหน้าไฟล์ PDF ย้อนหลัง</h6>
                        <small class="text-body-secondary">คำสั่ง: <code>php artisan rdb:count-pdf-pages</code></small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <p class="small text-body-secondary mb-3">
                        ตรวจนับจำนวนหน้าของไฟล์ PDF รายงานวิจัยและผลงานตีพิมพ์ทั้งระบบด้วยสถาปัตยกรรม 4-Tier Fallback (Poppler pdftotext, Smalot, Fpdi, Fast Regex) และบันทึกลงฐานข้อมูลอัตโนมัติ
                    </p>

                    <form action="{{ route('backend.system_commands.run') }}" method="POST" onsubmit="return confirm('ยืนยันเริ่มประมวลผลการนับจำนวนหน้า PDF?');">
                        @csrf
                        <input type="hidden" name="command" value="count_pages">

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">เลือกโมดูล:</label>
                                <select name="module" class="form-select form-select-sm">
                                    <option value="all">ทั้งหมด (โครงการ + ตีพิมพ์)</option>
                                    <option value="project">เฉพาะ โครงการวิจัย (rdb_project)</option>
                                    <option value="published">เฉพาะ ผลงานตีพิมพ์ (rdb_published)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">จำนวนรายการ (Limit):</label>
                                <select name="limit" class="form-select form-select-sm">
                                    <option value="20">ทดสอบ 20 รายการแรก</option>
                                    <option value="100">100 รายการ</option>
                                    <option value="500">500 รายการ</option>
                                    <option value="all" selected>ทั้งหมดในระบบ (Full System)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3 p-2.5 bg-body-tertiary rounded border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="force_count" name="force" value="1">
                            <label class="form-check-label small fw-semibold text-body" for="force_count">
                                บังคับนับใหม่ทับค่าเดิม (--force)
                                <div class="text-body-secondary fw-normal" style="font-size: 0.75rem;">สแกนนับจำนวนหน้าใหม่ทุกไฟล์ แม้จะมีจำนวนหน้าบันทึกอยู่แล้ว</div>
                            </label>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <div class="small text-body-tertiary" style="font-size: 0.76rem;">
                                <i class="bi bi-info-circle me-1"></i>บันทึกผลลง <code>pro_page_num</code> และ <code>pub_numfile</code>
                            </div>
                            <button type="submit" class="btn btn-info btn-sm text-dark d-inline-flex align-items-center gap-1.5 px-3">
                                <i class="bi bi-play-circle-fill"></i> รันคำสั่งนับจำนวนหน้า
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Command 3: Cache Management & Optimization -->
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary d-flex align-items-center gap-2 py-3 px-3">
                    <div class="p-2 rounded-2 bg-warning-subtle text-warning">
                        <i class="bi bi-lightning-charge-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-body">3. คำสั่งล้างแคชและรีเซ็ตระบบ (System Cache Clear)</h6>
                        <small class="text-body-secondary">คำสั่ง: <code>cache:clear</code>, <code>route:clear</code>, <code>view:clear</code></small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <p class="small text-body-secondary mb-3">
                        ล้างแคชคำนวณสถิติ แดชบอร์ด การคอมไพล์เทมเพลต Blade และแคชของเส้นทาง (Routes) เพื่อให้ระบบอ่านค่าการตั้งค่าและโค้ดล่าสุดทันที เหมาะสำหรับใช้หลังแก้ไขข้อมูลหรืออัปเดตระบบ
                    </p>

                    <form action="{{ route('backend.system_commands.run') }}" method="POST" onsubmit="return confirm('ยืนยันล้างแคชระบบทั้งหมด?');">
                        @csrf
                        <input type="hidden" name="command" value="clear_cache">

                        <div class="p-3 bg-body-tertiary rounded-3 border mb-3">
                            <div class="small fw-semibold text-body mb-2"><i class="bi bi-check2-all text-success me-1"></i>รายการแคชที่จะถูกล้าง:</div>
                            <ul class="small text-body-secondary mb-0 ps-3">
                                <li>Application Cache (ตัวเลขสถิติและผลลัพธ์คิวรี)</li>
                                <li>Blade Compiled Views (หน้าจอ UI ทั้งหน้าบ้านและหลังบ้าน)</li>
                                <li>Route Cache (ตารางเส้นทาง URL)</li>
                                <li>Configuration Cache (ไฟล์ตั้งค่า <code>.env</code>)</li>
                            </ul>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <div class="small text-body-tertiary" style="font-size: 0.76rem;">
                                <i class="bi bi-shield-check text-success me-1"></i>ปลอดภัย ไม่กระทบฐานข้อมูล
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1.5 px-3">
                                <i class="bi bi-trash3-fill"></i> ล้างแคชระบบทันที
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Command 4: System CLI Quick Reference -->
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary d-flex align-items-center gap-2 py-3 px-3">
                    <div class="p-2 rounded-2 bg-secondary-subtle text-secondary">
                        <i class="bi bi-code-slash fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-body">4. คู่มือคำสั่งผ่านหน้าต่าง Terminal / Shell (SysAdmin CLI)</h6>
                        <small class="text-body-secondary">สำหรับผู้ดูแลระบบที่ต้องการรันคำสั่งผ่าน SSH หรือ CMD</small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <p class="small text-body-secondary mb-2">
                        หากต้องการรันคำสั่งขนาดใหญ่ในโหมดพื้นหลัง (Background) หรือตั้ง Cron Job อัตโนมัติ สามารถใช้คำสั่ง Artisan ได้ดังนี้:
                    </p>

                    <div class="mb-2">
                        <small class="text-body fw-bold d-block mb-1">สร้างภาพหน้าปก PDF ทั้งระบบ:</small>
                        <pre class="p-2 bg-dark text-light rounded small mb-2" style="font-size: 0.75rem;">php artisan rdb:generate-pdf-covers --module=all --force</pre>
                    </div>

                    <div class="mb-2">
                        <small class="text-body fw-bold d-block mb-1">นับจำนวนหน้าเอกสาร PDF ทั้งระบบ:</small>
                        <pre class="p-2 bg-dark text-light rounded small mb-2" style="font-size: 0.75rem;">php artisan rdb:count-pdf-pages --module=all --force</pre>
                    </div>

                    <div>
                        <small class="text-body fw-bold d-block mb-1">ตรวจสอบโครงสร้างและสถานะการเชื่อมต่อ:</small>
                        <pre class="p-2 bg-dark text-light rounded small mb-0" style="font-size: 0.75rem;">php artisan storage:link && php artisan optimize:clear</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. System Backup & Disaster Recovery Hub (Strictly The Creator Only) -->
    <div class="row g-4 mt-1 mb-4" id="backup-section">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-3 px-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 rounded-2 bg-dark text-warning">
                            <i class="bi bi-shield-lock-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="mb-0 fw-bold text-body">5. ศูนย์สำรองและกู้คืนข้อมูลระบบ (System Backup &amp; Disaster Recovery Hub)</h6>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                    <i class="bi bi-crown me-1"></i>The Creator Only
                                </span>
                            </div>
                            <small class="text-body-secondary">ระบบสร้างแพ็กเกจสำรองฐานข้อมูล MariaDB และไฟล์ Storage พร้อมระบบกู้คืนยืนยัน 2 ชั้นผ่าน Email OTP</small>
                        </div>
                    </div>
                    @if($isCreator)
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#rcloneGuideModal">
                                <i class="bi bi-google me-1"></i> เชื่อมต่อ Google Drive (Rclone)
                            </button>
                        </div>
                    @endif
                </div>
                <div class="card-body p-3 p-md-4">
                    @if(!$isCreator)
                        <div class="alert alert-secondary border d-flex align-items-center mb-0 p-3 rounded-3">
                            <i class="bi bi-lock-fill fs-4 text-secondary me-3"></i>
                            <div>
                                <div class="fw-bold text-body">ฟังก์ชันสำรองและกู้คืนระบบถูกจำกัดสิทธิ์ระดับสูงสุด</div>
                                <div class="small text-body-secondary">
                                    สงวนสิทธิ์การเข้าถึงและสั่งการเฉพาะบัญชี <strong>The Creator</strong> (ผู้ดูแลระบบสูงสุด) เพียงผู้เดียวเท่านั้น เพื่อความปลอดภัยสูงสุดของข้อมูลงานวิจัยทั้งมหาวิทยาลัย
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Metrics Strip -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-sm-6">
                                <div class="p-3 bg-body-tertiary rounded-3 border">
                                    <div class="small text-body-secondary fw-semibold mb-1"><i class="bi bi-database me-1 text-primary"></i>ขนาดฐานข้อมูล MariaDB</div>
                                    <div class="h5 mb-0 fw-bold text-body" style="font-variant-numeric: tabular-nums;">{{ $dbSizeFormatted }}</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="p-3 bg-body-tertiary rounded-3 border">
                                    <div class="small text-body-secondary fw-semibold mb-1"><i class="bi bi-folder2-open me-1 text-info"></i>ขนาดไฟล์เอกสาร Storage</div>
                                    <div class="h5 mb-0 fw-bold text-body" style="font-variant-numeric: tabular-nums;">{{ $storageSizeFormatted }}</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="p-3 bg-body-tertiary rounded-3 border">
                                    <div class="small text-body-secondary fw-semibold mb-1"><i class="bi bi-archive me-1 text-success"></i>ไฟล์สำรองบนเซิร์ฟเวอร์</div>
                                    <div class="h5 mb-0 fw-bold text-body" style="font-variant-numeric: tabular-nums;">{{ count($backupList) }} ไฟล์</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="p-3 bg-body-tertiary rounded-3 border">
                                    <div class="small text-body-secondary fw-semibold mb-1"><i class="bi bi-terminal me-1 text-warning"></i>สถานะ mysqldump</div>
                                    <div class="h5 mb-0 fw-bold {{ $mysqldumpAvailable ? 'text-success' : 'text-danger' }}">
                                        {{ $mysqldumpAvailable ? 'พร้อมใช้งาน ✓' : 'ไม่พบไบนารี ✗' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons Strip -->
                        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap p-3 bg-body-tertiary rounded-3 border">
                            <span class="small fw-bold text-body me-2"><i class="bi bi-play-circle-fill text-primary me-1"></i>สั่งสำรองข้อมูลทันที:</span>
                            
                            <form action="{{ route('backend.system_commands.backup.create') }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันสำรองเฉพาะฐานข้อมูล (Database .sql.gz)?');">
                                @csrf
                                <input type="hidden" name="type" value="db">
                                <button type="submit" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5" {{ !$mysqldumpAvailable ? 'disabled' : '' }}>
                                    <i class="bi bi-database-down"></i> สำรองฐานข้อมูล (.sql.gz)
                                </button>
                            </form>

                            <form action="{{ route('backend.system_commands.backup.create') }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันสำรองไฟล์เอกสาร Storage (.zip)?');">
                                @csrf
                                <input type="hidden" name="type" value="files">
                                <button type="submit" class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-1.5">
                                    <i class="bi bi-file-earmark-zip"></i> สำรองไฟล์ Storage (.zip)
                                </button>
                            </form>

                            <form action="{{ route('backend.system_commands.backup.create') }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันสำรองข้อมูลทั้งระบบแบบสมบูรณ์ (Full Archive: DB + Storage + Config)?');">
                                @csrf
                                <input type="hidden" name="type" value="all">
                                <button type="submit" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5" {{ !$mysqldumpAvailable ? 'disabled' : '' }}>
                                    <i class="bi bi-archive-fill"></i> สำรองทั้งระบบ (Full Archive)
                                </button>
                            </form>
                        </div>

                        <!-- Backup Archives Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 42%;">ชื่อไฟล์สำรอง (Archive Filename)</th>
                                        <th style="width: 14%;">ประเภท</th>
                                        <th style="width: 12%;" class="text-end">ขนาดไฟล์</th>
                                        <th style="width: 16%;">วันที่-เวลาสำรอง</th>
                                        <th style="width: 16%;" class="text-center">การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($backupList as $b)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-body text-break font-monospace" style="font-size: 0.82rem;">
                                                    <i class="bi bi-file-earmark-binary text-secondary me-1"></i>{{ $b['filename'] }}
                                                </div>
                                                <div class="text-body-tertiary small" style="font-size: 0.72rem;">
                                                    MD5: <code>{{ substr($b['checksum'], 0, 16) }}...</code>
                                                </div>
                                            </td>
                                            <td>
                                                @if($b['type'] === 'full')
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                        <i class="bi bi-archive-fill me-1"></i>ทั้งระบบ (Full)
                                                    </span>
                                                @elseif($b['type'] === 'database')
                                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                                        <i class="bi bi-database me-1"></i>ฐานข้อมูล (DB)
                                                    </span>
                                                @elseif($b['type'] === 'files')
                                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                                        <i class="bi bi-folder-fill me-1"></i>ไฟล์เอกสาร
                                                    </span>
                                                @elseif($b['type'] === 'safety_snapshot')
                                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                                        <i class="bi bi-shield-shaded me-1"></i>Safety Snapshot
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">ทั่วไป</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold text-body" style="font-variant-numeric: tabular-nums;">
                                                {{ $b['size_formatted'] }}
                                            </td>
                                            <td class="text-body-secondary" style="font-variant-numeric: tabular-nums;">
                                                {{ $b['created_at_thai'] }} น.
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <!-- Download Button -->
                                                    <a href="{{ route('backend.system_commands.backup.download', $b['filename']) }}" 
                                                       class="btn btn-outline-success" 
                                                       title="ดาวน์โหลดไฟล์สำรองนี้ลงเครื่อง">
                                                        <i class="bi bi-download"></i>
                                                    </a>

                                                    <!-- Restore Button (Triggers OTP Modal) -->
                                                    @if(in_array($b['type'], ['database', 'full', 'safety_snapshot']))
                                                        <button type="button" 
                                                                class="btn btn-outline-danger" 
                                                                title="กู้คืนระบบจากไฟล์นี้ (ต้องใช้ OTP ยืนยัน)"
                                                                onclick="openRestoreModal('{{ $b['filename'] }}', '{{ $b['type'] }}', '{{ $b['size_formatted'] }}')">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                    @endif

                                                    <!-- Delete Button -->
                                                    <form action="{{ route('backend.system_commands.backup.destroy', $b['filename']) }}" 
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
                                            <td colspan="5" class="text-center py-4 text-body-secondary">
                                                <i class="bi bi-inbox fs-3 d-block mb-1 text-muted"></i>
                                                ยังไม่มีไฟล์สำรองในระบบ คลิกปุ่ม <strong>"สำรองฐานข้อมูล"</strong> ด้านบนเพื่อเริ่มสร้างไฟล์แรก
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Engine & Extension Setup Guide -->
<div class="modal fade" id="engineSetupModal" tabindex="-1" aria-labelledby="engineSetupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-body-tertiary border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-2 bg-primary-subtle text-primary">
                        <i class="bi bi-tools fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body" id="engineSetupModalLabel">คู่มือการติดตั้งเอนจินประมวลผล PDF และส่วนขยาย (SysAdmin Setup Guide)</h5>
                        <small class="text-body-secondary">คำสั่งและวิธีตั้งค่า MuPDF (PyMuPDF), Poppler CLI, PHP GD และ FreeType สำหรับผู้ดูแลระบบ</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Nav Tabs -->
                <ul class="nav nav-pills nav-fill mb-4 p-1.5 bg-body-tertiary rounded-3 border" id="engineSetupTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold small py-2" id="tab-mupdf-btn" data-bs-toggle="pill" data-bs-target="#tab-mupdf" type="button" role="tab">
                            <i class="bi bi-cpu me-1"></i>1. MuPDF (PyMuPDF)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold small py-2" id="tab-poppler-btn" data-bs-toggle="pill" data-bs-target="#tab-poppler" type="button" role="tab">
                            <i class="bi bi-file-earmark-binary me-1"></i>2. Poppler pdftotext
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold small py-2" id="tab-gd-btn" data-bs-toggle="pill" data-bs-target="#tab-gd" type="button" role="tab">
                            <i class="bi bi-image me-1"></i>3. PHP GD &amp; FreeType
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold small py-2" id="tab-env-btn" data-bs-toggle="pill" data-bs-target="#tab-env" type="button" role="tab">
                            <i class="bi bi-sliders me-1"></i>4. ตัวแปร .env &amp; ทดสอบ
                        </button>
                    </li>
                </ul>

                <!-- Tab Contents -->
                <div class="tab-content" id="engineSetupTabContent">
                    <!-- Tab 1: MuPDF -->
                    <div class="tab-pane fade show active" id="tab-mupdf" role="tabpanel">
                        <div class="alert alert-info-subtle border border-info-subtle d-flex align-items-center mb-3 p-3 rounded-3">
                            <i class="bi bi-info-circle-fill fs-5 text-info me-2.5"></i>
                            <div class="small">
                                <strong>หน้าที่ในระบบ:</strong> เรนเดอร์หน้าแรกของ PDF ฉบับเต็มทั้งหน้าออกมาเป็นภาพ WebP คุณภาพสูง คมชัดทุกภาษาและตัวอักษร ประหยัดเวลาและแรมสูงสุด (Tier 2 Full-Page Rasterizer)
                            </div>
                        </div>

                        <h6 class="fw-bold text-body mb-2"><i class="bi bi-windows text-primary me-1"></i>บน Windows / Laragon:</h6>
                        <ol class="small text-body-secondary ps-3 mb-3">
                            <li>ตรวจสอบว่ามี Python ในเครื่องหรือไม่ โดยเปิด CMD แล้วพิมพ์: <code>python --version</code> (หากยังไม่มี ให้ดาวน์โหลดจาก <a href="https://www.python.org/downloads/" target="_blank">python.org</a> และอย่าลืมติ๊ก <strong>"Add python.exe to PATH"</strong>)</li>
                            <li>เปิด Command Prompt หรือ PowerShell แล้วรันคำสั่งติดตั้งแพ็กเกจ:
                                <pre class="p-2.5 bg-dark text-light rounded mt-1 mb-2 font-monospace" style="font-size: 0.8rem;">pip install --upgrade pymupdf pillow</pre>
                            </li>
                            <li>หาก Laragon ตรวจไม่พบอัตโนมัติ ให้ระบุพาธของ Python ในไฟล์ <code>.env</code>:
                                <pre class="p-2 bg-dark text-warning rounded mt-1 mb-0 font-monospace" style="font-size: 0.78rem;">PYTHON_PATH="C:\Users\YourUser\AppData\Local\Programs\Python\Python312\python.exe"</pre>
                            </li>
                        </ol>

                        <h6 class="fw-bold text-body mb-2"><i class="bi bi-ubuntu text-danger me-1"></i>บน Linux (Ubuntu / Debian Production):</h6>
                        <pre class="p-2.5 bg-dark text-light rounded mb-3 font-monospace" style="font-size: 0.8rem;">sudo apt update && sudo apt install -y python3 python3-pip
pip3 install --upgrade pymupdf pillow</pre>

                        <h6 class="fw-bold text-body mb-2"><i class="bi bi-server text-success me-1"></i>บน Linux (CentOS / RHEL / AlmaLinux):</h6>
                        <pre class="p-2.5 bg-dark text-light rounded mb-0 font-monospace" style="font-size: 0.8rem;">sudo dnf install -y python3 python3-pip
pip3 install --upgrade pymupdf pillow</pre>
                    </div>

                    <!-- Tab 2: Poppler -->
                    <div class="tab-pane fade" id="tab-poppler" role="tabpanel">
                        <div class="alert alert-success-subtle border border-success-subtle d-flex align-items-center mb-3 p-3 rounded-3">
                            <i class="bi bi-check-circle-fill fs-5 text-success me-2.5"></i>
                            <div class="small">
                                <strong>หน้าที่ในระบบ:</strong> นับจำนวนหน้าเอกสาร PDF จริงด้วย Form Feed <code>\x0c</code> ได้แม่นยำ 99.99% ไม่สะดุดแม้เป็นไฟล์เข้ารหัส (Secured/AES) หรือมีหลายร้อยหน้า และสกัดหน้าแรกด้วย <code>pdftoppm</code>
                            </div>
                        </div>

                        <h6 class="fw-bold text-body mb-2"><i class="bi bi-windows text-primary me-1"></i>บน Windows / Laragon:</h6>
                        <div class="p-3 bg-body-tertiary rounded border mb-3 small">
                            <div class="fw-bold text-body mb-1"><i class="bi bi-check2-circle text-success me-1"></i>วิธีที่ 1 (ตรวจจับอัตโนมัติผ่าน Git):</div>
                            หาก Laragon ได้ติดตั้ง Git for Windows ไบนารี <code>pdftotext.exe</code> จะอยู่ที่โฟลเดอร์ <code>laragon\bin\git\mingw64\bin\pdftotext.exe</code> ซึ่งระบบของมหาวิทยาลัยตรวจจับให้ใช้งานได้ทันทีโดยไม่ต้องตั้งค่าใดๆ
                            <hr class="my-2">
                            <div class="fw-bold text-body mb-1"><i class="bi bi-folder-plus text-primary me-1"></i>วิธีที่ 2 (ติดตั้งชุด Poppler Binary แยก):</div>
                            1. ดาวน์โหลดแพ็กเกจ Poppler for Windows จาก <a href="https://github.com/oschwartz10612/poppler-windows/releases/" target="_blank">GitHub Release (oschwartz10612)</a><br>
                            2. แตกไฟล์โฟลเดอร์ <code>bin/</code> นำไปวางไว้ที่ <code>C:\laragon\bin\poppler\bin</code><br>
                            3. ระบุพาธในไฟล์ <code>.env</code>:
                            <pre class="p-2 bg-dark text-warning rounded mt-1 mb-0 font-monospace" style="font-size: 0.78rem;">PDFTOTEXT_PATH="C:/laragon/bin/poppler/bin/pdftotext.exe"
PDFTOPPM_PATH="C:/laragon/bin/poppler/bin/pdftoppm.exe"</pre>
                        </div>

                        <h6 class="fw-bold text-body mb-2"><i class="bi bi-ubuntu text-danger me-1"></i>บน Linux (Ubuntu / Debian Production):</h6>
                        <pre class="p-2.5 bg-dark text-light rounded mb-3 font-monospace" style="font-size: 0.8rem;">sudo apt update && sudo apt install -y poppler-utils</pre>

                        <h6 class="fw-bold text-body mb-2"><i class="bi bi-server text-success me-1"></i>บน Linux (CentOS / RHEL / AlmaLinux):</h6>
                        <pre class="p-2.5 bg-dark text-light rounded mb-0 font-monospace" style="font-size: 0.8rem;">sudo dnf install -y poppler-utils</pre>
                    </div>

                    <!-- Tab 3: PHP GD & FreeType -->
                    <div class="tab-pane fade" id="tab-gd" role="tabpanel">
                        <div class="alert alert-warning-subtle border border-warning-subtle d-flex align-items-center mb-3 p-3 rounded-3">
                            <i class="bi bi-lightbulb-fill fs-5 text-warning me-2.5"></i>
                            <div class="small">
                                <strong>หน้าที่ในระบบ:</strong> สังเคราะห์ภาพหน้าปกสำรองสไตล์วิชาการภาษาไทย (Academic GD Cover) เมื่อไฟล์ PDF ชำรุดหรือไม่พบไฟล์ และใช้แปลงไฟล์ภาพเป็นฟอร์แมต <code>.webp</code>
                            </div>
                        </div>

                        <h6 class="fw-bold text-body mb-2"><i class="bi bi-windows text-primary me-1"></i>บน Windows / Laragon:</h6>
                        <ol class="small text-body-secondary ps-3 mb-3">
                            <li>คลิกขวาที่หน้าต่างโปรแกรม Laragon ➔ เลือกเมนู <strong>PHP</strong> ➔ <strong>Extensions</strong> ➔ ติ๊กเครื่องหมายถูกที่ <strong><code>gd</code></strong></li>
                            <li>หรือเปิดไฟล์ <code>php.ini</code> (คลิกขวา Laragon ➔ PHP ➔ php.ini) ค้นหาบรรทัด:
                                <pre class="p-2 bg-dark text-light rounded mt-1 mb-1 font-monospace" style="font-size: 0.78rem;">;extension=gd</pre>
                                แล้วลบเครื่องหมาย <code>;</code> ด้านหน้าออกเป็น:
                                <pre class="p-2 bg-dark text-success rounded mt-1 mb-1 font-monospace" style="font-size: 0.78rem;">extension=gd</pre>
                            </li>
                            <li>กดปุ่ม <strong>Stop</strong> แล้วกด <strong>Start All</strong> ใน Laragon เพื่อรีสตาร์ท Apache</li>
                        </ol>

                        <h6 class="fw-bold text-body mb-2"><i class="bi bi-ubuntu text-danger me-1"></i>บน Linux (Ubuntu / Debian Production):</h6>
                        <pre class="p-2.5 bg-dark text-light rounded mb-3 font-monospace" style="font-size: 0.8rem;">sudo apt update
sudo apt install -y php8.2-gd  # ปรับเวอร์ชันตาม PHP เช่น php8.3-gd
sudo systemctl restart apache2 # หรือ sudo systemctl restart php8.2-fpm</pre>

                        <h6 class="fw-bold text-body mb-2"><i class="bi bi-server text-success me-1"></i>บน Linux (CentOS / RHEL / AlmaLinux):</h6>
                        <pre class="p-2.5 bg-dark text-light rounded mb-3 font-monospace" style="font-size: 0.8rem;">sudo dnf install -y php-gd
sudo systemctl restart httpd   # หรือ sudo systemctl restart php-fpm</pre>

                        <h6 class="fw-bold text-body mb-2"><i class="bi bi-fonts text-info me-1"></i>การติดตั้งฟอนต์ภาษาไทย (TrueType FreeType):</h6>
                        <p class="small text-body-secondary mb-2">เพื่อให้การวาดข้อความภาษาไทยบนภาพหน้าปกสำรองไม่ตกหล่น:</p>
                        <ul class="small text-body-secondary ps-3 mb-0">
                            <li><strong>Windows</strong>: ระบบเรียกใช้ <code>C:/Windows/Fonts/leelawb.ttf</code> (Leelawadee UI Bold) อัตโนมัติ</li>
                            <li><strong>Linux</strong>: ติดตั้งแพ็กเกจฟอนต์ไทย: <code>sudo apt install -y fonts-thai-tlwg fonts-noto-cjk</code> หรือคัดลอกไฟล์ฟอนต์ <code>.ttf</code> ไปใส่ไว้ที่โฟลเดอร์ <code>storage/fonts/</code> ในโปรเจกต์</li>
                        </ul>
                    </div>

                    <!-- Tab 4: Environment & Verification -->
                    <div class="tab-pane fade" id="tab-env" role="tabpanel">
                        <div class="alert alert-secondary border d-flex align-items-center mb-3 p-3 rounded-3">
                            <i class="bi bi-sliders fs-5 text-secondary me-2.5"></i>
                            <div class="small">
                                <strong>สรุปตัวแปรพาธในไฟล์ <code>.env</code>:</strong> ระบุเฉพาะในกรณีที่เซิร์ฟเวอร์ไม่ได้ติดตั้งเครื่องมือไว้ในโฟลเดอร์ System PATH มาตรฐาน
                            </div>
                        </div>

                        <h6 class="fw-bold text-body mb-2">ตัวอย่างการตั้งค่าในไฟล์ <code>.env</code>:</h6>
                        <pre class="p-3 bg-dark text-warning rounded mb-3 font-monospace" style="font-size: 0.8rem;"># กำหนดพาธ Python สำหรับ PyMuPDF (ถ้าไม่ได้อยู่ใน PATH)
PYTHON_PATH="python"

# กำหนดพาธ Poppler pdftotext สำหรับนับหน้า 99.99%
PDFTOTEXT_PATH="/usr/bin/pdftotext"

# กำหนดพาธ Poppler pdftoppm สำหรับเรนเดอร์ภาพ
PDFTOPPM_PATH="/usr/bin/pdftoppm"</pre>

                        <h6 class="fw-bold text-body mb-2">คำสั่งทดสอบสถานะผ่าน Terminal / Shell:</h6>
                        <div class="mb-2">
                            <small class="text-body-secondary fw-semibold d-block">1. ทดสอบ PyMuPDF (Python):</small>
                            <pre class="p-2 bg-dark text-light rounded font-monospace small mb-2" style="font-size: 0.76rem;">python -c "import fitz, PIL; print('PyMuPDF Ready')"</pre>
                        </div>
                        <div class="mb-2">
                            <small class="text-body-secondary fw-semibold d-block">2. ทดสอบ Poppler pdftotext:</small>
                            <pre class="p-2 bg-dark text-light rounded font-monospace small mb-2" style="font-size: 0.76rem;">pdftotext -v</pre>
                        </div>
                        <div>
                            <small class="text-body-secondary fw-semibold d-block">3. ทดสอบ PHP GD Extension &amp; WebP:</small>
                            <pre class="p-2 bg-dark text-light rounded font-monospace small mb-0" style="font-size: 0.76rem;">php -r "echo extension_loaded('gd') && function_exists('imagewebp') ? 'GD WebP OK' : 'GD Missing';"</pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-body-tertiary border-top py-2.5 d-flex justify-content-between">
                <span class="small text-body-secondary"><i class="bi bi-info-circle me-1"></i>หลังติดตั้งเสร็จ ให้คลิกปุ่ม <strong>"รีเฟรชสถานะ"</strong> ที่มุมขวาบน</span>
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Safe System Restore with Email OTP -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-exclamation fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="restoreModalLabel">ยืนยันการกู้คืนระบบ (System Restore)</h5>
                        <small class="text-white-50">การดำเนินการขั้นวิกฤต — จำเป็นต้องใช้รหัส OTP ยืนยัน</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('backend.system_commands.backup.confirm_restore') }}" method="POST" id="restoreForm">
                @csrf
                <input type="hidden" name="filename" id="restoreFilenameInput" value="">

                <div class="modal-body p-4">
                    <!-- Critical Warning Alert -->
                    <div class="alert alert-danger-subtle border border-danger-subtle mb-3 p-3 rounded-3 small">
                        <div class="fw-bold text-danger mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>คำเตือนความปลอดภัยสูงสุด:</div>
                        การกู้คืนจะนำข้อมูลจากไฟล์สำรองมาเขียนทับฐานข้อมูลปัจจุบัน ระบบจะสร้าง <strong>Safety Snapshot</strong> สำรองฐานข้อมูลปัจจุบันไว้ให้อัตโนมัติก่อนเริ่มกู้คืนเสมอ
                    </div>

                    <!-- Target File Info -->
                    <div class="p-3 bg-body-tertiary rounded-3 border mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-body-secondary">ไฟล์สำรองเป้าหมาย:</span>
                            <span class="fw-bold text-body font-monospace text-break" id="restoreFilenameDisplay">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-body-secondary">ขนาดไฟล์:</span>
                            <span class="fw-bold text-body" id="restoreFileSizeDisplay">-</span>
                        </div>
                    </div>

                    <!-- Step 1: Request OTP Section -->
                    <div id="otpRequestSection" class="mb-3 text-center p-3 bg-body-secondary rounded-3 border">
                        <div class="small text-body-secondary mb-2">
                            ระบบจะจัดส่งรหัสความปลอดภัย OTP 6 หลักไปยังอีเมลของผู้ดูแลระบบสูงสุด
                        </div>
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
function openEngineModal(tabBtnId) {
    const btn = document.getElementById(tabBtnId);
    if (btn) {
        const tab = new bootstrap.Tab(btn);
        tab.show();
    }
    const modalEl = document.getElementById('engineSetupModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

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

    fetch('{{ route('backend.system_commands.backup.request_otp') }}', {
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
