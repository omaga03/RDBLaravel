@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card shadow-sm border-0 py-5 px-4 rounded-4">
                <div class="card-body">
                    <div class="mb-4">
                        <span class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle p-4 shadow-sm" style="width: 100px; height: 100px;">
                            <i class="bi bi-shield-x" style="font-size: 3.5rem;"></i>
                        </span>
                    </div>

                    <h1 class="display-5 fw-bold text-danger mb-2">403</h1>
                    <h3 class="fw-bold text-body mb-3">ไม่มีสิทธิ์เข้าถึงหน้านี้ (Access Denied)</h3>

                    <p class="text-body-secondary mb-4 fs-6 leading-relaxed">
                        ขออภัย บัญชีผู้ใช้งานของคุณไม่ได้รับอนุญาตให้เข้าถึงหรือจัดการข้อมูลในส่วนนี้<br>
                        หากคุณต้องการใช้งานโมดูลนี้ กรุณาติดต่อผู้ดูแลระบบเพื่อขอรับการกำหนดสิทธิ์เพิ่มเติม
                    </p>

                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary px-4 py-2">
                            <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
                        </a>
                        <a href="{{ route('backend.site.index') }}" class="btn btn-primary px-4 py-2 shadow-sm">
                            <i class="bi bi-speedometer2 me-1"></i> กลับหน้าแดชบอร์ด
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
