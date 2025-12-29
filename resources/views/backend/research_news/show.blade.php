@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="mb-0"><i class="bi bi-newspaper"></i> รายละเอียดข่าว/กิจกรรม</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('backend.research_news.index') }}" class="btn btn-secondary d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                    </a>
                    <a href="{{ route('backend.research_news.edit', $item->id) }}" class="btn btn-warning d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-pencil me-2"></i> แก้ไขข้อมูล
                    </a>
                    <button onclick="window.print()" class="btn btn-primary d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-printer me-2"></i> พิมพ์
                    </button>
                    <button type="submit" form="delete-form-top" class="btn btn-danger d-print-none d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                        <i class="bi bi-trash me-2"></i> ลบ
                    </button>
                </div>
                <form id="delete-form-top" action="{{ route('backend.research_news.destroy', $item->id) }}" method="POST" class="d-none delete-form">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>

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
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white d-print-none">
                    <h6 class="mb-0"><i class="bi bi-clock-history"></i> ข้อมูลระบบ</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-0"><small class="text-muted"><i class="bi bi-plus-circle me-1"></i> สร้างเมื่อ: {{ $item->created_at ? \App\Helpers\ThaiDateHelper::format($item->created_at, true, true) : '-' }}</small></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0"><small class="text-muted"><i class="bi bi-pencil me-1"></i> แก้ไขล่าสุด: {{ $item->updated_at ? \App\Helpers\ThaiDateHelper::format($item->updated_at, true, true) : '-' }}</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ปุ่มด้านล่าง -->
        <div class="col-md-12 d-print-none">
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('backend.research_news.index') }}" class="btn btn-secondary d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                    <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                </a>
                <a href="{{ route('backend.research_news.edit', $item->id) }}" class="btn btn-warning d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                    <i class="bi bi-pencil me-2"></i> แก้ไขข้อมูล
                </a>
                <button onclick="window.print()" class="btn btn-primary d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                    <i class="bi bi-printer me-2"></i> พิมพ์
                </button>
                <button type="submit" form="delete-form-bottom" class="btn btn-danger d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
                    <i class="bi bi-trash me-2"></i> ลบ
                </button>
            </div>
            <form id="delete-form-bottom" action="{{ route('backend.research_news.destroy', $item->id) }}" method="POST" class="d-none delete-form">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endsection
