@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white py-3">
            <h4 class="mb-0 fw-bold"><i class="bi bi-newspaper me-2"></i>{{ $item->news_name }}</h4>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <!-- Main Content -->
                <div class="col-lg-12">
                    <!-- Meta Info (Moved above image) -->
                    <div class="d-flex flex-wrap gap-4 mb-4 pb-3 border-bottom small text-muted">
                        <!-- Publish Date -->
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-check text-primary me-2"></i>
                            <div>
                                <span class="d-block lh-1 text-uppercase" style="font-size: 0.75rem;">วันที่ลงข่าว</span>
                                <span class="fw-bold text-body">
                                     @php
                                        try {
                                            echo \Carbon\Carbon::parse($item->news_date)->locale('th')->addYears(543)->isoFormat('LL');
                                        } catch (\Exception $e) {
                                            echo $item->news_date;
                                        }
                                    @endphp
                                </span>
                            </div>
                        </div>

                        <!-- Event Date -->
                        @if($item->news_event_start)
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-event text-success me-2"></i>
                            <div>
                                <span class="d-block lh-1 text-uppercase" style="font-size: 0.75rem;">ระยะเวลากิจกรรม</span>
                                <span class="fw-bold text-body">
                                    @php
                                        try {
                                            echo \Carbon\Carbon::parse($item->news_event_start)->locale('th')->addYears(543)->isoFormat('D MMM YY');
                                        } catch (\Exception $e) {
                                            echo $item->news_event_start;
                                        }
                                    @endphp
                                    @if($item->news_event_end)
                                     - 
                                     @php
                                        try {
                                            echo \Carbon\Carbon::parse($item->news_event_end)->locale('th')->addYears(543)->isoFormat('D MMM YY');
                                        } catch (\Exception $e) {
                                            echo $item->news_event_end;
                                        }
                                    @endphp
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif

                        <!-- Views -->
                        <div class="d-flex align-items-center">
                            <i class="bi bi-eye-fill text-info me-2"></i>
                            <div>
                                <span class="d-block lh-1 text-uppercase" style="font-size: 0.75rem;">จำนวนผู้เข้าชม</span>
                                <span class="fw-bold text-body">{{ number_format($item->news_count) }} ครั้ง</span>
                            </div>
                        </div>

                         <!-- Type -->
                        @if($item->news_type)
                        <div class="d-flex align-items-center">
                            <i class="bi bi-tag-fill text-secondary me-2"></i>
                            <div>
                                <span class="d-block lh-1 text-uppercase" style="font-size: 0.75rem;">ประเภท</span>
                                <span class="badge bg-secondary rounded-pill">{{ $item->news_type }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Image -->
                    @if($item->news_img)
                        <div class="mb-4 rounded-3 overflow-hidden shadow-sm border position-relative">
                            <img src="{{ Storage::url($item->news_img) }}" class="img-fluid w-100 object-fit-cover" alt="{{ $item->news_name }}">
                        </div>
                    @endif

                    <!-- Detail -->
                    <div class="content-body">
                         <h5 class="fw-bold text-primary mb-3"><i class="bi bi-file-text me-2"></i>รายละเอียด</h5>
                         <div class="text-break lh-lg">
                             {!! html_entity_decode($item->news_detail) !!}
                         </div>
                    </div>

                     <!-- Reference/Link -->
                    @if($item->news_reference || $item->news_link)
                        <div class="mt-4 pt-4 border-top">
                             @if($item->news_reference)
                                <div class="mb-3">
                                    <strong class="d-block text-muted mb-1"><i class="bi bi-bookmark-fill me-1"></i>อ้างอิง:</strong>
                                    <span class="text-body">{{ $item->news_reference }}</span>
                                </div>
                             @endif
                             @if($item->news_link)
                                <a href="{{ $item->news_link }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-link-45deg me-1"></i> ลิงก์ที่เกี่ยวข้อง</a>
                             @endif
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top">
                <a href="{{ route('frontend.researchnews.index') }}" class="btn btn-secondary px-4 rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .content-body img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin: 1rem 0;
    }
</style>
@endsection