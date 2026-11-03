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
                    <div class="text-body-tertiary small" style="font-size: 0.78rem;">
                        Python: <code class="text-body-secondary">{{ basename($engineInfo['python_executable']) }}</code>
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
                    <div class="text-body-tertiary small" style="font-size: 0.78rem;">
                        @if($engineInfo['pdftotext_available'])
                            ไบนารี: <code class="text-body-secondary" title="{{ $engineInfo['pdftotext_binary'] }}">{{ basename($engineInfo['pdftotext_binary']) }}</code>
                            <span class="text-success ms-1">({{ basename(dirname($engineInfo['pdftotext_binary'])) }})</span>
                        @else
                            Poppler: <span class="text-danger">ไม่พบ</span> (ใช้ Smalot {{ $engineInfo['smalot_available'] ? '✓' : '✗' }})
                        @endif
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
                    <div class="text-body-tertiary small" style="font-size: 0.78rem;">
                        ฟอนต์: <code>Leelawadee UI Bold (ภาษาไทย)</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Processing Statistics (Current Coverage) -->
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
</div>
@endsection
