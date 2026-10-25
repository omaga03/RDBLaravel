@extends('layouts.app')

@section('content')
@include('layouts.partials.scholar-meta', ['type' => 'news', 'item' => $item])
<div class="container-fluid py-4">

    <!-- Breadcrumb & Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.site.index') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('frontend.researchnews.index') }}" class="text-decoration-none">ข่าวกิจกรรมงานวิจัย</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายละเอียดข่าว</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-semibold text-body page-title">
                <i class="bi bi-newspaper me-2 text-primary"></i>รายละเอียดข่าวกิจกรรม
            </h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontend.researchnews.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-printer me-1"></i> พิมพ์
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main News Content (Left Column) -->
        <div class="col-lg-8">
            <div class="card shadow-sm border mb-4">
                <div class="card-header text-white p-3 p-md-4" style="background: var(--rdb-gradient-primary);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        @if($item->news_type)
                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle fw-semibold">
                                <i class="bi bi-tag-fill me-1"></i>{{ $item->news_type }}
                            </span>
                        @endif
                        <span class="small text-white-50">
                            <i class="bi bi-eye me-1"></i>เข้าชม {{ number_format($item->news_count ?? 0) }} ครั้ง
                        </span>
                    </div>
                    <h4 class="card-title mb-0 fw-semibold text-white text-break" style="font-size: 1.15rem; line-height: 1.55;">
                        {{ html_entity_decode(strip_tags($item->news_name)) }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Metadata Info Bar -->
                    <div class="p-3 mb-4 rounded bg-body-tertiary border d-flex flex-wrap gap-4 align-items-center">
                        @if($item->news_date)
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-check-fill text-primary fs-4 me-2"></i>
                            <div>
                                <small class="text-body-secondary d-block">วันที่ประกาศ</small>
                                <span class="fw-bold text-body">
                                    {{ \App\Helpers\ThaiDateHelper::format($item->news_date, false, true) }}
                                </span>
                            </div>
                        </div>
                        @endif

                        @if($item->news_event_start)
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-range text-success fs-4 me-2"></i>
                            <div>
                                <small class="text-body-secondary d-block">ระยะเวลากิจกรรม</small>
                                <span class="fw-bold text-body">
                                    {{ \App\Helpers\ThaiDateHelper::format($item->news_event_start) }}
                                    @if($item->news_event_end)
                                        - {{ \App\Helpers\ThaiDateHelper::format($item->news_event_end) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Main News Image -->
                    @php
                        $newsImg = $item->image_url;
                    @endphp
                    @if($newsImg)
                        <div class="mb-4 rounded overflow-hidden shadow-sm border text-center bg-body-tertiary">
                            <img src="{{ $newsImg }}" class="img-fluid w-100 object-fit-cover" style="max-height: 480px;" alt="{{ $item->news_name }}">
                        </div>
                    @endif

                    <!-- Detailed Content -->
                    <div class="content-body py-2">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="bi bi-file-text me-2"></i>รายละเอียดเนื้อหา
                        </h5>
                        <div class="text-body text-break lh-lg fs-6">
                            {!! $item->formatted_detail !!}
                        </div>
                    </div>

                    <!-- Reference and Links -->
                    @if($item->news_reference || $item->news_link)
                    <div class="mt-4 pt-3 border-top">
                        @if($item->news_reference)
                            <div class="mb-3">
                                <span class="fw-bold text-body-secondary d-block mb-1">
                                    <i class="bi bi-bookmark-fill me-1 text-primary"></i>แหล่งข้อมูลอ้างอิง:
                                </span>
                                <span class="text-body text-break">{{ $item->news_reference }}</span>
                            </div>
                        @endif
                        @if($item->news_link)
                            <div>
                                <a href="{{ $item->news_link }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> ลิงก์ที่เกี่ยวข้อง / รายละเอียดเพิ่มเติม
                                </a>
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar (Right Column) -->
        <div class="col-lg-4">
            <!-- Recent News Card -->
            @if(isset($recentNews) && $recentNews->count() > 0)
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-body-tertiary d-flex align-items-center">
                    <i class="bi bi-newspaper text-primary me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold text-body">ข่าวกิจกรรมอื่นๆ</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($recentNews as $other)
                            @php
                                $otherImg = $other->image_url;
                            @endphp
                            <a href="{{ route('frontend.researchnews.show', $other->id) }}" class="list-group-item list-group-item-action bg-transparent py-3 px-3">
                                <div class="d-flex gap-3">
                                    @if($otherImg)
                                        <div class="ratio ratio-1x1 rounded overflow-hidden flex-shrink-0 bg-body-secondary" style="width: 60px; height: 60px;">
                                            <img src="{{ $otherImg }}" class="object-fit-cover w-100 h-100" alt="{{ $other->news_name }}">
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-body fw-bold hover-primary text-break" style="font-size: 0.9rem; line-height: 1.4;">
                                            {{ Str::limit(html_entity_decode(strip_tags($other->news_name)), 70) }}
                                        </h6>
                                        <small class="text-body-secondary d-block">
                                            <i class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\ThaiDateHelper::format($other->news_date) }}
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
                <a href="{{ route('frontend.researchnews.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> กลับไปหน้ารายการข่าว
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