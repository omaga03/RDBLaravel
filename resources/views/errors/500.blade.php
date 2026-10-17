@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card shadow-sm border-0 py-5 px-4 rounded-4">
                <div class="card-body">
                    <div class="mb-4">
                        <span class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle p-4 shadow-sm" style="width: 100px; height: 100px;">
                            <i class="bi bi-exclamation-triangle" style="font-size: 3.2rem;"></i>
                        </span>
                    </div>

                    <h1 class="display-5 fw-bold text-danger mb-2">500</h1>
                    <h3 class="fw-bold text-body mb-3">เกิดข้อผิดพลาดภายในระบบ (Server Error)</h3>

                    <p class="text-body-secondary mb-4 fs-6 leading-relaxed">
                        ขออภัย เกิดข้อผิดพลาดทางเทคนิคขึ้นในการประมวลผลคำขอของคุณ<br>
                        เจ้าหน้าที่ดูแลระบบได้รับข้อมูลเพื่อตรวจสอบและแก้ไขปัญหาแล้ว กรุณาลองใหม่อีกครั้งในภายหลัง
                    </p>

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
