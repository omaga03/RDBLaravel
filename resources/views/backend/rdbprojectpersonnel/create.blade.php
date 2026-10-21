@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body"><i class="bi bi-plus-circle text-primary me-2"></i>เพิ่มจำนวนบุคลากรในแต่ละคณะ รายปี (โครงการ)</h4>
            <div class="text-secondary small">กำหนดจำนวนอาจารย์/นักวิจัยประจำคณะ เพื่อใช้คำนวณเป้าหมายและสัดส่วนงบประมาณโครงการวิจัย</div>
        </div>
        <a href="{{ route('backend.rdbprojectpersonnel.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('backend.rdbprojectpersonnel.store') }}" method="POST">
                @csrf
                @include('backend.rdbprojectpersonnel._form')
                
                <div class="mt-4 pt-3 border-top d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> บันทึกข้อมูล
                    </button>
                    <a href="{{ route('backend.rdbprojectpersonnel.index') }}" class="btn btn-light px-4 border">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
