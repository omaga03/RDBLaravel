@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Breadcrumb & Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('frontend.researchcoferenceinthai.index') }}" class="text-decoration-none">การประชุมวิชาการ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายละเอียดการประชุม</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-megaphone-fill me-2 text-primary"></i>รายละเอียดการประชุมวิชาการ
            </h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontend.researchcoferenceinthai.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-printer me-1"></i> พิมพ์
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Conference Content (Left Column) -->
        <div class="col-lg-8">
            <div class="card shadow-sm border mb-4">
                <div class="card-header text-white p-3 p-md-4" style="background: var(--rdb-gradient-primary);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle fw-semibold">
                            <i class="bi bi-megaphone me-1"></i>การประชุมวิชาการ
                        </span>
                        <span class="small text-white-50">
                            <i class="bi bi-eye me-1"></i>เข้าชม {{ number_format($item->con_count ?? 0) }} ครั้ง
                        </span>
                    </div>
                    <h4 class="card-title mb-0 fw-semibold text-white text-break" style="font-size: 1.15rem; line-height: 1.55;">
                        {{ html_entity_decode(strip_tags($item->con_name)) }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Key Information Bar -->
                    <div class="p-3 mb-4 rounded bg-body-tertiary border">
                        <div class="row g-3">
                            @if($item->con_even_date)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-event-fill text-primary fs-3 me-3"></i>
                                    <div>
                                        <small class="text-body-secondary d-block">วันจัดงานประชุม</small>
                                        <span class="fw-bold text-body">
                                            {{ \App\Helpers\ThaiDateHelper::format($item->con_even_date, false, true) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($item->con_venue)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-geo-alt-fill text-danger fs-3 me-3"></i>
                                    <div>
                                        <small class="text-body-secondary d-block">สถานที่จัดงาน</small>
                                        <span class="fw-bold text-body text-break">{{ $item->con_venue }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Conference Banner/Image -->
                    @php
                        $conImg = $item->image_url;
                    @endphp
                    @if($conImg)
                        <div class="mb-4 rounded overflow-hidden shadow-sm border text-center bg-body-tertiary">
                            <img src="{{ $conImg }}" class="img-fluid w-100 object-fit-cover" style="max-height: 480px;" alt="{{ $item->con_name }}">
                        </div>
                    @endif

                    <!-- Detailed Content -->
                    <div class="content-body py-2">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="bi bi-file-text me-2"></i>รายละเอียดการประชุม
                        </h5>
                        <div class="text-body text-break lh-lg fs-6">
                            {!! $item->formatted_detail !!}
                        </div>
                    </div>

                    <!-- Website Link -->
                    @if($item->con_website)
                        <div class="mt-4 pt-3 border-top">
                            <a href="{{ $item->con_website }}" target="_blank" class="btn btn-outline-primary">
                                <i class="bi bi-globe me-1"></i> เข้าสู่เว็บไซต์การประชุม / ลงทะเบียน
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar (Right Column) -->
        <div class="col-lg-4">
            <!-- Deadline Card -->
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-clock-history text-warning me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold text-body">กำหนดการส่งบทความ</h5>
                </div>
                <div class="card-body text-center p-4">
                    @if($item->con_sub_deadline)
                        <div class="mb-2">
                            <i class="bi bi-stopwatch-fill text-warning fs-1"></i>
                        </div>
                        <span class="d-block text-body-secondary fw-bold text-uppercase mb-1 small">ส่งบทความวิชาการภายใน</span>
                        <h5 class="fw-bold text-danger mb-0">
                            {{ \App\Helpers\ThaiDateHelper::format($item->con_sub_deadline, false, true) }}
                        </h5>
                    @else
                        <div class="text-body-secondary py-2">
                            <i class="bi bi-check-circle fs-2 mb-2 d-block text-success"></i>
                            <span>ไม่ระบุวันหมดเขตรับบทความ</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Other Conferences Card -->
            @if(isset($recentConferences) && $recentConferences->count() > 0)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-megaphone text-primary me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold text-body">การประชุมวิชาการอื่นๆ</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($recentConferences as $other)
                            @php
                                $otherImg = $other->image_url;
                            @endphp
                            <a href="{{ route('frontend.researchcoferenceinthai.show', $other->id) }}" class="list-group-item list-group-item-action bg-transparent py-3 px-3">
                                <div class="d-flex gap-3">
                                    @if($otherImg)
                                        <div class="ratio ratio-1x1 rounded overflow-hidden flex-shrink-0 bg-body-secondary" style="width: 60px; height: 60px;">
                                            <img src="{{ $otherImg }}" class="object-fit-cover w-100 h-100" alt="{{ $other->con_name }}">
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-body fw-bold hover-primary text-break" style="font-size: 0.9rem; line-height: 1.4;">
                                            {{ Str::limit(html_entity_decode(strip_tags($other->con_name)), 70) }}
                                        </h6>
                                        <small class="text-body-secondary d-block">
                                            <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($other->con_even_date) }}
                                        </small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Navigation Button -->
            <div class="d-grid gap-2">
                <a href="{{ route('frontend.researchcoferenceinthai.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> กลับไปหน้ารายการการประชุม
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
    .content-body img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin: 1rem 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
</style>
@endsection