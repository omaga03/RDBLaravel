@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="row">
        <!-- Header & Action Buttons -->
        <x-page-header 
            title="รายละเอียดข่าว/กิจกรรม"
            icon="bi-newspaper"
            :backRoute="route('backend.research_news.index')"
            :editRoute="route('backend.research_news.edit', $item->id)"
            :deleteRoute="route('backend.research_news.destroy', $item->id)"
            :showPrint="true"
        />

        <!-- รูปภาพประกอบ (ส่วนแรก) -->
        @if($item->news_img)
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white d-print-none">
                    <h6 class="mb-0"><i class="bi bi-image"></i> รูปภาพประกอบ</h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ asset('storage/uploads/news/' . $item->news_img) }}" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                </div>
            </div>
        </div>
        @endif

        <!-- ข้อมูลข่าว/กิจกรรม -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-print-none">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> ข้อมูลข่าว/กิจกรรม</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-primary fw-bold mb-3">{!! $item->news_name !!}</h4>
                    
                    <div class="mb-3">
                        @if($item->news_type)
                            <span class="badge bg-info text-dark me-2">{{ $item->news_type }}</span>
                        @endif
                        <span class="text-muted border-end pe-2 me-2">
                            <i class="bi bi-calendar3 me-1"></i> ลงข่าวเมื่อ: {{ \App\Helpers\ThaiDateHelper::format($item->news_date, false, true) }}
                        </span>
                        <span class="text-muted">
                            <i class="bi bi-eye me-1"></i> เข้าชม: {{ $item->news_count ?? 0 }} ครั้ง
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

                    <h6 class="fw-bold border-bottom pb-2"><i class="bi bi-file-text me-2"></i>รายละเอียด</h6>
                    <div class="p-3 rounded border">
                        {!! $item->news_detail !!}
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
                :editRoute="route('backend.research_news.edit', $item->id)"
                :deleteRoute="route('backend.research_news.destroy', $item->id)"
                :showPrint="true"
            />
        </div>
    </div>
</div>
@endsection
