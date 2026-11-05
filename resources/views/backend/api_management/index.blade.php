@extends('layouts.app')

@section('title', 'ศูนย์บริหารจัดการบริการ API และ Bearer Token - PCRU RDB')

@push('styles')
<style>
    .api-kpi-card {
        border-radius: var(--rdb-radius-md, 10px);
        border: 1px solid var(--bs-border-color-translucent);
        background: var(--bs-body-bg);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .api-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--rdb-shadow-md);
    }
    .endpoint-badge-get {
        background-color: #047857;
        color: #FFFFFF;
        font-weight: 700;
        font-size: 0.75rem;
    }
    .endpoint-badge-post {
        background-color: #1D4ED8;
        color: #FFFFFF;
        font-weight: 700;
        font-size: 0.75rem;
    }
    .endpoint-badge-put {
        background-color: #D97706;
        color: #FFFFFF;
        font-weight: 700;
        font-size: 0.75rem;
    }
    .endpoint-badge-delete {
        background-color: #B91C1C;
        color: #FFFFFF;
        font-weight: 700;
        font-size: 0.75rem;
    }
    .json-code-box {
        background-color: #0F172A;
        color: #E2E8F0;
        border-radius: 8px;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 0.85rem;
        max-height: 420px;
        overflow-y: auto;
    }
    .nav-pills .nav-link {
        border-radius: 8px;
        padding: 0.6rem 1.2rem;
        font-weight: 500;
        color: var(--bs-body-color);
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link.active {
        background-color: var(--rdb-primary, #1B365D);
        color: #FFFFFF !important;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(27, 54, 93, 0.25);
    }
    [data-bs-theme="dark"] .nav-pills .nav-link.active {
        background-color: #2563EB;
        color: #FFFFFF !important;
    }
    /* TomSelect Searchable Dropdown inside Modal */
    .modal .ts-wrapper {
        width: 100% !important;
    }
    .modal .ts-control {
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
    }
    .ts-dropdown {
        z-index: 1060 !important;
    }
    [data-bs-theme="dark"] .ts-control,
    [data-bs-theme="dark"] .ts-dropdown {
        background-color: #1E293B !important;
        color: #F8FAFC !important;
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .ts-dropdown .option:hover,
    [data-bs-theme="dark"] .ts-dropdown .active {
        background-color: #334155 !important;
        color: #FFFFFF !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-body-secondary">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item active" aria-current="page">บริการ API &amp; Bearer Token</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-braces-asterisk text-primary fs-3"></i>
                {{ $isSuperAdmin ? 'ศูนย์บริหารจัดการบริการ API และ Bearer Token' : 'ศูนย์บริการ API และเครื่องมือทดสอบสำหรับนักวิจัย' }}
            </h1>
            <p class="text-body-secondary small mb-0 mt-1">
                @if($isSuperAdmin)
                    จัดการกุญแจเข้าถึงข้อมูล (Personal Access Tokens), แคตตาล็อก Endpoint ทั้งหมด, และเครื่องมือทดสอบ API แบบเรียลไทม์
                @else
                    คุณได้รับสิทธิ์เข้าถึงบริการ API เรียบร้อยแล้ว สามารถตรวจดู Endpoint ทุกจุด ทดสอบเรียกดูข้อมูลจริง และดูโค้ดตัวอย่างการเชื่อมต่อได้จากหน้านี้
                @endif
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($isSuperAdmin)
            <button type="button" class="btn btn-primary d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#createTokenModal">
                <i class="bi bi-key-fill"></i>
                <span>ออก Bearer Token ใหม่</span>
            </button>
            @endif
            <a href="#endpointsTab" onclick="switchToTab('endpoints-tab')" class="btn btn-outline-secondary d-flex align-items-center gap-1.5">
                <i class="bi bi-journal-code"></i>
                <span>ดูรายการ Endpoints</span>
            </a>
        </div>
    </div>

    <!-- Flash: Newly Generated Token Alert (Show once with 1-click copy) -->
    @if(session('newly_generated_token'))
        @php $newToken = session('newly_generated_token'); @endphp
        <div class="alert alert-success border-success-subtle shadow-sm p-4 mb-4 rounded-3 position-relative" role="alert">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-check2-circle fs-3"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="fw-bold text-success mb-1">
                            <i class="bi bi-shield-lock-fill me-1"></i> ออก Bearer Token สำเร็จเรียบร้อยแล้ว
                        </h5>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Token นี้จะแสดงเพียงครั้งเดียวเท่านั้น
                        </span>
                    </div>
                    <p class="small text-body mb-2">
                        สร้างสำหรับ: <strong>{{ $newToken['user_name'] }}</strong> ({{ $newToken['username'] }}) | 
                        ชื่อ Token: <strong>{{ $newToken['name'] }}</strong> | 
                        หมดอายุ: <strong>{{ $newToken['expires_at'] }}</strong>
                    </p>

                    <!-- Token Display & Copy Bar -->
                    <div class="input-group mb-2">
                        <input type="text" id="plainTextTokenInput" class="form-control font-monospace fw-semibold text-primary bg-body-tertiary" value="{{ $newToken['token'] }}" readonly>
                        <button class="btn btn-success d-flex align-items-center gap-1.5 px-3" type="button" onclick="copyTokenToClipboard()">
                            <i class="bi bi-clipboard-check" id="copyIcon"></i>
                            <span id="copyBtnText">คัดลอก Token</span>
                        </button>
                    </div>
                    <div class="small text-body-secondary">
                        <i class="bi bi-info-circle me-1"></i> นำ Token นี้ไปใส่ใน Header <code>Authorization: Bearer &lt;TOKEN&gt;</code> เมื่อเรียกใช้ RESTful API v1
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isSuperAdmin)
    <!-- KPI Summary Cards (Super Admin only) -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card api-kpi-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-body-secondary small fw-semibold">Bearer Tokens ทั้งหมด</div>
                        <div class="fs-3 fw-bold text-primary mt-1">{{ number_format($totalTokens) }}</div>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary p-3">
                        <i class="bi bi-key fs-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-body-secondary small">
                    <span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>{{ number_format($activeTokens) }}</span> ใช้งานได้ในปัจจุบัน
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card api-kpi-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-body-secondary small fw-semibold">สมาชิก/นักวิจัยที่เชื่อมต่อ</div>
                        <div class="fs-3 fw-bold text-success mt-1">{{ number_format($usersWithTokens) }}</div>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success p-3">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-body-secondary small">
                    มี Token อ้างอิงตัวตนในระบบ
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card api-kpi-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-body-secondary small fw-semibold">จุดเชื่อมต่อ (Endpoints)</div>
                        <div class="fs-3 fw-bold text-info mt-1">13+</div>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info p-3">
                        <i class="bi bi-diagram-3 fs-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-body-secondary small">
                    Dual-Tier: Legacy &amp; RESTful v1
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card api-kpi-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-body-secondary small fw-semibold">มาตรฐานความปลอดภัย</div>
                        <div class="fs-4 fw-bold text-warning mt-1">Sanctum v4</div>
                    </div>
                    <div class="rounded-3 bg-warning-subtle text-warning p-3">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-body-secondary small">
                    Rate Limit 60 req/min ต่อ IP
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Navigation Pills: Tokens vs Endpoints vs Developer Guide -->
    <ul class="nav nav-pills gap-2 p-1.5 bg-body-tertiary rounded-3 border mb-4" id="apiTabs" role="tablist">
        @if($isSuperAdmin)
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-flex align-items-center gap-2" id="tokens-tab" data-bs-toggle="pill" data-bs-target="#tokensTab" type="button" role="tab">
                <i class="bi bi-shield-lock"></i>
                <span>จัดการ Bearer Tokens ({{ $tokens->total() }})</span>
            </button>
        </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ !$isSuperAdmin ? 'active' : '' }} d-flex align-items-center gap-2" id="endpoints-tab" data-bs-toggle="pill" data-bs-target="#endpointsTab" type="button" role="tab">
                <i class="bi bi-journal-code"></i>
                <span>ตรวจดู Endpoint &amp; ทดสอบเรียลไทม์</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2" id="guide-tab" data-bs-toggle="pill" data-bs-target="#guideTab" type="button" role="tab">
                <i class="bi bi-file-earmark-code"></i>
                <span>คู่มือการเชื่อมต่อสำหรับนักพัฒนา</span>
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="apiTabsContent">
        @if($isSuperAdmin)
        <!-- TAB 1: Tokens Management (Super Admin only) -->
        <div class="tab-pane fade show active" id="tokensTab" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-body border-bottom py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-key-fill text-primary"></i>
                                <span>รายการ Bearer Tokens ในระบบ</span>
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('backend.api_management.index') }}" method="GET" class="d-flex gap-2 justify-content-md-end">
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary border-end-0"><i class="bi bi-search text-body-secondary"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="ค้นหาชื่อ Token, สมาชิก, PMIS..." value="{{ request('search') }}">
                                    @if(request('search'))
                                        <a href="{{ route('backend.api_management.index') }}" class="btn btn-outline-secondary">ล้าง</a>
                                    @endif
                                </div>
                                <button type="submit" class="btn btn-outline-primary">ค้นหา</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">ID</th>
                                <th>ชื่อ Token &amp; วัตถุประสงค์</th>
                                <th>สมาชิก / นักวิจัยที่ผูกไว้</th>
                                <th>ขอบเขตสิทธิ์ (Abilities)</th>
                                <th>สร้างเมื่อ / ใช้งานล่าสุด</th>
                                <th>วันหมดอายุ</th>
                                <th>สถานะ</th>
                                <th style="width: 90px;" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tokens as $t)
                                @php
                                    $user = $t->tokenable;
                                    $res = $user?->researcher;
                                    $isExpired = $t->expires_at && $t->expires_at->isPast();
                                @endphp
                                <tr>
                                    <td class="text-center font-monospace text-body-secondary small">{{ $t->id }}</td>
                                    <td>
                                        <div class="fw-semibold text-body">{{ $t->name }}</div>
                                        <div class="small text-body-secondary font-monospace">Token Hash: {{ substr($t->token, 0, 10) }}...</div>
                                    </td>
                                    <td>
                                        @if($user)
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-primary-subtle text-primary p-1.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-body">
                                                        {{ $user->name }}
                                                        <span class="text-body-secondary small">({{ $user->username }})</span>
                                                    </div>
                                                    <div class="small text-secondary">
                                                        @if($res)
                                                            <span class="badge bg-info-subtle text-info border border-info-subtle me-1">
                                                                PMIS: {{ $res->tea_code ?? '-' }}
                                                            </span>
                                                            <span>{{ $res->department?->department_nameTH ?? 'สถาบันวิจัยและพัฒนา' }}</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary">บัญชีผู้ดูแลระบบ</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-danger small"><i class="bi bi-exclamation-circle me-1"></i>ไม่พบข้อมูลผู้ใช้</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if(is_array($t->abilities))
                                                @foreach($t->abilities as $ab)
                                                    @if($ab === '*')
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                            <i class="bi bi-stars me-1"></i>Full Access (*)
                                                        </span>
                                                    @elseif(str_contains($ab, 'write'))
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                            <i class="bi bi-pencil-square me-1"></i>{{ $ab }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                            <i class="bi bi-eye me-1"></i>{{ $ab }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">{{ $t->abilities }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-body">{{ $t->created_at ? $t->created_at->format('d/m/Y H:i') : '-' }}</div>
                                        <div class="small text-body-secondary">
                                            ใช้งานล่าสุด: {{ $t->last_used_at ? $t->last_used_at->diffForHumans() : 'ยังไม่เคยใช้งาน' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($t->expires_at)
                                            <div class="small {{ $isExpired ? 'text-danger fw-bold' : 'text-body' }}">
                                                {{ $t->expires_at->format('d/m/Y H:i') }}
                                            </div>
                                            <div class="small text-body-secondary">({{ $t->expires_at->diffForHumans() }})</div>
                                        @else
                                            <span class="badge bg-light text-body-secondary border">ไม่มีวันหมดอายุ</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isExpired)
                                            <span class="badge bg-danger text-white">หมดอายุแล้ว</span>
                                        @else
                                            <span class="badge bg-success text-white">พร้อมใช้งาน</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('backend.api_management.tokens.revoke', $t->id) }}" method="POST" onsubmit="return confirm('ยืนยันการเพิกถอน Token \"{{ $t->name }}\" หรือไม่? เมื่อเพิกถอนแล้วระบบที่ใช้ Token นี้จะไม่สามารถเข้าถึง API ได้อีก');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="เพิกถอน Token">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-body-secondary">
                                        <i class="bi bi-key fs-1 d-block text-secondary opacity-50 mb-2"></i>
                                        <div class="fw-semibold">ยังไม่มี Bearer Token ในระบบ</div>
                                        <div class="small mb-3">กดปุ่ม "ออก Bearer Token ใหม่" เพื่อสร้างกุญแจสำหรับสมาชิกหรือนักวิจัย</div>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createTokenModal">
                                            <i class="bi bi-plus-lg me-1"></i> ออก Token รายการแรก
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($tokens->hasPages())
                    <div class="card-footer bg-body border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="small text-body-secondary">
                            แสดงรายการ {{ $tokens->firstItem() }} ถึง {{ $tokens->lastItem() }} จากทั้งหมด {{ $tokens->total() }} รายการ
                        </div>
                        <div>
                            {{ $tokens->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- TAB 2: Endpoints Catalog & Live Tester -->
        <div class="tab-pane fade {{ !$isSuperAdmin ? 'show active' : '' }}" id="endpointsTab" role="tabpanel">
            <div class="row g-4">
                <!-- Left: Catalog Accordion -->
                <div class="col-lg-7">
                    @foreach($endpoints as $tierKey => $tier)
                        <div class="card border-0 shadow-sm rounded-3 mb-4">
                            <div class="card-header bg-body border-bottom py-3 px-4">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <h5 class="fw-bold mb-0 text-{{ $tier['color'] }} d-flex align-items-center gap-2">
                                        <i class="bi {{ $tierKey === 'tier1' ? 'bi-link-45deg' : ($tierKey === 'tier3' ? 'bi-megaphone-fill' : 'bi-stars') }}"></i>
                                        <span>{{ $tier['name'] }}</span>
                                    </h5>
                                    <span class="badge bg-{{ $tier['color'] }}-subtle text-{{ $tier['color'] }} border border-{{ $tier['color'] }}-subtle px-2.5 py-1">
                                        {{ $tier['badge'] }}
                                    </span>
                                </div>
                                <p class="small text-body-secondary mb-0 mt-1">{{ $tier['description'] }}</p>
                            </div>

                            <div class="list-group list-group-flush">
                                @foreach($tier['items'] as $item)
                                    <div class="list-group-item p-3 border-bottom">
                                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="badge endpoint-badge-{{ strtolower($item['method']) }} px-2 py-1 rounded">
                                                    {{ $item['method'] }}
                                                </span>
                                                <code class="fs-6 fw-bold text-body">{{ $item['path'] }}</code>
                                                @if($item['auth_type'] === 'bearer')
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle small">
                                                        <i class="bi bi-shield-lock-fill me-1"></i>Bearer Token
                                                    </span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                                        <i class="bi bi-globe me-1"></i>Public Feed
                                                    </span>
                                                @endif
                                            </div>

                                            <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" onclick="loadTester('{{ $item['method'] }}', '{{ $item['sample_url'] }}', '{{ $item['auth_type'] }}')">
                                                <i class="bi bi-play-circle-fill"></i>
                                                <span>ทดสอบเรียกดู</span>
                                            </button>
                                        </div>

                                        <div class="fw-semibold text-body mb-1">{{ $item['title'] }}</div>
                                        <p class="small text-body-secondary mb-2">{{ $item['desc'] }}</p>

                                        @if(!empty($item['alt_paths']))
                                            <div class="small text-body-secondary mb-2">
                                                <span class="fw-semibold">Path สำรอง / Aliases:</span>
                                                @foreach($item['alt_paths'] as $ap)
                                                    <code class="me-1">{{ $ap }}</code>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(!empty($item['params']))
                                            <div class="bg-body-tertiary p-2.5 rounded-2 small mt-2">
                                                <div class="fw-bold text-secondary mb-1">พารามิเตอร์ที่รองรับ (Query Parameters):</div>
                                                <ul class="mb-0 ps-3">
                                                    @foreach($item['params'] as $p)
                                                        <li>
                                                            <code>{{ $p['name'] }}</code> <span class="text-body-secondary">({{ $p['type'] }})</span> - {{ $p['desc'] }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Right: Interactive Live Tester Console -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 80px; z-index: 1020;">
                        <div class="card-header bg-body border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-terminal text-success"></i>
                                <span>คอนโซลทดสอบ API แบบเรียลไทม์</span>
                            </h5>
                            <span id="testerStatusBadge" class="badge bg-secondary-subtle text-secondary">พร้อมทดสอบ</span>
                        </div>

                        <div class="card-body p-4">
                            <form id="liveTesterForm" class="no-loading" onsubmit="event.preventDefault(); executeTestCall();">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold d-flex justify-content-between align-items-center">
                                        <span>HTTP Method &amp; Endpoint Path:</span>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                            <i class="bi bi-shield-check me-1"></i>Read-Only (GET เท่านั้น)
                                        </span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text font-monospace fw-bold bg-success text-white px-3">GET</span>
                                        <input type="hidden" id="testerMethod" value="GET">
                                        <input type="text" id="testerPath" class="form-control font-monospace" placeholder="/rdbproject/jsonprojectall?dep=2" value="/rdbproject/jsonprojectall?dep=2" required>
                                    </div>
                                </div>

                                <div class="mb-3" id="testerTokenGroup">
                                    <label class="form-label small fw-bold d-flex justify-content-between">
                                        <span>Bearer Token (สำหรับ Endpoint ที่ต้องยืนยันตัวตน):</span>
                                        @if(session('newly_generated_token'))
                                            <a href="javascript:void(0)" onclick="fillRecentToken()" class="text-decoration-none small text-success">
                                                <i class="bi bi-magic me-1"></i>ใช้ Token ที่เพิ่งสร้าง
                                            </a>
                                        @endif
                                    </label>
                                    <input type="text" id="testerToken" class="form-control font-monospace small" placeholder="ใส่ Bearer Token หากจำเป็น (ไม่ต้องใส่คำว่า Bearer)">
                                </div>

                                <button type="button" id="testerSubmitBtn" onclick="executeTestCall()" class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold">
                                    <i class="bi bi-send-fill" id="testerSubmitIcon"></i>
                                    <span id="testerSubmitText">ส่งคำขอเรียกดูข้อมูล (Send Request)</span>
                                </button>
                            </form>

                            <!-- Response Metrics Bar -->
                            <div id="testerMetricsBar" class="d-none mt-3 p-2 bg-body-tertiary rounded-2 d-flex align-items-center justify-content-between small">
                                <div>
                                    สถานะ: <span id="resStatusBadge" class="badge bg-success">200 OK</span>
                                </div>
                                <div>
                                    ความเร็ว: <span id="resDurationBadge" class="fw-bold font-monospace">0 ms</span>
                                </div>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="copyResponseJson()">
                                    <i class="bi bi-clipboard me-1.5"></i> คัดลอก JSON
                                </button>
                            </div>

                            <!-- Response JSON Box -->
                            <div class="mt-3">
                                <label class="form-label small fw-bold text-body-secondary mb-1">ผลลัพธ์ (Response JSON Payload):</label>
                                <pre id="testerResponseBox" class="json-code-box p-3 mb-0"><code>{
  "message": "กดปุ่ม 'ส่งคำขอเรียกดูข้อมูล' ด้านบน หรือคลิกปุ่ม 'ทดสอบเรียกดู' จากรายการ Endpoint ทางซ้ายมือ เพื่อดูข้อมูลจริง"
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: Developer Guide & Examples -->
        <div class="tab-pane fade" id="guideTab" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h4 class="fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-code-square"></i>
                    <span>คู่มือและตัวอย่างโค้ดการเชื่อมต่อ API สำหรับนักพัฒนา (Developer Guide)</span>
                </h4>
                <p class="text-body-secondary mb-3">
                    ระบบ API ของสถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์ ทำงานบนมาตรฐาน HTTP RESTful รองรับการเรียกใช้จากทุกภาษาและ Framework
                </p>

                <!-- Security & Token Policy Callout -->
                <div class="alert alert-info border-0 rounded-3 mb-4 d-flex align-items-start gap-3 py-3">
                    <i class="bi bi-shield-lock-fill fs-3 text-info mt-1"></i>
                    <div>
                        <div class="fw-bold text-info-emphasis mb-1">นโยบายความปลอดภัยและการขอรับ Bearer Token</div>
                        <ul class="small text-secondary mb-0 ps-3">
                            <li><strong>การขอรับ Bearer Token ใหม่:</strong> ระบบไม่อนุญาตให้ออก Token ผ่าน API โดยตรง สมาชิก/นักวิจัยต้องติดต่อขอให้<strong>ผู้ดูแลระบบระดับสูงสุด (Super Admin)</strong> เป็นผู้ออก Token ให้ผ่านศูนย์บริหารจัดการ API หลังบ้านเท่านั้น</li>
                            <li><strong>มาตรฐาน Read-Only:</strong> ระบบ API สำหรับนักวิจัยเปิดให้บริการเฉพาะการเรียกดูข้อมูล (คำขอแบบ <code>GET</code> เท่านั้น)</li>
                            <li><strong>การเพิกถอน Token ด้วยตนเอง (Self-Revocation):</strong> หากท่านเลิกใช้งาน Token ใด สามารถส่งคำขอ <code>POST /api/v1/auth/logout</code> พร้อมแนบ Bearer Token เพื่อลบ Token นั้นออกจากฐานข้อมูลได้ทันที</li>
                        </ul>
                    </div>
                </div>

                <!-- Code Carousel / Language Tabs -->
                <ul class="nav nav-tabs mb-3" id="codeLangTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold" id="curl-tab" data-bs-toggle="tab" data-bs-target="#curlCode" type="button">cURL (CLI)</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="js-tab" data-bs-toggle="tab" data-bs-target="#jsCode" type="button">JavaScript (Fetch / Axios)</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="php-tab" data-bs-toggle="tab" data-bs-target="#phpCode" type="button">PHP (Guzzle / cURL)</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="python-tab" data-bs-toggle="tab" data-bs-target="#pythonCode" type="button">Python (Requests)</button>
                    </li>
                </ul>

                <div class="tab-content" id="codeLangTabContent">
                    <!-- cURL -->
                    <div class="tab-pane fade show active" id="curlCode">
                        <pre class="json-code-box p-3 mb-0"><code># 1. เรียกดูข้อมูลโครงการวิจัย (Legacy Feed)
curl -X GET "http://laravel_rdb.test/rdbproject/jsonprojectall?dep=2&year=2567" \
     -H "Accept: application/json"

# 2. เรียกดูข้อมูลด้วย RESTful API v1 พร้อม Bearer Token
curl -X GET "http://laravel_rdb.test/api/v1/projects?dep=2&year=2567" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer YOUR_BEARER_TOKEN"

# 3. เรียกดูข้อมูลงานนำไปใช้ประโยชน์ (Research Utilizations)
curl -X GET "http://laravel_rdb.test/api/v1/utilizations?dep=2" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer YOUR_BEARER_TOKEN"

# 4. เรียกดูข้อมูลงานทรัพย์สินทางปัญญา (Intellectual Properties / DIP)
curl -X GET "http://laravel_rdb.test/api/v1/intellectual-properties?dipt_id=2" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer YOUR_BEARER_TOKEN"

# 5. เรียกดูข่าวทุนวิจัย (Open Public API - ไม่ต้องใช้ Token)
curl -X GET "http://laravel_rdb.test/api/v1/news?limit=5" \
     -H "Accept: application/json"

# 6. เรียกดูงานประชุมวิชาการ (Open Public API - ไม่ต้องใช้ Token)
curl -X GET "http://laravel_rdb.test/api/v1/conferences?limit=5" \
     -H "Accept: application/json"

# 7. เพิกถอน Token ของตนเองเมื่อเลิกใช้งาน (Self Token Deletion)
curl -X POST "http://laravel_rdb.test/api/v1/auth/logout" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer YOUR_BEARER_TOKEN"</code></pre>
                    </div>

                    <!-- JavaScript -->
                    <div class="tab-pane fade" id="jsCode">
                        <pre class="json-code-box p-3 mb-0"><code>// ตัวอย่างการเรียกใช้ด้วย Fetch API (JavaScript ES6+)
const token = 'YOUR_BEARER_TOKEN';

async function fetchResearcherPortfolio(pmis) {
    try {
        const response = await fetch(`http://laravel_rdb.test/api/v1/researchers/${pmis}/portfolio`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });
        const data = await response.json();
        console.log('Portfolio Data:', data);
    } catch (error) {
        console.error('API Error:', error);
    }
}

fetchResearcherPortfolio('656219');</code></pre>
                    </div>

                    <!-- PHP -->
                    <div class="tab-pane fade" id="phpCode">
                        <pre class="json-code-box p-3 mb-0"><code>&lt;?php
// ตัวอย่างการเรียกใช้ด้วย PHP cURL
$token = 'YOUR_BEARER_TOKEN';
$url = 'http://laravel_rdb.test/api/v1/projects?dep=2&year=2567';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Authorization: Bearer ' . $token,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
print_r($data);
?&gt;</code></pre>
                    </div>

                    <!-- Python -->
                    <div class="tab-pane fade" id="pythonCode">
                        <pre class="json-code-box p-3 mb-0"><code># ตัวอย่างการเรียกใช้ด้วย Python Requests
import requests

token = "YOUR_BEARER_TOKEN"
url = "http://laravel_rdb.test/api/v1/publications"
params = {
    "dep": 6,
    "year": 2568
}
headers = {
    "Accept": "application/json",
    "Authorization": f"Bearer {token}"
}

response = requests.get(url, headers=headers, params=params)
if response.status_code == 200:
    data = response.json()
    print("Publications Count:", len(data.get("data", [])))
else:
    print(f"Error {response.status_code}:", response.text)</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($isSuperAdmin)
<!-- Modal: Create / Issue New Token (Super Admin only) -->
<div class="modal fade" id="createTokenModal" tabindex="-1" aria-labelledby="createTokenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <form action="{{ route('backend.api_management.tokens.create') }}" method="POST">
                @csrf
                <div class="modal-header bg-body border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="createTokenModalLabel">
                        <i class="bi bi-key-fill text-primary"></i>
                        <span>ออก Bearer Token สำหรับสมาชิก / นักวิจัย</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-toggle="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Select Member / Researcher with Searchable Typeahead -->
                    <div class="mb-3">
                        <label for="modalUserId" class="form-label fw-bold small">
                            สมาชิก / นักวิจัยที่ต้องการออก Token <span class="text-danger">*</span>
                        </label>
                        <select name="user_id" id="modalUserId" class="form-select" required>
                            <option value="">-- พิมพ์ค้นหาชื่อสมาชิก, username, รหัสนักวิจัย หรือคณะ/สังกัด --</option>
                            @foreach($usersList as $u)
                                @php
                                    $r = $u->researcher;
                                    $pmisText = $r && $r->tea_code ? " [PMIS: {$r->tea_code}]" : '';
                                    $deptText = $r && $r->department ? " - {$r->department->department_nameTH}" : '';
                                @endphp
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->username }}){{ $pmisText }}{{ $deptText }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small text-body-secondary">
                            Token จะถูกผูกกับสิทธิ์และโปรไฟล์ของสมาชิกท่านนี้ในระบบ (สามารถพิมพ์ค้นหาได้ทันที)
                        </div>
                    </div>

                    <!-- Token Name / Application Purpose -->
                    <div class="mb-3">
                        <label for="modalTokenName" class="form-label fw-bold small">
                            ชื่อ Token / วัตถุประสงค์การเชื่อมต่อ <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="modalTokenName" class="form-control" placeholder="เช่น ระบบสารสนเทศคณะวิทยาศาสตร์, Mobile App, PMIS Sync" required value="{{ old('name') }}">
                        <div class="form-text small text-body-secondary">ระบุชื่อเพื่อให้จดจำได้ง่ายเมื่อต้องการตรวจสอบประวัติการใช้งาน</div>
                    </div>

                    <!-- Expiration Duration -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">อายุการใช้งาน Token:</label>
                        <div class="row g-2">
                            <div class="col-6 col-sm-3">
                                <input type="radio" class="btn-check" name="expires_in_days" id="expNever" value="" checked>
                                <label class="btn btn-outline-secondary w-100 text-truncate small py-2" for="expNever">ไม่มีวันหมดอายุ</label>
                            </div>
                            <div class="col-6 col-sm-3">
                                <input type="radio" class="btn-check" name="expires_in_days" id="exp30" value="30">
                                <label class="btn btn-outline-secondary w-100 text-truncate small py-2" for="exp30">30 วัน</label>
                            </div>
                            <div class="col-6 col-sm-3">
                                <input type="radio" class="btn-check" name="expires_in_days" id="exp90" value="90">
                                <label class="btn btn-outline-secondary w-100 text-truncate small py-2" for="exp90">90 วัน</label>
                            </div>
                            <div class="col-6 col-sm-3">
                                <input type="radio" class="btn-check" name="expires_in_days" id="exp365" value="365">
                                <label class="btn btn-outline-secondary w-100 text-truncate small py-2" for="exp365">1 ปี (365 วัน)</label>
                            </div>
                        </div>
                    </div>

                    <!-- Abilities / Scopes Checkboxes -->
                    <div class="mb-2">
                        <label class="form-label fw-bold small">ขอบเขตสิทธิ์การเข้าถึงข้อมูล (Token Abilities):</label>
                        <div class="bg-body-tertiary p-3 rounded-3 border">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="abilities[]" value="*" id="abFull" onchange="toggleFullAccess(this)">
                                <label class="form-check-label fw-bold text-danger" for="abFull">
                                    <i class="bi bi-stars me-1"></i> สิทธิ์สูงสุด (Full Access - ทุกความสามารถ)
                                </label>
                            </div>
                            <hr class="my-2">
                            <div class="row g-2" id="granularAbilities">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input ability-item" type="checkbox" name="abilities[]" value="project:read" id="abProjRead" checked>
                                        <label class="form-check-label small" for="abProjRead">
                                            <code>project:read</code> ดึงข้อมูลโครงการวิจัย (GET)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input ability-item" type="checkbox" name="abilities[]" value="publication:read" id="abPubRead" checked>
                                        <label class="form-check-label small" for="abPubRead">
                                            <code>publication:read</code> ดึงข้อมูลผลงานตีพิมพ์ (GET)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input ability-item" type="checkbox" name="abilities[]" value="utilize:read" id="abUtzRead" checked>
                                        <label class="form-check-label small" for="abUtzRead">
                                            <code>utilize:read</code> ดึงข้อมูลการนำไปใช้ประโยชน์ (GET)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input ability-item" type="checkbox" name="abilities[]" value="ip:read" id="abIpRead" checked>
                                        <label class="form-check-label small" for="abIpRead">
                                            <code>ip:read</code> ดึงข้อมูลทรัพย์สินทางปัญญา (GET)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2.5 p-2 bg-success-subtle border border-success-subtle rounded small text-success">
                                <i class="bi bi-shield-check me-1"></i> <strong>นโยบายความปลอดภัยระบบ:</strong> บริการ API เปิดให้เรียกดูข้อมูลวิจัย (GET) เท่านั้น เพื่อความปลอดภัยของข้อมูลวิจัยและป้องกันการดัดแปลงข้อมูล
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-body border-top py-3 px-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1.5 px-4">
                        <i class="bi bi-check-lg"></i>
                        <span>ยืนยันการออก Token</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Initialize TomSelect for searchable Member / Researcher selection in Token Issuance modal
    document.addEventListener('DOMContentLoaded', function () {
        const modalUserIdEl = document.getElementById('modalUserId');
        if (modalUserIdEl && typeof TomSelect !== 'undefined') {
            const userTomSelect = new TomSelect(modalUserIdEl, {
                create: false,
                placeholder: '-- พิมพ์ค้นหาชื่อสมาชิก, username, รหัสนักวิจัย หรือคณะ/สังกัด --',
                allowEmptyOption: true,
                maxOptions: 1000,
                searchField: ['text'],
            });

            const createModal = document.getElementById('createTokenModal');
            if (createModal) {
                createModal.addEventListener('shown.bs.modal', function () {
                    setTimeout(() => {
                        userTomSelect.focus();
                    }, 150);
                });
            }
        }
    });

    // Tab switching helper
    function switchToTab(tabId) {
        const tabEl = document.getElementById(tabId);
        if (tabEl) {
            const tabInstance = new bootstrap.Tab(tabEl);
            tabInstance.show();
        }
    }

    // Toggle abilities when Full Access is checked
    function toggleFullAccess(checkbox) {
        const items = document.querySelectorAll('.ability-item');
        items.forEach(el => {
            el.disabled = checkbox.checked;
        });
    }

    // Copy newly generated token
    function copyTokenToClipboard() {
        const input = document.getElementById('plainTextTokenInput');
        if (input) {
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(() => {
                document.getElementById('copyBtnText').innerText = 'คัดลอกเรียบร้อย!';
                document.getElementById('copyIcon').className = 'bi bi-check-lg text-white';
                setTimeout(() => {
                    document.getElementById('copyBtnText').innerText = 'คัดลอก Token';
                    document.getElementById('copyIcon').className = 'bi bi-clipboard-check';
                }, 2500);
            });
        }
    }

    // Preload endpoint into tester
    function loadTester(method, path, authType) {
        document.getElementById('testerMethod').value = method;
        document.getElementById('testerPath').value = path;
        
        // Scroll to tester
        switchToTab('endpoints-tab');
        const tester = document.getElementById('liveTesterForm');
        if (tester) {
            tester.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Fill recent token from session into tester
    function fillRecentToken() {
        @if(session('newly_generated_token'))
            document.getElementById('testerToken').value = "{{ session('newly_generated_token')['token'] }}";
        @endif
    }

    // Escape HTML characters for code block rendering
    function escapeHtml(text) {
        if (typeof text !== 'string') text = String(text);
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Execute live test call directly via client-side fetch (zero session interference)
    async function executeTestCall() {
        const pathInput = document.getElementById('testerPath');
        let path = pathInput ? pathInput.value.trim() : '';
        if (!path) {
            alert('กรุณาระบุ Endpoint Path');
            return;
        }

        const method = document.getElementById('testerMethod').value;
        const token = document.getElementById('testerToken').value.trim();

        const btn = document.getElementById('testerSubmitBtn');
        const statusBadge = document.getElementById('testerStatusBadge');
        const metricsBar = document.getElementById('testerMetricsBar');
        const resStatusBadge = document.getElementById('resStatusBadge');
        const resDurationBadge = document.getElementById('resDurationBadge');
        const responseBox = document.getElementById('testerResponseBox');

        // Save original button state and show loading
        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> กำลังส่งคำขอ...';
        
        statusBadge.className = 'badge bg-warning text-dark';
        statusBadge.innerText = 'กำลังเรียกดูข้อมูล...';

        const startTime = performance.now();

        try {
            // Build full URL for direct client-side fetch
            const fullUrl = path.startsWith('http://') || path.startsWith('https://') 
                ? path 
                : (window.location.origin + '/' + path.replace(/^\//, ''));

            const headers = {
                'Accept': 'application/json'
            };
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }

            const fetchOptions = {
                method: method,
                headers: headers
            };

            const res = await fetch(fullUrl, fetchOptions);
            const durationMs = Math.round(performance.now() - startTime);

            let payload;
            const contentType = res.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                payload = await res.json();
            } else {
                const text = await res.text();
                try {
                    payload = JSON.parse(text);
                } catch {
                    payload = text;
                }
            }

            // Render metrics
            metricsBar.classList.remove('d-none');
            resDurationBadge.innerText = `${durationMs} ms`;

            if (res.status >= 200 && res.status < 300) {
                resStatusBadge.className = 'badge bg-success';
                resStatusBadge.innerText = `${res.status} OK`;
                statusBadge.className = 'badge bg-success';
                statusBadge.innerText = 'สำเร็จ';
            } else if (res.status === 401) {
                resStatusBadge.className = 'badge bg-warning text-dark';
                resStatusBadge.innerText = `401 Unauthorized`;
                statusBadge.className = 'badge bg-warning text-dark';
                statusBadge.innerText = 'ต้องยืนยันตัวตน (Bearer Token)';
            } else if (res.status === 403) {
                resStatusBadge.className = 'badge bg-danger';
                resStatusBadge.innerText = `403 Forbidden`;
                statusBadge.className = 'badge bg-danger';
                statusBadge.innerText = 'ไม่มีสิทธิ์เข้าถึง (Abilities)';
            } else if (res.status === 404) {
                resStatusBadge.className = 'badge bg-secondary';
                resStatusBadge.innerText = `404 Not Found`;
                statusBadge.className = 'badge bg-secondary';
                statusBadge.innerText = 'ไม่พบ Endpoint หรือข้อมูล';
            } else if (res.status === 422) {
                resStatusBadge.className = 'badge bg-warning text-dark';
                resStatusBadge.innerText = `422 Unprocessable`;
                statusBadge.className = 'badge bg-warning text-dark';
                statusBadge.innerText = 'ข้อมูลไม่ถูกต้อง';
            } else {
                resStatusBadge.className = 'badge bg-danger';
                resStatusBadge.innerText = `${res.status} ${res.statusText || 'Error'}`;
                statusBadge.className = 'badge bg-danger';
                statusBadge.innerText = 'ผิดพลาด';
            }

            // Render JSON
            const formattedJson = typeof payload === 'object' ? JSON.stringify(payload, null, 2) : payload;
            responseBox.innerHTML = `<code>${escapeHtml(formattedJson)}</code>`;

        } catch (err) {
            const durationMs = Math.round(performance.now() - startTime);
            metricsBar.classList.remove('d-none');
            resDurationBadge.innerText = `${durationMs} ms`;
            resStatusBadge.className = 'badge bg-danger';
            resStatusBadge.innerText = 'Network Error';
            statusBadge.className = 'badge bg-danger';
            statusBadge.innerText = 'ผิดพลาด';
            responseBox.innerHTML = `<code>${escapeHtml(JSON.stringify({ error: err.message || 'Network request failed' }, null, 2))}</code>`;
        } finally {
            // Restore button state
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        }
    }

    // Copy JSON from tester box
    function copyResponseJson() {
        const text = document.getElementById('testerResponseBox').innerText;
        navigator.clipboard.writeText(text).then(() => {
            alert('คัดลอกผลลัพธ์ JSON เรียบร้อยแล้ว');
        });
    }
</script>
@endpush
