@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backend.rdb_published.index') }}" class="text-decoration-none">จัดการผลงานตีพิมพ์</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ส่งออก DSpace (Batch Mode)</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('backend.rdb_published.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                <i class="bi bi-arrow-left me-1.5"></i> กลับหน้ารายการ
            </a>
        </div>
    </div>

    <!-- Mode Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link active fw-semibold shadow-sm d-inline-flex align-items-center" href="{{ route('backend.rdb_published.dspace_index') }}">
                <i class="bi bi-box-arrow-up-right me-2"></i> ระบบส่งออก DSpace (Batch Mode)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link bg-body border border-subtle text-body fw-semibold shadow-sm d-inline-flex align-items-center hover-primary" href="{{ route('backend.rdb_published.dspace_check') }}">
                <i class="bi bi-file-earmark-check me-2 text-primary"></i> ตรวจสอบจำนวนหน้า &amp; สถานะไฟล์ PDF (Check Mode)
            </a>
        </li>
    </ul>

    <!-- Main Container Card -->
    <div class="card shadow-sm border rounded-3 p-4 mb-4 bg-body">
        <!-- Card Header Row -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 mb-3 border-bottom">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h3 class="mb-0 fw-bold text-body d-flex align-items-center gap-2">
                    <i class="bi bi-journal-arrow-up fs-4 text-primary"></i>
                    <span>ระบบส่งออก DSpace ผลงานตีพิมพ์ (Batch Mode)</span>
                </h3>
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle ms-2 px-2.5 py-1.5 rounded-pill" style="font-variant-numeric: tabular-nums;">
                    {{ $statusFilter === 'exported' ? 'ส่งออกแล้ว' : ($statusFilter === 'all' ? 'ทั้งหมด' : 'รอส่งออก') }} {{ number_format(count($publishedIds)) }} รายการ
                </span>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-3">
                <!-- Status Filter -->
                <div class="d-flex align-items-center gap-1.5">
                    <label for="change-status-filter" class="text-body-secondary small mb-0 fw-normal">สถานะ:</label>
                    <select id="change-status-filter" class="form-select form-select-sm" style="min-width: 140px; width: auto;">
                        <option value="unexported" {{ $statusFilter === 'unexported' ? 'selected' : '' }}>รอส่งออก ({{ number_format($unexportedCount) }})</option>
                        <option value="exported" {{ $statusFilter === 'exported' ? 'selected' : '' }}>ส่งออกแล้ว ({{ number_format($exportedCount) }})</option>
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>ทั้งหมด ({{ number_format($totalWithFile) }})</option>
                    </select>
                </div>

                <!-- Chunk Size -->
                <div class="d-flex align-items-center gap-1.5">
                    <label for="change-chunk-size" class="text-body-secondary small mb-0 fw-normal">แบ่งกลุ่มละ:</label>
                    <select id="change-chunk-size" class="form-select form-select-sm" style="min-width: 125px; width: auto;">
                        @foreach($allowedPerPage as $val)
                            <option value="{{ $val }}" {{ $val == $perPage ? 'selected' : '' }}>{{ $val }} รายการ</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Alert Notification Box (Amber) -->
        <div class="alert text-white fw-medium d-flex align-items-center gap-2 mb-4 shadow-sm" style="background-color: #f39c12; border: none; border-radius: 6px; padding: 12px 18px;">
            <div>
                <strong>คำแนะนำ:</strong> เมื่อดาวน์โหลดแพ็กเกจ ZIP เสร็จแล้ว ให้กดปุ่ม <i class="bi bi-check-lg fw-bold me-1.5"></i> เพื่อทำเครื่องหมายว่าส่งออกแล้ว หรือไปที่ <a href="{{ route('backend.rdb_published.dspace_check') }}" class="text-white text-decoration-underline fw-bold">หน้าตรวจสอบจำนวนหน้า & สถานะไฟล์ PDF</a> เพื่อตรวจความสมบูรณ์ก่อนส่งออก
            </div>
        </div>

        <!-- Batch Cards Grid -->
        <div class="d-flex flex-wrap gap-3">
            @forelse($chunks as $index => $chunk)
                @php
                    $firstId = reset($chunk);
                    $lastId = end($chunk);
                    $count = count($chunk);
                    $batchId = "batch-" . ($index + 1);
                @endphp
                <div class="batch-card-wrapper position-relative" id="{{ $batchId }}">
                    <form action="{{ route('backend.rdb_published.dspace_batch') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="ids_json" value='@json($chunk)'>
                        <input type="hidden" name="part_label" value="{{ $index + 1 }}">

                        <button type="submit" 
                                class="btn main-download-btn shadow-sm text-start"
                                onclick="return confirm('เริ่มดาวน์โหลดแพ็กเกจ DSpace ชุดที่ {{ $index + 1 }} ใช่หรือไม่?')">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-download fs-4 me-2 text-primary"></i>
                                <strong class="fs-5 text-body">ชุดที่ {{ $index + 1 }}</strong>
                            </div>
                            <div class="text-body-secondary small mt-1 ps-1" style="font-variant-numeric: tabular-nums;">
                                ID: {{ $firstId }} – {{ $lastId }} <span class="ms-1">({{ $count }} รายการ)</span>
                            </div>
                        </button>
                    </form>

                    <!-- Round Status Update Button -->
                    <button type="button" 
                            class="btn btn-success btn-update-status rounded-circle position-absolute" 
                            title="ทำเครื่องหมายว่าส่งออกแล้ว"
                            data-ids='@json($chunk)'
                            style="top: 8px; right: 8px; width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; z-index: 5;">
                        <i class="bi bi-check-lg fw-bold" style="font-size: 1rem;"></i>
                    </button>
                </div>
            @empty
                <div class="w-100 py-5 text-center text-body-secondary">
                    <i class="bi bi-check2-circle text-success fs-1 d-block mb-3"></i>
                    <h5 class="fw-semibold text-body">ไม่มีผลงานตีพิมพ์ที่รอส่งออก DSpace ในกลุ่มนี้</h5>
                    <p class="text-body-secondary small mb-0">ผลงานตีพิมพ์ทั้งหมดได้รับการส่งออกไปยัง DSpace เรียบร้อยแล้ว หรือยังไม่มีรายการใหม่ที่ตรงตามเงื่อนไข</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
.main-download-btn {
    border: 1px solid var(--bs-border-color);
    border-radius: 8px;
    background-color: var(--bs-body-bg);
    min-width: 240px;
    padding: 14px 18px;
    transition: all 0.25s ease;
    cursor: pointer;
}
.main-download-btn:hover {
    border-color: #0d6efd;
    background-color: var(--bs-body-bg);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}
.batch-card-wrapper {
    display: inline-block;
}
.batch-done {
    opacity: 0.45;
    filter: grayscale(1);
    pointer-events: none;
}
.spin {
    animation: spin 1s linear infinite;
    display: inline-block;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dropdown chunk size change
    const chunkSelect = document.getElementById('change-chunk-size');
    if (chunkSelect) {
        chunkSelect.addEventListener('change', function () {
            const perPage = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            window.location.href = url.href;
        });
    }

    // Dropdown status filter change
    const statusSelect = document.getElementById('change-status-filter');
    if (statusSelect) {
        statusSelect.addEventListener('change', function () {
            const status = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('status', status);
            window.location.href = url.href;
        });
    }

    // Status update via AJAX
    const updateButtons = document.querySelectorAll('.btn-update-status');
    const updateUrl = "{{ route('backend.rdb_published.dspace_update_status') }}";
    const csrfToken = "{{ csrf_token() }}";

    updateButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const wrapper = this.closest('.batch-card-wrapper');
            const ids = this.getAttribute('data-ids');

            if (!confirm('ต้องการอัปเดตสถานะผลงานชุดนี้เป็น "ส่งออกแล้ว" ใช่หรือไม่?')) {
                return;
            }

            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="bi bi-arrow-repeat spin"></i>';
            this.disabled = true;

            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: JSON.parse(ids) })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    wrapper.classList.add('batch-done');
                    this.classList.remove('btn-success');
                    this.classList.add('btn-secondary');
                    this.innerHTML = '<i class="bi bi-check2-all fw-bold"></i>';
                    this.title = 'ส่งออกแล้ว';
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกสถานะได้'));
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้');
                this.innerHTML = originalHtml;
                this.disabled = false;
            });
        });
    });
});
</script>
@endpush
@endsection
