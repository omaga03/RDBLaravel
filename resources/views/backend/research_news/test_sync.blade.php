@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backend.research_news.index') }}" class="text-decoration-none">จัดการข่าวและกิจกรรม</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ทดสอบดึงข้อมูล NRIIS (Dry-Run Inspector)</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-flask text-warning me-2"></i>เครื่องมือทดสอบการดึงข้อมูลข่าวทุนวิจัย NRIIS
                <span class="text-body-secondary small fw-normal">(Dry-Run Scraper Inspector)</span>
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('backend.research_news.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm">
                <i class="bi bi-arrow-left me-1.5"></i> กลับหน้ารายการข่าว
            </a>
        </div>
    </div>

    <!-- Safety & Dry-Run Notice Banner -->
    <div class="alert alert-warning border border-warning-subtle shadow-sm mb-4">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-shield-check text-warning-emphasis fs-3 flex-shrink-0 mt-1"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1 text-warning-emphasis">
                    โหมดทดสอบความถูกต้องของการดึงข้อมูล (100% Dry-Run Isolation)
                </h5>
                <p class="mb-0 small text-body">
                    เครื่องมือนี้ใช้สำหรับตรวจสอบการทำงานของ NRIIS API ตรวจสอบโครงสร้างเนื้อหา เอกสารแนบหลัก และไฟล์ดาวน์โหลด 
                    <strong>โดยไม่มีการบันทึกข้อมูลลงฐานข้อมูล</strong>, <strong>ไม่มีการดาวน์โหลดหรือเขียนไฟล์ลงเซิร์ฟเวอร์</strong>, 
                    <strong>ไม่มีการส่ง Make.com Webhook</strong> และ <strong>ไม่มีการยิงแจ้งเตือนผ่าน Telegram</strong> 
                    คุณสามารถทดสอบรายการที่เคยมีอยู่ในระบบแล้วได้ตามต้องการ
                </p>
            </div>
        </div>
    </div>

    <!-- Control / Filter Form -->
    <div class="card shadow-sm border mb-4">
        <div class="card-header bg-body-tertiary py-3">
            <h5 class="mb-0 fw-bold text-body">
                <i class="bi bi-sliders me-2 text-primary"></i>ตั้งค่าการทดสอบดึงข้อมูล
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('backend.research_news.test_sync') }}" method="GET">
                <input type="hidden" name="run" value="1">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ระบุรหัสข่าว NRIIS (NID เฉพาะเจาะจง)</label>
                        <input type="text" name="nid" value="{{ $nid }}" class="form-control" placeholder="เช่น 12330 (เว้นว่างเพื่อดึงจากหน้าแรก)">
                        <div class="form-text">หากระบุรหัส จะทดสอบเฉพาะข่าวนั้นโดยตรง</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">จำนวนรายการที่ต้องการทดสอบ (Limit)</label>
                        <select name="limit" class="form-select">
                            <option value="1" {{ $limit == 1 ? 'selected' : '' }}>1 รายการ</option>
                            <option value="3" {{ $limit == 3 ? 'selected' : '' }}>3 รายการ</option>
                            <option value="5" {{ $limit == 5 ? 'selected' : '' }}>5 รายการ (ค่าเริ่มต้น)</option>
                            <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10 รายการ</option>
                            <option value="20" {{ $limit == 20 ? 'selected' : '' }}>20 รายการ</option>
                        </select>
                        <div class="form-text">มีผลเมื่อไม่ได้ระบุรหัสข่าวเฉพาะ</div>
                    </div>
                    <div class="col-md-5 d-flex gap-2">
                        <button type="submit" class="btn btn-warning d-inline-flex align-items-center flex-grow-1 justify-content-center shadow-sm">
                            <i class="bi bi-play-circle-fill me-1.5"></i> เริ่มทดสอบดึงข้อมูล (Run Test)
                        </button>
                        @if($testResult !== null)
                            <a href="{{ route('backend.research_news.test_sync') }}" class="btn btn-outline-secondary d-inline-flex align-items-center">
                                <i class="bi bi-arrow-counterclockwise me-1.5"></i> ล้างค่า
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($testResult !== null)
        @if(!empty($testResult['error']))
            <div class="alert alert-danger shadow-sm mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>เกิดข้อผิดพลาดในการทดสอบ:</strong> {{ $testResult['error'] }}
            </div>
        @else
            <!-- Test Result Summary Header -->
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-clipboard2-data me-2 text-primary"></i>ผลการทดสอบดึงข้อมูล NRIIS API
                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill ms-2">
                            พบ {{ count($testResult['items'] ?? []) }} รายการ
                        </span>
                    </h5>
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-2">
                        <i class="bi bi-shield-lock me-1"></i> Dry-Run Mode (ไม่มีการบันทึก)
                    </span>
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
                                        NID: {{ $tItem['news_id'] }}
                                    </span>
                                    @if(!empty($tItem['news_type']))
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                            {{ $tItem['news_type'] }}
                                        </span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if(!empty($tItem['exists_in_db']))
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-2.5 py-1.5">
                                            <i class="bi bi-check-circle me-1"></i> มีอยู่ในฐานข้อมูลแล้ว (ID: #{{ $tItem['existing_id'] }})
                                        </span>
                                        <a href="{{ route('backend.research_news.show', $tItem['existing_id']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> ดูข้อมูลในระบบ
                                        </a>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1.5">
                                            <i class="bi bi-plus-circle me-1"></i> ยังไม่มีในระบบ (เป็นรายการใหม่)
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if(($tItem['status'] ?? '') === 'error' || ($tItem['status'] ?? '') === 'empty')
                                <div class="alert alert-danger mb-0">
                                    <i class="bi bi-exclamation-octagon me-1"></i> {{ $tItem['message'] }}
                                </div>
                            @else
                                <div class="row g-4">
                                    <!-- Image Preview Column -->
                                    <div class="col-md-3">
                                        <div class="card border bg-body-tertiary p-2 text-center">
                                            <div class="ratio ratio-16x9 rounded overflow-hidden bg-body-secondary border mb-2">
                                                @if(!empty($tItem['news_img_url']))
                                                    <img src="{{ $tItem['news_img_url'] }}" alt="Live Image Preview" class="object-fit-cover w-100 h-100" onerror="this.src=''; this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center h-100 text-danger small\'><i class=\'bi bi-image-alt me-1\'></i> โหลดภาพจาก NRIIS ไม่สำเร็จ</div>';">
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center h-100 text-body-secondary">
                                                        <i class="bi bi-image fs-3"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="small text-body-secondary text-break font-monospace">
                                                <strong>ชื่อไฟล์จำลอง:</strong><br>{{ $tItem['simulated_file_name'] }}
                                            </div>
                                            @if(!empty($tItem['news_img_url']))
                                                <a href="{{ $tItem['news_img_url'] }}" target="_blank" class="btn btn-sm btn-outline-secondary mt-2">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> เปิดภาพต้นทาง
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Content & Parsed Info Column -->
                                    <div class="col-md-9">
                                        <h5 class="fw-bold text-body mb-2">
                                            {{ $tItem['news_name'] }}
                                        </h5>
                                        <div class="text-body-secondary small mb-3 d-flex flex-wrap gap-3">
                                            <span><i class="bi bi-calendar3 me-1 text-primary"></i> วันที่: {{ $tItem['news_date'] }}</span>
                                            <span><i class="bi bi-link-45deg me-1 text-primary"></i> ลิงก์ต้นฉบับ: <a href="{{ $tItem['news_link'] }}" target="_blank" class="text-decoration-none">{{ $tItem['news_link'] }}</a></span>
                                            <span><i class="bi bi-type me-1 text-primary"></i> ความยาวหัวข้อ: {{ mb_strlen($tItem['news_name']) }} ตัวอักษร</span>
                                        </div>

                                        <!-- Attachments Summary -->
                                        <div class="mb-3">
                                            <span class="fw-semibold text-body me-2"><i class="bi bi-paperclip text-primary"></i> การตรวจสอบไฟล์แนบ:</span>
                                            @if(!empty($tItem['pdf_attachment']))
                                                <a href="{{ $tItem['pdf_attachment'] }}" target="_blank" class="btn btn-sm btn-outline-danger me-2 my-1">
                                                    <i class="bi bi-file-earmark-pdf me-1"></i> พบเอกสารประกาศหลัก (PDF)
                                                </a>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle me-2">
                                                    ไม่มีเอกสารประกาศหลัก (NFILE_PDF ว่าง)
                                                </span>
                                            @endif

                                            @if(!empty($tItem['files_list']) && count($tItem['files_list']) > 0)
                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                                    <i class="bi bi-folder-check me-1"></i> พบไฟล์แนบเพิ่มเติม {{ count($tItem['files_list']) }} ไฟล์
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                                    ไม่มีไฟล์แนบเพิ่มเติม
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Collapsible Content & Payloads -->
                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#detail-{{ $idx }}">
                                                <i class="bi bi-file-richtext me-1"></i> แสดงเนื้อหา HTML ที่แปลงแล้ว
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#webhook-{{ $idx }}">
                                                <i class="bi bi-code-square me-1"></i> จำลอง Payload Make.com
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#telegram-{{ $idx }}">
                                                <i class="bi bi-telegram me-1"></i> จำลองข้อความ Telegram
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#raw-{{ $idx }}">
                                                <i class="bi bi-braces me-1"></i> Raw API Data
                                            </button>
                                        </div>

                                        <!-- HTML Detail Collapse -->
                                        <div class="collapse mt-3" id="detail-{{ $idx }}">
                                            <div class="card card-body bg-body-tertiary border small">
                                                <h6 class="fw-bold mb-2">ตัวอย่างการเรนเดอร์เนื้อหา HTML (รวม Iframe Fallback และกล่องดาวน์โหลดไฟล์):</h6>
                                                <div class="border rounded p-3 bg-body">
                                                    {!! $tItem['html_detail'] !!}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Webhook Payload Collapse -->
                                        <div class="collapse mt-3" id="webhook-{{ $idx }}">
                                            <div class="card card-body bg-body-tertiary border">
                                                <h6 class="fw-bold mb-2 text-info-emphasis"><i class="bi bi-send-check me-1"></i> โครงสร้าง JSON ที่จะส่งให้ Make.com Webhook:</h6>
                                                <pre class="bg-body p-3 rounded border font-monospace small mb-0 text-body" style="max-height: 250px; overflow-y: auto;">{{ json_encode($tItem['simulated_webhook'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        </div>

                                        <!-- Telegram Message Collapse -->
                                        <div class="collapse mt-3" id="telegram-{{ $idx }}">
                                            <div class="card card-body bg-body-tertiary border">
                                                <h6 class="fw-bold mb-2 text-success-emphasis"><i class="bi bi-chat-left-dots me-1"></i> รูปแบบข้อความที่จะยิงแจ้งเตือน Telegram:</h6>
                                                <pre class="bg-body p-3 rounded border font-monospace small mb-0 text-body" style="white-space: pre-wrap;">{{ $tItem['simulated_telegram'] }}</pre>
                                            </div>
                                        </div>

                                        <!-- Raw API Data Collapse -->
                                        <div class="collapse mt-3" id="raw-{{ $idx }}">
                                            <div class="card card-body bg-body-tertiary border">
                                                <h6 class="fw-bold mb-2 text-body-secondary"><i class="bi bi-database me-1"></i> ข้อมูล JSON ดิบที่ได้รับจาก NRIIS API:</h6>
                                                <pre class="bg-body p-3 rounded border font-monospace small mb-0 text-body" style="max-height: 300px; overflow-y: auto;">{{ json_encode($tItem['raw_api_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5 text-body-secondary">
                            <i class="bi bi-inbox fs-2 d-block mb-3 opacity-50"></i>
                            ไม่พบรายการข่าวจากการทดสอบ
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
