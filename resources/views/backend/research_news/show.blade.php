@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-newspaper me-2"></i> รายละเอียดข่าว/กิจกรรม</h1>
        <a href="{{ route('backend.research_news.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">หัวข้อข่าว</h6>
            <div class="dropdown no-arrow">
                 <a href="{{ route('backend.research_news.edit', $item->id) }}" class="btn btn-warning btn-sm me-2">
                    <i class="bi bi-pencil-square"></i> แก้ไข
                </a>
                <form action="{{ route('backend.research_news.destroy', $item->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('ยืนยันการลบข้อมูล?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> ลบ
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                 @if($item->news_img)
                <div class="col-md-12 text-center mb-3">
                    <img src="{{ asset('storage/uploads/news/' . $item->news_img) }}" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                </div>
                @endif
                
                <div class="col-md-12">
                     <h2 class="fw-bold mb-3">{!! $item->news_name !!}</h2>
                     <div class="mb-3">
                         @if($item->news_type == 1)
                            <span class="badge bg-info text-dark">ข่าวประชาสัมพันธ์</span>
                        @elseif($item->news_type == 2)
                            <span class="badge bg-success">การประชุม/อบรม</span>
                        @else
                            <span class="badge bg-secondary">อื่นๆ</span>
                        @endif
                         <span class="text-muted ms-2"><i class="bi bi-calendar3"></i> ลงข่าวเมื่อ: {{ \App\Helpers\ThaiDateHelper::format($item->news_date, false, true) }}</span>
                     </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <h5 class="fw-bold border-bottom pb-2">รายละเอียด</h5>
                    <div class="p-3 bg-light rounded border">
                        {!! $item->news_detail !!}
                    </div>
                </div>
            </div>

            @if($item->news_event_start || $item->news_event_end)
            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">ระยะเวลากิจกรรม:</div>
                <div class="col-md-9">
                    {{ \App\Helpers\ThaiDateHelper::format($item->news_event_start, false, true) }}
                    ถึง
                    {{ \App\Helpers\ThaiDateHelper::format($item->news_event_end, false, true) }}
                </div>
            </div>
            @endif

             @if($item->news_link)
            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">ลิ้งก์ที่เกี่ยวข้อง:</div>
                <div class="col-md-9">
                    <a href="{{ $item->news_link }}" target="_blank">{{ $item->news_link }}</a>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
