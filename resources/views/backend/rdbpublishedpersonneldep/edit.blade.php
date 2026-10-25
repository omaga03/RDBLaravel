@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-body"><i class="bi bi-pencil-square text-warning me-2"></i>แก้ไขจำนวนบุคลากรระดับคณะ/หลักสูตร: {{ $item->department->department_nameTH ?? '' }}</h4>
            <div class="text-secondary small">แก้ไขข้อมูลจำนวนบุคลากรสายวิชาการ</div>
        </div>
        <a href="{{ route('backend.rdbpublishedpersonneldep.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('backend.rdbpublishedpersonneldep.update', $item->perpd_id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('backend.rdbpublishedpersonneldep._form')
                
                <div class="mt-4 pt-3 border-top d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> บันทึกการแก้ไข
                    </button>
                    <a href="{{ route('backend.rdbpublishedpersonneldep.index') }}" class="btn btn-light px-4 border">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
