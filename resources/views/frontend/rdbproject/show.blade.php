@extends('layouts.app')

@section('content')
@include('layouts.partials.scholar-meta', ['type' => 'project', 'item' => $item])
<div class="container-fluid py-4">
    @php
        if (!function_exists('getFrontendUserName')) {
            function getFrontendUserName($user) {
                if(!$user) return '-';
                if($user->researcher) {
                    $r = $user->researcher;
                    return ($r->prefix->prefix_nameTH ?? '') . $r->researcher_fname . ' ' . $r->researcher_lname;
                }
                return $user->username ?? $user->email ?? '-';
            }
        }
    @endphp

    <!-- Breadcrumb & Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('frontend.rdbproject.index') }}" class="text-decoration-none">โครงการวิจัย</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายละเอียดโครงการ</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-folder2-open me-2 text-primary"></i>รายละเอียดโครงการวิจัย
            </h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontend.rdbproject.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-printer me-1"></i> พิมพ์
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content (Left Column: col-lg-8) -->
        <div class="col-lg-8">
            {{-- General Information Card --}}
            <div class="card shadow-sm border mb-4">
                <div class="card-header text-white p-3 p-md-4" style="background: var(--rdb-gradient-primary);">
                    <span class="small text-white-50"><i class="bi bi-folder2-open me-1"></i> ข้อมูลโครงการวิจัย</span>
                    <h4 class="card-title mb-0 mt-1 fw-semibold text-white text-break" style="font-size: 1.15rem; line-height: 1.55;">{{ strip_tags((string) $item->pro_nameTH) }}</h4>
                    @if($item->pro_nameEN)
                        <p class="text-white-50 small mb-0 mt-1 text-break">{{ strip_tags((string) $item->pro_nameEN) }}</p>
                    @endif
                </div>
                <div class="card-body">
                    <!-- Badges Row -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if($item->pro_code)
                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2.5 py-1">
                                <i class="bi bi-hash me-0.5"></i> รหัส: {{ $item->pro_code }}
                            </span>
                        @endif
                        @if($item->year)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                                <i class="bi bi-calendar3 me-1"></i> ปีงบประมาณ: {{ $item->year->year_name }}
                            </span>
                        @endif
                        @if($item->status)
                            <span class="badge px-2.5 py-1" style="background-color: {{ $item->status->ps_color ?? '#6c757d' }}; color: #fff;">
                                <i class="bi bi-flag me-1"></i> สถานะ: {{ $item->status->ps_name }}
                            </span>
                        @endif
                    </div>

                    <!-- Additional Details Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-body-secondary fw-semibold" style="width: 25%;">ประเภททุน:</th>
                                    <td class="text-body">
                                        {{ $item->type->pt_name ?? '-' }}
                                        @if($item->typeSub)
                                            <span class="text-body-secondary small ms-1">({{ $item->typeSub->pts_name }})</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-body-secondary fw-semibold">งบประมาณ:</th>
                                    <td><span class="text-success fw-bold">{{ number_format($item->pro_budget, 2) }}</span> <span class="text-body-secondary small">บาท</span></td>
                                </tr>
                                <tr>
                                    <th class="text-body-secondary fw-semibold">ระยะเวลาโครงการ:</th>
                                    <td class="text-body">
                                        {{ \App\Helpers\ThaiDateHelper::format($item->pro_date_start) }}
                                        <span class="text-body-secondary mx-1">ถึง</span>
                                        {{ \App\Helpers\ThaiDateHelper::format($item->pro_date_end) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-body-secondary fw-semibold">หน่วยงาน:</th>
                                    <td class="text-body">{{ $item->department->department_nameTH ?? '-' }}</td>
                                </tr>
                                @if($item->strategic)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">ยุทธศาสตร์:</th>
                                    <td class="text-body">{{ $item->strategic->strategic_name ?? '-' }}</td>
                                </tr>
                                @endif
                                @if($item->group || $item->pro_group)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">กลุ่มโครงการ:</th>
                                    <td class="text-body">
                                        @if($item->group)
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2 py-0.5 me-1">
                                                {{ $item->group->pgroup_nameTH ?? ($item->group->pgroup_nameEN ?? '-') }}
                                            </span>
                                        @endif

                                        {{-- If this is a sub-project, show parent plan (แผนงาน) --}}
                                        @if(($item->pgroup_id == 2 || ($item->pro_group && $item->pro_group != $item->pro_id)) && $item->parentProject)
                                            <div class="mt-1.5 p-2 rounded bg-body-tertiary border small">
                                                <div class="text-body-secondary fw-semibold mb-0.5">
                                                    <i class="bi bi-diagram-3-fill text-primary me-1"></i>ภายใต้แผนงานวิจัย:
                                                </div>
                                                <a href="{{ route('frontend.rdbproject.show', $item->parentProject->pro_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-medium text-primary hover-underline">
                                                    {{ $item->parentProject->pro_nameTH }}
                                                </a>
                                            </div>
                                        @endif

                                        {{-- If this is a parent plan (แผนงานวิจัย), show list of sub-projects --}}
                                        @if($item->pgroup_id == 1 && $item->subProjects && $item->subProjects->count() > 0)
                                            <div class="mt-2 p-2 rounded bg-body-tertiary border small">
                                                <div class="text-body-secondary fw-semibold mb-1">
                                                    <i class="bi bi-collection-fill text-success me-1"></i>โครงการวิจัยย่อยภายใต้แผนงานนี้ ({{ $item->subProjects->count() }} โครงการ):
                                                </div>
                                                <ol class="mb-0 ps-3">
                                                    @foreach($item->subProjects as $sub)
                                                        <li class="mb-1">
                                                            <a href="{{ route('frontend.rdbproject.show', $sub->pro_id) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body hover-underline">
                                                                {{ $sub->pro_nameTH }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ol>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if($item->pro_keyword)
                                <tr>
                                    <th class="text-body-secondary fw-semibold">คำสำคัญ:</th>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach(explode(',', $item->pro_keyword) as $keyword)
                                                @if(trim($keyword))
                                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2 py-0.5">
                                                        {{ trim($keyword) }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th class="text-body-secondary fw-semibold">จำนวนการเข้าดู:</th>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1">
                                            <i class="bi bi-eye me-1"></i> {{ number_format($item->pro_count_page ?? 0) }} ครั้ง
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Abstract Card --}}
            @if($item->pro_abstract)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-file-text-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">บทคัดย่อ (Abstract)</h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    @php
                        $abstractParts = \App\Helpers\AbstractHelper::split($item->pro_abstract);
                    @endphp

                    @if(count($abstractParts) >= 2 && !empty(trim(strip_tags($abstractParts[0]))) && !empty(trim(strip_tags($abstractParts[1]))))
                        <div class="abstract-box p-3 p-md-4 border border-start border-4 border-primary rounded-3 mb-3 bg-body-tertiary shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                        <i class="bi bi-card-text me-1"></i> บทคัดย่อ (ภาษาไทย)
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.7rem;">TH</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText(this, 'abstract-th-content')">
                                    <i class="bi bi-clipboard me-1"></i> คัดลอกบทคัดย่อ
                                </button>
                            </div>
                            <div id="abstract-th-content" class="abstract-content abstract-indent">
                                {!! strip_tags((string) $abstractParts[0], '<p><br><b><strong><i><em><u><ul><ol><li><span><sub><sup>') !!}
                            </div>
                        </div>

                        <div class="abstract-box p-3 p-md-4 border border-start border-4 border-info rounded-3 bg-body-tertiary shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                        <i class="bi bi-translate me-1"></i> Abstract (English)
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.7rem;">EN</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText(this, 'abstract-en-content')">
                                    <i class="bi bi-clipboard me-1"></i> Copy Abstract
                                </button>
                            </div>
                            <div id="abstract-en-content" class="abstract-content abstract-indent">
                                {!! strip_tags((string) $abstractParts[1], '<p><br><b><strong><i><em><u><ul><ol><li><span><sub><sup>') !!}
                            </div>
                        </div>
                    @else
                        <div class="abstract-box p-3 p-md-4 border border-start border-4 border-primary rounded-3 bg-body-tertiary shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle border-opacity-50">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-file-text me-1"></i> บทคัดย่อ
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText(this, 'abstract-single-content')">
                                    <i class="bi bi-clipboard me-1"></i> คัดลอกบทคัดย่อ
                                </button>
                            </div>
                            <div id="abstract-single-content" class="abstract-content abstract-indent">
                                {!! strip_tags((string) $item->pro_abstract, '<p><br><b><strong><i><em><u><ul><ol><li><span><sub><sup>') !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Note / หมายเหตุ --}}
            @if($item->pro_note)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-sticky text-warning me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold text-body">หมายเหตุ</h5>
                </div>
                <div class="card-body">
                    <div class="text-body text-break lh-lg">
                        {!! nl2br(e($item->pro_note)) !!}
                    </div>
                </div>
            </div>
            @endif

            {{-- Related Outputs Row --}}
            @if($item->utilizations->count() > 0 || $item->publisheds->count() > 0 || $item->dips->count() > 0)
            <div class="row g-3 mb-4">
                @if($item->utilizations->count() > 0)
                <div class="col-md-4">
                    <a href="{{ route('frontend.rdbprojectutilize.index', ['pro_id' => $item->pro_id]) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                        <div class="card h-100 border shadow-sm hover-shadow transition-all">
                            <div class="card-body text-center p-3">
                                <i class="bi bi-graph-up-arrow fs-1 text-success mb-2 d-block"></i>
                                <h6 class="text-body fw-bold mb-1" style="font-size: 0.9rem;">การนำไปใช้ประโยชน์</h6>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                    {{ $item->utilizations->count() }} รายการ
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                
                @if($item->publisheds->count() > 0)
                <div class="col-md-4">
                    <a href="{{ route('frontend.rdbpublished.index', ['pro_id' => $item->pro_id]) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                        <div class="card h-100 border shadow-sm hover-shadow transition-all">
                            <div class="card-body text-center p-3">
                                <i class="bi bi-journal-text fs-1 text-info mb-2 d-block"></i>
                                <h6 class="text-body fw-bold mb-1" style="font-size: 0.9rem;">ผลงานตีพิมพ์เผยแพร่</h6>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2.5 py-1">
                                    {{ $item->publisheds->count() }} ผลงาน
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                @endif

                @if($item->dips->count() > 0)
                <div class="col-md-4">
                    <a href="{{ route('frontend.rdbdip.index', ['pro_id' => $item->pro_id]) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                        <div class="card h-100 border shadow-sm hover-shadow transition-all">
                            <div class="card-body text-center p-3">
                                <i class="bi bi-award fs-1 text-warning mb-2 d-block"></i>
                                <h6 class="text-body fw-bold mb-1" style="font-size: 0.9rem;">ทรัพย์สินทางปัญญา</h6>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                    {{ $item->dips->count() }} รายการ
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
            </div>
            @endif

            {{-- Action Buttons --}}
            <div class="d-flex justify-content-start gap-2 mb-4">
                <a href="{{ route('frontend.rdbproject.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1.5"></i> กลับหน้ารายการ
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary d-inline-flex align-items-center">
                    <i class="bi bi-printer me-1.5"></i> พิมพ์เอกสาร
                </button>
            </div>
        </div>

        <!-- Sidebar / Right Column (col-lg-4) -->
        <div class="col-lg-4">
            {{-- 1. Files Card (with Cover / First-Page Preview) --}}
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-file-earmark-arrow-down-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">ไฟล์เอกสาร (Files)</h6>
                </div>
                <div class="card-body p-3">
                    {{-- Cover / First-Page Preview (.webp) --}}
                    @if((int) $item->pro_file_show === 0 && $item->cover_url)
                        @php
                            $coverReportPolicy = \App\Models\SystemSetting::get('download_policy_full_report', 'members_only');
                            $coverNeedsLogin = ($coverReportPolicy === 'members_only' || $coverReportPolicy === 'admin_only') && !auth()->check();
                            $coverHref = $coverNeedsLogin ? route('login') : route('frontend.rdbproject.view_report', $item->pro_id);
                        @endphp
                        <div class="text-center mb-3">
                            <a href="{{ $coverHref }}" @if(!$coverNeedsLogin) target="_blank" @endif title="{{ $coverNeedsLogin ? 'เข้าสู่ระบบเพื่อดาวน์โหลดรายงานฉบับสมบูรณ์' : 'คลิกเพื่อเปิดรายงานวิจัยฉบับสมบูรณ์' }}">
                                <img src="{{ $item->cover_url }}" alt="ปกหน้าแรกรายงานวิจัย" class="img-fluid rounded border shadow-sm" style="max-height: 260px; object-fit: contain;">
                            </a>
                        </div>
                    @endif

                    {{-- Abstract File --}}
                    @if(!empty($item->pro_abstract_file) || !empty($item->pro_abstract_file_path))
                        <a href="{{ route('frontend.rdbproject.view_abstract', $item->pro_id) }}" 
                           target="_blank" 
                           class="file-cta-card card border mb-3 text-decoration-none shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2.5">
                                    <div class="d-flex align-items-center min-w-0 me-2">
                                        <div class="file-icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                                            <i class="bi bi-file-earmark-text fs-2"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <h6 class="fw-bold text-body mb-0 text-truncate" style="font-size: 0.98rem;">ไฟล์บทคัดย่อ</h6>
                                            <small class="text-body-secondary d-block" style="font-size: 0.78rem;">Abstract Document (PDF)</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-body-secondary text-secondary-emphasis px-2 py-1 flex-shrink-0" title="จำนวนเปิดดู: {{ number_format($item->pro_count_abs ?? 0) }} ครั้ง" style="font-size: 0.76rem;">
                                        <i class="bi bi-eye me-1"></i>{{ number_format($item->pro_count_abs ?? 0) }}
                                    </span>
                                </div>
                                <div class="btn btn-sm btn-outline-primary w-100 rounded-2 fw-medium d-flex align-items-center justify-content-center gap-1.5 py-1.5">
                                    <i class="bi bi-box-arrow-up-right me-1.5"></i> เปิดอ่านบทคัดย่อ
                                </div>
                            </div>
                        </a>
                    @else
                        <div class="p-2.5 border border-dashed rounded bg-body-tertiary mb-3 text-body-secondary small d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-earmark text-muted fs-5 me-2"></i>
                            <span>ไม่มีไฟล์บทคัดย่อ</span>
                        </div>
                    @endif

                    {{-- Full Report File (0 = เปิด, 1 = ปิด, 2 = ร่าง) --}}
                    @if(!empty($item->pro_file) || !empty($item->pro_file_path))
                        @if((int) $item->pro_file_show === 0)
                            @php
                                $reportPolicy = \App\Models\SystemSetting::get('download_policy_full_report', 'members_only');
                                $needsLogin = ($reportPolicy === 'members_only' || $reportPolicy === 'admin_only') && !auth()->check();
                            @endphp

                            @if($needsLogin)
                                <a href="{{ route('login') }}" 
                                   class="file-cta-card card border border-warning-subtle text-decoration-none shadow-sm"
                                   style="background: rgba(245, 158, 11, 0.05);"
                                   title="ต้องเข้าสู่ระบบก่อนเพื่อดาวน์โหลดเอกสารฉบับสมบูรณ์">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                                            <div class="d-flex align-items-center min-w-0 me-2">
                                                <div class="file-icon-box bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-file-earmark-lock-fill fs-2"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <h6 class="fw-bold text-body mb-0 text-truncate" style="font-size: 0.98rem;">รายงานฉบับสมบูรณ์</h6>
                                                    <small class="text-body-secondary d-block" style="font-size: 0.78rem;">Full Report (สำหรับสมาชิก)</small>
                                                </div>
                                            </div>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 flex-shrink-0" style="font-size: 0.74rem;">
                                                <i class="bi bi-shield-lock me-1"></i>ต้องเข้าสู่ระบบ
                                            </span>
                                        </div>
                                        <div class="btn btn-sm btn-outline-warning w-100 rounded-2 fw-medium d-flex align-items-center justify-content-center gap-1.5 py-1.5 shadow-sm text-dark">
                                            <i class="bi bi-box-arrow-in-right me-1.5"></i> เข้าสู่ระบบเพื่อดาวน์โหลดรายงาน
                                        </div>
                                    </div>
                                </a>
                            @else
                                <a href="{{ route('frontend.rdbproject.view_report', $item->pro_id) }}" 
                                   target="_blank" 
                                   class="file-cta-card card border border-success-subtle text-decoration-none shadow-sm"
                                   style="background: rgba(16, 185, 129, 0.04);">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                                            <div class="d-flex align-items-center min-w-0 me-2">
                                                <div class="file-icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-file-earmark-pdf-fill fs-2"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <h6 class="fw-bold text-success mb-0 text-truncate" style="font-size: 0.98rem;">รายงานฉบับสมบูรณ์</h6>
                                                    <small class="text-body-secondary d-block" style="font-size: 0.78rem;">Full Research Report (PDF)</small>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" title="ดาวน์โหลด: {{ number_format($item->pro_count_full ?? 0) }} ครั้ง" style="font-size: 0.76rem;">
                                                    <i class="bi bi-download me-1"></i>{{ number_format($item->pro_count_full ?? 0) }}
                                                </span>
                                                @if(!empty($item->pro_page_num))
                                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-1.5 py-0.5" style="font-size: 0.7rem; font-variant-numeric: tabular-nums;">
                                                        <i class="bi bi-file-earmark-text me-1"></i>{{ $item->pro_page_num }} หน้า
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="btn btn-sm btn-success w-100 rounded-2 fw-medium d-flex align-items-center justify-content-center gap-1.5 py-1.5 text-white shadow-sm">
                                            <i class="bi bi-cloud-arrow-down-fill me-1.5"></i> ดาวน์โหลดรายงานฉบับสมบูรณ์
                                        </div>
                                    </div>
                                </a>
                            @endif
                        @elseif((int) $item->pro_file_show === 1)
                            <div class="p-2.5 border border-dashed rounded bg-body-tertiary mb-3 text-secondary small d-flex align-items-center justify-content-center">
                                <i class="bi bi-shield-lock-fill text-danger fs-5 me-2"></i>
                                <span>เล่มรายงานวิจัยถูกปกปิด (ไม่สามารถเปิดเผยข้อมูลได้)</span>
                            </div>
                        @elseif((int) $item->pro_file_show === 2)
                            <div class="p-2.5 border border-dashed rounded bg-body-tertiary mb-3 text-secondary small d-flex align-items-center justify-content-center">
                                <i class="bi bi-pencil-square text-warning fs-5 me-2"></i>
                                <span>เล่มรายงานวิจัยอยู่ในสถานะฉบับร่าง (ยังไม่เผยแพร่)</span>
                            </div>
                        @endif
                    @else
                        <div class="p-2.5 border border-dashed rounded bg-body-tertiary mb-3 text-body-secondary small d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-earmark text-muted fs-5 me-2"></i>
                            <span>ไม่มีไฟล์เล่มรายงาน</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. Researchers Card --}}
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-people-fill text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">คณะผู้วิจัย (Researchers)</h6>
                    <span class="badge bg-primary-subtle text-primary ms-auto">{{ $item->rdbProjectWorks->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($item->rdbProjectWorks && $item->rdbProjectWorks->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($item->rdbProjectWorks as $work)
                                <div class="list-group-item bg-transparent py-2.5 px-3">
                                    <div class="d-flex align-items-start">
                                        <div class="me-2 text-primary fs-5 mt-0.5">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            @if($work->researcher)
                                                <div class="d-flex flex-wrap align-items-center gap-1.5 mb-0.5">
                                                    <a href="{{ route('frontend.rdbresearcher.show', $work->researcher->researcher_id) }}" 
                                                       target="_blank" rel="noopener noreferrer"
                                                       class="text-decoration-none fw-bold text-body hover-primary">
                                                        {{ $work->researcher->prefix->prefix_nameTH ?? '' }}{{ $work->researcher->researcher_fname }} {{ $work->researcher->researcher_lname }}
                                                    </a>
                                                    @if($work->position)
                                                        <span class="badge {{ $work->position_id == 2 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-secondary-subtle text-secondary-emphasis' }}" 
                                                              style="font-size: 0.7rem; font-weight: 500; padding: 0.16rem 0.45rem;">
                                                            {{ $work->position->position_nameTH }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($work->researcher->department)
                                                    <small class="text-body-secondary d-block" style="font-size: 0.78rem;">
                                                        <i class="bi bi-building me-1"></i>{{ $work->researcher->department->department_nameTH }}
                                                    </small>
                                                @elseif($work->researcher->researcher_note)
                                                    <small class="text-body-secondary d-block" style="font-size: 0.78rem;">
                                                        {{ $work->researcher->researcher_note }}
                                                    </small>
                                                @endif
                                            @else
                                                <span class="text-body-secondary">-</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3 text-body-secondary text-center">ไม่มีข้อมูลนักวิจัย</div>
                    @endif
                </div>
            </div>

            {{-- 3. Citations Card (Academic Standards: APA 7, IEEE, MLA 9) --}}
            @php
                $authorsList = [];
                if ($item->rdbProjectWorks) {
                    foreach ($item->rdbProjectWorks as $w) {
                        if ($w->researcher) {
                            $name = trim($w->researcher->researcher_fname . ' ' . $w->researcher->researcher_lname);
                            if (!empty($name)) {
                                $authorsList[] = $name;
                            }
                        }
                    }
                }
                if (empty($authorsList)) {
                    $authorsList[] = 'มหาวิทยาลัยราชภัฏเพชรบูรณ์';
                }

                $yearBE = $item->year->year_name ?? (date('Y') + 543);
                $deptName = $item->department->department_nameTH ?? 'มหาวิทยาลัยราชภัฏเพชรบูรณ์';
                $cleanTitle = trim(preg_replace('/\s+/', ' ', strip_tags($item->pro_nameTH)));

                // 1. APA 7 (American Psychological Association 7th ed.)
                $numAuthors = count($authorsList);
                if ($numAuthors === 1) {
                    $apaAuthors = $authorsList[0];
                } elseif ($numAuthors === 2) {
                    $apaAuthors = $authorsList[0] . ' และ ' . $authorsList[1];
                } else {
                    $apaAuthors = implode(', ', array_slice($authorsList, 0, -1)) . ', และ ' . end($authorsList);
                }
                $citationApa = $apaAuthors . '. (' . $yearBE . '). <i>' . $cleanTitle . '</i> (รายงานการวิจัย). ' . $deptName . ', มหาวิทยาลัยราชภัฏเพชรบูรณ์.';

                // 2. IEEE
                if ($numAuthors === 1) {
                    $ieeeAuthors = $authorsList[0];
                } elseif ($numAuthors === 2) {
                    $ieeeAuthors = $authorsList[0] . ' และ ' . $authorsList[1];
                } else {
                    $ieeeAuthors = $authorsList[0] . ' และคณะ';
                }
                $citationIeee = '[1] ' . $ieeeAuthors . ', "' . $cleanTitle . '," รายงานการวิจัย, ' . $deptName . ', มหาวิทยาลัยราชภัฏเพชรบูรณ์, ' . $yearBE . '.';

                // 3. MLA 9
                if ($numAuthors === 1) {
                    $mlaAuthors = $authorsList[0];
                } elseif ($numAuthors === 2) {
                    $mlaAuthors = $authorsList[0] . ', และ ' . $authorsList[1];
                } else {
                    $mlaAuthors = $authorsList[0] . ', และคณะ';
                }
                $citationMla = $mlaAuthors . '. <i>' . $cleanTitle . '</i>. รายงานการวิจัย, ' . $deptName . ', มหาวิทยาลัยราชภัฏเพชรบูรณ์, ' . $yearBE . '.';
            @endphp
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2.5 px-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-quote text-primary me-2 fs-5"></i>
                        <h6 class="mb-0 fw-semibold text-body">การอ้างอิง (Citations)</h6>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">3 รูปแบบ</span>
                </div>
                <div class="card-body p-3">
                    <!-- Segmented Small Tabs -->
                    <ul class="nav nav-pills nav-fill gap-1 p-1 bg-body-tertiary rounded-2 mb-2.5 border" id="citationTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-2 rounded-2 fw-semibold" id="tab-apa7" data-bs-toggle="pill" data-bs-target="#pane-apa7" type="button" role="tab" aria-controls="pane-apa7" aria-selected="true" style="font-size: 0.78rem;">
                                APA 7
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-2 rounded-2 fw-semibold" id="tab-ieee" data-bs-toggle="pill" data-bs-target="#pane-ieee" type="button" role="tab" aria-controls="pane-ieee" aria-selected="false" style="font-size: 0.78rem;">
                                IEEE
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-2 rounded-2 fw-semibold" id="tab-mla9" data-bs-toggle="pill" data-bs-target="#pane-mla9" type="button" role="tab" aria-controls="pane-mla9" aria-selected="false" style="font-size: 0.78rem;">
                                MLA 9
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Panes -->
                    <div class="tab-content" id="citationTabContent">
                        <!-- APA 7 Pane -->
                        <div class="tab-pane fade show active" id="pane-apa7" role="tabpanel" aria-labelledby="tab-apa7" tabindex="0">
                            <div class="p-2.5 rounded bg-body-tertiary border text-body text-break mb-2" id="text-apa7" style="font-size: 0.82rem; line-height: 1.65; user-select: all;">
                                {!! strip_tags((string) $citationApa, '<i><em><b><strong>') !!}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 py-1.5 d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.78rem;" onclick="copyCitation('text-apa7', this)">
                                <i class="bi bi-clipboard me-1.5"></i> คัดลอก APA 7
                            </button>
                        </div>

                        <!-- IEEE Pane -->
                        <div class="tab-pane fade" id="pane-ieee" role="tabpanel" aria-labelledby="tab-ieee" tabindex="0">
                            <div class="p-2.5 rounded bg-body-tertiary border text-body text-break mb-2" id="text-ieee" style="font-size: 0.82rem; line-height: 1.65; user-select: all;">
                                {!! strip_tags((string) $citationIeee, '<i><em><b><strong>') !!}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 py-1.5 d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.78rem;" onclick="copyCitation('text-ieee', this)">
                                <i class="bi bi-clipboard me-1.5"></i> คัดลอก IEEE
                            </button>
                        </div>

                        <!-- MLA 9 Pane -->
                        <div class="tab-pane fade" id="pane-mla9" role="tabpanel" aria-labelledby="tab-mla9" tabindex="0">
                            <div class="p-2.5 rounded bg-body-tertiary border text-body text-break mb-2" id="text-mla9" style="font-size: 0.82rem; line-height: 1.65; user-select: all;">
                                {!! strip_tags((string) $citationMla, '<i><em><b><strong>') !!}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 py-1.5 d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.78rem;" onclick="copyCitation('text-mla9', this)">
                                <i class="bi bi-clipboard me-1.5"></i> คัดลอก MLA 9
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Additional Documents Card --}}
            @php
                $visibleFiles = $item->files->where('rf_files_show', 1);
            @endphp
            @if($visibleFiles->count() > 0)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center py-2.5 px-3">
                    <i class="bi bi-paperclip text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold text-body">ข้อมูลเอกสารเพิ่มเติม</h6>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis ms-auto">{{ $visibleFiles->count() }}</span>
                </div>
                <div class="card-body">
                    @foreach($visibleFiles as $file)
                        <div class="p-2.5 border rounded bg-body-tertiary mb-2">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center text-truncate me-2">
                                    <i class="bi bi-file-earmark-arrow-down text-primary fs-4 me-2 flex-shrink-0"></i>
                                    <div class="text-truncate">
                                        <a href="{{ route('frontend.rdbproject.file.download', [$item->pro_id, $file->id]) }}" target="_blank" class="text-decoration-none fw-semibold text-primary hover-underline text-truncate d-block" style="font-size: 0.88rem;">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> {{ $file->rf_filesname }}
                                        </a>
                                        @if($file->rf_note)
                                            <small class="text-body-secondary d-block text-truncate mt-0.5" style="font-size: 0.78rem;">
                                                <i class="bi bi-chat-left-text me-1"></i> {{ $file->rf_note }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1" title="ดาวน์โหลด: {{ number_format($file->rf_download ?? 0) }} ครั้ง" style="font-size: 0.78rem;">
                                        <i class="bi bi-download me-1"></i>{{ number_format($file->rf_download ?? 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 4. History Card (Collapsible by default as requested) --}}
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary p-0">
                    <button class="btn btn-link text-decoration-none w-100 d-flex justify-content-between align-items-center py-2.5 px-3 text-body collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#projectHistoryCollapse"
                            aria-expanded="false"
                            aria-controls="projectHistoryCollapse">
                        <span class="fw-semibold small"><i class="bi bi-clock-history text-secondary me-2"></i> ประวัติการแก้ไข (History)</span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                </div>
                <div class="collapse" id="projectHistoryCollapse">
                    <div class="card-body small text-secondary py-3 px-3">
                        <p class="mb-2"><strong>สร้างเมื่อ:</strong> {{ \App\Helpers\ThaiDateHelper::formatDateTime($item->created_at) }} โดย {{ getFrontendUserName($item->createdBy) }}</p>
                        <p class="mb-0"><strong>แก้ไขล่าสุด:</strong> {{ \App\Helpers\ThaiDateHelper::formatDateTime($item->updated_at) }} โดย {{ getFrontendUserName($item->updatedBy) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
    .hover-underline:hover {
        text-decoration: underline !important;
    }
    .hover-shadow {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    /* File CTA Cards */
    .file-cta-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        cursor: pointer;
    }
    .file-cta-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.12) !important;
        border-color: var(--bs-primary) !important;
    }
    .file-cta-card:hover .btn-outline-primary {
        background-color: var(--bs-primary);
        color: #fff;
    }
    .file-cta-card:hover .btn-success {
        filter: brightness(1.08);
    }
    /* Rotate collapse icon when expanded */
    button[aria-expanded="true"] .bi-chevron-down {
        transform: rotate(180deg);
        transition: transform 0.2s ease;
    }
    button[aria-expanded="false"] .bi-chevron-down {
        transition: transform 0.2s ease;
    }

    /* Abstract Typography & Box Styling */
    .abstract-box {
        border-radius: 12px;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .abstract-box:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }
    .abstract-content {
        font-size: 0.96rem;
        line-height: 1.88;
        letter-spacing: 0.012em;
        text-align: justify;
        text-justify: inter-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .abstract-indent {
        text-indent: 2.25rem;
    }
    .abstract-content p {
        margin-bottom: 0.85rem;
        text-indent: 2.25rem;
    }
    [data-bs-theme="dark"] .abstract-content {
        color: #d1d5db; /* Softer, comfortable reading tone in dark mode */
    }
    [data-bs-theme="light"] .abstract-content {
        color: #334155; /* Slate-700 for optimal contrast without eye strain */
    }
    .copy-abstract-btn {
        transition: all 0.15s ease;
    }
    .copy-abstract-btn:hover {
        background-color: var(--bs-secondary-bg);
        color: var(--bs-body-color);
    }
</style>

<script>
    function copyTextToClipboard(text, onSuccess, onError) {
        if (!text) return;
        text = text.trim();

        // 1. Modern Clipboard API (available in HTTPS or localhost)
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                if (typeof onSuccess === 'function') onSuccess();
            }).catch(() => {
                execFallback(text, onSuccess, onError);
            });
        } else {
            // 2. Fallback for non-secure contexts (e.g. http:// domain in Laragon / local dev)
            execFallback(text, onSuccess, onError);
        }

        function execFallback(str, successCb, errorCb) {
            let textarea = document.createElement('textarea');
            textarea.value = str;
            textarea.style.position = 'fixed';
            textarea.style.top = '0';
            textarea.style.left = '0';
            textarea.style.width = '2em';
            textarea.style.height = '2em';
            textarea.style.padding = '0';
            textarea.style.border = 'none';
            textarea.style.outline = 'none';
            textarea.style.boxShadow = 'none';
            textarea.style.background = 'transparent';
            textarea.style.opacity = '0';
            textarea.setAttribute('readonly', '');
            document.body.appendChild(textarea);

            textarea.focus();
            textarea.select();

            let successful = false;
            try {
                successful = document.execCommand('copy');
            } catch (err) {
                console.error('execCommand copy failed:', err);
            }
            document.body.removeChild(textarea);

            if (successful) {
                if (typeof successCb === 'function') successCb();
            } else {
                if (typeof errorCb === 'function') errorCb();
            }
        }
    }

    function copyAbstractText(target, triggerBtn) {
        let btn = (triggerBtn && triggerBtn.nodeType) ? triggerBtn : (target && target.nodeType ? target : null);
        let elId = (typeof target === 'string') ? target : (typeof triggerBtn === 'string' ? triggerBtn : null);
        if (!elId) return;
        const el = document.getElementById(elId);
        if (!el) return;
        const text = el.innerText || el.textContent || '';

        copyTextToClipboard(text, () => {
            if (!btn) return;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 text-success me-1"></i> คัดลอกแล้ว';
            btn.classList.add('border-success');
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('border-success');
            }, 2000);
        }, () => {
            window.prompt("กด Ctrl+C เพื่อคัดลอกข้อความ:", text);
        });
    }

    function copyCitation(target, triggerBtn) {
        let btn = (triggerBtn && triggerBtn.nodeType) ? triggerBtn : (target && target.nodeType ? target : null);
        let elId = (typeof target === 'string') ? target : (typeof triggerBtn === 'string' ? triggerBtn : null);
        if (!elId) return;
        const el = document.getElementById(elId);
        if (!el) return;
        const text = el.innerText || el.textContent || '';

        copyTextToClipboard(text, () => {
            if (!btn) return;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 text-success me-1"></i> คัดลอกการอ้างอิงแล้ว';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-primary');
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }, () => {
            window.prompt("กด Ctrl+C เพื่อคัดลอกข้อความการอ้างอิง:", text);
        });
    }
</script>
@endsection