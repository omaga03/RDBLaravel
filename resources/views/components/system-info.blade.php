@props(['created_at', 'created_by' => null, 'updated_at' => null, 'updated_by' => null])

@php
    use App\Helpers\ThaiDateHelper;
    $collapseId = 'systemInfoCollapse_' . \Illuminate\Support\Str::random(8);
@endphp

<div class="card shadow-sm border mb-3">
    <button class="btn btn-light bg-body-tertiary border-0 text-start w-100 d-flex justify-content-between align-items-center py-2.5 px-3 rounded text-body-secondary" 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#{{ $collapseId }}" 
            aria-expanded="false" 
            aria-controls="{{ $collapseId }}"
            title="คลิกเพื่อเปิดดูข้อมูลระบบ">
        <span class="small fw-semibold d-flex align-items-center text-body">
            <i class="bi bi-clock-history me-2 text-secondary"></i>ข้อมูลระบบ (System Info)
        </span>
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle d-inline-flex align-items-center gap-1 font-monospace" style="font-size: 0.72rem;">
            <span>คลิกเพื่อดู</span>
            <i class="bi bi-chevron-down ms-1"></i>
        </span>
    </button>
    <div class="collapse" id="{{ $collapseId }}">
        <div class="card-body py-3 px-3 bg-body border-top small text-body">
            <div class="row g-2">
                <div class="col-sm-6">
                    <span class="text-body-secondary d-block">สร้างเมื่อ:</span>
                    <span class="fw-medium">{{ $created_at ? ThaiDateHelper::formatDateTime($created_at) : '-' }}</span>
                </div>
                <div class="col-sm-6">
                    <span class="text-body-secondary d-block">โดย:</span>
                    <span class="fw-medium">{{ $created_by ?? '-' }}</span>
                </div>
                <div class="col-12"><hr class="my-1 border-secondary-subtle"></div>
                <div class="col-sm-6">
                    <span class="text-body-secondary d-block">แก้ไขล่าสุด:</span>
                    <span class="fw-medium">{{ $updated_at ? ThaiDateHelper::formatDateTime($updated_at) : '-' }}</span>
                </div>
                <div class="col-sm-6">
                    <span class="text-body-secondary d-block">โดย:</span>
                    <span class="fw-medium">{{ $updated_by ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
