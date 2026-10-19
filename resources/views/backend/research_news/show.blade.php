@extends('layouts.app')

@section('content')
    <div class="row">
        <!-- Header & Action Buttons -->
        <x-page-header 
            title="รายละเอียดข่าว/กิจกรรม"
            icon="bi-newspaper"
            :backRoute="route('backend.research_news.index')"
            :editRoute="auth()->user()->can('update', $item) ? route('backend.research_news.edit', $item->id) : null"
            :deleteRoute="auth()->user()->can('delete', $item) ? route('backend.research_news.destroy', $item->id) : null"
            :canDelete="auth()->user()->can('delete', $item)"
            :showPrint="true"
            :breadcrumbs="[
                'แดชบอร์ด' => route('backend.site.index'),
                'จัดการข่าว/กิจกรรม' => route('backend.research_news.index'),
                'รายละเอียดข่าว' => ''
            ]"
        />

        <!-- รูปภาพประกอบ (ส่วนแรก) -->
        @if($item->image_url)
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border">
                <div class="card-header text-white d-print-none" style="background: var(--rdb-gradient-primary);">
                    <h6 class="mb-0"><i class="bi bi-image me-1"></i> รูปภาพประกอบ</h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $item->image_url }}" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                </div>
            </div>
        </div>
        @endif

        <!-- ข้อมูลข่าว/กิจกรรม -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border">
                <div class="card-header text-white p-3 p-md-4 d-print-none border-0" style="background: var(--rdb-gradient-primary);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        @if($item->news_type)
                            <span class="badge bg-light text-dark fw-semibold">
                                <i class="bi bi-tag-fill me-1"></i>{{ $item->news_type }}
                            </span>
                        @endif
                        <span class="small text-white-50">
                            <i class="bi bi-eye me-1"></i>เข้าชม {{ number_format($item->news_count ?? 0) }} ครั้ง
                        </span>
                    </div>
                    <h4 class="card-title mb-0 mt-1 fw-semibold text-white text-break" style="font-size: 1.15rem; line-height: 1.55;">
                        {{ strip_tags($item->news_name) }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i> ลงข่าวเมื่อ: {{ \App\Helpers\ThaiDateHelper::format($item->news_date, false, true) }}
                        </span>
                    </div>

                    @if($item->news_event_start || $item->news_event_end)
                    <div class="mb-3">
                        <span class="text-muted">
                            <i class="bi bi-clock me-1"></i> ระยะเวลากิจกรรม: 
                            {{ \App\Helpers\ThaiDateHelper::format($item->news_event_start, false, true) }}
                            ถึง
                            {{ \App\Helpers\ThaiDateHelper::format($item->news_event_end, false, true) }}
                        </span>
                    </div>
                    @endif

                    @if($item->news_link)
                    <div class="mb-3">
                        <span class="text-muted">
                            <i class="bi bi-link-45deg me-1"></i> ลิ้งก์ที่เกี่ยวข้อง: 
                            <a href="{{ $item->news_link }}" target="_blank">{{ $item->news_link }}</a>
                        </span>
                    </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="fw-bold mb-0 text-body"><i class="bi bi-file-text-fill text-primary me-2"></i>รายละเอียด</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText('news-backend-detail-text', this)">
                            <i class="bi bi-clipboard me-1"></i> คัดลอกข้อความ
                        </button>
                    </div>
                    <div class="abstract-box p-3 p-md-4 border border-start border-4 border-primary rounded-3 bg-body-tertiary shadow-xs">
                        <div id="news-backend-detail-text" class="abstract-content abstract-indent">
                            {!! strip_tags($item->news_detail, '<p><br><b><strong><i><em><u><ul><ol><li><a><span>') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ข้อมูลระบบ (ส่วนล่างสุด) -->
        <div class="col-md-12 mb-4">
             <x-system-info :created_at="$item->created_at" :updated_at="$item->updated_at" />
        </div>

        <!-- ปุ่มด้านล่าง -->
        <div class="col-md-12">
            <x-action-buttons 
                :backRoute="route('backend.research_news.index')"
                :editRoute="auth()->user()->can('update', $item) ? route('backend.research_news.edit', $item->id) : null"
                :deleteRoute="auth()->user()->can('delete', $item) ? route('backend.research_news.destroy', $item->id) : null"
                :canDelete="auth()->user()->can('delete', $item)"
                :showPrint="true"
            />
        </div>
    </div>
@endsection
