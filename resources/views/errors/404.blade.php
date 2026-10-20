@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card shadow-sm border-0 py-5 px-4 rounded-4">
                <div class="card-body">
                    <div class="mb-4">
                        <span class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle p-4 shadow-sm" style="width: 100px; height: 100px;">
                            <i class="bi bi-search" style="font-size: 3.2rem;"></i>
                        </span>
                    </div>

                    <h1 class="display-5 fw-bold text-warning mb-2">404</h1>
                    <h3 class="fw-bold text-body mb-3">ไม่พบหน้าที่คุณต้องการ (Page Not Found)</h3>

                    @if(!empty($exception) && $exception->getMessage())
                        <div class="alert alert-warning border-warning-subtle small mb-4 py-2.5 px-3 text-center">
                            <i class="bi bi-info-circle-fill me-1"></i> {{ $exception->getMessage() }}
                        </div>
                    @else
                        <p class="text-body-secondary mb-4 fs-6 leading-relaxed">
                            ขออภัย หน้าเว็บหรือข้อมูลที่คุณกำลังเรียกหาไม่มีอยู่ในระบบ ย้ายตำแหน่ง หรือถูกลบออกแล้ว<br>
                            กรุณาตรวจสอบ URL อีกครั้ง หรือกลับไปยังหน้าหลัก
                        </p>
                    @endif

                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary px-4 py-2">
                            <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
                        </a>
                        <a href="{{ auth()->check() ? route('backend.site.index') : url('/') }}" class="btn btn-primary px-4 py-2 shadow-sm">
                            <i class="bi bi-house-door me-1"></i> กลับหน้าหลัก
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
