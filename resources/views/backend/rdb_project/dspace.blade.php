@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('backend.site.index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backend.rdb_project.index') }}" class="text-decoration-none">จัดการโครงการวิจัย</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ส่งออก DSpace (Batch Mode)</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('backend.rdb_project.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm" style="height: 38px;">
                <i class="bi bi-arrow-left me-1.5"></i> กลับหน้ารายการ
            </a>
        </div>
    </div>

    <!-- Main Container Card -->
    <div class="card shadow-sm border rounded-3 p-4 mb-4 bg-body">
        <!-- Card Header Row -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 mb-3 border-bottom">
            <div class="d-flex align-items-center">
                <h3 class="mb-0 fw-bold text-body d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-up-right fs-4 text-body"></i>
                    <span>ระบบส่งออก DSpace (Batch Mode)</span>
                </h3>
                @if(!empty($projectIds))
                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle ms-3 px-2.5 py-1.5 rounded-pill" style="font-variant-numeric: tabular-nums;">
                        รอส่งออก {{ number_format(count($projectIds)) }} รายการ
                    </span>
                @endif
            </div>

            <div class="d-flex align-items-center gap-2">
                <label for="change-chunk-size" class="text-body-secondary small mb-0 fw-normal">แบ่งกลุ่มละ:</label>
                <select id="change-chunk-size" class="form-select form-select-sm" style="min-width: 125px; width: auto;">
                    @foreach($allowedPerPage as $val)
                        <option value="{{ $val }}" {{ $val == $perPage ? 'selected' : '' }}>{{ $val }} รายการ</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Alert Notification Box (Amber) -->
        <div class="alert text-white fw-medium d-flex align-items-center gap-2 mb-4 shadow-sm" style="background-color: #f39c12; border: none; border-radius: 6px; padding: 12px 18px;">
            <div>
                <strong>คำแนะนำ:</strong> เมื่อดาวน์โหลดเสร็จแล้ว ให้กดปุ่ม <i class="bi bi-check-lg fw-bold me-1.5"></i> เพื่อทำเครื่องหมายว่าส่งออกแล้ว
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
                    <form action="{{ route('backend.rdb_project.export_dspace_batch') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="ids_json" value='@json($chunk)'>
                        <input type="hidden" name="part_label" value="{{ $index + 1 }}">

                        <button type="submit" 
                                class="btn main-download-btn shadow-sm text-start"
                                onclick="return confirm('เริ่มดาวน์โหลดชุดที่ {{ $index + 1 }} ใช่หรือไม่?')">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-download fs-4 me-2 text-body"></i>
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
                    <h5 class="fw-semibold text-body">ไม่มีโครงการวิจัยที่รอส่งออก DSpace ในขณะนี้</h5>
                    <p class="text-body-secondary small mb-0">โครงการวิจัยที่เสร็จสิ้นทั้งหมดได้รับการส่งออกไปยัง DSpace เรียบร้อยแล้ว หรือยังไม่มีโครงการใหม่ที่ตรงตามเงื่อนไข</p>
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
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dropdown pagination / chunk size change
    const chunkSelect = document.getElementById('change-chunk-size');
    if (chunkSelect) {
        chunkSelect.addEventListener('change', function () {
            const perPage = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            window.location.href = url.href;
        });
    }

    // Status update via AJAX
    const statusButtons = document.querySelectorAll('.btn-update-status');
    statusButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const wrapper = this.closest('.batch-card-wrapper');
            const rawIds = this.getAttribute('data-ids');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                           || document.querySelector('input[name="_token"]')?.value;

            let ids = [];
            try {
                ids = JSON.parse(rawIds);
            } catch (err) {
                ids = rawIds ? [rawIds] : [];
            }

            if (!confirm('ต้องการอัพเดทสถานะชุดนี้เป็น "ส่งออกแล้ว" ใช่หรือไม่?')) {
                return;
            }

            // Spinner
            const originalHtml = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm text-white" style="width: 14px; height: 14px;" role="status"></span>';
            this.disabled = true;

            fetch("{{ route('backend.rdb_project.update_dspace_batch_status') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    wrapper.classList.add('batch-done');
                    this.classList.remove('btn-success');
                    this.classList.add('btn-secondary');
                    this.innerHTML = '<i class="bi bi-check2-all fw-bold"></i>';
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถอัปเดตได้'));
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                }
            })
            .catch(err => {
                alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้: ' + err.message);
                this.innerHTML = originalHtml;
                this.disabled = false;
            });
        });
    });
});
</script>
@endpush
@endsection
