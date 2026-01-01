@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="row">
        <!-- Header & Action Buttons -->
        <x-page-header 
            title="รายละเอียดงานประชุมวิชาการ"
            icon="bi-calendar-event"
            :backRoute="route('backend.research_conference.index')"
            :editRoute="route('backend.research_conference.edit', $item->id)"
            :deleteRoute="route('backend.research_conference.destroy', $item->id)"
            :showPrint="true"
        />

        <!-- รูปภาพประกอบ (ส่วนแรก) -->
        @if($item->con_img)
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white d-print-none">
                    <h6 class="mb-0"><i class="bi bi-image"></i> รูปภาพประกอบ</h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ asset('storage/uploads/conference/' . $item->con_img) }}" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                </div>
            </div>
        </div>
        @endif

        <!-- ข้อมูลงานประชุม -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-print-none">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> ข้อมูลงานประชุมวิชาการ</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-primary fw-bold mb-3">{!! $item->con_name !!}</h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            @if($item->con_even_date)
                            <div class="mb-2">
                                <span class="text-muted">
                                    <i class="bi bi-calendar-event me-1"></i> วันจัดงาน: <strong>{{ $item->con_even_date }}</strong>
                                </span>
                            </div>
                            @endif
                            @if($item->con_venue)
                            <div class="mb-2">
                                <span class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i> สถานที่: {{ strip_tags($item->con_venue) }}
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($item->con_sub_deadline)
                            <div class="mb-2">
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock me-1"></i> ปิดรับผลงาน: {{ $item->con_sub_deadline }}
                                </span>
                            </div>
                            @endif
                            <div class="mb-2">
                                <span class="text-muted">
                                    <i class="bi bi-eye me-1"></i> เข้าชม: {{ $item->con_count ?? 0 }} ครั้ง
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($item->con_website)
                    <div class="mb-3">
                        <span class="text-muted">
                            <i class="bi bi-link-45deg me-1"></i> เว็บไซต์: 
                            <a href="{{ $item->con_website }}" target="_blank">{{ $item->con_website }}</a>
                        </span>
                    </div>
                    @endif

                    <hr>

                    <h6 class="fw-bold border-bottom pb-2"><i class="bi bi-file-text me-2"></i>รายละเอียด</h6>
                    <div class="p-3 rounded border">
                        {!! $item->con_detail !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- ข้อมูลระบบ (ส่วนล่างสุด) -->
        <div class="col-md-12 mb-4">
            <x-system-info :created_at="$item->created_at" :updated_at="$item->updated_at" />
        </div>

        <!-- ปุ่มด้านล่าง -->
        <div class="col-md-12 d-print-none">
             <x-action-buttons 
                :backRoute="route('backend.research_conference.index')"
                :editRoute="route('backend.research_conference.edit', $item->id)"
                :deleteRoute="route('backend.research_conference.destroy', $item->id)"
                :showPrint="true"
            />
        </div>
    </div>
</div>
@endsection
