@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white py-3">
            <h4 class="mb-0 fw-bold"><i class="bi bi-megaphone-fill me-2"></i>{{ $item->con_name }}</h4>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <!-- Main Content -->
                <div class="col-lg-12">
                    <!-- Meta Info (2 Columns) -->
                    <div class="mb-5">
                        <div class="row g-0 border rounded-4 overflow-hidden shadow-sm">
                            <!-- Left Column: Date, Venue, Views -->
                            <div class="col-lg-8 p-4 bg-body-tertiary"> <!-- Adaptive light bg -->
                                <div class="d-flex flex-column gap-4">
                                    <!-- Event Date -->
                                    @if($item->con_even_date)
                                    <div class="d-flex align-items-start">
                                        <div class="me-3 mt-1">
                                            <i class="bi bi-calendar-check text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="d-block text-uppercase text-muted fw-bold small mb-1">วันจัดงาน</span>
                                            <span class="fw-bold text-body fs-5">
                                                @php
                                                    try {
                                                        echo \Carbon\Carbon::parse($item->con_even_date)->locale('th')->addYears(543)->isoFormat('LL');
                                                    } catch (\Exception $e) {
                                                        echo $item->con_even_date;
                                                    }
                                                @endphp
                                            </span>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Venue -->
                                    @if($item->con_venue)
                                    <div class="d-flex align-items-start">
                                        <div class="me-3 mt-1">
                                            <i class="bi bi-geo-alt-fill text-danger fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="d-block text-uppercase text-muted fw-bold small mb-1">สถานที่จัดงาน</span>
                                            <span class="fw-bold text-body fs-5 lh-sm">{{ $item->con_venue }}</span>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Views -->
                                    <div class="d-flex align-items-center">
                                         <div class="me-3">
                                            <i class="bi bi-eye-fill text-info fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="d-block text-uppercase text-muted fw-bold small mb-1">จำนวนผู้เข้าชม</span>
                                            <span class="fw-bold text-body fs-5">{{ number_format($item->con_count) }} ครั้ง</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Deadline (Highlighted) -->
                            <div class="col-lg-4 p-4 bg-body d-flex align-items-center justify-content-center border-start-lg">
                                <div class="card w-100 h-100 border-2 border-warning shadow-none rounded-3 d-flex align-items-center justify-content-center text-center p-3">
                                    @if($item->con_sub_deadline)
                                        <div>
                                            <div class="mb-2">
                                                <i class="bi bi-stopwatch-fill text-warning display-4"></i>
                                            </div>
                                            <span class="d-block text-warning fw-bold text-uppercase mb-2">ส่งบทความภายใน</span>
                                            <h5 class="fw-bolder text-body mb-0">
                                                @php
                                                    try {
                                                        echo \Carbon\Carbon::parse($item->con_sub_deadline)->locale('th')->addYears(543)->isoFormat('D MMMM YYYY');
                                                    } catch (\Exception $e) {
                                                        echo $item->con_sub_deadline;
                                                    }
                                                @endphp
                                            </h5>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-check-circle display-4 mb-2 d-block"></i>
                                            <span>ไม่ระบุวันหมดเขต</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Image -->
                    @if($item->con_img)
                        <div class="mb-4 rounded-3 overflow-hidden shadow-sm border position-relative">
                            <img src="{{ Storage::url($item->con_img) }}" class="img-fluid w-100 object-fit-cover" alt="{{ $item->con_name }}">
                        </div>
                    @endif

                    <!-- Detail -->
                     <div class="content-body">
                         <h5 class="fw-bold text-primary mb-3"><i class="bi bi-file-text me-2"></i>รายละเอียดการประชุม</h5>
                         @php
                             $content = html_entity_decode($item->con_detail);
                             
                             // 1. Remove specific HTML patterns for "dot lines" or empty lines
                             $patterns = [
                                 '/<p>\s*[\.•\-]\s*<\/p>/iu',       // <p>.</p>
                                 '/<p>\s*&nbsp;\s*<\/p>/iu',      // <p>&nbsp;</p>
                                 '/<br\s*\/?>\s*[\.•\-]\s*/iu',   // <br>.
                                 '/<div[^>]*>\s*[\.•\-]\s*<\/div>/iu', // <div>.</div>
                             ];
                             $content = preg_replace($patterns, '', $content);

                             // 2. Remove standard text-based "dot lines" and separators (multiline)
                             $content = preg_replace('/^\s*[\.•\-]\s*$/m', '', $content);
                             $content = preg_replace('/^\s*[=_]{3,}\s*$/m', '', $content);

                             // 3. AGGRESSIVE: Collapse ALL multiple vertical whitespace to single
                             $content = preg_replace('/(<br\s*\/?>\s*)+/iu', '<br>', $content); // Any number of <br> -> single <br>
                             $content = preg_replace('/(\r?\n)+/', "\n", $content); // Any number of \n -> single \n
                             
                             // 4. Remove empty <p></p> entirely if consistent
                             $content = preg_replace('/<p>\s*<\/p>/iu', '', $content);
                         @endphp
                         <div class="text-break lh-lg">
                             {!! $content !!}
                         </div>
                    </div>

                     <!-- Website -->
                    @if($item->con_website)
                        <div class="mt-4 pt-4 border-top">
                             <a href="{{ $item->con_website }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-globe me-1"></i> เว็บไซต์การประชุม</a>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top">
                <a href="{{ route('frontend.researchcoferenceinthai.index') }}" class="btn btn-secondary px-4 rounded-pill">
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