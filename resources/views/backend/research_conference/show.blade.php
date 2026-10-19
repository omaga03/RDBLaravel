@extends('layouts.app')

@section('content')
    <div class="row">
        <!-- Header & Action Buttons -->
        <x-page-header 
            title="รายละเอียดงานประชุมวิชาการ"
            icon="bi-calendar-event"
            :backRoute="route('backend.research_conference.index')"
            :editRoute="auth()->user()->can('update', $item) ? route('backend.research_conference.edit', $item->id) : null"
            :deleteRoute="auth()->user()->can('delete', $item) ? route('backend.research_conference.destroy', $item->id) : null"
            :canDelete="auth()->user()->can('delete', $item)"
            :showPrint="true"
            :breadcrumbs="[
                'แดชบอร์ด' => route('backend.site.index'),
                'จัดการการประชุมวิชาการ' => route('backend.research_conference.index'),
                'รายละเอียดงานประชุม' => ''
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

        <!-- ข้อมูลงานประชุม -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border">
                <div class="card-header text-white p-3 p-md-4 d-print-none border-0" style="background: var(--rdb-gradient-primary);">
                    <span class="small text-white-50"><i class="bi bi-megaphone-fill me-1"></i> ข้อมูลงานประชุมวิชาการ</span>
                    <h4 class="card-title mb-0 mt-1 fw-semibold text-white text-break" style="font-size: 1.15rem; line-height: 1.55;">
                        {{ strip_tags($item->con_name) }}
                    </h4>
                </div>
                <div class="card-body">
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
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
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

                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="fw-bold mb-0 text-body"><i class="bi bi-file-text-fill text-primary me-2"></i>รายละเอียด</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill copy-abstract-btn d-inline-flex align-items-center" style="font-size: 0.75rem;" onclick="copyAbstractText('con-backend-detail-text', this)">
                            <i class="bi bi-clipboard me-1"></i> คัดลอกข้อความ
                        </button>
                    </div>
                    <div class="abstract-box p-3 p-md-4 border border-start border-4 border-primary rounded-3 bg-body-tertiary shadow-xs">
                        <div id="con-backend-detail-text" class="abstract-content abstract-indent">
                            {!! strip_tags($item->con_detail, '<p><br><b><strong><i><em><u><ul><ol><li><a><span>') !!}
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
        <div class="col-md-12 d-print-none">
             <x-action-buttons 
                :backRoute="route('backend.research_conference.index')"
                :editRoute="auth()->user()->can('update', $item) ? route('backend.research_conference.edit', $item->id) : null"
                :deleteRoute="auth()->user()->can('delete', $item) ? route('backend.research_conference.destroy', $item->id) : null"
                :canDelete="auth()->user()->can('delete', $item)"
                :showPrint="true"
            />
        </div>
    </div>
@endsection
