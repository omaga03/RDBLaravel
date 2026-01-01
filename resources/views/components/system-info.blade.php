@props(['created_at', 'created_by', 'updated_at' => null, 'updated_by' => null])

@php
    use App\Helpers\ThaiDateHelper;
@endphp

<div class="card shadow-sm">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>ข้อมูลระบบ (System Info)</h5>
    </div>
    <div class="card-body">
        <p class="mb-2">
            <strong>สร้างเมื่อ:</strong> {{ $created_at ? ThaiDateHelper::formatDateTime($created_at) : '-' }}
        </p>
        <p class="mb-2">
            <strong>โดย:</strong> {{ $created_by ?? '-' }}
        </p>
        <hr class="my-2">
        <p class="mb-2">
            <strong>แก้ไขล่าสุด:</strong> {{ $updated_at ? ThaiDateHelper::formatDateTime($updated_at) : '-' }}
        </p>
        <p class="mb-0">
            <strong>โดย:</strong> {{ $updated_by ?? '-' }}
        </p>
    </div>
</div>
