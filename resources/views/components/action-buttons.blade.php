@props([
    'backRoute',
    'editRoute' => null,
    'deleteRoute' => null,
    'canDelete' => true,
    'showPrint' => true,
    'formId' => 'delete-form-bottom'
])

<div class="d-flex justify-content-end gap-2 mt-4 d-print-none">
    <a href="{{ $backRoute }}" class="btn btn-secondary d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
    </a>
    @if($editRoute)
    <a href="{{ $editRoute }}" class="btn btn-warning d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
        <i class="bi bi-pencil me-2"></i> แก้ไข
    </a>
    @endif
    @if($showPrint)
    <button onclick="window.print()" class="btn btn-primary d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
        <i class="bi bi-printer me-2"></i> พิมพ์
    </button>
    @endif
    @if($deleteRoute && $canDelete)
    <button type="submit" form="{{ $formId }}" class="btn btn-danger d-inline-flex justify-content-center align-items-center" style="min-width: 120px;" onclick="return confirm('ยืนยันลบข้อมูลนี้?');">
        <i class="bi bi-trash me-2"></i> ลบ
    </button>
    <form id="{{ $formId }}" action="{{ $deleteRoute }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
    @endif
    {{ $slot }}
</div>
