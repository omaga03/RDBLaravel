@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backend.research_conference.index') }}" class="text-decoration-none">จัดการการประชุมวิชาการ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ทดสอบดึงข้อมูล Facebook (Dry-Run Inspector)</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-flask text-warning me-2"></i>เครื่องมือทดสอบการดึงข้อมูลงานประชุมวิชาการจาก Facebook
                <span class="text-body-secondary small fw-normal">(Dry-Run Scraper Inspector)</span>
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('backend.research_conference.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm">
                <i class="bi bi-arrow-left me-1.5"></i> กลับหน้ารายการการประชุม
            </a>
        </div>
    </div>

    <!-- Safety & Dry-Run Notice Banner -->
    <div class="alert alert-warning border border-warning-subtle shadow-sm mb-4">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-shield-check text-warning-emphasis fs-3 flex-shrink-0 mt-1"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1 text-warning-emphasis">
                    โหมดทดสอบความถูกต้องของการดึงข้อมูล Facebook (100% Dry-Run Isolation)
                </h5>
                <p class="mb-0 small text-body">
                    เครื่องมือนี้จำลองการกวาดข้อมูล (Scraping) จาก Facebook Page ตรวจสอบ regex สกัดรูปภาพ, ชื่อหัวข้อ, วันที่จัดงาน, วันหมดเขตรับบทความ และสถานที่ 
                    <strong>โดยไม่มีการบันทึกข้อมูลลงฐานข้อมูล</strong>, <strong>ไม่มีการดาวน์โหลดหรือเขียนไฟล์ลงเซิร์ฟเวอร์</strong>, 
                    <strong>ไม่มีการส่ง Make.com Webhook</strong> และ <strong>ไม่มีการยิงแจ้งเตือนผ่าน Telegram</strong> 
                    คุณสามารถทดสอบโพสต์ที่เคยมีอยู่ในระบบแล้วได้โดยไม่ถูกข้าม
                </p>
            </div>
        </div>
    </div>

    <!-- Control / Filter Form -->
    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-sliders me-2 text-primary"></i>ตั้งค่าการทดสอบ Scraper
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('backend.research_conference.test_sync') }}" method="GET">
                <input type="hidden" name="run" value="1">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ระบุรหัสรูปภาพ Facebook (FBID เฉพาะเจาะจง)</label>
                        <input type="text" name="fbid" value="{{ $fbid }}" class="form-control" placeholder="เช่น 100083168888888 (เว้นว่างเพื่อดึงจากหน้าแรกเพจ)">
                        <div class="form-text">หากระบุรหัส จะทดสอบเปิดหน้าภาพถ่ายนั้นโดยตรง</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">จำนวนรายการที่ต้องการทดสอบ (Limit)</label>
                        <select name="limit" class="form-select">
                            <option value="1" {{ $limit == 1 ? 'selected' : '' }}>1 รายการ</option>
                            <option value="2" {{ $limit == 2 ? 'selected' : '' }}>2 รายการ</option>
                            <option value="3" {{ $limit == 3 ? 'selected' : '' }}>3 รายการ (ค่าเริ่มต้น)</option>
                            <option value="5" {{ $limit == 5 ? 'selected' : '' }}>5 รายการ</option>
                            <option value="8" {{ $limit == 8 ? 'selected' : '' }}>8 รายการ</option>
                        </select>
                        <div class="form-text">มีผลเมื่อไม่ได้ระบุ FBID เฉพาะ</div>
                    </div>
                    <div class="col-md-5 d-flex gap-2">
                        <button type="submit" class="btn btn-warning d-inline-flex align-items-center flex-grow-1 justify-content-center shadow-sm">
                            <i class="bi bi-play-circle-fill me-1.5"></i> เริ่มทดสอบ Scraper (Run Test)
                        </button>
                        @if($testResult !== null)
                            <a href="{{ route('backend.research_conference.test_sync') }}" class="btn btn-outline-secondary d-inline-flex align-items-center">
                                <i class="bi bi-arrow-counterclockwise me-1.5"></i> ล้างค่า
                            </a>
                        @endif
                    </div>
                    <div class="col-12">
                        <div class="collapse" id="advanced-cookie">
                            <div class="pt-2">
                                <label class="form-label fw-semibold">Facebook Scrape Cookie สำหรับทดสอบชั่วคราว (Optional)</label>
                                <textarea name="cookie" class="form-control font-monospace small" rows="2" placeholder="c_user=...; xs=...; datr=...">{{ $cookie }}</textarea>
                                <div class="form-text">หากไม่ระบุ ระบบจะใช้ Cookie จาก .env หรือการตั้งค่าระบบอัตโนมัติ</div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-link text-decoration-none px-0 mt-1" type="button" data-bs-toggle="collapse" data-bs-target="#advanced-cookie">
                            <i class="bi bi-gear me-1"></i> ตัวเลือกขั้นสูง: ระบุ Facebook Cookie เอง
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($testResult !== null)
        @php
            $diagnostics = $testResult['diagnostics'] ?? [];
        @endphp

        <!-- Scraper Diagnostics Bar -->
        <div class="card shadow-sm border mb-4">
            <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold text-body">
                    <i class="bi bi-activity me-2 text-primary"></i>ข้อมูลการตรวจจับระบบ Scraper (Diagnostics)
                </h6>
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-1.5">
                    <i class="bi bi-shield-lock me-1"></i> Dry-Run Mode (ไม่มีการบันทึก)
                </span>
            </div>
            <div class="card-body p-3">
                <div class="row g-3 text-center">
                    <div class="col-md-3">
                        <div class="p-2 border rounded bg-body-secondary">
                            <small class="text-body-secondary d-block">สถานะการเชื่อมต่อ HTTP</small>
                            <span class="fw-bold fs-6 font-monospace {{ ($diagnostics['http_status'] ?? 0) === 200 ? 'text-success' : 'text-body' }}">
                                {{ $diagnostics['http_status'] ? 'HTTP ' . $diagnostics['http_status'] : 'ทดสอบเฉพาะ FBID' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 border rounded bg-body-secondary">
                            <small class="text-body-secondary d-block">จำนวน Photo Links ที่ตรวจพบ</small>
                            <span class="fw-bold fs-6 font-monospace text-primary">
                                {{ number_format($diagnostics['discovered_links_count'] ?? 0) }} รายการ
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 border rounded bg-body-secondary">
                            <small class="text-body-secondary d-block">สถานะ Cookie</small>
                            @if(!empty($diagnostics['has_cookie']))
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                    <i class="bi bi-check2-circle me-1"></i> มี Cookie ({{ $diagnostics['cookie_length'] }} ตัวอักษร)
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                    <i class="bi bi-exclamation-triangle me-1"></i> ไม่มี Cookie
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 border rounded bg-body-secondary">
                            <small class="text-body-secondary d-block">ผลการสกัดข้อมูลในรอบนี้</small>
                            <span class="fw-bold fs-6 font-monospace text-success">
                                สำเร็จ {{ count($testResult['items'] ?? []) }} รายการ
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($testResult['error']))
            <div class="alert alert-danger shadow-sm mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>เกิดข้อผิดพลาดในการดึงข้อมูล:</strong> {{ $testResult['error'] }}
            </div>
        @else
            <!-- Extracted Items -->
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-clipboard2-data me-2 text-primary"></i>รายการข้อมูลงานประชุมที่สกัดได้
                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2">
                            {{ count($testResult['items'] ?? []) }} รายการ
                        </span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @forelse($testResult['items'] as $idx => $tItem)
                        <div class="p-4 border-bottom {{ $loop->last ? 'border-bottom-0' : '' }}">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary rounded-pill px-3 py-2 font-monospace fs-6">
                                        #{{ $idx + 1 }}
                                    </span>
                                    <span class="badge bg-light text-body border font-monospace px-2.5 py-1.5">
                                        FBID: {{ $tItem['con_id'] }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if(!empty($tItem['exists_in_db']))
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-2.5 py-1.5">
                                            <i class="bi bi-check-circle me-1"></i> มีอยู่ในฐานข้อมูลแล้ว (ID: #{{ $tItem['existing_id'] }})
                                        </span>
                                        <a href="{{ route('backend.research_conference.show', $tItem['existing_id']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> ดูข้อมูลในระบบ
                                        </a>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1.5">
                                            <i class="bi bi-plus-circle me-1"></i> ยังไม่มีในระบบ (เป็นรายการใหม่)
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if(($tItem['status'] ?? '') === 'error' || ($tItem['status'] ?? '') === 'skipped')
                                <div class="alert {{ ($tItem['status'] ?? '') === 'error' ? 'alert-danger' : 'alert-secondary' }} mb-0">
                                    <i class="bi bi-info-circle me-1"></i> {{ $tItem['message'] }}
                                </div>
                            @else
                                <div class="row g-4">
                                    <!-- Image Preview Column -->
                                    <div class="col-md-3">
                                        <div class="card border bg-body-tertiary p-2 text-center">
                                            <div class="ratio ratio-4x3 rounded overflow-hidden bg-body-secondary border mb-2">
                                                @if(!empty($tItem['image_uri']))
                                                    <img src="{{ $tItem['image_uri'] }}" alt="Facebook Image Preview" class="object-fit-cover w-100 h-100" referrerpolicy="no-referrer" onerror="this.src=''; this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center h-100 text-warning small\'><i class=\'bi bi-image-alt me-1\'></i> โหลดภาพจาก Facebook CDN ไม่ได้ (อาจติด Referrer)</div>';">
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center h-100 text-body-secondary">
                                                        <i class="bi bi-image fs-3"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="small text-body-secondary text-break font-monospace">
                                                <strong>ชื่อไฟล์จำลอง:</strong><br>{{ $tItem['con_id'] }}.jpg
                                            </div>
                                            @if(!empty($tItem['image_uri']))
                                                <a href="{{ $tItem['image_uri'] }}" target="_blank" rel="noreferrer" class="btn btn-sm btn-outline-secondary mt-2">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> เปิดภาพจาก Facebook CDN
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Extracted Fields Column -->
                                    <div class="col-md-9">
                                        <h5 class="fw-bold text-body mb-2">
                                            {{ $tItem['con_name'] }}
                                        </h5>
                                        <div class="text-body-secondary small mb-3">
                                            <a href="{{ $tItem['url'] }}" target="_blank" class="text-decoration-none">
                                                <i class="bi bi-facebook me-1 text-primary"></i>{{ $tItem['url'] }}
                                            </a>
                                        </div>

                                        <!-- Extracted Details Grid -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <div class="p-2 border rounded bg-body-tertiary small">
                                                    <strong class="text-body"><i class="bi bi-calendar-event me-1 text-primary"></i> วันจัดงาน (con_even_date):</strong>
                                                    <div class="text-body-emphasis mt-1">
                                                        {{ $tItem['con_even_date'] ?: '(ไม่พบข้อมูลในข้อความ)' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 border rounded bg-body-tertiary small">
                                                    <strong class="text-body"><i class="bi bi-calendar-x me-1 text-danger"></i> หมดเขตรับบทความ (con_sub_deadline):</strong>
                                                    <div class="text-body-emphasis mt-1">
                                                        {{ $tItem['con_sub_deadline'] ?: '(ไม่พบข้อมูล Deadline ในข้อความ)' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="p-2 border rounded bg-body-tertiary small">
                                                    <strong class="text-body"><i class="bi bi-geo-alt-fill me-1 text-danger"></i> สถานที่จัดงาน (con_venue):</strong>
                                                    <div class="text-body-emphasis mt-1">
                                                        {{ $tItem['con_venue'] ?: '(ไม่พบข้อมูลสถานที่ ณ... ในข้อความ)' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Keyword Check Badges -->
                                        @php
                                            $kw = $tItem['keyword_checks'] ?? [];
                                        @endphp
                                        <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                                            <span class="small fw-semibold text-body"><i class="bi bi-check2-all text-primary me-1"></i> ตรวจเงื่อนไขคำสำคัญ:</span>
                                            @if(!empty($kw['has_conf_keyword']))
                                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                                    <i class="bi bi-check me-1"></i> คำสำคัญงานประชุม (ผ่าน)
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                                    <i class="bi bi-x me-1"></i> คำสำคัญงานประชุม (ไม่ผ่าน)
                                                </span>
                                            @endif

                                            @if(!empty($kw['has_detail_keyword']))
                                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                                    <i class="bi bi-check me-1"></i> คำสำคัญรายละเอียด (ผ่าน)
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                                    <i class="bi bi-x me-1"></i> คำสำคัญรายละเอียด (ไม่ผ่าน)
                                                </span>
                                            @endif

                                            @if(!empty($kw['passed']))
                                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                                    <i class="bi bi-shield-check me-1"></i> ตรงเงื่อนไขนำเข้าของระบบ
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                                    <i class="bi bi-shield-exclamation me-1"></i> จะถูกข้ามในระบบจริง
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Collapsible Tools -->
                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#detail-{{ $idx }}">
                                                <i class="bi bi-file-richtext me-1"></i> แสดงเนื้อหาที่สกัดได้ (con_detail)
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#webhook-{{ $idx }}">
                                                <i class="bi bi-code-square me-1"></i> จำลอง Payload Make.com
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#telegram-{{ $idx }}">
                                                <i class="bi bi-telegram me-1"></i> จำลองข้อความ Telegram
                                            </button>
                                        </div>

                                        <!-- Detail Collapse -->
                                        <div class="collapse mt-3" id="detail-{{ $idx }}">
                                            <div class="card card-body bg-body-tertiary border small">
                                                <h6 class="fw-bold mb-2">เนื้อหารายละเอียดงานประชุม (con_detail):</h6>
                                                <div class="border rounded p-3 bg-body" style="max-height: 250px; overflow-y: auto;">
                                                    {!! $tItem['con_detail'] !!}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Webhook Collapse -->
                                        <div class="collapse mt-3" id="webhook-{{ $idx }}">
                                            <div class="card card-body bg-body-tertiary border">
                                                <h6 class="fw-bold mb-2 text-info-emphasis"><i class="bi bi-send-check me-1"></i> โครงสร้าง JSON ที่จะส่งให้ Make.com Webhook:</h6>
                                                <pre class="bg-body p-3 rounded border font-monospace small mb-0 text-body" style="max-height: 250px; overflow-y: auto;">{{ json_encode($tItem['simulated_webhook'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        </div>

                                        <!-- Telegram Collapse -->
                                        <div class="collapse mt-3" id="telegram-{{ $idx }}">
                                            <div class="card card-body bg-body-tertiary border">
                                                <h6 class="fw-bold mb-2 text-success-emphasis"><i class="bi bi-chat-left-dots me-1"></i> รูปแบบข้อความที่จะยิงแจ้งเตือน Telegram:</h6>
                                                <pre class="bg-body p-3 rounded border font-monospace small mb-0 text-body" style="white-space: pre-wrap;">{{ $tItem['simulated_telegram'] }}</pre>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5 text-body-secondary">
                            <i class="bi bi-inbox fs-2 d-block mb-3 opacity-50"></i>
                            ไม่พบรายการงานประชุมจากการทดสอบ
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
